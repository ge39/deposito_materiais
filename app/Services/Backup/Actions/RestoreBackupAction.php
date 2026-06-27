<?php

namespace App\Services\Backup\Actions;

use App\Services\Backup\BackupRestoreService;
use App\Services\Logging\BackupLogService;
use App\Services\Logging\LogActions;

class RestoreBackupAction
{
    public function __construct(
        protected BackupRestoreService $restoreService,
        protected BackupLogService $logService
    ) {}

   public function execute(string $arquivo): bool
{
    $arquivo = basename($arquivo);

    $log = $this->logService->iniciar(
        LogActions::BACKUP_RESTAURAR,
        $arquivo
    );

    try {
        $resultado = $this->restoreService->restaurar($arquivo);

        $pastaBackups = storage_path('app/backups/temp_zip');

        $possiveis = [
            $pastaBackups . DIRECTORY_SEPARATOR . $arquivo,
            $pastaBackups . DIRECTORY_SEPARATOR . pathinfo($arquivo, PATHINFO_FILENAME),
            $pastaBackups . DIRECTORY_SEPARATOR . pathinfo($arquivo, PATHINFO_FILENAME) . '.zip',
        ];

        $tamanho = 0;

        foreach ($possiveis as $path) {
            if (file_exists($path)) {
                $tamanho = filesize($path);
                break;
            }
        }

        $this->logService->sucesso(
            $log,
            $arquivo,
            $tamanho,
            'Backup restaurado com sucesso.'
        );

        return $resultado;

    } catch (\Throwable $e) {
        $this->logService->erro($log, $e);
        throw $e;
    }
}
}