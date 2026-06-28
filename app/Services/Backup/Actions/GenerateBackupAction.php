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

    // public function execute(): string
    // {
    //     $log = $this->logService->iniciar(LogActions::BACKUP_GERAR);

    //     try {
    //         $arquivo = $this->backupService->gerarBackupCompleto();

    //         $path = storage_path('app/backups/' . basename($arquivo));
    //         $tamanho = file_exists($path) ? filesize($path) : 0;

    //         $this->logService->sucesso(
    //             $log,
    //             basename($arquivo),
    //             $tamanho,
    //             'Backup gerado com sucesso.'
    //         );

    //         return $arquivo;

    //     } catch (\Throwable $e) {
    //         $this->logService->erro($log, $e);
    //         throw $e;
    //     }
    // }
    public function execute(): string
    {
        $log = $this->logService->iniciar(LogActions::BACKUP_GERAR);

        try {
            $arquivo = $this->backupService->gerarBackupCompleto();

            $nomeArquivo = basename($arquivo);

            $possiveisPaths = [
                storage_path('app/private/backups/' . $nomeArquivo),
                storage_path('app/backups/' . $nomeArquivo),
                storage_path('app/public/backups/' . $nomeArquivo),
                $arquivo,
            ];

            $pathReal = null;

            foreach ($possiveisPaths as $path) {
                if (is_string($path) && file_exists($path)) {
                    $pathReal = $path;
                    break;
                }
            }

            $tamanho = $pathReal ? filesize($pathReal) : 0;

            $this->logService->sucesso(
                $log,
                $nomeArquivo,
                $tamanho,
                'Backup gerado com sucesso.',
                [
                    'path_real_backup' => $pathReal,
                    'paths_testados' => $possiveisPaths,
                ]
            );

            return $nomeArquivo;

        } catch (\Throwable $e) {
            $this->logService->erro($log, $e);
            throw $e;
        }
    }
}