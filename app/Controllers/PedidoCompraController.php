<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoCompra;
use App\Models\ItemPedidoCompra;
use App\Models\Fornecedor;
use App\Models\Produto;

class PedidoCompraController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCompra::with('fornecedor')->get();
        return view('pedidos_compras.index', compact('pedidos'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        return view('pedidos_compras.create', compact('fornecedores','produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco_unitario' => 'required|numeric|min:0',
        ]);

        // Cria o pedido
        $pedido = PedidoCompra::create([
            'fornecedor_id' => $request->fornecedor_id,
            'data_pedido' => $request->data_pedido,
            'status' => 'pendente',
            'total' => 0,
            'observacoes' => $request->observacoes,
        ]);

        $totalPedido = 0;

        foreach ($request->itens as $item) {
            $totalItem = $item['quantidade'] * $item['preco_unitario'];
            ItemPedidoCompra::create([
                'pedido_id' => $pedido->id,
                'produto_id' => $item['produto_id'],
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $item['preco_unitario'],
                'total' => $totalItem,
            ]);
            $totalPedido += $totalItem;
        }

        $pedido->total = $totalPedido;
        $pedido->save();

        return redirect()->route('pedidos_compras.index')->with('success','Pedido de compra cadastrado com sucesso!');
    }

    public function show($id)
    {
        $pedido = PedidoCompra::with('itens.produto','fornecedor')->findOrFail($id);
        return view('pedidos_compras.show', compact('pedido'));
    }

    public function edit($id)
    {
        $pedido = PedidoCompra::with('itens')->findOrFail($id);
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        return view('pedidos_compras.edit', compact('pedido','fornecedores','produtos'));
    }

    public function update(Request $request, $id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'status' => 'required|in:pendente,recebido,cancelado',
        ]);

        $pedido->fornecedor_id = $request->fornecedor_id;
        $pedido->data_pedido = $request->data_pedido;
        $pedido->status = $request->status;
        $pedido->observacoes = $request->observacoes;
        $pedido->save();

        // Atualizar estoque se o status for recebido
        if($pedido->status == 'recebido'){
            foreach($pedido->itens as $item){
                $produto = $item->produto;
                $produto->estoque += $item->quantidade;
                $produto->save();
            }
        }

        return redirect()->route('pedidos_compras.index')->with('success','Pedido atualizado com sucesso!');
    }
}
