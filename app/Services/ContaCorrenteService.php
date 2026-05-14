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

        // Carrega o cliente com seu crédito ativo para obter o limite base se necessário
        $cliente = $pagamento->venda->cliente()->with('creditoAtivo')->first();
        if (!$cliente) {
            throw new \Exception('Cliente não encontrado para registrar movimentação.');
        }

        DB::transaction(function () use ($cliente, $pagamento) {
            // 🔒 Trava o último registro de conta corrente do cliente para evitar concorrência (Race Condition)
            $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $cliente->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            // 💰 Se nunca movimentou, o saldo base é o limite de crédito do cliente
            $limiteCredito = (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
            $saldoAtual = $ultimaMovimentacao !== null ? (float)$ultimaMovimentacao->saldo_apos : $limiteCredito;

            // ❌ Valida se o saldo atual suporta o débito
            if ($saldoAtual < (float)$pagamento->valor) {
                throw new \Exception('Saldo insuficiente na carteira para processar este pagamento.');
            }

            // ➖ Calcula o novo saldo após o débito
            $novoSaldo = $saldoAtual - (float)$pagamento->valor;

            // 💾 Registra a movimentação mantendo o histórico perfeito do saldo_apos
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

            // Limpa o cache imediatamente para o próximo select ler o dado atualizado
            Cache::forget("cliente_saldo_{$cliente->id}");
        });
    }

    /**
     * Retorna o saldo atual real do cliente (Sincronizado com as regras do PDV)
     */
    public function saldoAtual(int $clienteId): float
    {
        return (float) Cache::remember("cliente_saldo_$clienteId", 10, function () use ($clienteId) {
            // 1️⃣ Busca a última movimentação realizada
            $ultimoSaldoRaw = ClienteContaCorrente::where('cliente_id', $clienteId)
                ->orderByDesc('id')
                ->value('saldo_apos');

            // 2️⃣ Se encontrou uma movimentação, retorna o saldo dela
            if ($ultimoSaldoRaw !== null) {
                return (float)$ultimoSaldoRaw;
            }

            // 3️⃣ 🔥 RETORNO SEGURO: Se nunca movimentou, busca o limite de crédito do banco como saldo inicial disponível
            $cliente = Cliente::with('creditoAtivo')->find($clienteId);
            
            return (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
        });
    }
}
