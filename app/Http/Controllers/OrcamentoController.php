<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // no topo do controller
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;


class OrcamentoController extends Controller
{
    public function index()
    {
        $orcamentos = Orcamento::with('cliente')->orderBy('id', 'desc')->get();
        return view('orcamentos.index', compact('orcamentos'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $produtos = Produto::all();
        return view('orcamentos.create', compact('clientes', 'produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date',
            'produtos' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $orcamento = Orcamento::create([
                'cliente_id' => $request->cliente_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'status' => 'Aberto',
                'observacoes' => $request->observacoes ?? null,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->produtos as $produto) {
                $subtotal = $produto['quantidade'] * $produto['preco_unitario'];
                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $produto['id'],
                    'quantidade' => $produto['quantidade'],
                    'preco_unitario' => $produto['preco_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $orcamento->update(['total' => $total]);

            DB::commit();

            return redirect()->route('orcamentos.index')->with('success', 'Orçamento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $orcamento = Orcamento::with(['cliente', 'itens.produto'])->findOrFail($id);
        return view('orcamentos.show', compact('orcamento'));
    }

    public function edit($id)
    {
        $orcamento = Orcamento::with('itens')->findOrFail($id);
        $clientes = Cliente::all();
        $produtos = Produto::all();
        return view('orcamentos.edit', compact('orcamento', 'clientes', 'produtos'));
    }

    public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status !== 'Aberto') {
            return back()->with('error', 'Apenas orçamentos abertos podem ser editados.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date',
            'produtos' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $orcamento->update([
                'cliente_id' => $request->cliente_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'observacoes' => $request->observacoes ?? null,
            ]);

            $orcamento->itens()->delete();
            $total = 0;

            foreach ($request->produtos as $produto) {
                $subtotal = $produto['quantidade'] * $produto['preco_unitario'];
                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $produto['id'],
                    'quantidade' => $produto['quantidade'],
                    'preco_unitario' => $produto['preco_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $orcamento->update(['total' => $total]);

            DB::commit();

            return redirect()->route('orcamentos.index')->with('success', 'Orçamento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar orçamento: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status === 'Aprovado') {
            return back()->with('error', 'Não é possível excluir orçamentos aprovados.');
        }

        $orcamento->delete();
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento excluído com sucesso.');
    }

    public function aprovar($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->update(['status' => 'Aprovado']);
        return back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function cancelar($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->update(['status' => 'Cancelado']);
        return back()->with('success', 'Orçamento cancelado com sucesso!');
    }
    public function gerarPdf($id)
    {
        $orcamento = Orcamento::with(['cliente', 'itens.produto'])->findOrFail($id);
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'))->setPaper('a4');
        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }
}
