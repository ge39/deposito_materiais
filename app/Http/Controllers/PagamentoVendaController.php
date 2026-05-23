<?php

namespace App\Http\Controllers;

use App\Models\PagamentoVenda;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagamentoVendaController extends Controller
{
    public function store(Request $request)
    {
        // 1️⃣ Validação rigorosa conforme o seu Describe do banco
        $request->validate([
            'venda_id'        => 'required|exists:vendas,id',
            'forma_pagamento' => 'required|in:dinheiro,pix,cartao_credito,cartao_debito,carteira,boleto,outros',
            'valor'           => 'required|numeric|min:0.01',
            'bandeira'        => 'nullable|string|max:30',
            'parcelas'        => 'nullable|integer|min:1',
            'data_vencimento' => 'nullable|date'
        ]);

        try {
            // 2️⃣ Carrega a venda para checar a trava do caixa antes de tentar inserir
            $venda = Venda::findOrFail($request->venda_id);
            $venda->loadMissing('caixa');

            // Proteção idêntica ao booted para evitar que o banco trave sem resposta tratada
            if ($venda->caixa && in_array($venda->caixa->status, ['fechado', 'inconsistente'])) {
                return back()->withErrors(['erro' => 'Não é permitido adicionar pagamentos a uma venda de um caixa já fechado.']);
            }

            // 3️⃣ Persistência utilizando o Model PagamentoVenda (Dispara observers se houver)
            DB::transaction(function () use ($request) {
                PagamentoVenda::create([
                    'user_id'         => auth()->id() ?? $request->input('user_id', 1),
                    'venda_id'        => $request->venda_id,
                    'forma_pagamento' => $request->forma_pagamento,
                    'bandeira'        => $request->bandeira,
                    'valor'           => $request->valor,
                    'parcelas'        => $request->parcelas ?? 1,
                    'status'          => 'confirmado',
                    'data_vencimento' => $request->data_vencimento ?? now()->format('Y-m-d')
                ]);

                // Opcional: Aqui você pode disparar a atualização de saldo de caixa se necessário
            });

            return back()->with('success', 'Pagamento registrado com sucesso.');

        } catch (\Exception $e) {
            return back()->withErrors(['erro' => 'Falha ao registrar pagamento: ' . $e->getMessage()]);
        }
    }
}
