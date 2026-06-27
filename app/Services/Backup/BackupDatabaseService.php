<?php

namespace App\Services\Backup;

use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabaseService
{
    public function gerarDump(string $nomeBase, string $pastaTemporaria): string
{
    $database = config('database.connections.mysql.database');
    $username = config('database.connections.mysql.username');
    $password = config('database.connections.mysql.password');
    $host = config('database.connections.mysql.host', '127.0.0.1');
    $port = config('database.connections.mysql.port', '3306');

    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    $arquivoSql = "{$pastaTemporaria}/{$nomeBase}.sql";

    File::ensureDirectoryExists($pastaTemporaria);

    $mysqldump = $this->localizarMysqlDump();

    $comando = [
        $mysqldump,
        '--host=' . $host,
        '--port=' . $port,
        '--user=' . $username,
        '--single-transaction',
        '--routines',
        '--triggers',
        '--default-character-set=utf8mb4',
    ];

    if (!empty($password)) {
        $comando[] = '--password=' . $password;
    }

    $comando[] = $database;

    $process = new Process($comando);
    $process = new Process($comando);

    if (PHP_OS_FAMILY === 'Windows') {
        $process->setEnv([
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH'       => getenv('PATH'),
        ]);
    }

    $process->setTimeout(300);
    $process->run();

    $saida = $process->getOutput();

    if (trim($saida) === '') {
        throw new Exception('mysqldump executou, mas não retornou conteúdo SQL.');
    }

    File::put($arquivoSql, $saida);

    if (! file_exists($arquivoSql) || filesize($arquivoSql) <= 0) {
        throw new Exception('Falha ao gravar arquivo SQL do backup.');
    }

    return $arquivoSql;
}

    private function localizarMysqlDump(): string
    {
        $pathConfig = config('backup.mysql_dump', 'mysqldump');

        if ($pathConfig === 'mysqldump') {
            return 'mysqldump';
        }

        if (file_exists($pathConfig)) {
            return $pathConfig;
        }

        throw new Exception("mysqldump não encontrado em: {$pathConfig}");
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