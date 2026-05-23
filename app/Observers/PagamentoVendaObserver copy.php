<?php
namespace App\Observers;

use App\Models\PagamentoVenda;
use App\Services\BloqueioCreditoService;
use App\Services\ContaCorrenteService;
use Illuminate\Support\Facades\Log;

class PagamentoVendaObserver
{
    public function saved(PagamentoVenda $pagamento): void
    {
        $venda = $pagamento->venda;

        if (!$venda) {
            Log::warning("PagamentoVendaObserver: venda não encontrada para pagamento ID {$pagamento->id}");
            return; // evita continuar se venda não existir
        }

        $cliente = $venda->cliente;

        if (!$cliente) {
            Log::warning("PagamentoVendaObserver: cliente não encontrado para venda ID {$venda->id}");
            return;
        }

        // Registrar movimentação na conta corrente com proteção de erro
        try {
            app(ContaCorrenteService::class)->registrarMovimentacao($pagamento);
        } catch (\Throwable $e) {
            Log::error("PagamentoVendaObserver: erro ao registrar movimentação para pagamento ID {$pagamento->id} - {$e->getMessage()}");
        }

        // Reavaliar cliente com proteção de erro
        try {
            app(BloqueioCreditoService::class)->reavaliarCliente($cliente);
        } catch (\Throwable $e) {
            Log::error("PagamentoVendaObserver: erro ao reavaliar cliente ID {$cliente->id} - {$e->getMessage()}");
        }
    }

    public function deleted(PagamentoVenda $pagamento): void
    {
        $venda = $pagamento->venda;

        if (!$venda) {
            Log::warning("PagamentoVendaObserver: venda não encontrada ao deletar pagamento ID {$pagamento->id}");
            return;
        }

        $cliente = $venda->cliente;

        if (!$cliente) {
            Log::warning("PagamentoVendaObserver: cliente não encontrado ao deletar pagamento ID {$pagamento->id}");
            return;
        }

        try {
            app(BloqueioCreditoService::class)->reavaliarCliente($cliente);
        } catch (\Throwable $e) {
            Log::error("PagamentoVendaObserver: erro ao reavaliar cliente ID {$cliente->id} ao deletar pagamento - {$e->getMessage()}");
        }
    }
}