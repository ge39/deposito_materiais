<?php

namespace App\Services\Backup;

use App\Services\Backup\Contracts\BackupDriverInterface;
use Illuminate\Support\Facades\File;

class BackupService
{
    public function __construct(
        protected BackupDatabaseService $databaseService,
        protected BackupFilesService $filesService,
        protected BackupZipService $zipService,
        protected BackupDriverInterface $driver
    ) {}

    public function gerarBackupCompleto(): string
    {
        $nomeBase = 'backup_' . now()->format('Y-m-d_H-i-s');

        $pastaTemporaria = storage_path("app/backups/temp/{$nomeBase}");

        File::ensureDirectoryExists($pastaTemporaria);

        try {
            $sqlPath = $this->databaseService->gerarDump($nomeBase, $pastaTemporaria);

            $arquivosPath = $this->filesService->prepararArquivos($pastaTemporaria);

            $zipPath = $this->zipService->compactar($nomeBase, $sqlPath, $arquivosPath);

            return $this->driver->salvar($zipPath);
        } finally {
            File::deleteDirectory($pastaTemporaria);
        }
    }
}