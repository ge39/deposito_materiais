<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoOrcamento;
use Illuminate\Http\Request;

class MovimentacaoOrcamentoController extends Controller
{
    /**
     * 🔹 Listar movimentações (geral ou por orçamento)
     */
    public function index(Request $request)
    {
        $query = MovimentacaoOrcamento::with(['orcamento', 'item', 'user'])
            ->orderBy('created_at', 'desc');

        // 🔍 filtro por orçamento
        if ($request->has('orcamento_id')) {
            $query->where('orcamento_id', $request->orcamento_id);
        }

        $movimentacoes = $query->paginate(20);

        return view('movimentacoes.index', compact('movimentacoes'));
    }

    /**
     * 🔹 Mostrar histórico de um orçamento específico (ideal pro cliente)
     */
    public function showByOrcamento($orcamentoId)
    {
        $movimentacoes = MovimentacaoOrcamento::with(['item', 'user'])
            ->where('orcamento_id', $orcamentoId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('movimentacoes.show', compact('movimentacoes', 'orcamentoId'));
    }

    /**
     * 🔹 Criar movimentação manual (opcional)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orcamento_id' => 'required|integer',
            'item_orcamento_id' => 'nullable|integer',
            'tipo' => 'required|string',
            'descricao' => 'nullable|string',
            'quantidade' => 'nullable|numeric',
        ]);

        $data['user_id'] = auth()->id();

        MovimentacaoOrcamento::create($data);

        return redirect()->back()->with('success', 'Movimentação registrada!');
    }
}