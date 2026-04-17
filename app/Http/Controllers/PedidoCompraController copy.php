<?php

namespace App\Http\Controllers;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use App\Models\PedidoCompra;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\UnidadeMedida;
use App\Models\Lote;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class PedidoCompraController extends Controller
{
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
                    'pedido_compra_id'  => $pedido->id,
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
            \Log::error($e->getMessage());

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
        $pedido = PedidoCompra::findOrFail($id);

        if ($pedido->status !== 'pendente') {
            return back()->withErrors('Aprovação só permitida para pedidos pendentes.');
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
 
    public function receber(Request $request, $id)
    {
        
        $pedido = PedidoCompra::with('itens.produto', 'fornecedor')->findOrFail($id);
       
        if (!$request->has('itens') || empty($request->itens)) {
            return back()->with('error', 'Nenhum item foi enviado.');
        }

        DB::beginTransaction();

        try {
            foreach ($pedido->itens as $item) {

                $dados = $request->itens[$item->id] ?? null;
                if (!$dados) continue;

                $criarLote = $dados['criar_lote'] ?? "0";
                $quantidade = max(0, (int) ($dados['quantidade_recebida'] ?? 0));
                $validade = $dados['validade_lote'] ?? now()->addDays(30)->format('Y-m-d');
                $status = ($criarLote === "1" && $quantidade > 0) ? 1 : 0;

                // Criar lote com número temporário
                $lote = Lote::create([
                    'numero_lote'           => 'TEMP',
                    'pedido_compra_id'      => $pedido->id,
                    'produto_id'            => $item->produto_id,
                    'fornecedor_id'         => $pedido->fornecedor_id,
                    'quantidade'            => $quantidade,
                    'quantidade_disponivel' => $quantidade,
                    'preco_compra'          => $dados['preco_compra'] ?? $item->preco_compra,
                    'data_compra'           => now()->format('Y-m-d'),
                    'lancado_por'           => auth()->id(),
                    'validade_lote'         => $validade,
                    'status'                => $status,
                ]);

                // Atualizar numero_lote para: AAAAMMDD-ID
                $lote->numero_lote = now()->format('Ymd') . $lote->id;
                $lote->save();

              // Atualiza status do pedido para recebido
              $pedido->status = 'recebido';
              $pedido->save();

            }

            DB::commit();

            return redirect()->route('pedidos.index')
                ->with('success', 'Produtos recebidos e lotes criados com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao receber produtos: ' . $e->getMessage());
        }
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }
        
    }
    // public function receber(Request $request, $id)
    // {
    //     $estoqueService = new \App\Services\EstoqueService();
    //     $pedido = PedidoCompra::with('itens.produto', 'fornecedor')->findOrFail($id);
        
    //     if (!$request->has('itens') || empty($request->itens)) {
    //         return back()->with('error', 'Nenhum item foi enviado.');
    //     }

    //     DB::beginTransaction();

    //     try {
    //         foreach ($pedido->itens as $item) {

    //             $dados = $request->itens[$item->id] ?? null;
    //             if (!$dados) continue;

    //             $criarLote = $dados['criar_lote'] ?? "0";
    //             $quantidade = max(0, (int) ($dados['quantidade_recebida'] ?? 0));
    //             $validade = $dados['validade_lote'] ?? now()->addDays(30)->format('Y-m-d');
    //             $status = ($criarLote === "1" && $quantidade > 0) ? 1 : 0;

    //             // 🔹 Criar lote
    //             $lote = Lote::create([
    //                 'numero_lote'           => 'TEMP',
    //                 'pedido_compra_id'      => $pedido->id,
    //                 'produto_id'            => $item->produto_id,
    //                 'fornecedor_id'         => $pedido->fornecedor_id,
    //                 'quantidade'            => $quantidade,
    //                 'quantidade_disponivel' => $quantidade,
    //                 'preco_compra'          => $dados['preco_compra'] ?? $item->preco_compra,
    //                 'data_compra'           => now()->format('Y-m-d'),
    //                 'lancado_por'           => auth()->id(),
    //                 'validade_lote'         => $validade,
    //                 'status'                => $status,
    //             ]);

    //             $lote->numero_lote = now()->format('Ymd') . $lote->id;
    //             $lote->save();

    //             // 🔥 AQUI ESTÁ A INTEGRAÇÃO
    //             if ($status == 1 && $quantidade > 0) {
    //                 $estoqueService->atenderPendentes($item->produto_id);
    //             }
    //         }

    //         // 🔥 Atualiza status fora do loop (melhor prática)
    //         $pedido->status = 'recebido';
    //         $pedido->save();

    //         DB::commit();

    //         return redirect()->route('pedidos.index')
    //             ->with('success', 'Produtos recebidos e estoque atualizado!');

    //     // } catch (\Exception $e) {
    //     //     DB::rollBack();
    //     //     return back()->with('error', 'Erro ao receber produtos: ' . $e->getMessage());
    //     // }
    //     } catch (\Exception $e) {
    //         dd( $e->getMessage());
    //     }
    // }

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

        // Atualiza dados principais
        $pedido->fornecedor_id = $request->fornecedor_id;
        $pedido->data_pedido   = $request->data_pedido;
        $pedido->status        = $request->status ?? $pedido->status;
        $pedido->save();

        // Remove itens antigos
        $pedido->itens()->delete();

        // Reinsere itens atualizados
        if ($request->has('itens')) {
            foreach ($request->itens as $item) {

                // Ignora linhas vazias
                if (!isset($item['produto_id']) || empty($item['produto_id'])) {
                    continue;
                }

                $valor = str_replace(',', '.', $item['valor_unitario']);

                $pedido->itens()->create([
                    'produto_id'     => $item['produto_id'],
                    'quantidade'     => $item['quantidade'],
                    'valor_unitario' => $valor,
                    'subtotal'       => $item['quantidade'] * $valor,
                ]);
            }
        }

        // Atualiza total geral
        $total = $pedido->itens()->sum(\DB::raw('quantidade * valor_unitario'));
        $pedido->total = $total;
        $pedido->save();

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