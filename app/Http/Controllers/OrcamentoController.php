<?php

namespace App\Http\Controllers;

use App\Services\OrcamentoService;
use App\Models\Orcamento;
use Illuminate\Http\Request;

class OrcamentoController extends Controller
{
    protected $service;

    public function __construct(OrcamentoService $service)
    {
        $this->service = $service;
    }

    /**
     * LISTAGEM
     */
    public function index(Request $request)
    {
        $orcamentos = $this->service->listar($request);

        return view('orcamentos.index', compact('orcamentos'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        $data = $this->service->dadosParaCriacao();

        return view('orcamentos.create', $data);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $this->service->criarCompleto($request->all());

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso!');
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $orcamento = $this->service->buscarCompleto($id);

        return view('orcamentos.show', compact('orcamento'));
    }

    /**
     * EDIT
     */
    public function edit($id)
    {
        $data = $this->service->dadosParaEdicao($id);

        if (!$data) {
            return back()->with('error', 'Orçamento em edição por outro usuário.');
        }

        return view('orcamentos.edit', $data);
    }

    /**
     * UPDATE
     */
  
    public function update(Request $request, $id)
    {
        $this->service->atualizarCompleto($request, $id);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento atualizado com sucesso!');
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $this->service->excluir($orcamento);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento excluído!');
    }

    /**
     * APROVAR
     */
    public function aprovar($id)
    {
        $this->service->aprovarCompleto($id);

        return back()->with('success', "Orçamento {$id} aprovado!");
    }

    /**
     * CANCELAR
     */
    public function cancelar($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $this->service->cancelar($orcamento);

        return back()->with('success', 'Orçamento cancelado!');
    }

    /**
     * REATIVAR
     */
    public function reativar($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $this->service->reativar($orcamento);

        return back()->with('success', 'Orçamento reativado!');
    }

    /**
     * PDF
     */
    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load([
            'itens.produto',
            'itens.lote',
            'cliente',
            'empresa'
        ]);

        $movimentacoes = \App\Models\MovimentacaoOrcamento::where('orcamento_id', $orcamento->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // DEBUG opcional (remover depois)
        // dd($orcamento->toArray(), $movimentacoes->toArray());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'orcamentos.pdf',
            compact('orcamento', 'movimentacoes')
        );

        return $pdf->stream("orcamento_{$orcamento->id}.pdf");
    }

    /**
     * WHATSAPP
     */
    public function enviarWhatsApp($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $url = $this->service->enviarWhatsapp($orcamento);

        return redirect()->away($url);
    }

    /**
     * VISUALIZAR PDF
     */
    public function visualizarOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $linkPdf = $this->service->visualizarArquivo($orcamento);

        return view('orcamentos.visualizar', compact('orcamento', 'linkPdf'));
    }

    /**
     * LIMPAR EDIÇÃO
     */
    public function limparEdicao($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $this->service->limparEdicao($orcamento);

        return response()->json(['status' => 'ok']);
    }
}