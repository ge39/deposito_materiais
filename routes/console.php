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
Schedule::command('backup:automatico')
    ->dailyAt('22:00')
    ->withoutOverlapping()
    ->runInBackground();