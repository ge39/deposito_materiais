<?php

namespace App\Services\Backup;

use App\Services\Backup\Contracts\BackupDriverInterface;
use Carbon\Carbon;

class BackupCleanupService
{
    public function __construct(
        protected BackupDriverInterface $driver
    ) {}

    public function limparAntigos(): int
    {
        $retentionDays = (int) config('backup.retention_days', 30);

        $arquivos = $this->driver->listar();

        $removidos = 0;

        foreach ($arquivos as $arquivo) {
            $nomeArquivo = basename($arquivo);

            if (! preg_match('/backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.zip$/', $nomeArquivo, $matches)) {
                continue;
            }

            $dataBackup = Carbon::createFromFormat(
                'Y-m-d H-i-s',
                "{$matches[1]} {$matches[2]}"
            );

            if ($dataBackup->lt(now()->subDays($retentionDays))) {
                if ($this->driver->excluir($nomeArquivo)) {
                    $removidos++;
                }
            }
        }

        return $removidos;
    }
}