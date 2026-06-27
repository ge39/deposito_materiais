<?php

namespace App\Services\Backup\Actions;

use App\Services\Backup\Contracts\BackupDriverInterface;
use App\Services\Logging\BackupLogService;
use App\Services\Logging\LogActions;

class DeleteBackupAction
{
    public function __construct(
        protected BackupDriverInterface $driver,
        protected BackupLogService $logService
    ) {}

    public function execute(string $arquivo): bool
    {
        $arquivo = basename($arquivo);

        $log = $this->logService->iniciar(
            LogActions::BACKUP_EXCLUIR,
            $arquivo
        );

        try {
            $removido = $this->driver->excluir($arquivo);

            if (! $removido) {
                throw new \Exception('Backup não encontrado ou não pôde ser excluído.');
            }

            $this->logService->sucesso(
                $log,
                $arquivo,
                0,
                'Backup excluído com sucesso.'
            );

            return true;

        } catch (\Throwable $e) {
            $this->logService->erro($log, $e);
            throw $e;
        }
    }
}