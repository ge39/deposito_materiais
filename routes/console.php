<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // 1. Importe a fachada aqui

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 2. Chame o agendamento usando a fachada Schedule::
Schedule::command('credito:verificar-atrasos')->dailyAt('02:00');
