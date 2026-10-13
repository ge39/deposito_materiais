<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class BackupAutomaticoProtecaoService
{
    private const PASTA_SECUNDARIA = 'deposito_materiais_backups';

    private function controle(): string
    {
        return storage_path('app/private/backup_scheduler');
    }

    public function pastaPrincipal(): string
    {
        $disk = Storage::disk(config('backup.disk', 'local'));

        return $disk->path('backups');
    }

    private function nomeValido(string $nome): bool
    {
        return (bool) preg_match(
            '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.zip$/',
            $nome
        );
    }

    public function localizarPrincipal(string $nome): string
    {
        if (!$this->nomeValido($nome)) {
            throw new RuntimeException('Nome de backup invalido.');
        }

        $candidatos = array_unique([
            $this->pastaPrincipal() . DIRECTORY_SEPARATOR . $nome,
            storage_path('app/private/backups/' . $nome),
            storage_path('app/backups/' . $nome),
        ]);

        foreach ($candidatos as $arquivo) {
            if (is_file($arquivo)) {
                return $arquivo;
            }
        }

        throw new RuntimeException(
            'ZIP principal nao localizado: ' . $nome
        );
    }

    public function validarZip(string $arquivo): void
    {
        if (!is_file($arquivo) || filesize($arquivo) <= 0) {
            throw new RuntimeException('Backup ausente ou vazio.');
        }

        $zip = new ZipArchive();

        if ($zip->open($arquivo, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('Estrutura ZIP invalida.');
        }

        try {
            $sqlEncontrado = false;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $nome = $zip->getNameIndex($i);

                if (
                    str_starts_with($nome, 'database/') &&
                    str_ends_with(strtolower($nome), '.sql')
                ) {
                    $sqlEncontrado = true;
                }
            }

            if (!$sqlEncontrado) {
                throw new RuntimeException(
                    'Backup ZIP sem arquivo SQL.'
                );
            }
        } finally {
            $zip->close();
        }
    }

    private function destinoSecundario(array $config): ?string
    {
        $base = trim(
            (string) ($config['destino_secundario'] ?? '')
        );

        if ($base === '') {
            return null;
        }

        $real = realpath($base);

        if ($real === false || !is_dir($real) || !is_writable($real)) {
            throw new RuntimeException(
                'Diretorio secundario indisponivel ou sem escrita.'
            );
        }

        // Nao permitir que o secundario seja o armazenamento principal
        // nem um diretorio ancestral do projeto.
        $principal = realpath($this->pastaPrincipal());

        if ($principal !== false) {
            $normalizar = fn ($p) => strtolower(
                rtrim(str_replace('\\', '/', $p), '/')
            );

            $dest = $normalizar($real);
            $origem = $normalizar($principal);

            if (
                $dest === $origem ||
                str_starts_with($origem . '/', $dest . '/')
            ) {
                throw new RuntimeException(
                    'O destino secundario nao pode conter a pasta principal.'
                );
            }
        }

        $destino = $real . DIRECTORY_SEPARATOR .
            self::PASTA_SECUNDARIA;

        if (!is_dir($destino) && !mkdir($destino, 0750, true)) {
            throw new RuntimeException(
                'Nao foi possivel criar a pasta secundaria.'
            );
        }

        return $destino;
    }

    public function copiar(string $nome, array $config): string
    {
        $origem = $this->localizarPrincipal($nome);

        $this->validarZip($origem);

        $destinoPasta = $this->destinoSecundario($config);

        if ($destinoPasta === null) {
            return 'NAO_CONFIGURADO';
        }

        $destino = $destinoPasta . DIRECTORY_SEPARATOR . $nome;

        $origemReal = realpath($origem);
        $destinoReal = is_file($destino) ? realpath($destino) : false;

        if (
            $origemReal !== false &&
            $destinoReal !== false &&
            $origemReal === $destinoReal
        ) {
            throw new RuntimeException(
                'Origem e destino sao o mesmo arquivo.'
            );
        }

        $hashOriginal = hash_file('sha256', $origem);

        if (is_file($destino)) {
            if (
                hash_file('sha256', $destino) === $hashOriginal
            ) {
                return 'VALIDADO';
            }

            // Nunca substituir silenciosamente uma copia divergente.
            throw new RuntimeException(
                'Existe uma copia secundaria com conteudo divergente.'
            );
        }

        $temporario = $destino . '.part';

        try {
            if (is_file($temporario)) {
                throw new RuntimeException(
                    'Existe uma transferencia temporaria pendente.'
                );
            }

            if (!copy($origem, $temporario)) {
                throw new RuntimeException(
                    'Falha ao copiar backup para o segundo destino.'
                );
            }

            if (hash_file('sha256', $temporario) !== $hashOriginal) {
                throw new RuntimeException(
                    'Hash da copia secundaria nao confere.'
                );
            }

            $this->validarZip($temporario);

            if (!rename($temporario, $destino)) {
                throw new RuntimeException(
                    'Falha ao publicar copia secundaria.'
                );
            }

            Log::info('BACKUP_SECUNDARIO_VALIDADO', [
                'arquivo' => $nome,
                'sha256' => $hashOriginal,
                'destino' => $destino,
            ]);

            return 'VALIDADO';

        } finally {
            if (is_file($temporario)) {
                @unlink($temporario);
            }
        }
    }

    public function salvarPendente(
        string $nome,
        string $horario
    ): void {
        if (!$this->nomeValido($nome)) {
            throw new RuntimeException('Arquivo invalido.');
        }

        $pasta = $this->controle() . DIRECTORY_SEPARATOR .
            'pendentes';

        if (!is_dir($pasta)) {
            mkdir($pasta, 0750, true);
        }

        $dados = [
            'arquivo' => $nome,
            'horario' => $horario,
            'criado_em' => now()->toDateTimeString(),
        ];

        file_put_contents(
            $pasta . DIRECTORY_SEPARATOR . $nome . '.json',
            json_encode($dados, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            LOCK_EX
        );
    }

    public function processarPendentes(array $config): void
    {
        $pasta = $this->controle() . DIRECTORY_SEPARATOR .
            'pendentes';

        if (!is_dir($pasta)) {
            return;
        }

        $arquivos = glob($pasta . DIRECTORY_SEPARATOR . '*.json')
            ?: [];

        sort($arquivos);

        foreach ($arquivos as $controle) {
            $dados = json_decode(
                (string) file_get_contents($controle),
                true
            );

            $nome = $dados['arquivo'] ?? '';

            if (!is_string($nome) || !$this->nomeValido($nome)) {
                Log::warning('BACKUP_PENDENCIA_INVALIDA', [
                    'controle' => basename($controle),
                ]);

                continue;
            }

            try {
                $status = $this->copiar($nome, $config);

                if ($status === 'VALIDADO') {
                    unlink($controle);
                }

                if ($status === 'NAO_CONFIGURADO') {
                    break;
                }

            } catch (\Throwable $e) {
                Log::warning('BACKUP_COPIA_PENDENTE', [
                    'arquivo' => $nome,
                    'erro' => $e->getMessage(),
                ]);

                // Nao impedir que outros servicos do ERP continuem.
                break;
            }
        }
    }

    private function arquivosGerenciados(string $pasta): array
    {
        if (!is_dir($pasta)) {
            return [];
        }

        $arquivos = [];

        foreach (glob($pasta . DIRECTORY_SEPARATOR . 'backup_*.zip') ?: [] as $arquivo) {
            if ($this->nomeValido(basename($arquivo))) {
                $arquivos[] = $arquivo;
            }
        }

        return $arquivos;
    }

    public function limpar30Dias(array $config): array
    {
        $limite = now()->subDays(30)->timestamp;
        $resultado = ['principal' => 0, 'secundario' => 0];

        $pastas = [
            'principal' => $this->pastaPrincipal(),
        ];

        // Tratar falha externa sem impedir limpeza do principal.
        try {
            $secundario = $this->destinoSecundario($config);

            if ($secundario !== null) {
                $pastas['secundario'] = $secundario;
            }
        } catch (\Throwable $e) {
            Log::warning('BACKUP_LIMPEZA_SECUNDARIA_ADIADA', [
                'erro' => $e->getMessage(),
            ]);
        }

        foreach ($pastas as $tipo => $pasta) {
            $arquivos = $this->arquivosGerenciados($pasta);

            // Nunca eliminar a ultima copia ZIP estruturalmente valida.
            $validos = [];

            foreach ($arquivos as $arquivo) {
                try {
                    $this->validarZip($arquivo);
                    $validos[] = $arquivo;
                } catch (\Throwable $e) {
                    // Arquivo invalido nao pode proteger exclusoes.
                }
            }

            usort(
                $validos,
                fn ($a, $b) => strcmp(basename($b), basename($a))
            );

            $protegido = $validos[0] ?? null;

            foreach ($arquivos as $arquivo) {
                if ($arquivo === $protegido) {
                    continue;
                }

                $nome = basename($arquivo);

                if (!preg_match(
                    '/^backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.zip$/',
                    $nome,
                    $m
                )) {
                    continue;
                }

                $data = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d H-i-s',
                    $m[1] . ' ' . $m[2]
                );

                if (!$data || $data->getTimestamp() >= $limite) {
                    continue;
                }

                // Backups com copia secundaria ainda pendente
                // nao devem ser removidos do principal.
                $pendente = $this->controle() .
                    DIRECTORY_SEPARATOR . 'pendentes' .
                    DIRECTORY_SEPARATOR . $nome . '.json';

                if ($tipo === 'principal' && is_file($pendente)) {
                    continue;
                }

                if (@unlink($arquivo)) {
                    $resultado[$tipo]++;

                    Log::info('BACKUP_RETENCAO_EXCLUIDO', [
                        'tipo' => $tipo,
                        'arquivo' => $nome,
                    ]);
                }
            }
        }

        return $resultado;
    }
}