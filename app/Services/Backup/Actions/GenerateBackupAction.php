<?php

namespace App\Services\Backup\Actions;

use App\Services\Backup\BackupService;
use App\Services\Logging\BackupLogService;
use App\Services\Logging\LogActions;

class GenerateBackupAction
{
    public function __construct(
        protected BackupService $backupService,
        protected BackupLogService $logService
    ) {}

    public function execute(): string
    {
        $log = $this->logService->iniciar(LogActions::BACKUP_GERAR);

        try {
            $arquivo = $this->backupService->gerarBackupCompleto();

            $path = storage_path('app/backups/' . basename($arquivo));
            $tamanho = file_exists($path) ? filesize($path) : 0;

            $this->logService->sucesso(
                $log,
                basename($arquivo),
                $tamanho,
                'Backup gerado com sucesso.'
            );

            return $arquivo;

        } catch (\Throwable $e) {
            $this->logService->erro($log, $e);
            throw $e;
        }
    }
}