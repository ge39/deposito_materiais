<?php

namespace App\Http\Controllers;

use App\Models\ItensVenda;
use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Http\Request;

class ItensVendaController extends Controller
{
    public function index()
    {
        $itens = ItensVenda::with(['venda', 'produto'])->get();
        return view('itens_venda.index', compact('itens'));
    }

    public function create()
    {
        $vendas = Venda::all();
        $produtos = Produto::all();
        return view('itens_venda.create', compact('vendas', 'produtos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer',
            'preco' => 'required|numeric',
        ]);

        ItensVenda::create($data);
        return redirect()->route('itens_venda.index')->with('success', 'Item de venda criado com sucesso.');
    }

    public function show(ItensVenda $itensVenda)
    {
        return view('itens_venda.show', compact('itensVenda'));
    }

    public function edit(ItensVenda $itensVenda)
    {
        $vendas = Venda::all();
        $produtos = Produto::all();
        return view('itens_venda.edit', compact('itensVenda','vendas','produtos'));
    }

    public function update(Request $request, ItensVenda $itensVenda)
    {
        $data = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer',
            'preco' => 'required|numeric',
        ]);

        $itensVenda->update($data);
        return redirect()->route('itens_venda.index')->with('success', 'Item de venda atualizado com sucesso.');
    }

    public function destroy(ItensVenda $itensVenda)
    {
        $itensVenda->delete();
        return redirect()->route('itens_venda.index')->with('success', 'Item de venda removido com sucesso.');
    }
}
