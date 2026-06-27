<?php

namespace App\Services\Backup\Drivers;

use App\Services\Backup\Contracts\BackupDriverInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LocalBackupDriver implements BackupDriverInterface
{
    public function salvar(string $arquivoLocal): string
    {
        $nomeArquivo = basename($arquivoLocal);

        Storage::disk('local')->put(
            "backups/{$nomeArquivo}",
            File::get($arquivoLocal)
        );

        return $nomeArquivo;
    }

    public function listar(): array
    {
        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn ($arquivo) => str_ends_with($arquivo, '.zip'))
            ->sortDesc()
            ->values()
            ->toArray();
    }

   public function baixar(string $nomeArquivo): string
    {
        $nomeArquivo = basename($nomeArquivo);

        $disk = config('backup.disk', 'local');

        $path = 'backups/' . $nomeArquivo;

        if (! Storage::disk($disk)->exists($path)) {
            throw new \Exception('Arquivo de backup não encontrado para download.');
        }

        return Storage::disk($disk)->path($path);
    }

    public function excluir(string $nomeArquivo): bool
    {
        $path = 'backups/' . basename($nomeArquivo);

        if (! Storage::disk('local')->exists($path)) {
            return false;
        }

        return Storage::disk('local')->delete($path);
    }
}