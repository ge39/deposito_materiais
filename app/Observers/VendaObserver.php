<?php

namespace App\Observers;

use App\Models\Venda;
use App\Services\BloqueioCreditoService;

class VendaObserver
{
    public function saved(Venda $venda): void
    {
        $cliente = $venda->cliente;

        app(BloqueioCreditoService::class)
            ->reavaliarCliente($cliente);
    }

    public function deleted(Venda $venda): void
    {
        $cliente = $venda->cliente;

        app(BloqueioCreditoService::class)
            ->reavaliarCliente($cliente);
    }
}