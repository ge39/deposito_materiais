<?php
namespace App\Services;

use App\Models\ClienteContaCorrente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;

class ContaCorrenteService
{
    public function registrarMovimentacao(PagamentoVenda $pagamento): void
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return;
        }

        $cliente = $pagamento->venda->cliente;

        DB::transaction(function () use ($cliente, $pagamento) {

            $ultimoSaldo = ClienteContaCorrente::where('cliente_id', $cliente->id)
                ->lockForUpdate()
                ->latest('id')
                ->value('saldo_apos') ?? 0;

            // Débito quando cria pendente
            if ($pagamento->status === 'pendente') {

                $novoSaldo = $ultimoSaldo + $pagamento->valor;

                ClienteContaCorrente::create([
                    'cliente_id' => $cliente->id,
                    'venda_id' => $pagamento->venda_id,
                    'pagamento_venda_id' => $pagamento->id,
                    'tipo' => 'debito',
                    'origem' => 'venda',
                    'valor' => $pagamento->valor,
                    'saldo_apos' => $novoSaldo,
                    'descricao' => 'Venda em carteira'
                ]);
            }

            // Crédito quando confirmar pagamento
            if ($pagamento->status === 'confirmado') {

                $novoSaldo = $ultimoSaldo - $pagamento->valor;

                ClienteContaCorrente::create([
                    'cliente_id' => $cliente->id,
                    'venda_id' => $pagamento->venda_id,
                    'pagamento_venda_id' => $pagamento->id,
                    'tipo' => 'credito',
                    'origem' => 'pagamento',
                    'valor' => $pagamento->valor,
                    'saldo_apos' => $novoSaldo,
                    'descricao' => 'Pagamento de carteira'
                ]);
            }
        });
    }

    public function saldoAtual(int $clienteId): float
    {
        return ClienteContaCorrente::where('cliente_id', $clienteId)
            ->latest('id')
            ->value('saldo_apos') ?? 0;
    }
}