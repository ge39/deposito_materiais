<?php

namespace App\Services;

use App\Models\ClienteContaCorrente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Cliente;

class ContaCorrenteService
{
    // public function registrarMovimentacao(PagamentoVenda $pagamento): void
    // {
    //     if ($pagamento->forma_pagamento !== 'carteira') {
    //         return;
    //     }

    //     $cliente = $pagamento->venda->cliente;

    //     DB::transaction(function () use ($cliente, $pagamento) {

    //         $ultimoSaldo = ClienteContaCorrente::where('cliente_id', $cliente->id)
    //             ->lockForUpdate()
    //             ->latest('id')
    //             ->value('saldo_apos');

    //         // 🧠 usa limite como saldo inicial
    //         if (is_null($ultimoSaldo)) {
    //             $ultimoSaldo = $cliente->limite_credito ?? 0;
    //         }

    //         $novoSaldo = $ultimoSaldo - $pagamento->valor;

    //         if ($novoSaldo < 0) {
    //             throw new \Exception('Saldo insuficiente na carteira');
    //         }

    //         ClienteContaCorrente::create([
    //             'cliente_id' => $cliente->id,
    //             'venda_id' => $pagamento->venda_id,
    //             'pagamento_venda_id' => $pagamento->id,
    //             'tipo' => 'debito',
    //             'origem' => 'venda',
    //             'valor' => $pagamento->valor,
    //             'saldo_apos' => $novoSaldo,
    //             'descricao' => 'Pagamento via carteira'
    //         ]);
    //     });
    // }
    public function registrarMovimentacao(PagamentoVenda $pagamento): void
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return;
        }

        $cliente = $pagamento->venda->cliente;

        DB::transaction(function () use ($cliente, $pagamento) {

            // 🔒 trava para evitar concorrência
            $ultimoSaldo = ClienteContaCorrente::where('cliente_id', $cliente->id)
                ->lockForUpdate()
                ->sum(DB::raw("
                    CASE 
                        WHEN tipo = 'credito' THEN valor
                        WHEN tipo = 'debito' THEN -valor
                    END
                "));

            // 💰 saldo real da carteira
            $saldoAtual = $ultimoSaldo ?? 0;

            // ❌ valida saldo
            if ($saldoAtual < $pagamento->valor) {
                throw new \Exception('Saldo insuficiente na carteira');
            }

            // ➖ novo saldo
            $novoSaldo = $saldoAtual - $pagamento->valor;

            // 💾 registra movimentação
            ClienteContaCorrente::create([
                'cliente_id' => $cliente->id,
                'venda_id' => $pagamento->venda_id,
                'pagamento_venda_id' => $pagamento->id,
                'tipo' => 'debito',
                'origem' => 'venda',
                'valor' => $pagamento->valor,
                'saldo_apos' => $novoSaldo,
                'descricao' => 'Pagamento via carteira'
            ]);
            Cache::forget("cliente_saldo_{$cliente->id}");
        });
    }

   public function saldoAtual(int $clienteId): float
    {
        return Cache::remember("cliente_saldo_$clienteId", 10, function () use ($clienteId) {

            return ClienteContaCorrente::where('cliente_id', $clienteId)
                ->sum(DB::raw("
                    CASE 
                        WHEN tipo = 'credito' THEN valor
                        WHEN tipo = 'debito' THEN -valor
                    END
                "));
        });
    }
}