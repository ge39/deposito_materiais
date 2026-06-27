<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;

class BackupFilesService
{
    public function prepararArquivos(string $pastaTemporaria): string
    {
        $destino = "{$pastaTemporaria}/arquivos";

        File::ensureDirectoryExists($destino);

        $pastasParaBackup = [
            public_path('image'),
            public_path('produtos'),
            public_path('devolucoes'),
            public_path('uploads'),
        ];

        foreach ($pastasParaBackup as $pasta) {
            if (File::exists($pasta)) {
                File::copyDirectory($pasta, $destino . '/' . basename($pasta));
            }
        }

        return $destino;
    }
}