<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Veiculo;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Throwable;

class RomaneioController extends Controller
{
    protected RomaneioService $romaneioService;

    public function __construct(RomaneioService $romaneioService)
    {
        $this->romaneioService = $romaneioService;
    }

    public function index(Request $request)
    {
        $romaneios = Romaneio::with([
                'entrega.orcamento.cliente',
                'entrega.venda',
                'motorista',
                'veiculo',
                'entrega.cliente',
                'itens',
            ])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('romaneios.index', compact('romaneios'));
    }

    public function create()
    {
        $entregasDisponiveis = Entrega::with([
                'cliente',
                'orcamento.cliente',
                'venda.cliente',
                'itens.produto',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->whereIn('status', [
                'Aguardando_separacao',
                'aguardando_separacao',
                'Separando',
                'separando',
            ])
            ->orderBy('data_prevista_entrega')
            ->orderBy('id')
            ->get();

        $motoristas = Funcionario::where('funcao', 'motorista')
            ->where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view('romaneios.create', compact(
            'entregasDisponiveis',
            'motoristas',
            'veiculos'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entregas' => ['nullable', 'array'],
            'entregas.*' => ['nullable', 'integer', 'exists:entregas,id'],

            'entrega_itens' => ['nullable', 'array'],
            'entrega_itens.*' => ['nullable', 'integer', 'exists:entrega_itens,id'],

            'motorista_id' => ['nullable', 'integer', 'exists:funcionarios,id'],
            'veiculo_id' => ['nullable', 'integer', 'exists:frotas,id'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);

        if (
            empty($request->input('entregas', [])) &&
            empty($request->input('entrega_itens', []))
        ) {
            return back()
                ->withInput()
                ->with('error', 'Selecione pelo menos uma entrega ou item para criar o romaneio.');
        }

        try {
            $romaneio = $this->romaneioService->criarRomaneio($request->all());

            return redirect()
                ->route('romaneios.show', $romaneio->id)
                ->with('success', 'Romaneio criado com sucesso. Agora ele já pode ser impresso para coleta física do estoque.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar romaneio: ' . $e->getMessage());
        }
    }

    public function show(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.orcamento',
            'entrega.venda',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        return view('romaneios.show', compact('romaneio'));
    }

    public function cancelar(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'motivo_cancelamento' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->romaneioService->cancelar($romaneio, $request->motivo_cancelamento);

            return redirect()
                ->route('romaneios.index')
                ->with('success', 'Romaneio cancelado com sucesso.');
        } catch (Throwable $e) {
            return back()
                ->with('error', 'Erro ao cancelar romaneio: ' . $e->getMessage());
        }
    }
    public function imprimir(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.orcamento',
            'entrega.venda',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $romaneio->setRelation(
            'itens',
            $romaneio->itens->sortBy(function ($item) {
                return $item->entregaItem->produto->localizacao_estoque
                    ?? $item->entregaItem->vendaItem->produto->localizacao_estoque
                    ?? $item->entregaItem->itemOrcamento->produto->localizacao_estoque
                    ?? 'ZZZ';
            })->values()
        );

        return view('romaneios.imprimir', compact('romaneio'));
    }

    public function atribuirEquipe(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.orcamento.cliente',
            'entrega.cliente',
        ]);

        $motoristas = Funcionario::where('funcao', 'motorista')
            ->where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('observacoes')
            ->get();

        return view('expedicao.atribuir-equipe', compact(
            'romaneio',
            'motoristas',
            'veiculos'
        ));
    }

    public function salvarEquipe(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'motorista_id' => ['required', 'integer', 'exists:funcionarios,id'],
            'veiculo_id' => ['required', 'integer', 'exists:frotas,id'],
        ]);

        $romaneio->update([
            'motorista_id' => $request->motorista_id,
            'veiculo_id' => $request->veiculo_id,
        ]);

       return redirect()
        ->route('romaneios.show', $romaneio->id)
        ->with('success', 'Equipe atribuída ao romaneio com sucesso.');
    }

    public function separacao(Romaneio $romaneio)
{
    $romaneio->load([
        'motorista',
        'veiculo',
        'entrega.cliente',
        'entrega.orcamento',
        'entrega.orcamento.cliente',
        'entrega.venda',
        'entrega.venda.cliente',
        'itens.entregaItem.entrega.cliente',
        'itens.entregaItem.produto',
        'itens.entregaItem.vendaItem.produto',
        'itens.entregaItem.itemOrcamento.produto',
    ]);

    $romaneio->setRelation(
        'itens',
        $romaneio->itens
            ->sortBy(function ($item) {
                return $item->entregaItem?->produto?->localizacao_estoque
                    ?? $item->entregaItem?->vendaItem?->produto?->localizacao_estoque
                    ?? $item->entregaItem?->itemOrcamento?->produto?->localizacao_estoque
                    ?? 'ZZZ';
            })
            ->values()
    );

    return view('romaneios.separacao', compact('romaneio'));
}
}