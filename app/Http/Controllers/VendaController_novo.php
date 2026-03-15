<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagamentoVenda;
use App\Models\Cliente;
use App\Services\ContaCorrenteService;
use App\Services\BloqueioCreditoService;
use App\Services\CreditoService;
use Illuminate\Support\Facades\DB;

class CarteiraController extends Controller
{
    protected $contaCorrenteService;
    protected $bloqueioService;
    protected $creditoService;

    public function __construct(
        ContaCorrenteService $contaCorrenteService,
        BloqueioCreditoService $bloqueioService,
        CreditoService $creditoService
    ) {
        $this->contaCorrenteService = $contaCorrenteService;
        $this->bloqueioService = $bloqueioService;
        $this->creditoService = $creditoService;
    }

    /**
     * Confirma pagamento de uma venda em carteira
     */
    public function pagar(Request $request, PagamentoVenda $pagamento)
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
                'message' => 'Pagamento já confirmado.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cliente = $pagamento->venda->cliente;

            // 1️⃣ Atualiza status do pagamento
            $pagamento->update([
                'status' => 'confirmado'
            ]);

            // 2️⃣ Registra crédito na conta corrente
            $this->contaCorrenteService->registrarMovimentacao($pagamento);

            // 3️⃣ Atualiza status do cliente (bloqueio ou desbloqueio)
            $this->bloqueioService->reavaliarCliente($cliente);

            // 4️⃣ Registra evento no histórico existente
            $cliente->historico()->create([
                'tipo_evento'    => 'desbloqueio', // ou 'bloqueio_limite' se saldo > limite
                'descricao'      => "Pagamento em carteira confirmado. Valor: R$ {$pagamento->valor}",
                'score_anterior' => $cliente->score_credito,
                'score_novo'     => $cliente->score_credito,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento de carteira confirmado com sucesso.'
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