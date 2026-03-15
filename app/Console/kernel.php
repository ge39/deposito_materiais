<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Comandos Artisan registrados.
     */
    protected $commands = [
        \App\Console\Commands\RestaurarPromocoes::class,
        \App\Console\Commands\ReavaliarCreditoClientes::class,
    ];

    /**
     * Definição do agendamento de tarefas.
     */
    protected function schedule(Schedule $schedule)
    {
        // Roda o comando de restaurar promoções a cada hora
        $schedule->command('promocoes:restaurar')
            ->hourly()
            ->withoutOverlapping();

        // Roda o comando de reavaliar crédito diariamente às 02:00
        $schedule->command('credito:reavaliar')
            ->dailyAt('02:00')
            ->withoutOverlapping();
    }

    /**
     * Registro de comandos para console.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}