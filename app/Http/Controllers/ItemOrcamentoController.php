<?php

namespace App\Http\Controllers;

use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class ItemOrcamentoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'orcamento_id' => 'required|exists:orcamentos,id',
            'quantidade' => 'required|numeric|min:1',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        $item = new ItemOrcamento([
            'orcamento_id' => $request->orcamento_id,
            'produto_id' => $request->produto_id ?: null,
            'descricao_cliente' => $request->descricao_cliente,
            'marca' => $request->marca,
            'fornecedor_id' => $request->fornecedor_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $request->valor_unitario,
            'subtotal' => $request->quantidade * $request->valor_unitario,
        ]);

        $item->save();

        return redirect()
            ->route('orcamentos.show', $item->orcamento_id)
            ->with('success', 'Item adicionado ao orçamento com sucesso!');
    }

    public function update(Request $request, ItemOrcamento $itemOrcamento)
    {
        $request->validate([
            'quantidade' => 'required|numeric|min:1',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        $itemOrcamento->update([
            'produto_id' => $request->produto_id ?: null,
            'descricao_cliente' => $request->descricao_cliente,
            'marca' => $request->marca,
            'fornecedor_id' => $request->fornecedor_id,
            'quantidade' => $request->quantidade,
            'valor_unitario' => $request->valor_unitario,
            'subtotal' => $request->quantidade * $request->valor_unitario,
        ]);

        return redirect()
            ->route('orcamentos.show', $itemOrcamento->orcamento_id)
            ->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(ItemOrcamento $itemOrcamento)
    {
        $orcamentoId = $itemOrcamento->orcamento_id;
        $itemOrcamento->delete();

        return redirect()
            ->route('orcamentos.show', $orcamentoId)
            ->with('success', 'Item removido com sucesso!');
    }
}
