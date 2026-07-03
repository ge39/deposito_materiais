<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Services\Entregas\EntregaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class EntregaController extends Controller
{
    protected EntregaService $entregaService;

    public function __construct(EntregaService $entregaService)
    {
        $this->entregaService = $entregaService;
    }

    // public function index(Request $request)
    // {
    //     $query = Entrega::with(['venda', 'orcamento', 'itens'])
    //         ->orderByDesc('id');

    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('data_prevista')) {
    //         $query->whereDate('data_prevista', $request->data_prevista);
    //     }

    //     if ($request->filled('codigo_entrega')) {
    //         $query->where('codigo_entrega', 'like', '%' . $request->codigo_entrega . '%');
    //     }

    //     $entregas = $query->paginate(20)->withQueryString();

    //     $resumo = [
    //         'pendentes' => Entrega::where('status', 'pendente')->count(),
    //         'separando' => Entrega::where('status', 'separando')->count(),
    //         'carregados' => Entrega::where('status', 'carregado')->count(),
    //         'em_rota' => Entrega::where('status', 'em_rota')->count(),
    //         'entregues' => Entrega::where('status', 'entregue')->count(),
    //         'parciais' => Entrega::where('status', 'parcial')->count(),

    //         'atrasadas' => Entrega::whereDate('data_prevista', '<', now()->toDateString())
    //             ->whereNotIn('status', ['entregue', 'cancelado', 'devolvido'])
    //             ->count(),
    //     ];

    //     return view('entregas.index', compact('entregas', 'resumo'));
    // }

    public function index(Request $request)
    {
        $query = Entrega::with(['venda', 'orcamento', 'itens'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_prevista')) {
            $query->whereDate('data_prevista', $request->data_prevista);
        }

        if ($request->filled('codigo_entrega')) {
            $query->where('codigo_entrega', 'like', '%' . $request->codigo_entrega . '%');
        }

        $entregas = $query->paginate(20)->withQueryString();

       $resumo = [
            'pendente_pagamento' => Entrega::where('status', 'pendente_pagamento')->count(),

            'aguardando_separacao' => Entrega::where('status', 'aguardando_separacao')->count(),

            'separando' => Entrega::where('status', 'separando')->count(),

            'carregados' => Entrega::where('status', 'carregado')->count(),

            'em_rota' => Entrega::where('status', 'em_rota')->count(),

            'entregues' => Entrega::where('status', 'entregue')->count(),

            'parciais' => Entrega::where('status', 'parcial')->count(),

            'devolvidos' => Entrega::where('status', 'devolvido')->count(),

            'cancelados' => Entrega::where('status', 'cancelado')->count(),

            'atrasadas' => Entrega::whereDate('data_prevista', '<', now()->toDateString())
                ->whereNotIn('status', ['entregue', 'cancelado', 'devolvido'])
                ->count(),
        ];

        return view('entregas.index', compact('entregas', 'resumo'));
    }

   public function show(Entrega $entrega)
    {
        $entrega->load([
            'venda',
            'venda.cliente',
            'venda.itens.produto',

            'orcamento',
            'orcamento.cliente',
            'orcamento.itens.produto',

            'itens',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ]);

        return view('entregas.show', compact('entrega'));
    }

    public function separar(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'separando', 'Entrega enviada para separação.');
    }

    public function carregar(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'carregado', 'Entrega marcada como carregada.');
    }

    public function enviarParaRota(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'em_rota', 'Entrega enviada para rota.');
    }

    public function confirmar(Entrega $entrega)
    {
        try {
            $this->entregaService->confirmarEntrega($entrega);

            return redirect()
                ->back()
                ->with('success', 'Entrega confirmada com sucesso.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao confirmar entrega: ' . $e->getMessage());
        }
    }

    public function confirmarParcial(Request $request, Entrega $entrega)
    {
        $dados = $request->validate([
            'itens' => ['required', 'array'],
            'itens.*.entrega_item_id' => ['required', 'integer', 'exists:entrega_itens,id'],
            'itens.*.quantidade_entregue' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->entregaService->confirmarParcial($entrega, $dados['itens']);

            return redirect()
                ->back()
                ->with('success', 'Entrega parcial registrada com sucesso.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao registrar entrega parcial: ' . $e->getMessage());
        }
    }

    public function cancelar(Request $request, Entrega $entrega)
    {
        $dados = $request->validate([
            'motivo' => ['nullable', 'string'],
        ]);

        try {
            $this->entregaService->cancelar($entrega, $dados['motivo'] ?? null);

            return redirect()
                ->back()
                ->with('success', 'Entrega cancelada com sucesso.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar entrega: ' . $e->getMessage());
        }
    }

    private function alterarStatusComRetorno(Entrega $entrega, string $status, string $mensagem)
    {
        try {
            $this->entregaService->alterarStatus($entrega, $status);

            return redirect()
                ->back()
                ->with('success', $mensagem);

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar entrega: ' . $e->getMessage());
        }
    }
}