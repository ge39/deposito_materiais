<?php

namespace App\Http\Controllers;

use App\Models\Devolucao;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevolucaoController extends Controller
{
    /**
     * Lista todas as devoluções com filtros opcionais.
     */
    public function index(Request $request)
    {
        $clientes = Cliente::orderBy('nome')->get();

        $query = Devolucao::with(['cliente', 'produto', 'venda'])
            ->when($request->cliente_id, fn($q) => $q->where('cliente_id', $request->cliente_id))
            ->when($request->venda_id, fn($q) => $q->where('venda_id', $request->venda_id))
            ->when($request->produto_codigo, function ($q) use ($request) {
                $q->whereHas('produto', fn($p) => $p->where('codigo', 'like', "%{$request->produto_codigo}%"));
            })
            ->when($request->data, fn($q) => $q->whereDate('created_at', $request->data))
            ->orderByDesc('created_at');

        $devolucoes = $query->paginate(15);

        return view('devolucoes.index', compact('clientes', 'devolucoes'));
    }

    /**
     * Exibe o formulário de nova devolução.
     */
    public function create($item_id)
    {
        $item = VendaItem::with('venda.cliente', 'produto')->findOrFail($item_id);

        return view('devolucoes.create', compact('item'));
    }

    /**
     * Registra uma nova devolução.
     */
    public function store(Request $request)
    {
        $request->validate([
            'venda_item_id' => 'required|exists:venda_itens,id',
            'quantidade' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        $item = VendaItem::with('venda.cliente', 'produto')->findOrFail($request->venda_item_id);

        if ($request->quantidade > $item->quantidade) {
            return back()->withErrors(['quantidade' => 'A quantidade devolvida não pode ser maior que a vendida.']);
        }

        $devolucao = Devolucao::create([
            'cliente_id' => $item->venda->cliente_id,
            'venda_id' => $item->venda_id,
            'venda_item_id' => $item->id,
            'produto_id' => $item->produto_id,
            'quantidade' => $request->quantidade,
            'motivo' => $request->motivo,
            'tipo' => $request->tipo ?? 'defeito',
            'status' => 'pendente',
            'observacao' => $request->observacao,
            'criado_por' => Auth::id(),
        ]);

        return redirect()->route('devolucoes.show', $devolucao)
            ->with('success', 'Devolução registrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma devolução específica.
     */
    public function show(Devolucao $devolucao)
    {
        $devolucao->load(['cliente', 'produto', 'venda']);

        return view('devolucoes.show', compact('devolucao'));
    }

    /**
     * Atualiza o status da devolução (ex: aprovada, rejeitada, concluída).
     */
    public function updateStatus(Request $request, Devolucao $devolucao)
    {
        $request->validate([
            'status' => 'required|in:pendente,aprovada,rejeitada,concluida',
        ]);

        $devolucao->update(['status' => $request->status]);

        return back()->with('success', 'Status atualizado com sucesso!');
    }

    /**
     * Remove uma devolução (apenas administradores).
     */
    public function destroy(Devolucao $devolucao)
    {
        $devolucao->delete();

        return redirect()->route('devolucoes.index')->with('success', 'Devolução removida com sucesso!');
    }
}
