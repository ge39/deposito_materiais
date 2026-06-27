<?php

namespace App\Services\Backup;

use Exception;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupZipService
{
    public function compactar(string $nomeBase, string $sqlPath, string $arquivosPath): string
    {
        $pastaTempZip = storage_path('app/backups/temp_zip');

        File::ensureDirectoryExists($pastaTempZip);

        $zipPath = "{$pastaTempZip}/{$nomeBase}.zip";

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Não foi possível criar o arquivo ZIP do backup.');
        }

        if (File::exists($sqlPath)) {
            $zip->addFile($sqlPath, 'database/' . basename($sqlPath));
        }

        if (File::exists($arquivosPath)) {
            $this->adicionarPastaAoZip($zip, $arquivosPath, 'arquivos');
        }

        $zip->close();

        return $zipPath;
    }

    private function adicionarPastaAoZip(ZipArchive $zip, string $pasta, string $prefixo): void
    {
        foreach (File::allFiles($pasta) as $arquivo) {
            $caminhoCompleto = $arquivo->getRealPath();

            $caminhoRelativo = $prefixo . '/' . str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $arquivo->getRelativePathname()
            );

            $zip->addFile($caminhoCompleto, $caminhoRelativo);
        }
    }
}