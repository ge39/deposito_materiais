<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Frota;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $romaneios = Romaneio::with(['motorista', 'veiculo'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('romaneios.index', compact('romaneios'));
    }

    public function show(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
        ]);

        return view('romaneios.show', compact('romaneio'));
    }

    public function create()
    {
        $entregasDisponiveis = Entrega::with([
            'orcamento.cliente',
            'venda.cliente',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ])
        ->whereIn('status', [
            'aguardando_separacao',
            'separando',
        ])
        ->orderBy('data_prevista')
        ->get();

        $motoristas = Funcionario::where('funcao', 'motorista')
            ->where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Frota::where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');
            })
            ->orderBy('observacoes')
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
            'entregas' => ['required', 'array', 'min:1'],
            'entregas.*' => ['required', 'integer', 'exists:entregas,id'],
            'motorista_id' => ['nullable', 'integer', 'exists:funcionarios,id'],
            'veiculo_id' => ['nullable', 'integer', 'exists:frotas,id'],
            'observacao' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {

            $romaneio = $this->romaneioService->criarRomaneio($request->all());

            DB::commit();

            return redirect()
                ->route('expedicao.show', $romaneio->id)
                ->with('success', 'Romaneio criado com sucesso.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar romaneio: ' . $e->getMessage());
        }
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
            return back()->with('error', 'Erro ao cancelar romaneio: ' . $e->getMessage());
        }
    }

    public function imprimir(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
        ]);

        return view('romaneios.imprimir', compact('romaneio'));
    }
}