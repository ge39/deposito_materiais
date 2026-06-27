<?php

namespace App\Services\Backup\Actions;

use App\Services\Backup\Contracts\BackupDriverInterface;
use App\Services\Logging\BackupLogService;
use App\Services\Logging\LogActions;

class DownloadBackupAction
{
    public function __construct(
        protected BackupDriverInterface $driver,
        protected BackupLogService $logService
    ) {}

    public function execute(string $arquivo): string
    {
        $arquivo = basename($arquivo);

        $log = $this->logService->iniciar(
            LogActions::BACKUP_DOWNLOAD,
            $arquivo
        );

        try {
            $path = $this->driver->baixar($arquivo);

            if (! file_exists($path)) {
                throw new \Exception('Arquivo de backup não encontrado para download.');
            }

            $this->logService->sucesso(
                $log,
                $arquivo,
                filesize($path),
                'Backup baixado com sucesso.'
            );

            return $path;

        } catch (\Throwable $e) {
            $this->logService->erro($log, $e);
            throw $e;
        }
    }
}