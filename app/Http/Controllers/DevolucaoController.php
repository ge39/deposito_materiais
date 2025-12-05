<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\Devolucao;
use App\Models\DevolucaoLog;
use App\Models\PedidoCompra;
use Illuminate\Support\Facades\DB;

class DevolucaoController extends Controller
{
    public function index()
    {
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

    public function buscar(Request $request)
    {
        $search = $request->input('search');

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

        if (!$search) {
            $itens = collect();
            $vendas = collect();
            return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
        }

        $vendas = DB::table('vendas')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->select(
                'vendas.id as venda_id',
                'clientes.nome as cliente_nome',
                'clientes.cpf_cnpj as cliente_cpf_cnpj',
                'clientes.tipo as cliente_tipo',
                'vendas.data_venda',
                'vendas.total as valor_total',
                DB::raw('(SELECT SUM(quantidade) FROM Item_Vendas WHERE Item_Vendas.venda_id = vendas.id) as quantidade_comprada'),
                DB::raw('(SELECT COALESCE(SUM(quantidade),0) FROM devolucoes WHERE devolucoes.venda_id = vendas.id) as quantidade_devolvida'),
                DB::raw('((SELECT SUM(quantidade) FROM Item_Vendas WHERE Item_Vendas.venda_id = vendas.id) - 
                        (SELECT COALESCE(SUM(quantidade),0) FROM devolucoes WHERE devolucoes.venda_id = vendas.id)) as quantidade_disponivel'),
                DB::raw('(SELECT COALESCE(SUM(d.quantidade * p.preco_venda),0)
                        FROM devolucoes d
                        JOIN Item_Vendas vi ON vi.id = d.venda_item_id
                        JOIN produtos p ON p.id = vi.produto_id
                        WHERE d.venda_id = vendas.id) as valor_extornado')
            )
            ->where('vendas.id', $search)
            ->orWhere('clientes.nome', 'like', "%{$search}%")
            ->orderByDesc('vendas.id')
            ->paginate(10);

        $vendas->appends(['search' => $search]);
        $itens = collect();

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function registrar($venda_id)
    {
        $venda = Venda::with(['itens.produto', 'itens.lote', 'itens.devolucoes'])
            ->where('id', $venda_id)
            ->firstOrFail();

        $temPendente = $venda->itens->some(function ($item) {
            return $item->devolucoes->contains('status', 'Pendente');
        });

        if ($temPendente) {
            $msg = '
                <div class="alert alert-danger d-flex justify-content-between align-items-center" style="font-size: 15px;">
                    <div>
                        <strong>Atenção!</strong><br>
                        Já existe uma devolução pendente para esta venda.<br>
                        Finalize a devolução pendente antes de abrir uma nova.
                        Clique no botão ao lado para ver as devoluções pendentes.
                    </div>
                    <a href="/devolucoes/pendentes" class="btn btn-sm btn-primary fw-bold shadow-sm" style="white-space: nowrap;">
                        Ver pendentes
                    </a>
                </div>
            ';

            return redirect()->back()->with('error', $msg);
        }

        return view('devolucoes.registrar', compact('venda'));
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:Item_Vendas,id',
            'quantidade' => 'nullable|numeric|min:1',
            'completo' => 'nullable|boolean',
            'motivo' => 'required|string|max:255',
            'motivo_outro' => 'nullable|string|max:255',
            'imagem1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imagem4' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $motivoSelecionado = $request->motivo;
        $motivoFinal = ($motivoSelecionado === 'Outro motivo' || $motivoSelecionado === 'Outro')
            ? ($request->motivo_outro ?? $motivoSelecionado)
            : $motivoSelecionado;

        $existingPending = Devolucao::where('venda_item_id', $request->item_id)
            ->where('status', 'pendente')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Já existe uma devolução pendente para este item. Aguarde a análise antes de registrar outra.');
        }

        DB::beginTransaction();
        try {
            $existingPending = Devolucao::where('venda_item_id', $request->item_id)
                ->where('status', 'pendente')
                ->lockForUpdate()
                ->exists();

            if ($existingPending) {
                DB::rollBack();
                return back()->with('error', 'Já existe uma devolução pendente para este item (verificação final).');
            }

            $itemVenda = ItemVenda::findOrFail($request->item_id);
            $qtdeDisponivel = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;

            $quantidadeDevolver = ($request->has('completo') && $request->completo) 
                ? $qtdeDisponivel 
                : ($request->quantidade ?? 0);

            if ($quantidadeDevolver > $qtdeDisponivel) {
                DB::rollBack();
                return back()->with('error', 'Quantidade informada excede o limite permitido.');
            }

            $imagens = [];
            for ($i = 1; $i <= 4; $i++) {
                $campo = 'imagem' . $i;
                $imagens[$campo] = $request->hasFile($campo)
                    ? $request->file($campo)->store('devolucoes', 'public')
                    : null;
            }

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
            return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
        }
    }
  
  public function aprovar(Devolucao $devolucao)
    {
        try {

            DB::beginTransaction();

            $devolucao->refresh();

            // =========================================================
            // 1) Buscar item da venda
            // =========================================================
            $itemVenda = ItemVenda::find($devolucao->venda_item_id);

            if (!$itemVenda) {
                return back()->with('error', 'Item da venda não encontrado.');
            }

            // =========================================================
            // 2) Validar quantidade disponível para devolução
            // =========================================================
            $quantidadeSolicitada = $devolucao->quantidade;
            $quantidadeVendida = $itemVenda->quantidade;

            $jaDevolvido = Devolucao::where('venda_item_id', $itemVenda->id)
                ->where('status', 'aprovada')
                ->sum('quantidade');

            $saldoParaDevolver = $quantidadeVendida - $jaDevolvido;

            if ($quantidadeSolicitada > $saldoParaDevolver) {
                return back()->with('error', 'Quantidade solicitada excede o permitido.');
            }

            // =========================================================
            // 3) Buscar LOTE REAL usado na venda
            // =========================================================
            $lote = Lote::find($itemVenda->lote_id);

            if (!$lote) {
                return back()->with('error', 'Lote da venda não encontrado.');
            }

            // =========================================================
            // 4) Registrar devolução no pivot devolucao_lotes
            // =========================================================
            DB::table('devolucao_lotes')->insert([
                'devolucao_id'  => $devolucao->id,
                'produto_id'    => $itemVenda->produto_id,
                'lote_id'       => $lote->id,
                'quantidade'    => $quantidadeSolicitada,
                'venda_id'      => $itemVenda->venda_id,
                'item_venda_id' => $itemVenda->id,
                'devolvido_por' => auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // =========================================================
            // 5) Repor estoque EXATAMENTE no lote da venda
            // =========================================================
            $lote->quantidade_disponivel += $quantidadeSolicitada;
            $lote->save();

            // =========================================================
            // 6) Atualizar devolução principal
            // =========================================================
            $devolucao->status = 'aprovada';
            $devolucao->criado_por = auth()->id();
            $devolucao->save();

            // =========================================================
            // 7) Log opcional
            // =========================================================
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao'         => 'aprovada',
                'descricao'    => "Devolução aprovada. Quantidade: {$quantidadeSolicitada}.",
                'usuario'      => auth()->user()->name,
            ]);

            DB::commit();

            return back()->with('success', 'Devolução aprovada com sucesso!');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar devolução: '.$e->getMessage());
        }
    }


    public function rejeitar(Devolucao $devolucao)
    {
        DB::transaction(function () use ($devolucao) {
            $devolucao->status = 'rejeitada';
            $devolucao->save();

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
        $devolucoes = Devolucao::with([
            'itemVenda.venda.cliente',
            'itemVenda.produto',
            'itemVenda.lote',
        ])->where('status', 'pendente')->get();

        return view('devolucoes.pendentes', compact('devolucoes'));
    }

    public function gerarCupom($id)
    {
        $devolucao = Devolucao::findOrFail($id);
        $venda = Venda::with('itens.produto')->find($devolucao->venda_id);
        $cliente = Cliente::find($venda->cliente_id ?? $devolucao->cliente_id);
        $itens = $venda ? $venda->itens : [];

        $pdf = Pdf::loadView('devolucoes.cupom', compact('devolucao', 'venda', 'cliente', 'itens'));
        return $pdf->stream('cupom_devolucao_'.$devolucao->id.'.pdf');
    }
}
