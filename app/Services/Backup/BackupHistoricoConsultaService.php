<?php

namespace App\Services\Backup;

use App\Models\LogBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupHistoricoConsultaService
{
    private const REGEX =
        '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.zip$/';

    private function listarBackups(): array
    {
        $pastas = [
            storage_path('app/private/backups'),
            storage_path('app/backups'),
        ];

        try {
            array_unshift(
                $pastas,
                Storage::disk(
                    config('backup.disk', 'local')
                )->path('backups')
            );
        } catch (Throwable $e) {
            // Utilizar pastas conhecidas.
        }

        $arquivos = [];

        foreach (array_unique($pastas) as $pasta) {
            if (!is_dir($pasta)) {
                continue;
            }

            foreach (glob(
                rtrim($pasta, '/\\') .
                DIRECTORY_SEPARATOR . 'backup_*.zip'
            ) ?: [] as $path) {
                $nome = basename($path);

                if (!preg_match(self::REGEX, $nome)) {
                    continue;
                }

                $arquivos[$nome] = [
                    'nome' => $nome,
                    'path' => $path,
                    'timestamp' => filemtime($path),
                ];
            }
        }

        return $arquivos;
    }

    private function marcadores(): array
    {
        $pasta = storage_path(
            'app/private/backup_scheduler'
        );

        $resultado = [];

        foreach (
            glob($pasta . DIRECTORY_SEPARATOR . '*.ok') ?: []
            as $path
        ) {
            $dados = json_decode(
                (string) @file_get_contents($path),
                true
            );

            $nome = $dados['arquivo'] ?? null;

            if (!is_string($nome) ||
                !preg_match(self::REGEX, $nome)) {
                continue;
            }

            $resultado[$nome][] = $dados;
        }

        return $resultado;
    }

    private function pendentes(): array
    {
        $pasta = storage_path(
            'app/private/backup_scheduler/pendentes'
        );

        $resultado = [];

        foreach (
            glob($pasta . DIRECTORY_SEPARATOR . '*.json') ?: []
            as $path
        ) {
            $nome = basename($path, '.json');

            if (preg_match(self::REGEX, $nome)) {
                $resultado[$nome] = true;
            }
        }

        return $resultado;
    }

    private function encontrarArquivo(
        LogBackup $log,
        array $arquivos
    ): ?array {
        foreach ([
            'nome_arquivo',
            'arquivo',
            'arquivo_backup',
        ] as $campo) {
            $valor = $log->getAttribute($campo);

            if (is_string($valor)) {
                $nome = basename(
                    str_replace('\\', '/', $valor)
                );

                if (isset($arquivos[$nome])) {
                    return $arquivos[$nome];
                }
            }
        }

        if (!$log->created_at) {
            return null;
        }

        $candidatos = [];

        foreach ($arquivos as $arquivo) {
            if (preg_match(
                '/^backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.zip$/',
                $arquivo['nome'],
                $partes
            )) {
                try {
                    $data = Carbon::createFromFormat(
                        '!Y-m-d H-i-s',
                        $partes[1] . ' ' . $partes[2]
                    );

                    if (
                        abs(
                            $data->timestamp -
                            $log->created_at->timestamp
                        ) <= 30
                    ) {
                        $candidatos[] = $arquivo;
                    }
                } catch (Throwable $e) {
                    continue;
                }
            }
        }

        // Nao associar arquivos ambiguos.
        return count($candidatos) === 1
            ? $candidatos[0]
            : null;
    }

    private function segundaCopia(
        array $arquivo,
        array $config,
        array $pendentes
    ): string {
        $nome = $arquivo['nome'];

        if (isset($pendentes[$nome])) {
            return 'Pendente';
        }

        $base = trim(
            (string) ($config['destino_secundario'] ?? '')
        );

        if ($base === '') {
            return 'Não configurada';
        }

        $pastas = [
            $base,
            rtrim($base, '/\\') .
                DIRECTORY_SEPARATOR . 'deposito_materiais_backups',
        ];

        foreach ($pastas as $pasta) {
            $destino = rtrim($pasta, '/\\') .
                DIRECTORY_SEPARATOR . $nome;

            if (!is_file($destino)) {
                continue;
            }

            if (
                !is_file($arquivo['path']) ||
                filesize($arquivo['path']) !== filesize($destino)
            ) {
                return 'Divergente';
            }

            try {
                $hash1 = hash_file('sha256', $arquivo['path']);
                $hash2 = hash_file('sha256', $destino);

                if (
                    is_string($hash1) &&
                    is_string($hash2) &&
                    hash_equals($hash1, $hash2)
                ) {
                    return 'Validada';
                }

                return 'Divergente';
            } catch (Throwable $e) {
                return 'Falha na verificação';
            }
        }

        return 'Não encontrada';
    }

    public function preparar($paginador)
    {
        $arquivos = $this->listarBackups();
        $marcadores = $this->marcadores();
        $pendentes = $this->pendentes();

        $config = [];

        $pathConfig = storage_path(
            'app/private/backup_automatico_config.json'
        );

        if (is_file($pathConfig)) {
            $dados = json_decode(
                (string) @file_get_contents($pathConfig),
                true
            );

            if (is_array($dados)) {
                $config = $dados;
            }
        }

        $paginador->getCollection()->transform(
            function ($log) use (
                $arquivos,
                $marcadores,
                $pendentes,
                $config
            ) {
                $geracao = $log->acao === 'BACKUP_GERAR';

                $arquivo = $geracao
                    ? $this->encontrarArquivo($log, $arquivos)
                    : null;

                $nome = $arquivo['nome'] ?? null;
                $marcas = $nome
                    ? ($marcadores[$nome] ?? [])
                    : [];

                $origem = 'Não identificada';
                $horarios = [];
                $recuperacao = count($marcas) > 1;

                foreach ($marcas as $marca) {
                    $programado = $marca['programado'] ?? null;

                    if (is_string($programado)) {
                        $horarios[] = $programado;
                    }

                    if (
                        !empty($marca['recuperacao']) ||
                        (int) ($marca['horarios_consolidados'] ?? 1) > 1
                    ) {
                        $recuperacao = true;
                    }
                }

                if ($marcas) {
                    $origem = $recuperacao
                        ? 'Recuperação'
                        : 'Automático';
                }

                $log->historico_origem = $origem;

                $log->historico_zip = $nome ?: '-';

                $log->historico_horarios = array_unique(
                    $horarios
                );

                $log->historico_principal = $arquivo
                    ? 'Presente'
                    : ($geracao ? 'Não identificado' : '-');

                $log->historico_secundaria =
                    ($arquivo && $marcas)
                        ? $this->segundaCopia(
                            $arquivo,
                            $config,
                            $pendentes
                        )
                        : '-';

                return $log;
            }
        );

        return $paginador;
    }
}