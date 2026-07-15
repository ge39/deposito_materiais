<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Veiculo;
use App\Services\Entregas\EntregaService;

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
        $statusMap = [
            'pendente_pagamento' => 'Pendente_pagamento',
            'aguardando_faturamento' => 'Aguardando_faturamento',
            'aguardando_separacao' => 'Aguardando_separacao',
            'separando' => 'Em_preparacao',
            'em_preparacao' => 'Em_preparacao',
            'pronta_para_carregamento' => 'Pronta_para_carregamento',
            'carregado' => 'Carregada',
            'carregada' => 'Carregada',
            'liberada' => 'Liberada',
            'em_rota' => 'Em_rota',
            'no_destino' => 'No_destino',
            'entregue' => 'Entregue',
            'parcial' => 'Entregue_parcial',
            'entregue_parcial' => 'Entregue_parcial',
            'nao_entregue' => 'Nao_entregue',
            'recusada' => 'Recusada',
            'reagendada' => 'Reagendada',
            'devolvido' => 'Devolvida',
            'devolvida' => 'Devolvida',
            'cancelado' => 'Cancelada',
            'cancelada' => 'Cancelada',
        ];

        $query = Entrega::with([
            'venda',
            'orcamento',
            'itens',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ])->orderByDesc('id');

        if ($request->filled('status')) {
            $statusInformado = strtolower(
                trim((string) $request->status)
            );

            if (isset($statusMap[$statusInformado])) {
                $query->where(
                    'status',
                    $statusMap[$statusInformado]
                );
            }
        }

        if ($request->filled('data_prevista')) {
            $query->whereDate(
                'data_prevista',
                $request->data_prevista
            );
        }

        if ($request->filled('codigo_entrega')) {
            $query->where(
                'codigo_entrega',
                'like',
                '%' . trim((string) $request->codigo_entrega) . '%'
            );
        }

        $entregas = $query
            ->paginate(20)
            ->withQueryString();

        $resumo = [
            'pendente_pagamento' => Entrega::where(
                'status',
                'Pendente_pagamento'
            )->count(),

            'aguardando_separacao' => Entrega::where(
                'status',
                'Aguardando_separacao'
            )->count(),

            'separando' => Entrega::where(
                'status',
                'Em_preparacao'
            )->count(),

            'carregados' => Entrega::where(
                'status',
                'Carregada'
            )->count(),

            'em_rota' => Entrega::where(
                'status',
                'Em_rota'
            )->count(),

            'entregues' => Entrega::where(
                'status',
                'Entregue'
            )->count(),

            'parciais' => Entrega::where(
                'status',
                'Entregue_parcial'
            )->count(),

            'devolvidos' => Entrega::where(
                'status',
                'Devolvida'
            )->count(),

            'cancelados' => Entrega::where(
                'status',
                'Cancelada'
            )->count(),

            'atrasadas' => Entrega::whereDate(
                'data_prevista',
                '<',
                now()->toDateString()
            )
                ->whereNotIn('status', [
                    'Entregue',
                    'Cancelada',
                    'Devolvida',
                ])
                ->count(),
        ];

        return view(
            'entregas.index',
            compact(
                'entregas',
                'resumo'
            )
        );
    }

    public function show(Entrega $entrega)
    {
        $entrega->load([
            'motorista',
            'veiculo',

            'romaneio',
            'romaneio.motorista',
            'romaneio.veiculo',

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
        return $this->alterarStatusComRetorno($entrega, 'Separando', 'Entrega enviada para separação.');
    }

    public function carregar(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'Carregado', 'Entrega marcada como carregada.');
    }

    public function enviarParaRota(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'Em_rota', 'Entrega enviada para rota.');
    }

    public function confirmar(Entrega $entrega)
    {
        try {
            if ($entrega->status !== 'Em_rota') {
                throw ValidationException::withMessages([
                    'status' => 'A entrega só pode ser confirmada quando estiver Em rota.',
                ]);
            }

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
      
    public function atribuirEquipe(Entrega $entrega)
    {
        $motoristas = Funcionario::motoristas()
            ->ativos()
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::where('ativo', 1)
            ->where('status', 'Ativo')
            ->whereIn('disponibilidade', ['Disponivel', 'Reservado'])
            ->orderBy('placa')
            ->get();

        return view('entregas.atribuir-equipe', compact('entrega', 'motoristas', 'veiculos'));
    }

    public function salvarEquipe(Request $request, Entrega $entrega)
    {
        $dados = $request->validate([
            'motorista_id' => ['required', 'exists:funcionarios,id'],
            'veiculo_id' => ['required', 'exists:veiculos,id'],
        ]);

        Funcionario::where('id', $dados['motorista_id'])
            ->where('funcao', 'motorista')
            ->where('ativo', 1)
            ->firstOrFail();

        Veiculo::where('id', $dados['veiculo_id'])
            ->where('ativo', 1)
            ->where('status', 'Ativo')
            ->firstOrFail();

        $this->entregaService->atribuirEquipe(
            $entrega,
            $dados['motorista_id'],
            $dados['veiculo_id']
        );

        return redirect()
            ->route('entregas.show', $entrega->id)
            ->with('success', 'Motorista e veículo atribuídos com sucesso.');
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