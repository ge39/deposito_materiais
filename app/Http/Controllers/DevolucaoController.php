<?php

namespace App\Http\Controllers;

use App\Models\Devolucao;
use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Http\Request;

class DevolucaoController extends Controller
{
    public function index()
    {
        $devolucoes = Devolucao::with('venda','produto','produtoTroca')->get();
        return view('devolucoes.index', compact('devolucoes'));
    }

    public function create()
    {
        $vendas = Venda::with('itens')->get();
        $produtos = Produto::all();
        return view('devolucoes.create', compact('vendas','produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1',
            'tipo' => 'required|in:devolucao,troca',
        ]);

        $venda = Venda::findOrFail($request->venda_id);
        $produto = Produto::findOrFail($request->produto_id);
        $quantidade = $request->quantidade;

        // Valor unitário do item original
        $valor_unitario = $produto->preco;

        $diferenca = 0;
        if($request->tipo === 'troca' && $request->produto_troca_id) {
            $produto_troca = Produto::findOrFail($request->produto_troca_id);
            $diferenca = ($produto_troca->preco - $valor_unitario) * $quantidade;

            // Atualiza estoque do produto de troca
            $produto_troca->estoque -= $quantidade;
            $produto_troca->save();
        }

        // Atualiza estoque do produto devolvido
        $produto->estoque += $quantidade;
        $produto->save();

        // Cria registro da devolução
        Devolucao::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => $quantidade,
            'valor_unitario' => $valor_unitario,
            'tipo' => $request->tipo,
            'produto_troca_id' => $request->produto_troca_id ?? null,
            'diferenca' => $diferenca,
            'observacoes' => $request->observacoes,
        ]);

        // Atualiza total da venda
        $itens = $venda->itens;
        $total = 0;
        foreach($itens as $item) {
            $total += $item->quantidade * $item->preco;
        }

        // Subtrai valor da devolução e adiciona diferença se houver
        $total -= $quantidade * $valor_unitario;
        $total += $diferenca;
        $venda->total = $total;
        $venda->save();

        return redirect()->route('devolucoes.index')->with('success','Devolução registrada com sucesso!');
    }

    public function show($id)
    {
        $devolucao = Devolucao::with('venda','produto','produtoTroca')->findOrFail($id);
        return view('devolucoes.show', compact('devolucao'));
    }
}
