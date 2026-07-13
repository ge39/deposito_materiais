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

    // public function create()
    // {
    //     $entregasDisponiveis = Entrega::with([
    //             'cliente',
    //             'orcamento.cliente',
    //             'venda.cliente',
    //             'itens.produto',
    //             'itens.vendaItem.produto',
    //             'itens.itemOrcamento.produto',
    //         ])
    //         ->whereIn('status', [
    //             'Aguardando_separacao',
    //             'aguardando_separacao',
    //             'Separando',
    //             'separando',
    //         ])
    //         ->orderBy('data_prevista_entrega')
    //         ->orderBy('id')
    //         ->get();

    //     $motoristas = Funcionario::where('funcao', 'motorista')
    //         ->where(function ($query) {
    //             $query->where('ativo', 1)->orWhereNull('ativo');
    //         })
    //         ->orderBy('nome')
    //         ->get();

    //     $veiculos = Veiculo::where(function ($query) {
    //             $query->where('ativo', 1)->orWhereNull('ativo');
    //         })
    //         ->orderBy('observacao')
    //         ->get();

    //     return view('romaneios.create', compact(
    //         'entregasDisponiveis',
    //         'motoristas',
    //         'veiculos'
    //     ));
    // }

   public function create(Request $request)
    {
        $entregaId = $request->integer('entrega_id');

        $statusOperacionais = [
            'Aguardando_separacao',
            'aguardando_separacao',
            'Separando',
            'separando',
            'Aguardando_carregamento',
            'aguardando_carregamento',
            'Carregando',
            'carregando',
            'Aguardando_conferencia',
            'aguardando_conferencia',
            'Conferindo',
            'conferindo',
            'Aguardando_liberacao',
            'aguardando_liberacao',
        ];

        $entregasDisponiveis = Entrega::with([
                'cliente',
                'orcamento.cliente',
                'venda.cliente',
                'itens.produto',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
                // 'romaneioAtivo.motorista',
                // 'romaneioAtivo.veiculo',
                // 'romaneioAtivo.itens.entregaItem.produto',
                // 'romaneioAtivo.itens.entregaItem.vendaItem.produto',
                // 'romaneioAtivo.itens.entregaItem.itemOrcamento.produto',
            ])
            ->whereIn('status', $statusOperacionais)
            ->when($entregaId, function ($query) use ($entregaId) {
                $query->where('id', $entregaId);
            })
            ->orderBy('data_prevista')
            ->orderBy('id')
            ->get();

        if ($entregaId && $entregasDisponiveis->isEmpty()) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'A entrega selecionada não está disponível para operação do romaneio.'
                );
        }

        $motoristas = Funcionario::where('funcao', 'motorista')
            ->where(function ($query) {
                $query->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::where(function ($query) {
                $query->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view('romaneios.create', compact(
            'entregasDisponiveis',
            'motoristas',
            'veiculos',
            'entregaId'
        ));
    }

    public function store(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'entregas' => ['nullable', 'array'],
                'entregas.*' => [
                    'nullable',
                    'integer',
                    'exists:entregas,id',
                ],

                'entrega_itens' => ['nullable', 'array'],
                'entrega_itens.*' => [
                    'nullable',
                    'integer',
                    'exists:entrega_itens,id',
                ],

                'motorista_id' => [
                    'nullable',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'veiculo_id' => [
                    'nullable',
                    'integer',
                    'exists:veiculos,id',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'entregas.array' => 'A seleção de entregas é inválida.',
                'entregas.*.integer' => 'Uma das entregas selecionadas é inválida.',
                'entregas.*.exists' => 'Uma das entregas selecionadas não foi encontrada.',

                'entrega_itens.array' => 'A seleção de itens é inválida.',
                'entrega_itens.*.integer' => 'Um dos itens selecionados é inválido.',
                'entrega_itens.*.exists' => 'Um dos itens selecionados não foi encontrado.',

                'motorista_id.integer' => 'O motorista selecionado é inválido.',
                'motorista_id.exists' => 'O motorista selecionado não foi encontrado.',

                'veiculo_id.integer' => 'O veículo selecionado é inválido.',
                'veiculo_id.exists' => 'O veículo selecionado não foi encontrado.',

                'observacao.string' => 'A observação deve ser um texto.',
                'observacao.max' => 'A observação pode ter no máximo 1000 caracteres.',
            ]
        );

        if (
            empty($dadosValidados['entregas'] ?? []) &&
            empty($dadosValidados['entrega_itens'] ?? [])
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selecione pelo menos uma entrega ou item para criar o romaneio.'
                );
        }

        try {
            $romaneio = $this->romaneioService->criarRomaneio(
                $dadosValidados
            );

            return redirect()
                ->route('romaneios.show', $romaneio->id)
                ->with(
                    'success',
                    'Romaneio criado com sucesso. Agora ele já pode ser impresso para coleta física do estoque.'
                );
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao criar romaneio: ' . $e->getMessage()
                );
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
    // public function imprimir(Romaneio $romaneio)
    // {
    //     $romaneio->load([
    //         'motorista',
    //         'veiculo',
    //         'entrega.cliente',
    //         'entrega.orcamento',
    //         'entrega.venda',
    //         'itens.entregaItem.entrega.cliente',
    //         'itens.entregaItem.produto',
    //         'itens.entregaItem.vendaItem.produto',
    //         'itens.entregaItem.itemOrcamento.produto',
    //     ]);

    //     $romaneio->setRelation(
    //         'itens',
    //         $romaneio->itens->sortBy(function ($item) {
    //             return $item->entregaItem->produto->localizacao_estoque
    //                 ?? $item->entregaItem->vendaItem->produto->localizacao_estoque
    //                 ?? $item->entregaItem->itemOrcamento->produto->localizacao_estoque
    //                 ?? 'ZZZ';
    //         })->values()
    //     );

    //     return view('romaneios.imprimir', compact('romaneio'));
    // }

    public function imprimir(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'impressor',
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
            $romaneio->itens
                ->sortBy(function ($item) {
                    return $item->entregaItem?->produto?->localizacao_estoque
                        ?? $item->entregaItem?->vendaItem?->produto?->localizacao_estoque
                        ?? $item->entregaItem?->itemOrcamento?->produto?->localizacao_estoque
                        ?? 'ZZZ';
                })
                ->values()
        );

        return view(
            'romaneios.imprimir',
            compact('romaneio')
        );
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

    // public function salvarEquipe(Request $request, Romaneio $romaneio)
    // {
    //     $request->validate([
    //         'motorista_id' => ['required', 'integer', 'exists:funcionarios,id'],
    //         'veiculo_id' => ['required', 'integer', 'exists:frotas,id'],
    //     ]);

    //     $romaneio->update([
    //         'motorista_id' => $request->motorista_id,
    //         'veiculo_id' => $request->veiculo_id,
    //     ]);

    //    return redirect()
    //     ->route('romaneios.show', $romaneio->id)
    //     ->with('success', 'Equipe atribuída ao romaneio com sucesso.');
    // }

    public function salvarEquipe( Request $request, Romaneio $romaneio) 
    {
        $dadosValidados = $request->validate(
            [
                'motorista_id' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'veiculo_id' => [
                    'required',
                    'integer',
                    'exists:veiculos,id',
                ],
            ],
            [
                'motorista_id.required' => 'Selecione o motorista.',
                'motorista_id.integer' => 'O motorista selecionado é inválido.',
                'motorista_id.exists' => 'O motorista selecionado não foi encontrado.',

                'veiculo_id.required' => 'Selecione o veículo.',
                'veiculo_id.integer' => 'O veículo selecionado é inválido.',
                'veiculo_id.exists' => 'O veículo selecionado não foi encontrado.',
            ]
        );

        $romaneio->update([
            'motorista_id' => $dadosValidados['motorista_id'],
            'veiculo_id' => $dadosValidados['veiculo_id'],
        ]);

        return redirect()
            ->route('romaneios.show', $romaneio->id)
            ->with(
                'success',
                'Equipe atribuída ao romaneio com sucesso.'
            );
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

    // public function atualizarOperacao(Request $request, Romaneio $romaneio)
    // {
    //     $dadosValidados = $request->validate(
    //         [
    //             'acao' => [
    //                 'required',
    //                 'string',
    //                 'in:salvar_andamento,finalizar_separacao,finalizar_carregamento,concluir_conferencia,liberar_veiculo',
    //             ],
    //         ],
    //         [
    //             'acao.required' => 'A ação operacional não foi informada.',
    //             'acao.in' => 'A ação operacional informada é inválida.',
    //         ]
    //     );

    //     try {
    //         $romaneio = $this->romaneioService->atualizarOperacao(
    //             $romaneio,
    //             $dadosValidados['acao'],
    //             $request->all()
    //         );

    //         $mensagem = match ($dadosValidados['acao']) {
    //             'salvar_andamento' =>
    //                 'Andamento salvo com sucesso.',

    //             'finalizar_separacao' =>
    //                 'Separação finalizada. O romaneio avançou para Carregamento.',

    //             'finalizar_carregamento' =>
    //                 'Carregamento finalizado. O romaneio avançou para Conferência.',

    //             'concluir_conferencia' =>
    //                 'Conferência concluída. O romaneio avançou para Liberação.',

    //             'liberar_veiculo' =>
    //                 'Veículo liberado com sucesso.',

    //             default =>
    //                 'Operação atualizada com sucesso.',
    //         };

    //         return redirect()
    //             ->route('romaneios.create', [
    //                 'entrega_id' => $romaneio->entrega_id,
    //             ])
    //             ->with('success', $mensagem);

    //     } catch (Throwable $e) {
    //         return back()
    //             ->withInput()
    //             ->with(
    //                 'error',
    //                 'Erro ao atualizar operação do romaneio: ' .
    //                 $e->getMessage()
    //             );
    //     }
    // }

    public function atualizarOperacao(Request $request, Romaneio $romaneio)
    {
        $dadosValidados = $request->validate(
            [
                'acao' => [
                    'required',
                    'string',
                    'in:salvar_andamento,finalizar_separacao,finalizar_carregamento,concluir_conferencia,liberar_veiculo,voltar_etapa',
                ],

                'motivo_retorno' => [
                    'nullable',
                    'required_if:acao,voltar_etapa',
                    'string',
                    'min:5',
                    'max:500',
                ],

                'itens' => [
                    'nullable',
                    'array',
                ],

                'itens.*.entrega_item_id' => [
                    'required_with:itens',
                    'integer',
                    'exists:entrega_itens,id',
                ],

                'itens.*.romaneio_item_id' => [
                    'nullable',
                    'integer',
                    'exists:romaneio_itens,id',
                ],

                'itens.*.quantidade' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_separada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_carregada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.status' => [
                    'nullable',
                    'string',
                ],
            ],
            [
                'acao.required' =>
                    'A ação operacional não foi informada.',

                'acao.in' =>
                    'A ação operacional informada é inválida.',

                'motivo_retorno.required_if' =>
                    'Informe o motivo do retorno da etapa.',

                'motivo_retorno.min' =>
                    'O motivo do retorno deve possuir pelo menos 5 caracteres.',

                'motivo_retorno.max' =>
                    'O motivo do retorno pode possuir no máximo 500 caracteres.',

                'itens.*.entrega_item_id.required_with' =>
                    'Não foi possível identificar um dos itens da entrega.',

                'itens.*.entrega_item_id.exists' =>
                    'Um dos itens da entrega não foi encontrado.',

                'itens.*.romaneio_item_id.exists' =>
                    'Um dos itens do romaneio não foi encontrado.',

                'itens.*.quantidade_separada.numeric' =>
                    'A quantidade separada deve ser numérica.',

                'itens.*.quantidade_carregada.numeric' =>
                    'A quantidade carregada deve ser numérica.',
            ]
        );

        try {
            $romaneio = $this->romaneioService
                ->atualizarOperacao(
                    $romaneio,
                    $dadosValidados['acao'],
                    $request->all()
                );

            $mensagem = match ($dadosValidados['acao']) {
                'salvar_andamento' =>
                    'Andamento salvo com sucesso.',

                'finalizar_separacao' =>
                    'Separação finalizada. O romaneio avançou para Carregamento.',

                'finalizar_carregamento' =>
                    'Carregamento finalizado. O romaneio avançou para Conferência.',

                'concluir_conferencia' =>
                    'Conferência concluída. O romaneio avançou para Liberação.',

                'liberar_veiculo' =>
                    'Veículo liberado com sucesso.',

                'voltar_etapa' =>
                    'O romaneio retornou para a etapa anterior.',

                default =>
                    'Operação atualizada com sucesso.',
            };

            return redirect()
                ->route('romaneios.create', [
                    'entrega_id' => $romaneio->entrega_id,
                ])
                ->with('success', $mensagem);

        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao atualizar operação do romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function registrarImpressao(Romaneio $romaneio) 
    {
        try {
            $statusAtual = strtolower(
                trim((string) $romaneio->status)
            );

            if (! in_array($statusAtual, [
                'conferido',
                'aguardando_liberacao',
            ], true)) {
                return back()->with(
                    'error',
                    'O romaneio somente pode ser impresso para liberação após a conclusão da conferência.'
                );
            }

            $romaneio->update([
                'impresso_em' => now(),
                'impresso_por' => auth()->id(),
            ]);

            return redirect()
                ->route(
                    'romaneios.imprimir',
                    $romaneio->id
                );

        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Erro ao registrar a impressão do romaneio: ' .
                $e->getMessage()
            );
        }
    }




}