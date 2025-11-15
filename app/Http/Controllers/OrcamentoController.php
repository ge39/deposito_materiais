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
    public function index(Request $request)
    {
        $query = Orcamento::with('cliente');

        if ($request->status) {
            $query->where('status', $request->status);
        }

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
        // 1) Validação inicial (entrada)
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

            /**
             * 2) Revalida o cliente durante a transação (blindagem contra concorrência)
             */
            $cliente = Cliente::where('id', $request->cliente_id)
                ->lockForUpdate()
                ->firstOrFail();

            /**
             * 3) Cria o orçamento
             */
            $orcamento = Orcamento::create([
                'cliente_id' => $cliente->id,
                'data_orcamento' => $request->data_orcamento,
                'codigo_orcamento' => now()->format('YmdHis'), // temporário
                'validade' => $request->validade,
                'status' => 'Aguardando aprovacao',
                'observacoes' => $request->observacoes,
                'total' => 0,
                'ativo' => 1,
            ]);

            /**
             * 4) Gera o código único usando o ID gerado na transação
             */
            $codigo = now()->format('Ymd') . $orcamento->id;
            $orcamento->update(['codigo_orcamento' => $codigo]);

            $total = 0;
            $produtoIds = [];

            /**
             * 5) PROCESSA OS PRODUTOS COM LOCK
             */
            foreach ($request->produtos as $produto) {

                // -----------------------------------------------
                // Proteção contra item duplicado na mesma requisição
                // -----------------------------------------------
                if (in_array($produto['id'], $produtoIds)) {
                    throw new \Exception('Produto duplicado: ' . $produto['id']);
                }
                $produtoIds[] = $produto['id'];

                // -----------------------------------------------
                // Revalidar produto dentro da transação
                // garante integridade caso seja alterado por outro terminal
                // -----------------------------------------------
                $produtoModel = Produto::where('id', $produto['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // -----------------------------------------------
                // Cálculo blindado
                // -----------------------------------------------
                $quantidade = (float) $produto['quantidade'];
                $preco = (float) $produto['preco_unitario'];
                $subtotal = $quantidade * $preco;

                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $produtoModel->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $preco,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            /**
             * 6) Atualiza o total com segurança
             */
            $orcamento->update(['total' => $total]);

            DB::commit();

            return redirect()->route('orcamentos.index')
                ->with('success', 'Orçamento criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage());
        }
    }



    /** REATIVAR ORÇAMENTO */
    public function reativar($id)
    {
        $orcamento = Orcamento::findOrFail($id);

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

        // Verifica se já está sendo editado por outro usuário
        if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
            $usuario = $orcamento->usuarioEditando; // relação opcional
            $nomeUsuario = $usuario->name ?? 'Outro usuário';
            return back()->with('error', "Este orçamento está sendo editado por: {$nomeUsuario}");
        }

        // Marca como editando pelo usuário atual
        $orcamento->editando_por = auth()->id();
        $orcamento->editando_em = now();
        $orcamento->save();

        $clientes = Cliente::where('ativo', 1)->orderBy('nome')->get();
        $produtos = Produto::with('unidadeMedida')->where('ativo', 1)->orderBy('nome')->get();

        return view('orcamentos.edit', compact('orcamento', 'clientes', 'produtos'));
    }

    /** ATUALIZA ORÇAMENTO */
    public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        // Validação de quem está editando
        if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
            return back()->with('error', 'Este orçamento está sendo editado por outro usuário.');
        }

        // Reseta a flag de edição ao iniciar a atualização
        $orcamento->editando_por = null;
        $orcamento->editando_em = null;
        $orcamento->save();

        if ($orcamento->status === 'Expirado') {
            $orcamento->status = 'Aguardando aprovacao';
            $orcamento->save();
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
                'observacoes' => $request->observacoes
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

        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }

    public function enviarWhatsApp($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $pdf = \PDF::loadView('orcamentos.pdf', compact('orcamento'));
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $pdfPath = storage_path("app/public/orcamento/{$fileName}");
        $pdf->save($pdfPath);

        $linkPdf = asset("storage/orcamento/{$fileName}");
        $mensagem = urlencode("Olá! Segue o seu orçamento: {$linkPdf}");
        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            return back()->with('error', 'O cliente não possui número cadastrado.');
        }

        return redirect()->away("https://wa.me/55{$telefone}?text={$mensagem}");
    }

    public function visualizarOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $linkPdf = asset("storage/orcamento/{$fileName}");

        return view('orcamentos.visualizar', compact('linkPdf', 'orcamento'));
    }

    /** LIMPAR EDIÇÃO */
    public function limparEdicao($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->editando_por = null;
        $orcamento->editando_em = null;
        $orcamento->save();

        return response()->json(['status' => 'ok']);
    }
}
