<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =========================
// CRÉDITO
// =========================
Schedule::command('credito:verificar-atrasos')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// =========================
// BACKUP AUTOMÁTICO
// =========================
// ============================================================
// BACKUP AUTOMATICO - HORARIOS CONTROLADOS PELO ERP
// ============================================================

// Ler os horarios configurados pelo administrador.
$backupSettings = app(
    \App\Services\Backup\BackupAutomationSettings::class
);

$backupConfig = $backupSettings->obter();

if (!empty($backupConfig['ativo'])) {
    foreach ($backupSettings->horarios() as $horarioBackup) {
        Schedule::command('backup:automatico')
            ->dailyAt($horarioBackup)
            ->withoutOverlapping(10);
    }

    // Recuperacao de horarios perdidos:
    // o proprio comando verifica se existe execucao vencida.
    Schedule::command('backup:automatico')
        ->everyFifteenMinutes()
        ->withoutOverlapping(10);
}