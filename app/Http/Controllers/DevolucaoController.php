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
    // public function index()
    // {
    //      $clientes = Cliente::orderBy('nome')->get();
    //       // Somente produtos já usados em alguma devolução
    //      $produtos = Produto::whereIn(
    //          'id',
    //          Devolucao::select('produto_id')
    //              ->distinct()
    //              ->pluck('produto_id')
    //      )->orderBy('nome')->get();

    //     // Lotes relacionados a esses produtos
   
    //      $lote = Lote::whereIn(
    //          'produto_id',
    //          Devolucao::distinct()->pluck('produto_id') // pega apenas os produtos que já tiveram devoluções
    //      )
    //      ->orderBy('id')
    //      ->pluck('id'); // retorna só os IDs dos lotes



    //      $vendas = Venda::with('cliente')->orderBy('id','desc')->get();
    //      $itens = collect();

    //      return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    // }

  public function index()
    {
        // Tela inicial vazia
        $itens = collect();
        $vendas = collect(); // Nenhuma venda mostrada

        // Dados para os selects de filtro (clientes, produtos e lotes)
        $clientes = Cliente::orderBy('nome')->get();

        $produtos = Produto::whereIn(
            'id',
            Devolucao::distinct()->pluck('produto_id')
        )->orderBy('nome')->get();

        $lotes = Lote::whereIn(
            'produto_id',
            Devolucao::distinct()->pluck('produto_id')
        )
        ->orderBy('id')
        ->pluck('id');

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function buscar(Request $request)
    {
        $search = $request->input('search');

        if (!$search) {
            // Campo de busca vazio: tela limpa
            $itens = collect();
            $vendas = collect();
            $clientes = Cliente::orderBy('nome')->get();
            $produtos = Produto::whereIn(
                'id',
                Devolucao::distinct()->pluck('produto_id')
            )->orderBy('nome')->get();
            $lotes = Lote::whereIn(
                'produto_id',
                Devolucao::distinct()->pluck('produto_id')
            )
            ->orderBy('id')
            ->pluck('id');

            return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
        }

        $vendas = DB::table('vendas')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->select(
                'vendas.id as venda_id',
                'clientes.nome as cliente_nome',
                'vendas.data_venda',
                'vendas.total as valor_total',
                DB::raw('(SELECT SUM(quantidade) FROM venda_itens WHERE venda_itens.venda_id = vendas.id) as quantidade_comprada'),
                DB::raw('(SELECT COALESCE(SUM(quantidade),0) FROM devolucoes WHERE devolucoes.venda_id = vendas.id) as quantidade_devolvida'),
                DB::raw('((SELECT SUM(quantidade) FROM venda_itens WHERE venda_itens.venda_id = vendas.id) - 
                        (SELECT COALESCE(SUM(quantidade),0) FROM devolucoes WHERE devolucoes.venda_id = vendas.id)) as quantidade_disponivel'),
                DB::raw('(SELECT COALESCE(SUM(d.quantidade * p.preco_venda),0)
                        FROM devolucoes d
                        JOIN venda_itens vi ON vi.id = d.venda_item_id
                        JOIN produtos p ON p.id = vi.produto_id
                        WHERE d.venda_id = vendas.id) as valor_extornado')
            )
            ->where('vendas.id', $search)
            ->orWhere('clientes.nome', 'like', "%{$search}%")
            ->orderByDesc('vendas.id')
            ->paginate(10);

        $vendas->appends(['search' => $search]);

        // Mantém os dados dos filtros
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::whereIn(
            'id',
            Devolucao::distinct()->pluck('produto_id')
        )->orderBy('nome')->get();
        $lotes = Lote::whereIn(
            'produto_id',
            Devolucao::distinct()->pluck('produto_id')
        )
        ->orderBy('id')
        ->pluck('id');

        $itens = collect(); // Mantendo consistência com o index()

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }


    // public function registrar(int $item_id)
    // {
    //     $item = VendaItem::with(['venda.cliente', 'produto', 'lote'])->findOrFail($item_id);
    //     $venda = $item->venda;

    //     return view('devolucoes.registrar', compact('item', 'venda'));
    // }
    public function registrar($venda_id)
    {
        $venda = Venda::with(['itens.produto', 'itens.lote', 'itens.devolucoes'])
            ->where('id', $venda_id)
            ->firstOrFail();

        return view('devolucoes.registrar', compact('venda'));
    }


    // public function salvar(Request $request)
    // {
    //     $request->validate([
    //         'item_id' => 'required|exists:venda_itens,id',
    //         'quantidade' => 'nullable|numeric|min:1',
    //         'completo' => 'nullable|boolean',
    //         'motivo' => 'required|string|max:255',
    //         'imagem1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     DB::beginTransaction();

        
    //         try {
    //         $itemVenda = VendaItem::findOrFail($request->item_id);
    //         $qtdeDisponivel = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;

    //         // Verificação: bloqueia se já existe devolução pendente ou rejeitada
    //         $pedidoExistente = Devolucao::where('venda_item_id', $itemVenda->id)
    //             ->whereIn('status', ['pendente','rejeitada'])
    //             ->exists();

    //         if ($pedidoExistente) {
    //             return back()->with('error', 'Já existe um pedido de devolução pendente ou rejeitado para este item da venda. Aguarde aprovação ou conclusão.');
    //         }

    //         // Define a quantidade a devolver
    //         if ($request->has('completo') && $request->completo) {
    //             $quantidadeDevolver = $qtdeDisponivel;
    //         } else {
    //             $quantidadeDevolver = $request->quantidade ?? 0;
    //             if ($quantidadeDevolver > $qtdeDisponivel) {
    //                 return back()->with('error', 'Quantidade informada excede o limite permitido.');
    //             }
    //         }
    //         $imagens = [];
    //         for ($i = 1; $i <= 4; $i++) {
    //             $campo = 'imagem' . $i;
    //             $imagens[$campo] = $request->hasFile($campo)
    //                 ? $request->file($campo)->store('devolucoes', 'public')
    //                 : null;
    //         }

    //         // Cria a devolução
    //         $devolucao = Devolucao::create([
    //             'cliente_id' => $itemVenda->venda->cliente_id,
    //             'venda_id' => $itemVenda->venda_id,
    //             'venda_item_id' => $itemVenda->id,
    //             'produto_id' => $itemVenda->produto_id,
    //             'quantidade' => $quantidadeDevolver,
    //             'motivo' => $request->motivo,
    //             'status' => 'pendente',
    //             'imagem1' => $imagens['imagem1'],
    //             'imagem2' => $imagens['imagem2'],
    //             'imagem3' => $imagens['imagem3'],
    //             'imagem4' => $imagens['imagem4'],
    //         ]);

    //         // Log: devolução registrada
    //         DevolucaoLog::create([
    //             'devolucao_id' => $devolucao->id,
    //             'acao' => 'registrada',
    //             'descricao' => 'Devolução registrada pelo cliente. Aguardando aprovação.',
    //             'usuario' => 'Sistema',
    //         ]);

    //         DB::commit();

    //         return redirect()->route('devolucoes.pendentes')
    //             ->with('success', 'Devolução registrada com sucesso e aguardando aprovação.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
    //     }
    // }

    // public function salvar(Request $request)
    // {
    //     $request->validate([
    //         'item_id' => 'required|exists:venda_itens,id',
    //         'quantidade' => 'nullable|numeric|min:1',
    //         'completo' => 'nullable|boolean',
    //         'motivo' => 'required|string|max:255',
    //         'imagem1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'imagem4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $itemVenda = VendaItem::findOrFail($request->item_id);
    //         $qtdeDisponivel = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;

    //         // Verificação: bloqueia se já existe devolução pendente ou rejeitada
    //         $pedidoExistente = Devolucao::where('venda_item_id', $itemVenda->id)
    //             ->whereIn('status', ['pendente','rejeitada'])
    //             ->exists();

    //         if ($pedidoExistente) {
    //             return back()->with('error', 'Já existe um pedido de devolução pendente ou rejeitado para este item da venda. Aguarde aprovação ou conclusão.');
    //         }

    //         // Define a quantidade a devolver
    //         if ($request->has('completo') && $request->completo) {
    //             $quantidadeDevolver = $qtdeDisponivel;
    //         } else {
    //             $quantidadeDevolver = $request->quantidade ?? 0;
    //             if ($quantidadeDevolver > $qtdeDisponivel) {
    //                 return back()->with('error', 'Quantidade informada excede o limite permitido.');
    //             }
    //         }

    //         // Processa imagens
    //         $imagens = [];
    //         for ($i = 1; $i <= 4; $i++) {
    //             $campo = 'imagem' . $i;
    //             $imagens[$campo] = $request->hasFile($campo)
    //                 ? $request->file($campo)->store('devolucoes', 'public')
    //                 : null;
    //         }

    //         // Cria a devolução
    //         $devolucao = Devolucao::create([
    //             'cliente_id' => $itemVenda->venda->cliente_id,
    //             'venda_id' => $itemVenda->venda_id,
    //             'venda_item_id' => $itemVenda->id,
    //             'produto_id' => $itemVenda->produto_id,
    //             'quantidade' => $quantidadeDevolver,
    //             'motivo' => $request->motivo,
    //             'tipo' => $request->tipo ?? 'devolucao',
    //             'status' => 'pendente',
    //             'imagem1' => $imagens['imagem1'],
    //             'imagem2' => $imagens['imagem2'],
    //             'imagem3' => $imagens['imagem3'],
    //             'imagem4' => $imagens['imagem4'],
    //         ]);

    //         // Log
    //         DevolucaoLog::create([
    //             'devolucao_id' => $devolucao->id,
    //             'acao' => 'registrada',
    //             'descricao' => 'Devolução registrada pelo cliente. Aguardando aprovação.',
    //             'usuario' => 'Sistema',
    //         ]);

    //         DB::commit();

    //         return redirect()->route('devolucoes.pendentes')
    //             ->with('success', 'Devolução registrada com sucesso e aguardando aprovação.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
    //     }
    // }

    public function salvar(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:venda_itens,id',
            'quantidade' => 'nullable|numeric|min:1',
            'completo' => 'nullable|boolean',
            'motivo' => 'required|string|max:255',
            'motivo_outro' => 'nullable|string|max:255',
            'imagem1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // normalizar motivo final (se usar "Outro motivo")
        $motivoSelecionado = $request->motivo;
        $motivoFinal = ($motivoSelecionado === 'Outro motivo' || $motivoSelecionado === 'Outro')
            ? ($request->motivo_outro ?? $motivoSelecionado)
            : $motivoSelecionado;

        // checagem rápida antes de começar a transação
        $existingPending = Devolucao::where('venda_item_id', $request->item_id)
            ->where('status', 'pendente')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Já existe uma devolução pendente para este item. Aguarde a análise antes de registrar outra.');
        }

        DB::beginTransaction();
        try {
            // rechecagem dentro da transação (protege contra concorrência simples)
            $existingPending = Devolucao::where('venda_item_id', $request->item_id)
                ->where('status', 'pendente')
                ->lockForUpdate() // opcional: tenta bloquear os registros (se existirem)
                ->exists();

            if ($existingPending) {
                DB::rollBack();
                return back()->with('error', 'Já existe uma devolução pendente para este item (verificação final).');
            }

            $itemVenda = VendaItem::findOrFail($request->item_id);
            $qtdeDisponivel = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;

            if ($request->has('completo') && $request->completo) {
                $quantidadeDevolver = $qtdeDisponivel;
            } else {
                $quantidadeDevolver = $request->quantidade ?? 0;
                if ($quantidadeDevolver > $qtdeDisponivel) {
                    DB::rollBack();
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
                'motivo' => $motivoFinal,
                'status' => 'pendente',
                'imagem1' => $imagens['imagem1'],
                'imagem2' => $imagens['imagem2'],
                'imagem3' => $imagens['imagem3'],
                'imagem4' => $imagens['imagem4'],
            ]);

            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'registrada',
                'descricao' => 'Devolução registrada pelo cliente. Aguardando aprovação.',
                'usuario' => auth()->user()->name ?? 'Sistema',
            ]);

            DB::commit();

            return redirect()->route('devolucoes.pendentes')
                ->with('success', 'Devolução registrada com sucesso e aguardando aprovação.');

        } catch (\Exception $e) {
            DB::rollBack();
            // opcional: logar $e->getMessage() em log de aplicação
            return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
        }
    }

    public function show(PedidoCompra $pedido)
    {
        // Carrega os itens, os produtos e a unidade de medida de cada produto
        $pedido->load('fornecedor', 'user', 'itens.produto.unidade');

        return view('pedidos.show', compact('pedido'));
    }

//    public function aprovar(Devolucao $devolucao)
//     {
//         DB::transaction(function () use ($devolucao) {
//             // Atualiza status da devolução
//             $devolucao->status = 'aprovada';
//             $devolucao->save();

//             // Atualiza item da venda
//             $item = $devolucao->vendaItem;
//             if ($item) {
//                 $item->quantidade_devolvida += $devolucao->quantidade;
//                 $item->quantidade -= $devolucao->quantidade;
//                 $item->save(); // MySQL recalcula subtotal automaticamente
//             }

//             // Atualiza estoque do produto
//             $produto = $devolucao->produto;
//             if ($produto) {
//                 $produto->quantidade_estoque += $devolucao->quantidade;
//                 $produto->save();
//             }

//             // Log: devolução aprovada
//             DevolucaoLog::create([
//                 'devolucao_id' => $devolucao->id,
//                 'acao' => 'aprovada',
//                 'descricao' => 'Devolução aprovada, estoque e subtotal atualizados.',
//                 'usuario' => auth()->user()->name ?? 'Administrador',
//             ]);
//         });

//         return redirect()->route('devolucoes.index')
//             ->with('success', 'Devolução aprovada, subtotal e estoque atualizados com sucesso!');
//     }

    public function aprovar(Devolucao $devolucao)
    {
        DB::transaction(function () use ($devolucao) {

            // Atualiza status da devolução
            $devolucao->status = 'aprovada';
            $devolucao->save();

            // Atualiza estoque do produto
            $produto = $devolucao->produto;
            if ($produto) {
                $produto->quantidade_estoque += $devolucao->quantidade;
                $produto->save();
            }

            // Log
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'aprovada',
                'descricao' => 'Devolução aprovada e estoque atualizado.',
                'usuario' => auth()->user()->name ?? 'Administrador',
            ]);
        });

        return redirect()->route('devolucoes.index')
            ->with('success', 'Devolução aprovada com sucesso!');
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
