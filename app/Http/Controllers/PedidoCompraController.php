<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoCompra;        // Modelo principal do pedido
use App\Models\Fornecedor;          // Fornecedor
use App\Models\Produto;             // Produtos
use App\Models\UnidadeMedida;       // Unidades de medida
use App\Models\Lote;                // Lotes
use App\Models\User;                // Usuários (ex: funcionários)
use App\Models\Orcamento;           // Caso use orçamentos relacionados
use Illuminate\Support\Facades\DB;  // Para queries diretas, se houver
use Illuminate\Support\Facades\Auth; // Para usuário autenticado

class PedidoCompraController extends Controller
{
    /**
     * Listar pedidos de compra
     */
    public function index()
    {
        $pedidos = PedidoCompra::with('fornecedor', 'user', 'itens')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pedidos.index', compact('pedidos'));
    }
    public function edit($id)
    {
        $pedido = PedidoCompra::with('itens', 'fornecedor')->findOrFail($id); // carrega o pedido

        $fornecedores = Fornecedor::all(); // ou outros dados necessários
        $produtos = Produto::all();
        $unidades = UnidadeMedida::all();

        return view('pedidos.edit', compact('pedido', 'fornecedores', 'produtos', 'unidades'));
    }

    /**
     * Tela de criação de pedido
     */
    public function create()
    {
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        return view('pedidos.create', compact('fornecedores', 'produtos'));
    }

    /**
     * Armazenar pedido no banco
     */
    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pedido = PedidoCompra::create([
                'user_id' => auth()->id() ?? 1,
                'fornecedor_id' => $request->fornecedor_id,
                'data_pedido' => $request->data_pedido,
                'status' => 'pendente',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($request->itens as $item) {
                $subtotal = $item['quantidade'] * $item['valor_unitario'];
                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'pedido_compra_id' => $pedido->id,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $subtotal,
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
     * Aprovar pedido
     */
    public function aprovar($id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        if ($pedido->status !== 'pendente') {
            return redirect()->back()->withErrors('Aprovação só é permitida para pedidos pendentes.');
        }

        $pedido->update(['status' => 'aprovado']);
        return redirect()->route('pedidos.index')->with('success', "Pedido #{$pedido->id} aprovado com sucesso!");
    }

    /**
     * Receber pedido e gerar lotes
     */
    public function receber($id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);

        DB::transaction(function () use ($pedido) {
            foreach ($pedido->itens as $item) {
                $produto = $item->produto;

                // Atualiza o estoque do produto
                $produto->quantidade_estoque = ($produto->quantidade_estoque ?? 0) + $item->quantidade;
                $produto->save();

                // Criação do lote completo
                Lote::create([
                    'pedido_compra_id' => $pedido->id,
                    'produto_id'       => $produto->id,
                    'fornecedor_id'    => $pedido->fornecedor_id,
                    'quantidade'       => $item->quantidade,
                    'preco_compra'     => $item->valor_unitario,
                    'data_compra'      => $pedido->data_pedido,
                    'validade'         => now()->addMonths(12),
                    'numero_lote'      => now()->format('Ymd') . '-' . $produto->id,
                ]);
            }

            $pedido->update(['status' => 'recebido']);
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido recebido e lotes gerados com sucesso!');
    }

    /**
     * Cancelar pedido
     */
    public function cancelar($id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        if (!in_array($pedido->status, ['pendente', 'aprovado'])) {
            return redirect()->back()->withErrors('Cancelamento não permitido para pedidos recebidos ou já cancelados.');
        }

        $pedido->update(['status' => 'cancelado']);
        return redirect()->route('pedidos.index')->with('success', "Pedido #{$pedido->id} cancelado.");
    }

    /**
     * Gerar PDF do pedido
     */
    public function gerarPdf($id)
    {
        $pedido = PedidoCompra::with(['fornecedor', 'itens.produto.unidadeMedida'])->findOrFail($id);
        $empresa = Empresa::where('ativo', true)->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pedidos.pdf', compact('pedido', 'empresa'));
        return $pdf->stream('pedido_' . $pedido->id . '.pdf');
    }

    public function show($id)
    {
        $pedido = PedidoCompra::with(['fornecedor', 'user', 'itens.produto'])->findOrFail($id);
        return view('pedidos.show', compact('pedido'));
    }
}
