<?php

namespace App\Http\Controllers;

use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\Lote; // 🔥 LOTE
use Illuminate\Http\Request;

class ItemOrcamentoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'orcamento_id' => 'required|exists:orcamentos,id',
            'produto_id' => 'nullable|exists:produtos,id',
            'lote_id' => 'nullable|exists:lotes,id', // 🔥 LOTE
            'quantidade' => 'required|numeric|min:1',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        // 🔥 LOTE - validação extra (opcional mas recomendado)
        if (!empty($request->lote_id) && !empty($request->produto_id)) {
            $lote = Lote::find($request->lote_id);

            if (!$lote) {
                return back()->with('error', 'Lote não encontrado.');
            }

            if ($lote->produto_id != $request->produto_id) {
                return back()->with('error', 'O lote não pertence ao produto selecionado.');
            }
        }

        $item = ItemOrcamento::create([
            'orcamento_id' => $request->orcamento_id,
            'produto_id' => $request->produto_id,
            'lote_id' => $request->lote_id ?? null, // 🔥 LOTE
            'quantidade' => $request->quantidade,
            'preco_unitario' => $request->preco_unitario,
            'subtotal' => $request->quantidade * $request->preco_unitario,
        ]);

        return redirect()
            ->route('orcamentos.show', $item->orcamento_id)
            ->with('success', 'Item adicionado ao orçamento com sucesso!');
    }
    
    public function update(Request $request, ItemOrcamento $itemOrcamento)
    {
        $request->validate([
            'produto_id' => 'nullable|exists:produtos,id',
            'lote_id' => 'nullable|exists:lotes,id', // 🔥 LOTE
            'quantidade' => 'required|numeric|min:1',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        // 🔥 LOTE - validação extra
        if (!empty($request->lote_id) && !empty($request->produto_id)) {
            $lote = Lote::find($request->lote_id);

            if (!$lote) {
                return back()->with('error', 'Lote não encontrado.');
            }

            if ($lote->produto_id != $request->produto_id) {
                return back()->with('error', 'O lote não pertence ao produto selecionado.');
            }
        }

        $itemOrcamento->update([
            'produto_id' => $request->produto_id ?: null,
            'lote_id' => $request->lote_id ?? null, // 🔥 LOTE
            'quantidade' => $request->quantidade,
            'preco_unitario' => $request->preco_unitario,
            'subtotal' => $request->quantidade * $request->preco_unitario,
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