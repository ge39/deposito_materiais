<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;

class OrcamentoController extends Controller
{
    /** LISTAGEM */
    public function index()
    {
        $orcamentos = Orcamento::with(['cliente'])
            ->orderBy('id', 'desc')
            ->get();

        return view('orcamentos.index', compact('orcamentos'));
    }

    /** FORMULÁRIO DE CRIAÇÃO */
    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::with('unidadeMedida')->orderBy('nome')->get();

        return view('orcamentos.create', compact('clientes', 'produtos'));
    }

    /** ARMAZENA NOVO ORÇAMENTO */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
           $orcamento = Orcamento::create([
            'cliente_id' => $request->cliente_id,
            'data_orcamento' => $request->data_orcamento,
            'codigo_orcamento' => now()->format('YmdHis'),
            'validade' => $request->validade,
            'status' => 'Aguardando aprovacao',
            'observacoes' => $request->observacoes,
            'total' => 0,
            'ativo' => 1,
        ]);


            // Atualiza código com ID
            $codigo = now()->format('Ymd') . $orcamento->id;
            $orcamento->update(['codigo_orcamento' => $codigo]);

            $total = 0;
            $produtoIds = [];

            foreach ($request->produtos as $produto) {
                if (in_array($produto['id'], $produtoIds)) {
                    throw new \Exception('Produto duplicado: ' . $produto['id']);
                }

                $produtoIds[] = $produto['id'];
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

            return redirect()->route('orcamentos.index')
                ->with('success', 'Orçamento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage());
        }
    }

    /** EXIBE DETALHES */
    public function show($id)
    {
        $orcamento = Orcamento::with(['cliente', 'itens.produto.unidadeMedida'])
            ->findOrFail($id);

        return view('orcamentos.show', compact('orcamento'));
    }

    /** FORMULÁRIO DE EDIÇÃO */
    public function edit($id)
    {
        $orcamento = Orcamento::with('itens.produto.unidadeMedida')->findOrFail($id);
        $clientes = Cliente::where('ativo', 1)->orderBy('nome')->get();
        $produtos = Produto::with('unidadeMedida')->where('ativo', 1)->orderBy('nome')->get();

        return view('orcamentos.edit', compact('orcamento', 'clientes', 'produtos'));
    }

    /** ATUALIZA ORÇAMENTO */
    public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status !== 'Aguardando aprovacao') {
            return back()->with('error', 'Apenas orçamentos ** Aguardando aprovacao ** podem ser editados.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $orcamento->update([
                'cliente_id' => $request->cliente_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'observacoes' => $request->observacoes,
            ]);

            // Remove itens antigos
            $orcamento->itens()->delete();

            $total = 0;
            $produtoIds = [];

            foreach ($request->produtos as $produto) {
                if (in_array($produto['id'], $produtoIds)) {
                    throw new \Exception('Produto duplicado: ' . $produto['id']);
                }

                $produtoIds[] = $produto['id'];
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

            return redirect()->route('orcamentos.index')
                ->with('success', 'Orçamento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    /** EXCLUSÃO */
    public function destroy($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status === 'Aprovado') {
            return back()->with('error', 'Não é possível excluir orçamentos aprovados.');
        }

        $orcamento->delete();

        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso.');
    }

    /** APROVAR */
    public function aprovar($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->update(['status' => 'Aprovado']);

        return back()->with('success', 'Orçamento aprovado com sucesso!');
    }

    /** CANCELAR */
    public function cancelar($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->update(['status' => 'Cancelado']);

        return back()->with('success', 'Orçamento cancelado com sucesso!');
    }

    /** GERA PDF */
    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load('cliente', 'itens.produto.unidadeMedida');
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));

        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }
}
