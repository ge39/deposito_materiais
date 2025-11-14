<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;
use Carbon\Carbon;
class OrcamentoController extends Controller
{
    /** LISTAGEM */
    // public function index(Request $request)
    // {
    //     $status = $request->status;

    //     $orcamentos = Orcamento::when($status, function ($query, $status) {
    //         return $query->where('status', $status);
    //     })
    //     ->orderBy('id', 'desc')
    //     ->paginate(15);

    //     return view('orcamentos.index', compact('orcamentos', 'status'));
    // }
    public function index(Request $request)
{
    $query = Orcamento::with('cliente');

    // FILTRO STATUS
    if ($request->status) {
        $query->where('status', $request->status);
    }

    // FILTRO CÓDIGO DO ORÇAMENTO
    if ($request->codigo_orcamento) {
        $query->where('codigo_orcamento', $request->codigo_orcamento);
    }

    $orcamentos = $query->orderBy('id', 'desc')->paginate(15);

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

    public function reativar($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        // Só permite reativar se realmente estiver expirado
        if ($orcamento->status === 'Expirado') {
            $orcamento->status = 'Aguardando Aprovação';
            $orcamento->save();

            return back()->with('success', 'Status alterado para Aguardando Aprovação!');
        }

        return back()->with('error', 'Este orçamento não está expirado.');
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

    // Regra solicitada:
    if ($orcamento->status === 'Expirado') {
        $orcamento->status = 'Aguardando aprovacao';
        $orcamento->save();
    }

    // Validação normal
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

        // Atualiza campos principais
        $orcamento->update([
            'cliente_id' => $request->cliente_id,
            'data_orcamento' => $request->data_orcamento,
            'validade' => $request->validade,
            'observacoes' => $request->observacoes
        ]);

        // Remove itens antigos
        $orcamento->itens()->delete();

        $total = 0;

        foreach ($request->produtos as $produto) {
            $subtotal = $produto['quantidade'] * $produto['preco_unitario'];

            ItemOrcamento::create([
                'orcamento_id' => $orcamento->id,
                'produto_id' => $produto['id'],
                'quantidade' => $produto['quantidade'],
                'preco_unitario' => $produto['preco_unitario'],
                'subtotal' => $subtotal
            ]);

            $total += $subtotal;
        }

        $orcamento->update(['total' => $total]);

        DB::commit();

        return redirect()
            ->route('orcamentos.index')
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

        $pdfPath = storage_path('app/public/orcamento/orcamento_'.$orcamento->id.'.pdf');
        $linkPdf = asset("storage/orcamento/orcamento_{$orcamento->id}.pdf");

        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }

   public function enviarWhatsApp($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        // Gera o PDF primeiro
        $pdf = \PDF::loadView('orcamentos.pdf', compact('orcamento'));

        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $pdfPath = storage_path("app/public/orcamento/{$fileName}");

        // Salvar PDF no storage
        $pdf->save($pdfPath);

        // Criar link público direto para o PDF
        $linkPdf = asset("storage/orcamento/{$fileName}");

        // Montar mensagem com link direto
        $mensagem = urlencode("Olá! Segue o seu orçamento: {$linkPdf}");

        // Número do cliente
        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            return back()->with('error', 'O cliente não possui número cadastrado.');
        }

        // Redireciona para WhatsApp Web com link direto para o PDF
        return redirect()->away("https://wa.me/55{$telefone}?text={$mensagem}");
    }


    public function visualizarOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $linkPdf = asset("storage/orcamento/{$fileName}");

        // Retorna uma view simples com botão
        return view('orcamentos.visualizar', compact('linkPdf', 'orcamento'));
    }



}
