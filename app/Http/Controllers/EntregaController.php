<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Veiculo;
use App\Models\Romaneio;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Validation\Rule;
use App\Services\Entregas\EntregaService;

use Throwable;

class EntregaController extends Controller
{
   public function __construct(EntregaService $entregaService, RomaneioService $romaneioService) 
   {
        $this->entregaService = $entregaService;
        $this->romaneioService = $romaneioService;
    }

   public function index(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'codigo_entrega' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'status' => [
                    'nullable',
                    'string',
                ],

                'data_inicio' => [
                    'nullable',
                    'date',
                ],

                'data_fim' => [
                    'nullable',
                    'date',
                    'after_or_equal:data_inicio',
                ],
            ],
            [
                'codigo_entrega.string' =>
                    'O código da entrega informado é inválido.',

                'codigo_entrega.max' =>
                    'O código da entrega pode possuir no máximo 100 caracteres.',

                'data_inicio.date' =>
                    'A data inicial informada é inválida.',

                'data_fim.date' =>
                    'A data final informada é inválida.',

                'data_fim.after_or_equal' =>
                    'A data final deve ser igual ou posterior à data inicial.',
            ]
        );

        $statusMap = [
            'pendente_pagamento' =>
                'Pendente_pagamento',

            'aguardando_faturamento' =>
                'Aguardando_faturamento',

            'aguardando_separacao' =>
                'Aguardando_separacao',

            'separando' =>
                'Em_preparacao',

            'em_preparacao' =>
                'Em_preparacao',

            'pronta_para_carregamento' =>
                'Pronta_para_carregamento',

            'carregado' =>
                'Carregada',

            'carregada' =>
                'Carregada',

            'liberada' =>
                'Liberada',

            'em_rota' =>
                'Em_rota',

            'no_destino' =>
                'No_destino',

            'entregue' =>
                'Entregue',

            'parcial' =>
                'Entregue_parcial',

            'entregue_parcial' =>
                'Entregue_parcial',

            'nao_entregue' =>
                'Nao_entregue',

            'recusada' =>
                'Recusada',

            'reagendada' =>
                'Reagendada',

            'devolvido' =>
                'Devolvida',

            'devolvida' =>
                'Devolvida',

            'cancelado' =>
                'Cancelada',

            'cancelada' =>
                'Cancelada',
        ];

        $dataInicio = ! empty(
            $dadosValidados['data_inicio'] ?? null
        )
            ? \Carbon\Carbon::parse(
                $dadosValidados['data_inicio']
            )->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dataFim = ! empty(
            $dadosValidados['data_fim'] ?? null
        )
            ? \Carbon\Carbon::parse(
                $dadosValidados['data_fim']
            )->endOfDay()
            : now()->endOfDay();

        $query = Entrega::query()
        ->with([
            'venda',
            'orcamento',
            'itens',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ])
        ->whereBetween(
            'data_prevista',
            [
                $dataInicio,
                $dataFim,
            ]
        );

        if (! empty(
            $dadosValidados['status'] ?? null
        )) {
            $statusInformado = strtolower(
                trim(
                    (string) $dadosValidados['status']
                )
            );

            if (isset($statusMap[$statusInformado])) {
                $query->where(
                    'status',
                    $statusMap[$statusInformado]
                );
            }
        }

        if (! empty(
            $dadosValidados['codigo_entrega'] ?? null
        )) {
            $query->where(
                'codigo_entrega',
                'like',
                '%' . trim(
                    (string) $dadosValidados['codigo_entrega']
                ) . '%'
            );
        }

        /*
        * Entregas canceladas ficam sempre no final.
        * As demais continuam ordenadas pela data prevista
        * e pelo identificador.
        */
        $query
            ->orderByRaw(
                "
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'cancelada',
                            'cancelado'
                        ) THEN 1
                        ELSE 0
                    END ASC
                "
            )
            ->orderBy('data_prevista')
            ->orderBy('id');

        $entregas = $query
            ->paginate(20)
            ->withQueryString();

        $resumo = [
            'pendente_pagamento' =>
                Entrega::where(
                    'status',
                    'Pendente_pagamento'
                )->count(),

            'aguardando_separacao' =>
                Entrega::where(
                    'status',
                    'Aguardando_separacao'
                )->count(),

            'separando' =>
                Entrega::where(
                    'status',
                    'Em_preparacao'
                )->count(),

            'carregados' =>
                Entrega::where(
                    'status',
                    'Carregada'
                )->count(),

            'em_rota' =>
                Entrega::where(
                    'status',
                    'Em_rota'
                )->count(),

            'entregues' =>
                Entrega::where(
                    'status',
                    'Entregue'
                )->count(),

            'parciais' =>
                Entrega::where(
                    'status',
                    'Entregue_parcial'
                )->count(),

            'devolvidos' =>
                Entrega::where(
                    'status',
                    'Devolvida'
                )->count(),

            'cancelados' =>
                Entrega::where(
                    'status',
                    'Cancelada'
                )->count(),

            'atrasadas' =>
                Entrega::whereDate(
                    'data_prevista',
                    '<',
                    now()->toDateString()
                )
                    ->whereNotIn(
                        'status',
                        [
                            'Entregue',
                            'Cancelada',
                            'Devolvida',
                        ]
                    )
                    ->count(),
        ];

        return view(
            'entregas.index',
            compact(
                'entregas',
                'resumo',
                'dataInicio',
                'dataFim'
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

    public function retorno(Entrega $entrega)
    {
        $romaneio = Romaneio::query()
            ->with([
                'motorista',
                'veiculo',

                'itens.conferenteRetorno',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',
            ])
            ->where('entrega_id', $entrega->id)
            ->whereNotIn('status', [
                'Fechado',
                'Cancelado',
            ])
            ->latest('id')
            ->first();

        if (! $romaneio) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'Não foi encontrado um romaneio ativo para esta entrega.'
                );
        }

        $statusPermitidos = [
            'Em_rota',
            'Retornando',
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
        ];

        if (! in_array(
            $romaneio->status,
            $statusPermitidos,
            true
        )) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'O romaneio não está disponível para operação de retorno.'
                );
        }

        $entrega->loadMissing([
            'cliente',
            'motorista',
            'veiculo',
            'venda.cliente',
            'orcamento.cliente',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ]);

        $funcionariosOperacionais = Funcionario::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        return view(
            'entregas.retorno',
            compact(
                'entrega',
                'romaneio',
                'funcionariosOperacionais'
            )
        );
    }

    public function iniciarConferenciaRetorno(Request $request, Entrega $entrega)
    {
        $dadosValidados = $request->validate(
            [
                'retorno_conferido_por' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],
            ],
            [
                'retorno_conferido_por.required' =>
                    'Informe o funcionário responsável pela conferência do retorno.',

                'retorno_conferido_por.integer' =>
                    'O funcionário informado é inválido.',

                'retorno_conferido_por.exists' =>
                    'O funcionário selecionado não foi encontrado.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where('entrega_id', $entrega->id)
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (
                $romaneio->status
                !== 'Aguardando_conferencia_retorno'
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'O romaneio não está aguardando conferência do retorno.',
                ]);
            }

            /*
            * Enviamos somente a identificação dos itens.
            * As quantidades permanecem com a declaração inicial
            * até o conferente verificar fisicamente os produtos.
            */
            $itens = $romaneio->itens
                ->map(
                    fn ($item) => [
                        'romaneio_item_id' =>
                            $item->id,

                        'entrega_item_id' =>
                            $item->entrega_item_id,
                    ]
                )
                ->values()
                ->all();

            $this->romaneioService->atualizarOperacao(
                $romaneio,
                'iniciar_conferencia_retorno',
                [
                    'retorno_conferido_por' =>
                        $dadosValidados['retorno_conferido_por'],

                    'itens' =>
                        $itens,
                ]
            );

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Conferência física do retorno iniciada.'
                );

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao iniciar a conferência do retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function finalizarConferenciaRetorno(Request $request, Entrega $entrega) 
    {
        $dadosValidados = $request->validate(
            [
                'retorno_conferido_por' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'itens' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'itens.*.romaneio_item_id' => [
                    'required',
                    'integer',
                    'exists:romaneio_itens,id',
                ],

                'itens.*.entrega_item_id' => [
                    'required',
                    'integer',
                    'exists:entrega_itens,id',
                ],

                'itens.*.quantidade_entregue' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_devolvida' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_recusada' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_avariada' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_perdida' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.observacao' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ],
            [
                'retorno_conferido_por.required' =>
                    'Informe o responsável pela conferência.',

                'itens.required' =>
                    'Informe o resultado conferido dos produtos.',

                'itens.min' =>
                    'Informe o resultado de pelo menos um produto.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where('entrega_id', $entrega->id)
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (
                $romaneio->status
                !== 'Em_conferencia_retorno'
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'A conferência do retorno ainda não foi iniciada.',
                ]);
            }

            $itensDoRomaneio = $romaneio->itens->keyBy('id');

            foreach (
                $dadosValidados['itens']
                as $indice => $dadosItem
            ) {
                $romaneioItem = $itensDoRomaneio->get(
                    (int) $dadosItem['romaneio_item_id']
                );

                if (
                    ! $romaneioItem
                    || (int) $romaneioItem->entrega_item_id
                        !== (int) $dadosItem['entrega_item_id']
                ) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.romaneio_item_id" =>
                            'O produto informado não pertence a este romaneio.',
                    ]);
                }

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    2
                );

                $totalResultado = round(
                    (float) $dadosItem['quantidade_entregue']
                    + (float) $dadosItem['quantidade_devolvida']
                    + (float) $dadosItem['quantidade_recusada']
                    + (float) $dadosItem['quantidade_avariada']
                    + (float) $dadosItem['quantidade_perdida'],
                    2
                );

                if ($totalResultado !== $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.quantidade_entregue" =>
                            "A soma do resultado do item #{$romaneioItem->entrega_item_id} deve ser igual à quantidade conferida na saída: "
                            . number_format(
                                $quantidadeSaida,
                                2,
                                ',',
                                '.'
                            )
                            . '.',
                    ]);
                }
            }

            $this->romaneioService->atualizarOperacao(
                $romaneio,
                'finalizar_conferencia_retorno',
                [
                    'retorno_conferido_por' =>
                        $dadosValidados['retorno_conferido_por'],

                    'observacao' =>
                        $dadosValidados['observacao']
                        ?? null,

                    'itens' =>
                        $dadosValidados['itens'],
                ]
            );

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Conferência do retorno concluída. O romaneio está aguardando a prestação de contas.'
                );

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao finalizar a conferência do retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function registrarRetorno(Request $request, Entrega $entrega) 
    {
        $dadosBasicos = $request->validate(
            [
                'tipo_retorno' => [
                    'required',
                    Rule::in([
                        'normal',
                        'ocorrencia',
                    ]),
                ],

                'observacao_retorno' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'tipo_retorno.required' =>
                    'Informe se a entrega ocorreu normalmente ou se houve alguma falha.',

                'tipo_retorno.in' =>
                    'O tipo de retorno informado é inválido.',

                'observacao_retorno.max' =>
                    'A observação geral pode possuir no máximo 1000 caracteres.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where('entrega_id', $entrega->id)
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (! in_array(
                $romaneio->status,
                [
                    'Em_rota',
                    'Retornando',
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'O romaneio não está disponível para registro de retorno.',
                ]);
            }

            if ($dadosBasicos['tipo_retorno'] === 'normal') {
                $itens = $romaneio->itens
                    ->map(function ($romaneioItem) {
                        $quantidadeSaida = round(
                            (float) $romaneioItem
                                ->quantidade_conferida_saida,
                            2
                        );

                        return [
                            'romaneio_item_id' =>
                                $romaneioItem->id,

                            'entrega_item_id' =>
                                $romaneioItem->entrega_item_id,

                            'quantidade_entregue' =>
                                $quantidadeSaida,

                            'quantidade_devolvida' =>
                                0,

                            'quantidade_recusada' =>
                                0,

                            'quantidade_avariada' =>
                                0,

                            'quantidade_perdida' =>
                                0,

                            'observacao' =>
                                null,
                        ];
                    })
                    ->values()
                    ->all();

                $observacaoRetorno =
                    'Entrega realizada normalmente, sem ocorrências.';
            } else {
                $dadosOcorrencia = $request->validate(
                    [
                        'itens' => [
                            'required',
                            'array',
                            'min:1',
                        ],

                        'itens.*.romaneio_item_id' => [
                            'required',
                            'integer',
                            'exists:romaneio_itens,id',
                        ],

                        'itens.*.entrega_item_id' => [
                            'required',
                            'integer',
                            'exists:entrega_itens,id',
                        ],

                        'itens.*.quantidade_entregue' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_devolvida' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_recusada' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_avariada' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_perdida' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.observacao' => [
                            'nullable',
                            'string',
                            'max:500',
                        ],
                    ],
                    [
                        'itens.required' =>
                            'Informe o resultado dos produtos transportados.',

                        'itens.min' =>
                            'Informe o resultado de pelo menos um produto.',

                        'itens.*.romaneio_item_id.required' =>
                            'Não foi possível identificar um item do romaneio.',

                        'itens.*.entrega_item_id.required' =>
                            'Não foi possível identificar um item da entrega.',

                        'itens.*.quantidade_entregue.required' =>
                            'Informe a quantidade entregue.',

                        'itens.*.quantidade_devolvida.required' =>
                            'Informe a quantidade devolvida.',

                        'itens.*.quantidade_recusada.required' =>
                            'Informe a quantidade recusada.',

                        'itens.*.quantidade_avariada.required' =>
                            'Informe a quantidade avariada.',

                        'itens.*.quantidade_perdida.required' =>
                            'Informe a quantidade perdida.',
                    ]
                );

                $itens = $dadosOcorrencia['itens'];

                $observacaoRetorno = trim(
                    (string) (
                        $dadosBasicos['observacao_retorno']
                        ?? ''
                    )
                );

                $possuiOcorrenciaProduto = collect($itens)
                    ->contains(function ($item) {
                        return
                            round(
                                (float) (
                                    $item['quantidade_devolvida']
                                    ?? 0
                                ),
                                2
                            ) > 0
                            || round(
                                (float) (
                                    $item['quantidade_recusada']
                                    ?? 0
                                ),
                                2
                            ) > 0
                            || round(
                                (float) (
                                    $item['quantidade_avariada']
                                    ?? 0
                                ),
                                2
                            ) > 0
                            || round(
                                (float) (
                                    $item['quantidade_perdida']
                                    ?? 0
                                ),
                                2
                            ) > 0
                            || trim(
                                (string) (
                                    $item['observacao']
                                    ?? ''
                                )
                            ) !== '';
                    });

                if (
                    ! $possuiOcorrenciaProduto
                    && $observacaoRetorno === ''
                ) {
                    throw ValidationException::withMessages([
                        'observacao_retorno' =>
                            'Descreva a falha ocorrida no trajeto ou informe a ocorrência de pelo menos um produto.',
                    ]);
                }
            }

            $itensDoRomaneio = $romaneio->itens
                ->keyBy('id');

            foreach ($itens as $indice => $dadosItem) {
                $romaneioItem = $itensDoRomaneio->get(
                    (int) $dadosItem['romaneio_item_id']
                );

                if (
                    ! $romaneioItem
                    || (int) $romaneioItem->entrega_item_id
                        !== (int) $dadosItem['entrega_item_id']
                ) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.romaneio_item_id" =>
                            'O produto informado não pertence a este romaneio.',
                    ]);
                }

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    2
                );

                $quantidadeEntregue = round(
                    (float) $dadosItem['quantidade_entregue'],
                    2
                );

                $quantidadeDevolvida = round(
                    (float) $dadosItem['quantidade_devolvida'],
                    2
                );

                $quantidadeRecusada = round(
                    (float) $dadosItem['quantidade_recusada'],
                    2
                );

                $quantidadeAvariada = round(
                    (float) $dadosItem['quantidade_avariada'],
                    2
                );

                $quantidadePerdida = round(
                    (float) $dadosItem['quantidade_perdida'],
                    2
                );

                $totalResultado = round(
                    $quantidadeEntregue
                    + $quantidadeDevolvida
                    + $quantidadeRecusada
                    + $quantidadeAvariada
                    + $quantidadePerdida,
                    2
                );

                if ($totalResultado !== $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.quantidade_entregue" =>
                            'A soma do resultado do produto deve ser igual à quantidade que saiu no romaneio: '
                            . number_format(
                                $quantidadeSaida,
                                2,
                                ',',
                                '.'
                            )
                            . '.',
                    ]);
                }
            }

            $this->romaneioService->atualizarOperacao(
                $romaneio,
                'registrar_retorno',
                [
                    'tipo_retorno' =>
                        $dadosBasicos['tipo_retorno'],

                    'itens' =>
                        $itens,

                    'observacao_retorno' =>
                        $observacaoRetorno,
                ]
            );

            if ($dadosBasicos['tipo_retorno'] === 'normal') {
                return redirect()
                    ->route('entregas.index')
                    ->with(
                        'success',
                        'Entrega finalizada normalmente. O romaneio foi encaminhado para a prestação de contas.'
                    );
            }

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Retorno com ocorrência registrado. O romaneio está aguardando a conferência física.'
                );
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao registrar o retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function imprimirRelatorioRetorno(Entrega $entrega) 
    {
        $entrega->loadMissing([
            'cliente',
            'motorista',
            'veiculo',
            'venda.cliente',
            'orcamento.cliente',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ]);

        $romaneio = Romaneio::query()
            ->with([
                'motorista',
                'veiculo',
                'usuarioRegistroRetorno',

                'itens.conferenteRetorno',
                'itens.entregaItem',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',

                'eventos' => function ($query) {
                    $query->orderBy(
                        'ocorrido_em',
                        'asc'
                    );
                },

                'eventos.usuario',
                'eventos.funcionario',
            ])
            ->where(
                'entrega_id',
                $entrega->id
            )
            ->where(
                'status',
                '!=',
                'Cancelado'
            )
            ->latest('id')
            ->first();

        if (! $romaneio) {
            return redirect()
                ->route('entregas.show', $entrega->id)
                ->with(
                    'error',
                    'Não foi encontrado um romaneio para gerar o relatório de retorno.'
                );
        }

        $statusPermitidos = [
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
            'Fechado',
        ];

        if (
            ! in_array(
                $romaneio->status,
                $statusPermitidos,
                true
            )
            && empty($romaneio->data_retorno)
        ) {
            return redirect()
                ->route('entregas.retorno', $entrega->id)
                ->with(
                    'error',
                    'O retorno desta entrega ainda não foi registrado.'
                );
        }

        $cliente =
            $entrega->cliente
            ?? $entrega->venda?->cliente
            ?? $entrega->orcamento?->cliente;

        $eventoRetorno = $romaneio->eventos
            ->filter(function ($evento) {
                $nomeEvento = strtolower(
                    trim(
                        (string) (
                            $evento->evento
                            ?? ''
                        )
                    )
                );

                return str_contains(
                    $nomeEvento,
                    'retorno'
                );
            })
            ->last();

        $observacaoRetorno = trim(
            (string) (
                $eventoRetorno?->observacao
                ?? $romaneio->observacao
                ?? ''
            )
        );

        $possuiOcorrenciaProduto = $romaneio->itens
            ->contains(function ($item) {
                return
                    round(
                        (float) (
                            $item->quantidade_devolvida
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_recusada
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_avariada
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_perdida
                            ?? 0
                        ),
                        2
                    ) > 0
                    || trim(
                        (string) (
                            $item->observacao
                            ?? ''
                        )
                    ) !== '';
            });

        $eventoIndicaOcorrencia = $romaneio->eventos
            ->contains(function ($evento) {
                $nomeEvento = strtolower(
                    trim(
                        (string) (
                            $evento->evento
                            ?? ''
                        )
                    )
                );

                return str_contains(
                    $nomeEvento,
                    'ocorrência'
                )
                    || str_contains(
                        $nomeEvento,
                        'ocorrencia'
                    );
            });

        $possuiOcorrencias =
            $possuiOcorrenciaProduto
            || $eventoIndicaOcorrencia;

        return view(
            'entregas.relatorio-retorno',
            compact(
                'entrega',
                'romaneio',
                'cliente',
                'eventoRetorno',
                'observacaoRetorno',
                'possuiOcorrencias'
            )
        );
    }

}