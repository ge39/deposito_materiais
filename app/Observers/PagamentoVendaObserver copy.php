<?php
namespace App\Observers;

use App\Models\PagamentoVenda;
use App\Services\BloqueioCreditoService;
use App\Services\ContaCorrenteService;

class PagamentoVendaObserver
{
    public function saved(PagamentoVenda $pagamento): void
    {
        $cliente = $pagamento->venda->cliente;

        app(ContaCorrenteService::class)
            ->registrarMovimentacao($pagamento);

        app(BloqueioCreditoService::class)
            ->reavaliarCliente($cliente);
    }

    public function deleted(PagamentoVenda $pagamento): void
    {
        $cliente = $pagamento->venda->cliente;

        app(BloqueioCreditoService::class)
            ->reavaliarCliente($cliente);
    }
}