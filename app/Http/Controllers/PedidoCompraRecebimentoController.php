<?php

namespace App\Http\Controllers;

use App\Models\PedidoCompra;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PedidoCompraRecebimentoController extends Controller
{
    /**
     * Carrega os itens do pedido e retorna JSON para abrir o modal.
     */
    public function carregarItens($id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);

        return response()->json([
            'pedido' => $pedido,
            'itens' => $pedido->itens
        ]);
    }

    /**
     * Receber e gerar lotes
     */
    public function receber(Request $request, $id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);

        $request->validate([
            'itens' => 'required|array',
            'itens.*.preco_compra' => 'required|numeric|min:0',
            'itens.*.validade_lote' => 'required|date',
            'itens.*.quantidade_recebida' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->itens as $itemId => $dados) {

                $itemPedido = $pedido->itens->where('id', $itemId)->first();
                if (!$itemPedido) continue;

                Lote::create([
                    'produto_id' => $itemPedido->produto_id,
                    'pedido_compra_id' => $pedido->id,
                    'quantidade_inicial' => $dados['quantidade_recebida'],
                    'quantidade_atual' => $dados['quantidade_recebida'],
                    'preco_compra' => $dados['preco_compra'],
                    'validade_lote' => Carbon::parse($dados['validade_lote'])->format('Y-m-d'),
                ]);
            }

            $pedido->update([
                'status' => 'recebido'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido recebido e lotes gerados com sucesso!'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao receber pedido.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
