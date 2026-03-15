<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PagamentoVenda;
use App\Models\Cliente;
use App\Services\ContaCorrenteService;
use App\Services\HistoricoCreditoService;
use App\Services\BloqueioCreditoService;

class CarteiraController extends Controller
{
    /**
     * Marca pagamento em carteira como confirmado
     * e atualiza conta corrente, histórico e bloqueio do cliente.
     */
    public function pagar(PagamentoVenda $pagamento)
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return response()->json([
                'success' => false,
                'message' => 'Pagamento não é do tipo carteira.'
            ], 422);
        }

        if ($pagamento->status === 'confirmado') {
            return response()->json([
                'success' => false,
                'message' => 'Pagamento já foi confirmado.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cliente = $pagamento->venda->cliente;

            // 1️⃣ Atualiza status do pagamento para confirmado
            $pagamento->update(['status' => 'confirmado']);

            // 2️⃣ Gera crédito na conta corrente
            app(ContaCorrenteService::class)->registrarMovimentacao($pagamento);

            // 3️⃣ Registra crédito no histórico
            app(HistoricoCreditoService::class)->registrarCredito($cliente, $pagamento);

            // 4️⃣ Reavalia bloqueio do cliente
            app(BloqueioCreditoService::class)->reavaliarCliente($cliente);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento em carteira confirmado com sucesso.',
                'saldo_atual' => app(ContaCorrenteService::class)->saldoAtual($cliente->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}