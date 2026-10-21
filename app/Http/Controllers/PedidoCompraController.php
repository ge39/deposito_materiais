<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\EstoqueService;
use App\Models\PedidoCompra;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\UnidadeMedida;
use App\Models\Lote;
use App\Models\Empresa;

class PedidoCompraController extends Controller
{
    /**
     * Regra financeira para aprovar e receber um pedido.
     * Nao altera saldos nem atualiza o pedido.
     */
    private function erroFinanceiro(PedidoCompra $pedido): ?string
    {
        $pedido->loadMissing('itens');

        if ($pedido->itens->isEmpty()) {
            return 'O pedido nao possui itens.';
        }

        $totalCalculado = 0.0;

        foreach ($pedido->itens as $item) {
            $quantidade = (float) $item->quantidade;
            $valor = (float) $item->valor_unitario;
            $subtotal = (float) $item->subtotal;
            $subtotalEsperado = round($quantidade * $valor, 2);

            if ($quantidade <= 0 || $valor <= 0 || $subtotal <= 0) {
                return 'O produto ' . $item->produto_id
                    . ' possui quantidade, valor unitario ou subtotal zerado.';
            }

            if (abs($subtotal - $subtotalEsperado) > 0.01) {
                return 'O subtotal do produto ' . $item->produto_id
                    . ' nao corresponde a quantidade multiplicada pelo preco.';
            }

            $totalCalculado += $subtotalEsperado;
        }

        if ((float) $pedido->total <= 0 ||
            abs((float) $pedido->total - round($totalCalculado, 2)) > 0.01) {
            return 'O valor total do pedido esta zerado ou divergente dos itens.';
        }

        return null;
    }

    /**
     * LISTA PEDIDOS
     */
    public function index()
    {
 
        $pedidos = PedidoCompra::with('fornecedor', 'user', 'itens')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * VIEW CREATE
     */
    public function create()
    {
        return view('pedidos.create', [
            'fornecedores' => Fornecedor::all(),
            'produtos' => Produto::all()
        ]);
    }

    /**
     * SALVAR PEDIDO
     */
    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido'   => 'required|date',
            'itens'         => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pedido = PedidoCompra::create([
                'user_id'       => auth()->id() ?? 1,
                'fornecedor_id' => $request->fornecedor_id,
                'data_pedido'   => $request->data_pedido,
                'status'        => 'pendente',
                'total'         => 0,
            ]);

            $total = 0;
            foreach ($request->itens as $item) {

                $subtotal = $item['quantidade'] * $item['valor_unitario'];

                $pedido->itens()->create([
                    'produto_id'        => $item['produto_id'],
                    'quantidade'        => $item['quantidade'],
                    'valor_unitario'    => $item['valor_unitario'],
                    'subtotal'          => $subtotal,
                ]);

                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido salvo com sucesso!');
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error($e->getMessage());

            return back()->withErrors('Erro ao salvar o pedido: ' . $e->getMessage());
        }
    }

    /**
     * EDITAR PEDIDO
     */
    public function edit($id)
    {
        return view('pedidos.edit', [
            'pedido'      => PedidoCompra::with('itens', 'fornecedor')->findOrFail($id),
            'fornecedores'=> Fornecedor::all(),
            'produtos'    => Produto::all(),
            'unidades'    => UnidadeMedida::all(),
        ]);
    }

    /**
     * APROVAR PEDIDO
     */
    public function aprovar($id)
    {
        $pedido = PedidoCompra::with('itens')->findOrFail($id);

        if ($pedido->status !== 'pendente') {
            return back()->withErrors(['pedido' => 'Aprovação só permitida para pedidos pendentes.']);
        }

        if ($erro = $this->erroFinanceiro($pedido)) {
            return back()->withErrors([
                'pedido' => 'Aprovação bloqueada: ' . $erro
                    . ' Corrija os valores antes de finalizar.'
            ]);
        }

        $pedido->update(['status' => 'aprovado']);

        return redirect()->route('pedidos.index')->with(
            'success',
            "Pedido #{$pedido->id} aprovado com sucesso!"
        );
    }

    /**
     * RECEBER PEDIDO + GERAR LOTES
     */
 
    public function receber(Request $request, $id, EstoqueService $estoqueService)
    {
        $itensRecebidos = $request->input('itens', []);

        if (!is_array($itensRecebidos) || empty($itensRecebidos)) {
            return back()->withErrors([
                'pedido' => 'Recebimento bloqueado: nenhum item enviado.'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $id, $estoqueService, $itensRecebidos) {
                $pedido = PedidoCompra::whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $pedido->load(['itens.produto', 'fornecedor']);

                if ($pedido->status !== 'aprovado') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'pedido' => 'Somente pedidos aprovados podem ser recebidos.'
                    ]);
                }

                if ($erro = $this->erroFinanceiro($pedido)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'pedido' => 'Recebimento bloqueado: ' . $erro
                    ]);
                }

                // Validar todos os itens ANTES da primeira criacao de lote.
                $precos = [];
                foreach ($pedido->itens as $item) {
                    $dados = $itensRecebidos[$item->id] ?? null;
                    if (!is_array($dados)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'pedido' => 'Dados de recebimento ausentes para o produto ' . $item->produto_id . '.'
                        ]);
                    }

                    $valorRecebido = $dados['preco_compra'] ?? null;
                    if ($valorRecebido === null || trim((string) $valorRecebido) === '') {
                        $valorRecebido = $item->valor_unitario;
                    }

                    $numero = str_replace(',', '.', trim((string) $valorRecebido));
                    if (!is_numeric($numero) || (float) $numero <= 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'pedido' => 'Preco de compra invalido ou zerado para o produto ' . $item->produto_id . '.'
                        ]);
                    }

                    $precos[$item->id] = round((float) $numero, 2);
                }

                foreach ($pedido->itens as $item) {
                    $dados = $itensRecebidos[$item->id];
                    $criarLote = (string) ($dados['criar_lote'] ?? '0');
                    $quantidade = max(0, (int) ($dados['quantidade_recebida'] ?? 0));
                    $validade = $dados['validade_lote'] ?? now()->addDays(30)->format('Y-m-d');
                    $status = ($criarLote === '1' && $quantidade > 0) ? 1 : 0;

                    $lote = Lote::create([
                        'numero_lote'           => 'TEMP',
                        'pedido_compra_id'      => $pedido->id,
                        'produto_id'            => $item->produto_id,
                        'fornecedor_id'         => $pedido->fornecedor_id,
                        'quantidade'            => $quantidade,
                        'quantidade_disponivel' => $quantidade,
                        'preco_compra'          => $precos[$item->id],
                        'data_compra'           => now()->format('Y-m-d'),
                        'lancado_por'           => auth()->id(),
                        'validade_lote'         => $validade,
                        'status'                => $status,
                    ]);

                    // Preservar a numeracao automatica ja homologada.
                    $lote->numero_lote = now()->format('Ymd') . $lote->id;
                    $lote->save();

                    if ($status === 1 && $quantidade > 0) {
                        $estoqueService->atenderPendentesPorProduto($item->produto_id);
                    }
                }

                $pedido->status = 'recebido';
                $pedido->save();
            });

            return redirect()->route('pedidos.index')
                ->with('success', 'Produtos recebidos e estoque atualizado!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erro ao receber pedido de compra', [
                'pedido_id' => $id,
                'erro' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'pedido' => 'Nao foi possivel receber o pedido. Nenhum recebimento foi confirmado.'
            ]);
        }
    }

    /**
     * CANCELAR
     */
    public function cancelar($id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        if (!in_array($pedido->status, ['pendente', 'aprovado'])) {
            return back()->withErrors('Cancelamento não permitido.');
        }

        $pedido->update(['status' => 'cancelado']);

        return redirect()->route('pedidos.index')->with(
            'success',
            "Pedido #{$pedido->id} cancelado."
        );
    }

    /**
     * PDF
     */
    public function gerarPdf($id)
    {
        $pedido = PedidoCompra::with(['fornecedor', 'itens.produto.unidadeMedida'])->findOrFail($id);
        $empresa = Empresa::where('ativo', true)->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pedidos.pdf', compact('pedido', 'empresa'));

        return $pdf->stream('pedido_' . $pedido->id . '.pdf');
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $pedido = PedidoCompra::with(['fornecedor', 'user', 'itens.produto'])->findOrFail($id);

        $totalGeral = $pedido->itens()->selectRaw('SUM(quantidade * valor_unitario) as total')->value('total');


        return view('pedidos.show', compact('pedido', 'totalGeral'));
    }

    public function receberForm($id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);

        // Garantir que apenas pedidos aprovados podem ser recebidos
        if ($pedido->status !== 'aprovado') {
            return redirect()->route('pedidos.index')
                ->withErrors('Somente pedidos aprovados podem ser recebidos.');
        }

        return view('pedidos.receber', compact('pedido'));
    }


    public function update(Request $request, $id)
    {
        $pedido = PedidoCompra::with('itens')->findOrFail($id);

        if (!in_array($pedido->status, ['pendente', 'aprovado'], true)) {
            return back()->withErrors([
                'pedido' => 'Pedidos recebidos ou cancelados nao podem ser editados.'
            ]);
        }

        $novoStatus = (string) $request->input('status', $pedido->status);
        if ($novoStatus !== $pedido->status) {
            return back()->withErrors([
                'pedido' => 'Altere o status exclusivamente pelas acoes de aprovar ou receber.'
            ]);
        }

        $dados = $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'itens' => 'required|array|min:1',
        ]);

        $itensValidados = [];
        foreach ($request->input('itens', []) as $item) {
            if (!isset($item['produto_id']) || !$item['produto_id']) {
                continue;
            }

            $quantidade = str_replace(',', '.', trim((string) ($item['quantidade'] ?? '')));
            $valor = str_replace(',', '.', trim((string) ($item['valor_unitario'] ?? '')));

            if (!is_numeric($quantidade) || (float) $quantidade <= 0 ||
                !is_numeric($valor) || (float) $valor < 0) {
                return back()->withErrors([
                    'pedido' => 'Quantidade ou valor unitario invalido nos itens do pedido.'
                ]);
            }

            if ($pedido->status === 'aprovado' && (float) $valor <= 0) {
                return back()->withErrors([
                    'pedido' => 'Nao e permitido editar um pedido aprovado com preco zerado.'
                ]);
            }

            $itensValidados[] = [
                'produto_id' => (int) $item['produto_id'],
                'quantidade' => (float) $quantidade,
                'valor_unitario' => round((float) $valor, 2),
            ];
        }

        if (!$itensValidados) {
            return back()->withErrors(['pedido' => 'O pedido precisa ter ao menos um item.']);
        }

        DB::transaction(function () use ($pedido, $request, $itensValidados) {
            $pedido->fornecedor_id = $request->fornecedor_id;
            $pedido->data_pedido = $request->data_pedido;
            $pedido->save();

            $pedido->itens()->delete();
            $total = 0.0;
            foreach ($itensValidados as $item) {
                $subtotal = round($item['quantidade'] * $item['valor_unitario'], 2);
                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $pedido->total = round($total, 2);
            $pedido->save();
            $pedido->unsetRelation('itens');

            if ($pedido->status === 'aprovado' &&
                ($erro = $this->erroFinanceiro($pedido))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'pedido' => 'Edicao bloqueada: ' . $erro
                ]);
            }
        });

        return redirect()->route('pedidos.edit', $pedido->id)
            ->with('success', 'Pedido atualizado com sucesso!');
    }

    /**
     * AJAX CARREGAR ITENS
     */
    public function carregarItensRecebimento($id)
    {
        return response()->json([
            'pedido' => PedidoCompra::with('itens.produto')->findOrFail($id)
        ]);
    }
    public function receberView($id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);
        return view('pedidos.receber', compact('pedido'));
    }
}