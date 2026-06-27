<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class GerarBackupAutomatico extends Command
{
    protected $signature = 'backup:automatico';

    protected $description = 'Gera backup automático do banco de dados';

    public function handle(BackupService $backupService): int
    {
        try {
            $backup = $backupService->gerar('automatico', null);

            $this->info("Backup automático gerado com sucesso: {$backup->nome_arquivo}");

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Falha ao gerar backup automático: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}