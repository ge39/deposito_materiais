<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ContaCorrenteService
{
    /**
     * Registra uma movimentação de débito na carteira de forma segura.
     */
    public function registrarMovimentacao(PagamentoVenda $pagamento): void
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return;
        }

        // 🚀 OTIMIZAÇÃO: Evita queries redundantes puxando o cliente diretamente da relação já carregada
        $venda = $pagamento->venda;
        $cliente = $venda ? $venda->cliente : null;

        if (!$cliente) {
            throw new \Exception('Cliente não encontrado para registrar movimentação.');
        }

        DB::transaction(function () use ($cliente, $pagamento) {
            // 🔒 Mantido o lock protetivo, mas agora executado em milissegundos após o commit da venda
            $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $cliente->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            // Garante o carregamento do crédito ativo de forma leve se for o primeiro uso
            if ($ultimaMovimentacao === null) {
                $cliente->loadMissing('creditoAtivo');
            }

            $limiteCredito = (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
            $saldoAtual = $ultimaMovimentacao !== null ? (float)$ultimaMovimentacao->saldo_apos : $limiteCredito;

            if ($saldoAtual < (float)$pagamento->valor) {
                throw new \Exception('Saldo insuficiente na carteira para processar este pagamento.');
            }

            $novoSaldo = $saldoAtual - (float)$pagamento->valor;

            ClienteContaCorrente::create([
                'cliente_id'         => $cliente->id,
                'venda_id'           => $pagamento->venda_id,
                'pagamento_venda_id' => $pagamento->id,
                'tipo'               => 'debito',
                'origem'             => 'venda',
                'valor'              => $pagamento->valor,
                'saldo_apos'         => $novoSaldo,
                'descricao'          => 'Pagamento via carteira'
            ]);

            // 🔥 CORREÇÃO: Limpa a chave exata do cache usando a mesma string do método saldoAtual
            Cache::forget("cliente_saldo_{$cliente->id}");
        });
    }

    /**
     * Retorna o saldo atual real do cliente (Sincronizado com as regras do PDV)
     */
    public function saldoAtual(int $clienteId): float
    {
        // 🚀 OTIMIZAÇÃO: 10 segundos de cache são suficientes para proteger o PDV de cliques duplos 
        // sem causar risco de leituras desatualizadas de saldo.
        return (float) Cache::remember("cliente_saldo_{$clienteId}", 10, function () use ($clienteId) {
            
            $ultimoSaldoRaw = ClienteContaCorrente::where('cliente_id', $clienteId)
                ->orderByDesc('id')
                ->value('saldo_apos');

            if ($ultimoSaldoRaw !== null) {
                return (float)$ultimoSaldoRaw;
            }

            $cliente = Cliente::with('creditoAtivo')->find($clienteId);
            
            return (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
        });
    }
}
