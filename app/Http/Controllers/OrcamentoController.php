<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class OrcamentoController extends Controller
{
    /** LISTAGEM */
    public function index()
    {
        $orcamentos = Orcamento::with(['cliente', 'fornecedor'])->orderBy('id', 'desc')->get();
        return view('orcamentos.index', compact('orcamentos'));
    }

    /** FORMULÁRIO DE CRIAÇÃO */
    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome')->get();
        $produtos = Produto::with('fornecedor')->orderBy('descricao')->get();

        return view('orcamentos.create', compact('clientes', 'fornecedores', 'produtos'));
    }
    
    /** ARMAZENA NOVO ORÇAMENTO */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
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
                'fornecedor_id' => $request->fornecedor_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'status' => 'Aberto',
                'observacoes' => $request->observacoes,
                'total' => 0,
            ]);

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
        $orcamento = Orcamento::with(['cliente', 'fornecedor', 'itens.produto'])->findOrFail($id);
        return view('orcamentos.show', compact('orcamento'));
    }

    /** FORMULÁRIO DE EDIÇÃO */
    public function edit($id)
    {
        $orcamento = Orcamento::with('itens.produto')->findOrFail($id);
        $clientes = Cliente::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome')->get();
        $produtos = Produto::with('fornecedor')->orderBy('descricao')->get();

        return view('orcamentos.edit', compact('orcamento', 'clientes', 'fornecedores', 'produtos'));
    }

    /** ATUALIZA ORÇAMENTO */
    public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status !== 'Aberto') {
            return back()->with('error', 'Apenas orçamentos abertos podem ser editados.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'produtos' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $orcamento->update([
                'cliente_id' => $request->cliente_id,
                'fornecedor_id' => $request->fornecedor_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'observacoes' => $request->observacoes,
            ]);

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

            return redirect()->route('orcamentos.index')->with('success', 'Orçamento atualizado com sucesso!');
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
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento excluído com sucesso.');
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


    /** BUSCA AJAX DE PRODUTOS */
    public function buscarProduto(Request $request)
    {
        $busca = $request->input('q');

        $produtos = Produto::with('fornecedor')
            ->where('descricao', 'like', "%$busca%")
            ->orWhere('nome', 'like', "%$busca%")
            ->orWhereHas('fornecedor', function ($query) use ($busca) {
                $query->where('nome', 'like', "%$busca%");
            })
            ->limit(20)
            ->get();

        return response()->json($produtos);
    }
    /** PDF */
    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load('cliente', 'itens.produto'); // carregar relacionamentos

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orcamentos.pdf', compact('orcamento'));

        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }

}
