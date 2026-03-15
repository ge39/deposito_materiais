<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteHistoricoCredito;
use App\Models\PagamentoVenda;

class HistoricoCreditoService
{
    public function registrarCredito(Cliente $cliente, PagamentoVenda $pagamento): void
    {
        ClienteHistoricoCredito::create([
            'cliente_id' => $cliente->id,
            'pagamento_venda_id' => $pagamento->id,
            'valor' => $pagamento->valor,
            'tipo' => 'credito',
            'descricao' => 'Pagamento de carteira',
        ]);
    }

    public function registrarDebito(Cliente $cliente, PagamentoVenda $pagamento): void
    {
        ClienteHistoricoCredito::create([
            'cliente_id' => $cliente->id,
            'pagamento_venda_id' => $pagamento->id,
            'valor' => $pagamento->valor,
            'tipo' => 'debito',
            'descricao' => 'Venda em carteira',
        ]);
    }
}