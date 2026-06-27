<?php

namespace App\Services\Backup;

use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupRestoreService
{
    public function restaurar(string $arquivo): bool
    {
        $arquivo = basename($arquivo);

        $zipPath = $this->localizarZipBackup($arquivo);

        $pastaRestore = storage_path(
            'app/backups/temp_restore/' . pathinfo($arquivo, PATHINFO_FILENAME)
        );

        File::deleteDirectory($pastaRestore);
        File::ensureDirectoryExists($pastaRestore);

        try {
            $this->extrairZip($zipPath, $pastaRestore);

            $sqlFile = $this->localizarArquivoSql($pastaRestore);

            $this->restaurarBanco($sqlFile);

            return true;
        } finally {
            File::deleteDirectory($pastaRestore);
        }
    }

    private function localizarZipBackup(string $arquivo): string
    {
        $pastaBackups = storage_path('app/backups/temp_zip');

        $possiveis = [
            $pastaBackups . DIRECTORY_SEPARATOR . $arquivo,
            $pastaBackups . DIRECTORY_SEPARATOR . pathinfo($arquivo, PATHINFO_FILENAME),
            $pastaBackups . DIRECTORY_SEPARATOR . pathinfo($arquivo, PATHINFO_FILENAME) . '.zip',
        ];

        foreach ($possiveis as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception(
            'Arquivo de backup não encontrado na pasta física: ' .
            $pastaBackups .
            ' | Nome recebido: ' . $arquivo
        );
    }

    private function extrairZip(string $zipPath, string $destino): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new Exception('Não foi possível abrir o arquivo ZIP do backup.');
        }

        $zip->extractTo($destino);
        $zip->close();
    }

    private function localizarArquivoSql(string $pastaRestore): string
    {
        foreach (File::allFiles($pastaRestore) as $arquivo) {
            if (strtolower($arquivo->getExtension()) === 'sql') {
                return $arquivo->getRealPath();
            }
        }

        throw new Exception('Arquivo SQL não encontrado dentro do backup.');
    }

    private function restaurarBanco(string $sqlFile): void
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');

        if ($host === 'localhost') {
            $host = '127.0.0.1';
        }

        if (! file_exists($sqlFile) || filesize($sqlFile) <= 0) {
            throw new Exception('Arquivo SQL vazio ou inválido.');
        }

        $mysql = $this->localizarMysql();

        $command = [
            $mysql,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
        ];

        if (! empty($password)) {
            $command[] = '--password=' . $password;
        }

        $command[] = $database;

        $sql = file_get_contents($sqlFile);

        if ($sql === false) {
            throw new Exception('Não foi possível ler o arquivo SQL.');
        }

        $process = new Process($command);

        if (PHP_OS_FAMILY === 'Windows') {
            $process->setEnv([
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
                'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
                'PATH'       => getenv('PATH'),
            ]);
        }

        $process->setInput($sql);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception(
                'Erro ao restaurar banco: ' .
                $this->limparTexto($process->getErrorOutput() ?: $process->getOutput())
            );
        }
    }

    private function localizarMysql(): string
    {
        $mysqlPath = config('backup.mysql_path', 'mysql');

        if ($mysqlPath === 'mysql') {
            return PHP_OS_FAMILY === 'Windows' ? 'mysql.exe' : 'mysql';
        }

        if (file_exists($mysqlPath)) {
            return $mysqlPath;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            foreach ([
                'C:/xampp/mysql/bin/mysql.exe',
                'C:/laragon/bin/mysql/mysql-8.0/bin/mysql.exe',
                'C:/laragon/bin/mysql/mysql-5.7/bin/mysql.exe',
            ] as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        throw new Exception("mysql não encontrado em: {$mysqlPath}");
    }

    private function limparTexto(?string $texto): string
    {
        if (empty($texto)) {
            return '';
        }

        return mb_convert_encoding(
            $texto,
            'UTF-8',
            'UTF-8, ISO-8859-1, Windows-1252, CP850'
        );
    }
}