<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 use Carbon\Carbon;

class VendaController extends Controller
{
    public function index()
    {
        $vendas = Venda::with(['cliente','funcionario'])
            ->orderByDesc('data_venda')
            ->paginate(20);

        return view('vendas.index', compact('vendas'));
    }

    public function show(Venda $venda)
    {
        $venda->load(['itens.produto','pagamentos']);
        return view('vendas.show', compact('venda'));
    }

    /**
     * F4 – Buscar Vendas do dia (Histórico)*/
    public function buscarVendasDia()
    {
        $vendas = Venda::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->get();

        return response()->json($vendas);
    }
    
    // Salva a venda no banco
    public function storeVenda(Request $request)
    {
        $request->validate([
            'forma_pagamento' => 'required|string',
            'observacoes' => 'nullable|string',
        ]);

        $cliente = session('pdv_cliente');
        $carrinho = session('pdv_carrinho', []);

        if (empty($carrinho)) {
            return redirect()->back()->withErrors('O carrinho está vazio.');
        }

        DB::beginTransaction();

        try {
            // Cria a venda
            $venda = Venda::create([
                'cliente_id' => $cliente['id'] ?? null,
                'user_id' => auth()->id(),
                'total' => array_sum(array_map(fn($i) => $i['preco'] * $i['quantidade'], $carrinho)),
                'forma_pagamento' => $request->forma_pagamento,
                'observacoes' => $request->observacoes,
                'status' => 'Concluida',
                'data_venda' => Carbon::now(),
            ]);

            // Cria os itens da venda
            foreach ($carrinho as $item) {
                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco' => $item['preco'],
                    'subtotal' => $item['preco'] * $item['quantidade'],
                ]);

                // Atualiza o estoque
                $produto = Produto::find($item['id']);
                if ($produto) {
                    $produto->estoque -= $item['quantidade'];
                    $produto->save();
                }
            }

            DB::commit();

            // Limpa sessão PDV
            session()->forget(['pdv_cliente', 'pdv_carrinho']);

            return redirect()->route('pdv.success', ['venda' => $venda->id])
                             ->with('success', 'Venda concluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Erro ao finalizar a venda: ' . $e->getMessage());
        }
    }

    /**
     * IMPORTANTE:
     * store completo (estoque, caixa, pagamento)
     * será tratado depois — este controller está pronto estruturalmente.
     */
}
