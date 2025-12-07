<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\Produto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use DB;

class VendaController extends Controller
{
    // Tela inicial do PDV
    public function index()
    {
        $vendasAbertas = Venda::where('status', 'aberta')->with('cliente')->get();
        $clientes = Cliente::orderBy('nome')->get();
        return view('pdv.index', compact('vendasAbertas', 'clientes'));
    }

    // Abrir nova venda
    public function abrir()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('pdv.abrir', compact('clientes'));
    }

    public function abrirConfirmar(Request $request)
    {
        $venda = Venda::create([
            'cliente_id' => $request->cliente_id,
            'funcionario_id' => auth()->id(),
            'status' => 'aberta',
            'total' => 0,
            'data_venda' => now()
        ]);

        return redirect()->route('pdv.itens', $venda->id);
    }

    // Tela de itens
    public function itens(Venda $venda)
    {
        $produtos = Produto::where('ativo',1)->get();
        $itens = $venda->itens()->with('produto')->get();
        return view('pdv.itens', compact('venda','itens','produtos'));
    }

    // Adicionar item
    public function adicionarItem(Request $request, Venda $venda)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $produto = Produto::find($request->produto_id);

        ItemVenda::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'lote_id' => null, // vincular conforme lógica de estoque/lotes
            'quantidade' => $request->quantidade,
            'preco_unitario' => $produto->preco_venda,
            'desconto' => 0
        ]);

        $venda->update([
            'total' => $venda->itens()->sum('subtotal')
        ]);

        return redirect()->route('pdv.itens', $venda->id);
    }

    // Remover item
    public function removerItem(ItemVenda $item)
    {
        $venda = $item->venda;
        $item->delete();

        $venda->update([
            'total' => $venda->itens()->sum('subtotal')
        ]);

        return redirect()->route('pdv.itens', $venda->id);
    }

    // Finalizar venda - tela
    public function finalizar(Venda $venda)
    {
        return view('pdv.finalizar', compact('venda'));
    }

    // Confirmar finalização
    public function finalizarConfirmado(Request $request, Venda $venda)
    {
        $venda->update([
            'status' => 'concluida',
            'total' => $venda->itens()->sum('subtotal')
        ]);

        return redirect()->route('pdv.recibo', $venda->id);
    }

    // Cancelar venda
    public function cancelar(Venda $venda)
    {
        $venda->itens()->delete();
        $venda->update(['status'=>'cancelada','total'=>0]);
        return redirect()->route('pdv.index');
    }

    // Recibo
    public function recibo(Venda $venda)
    {
        $itens = $venda->itens()->with('produto')->get();
        return view('pdv.recibo', compact('venda','itens'));
    }
}
