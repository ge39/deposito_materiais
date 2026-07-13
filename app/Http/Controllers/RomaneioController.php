<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Models\Veiculo;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RomaneioController extends Controller
{
    protected RomaneioService $romaneioService;

    public function __construct(
        RomaneioService $romaneioService
    ) {
        $this->romaneioService = $romaneioService;
    }

    public function index(Request $request)
    {
        $statusValidos = [
            'Gerado',
            'Em_separacao',
            'Separado',
            'Na_doca',
            'Carregando',
            'Carregado',
            'Conferido',
            'Liberado',
            'Saiu_para_entrega',
            'Entregue',
            'Parcial',
            'Devolvido',
            'Cancelado',
        ];

        $romaneios = Romaneio::query()
            ->with([
                'entrega.cliente',
                'entrega.orcamento.cliente',
                'entrega.venda.cliente',
                'motorista',
                'veiculo',
                'itens',
            ])
            ->when(
                $request->filled('status'),
                function ($query) use (
                    $request,
                    $statusValidos
                ) {
                    if (
                        in_array(
                            $request->status,
                            $statusValidos,
                            true
                        )
                    ) {
                        $query->where(
                            'status',
                            $request->status
                        );
                    }
                }
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'romaneios.index',
            compact(
                'romaneios',
                'statusValidos'
            )
        );
    }

    public function create(Request $request)
    {
        $entregaId = $request->integer('entrega_id');

        if (
            ! $entregaId &&
            $request->filled('entregas_id')
        ) {
            $entregaId = (int) $request->input(
                'entregas_id'
            );
        }

        $statusEntregasOperacionais = [
            'Aguardando_separacao',
            'Separando',
            'Aguardando_carregamento',
            'Carregando',
            'Aguardando_conferencia',
            'Aguardando_liberacao',
            'Liberado',
            'Em_rota',
        ];

        $entregasDisponiveis = Entrega::query()
            ->with([
                'cliente',
                'orcamento.cliente',
                'venda.cliente',
                'itens.produto',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->whereIn(
                'status',
                $statusEntregasOperacionais
            )
            ->when(
                $entregaId,
                function ($query) use ($entregaId) {
                    $query->where(
                        'id',
                        $entregaId
                    );
                }
            )
            ->orderBy('data_prevista')
            ->orderBy('id')
            ->get();

        $romaneioAtivo = null;

        if ($entregaId) {
            $romaneioAtivo = Romaneio::query()
                ->with([
                    'motorista',
                    'veiculo',
                    'impressor',
                    'entrega.cliente',
                    'entrega.orcamento.cliente',
                    'entrega.venda.cliente',
                    'itens.entregaItem.entrega.cliente',
                    'itens.entregaItem.produto',
                    'itens.entregaItem.vendaItem.produto',
                    'itens.entregaItem.itemOrcamento.produto',
                ])
                ->where(
                    'entrega_id',
                    $entregaId
                )
                ->whereNotIn('status', [
                    'Entregue',
                    'Devolvido',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if ($romaneioAtivo) {
                $entregaDoRomaneio =
                    $romaneioAtivo->entrega;

                if ($entregaDoRomaneio) {
                    $entregaDoRomaneio->loadMissing([
                        'cliente',
                        'orcamento.cliente',
                        'venda.cliente',
                        'itens.produto',
                        'itens.vendaItem.produto',
                        'itens.itemOrcamento.produto',
                    ]);

                    $entregasDisponiveis = collect([
                        $entregaDoRomaneio,
                    ]);
                }
            }
        }

        if (
            $entregaId &&
            $entregasDisponiveis->isEmpty()
        ) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'A entrega selecionada não está disponível para operação de romaneio.'
                );
        }

        $motoristas = Funcionario::query()
            ->where('funcao', 'motorista')
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view(
            'romaneios.create',
            compact(
                'entregasDisponiveis',
                'motoristas',
                'veiculos',
                'entregaId',
                'romaneioAtivo'
            )
        );
    }

    // public function store(Request $request)
    // {
    //     $dadosValidados = $request->validate(
    //         [
    //             'entrega_id' => [
    //                 'nullable',
    //                 'integer',
    //                 'exists:entregas,id',
    //             ],

    //             'entregas' => [
    //                 'nullable',
    //                 'array',
    //             ],

    //             'entregas.*' => [
    //                 'nullable',
    //                 'integer',
    //                 'exists:entregas,id',
    //             ],

    //             'entrega_itens' => [
    //                 'nullable',
    //                 'array',
    //             ],

    //             'entrega_itens.*' => [
    //                 'nullable',
    //                 'integer',
    //                 'exists:entrega_itens,id',
    //             ],

    //             'itens' => [
    //                 'nullable',
    //                 'array',
    //             ],

    //             'itens.*.entrega_item_id' => [
    //                 'required_with:itens',
    //                 'integer',
    //                 'exists:entrega_itens,id',
    //             ],

    //             'itens.*.quantidade' => [
    //                 'nullable',
    //                 'numeric',
    //                 'gt:0',
    //             ],

    //             'motorista_id' => [
    //                 'nullable',
    //                 'integer',
    //                 'exists:funcionarios,id',
    //             ],

    //             'veiculo_id' => [
    //                 'nullable',
    //                 'integer',
    //                 'exists:veiculos,id',
    //             ],

    //             'observacao' => [
    //                 'nullable',
    //                 'string',
    //                 'max:1000',
    //             ],
    //         ],
    //         [
    //             'entrega_id.exists' =>
    //                 'A entrega selecionada não foi encontrada.',

    //             'entregas.array' =>
    //                 'A seleção de entregas é inválida.',

    //             'entregas.*.exists' =>
    //                 'Uma das entregas selecionadas não foi encontrada.',

    //             'entrega_itens.array' =>
    //                 'A seleção de itens é inválida.',

    //             'entrega_itens.*.exists' =>
    //                 'Um dos itens selecionados não foi encontrado.',

    //             'itens.array' =>
    //                 'Os itens informados são inválidos.',

    //             'itens.*.entrega_item_id.required_with' =>
    //                 'Não foi possível identificar um dos itens.',

    //             'itens.*.entrega_item_id.exists' =>
    //                 'Um dos itens da entrega não foi encontrado.',

    //             'itens.*.quantidade.numeric' =>
    //                 'A quantidade do item deve ser numérica.',

    //             'itens.*.quantidade.gt' =>
    //                 'A quantidade do item deve ser maior que zero.',

    //             'motorista_id.exists' =>
    //                 'O motorista selecionado não foi encontrado.',

    //             'veiculo_id.exists' =>
    //                 'O veículo selecionado não foi encontrado.',

    //             'observacao.max' =>
    //                 'A observação pode possuir no máximo 1000 caracteres.',
    //         ]
    //     );

    //     $possuiEntrega =
    //         ! empty($dadosValidados['entrega_id'] ?? null)
    //         || ! empty($dadosValidados['entregas'] ?? []);

    //     $possuiItens =
    //         ! empty($dadosValidados['entrega_itens'] ?? [])
    //         || ! empty($dadosValidados['itens'] ?? []);

    //     if (
    //         ! $possuiEntrega &&
    //         ! $possuiItens
    //     ) {
    //         return back()
    //             ->withInput()
    //             ->with(
    //                 'error',
    //                 'Selecione pelo menos uma entrega ou item para criar o romaneio.'
    //             );
    //     }

    //     try {
    //         $romaneio =
    //             $this->romaneioService
    //                 ->criarRomaneio(
    //                     $dadosValidados
    //                 );

    //         return redirect()
    //             ->route(
    //                 'romaneios.create',
    //                 [
    //                     'entrega_id' =>
    //                         $romaneio->entrega_id,
    //                 ]
    //             )
    //             ->with(
    //                 'success',
    //                 'Romaneio criado com sucesso. A operação está pronta para iniciar a separação.'
    //             );

    //     } catch (Throwable $e) {
    //         return back()
    //             ->withInput()
    //             ->with(
    //                 'error',
    //                 'Erro ao criar romaneio: ' .
    //                 $e->getMessage()
    //             );
    //     }
    // }

    public function store(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'entrega_id' => [
                    'nullable',
                    'integer',
                    'exists:entregas,id',
                ],

                'entregas' => [
                    'nullable',
                    'array',
                ],

                'entregas.*' => [
                    'nullable',
                    'integer',
                    'exists:entregas,id',
                ],

                'entrega_itens' => [
                    'nullable',
                    'array',
                ],

                'entrega_itens.*' => [
                    'nullable',
                    'integer',
                    'exists:entrega_itens,id',
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

                'itens.*.quantidade' => [
                    'required_with:itens',
                    'numeric',
                    'gt:0',
                ],

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

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'entrega_id.exists' =>
                    'A entrega selecionada não foi encontrada.',

                'entregas.array' =>
                    'A seleção de entregas é inválida.',

                'entregas.*.integer' =>
                    'Uma das entregas selecionadas é inválida.',

                'entregas.*.exists' =>
                    'Uma das entregas selecionadas não foi encontrada.',

                'entrega_itens.array' =>
                    'A seleção de itens é inválida.',

                'entrega_itens.*.integer' =>
                    'Um dos itens selecionados é inválido.',

                'entrega_itens.*.exists' =>
                    'Um dos itens selecionados não foi encontrado.',

                'itens.array' =>
                    'Os itens informados são inválidos.',

                'itens.*.entrega_item_id.required_with' =>
                    'Não foi possível identificar um dos itens.',

                'itens.*.entrega_item_id.integer' =>
                    'Um dos itens informados é inválido.',

                'itens.*.entrega_item_id.exists' =>
                    'Um dos itens da entrega não foi encontrado.',

                'itens.*.quantidade.required_with' =>
                    'Informe a quantidade de todos os itens do romaneio.',

                'itens.*.quantidade.numeric' =>
                    'A quantidade do item deve ser numérica.',

                'itens.*.quantidade.gt' =>
                    'A quantidade de cada item deve ser maior que zero.',

                'motorista_id.required' =>
                    'Selecione o motorista responsável pela entrega.',

                'motorista_id.integer' =>
                    'O motorista selecionado é inválido.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.required' =>
                    'Selecione o veículo que realizará a entrega.',

                'veiculo_id.integer' =>
                    'O veículo selecionado é inválido.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 1000 caracteres.',
            ]
        );

        $possuiEntrega =
            ! empty($dadosValidados['entrega_id'] ?? null)
            || ! empty($dadosValidados['entregas'] ?? []);

        $possuiItens =
            ! empty($dadosValidados['entrega_itens'] ?? [])
            || ! empty($dadosValidados['itens'] ?? []);

        if (! $possuiEntrega && ! $possuiItens) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selecione pelo menos uma entrega ou item para criar o romaneio.'
                );
        }

        try {
            $romaneio = $this->romaneioService
                ->criarRomaneio($dadosValidados);

            return redirect()
                ->route('romaneios.create', [
                    'entrega_id' => $romaneio->entrega_id,
                ])
                ->with(
                    'success',
                    'Romaneio criado com sucesso. A operação está pronta para iniciar a separação.'
                );

        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao criar romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function show(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'criador',
            'iniciador',
            'carregador',
            'conferente',
            'finalizador',
            'impressor',
            'entrega.cliente',
            'entrega.orcamento.cliente',
            'entrega.venda.cliente',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        return view(
            'romaneios.show',
            compact('romaneio')
        );
    }

    public function atualizarOperacao(Request $request, Romaneio $romaneio)
    {
        $acoesPermitidas = [
            'salvar_andamento',
            'iniciar_separacao',
            'finalizar_separacao',
            'enviar_para_doca',
            'iniciar_carregamento',
            'finalizar_carregamento',
            'concluir_conferencia',
            'liberar_veiculo',
            'registrar_saida',
            'voltar_etapa',
        ];

        $dadosValidados = $request->validate(
            [
                'acao' => [
                    'required',
                    'string',
                    Rule::in(
                        $acoesPermitidas
                    ),
                ],

                'motivo_retorno' => [
                    'nullable',
                    'required_if:acao,voltar_etapa',
                    'string',
                    'min:5',
                    'max:500',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
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
                    Rule::in([
                        'pendente',
                        'em_andamento',
                        'concluido',
                        'separado',
                        'carregado',
                        'conferido',
                        'divergente',
                        'parcial',
                    ]),
                ],

                'itens.*.observacao' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ],
            [
                'acao.required' =>
                    'A ação operacional não foi informada.',

                'acao.in' =>
                    'A ação operacional informada é inválida.',

                'motivo_retorno.required_if' =>
                    'Informe o motivo do retorno de etapa.',

                'motivo_retorno.min' =>
                    'O motivo do retorno deve possuir pelo menos 5 caracteres.',

                'motivo_retorno.max' =>
                    'O motivo do retorno pode possuir no máximo 500 caracteres.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 1000 caracteres.',

                'itens.array' =>
                    'Os dados dos itens são inválidos.',

                'itens.*.entrega_item_id.required_with' =>
                    'Não foi possível identificar um dos itens da entrega.',

                'itens.*.entrega_item_id.exists' =>
                    'Um dos itens da entrega não foi encontrado.',

                'itens.*.romaneio_item_id.exists' =>
                    'Um dos itens do romaneio não foi encontrado.',

                'itens.*.quantidade.numeric' =>
                    'A quantidade do item deve ser numérica.',

                'itens.*.quantidade_separada.numeric' =>
                    'A quantidade separada deve ser numérica.',

                'itens.*.quantidade_carregada.numeric' =>
                    'A quantidade carregada deve ser numérica.',

                'itens.*.status.in' =>
                    'A situação informada para um dos itens é inválida.',

                'itens.*.observacao.max' =>
                    'A observação do item pode possuir no máximo 500 caracteres.',
            ]
        );

        try {
            $romaneio =
                $this->romaneioService
                    ->atualizarOperacao(
                        $romaneio,
                        $dadosValidados['acao'],
                        $dadosValidados
                    );

            $mensagem = match (
                $dadosValidados['acao']
            ) {
                'salvar_andamento' =>
                    'Andamento salvo com sucesso.',

                'iniciar_separacao' =>
                    'Separação iniciada com sucesso.',

                'finalizar_separacao' =>
                    'Separação finalizada com sucesso.',

                'enviar_para_doca' =>
                    'Romaneio encaminhado para a doca.',

                'iniciar_carregamento' =>
                    'Carregamento iniciado com sucesso.',

                'finalizar_carregamento' =>
                    'Carregamento finalizado com sucesso.',

                'concluir_conferencia' =>
                    'Conferência concluída com sucesso.',

                'liberar_veiculo' =>
                    'Romaneio liberado com sucesso. Registre a saída física do veículo.',

                'registrar_saida' =>
                    'Saída do veículo registrada. O romaneio está em rota.',

                'voltar_etapa' =>
                    'O romaneio retornou para a etapa anterior.',

                default =>
                    'Operação atualizada com sucesso.',
            };

            return redirect()
                ->route(
                    'romaneios.create',
                    [
                        'entrega_id' =>
                            $romaneio->entrega_id,
                    ]
                )
                ->with(
                    'success',
                    $mensagem
                );

        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao atualizar a operação do romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function registrarImpressao(
        Romaneio $romaneio
    ) {
        try {
            $statusAtual = strtolower(
                trim(
                    str_replace(
                        ' ',
                        '_',
                        (string) $romaneio->status
                    )
                )
            );

            if (! in_array(
                $statusAtual,
                [
                    'conferido',
                    'liberado',
                ],
                true
            )) {
                return back()->with(
                    'error',
                    'O romaneio somente pode ser impresso após a conclusão da conferência.'
                );
            }

            $romaneio->update([
                'impresso_em' => now(),
                'impresso_por' => auth()->id(),
            ]);

            return redirect()->route(
                'romaneios.imprimir',
                $romaneio
            );

        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Erro ao registrar a impressão do romaneio: ' .
                $e->getMessage()
            );
        }
    }

    public function imprimir(
        Romaneio $romaneio
    ) {
        $romaneio->load([
            'motorista',
            'veiculo',
            'criador',
            'iniciador',
            'carregador',
            'conferente',
            'finalizador',
            'impressor',
            'entrega.cliente',
            'entrega.orcamento.cliente',
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
                    return
                        $item->entregaItem?->produto
                            ?->localizacao_estoque
                        ?? $item->entregaItem?->vendaItem
                            ?->produto?->localizacao_estoque
                        ?? $item->entregaItem?->itemOrcamento
                            ?->produto?->localizacao_estoque
                        ?? 'ZZZ';
                })
                ->values()
        );

        return view(
            'romaneios.imprimir',
            compact('romaneio')
        );
    }

    public function cancelar(
        Request $request,
        Romaneio $romaneio
    ) {
        $dadosValidados = $request->validate(
            [
                'motivo_cancelamento' => [
                    'required',
                    'string',
                    'min:5',
                    'max:500',
                ],
            ],
            [
                'motivo_cancelamento.required' =>
                    'Informe o motivo do cancelamento.',

                'motivo_cancelamento.min' =>
                    'O motivo do cancelamento deve possuir pelo menos 5 caracteres.',

                'motivo_cancelamento.max' =>
                    'O motivo do cancelamento pode possuir no máximo 500 caracteres.',
            ]
        );

        try {
            $this->romaneioService->cancelar(
                $romaneio,
                $dadosValidados[
                    'motivo_cancelamento'
                ]
            );

            return redirect()
                ->route('romaneios.index')
                ->with(
                    'success',
                    'Romaneio cancelado com sucesso.'
                );

        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao cancelar o romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function atribuirEquipe(
        Romaneio $romaneio
    ) {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.orcamento.cliente',
            'entrega.cliente',
        ]);

        $motoristas = Funcionario::query()
            ->where('funcao', 'motorista')
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view(
            'expedicao.atribuir-equipe',
            compact(
                'romaneio',
                'motoristas',
                'veiculos'
            )
        );
    }

    public function salvarEquipe(
        Request $request,
        Romaneio $romaneio
    ) {
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
                'motorista_id.required' =>
                    'Selecione o motorista.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.required' =>
                    'Selecione o veículo.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',
            ]
        );

        $statusBloqueados = [
            'Liberado',
            'Saiu_para_entrega',
            'Entregue',
            'Parcial',
            'Devolvido',
            'Cancelado',
        ];

        if (
            in_array(
                $romaneio->status,
                $statusBloqueados,
                true
            )
        ) {
            return back()->with(
                'error',
                'A equipe não pode ser alterada no status atual do romaneio.'
            );
        }

        $romaneio->update([
            'motorista_id' =>
                $dadosValidados['motorista_id'],

            'veiculo_id' =>
                $dadosValidados['veiculo_id'],
        ]);

        return redirect()
            ->route(
                'romaneios.create',
                [
                    'entrega_id' =>
                        $romaneio->entrega_id,
                ]
            )
            ->with(
                'success',
                'Equipe atribuída ao romaneio com sucesso.'
            );
    }

    public function separacao(
        Romaneio $romaneio
    ) {
        return redirect()->route(
            'romaneios.create',
            [
                'entrega_id' =>
                    $romaneio->entrega_id,
            ]
        );
    }
}