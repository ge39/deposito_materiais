<?php

namespace App\Console\Commands;

use App\Services\Backup\Actions\GenerateBackupAction;
use App\Services\Backup\BackupAutomationSettings;
use App\Services\Backup\BackupAutomaticoProtecaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GerarBackupAutomatico extends Command
{
    protected $signature = 'backup:automatico';

    protected $description =
        'Executa, recupera e protege backups automaticos do ERP';

    public function handle(
        BackupAutomationSettings $settings,
        BackupAutomaticoProtecaoService $protecao,
        GenerateBackupAction $gerador
    ): int {
        $pasta = storage_path('app/private/backup_scheduler');

        File::ensureDirectoryExists($pasta);

        $lock = fopen(
            $pasta . DIRECTORY_SEPARATOR . 'execucao.lock',
            'c+'
        );

        if (!$lock) {
            $this->error('Falha ao abrir controle de concorrencia.');
            return self::FAILURE;
        }

        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            return self::SUCCESS;
        }

        try {
            $config = $settings->obter();

            // Tentar novamente copias externas pendentes.
            $protecao->processarPendentes($config);

            // Limpeza dos backups anteriores a 30 dias.
            $protecao->limpar30Dias($config);

            if (empty($config['ativo'])) {
                return self::SUCCESS;
            }

            $agora = now();
            $horarios = $settings->horarios();

            // Identificar o ultimo horario programado vencido,
            // considerando hoje e ontem.
            $vencidos = [];

            foreach ([1, 0] as $diasAtras) {
                $dia = $agora->copy()->subDays($diasAtras);

                foreach ($horarios as $horario) {
                    $data = $dia->format('Y-m-d') . ' ' .
                        $horario . ':00';

                    $programado = \Carbon\Carbon::parse(
                        $data,
                        config('app.timezone')
                    );

                    if ($programado->lte($agora)) {
                        $vencidos[] = $programado;
                    }
                }
            }

            if (empty($vencidos)) {
                return self::SUCCESS;
            }

            usort(
                $vencidos,
                fn ($a, $b) => $b->timestamp <=> $a->timestamp
            );

            $ultimo = $vencidos[0];

            $identificador = $ultimo->format('Y-m-d_H-i');

            $marcador = $pasta . DIRECTORY_SEPARATOR .
                $identificador . '.ok';

            if (is_file($marcador)) {
                return self::SUCCESS;
            }

            // Evita repetir um backup ja concluido depois
            // de varios horarios perdidos.
            $marcadores = glob(
                $pasta . DIRECTORY_SEPARATOR . '*.ok'
            ) ?: [];

            foreach ($marcadores as $anterior) {
                $nome = basename($anterior, '.ok');

                if (
                    preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}$/', $nome) &&
                    strcmp($nome, $identificador) > 0
                ) {
                    return self::SUCCESS;
                }
            }

            $this->info(
                'Backup programado: ' .
                $ultimo->format('d/m/Y H:i')
            );

            // Mesma rotina utilizada no Backup Manual.
            $arquivo = $gerador->execute();

            if (!is_string($arquivo) || $arquivo === '') {
                throw new \RuntimeException(
                    'Geracao nao retornou arquivo.'
                );
            }

            $protecao->validarZip(
                $protecao->localizarPrincipal($arquivo)
            );

            // Registrar que a primeira copia foi concluida.
            file_put_contents(
                $marcador,
                json_encode([
                    'programado' => $ultimo->toDateTimeString(),
                    'executado' => now()->toDateTimeString(),
                    'arquivo' => $arquivo,
                ], JSON_PRETTY_PRINT),
                LOCK_EX
            );

            // Persistir pendencia antes de tentar a segunda copia.
            $protecao->salvarPendente(
                $arquivo,
                $ultimo->format('Y-m-d H:i')
            );

            $protecao->processarPendentes($config);

            $this->info(
                'Backup principal concluido: ' . $arquivo
            );

            Log::info('BACKUP_AUTOMATICO_GERADO', [
                'arquivo' => $arquivo,
                'horario' => $ultimo->toDateTimeString(),
            ]);

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error(
                'Falha no backup automatico: ' . $e->getMessage()
            );

            Log::error('BACKUP_AUTOMATICO_FALHA', [
                'erro' => $e->getMessage(),
            ]);

            return self::FAILURE;

        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}