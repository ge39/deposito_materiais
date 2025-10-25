<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Devolucao;
use App\Models\DevolucaoLog;
use App\Models\PedidoCompra;
use Illuminate\Support\Facades\DB;


class DevolucaoController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $produtos = Produto::with('unidade')->get();
        $lotes = Lote::orderBy('id')->get();
        $vendas = Venda::with('cliente')->orderBy('id')->get();
        $itens = collect();

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function buscar(Request $request)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $produtos = Produto::with('unidade')->get();
        $lotes = Lote::orderBy('id')->get();
        $vendas = Venda::with('cliente')->orderBy('id')->get();

        $itensQuery = VendaItem::query();

        if ($request->filled('venda_id')) {
            $itensQuery->where('venda_id', $request->venda_id);
        } else {
            if ($request->filled('cliente_id')) {
                $vendasCliente = Venda::where('cliente_id', $request->cliente_id)->pluck('id');
                $itensQuery->whereIn('venda_id', $vendasCliente);
            }
            if ($request->filled('produto_id')) {
                $itensQuery->where('produto_id', $request->produto_id);
            }
            if ($request->filled('lote_id')) {
                $itensQuery->where('lote_id', $request->lote_id);
            }
        }

        $itens = $itensQuery->get();

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function registrar(int $item_id)
    {
        $item = VendaItem::with(['venda.cliente', 'produto', 'lote'])->findOrFail($item_id);
        $venda = $item->venda;

        return view('devolucoes.registrar', compact('item', 'venda'));
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:venda_itens,id',
            'quantidade' => 'nullable|numeric|min:1',
            'completo' => 'nullable|boolean',
            'motivo' => 'required|string|max:255',
            'imagem1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $itemVenda = VendaItem::findOrFail($request->item_id);
            $qtdeDisponivel = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;

            if ($request->has('completo') && $request->completo) {
                $quantidadeDevolver = $qtdeDisponivel;
            } else {
                $quantidadeDevolver = $request->quantidade ?? 0;
                if ($quantidadeDevolver > $qtdeDisponivel) {
                    return back()->with('error', 'Quantidade informada excede o limite permitido.');
                }
            }

            $imagens = [];
            for ($i = 1; $i <= 4; $i++) {
                $campo = 'imagem' . $i;
                $imagens[$campo] = $request->hasFile($campo)
                    ? $request->file($campo)->store('devolucoes', 'public')
                    : null;
            }

            // Cria a devolução
            $devolucao = Devolucao::create([
                'cliente_id' => $itemVenda->venda->cliente_id,
                'venda_id' => $itemVenda->venda_id,
                'venda_item_id' => $itemVenda->id,
                'produto_id' => $itemVenda->produto_id,
                'quantidade' => $quantidadeDevolver,
                'motivo' => $request->motivo,
                'status' => 'pendente',
                'imagem1' => $imagens['imagem1'],
                'imagem2' => $imagens['imagem2'],
                'imagem3' => $imagens['imagem3'],
                'imagem4' => $imagens['imagem4'],
            ]);

            // Log: devolução registrada
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'registrada',
                'descricao' => 'Devolução registrada pelo cliente. Aguardando aprovação.',
                'usuario' => 'Sistema',
            ]);

            DB::commit();

            return redirect()->route('devolucoes.index')
                ->with('success', 'Devolução registrada com sucesso e aguardando aprovação.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
        }
    }
    public function show(PedidoCompra $pedido)
    {
        // Carrega os itens, os produtos e a unidade de medida de cada produto
        $pedido->load('fornecedor', 'user', 'itens.produto.unidade');

        return view('pedidos.show', compact('pedido'));
    }

   public function aprovar(Devolucao $devolucao)
    {
        DB::transaction(function () use ($devolucao) {
            // Atualiza status da devolução
            $devolucao->status = 'aprovada';
            $devolucao->save();

            // Atualiza item da venda
            $item = $devolucao->vendaItem;
            if ($item) {
                $item->quantidade_devolvida += $devolucao->quantidade;
                $item->quantidade -= $devolucao->quantidade;
                $item->save(); // MySQL recalcula subtotal automaticamente
            }

            // Atualiza estoque do produto
            $produto = $devolucao->produto;
            if ($produto) {
                $produto->quantidade_estoque += $devolucao->quantidade;
                $produto->save();
            }

            // Log: devolução aprovada
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'aprovada',
                'descricao' => 'Devolução aprovada, estoque e subtotal atualizados.',
                'usuario' => auth()->user()->name ?? 'Administrador',
            ]);
        });

        return redirect()->route('devolucoes.index')
            ->with('success', 'Devolução aprovada, subtotal e estoque atualizados com sucesso!');
    }


    public function rejeitar(Devolucao $devolucao)
    {
        DB::transaction(function () use ($devolucao) {
            $devolucao->status = 'rejeitada';
            $devolucao->save();

            // Log: devolução rejeitada
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'rejeitada',
                'descricao' => 'Devolução rejeitada pelo setor responsável.',
                'usuario' => auth()->user()->name ?? 'Administrador',
            ]);
        });

        return redirect()->route('devolucoes.index')->with('success', 'Devolução rejeitada com sucesso!');
    }

    public function pendentes()
    {
        $devolucoes = Devolucao::with(['vendaItem', 'vendaItem.venda', 'vendaItem.produto', 'vendaItem.lote'])
            ->where('status', 'pendente')
            ->whereHas('vendaItem')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('devolucoes.pendentes', compact('devolucoes'));
    }
    
    public function gerarCupom($id)
        {
        // Buscar a devolução
        $devolucao = Devolucao::findOrFail($id);

        // Buscar venda relacionada
        $venda = Venda::with('itens.produto')->find($devolucao->venda_id);

        // Buscar cliente
        $cliente = Cliente::find($venda->cliente_id ?? $devolucao->cliente_id);

        // Itens da venda
        $itens = $venda ? $venda->itens : [];

        // Gerar PDF
        $pdf = Pdf::loadView('devolucoes.cupom', compact('devolucao', 'venda', 'cliente', 'itens'));

        return $pdf->stream('cupom_devolucao_'.$devolucao->id.'.pdf');
    }
}
