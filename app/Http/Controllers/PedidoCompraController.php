<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use PDF;
use Illuminate\Http\Request;
use App\Models\PedidoCompra;
use App\Models\PedidoItem;
use App\Models\Fornecedor;
use App\Models\Produto;

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
                'user_id' => auth()->id() ?? 1, // usuário logado ou 1 se não existir
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
     * Tela de edição de pedido
     */
    public function edit(PedidoCompra $pedido)
    {
        $fornecedores = Fornecedor::all();
        $produtos = Produto::all();
        $pedido->load('itens.produto.unidadeMedida', 'fornecedor');

        return view('pedidos.edit', compact('pedido', 'fornecedores', 'produtos'));
    }

    /**
     * Atualizar pedido e validar fluxo de status
     */
    public function update(Request $request, PedidoCompra $pedido)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'data_pedido' => 'required|date',
            'status' => 'required|string',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        $fluxoStatus = [
            'pendente' => ['pendente', 'aprovado', 'cancelado'],
            'aprovado' => ['aprovado', 'recebido', 'cancelado'],
            'recebido' => ['recebido'],
            'cancelado' => ['cancelado'],
        ];

        if (!in_array($request->status, $fluxoStatus[$pedido->status] ?? [])) {
            return redirect()->back()
                ->withErrors(['status' => 'Alteração de status não permitida.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $pedido->update([
                'fornecedor_id' => $request->fornecedor_id,
                'data_pedido' => $request->data_pedido,
                'status' => $request->status,
            ]);

            $pedido->itens()->delete();

            $total = 0;
            foreach ($request->itens as $itemData) {
                $subtotal = $itemData['quantidade'] * $itemData['valor_unitario'];
                $pedido->itens()->create([
                    'produto_id' => $itemData['produto_id'],
                    'quantidade' => $itemData['quantidade'],
                    'valor_unitario' => $itemData['valor_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            DB::commit();
            return redirect()->route('pedidos.edit', $pedido->id)
                ->with('success', 'Pedido atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return back()->withErrors('Erro ao atualizar o pedido: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar pedido
     */
    public function show(PedidoCompra $pedido)
    {
        $pedido->load('itens.produto.unidadeMedida', 'fornecedor', 'user');
        return view('pedidos.show', compact('pedido'));
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
        return $pdf->stream('pedido_'.$pedido->id.'.pdf');
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
     * Receber pedido e atualizar estoque
     */
    public function receber($id)
    {
        $pedido = PedidoCompra::with('itens.produto')->findOrFail($id);

        if ($pedido->status !== 'aprovado') {
            return redirect()->back()->withErrors('Recebimento só permitido para pedidos aprovados.');
        }

        try {
            $pedido->receberProdutos(); // método na model PedidoCompra
            return redirect()->route('pedidos.index')
                            ->with('success', "Pedido #{$pedido->id} recebido e estoque atualizado!");
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
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
}
