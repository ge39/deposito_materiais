<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoCompra;
use App\Models\PedidoItem;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class PedidoCompraController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCompra::with('fornecedor', 'user', 'itens')->orderBy('data_pedido', 'desc')->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        return view('pedidos.create', compact('fornecedores', 'produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $pedido = PedidoCompra::create([
                'user_id' => 1, // valor fixo
                'fornecedor_id' => $request->fornecedor_id,
                'data_pedido' => $request->data_pedido,
                'status' => 'pendente',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->itens as $item) {
                $subtotal = $item['quantidade'] * $item['valor_unitario'];
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            DB::commit();

            return redirect()->route('pedidos.index')->with('success', 'Pedido salvo com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Erro ao salvar o pedido: ' . $e->getMessage());
        }
    }
    public function edit(PedidoCompra $pedido)
    {
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        $pedido->load('itens'); // carrega itens do pedido
        return view('pedidos.edit', compact('pedido', 'fornecedores', 'produtos'));
}

    public function update(Request $request, PedidoCompra $pedido)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pedido->update([
                'fornecedor_id' => $request->fornecedor_id,
                'data_pedido' => $request->data_pedido,
            ]);

            // Remove itens antigos
            $pedido->itens()->delete();

            $total = 0;
            foreach ($request->itens as $item) {
                $subtotal = $item['quantidade'] * $item['valor_unitario'];
                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Erro ao atualizar o pedido: ' . $e->getMessage());
        }
    }
    public function show(PedidoCompra $pedido)
    {
        // Carrega os itens com os produtos e a unidade de medida de cada produto
        $pedido->load('itens.produto.unidade', 'fornecedor','user', 'itens.produto');

        return view('pedidos.show', compact('pedido'));
    }
}
