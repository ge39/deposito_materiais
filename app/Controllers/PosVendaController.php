<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosVenda;
use App\Models\ItensPosVenda;
use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class PosVendaController extends Controller
{
    // Lista todas as ocorrências pós-venda
    public function index()
    {
        $posVendas = PosVenda::with('itens.produto')->orderBy('id', 'desc')->get();
        return view('pos_vendas.index', compact('posVendas'));
    }

    // Formulário de criação
    public function create($venda_id)
    {
        $venda = Venda::findOrFail($venda_id);
        $produtos = $venda->itens()->with('produto')->get();

        return view('pos_vendas.create', [
            'venda_id' => $venda_id,
            'produtos' => $produtos
        ]);
    }

    // Salva nova ocorrência pós-venda
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'tipo' => 'required|in:devolucao,troca,atendimento',
            'descricao' => 'nullable|string',
            'valor_devolucao' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $posVenda = PosVenda::create([
                'venda_id' => $request->venda_id,
                'tipo' => $request->tipo,
                'descricao' => $request->descricao,
                'valor_devolucao' => $request->valor_devolucao ?? 0,
                'status' => 'pendente'
            ]);

            // Se houver itens (apenas para devolução ou troca)
            if($request->has('itens')) {
                foreach($request->itens as $item) {
                    $produto = Produto::find($item['produto_id']);
                    if($produto) {
                        ItensPosVenda::create([
                            'pos_venda_id' => $posVenda->id,
                            'produto_id' => $produto->id,
                            'quantidade' => $item['quantidade'],
                            'valor_unitario' => $produto->preco_venda,
                            'total' => $produto->preco_venda * $item['quantidade'],
                        ]);

                        // Ajuste de estoque se for devolução
                        if($request->tipo === 'devolucao') {
                            $produto->estoque += $item['quantidade'];
                            $produto->save();
                        }
                    }
                }
            }
        });

        return redirect()->route('pos_vendas.index')->with('success', 'Ocorrência pós-venda registrada com sucesso!');
    }

    // Visualizar detalhes
    public function show($id)
    {
        $posVenda = PosVenda::with('itens.produto')->findOrFail($id);
        return view('pos_vendas.show', compact('posVenda'));
    }

    // Formulário de edição
    public function edit($id)
    {
        $posVenda = PosVenda::findOrFail($id);
        return view('pos_vendas.edit', compact('posVenda'));
    }

    // Atualizar ocorrência
    public function update(Request $request, $id)
    {
        $posVenda = PosVenda::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pendente,concluido',
            'descricao' => 'nullable|string'
        ]);

        $posVenda->update([
            'status' => $request->status,
            'descricao' => $request->descricao
        ]);

        return redirect()->route('pos_vendas.index')->with('success', 'Ocorrência atualizada com sucesso!');
    }

    // Remover ocorrência
    public function destroy($id)
    {
        $posVenda = PosVenda::findOrFail($id);
        $posVenda->delete();

        return redirect()->route('pos_vendas.index')->with('success', 'Ocorrência removida com sucesso!');
    }
}
