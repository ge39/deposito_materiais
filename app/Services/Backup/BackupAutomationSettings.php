<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class BackupAutomationSettings
{
    private function caminho(): string
    {
        return storage_path('app/private/backup_automatico_config.json');
    }

    public function obter(): array
    {
        $padrao = [
            'ativo' => false,
            'horario' => '22:00',
            'quantidade' => 1,
            'horarios_personalizados' => [],
            'destino_secundario' => '',
        ];

        $arquivo = $this->caminho();

        if (!is_file($arquivo)) {
            return $padrao;
        }

        $dados = json_decode(
            (string) file_get_contents($arquivo),
            true
        );

        return is_array($dados)
            ? array_merge($padrao, $dados)
            : $padrao;
    }

    public function salvar(array $dados): void
    {
        $quantidade = (int) ($dados['quantidade'] ?? 0);

        if ($quantidade < 1 || $quantidade > 5) {
            throw new InvalidArgumentException(
                'Permitir de 1 a 5 backups diarios.'
            );
        }

        $horarios = array_values(
            (array) ($dados['horarios_personalizados'] ?? [])
        );

        if (count($horarios) !== $quantidade) {
            throw new InvalidArgumentException(
                'Informe um horario para cada backup.'
            );
        }

        foreach ($horarios as $horario) {
            if (
                !is_string($horario) ||
                !preg_match(
                    '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
                    $horario
                )
            ) {
                throw new InvalidArgumentException(
                    'Existe um horario invalido.'
                );
            }
        }

        if (count(array_unique($horarios)) !== count($horarios)) {
            throw new InvalidArgumentException(
                'Nao e permitido repetir horarios.'
            );
        }

        sort($horarios);

        $config = [
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'horario' => $horarios[0],
            'quantidade' => $quantidade,
            'horarios_personalizados' => $horarios,
            'destino_secundario' => trim(
                (string) ($dados['destino_secundario'] ?? '')
            ),
        ];

        $caminho = $this->caminho();

        File::ensureDirectoryExists(dirname($caminho));

        $temporario = $caminho . '.tmp';

        $json = json_encode(
            $config,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES |
            JSON_THROW_ON_ERROR
        );

        if (file_put_contents($temporario, $json, LOCK_EX) === false) {
            throw new \RuntimeException(
                'Falha ao gravar configuracao.'
            );
        }

        // Publicar sem remover antecipadamente a configuracao valida.
        if (!@rename($temporario, $caminho)) {
            if (is_file($temporario)) {
                @unlink($temporario);
            }

            throw new \RuntimeException(
                'Nao foi possivel publicar configuracao.'
            );
        }
    }

    public function horarios(): array
    {
        $config = $this->obter();

        $personalizados = $config['horarios_personalizados'] ?? [];

        if (
            is_array($personalizados) &&
            count($personalizados) === (int) $config['quantidade'] &&
            count($personalizados) >= 1
        ) {
            $validos = array_filter(
                $personalizados,
                fn ($h) => is_string($h) &&
                    preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $h)
            );

            if (
                count($validos) === count($personalizados) &&
                count(array_unique($validos)) === count($validos)
            ) {
                sort($validos);
                return array_values($validos);
            }
        }

        // Compatibilidade com a configuracao da primeira versao.
        $quantidade = max(
            1,
            min(5, (int) ($config['quantidade'] ?? 1))
        );

        $horario = $config['horario'] ?? '22:00';

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horario)) {
            $horario = '22:00';
        }

        [$hora, $minuto] = array_map(
            'intval',
            explode(':', $horario)
        );

        $inicio = $hora * 60 + $minuto;
        $resultado = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $total = (
                $inicio + intdiv($i * 1440, $quantidade)
            ) % 1440;

            $resultado[] = sprintf(
                '%02d:%02d',
                intdiv($total, 60),
                $total % 60
            );
        }

        sort($resultado);

        return $resultado;
    }
}