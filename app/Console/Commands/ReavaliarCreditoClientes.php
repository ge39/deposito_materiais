<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Services\BloqueioCreditoService;

class ReavaliarCreditoClientes extends Command
{
    protected $signature = 'credito:reavaliar';

    protected $description = 'Reavalia crédito de todos os clientes automaticamente';

    public function handle(): int
    {
        $this->info('Iniciando reavaliação de crédito...');

        $bloqueioService = app(BloqueioCreditoService::class);

        Cliente::where('limite_credito', '>', 0)
            ->chunk(100, function ($clientes) use ($bloqueioService) {

                foreach ($clientes as $cliente) {
                    $bloqueioService->reavaliarCliente($cliente);
                }

            });

        $this->info('Reavaliação finalizada.');

        return Command::SUCCESS;
    }
}