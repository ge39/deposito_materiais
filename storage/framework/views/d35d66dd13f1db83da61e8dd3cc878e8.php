show.blade.php




<?php $__env->startSection('content'); ?>

<?php
    $statusEntrega = strtolower(
        trim(
            str_replace(
                ' ',
                '_',
                (string) ($entrega->status ?? '')
            )
        )
    );

    $romaneio = $entrega->romaneio ?? null;

    $statusRomaneio = strtolower(
        trim(
            str_replace(
                ' ',
                '_',
                (string) ($romaneio?->status ?? '')
            )
        )
    );

    $statusLabels = [
        'pendente_pagamento' => 'Pendente pagamento',
        'aguardando_faturamento' => 'Aguardando faturamento',
        'aguardando_separacao' => 'Aguardando separação',
        'em_preparacao' => 'Em preparação',
        'pronta_para_carregamento' => 'Pronta para carregamento',
        'carregada' => 'Carregada',
        'liberada' => 'Liberada',
        'em_rota' => 'Em rota',
        'no_destino' => 'No destino',
        'entregue' => 'Entregue',
        'entregue_parcial' => 'Entregue parcial',
        'entregue_finalizada_com_ocorrencia' =>
            'Entregue Finalizada com ocorrência',
        'nao_entregue' => 'Não entregue',
        'recusada' => 'Recusada',
        'reagendada' => 'Reagendada',
        'devolvida' => 'Devolvida',
        'cancelada' => 'Cancelada',
    ];

    $statusClasses = [
        'pendente_pagamento' => 'bg-secondary',
        'aguardando_faturamento' => 'bg-secondary',
        'aguardando_separacao' => 'bg-warning text-dark',
        'em_preparacao' => 'bg-primary',
        'pronta_para_carregamento' => 'bg-info text-dark',
        'carregada' => 'bg-info text-dark',
        'liberada' => 'bg-success',
        'em_rota' => 'bg-dark',
        'no_destino' => 'bg-primary',
        'entregue' => 'bg-success',
        'entregue_parcial' => 'bg-warning text-dark',
        'entregue_finalizada_com_ocorrencia' =>
            'bg-warning text-dark',
        'nao_entregue' => 'bg-danger',
        'recusada' => 'bg-danger',
        'reagendada' => 'bg-warning text-dark',
        'devolvida' => 'bg-danger',
        'cancelada' => 'bg-danger',
    ];

    $progressoStatus = [
        'pendente_pagamento' => 10,
        'aguardando_faturamento' => 15,
        'aguardando_separacao' => 20,
        'em_preparacao' => 35,
        'pronta_para_carregamento' => 50,
        'carregada' => 65,
        'liberada' => 75,
        'em_rota' => 85,
        'no_destino' => 90,
        'entregue_parcial' => 95,
        'entregue_finalizada_com_ocorrencia' => 100,
        'nao_entregue' => 95,
        'recusada' => 95,
        'reagendada' => 95,
        'devolvida' => 100,
        'cancelada' => 100,
        'entregue' => 100,
    ];

    $percentual = $progressoStatus[$statusEntrega] ?? 0;

    $dataPrevista = $entrega->data_prevista_entrega
        ? \Carbon\Carbon::parse($entrega->data_prevista_entrega)
        : (
            $entrega->data_prevista
                ? \Carbon\Carbon::parse($entrega->data_prevista)
                : null
        );

    $dataRealizada = $entrega->data_realizada
        ? \Carbon\Carbon::parse($entrega->data_realizada)
        : null;

    $periodoEntrega = $entrega->periodo_entrega ?? null;

    $observacaoEntrega = $entrega->observacao_entrega
        ?? $entrega->observacao
        ?? null;

    $resultadosItens = collect(
        $resultadosItens ?? []
    );

    $contextosItensVenda = collect(
        $contextosItensVenda ?? []
    );

    $contextosItensOrcamento = collect(
        $contextosItensOrcamento ?? []
    );

    $historicoEntregasFracionadas = collect(
        $historicoEntregasFracionadas ?? []
    );

    $resolverResultadoItem = function (
        $entregaItem
    ) use ($resultadosItens) {
        $quantidadePrevista = round(
            (float) (
                $entregaItem?->quantidade_prevista
                ?? 0
            ),
            3
        );

        $resultado = $resultadosItens->get(
            (int) (
                $entregaItem?->id
                ?? 0
            )
        );

        $quantidadeEntregue = round(
            (float) (
                $resultado?->quantidade_entregue
                ?? $entregaItem?->quantidade_entregue
                ?? 0
            ),
            3
        );

        $quantidadeDevolvida = round(
            (float) (
                $resultado?->quantidade_devolvida
                ?? $entregaItem?->quantidade_devolvida
                ?? 0
            ),
            3
        );

        $quantidadeRecusada = round(
            (float) (
                $resultado?->quantidade_recusada
                ?? 0
            ),
            3
        );

        $quantidadeAvariada = round(
            (float) (
                $resultado?->quantidade_avariada
                ?? 0
            ),
            3
        );

        $quantidadePerdida = round(
            (float) (
                $resultado?->quantidade_perdida
                ?? 0
            ),
            3
        );

        $quantidadeComOcorrencia = round(
            $quantidadeDevolvida
            + $quantidadeRecusada
            + $quantidadeAvariada
            + $quantidadePerdida,
            3
        );

        $quantidadeApurada = round(
            min(
                $quantidadePrevista,
                $quantidadeEntregue
                + $quantidadeComOcorrencia
            ),
            3
        );

        $saldo = round(
            max(
                $quantidadePrevista
                - $quantidadeApurada,
                0
            ),
            3
        );

        $statusOriginal = strtolower(
            trim(
                str_replace(
                    ' ',
                    '_',
                    (string) (
                        $entregaItem?->status
                        ?? 'pendente'
                    )
                )
            )
        );

        $status = match (true) {
            $saldo < 0.001
                && $quantidadeComOcorrencia > 0 =>
                    'finalizado_com_ocorrencia',

            $saldo < 0.001
                && $quantidadePrevista > 0 =>
                    'entregue',

            $quantidadeEntregue > 0 =>
                    'entregue_parcial',

            $quantidadeComOcorrencia > 0 =>
                    'ocorrencia_pendente',

            default =>
                    $statusOriginal,
        };

        return [
            'quantidade_prevista' =>
                $quantidadePrevista,

            'quantidade_entregue' =>
                $quantidadeEntregue,

            'quantidade_com_ocorrencia' =>
                $quantidadeComOcorrencia,

            'quantidade_apurada' =>
                $quantidadeApurada,

            'saldo' =>
                $saldo,

            'status' =>
                $status,
        ];
    };

    $itensBase = collect();
    $origemItens = '-';

    if (
        $entrega->venda_id
        && $entrega->venda
        && $entrega->venda->itens
    ) {
        $itensBase = collect(
            $entrega->venda->itens
        );

        $origemItens = 'Venda';
    } elseif (
        $entrega->orcamento_id
        && $entrega->orcamento
        && $entrega->orcamento->itens
    ) {
        $itensBase = collect(
            $entrega->orcamento->itens
        );

        $origemItens = 'Orçamento';
    }

    $itensOperacionais = collect(
        $entrega->itens ?? []
    );

    $resolverExibicaoItem = function (
        $itemBase
    ) use (
        $origemItens,
        $itensOperacionais,
        $contextosItensVenda,
        $contextosItensOrcamento,
        $resolverResultadoItem
    ) {
        $entregaItem = null;

        if ($origemItens === 'Venda') {
            $entregaItem = $itensOperacionais
                ->first(
                    fn ($itemOperacional) =>
                        (int) $itemOperacional->venda_item_id
                        === (int) $itemBase->id
                );

            $contexto = $contextosItensVenda->get(
                (int) $itemBase->id
            );
        } else {
            $entregaItem = $itensOperacionais
                ->first(
                    fn ($itemOperacional) =>
                        (int) $itemOperacional->item_orcamento_id
                        === (int) $itemBase->id
                );

            $contexto = $contextosItensOrcamento->get(
                (int) $itemBase->id
            );
        }

        $quantidadeBase = round(
            (float) (
                $itemBase?->quantidade
                ?? $itemBase?->qtd
                ?? $itemBase?->quantidade_vendida
                ?? $itemBase?->quantidade_orcada
                ?? $itemBase?->quantidade_solicitada
                ?? 0
            ),
            3
        );

        $resultadoOperacionalAtual = $entregaItem
            ? $resolverResultadoItem($entregaItem)
            : null;

        $statusOperacionalAtual = (string) (
            $resultadoOperacionalAtual['status']
            ?? 'pendente'
        );

        $contextoFornecido = (bool) $contexto;

        if (! $contexto) {
            $contexto = [
                'possui_item_atual' =>
                    (bool) $entregaItem,

                'quantidade_prevista_atual' =>
                    (float) (
                        $resultadoOperacionalAtual[
                            'quantidade_prevista'
                        ]
                        ?? 0
                    ),

                'quantidade_entregue_anterior' =>
                    0.0,

                'quantidade_ocorrencia_anterior' =>
                    0.0,

                'quantidade_ocorrencia_finalizada_anterior' =>
                    0.0,

                'quantidade_entregue_atual' =>
                    (float) (
                        $resultadoOperacionalAtual[
                            'quantidade_entregue'
                        ]
                        ?? 0
                    ),

                'quantidade_ocorrencia_atual' =>
                    (float) (
                        $resultadoOperacionalAtual[
                            'quantidade_com_ocorrencia'
                        ]
                        ?? 0
                    ),

                'quantidade_ocorrencia_finalizada_atual' =>
                    $statusOperacionalAtual
                        === 'finalizado_com_ocorrencia'
                    ? (float) (
                        $resultadoOperacionalAtual[
                            'quantidade_com_ocorrencia'
                        ]
                        ?? 0
                    )
                    : 0.0,

                'quantidade_encaminhada_proxima' =>
                    0.0,
            ];
        }

        $possuiItemAtual = (bool) (
            $contexto['possui_item_atual']
            ?? false
        );

        $quantidadePrevistaAtual = round(
            (float) (
                $contexto['quantidade_prevista_atual']
                ?? 0
            ),
            3
        );

        $quantidadeEntregueAnterior = round(
            (float) (
                $contexto['quantidade_entregue_anterior']
                ?? 0
            ),
            3
        );

        $quantidadeOcorrenciaAnterior = round(
            (float) (
                $contexto['quantidade_ocorrencia_anterior']
                ?? 0
            ),
            3
        );

        $quantidadeOcorrenciaFinalizadaAnterior = round(
            (float) (
                $contexto[
                    'quantidade_ocorrencia_finalizada_anterior'
                ]
                ?? 0
            ),
            3
        );

        $quantidadeEntregueAtual = round(
            (float) (
                $contexto['quantidade_entregue_atual']
                ?? 0
            ),
            3
        );

        $quantidadeOcorrenciaAtual = round(
            (float) (
                $contexto['quantidade_ocorrencia_atual']
                ?? 0
            ),
            3
        );

        $quantidadeOcorrenciaFinalizadaAtual = round(
            (float) (
                $contexto[
                    'quantidade_ocorrencia_finalizada_atual'
                ]
                ?? 0
            ),
            3
        );

        if (
            $contextoFornecido
            && $statusOperacionalAtual
                === 'finalizado_com_ocorrencia'
            && $quantidadeOcorrenciaFinalizadaAtual < 0.001
        ) {
            $statusOperacionalAtual =
                'ocorrencia_pendente';
        }

        $quantidadeEncaminhadaProxima = round(
            (float) (
                $contexto['quantidade_encaminhada_proxima']
                ?? 0
            ),
            3
        );

        $quantidadeEntregueAcumulada = round(
            min(
                $quantidadeBase,
                $quantidadeEntregueAnterior
                + $quantidadeEntregueAtual
            ),
            3
        );

        $quantidadeOcorrenciaAcumulada = round(
            $quantidadeOcorrenciaAnterior
            + $quantidadeOcorrenciaAtual,
            3
        );

        $quantidadeOcorrenciaFinalizadaAcumulada = round(
            $quantidadeOcorrenciaFinalizadaAnterior
            + $quantidadeOcorrenciaFinalizadaAtual,
            3
        );

        $quantidadeApuradaAcumulada = round(
            min(
                $quantidadeBase,
                $quantidadeEntregueAcumulada
                + $quantidadeOcorrenciaFinalizadaAcumulada
            ),
            3
        );

        $saldo = round(
            max(
                $quantidadeBase
                - $quantidadeApuradaAcumulada,
                0
            ),
            3
        );

        $statusItem = match (true) {
            $quantidadeOcorrenciaFinalizadaAtual > 0 =>
                    'finalizado_com_ocorrencia',

            $quantidadeEntregueAtual > 0
                && $saldo < 0.001 =>
                    'entregue_nesta_entrega',

            $quantidadeEntregueAtual > 0
                && $quantidadeEncaminhadaProxima > 0 =>
                    'parcial_encaminhado',

            $quantidadeEntregueAtual > 0 =>
                    'entregue_parcial',

            $possuiItemAtual
                && $quantidadeEncaminhadaProxima > 0 =>
                    'encaminhado_proxima',

            $possuiItemAtual
                && ! in_array(
                    $statusOperacionalAtual,
                    ['', 'pendente'],
                    true
                ) =>
                    $statusOperacionalAtual,

            $possuiItemAtual =>
                    'pendente_nesta_entrega',

            $saldo < 0.001
                && $quantidadeOcorrenciaFinalizadaAnterior > 0 =>
                    'finalizado_com_ocorrencia_anterior',

            $quantidadeEntregueAnterior + 0.001
                >= $quantidadeBase
                && $quantidadeBase > 0 =>
                    'entregue_anteriormente',

            $quantidadeEntregueAnterior > 0 =>
                    'entregue_parcial_anteriormente',

            default =>
                    'pendente',
        };

        return [
            'entrega_item' =>
                $entregaItem,

            'quantidade_base' =>
                $quantidadeBase,

            'quantidade_prevista_atual' =>
                $quantidadePrevistaAtual,

            'quantidade_entregue_anterior' =>
                $quantidadeEntregueAnterior,

            'quantidade_entregue_atual' =>
                $quantidadeEntregueAtual,

            'quantidade_entregue_acumulada' =>
                $quantidadeEntregueAcumulada,

            'quantidade_ocorrencia_acumulada' =>
                $quantidadeOcorrenciaAcumulada,

            'quantidade_ocorrencia_finalizada_acumulada' =>
                $quantidadeOcorrenciaFinalizadaAcumulada,

            'quantidade_encaminhada_proxima' =>
                $quantidadeEncaminhadaProxima,

            'saldo' =>
                $saldo,

            'status' =>
                $statusItem,
        ];
    };

    $totalItens = $itensBase->count();

    $itensEntregues = $itensBase
        ->filter(
            fn ($itemBase) =>
                $resolverExibicaoItem(
                    $itemBase
                )['saldo'] < 0.001
        )
        ->count();

    $mapsUrl = $entrega->endereco_entrega
        ? 'https://www.google.com/maps/search/?api=1&query=' .
            urlencode($entrega->endereco_entrega)
        : null;

    $formatarDataHora = function ($data) {
        if (empty($data)) {
            return null;
        }

        return \Carbon\Carbon::parse($data)
            ->format('d/m/Y H:i');
    };

    /*
    |--------------------------------------------------------------------------
    | Fluxo real do romaneio
    |--------------------------------------------------------------------------
    |
    | Uma etapa somente é marcada como concluída quando existe evidência
    | operacional: data de conclusão, saída, retorno ou fechamento.
    |
    */

    $etapasFluxo = [
        [
            'titulo' => 'Entrega criada',
            'icone' => 'bi-file-earmark-check',
            'concluida' => ! empty($entrega->id),
            'atual' => false,
            'data' => $entrega->created_at,
        ],

        [
            'titulo' => 'Venda faturada',
            'icone' => 'bi-receipt',
            'concluida' => ! empty($entrega->venda_id),
            'atual' => in_array(
                $statusEntrega,
                [
                    'pendente_pagamento',
                    'aguardando_faturamento',
                ],
                true
            ),
            'data' => $entrega->venda?->created_at,
        ],

        [
            'titulo' => 'Romaneio montado',
            'icone' => 'bi-clipboard-check',
            'concluida' => ! empty($romaneio?->id),
            'atual' => $statusRomaneio === 'montagem',
            'data' => $romaneio?->data_emissao
                ?? $romaneio?->created_at,
        ],

        [
            'titulo' => 'Separação',
            'icone' => 'bi-box-seam',
            'concluida' => ! empty(
                $romaneio?->data_fim_separacao
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'aguardando_separacao',
                    'em_separacao',
                ],
                true
            ),
            'data' => $romaneio?->data_inicio_separacao,
        ],

        [
            'titulo' => 'Conferência da separação',
            'icone' => 'bi-clipboard2-check',
            'concluida' => ! empty(
                $romaneio?->data_fim_conferencia_separacao
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'aguardando_conferencia_separacao',
                    'em_conferencia_separacao',
                    'separacao_conferida',
                ],
                true
            ),
            'data' => $romaneio?->data_inicio_conferencia_separacao,
        ],

        [
            'titulo' => 'Carregamento',
            'icone' => 'bi-truck-front',
            'concluida' => ! empty(
                $romaneio?->data_fim_carregamento
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'aguardando_carregamento',
                    'carregando',
                ],
                true
            ),
            'data' => $romaneio?->data_inicio_carregamento,
        ],

        [
            'titulo' => 'Conferência de saída',
            'icone' => 'bi-clipboard-data',
            'concluida' => ! empty(
                $romaneio?->data_fim_conferencia_saida
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'aguardando_conferencia_saida',
                    'em_conferencia_saida',
                ],
                true
            ),
            'data' => $romaneio?->data_inicio_conferencia_saida,
        ],

        [
            'titulo' => 'Veículo liberado',
            'icone' => 'bi-shield-check',
            'concluida' => in_array(
                $statusRomaneio,
                [
                    'liberado',
                    'em_rota',
                    'retornando',
                    'aguardando_conferencia_retorno',
                    'em_conferencia_retorno',
                    'aguardando_prestacao_contas',
                    'em_prestacao_contas',
                    'aguardando_fechamento',
                    'fechado',
                ],
                true
            ),
            'atual' => $statusRomaneio === 'aguardando_liberacao',
            'data' => $romaneio?->data_fim_conferencia_saida,
        ],

        [
            'titulo' => 'Saiu para entrega',
            'icone' => 'bi-sign-turn-right',
            'concluida' => ! empty(
                $romaneio?->data_saida
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'liberado',
                    'em_rota',
                ],
                true
            ),
            'data' => $romaneio?->data_saida,
        ],

        [
            'titulo' => 'Retorno registrado',
            'icone' => 'bi-arrow-return-left',
            'concluida' => ! empty(
                $romaneio?->data_retorno
            ),
            'atual' => in_array(
                $statusRomaneio,
                [
                    'retornando',
                    'aguardando_conferencia_retorno',
                    'em_conferencia_retorno',
                ],
                true
            ),
            'data' => $romaneio?->data_retorno,
        ],

        [
            'titulo' => 'Resultado da entrega',
            'icone' => 'bi-check2-circle',
            'concluida' => in_array(
                $statusEntrega,
                [
                    'entregue',
                    'entregue_parcial',
                    'entregue_finalizada_com_ocorrencia',
                    'nao_entregue',
                    'recusada',
                    'reagendada',
                    'devolvida',
                ],
                true
            ),
            'atual' => in_array(
                $statusEntrega,
                [
                    'em_rota',
                    'no_destino',
                ],
                true
            ),
            'data' => $entrega->data_realizada,
        ],
    ];

    $etapasConcluidas = collect($etapasFluxo)
        ->where('concluida', true)
        ->count();

    $percentualFluxo = count($etapasFluxo) > 0
        ? round(
            ($etapasConcluidas / count($etapasFluxo)) * 100
        )
        : 0;

    if (
        in_array(
            $statusEntrega,
            [
                'cancelada',
                'devolvida',
            ],
            true
        )
        || $statusRomaneio === 'cancelado'
    ) {
        $percentualFluxo = $percentual;
    }
?>

<style>
    .kpi-card {
        border-radius: 6px;
    }

    .kpi-card .card-body {
        padding: 10px 12px;
    }

    .kpi-card small {
        color: #6c757d;
        font-size: .72rem;
    }

    .kpi-card h5 {
        font-weight: 700;
        margin: 0;
    }

    .timeline-entrega {
        padding-left: 30px;
        position: relative;
    }

    .timeline-entrega::before {
        background: #dee2e6;
        bottom: 4px;
        content: "";
        left: 10px;
        position: absolute;
        top: 4px;
        width: 2px;
    }

    .timeline-item {
        margin-bottom: 17px;
        position: relative;
    }

    .timeline-icon {
        align-items: center;
        background: #fff;
        border: 2px solid #ced4da;
        border-radius: 50%;
        color: #6c757d;
        display: flex;
        font-size: .7rem;
        height: 22px;
        justify-content: center;
        left: -30px;
        position: absolute;
        top: 0;
        width: 22px;
    }

    .timeline-item.concluida .timeline-icon {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    .timeline-item.atual .timeline-icon {
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 .18rem rgba(13, 110, 253, .15);
        color: #fff;
    }

    .timeline-item.cancelada .timeline-icon {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .timeline-titulo {
        font-size: .83rem;
        font-weight: 650;
    }

    .timeline-data {
        color: #6c757d;
        font-size: .7rem;
    }

    .table-itens th,
    .table-itens td {
        vertical-align: middle;
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-2">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                Fluxo da Entrega
                #<?php echo e($entrega->codigo_entrega ?? $entrega->id); ?>

            </h4>

            <small class="text-muted">
                Acompanhamento da entrega e da operação logística vinculada.
            </small>
        </div>
         
        <div class="d-flex gap-1">
            <a href="<?php echo e(route('entregas.index')); ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
            
                <?php if(
                    $entrega->romaneio
                    && $entrega->romaneio
                        ->ocorrencias()
                        ->exists()
                ): ?>
                <a href="<?php echo e(route(
                        'romaneios.ocorrencias.index',
                        $entrega->romaneio->id
                    )); ?>"
                class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Ocorrências
                </a>
            <?php endif; ?>
        
            <button type="button"
                    onclick="window.print()"
                    class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>
                Imprimir
            </button>

            <a href="<?php echo e(route(
                    'entregas.show',
                    $entrega->id
                )); ?>"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Atualizar
            </a>

            <?php if(
                in_array(
                    $statusEntrega,
                    [
                        'pendente_pagamento',
                        'aguardando_faturamento',
                        'aguardando_separacao',
                    ],
                    true
                )
            ): ?>
                <a href="<?php echo e(route(
                        'entregas.atribuir-equipe',
                        $entrega->id
                    )); ?>"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-truck me-1"></i>
                    Equipe
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-2">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo e(session('success')); ?>


            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-2">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo e(session('error')); ?>


            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if(
        $statusEntrega === 'cancelada'
        || $statusRomaneio === 'cancelado'
    ): ?>
        <div class="alert alert-danger">
            <div class="fw-bold">
                <i class="bi bi-x-octagon me-1"></i>

                <?php echo e($statusEntrega === 'cancelada'
                    ? 'Entrega cancelada'
                    : 'Romaneio cancelado'); ?>

            </div>

            <?php if($romaneio?->motivo_cancelamento): ?>
                <div class="small mt-1">
                    <?php echo e($romaneio->motivo_cancelamento); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>STATUS</small>

                    <h5>
                        <span class="badge <?php echo e($statusClasses[$statusEntrega]
                            ?? 'bg-secondary'); ?>">
                            <?php echo e($statusLabels[$statusEntrega]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $statusEntrega
                                    )
                                )); ?>

                        </span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PREVISÃO</small>

                    <h5>
                        <?php echo e($dataPrevista
                            ? $dataPrevista->format('d/m/Y')
                            : '-'); ?>

                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PERÍODO</small>

                    <h5>
                        <?php echo e($periodoEntrega
                            ? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $periodoEntrega
                                )
                            )
                            : '-'); ?>

                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>ITENS</small>
                    <h5><?php echo e($itensEntregues); ?>/<?php echo e($totalItens); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PROGRESSO</small>
                    <h5><?php echo e($percentualFluxo); ?>%</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>TIPO</small>

                    <h5>
                        <?php if($entrega->tipo_entrega === 'retira_loja'): ?>
                            <span class="badge bg-secondary">
                                Retira loja
                            </span>
                        <?php else: ?>
                            <span class="badge bg-info text-dark">
                                Entrega
                            </span>
                        <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>

    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
                <strong>Andamento da entrega</strong>
                <span><?php echo e($percentualFluxo); ?>%</span>
            </div>

            <div class="progress" style="height: 14px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: <?php echo e(min(
                         $percentualFluxo,
                         100
                     )); ?>%;">
                    <?php echo e($percentualFluxo); ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        
        <div class="col-md-8">
            
             
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-calendar-check me-2"></i>
                        Resumo Operacional
                    </strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">
                            Data prevista entrega
                        </small>

                        <div class="fw-semibold">
                            <?php echo e($dataPrevista
                                ? $dataPrevista->format('d/m/Y')
                                : '-'); ?>

                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Período</small>

                        <div class="fw-semibold">
                            <?php echo e($periodoEntrega
                                ? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $periodoEntrega
                                    )
                                )
                                : '-'); ?>

                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Observação</small>
                        <div><?php echo e($observacaoEntrega ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-info-circle me-2"></i>
                        Dados da Entrega
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Código</small>

                            <div class="fw-semibold">
                                <?php echo e($entrega->codigo_entrega ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Venda</small>

                            <div class="fw-semibold">
                                <?php echo e($entrega->venda_id ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Orçamento</small>

                            <div class="fw-semibold">
                                <?php echo e($entrega->orcamento?->codigo_orcamento
                                    ?? $entrega->orcamento_id
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">
                                Data prevista
                            </small>

                            <div class="fw-semibold">
                                <?php echo e($dataPrevista
                                    ? $dataPrevista->format('d/m/Y')
                                    : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Período da entrega
                            </small>

                            <div class="fw-semibold">
                                <?php echo e($periodoEntrega
                                    ? ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $periodoEntrega
                                        )
                                    )
                                    : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Data realizada
                            </small>

                            <div>
                                <?php echo e($dataRealizada
                                    ? $dataRealizada->format('d/m/Y')
                                    : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Responsável
                            </small>

                            <div class="fw-semibold">
                                <?php echo e($entrega->responsavel_recebimento
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Telefone</small>

                            <div>
                                <?php echo e($entrega->telefone_recebimento
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Motorista</small>

                            <div>
                                <?php echo e($entrega->motorista?->name
                                    ?? $entrega->motorista?->nome
                                    ?? $romaneio?->motorista?->name
                                    ?? $romaneio?->motorista?->nome
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Veículo</small>

                            <div>
                                <?php echo e($entrega->veiculo?->placa
                                    ?? $romaneio?->veiculo?->placa
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Frete</small>

                            <div>
                                <?php if($entrega->cobrar_frete): ?>
                                    R$
                                    <?php echo e(number_format(
                                        $entrega->valor_frete ?? 0,
                                        2,
                                        ',',
                                        '.'
                                    )); ?>

                                <?php else: ?>
                                    Sem cobrança
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <small class="text-muted">
                                Observação da entrega
                            </small>

                            <div>
                                <?php echo e($observacaoEntrega ?? '-'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-person-vcard me-2"></i>
                        Dados do Cliente
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-6">
                            <small class="text-muted">Cliente</small>

                            <div class="fw-semibold">
                                <?php echo e($entrega->venda?->cliente?->nome
                                    ?? $entrega->orcamento?->cliente?->nome
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Telefone</small>

                            <div>
                                <?php echo e($entrega->venda?->cliente?->telefone
                                    ?? $entrega->orcamento?->cliente?->telefone
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Documento</small>

                            <div>
                                <?php echo e($entrega->venda?->cliente?->cpf_cnpj
                                    ?? $entrega->orcamento?->cliente?->cpf_cnpj
                                    ?? '-'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-geo-alt me-2"></i>
                        Endereço de Entrega
                    </strong>

                    <?php if($mapsUrl): ?>
                        <a href="<?php echo e($mapsUrl); ?>"
                           target="_blank"
                           class="btn btn-light btn-sm">
                            <i class="bi bi-map me-1"></i>
                            Abrir no Maps
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        <?php echo e($entrega->endereco_entrega
                            ?? 'Endereço não informado'); ?>

                    </div>

                    <small class="text-muted">
                        Usar endereço do cliente:
                        <?php echo e($entrega->usar_endereco_cliente
                            ? 'Sim'
                            : 'Não'); ?>

                    </small>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-box-seam me-2"></i>
                        Itens da Entrega
                    </strong>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0 table-itens">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Produto</th>
                                    <th>Origem</th>
                                    <th>Qtd. Venda/Orçamento</th>
                                    <th>Previsto</th>
                                    <th>Entregue</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                    $statusItemClasses = [
                                        'pendente' => 'bg-secondary',
                                        'preparando' =>
                                            'bg-warning text-dark',
                                        'separado' => 'bg-primary',
                                        'carregado' =>
                                            'bg-info text-dark',
                                        'em_rota' => 'bg-dark',
                                        'entregue' => 'bg-success',
                                        'ocorrencia_pendente' =>
                                            'bg-danger',
                                        'recusado' => 'bg-danger',
                                        'devolvido' => 'bg-danger',
                                        'avariado' => 'bg-danger',
                                        'nao_entregue' => 'bg-danger',
                                        'cancelado' => 'bg-danger',
                                        'pendente_nesta_entrega' =>
                                            'bg-secondary',
                                        'entregue_nesta_entrega' =>
                                            'bg-success',
                                        'entregue_anteriormente' =>
                                            'bg-success',
                                        'entregue_parcial_anteriormente' =>
                                            'bg-warning text-dark',
                                        'entregue_parcial' => 'bg-warning text-dark',
                                        'parcial_encaminhado' =>
                                            'bg-warning text-dark',
                                        'encaminhado_proxima' =>
                                            'bg-info text-dark',
                                        'finalizado_com_ocorrencia' =>
                                            'bg-warning text-dark',
                                        'finalizado_com_ocorrencia_anterior' =>
                                            'bg-warning text-dark',
                                    ];

                                    $statusItemLabels = [
                                        'pendente' =>
                                            'Pendente',
                                        'preparando' =>
                                            'Preparando',
                                        'separado' =>
                                            'Separado',
                                        'carregado' =>
                                            'Carregado',
                                        'em_rota' =>
                                            'Em rota',
                                        'entregue' =>
                                            'Entregue',
                                        'ocorrencia_pendente' =>
                                            'Ocorrência pendente',
                                        'recusado' =>
                                            'Recusado',
                                        'devolvido' =>
                                            'Devolvido',
                                        'avariado' =>
                                            'Avariado',
                                        'nao_entregue' =>
                                            'Não entregue',
                                        'cancelado' =>
                                            'Cancelado',
                                        'pendente_nesta_entrega' =>
                                            'Pendente nesta entrega',
                                        'entregue_nesta_entrega' =>
                                            'Entregue nesta entrega',
                                        'entregue_anteriormente' =>
                                            'Entregue anteriormente',
                                        'entregue_parcial_anteriormente' =>
                                            'Parcial em entrega anterior',
                                        'entregue_parcial' =>
                                            'Entregue parcialmente',
                                        'parcial_encaminhado' =>
                                            'Parcial / próxima entrega',
                                        'encaminhado_proxima' =>
                                            'Encaminhado para próxima',
                                        'finalizado_com_ocorrencia' =>
                                            'Finalizado com ocorrência',
                                        'finalizado_com_ocorrencia_anterior' =>
                                            'Finalizado com ocorrência anterior',
                                    ];
                                ?>

                                <?php $__empty_1 = true; $__currentLoopData = $itensBase; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemBase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $produtoNome =
                                            $itemBase?->produto?->nome
                                            ?? $itemBase?->produto_nome
                                            ?? $itemBase?->descricao
                                            ?? $itemBase?->nome_produto
                                            ?? 'Produto não identificado';

                                        $exibicaoItem =
                                            $resolverExibicaoItem(
                                                $itemBase
                                            );

                                        $quantidadeBase = (float) (
                                            $exibicaoItem[
                                                'quantidade_base'
                                            ]
                                        );

                                        $quantidadePrevistaAtual =
                                            (float) $exibicaoItem[
                                                'quantidade_prevista_atual'
                                            ];

                                        $quantidadeEntregue =
                                            (float) $exibicaoItem[
                                                'quantidade_entregue_acumulada'
                                            ];

                                        $quantidadeEntregueAnterior =
                                            (float) $exibicaoItem[
                                                'quantidade_entregue_anterior'
                                            ];

                                        $quantidadeEntregueAtual =
                                            (float) $exibicaoItem[
                                                'quantidade_entregue_atual'
                                            ];

                                        $quantidadeEncaminhadaProxima =
                                            (float) $exibicaoItem[
                                                'quantidade_encaminhada_proxima'
                                            ];

                                        $saldo =
                                            (float) $exibicaoItem[
                                                'saldo'
                                            ];

                                        $statusItem =
                                            (string) $exibicaoItem[
                                                'status'
                                            ];

                                        $statusItemLabel =
                                            $statusItemLabels[$statusItem]
                                            ?? ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $statusItem
                                                )
                                            );
                                    ?>

                                    <tr>
                                        <td class="text-center">
                                            <?php echo e($loop->iteration); ?>

                                        </td>

                                        <td class="fw-semibold">
                                            <?php echo e($produtoNome); ?>

                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($origemItens === 'Venda'
                                                ? 'bg-success'
                                                : 'bg-secondary'); ?>">
                                                <?php echo e($origemItens); ?>

                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format(
                                                $quantidadeBase,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format(
                                                $quantidadeBase,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>


                                            <?php if(
                                                $quantidadePrevistaAtual > 0
                                                && abs(
                                                    $quantidadePrevistaAtual
                                                    - $quantidadeBase
                                                ) > 0.001
                                            ): ?>
                                                <small class="d-block text-muted">
                                                    Nesta:
                                                    <?php echo e(number_format(
                                                        $quantidadePrevistaAtual,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>

                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format(
                                                $quantidadeEntregue,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>


                                            <?php if($quantidadeEntregueAnterior > 0): ?>
                                                <small class="d-block text-muted">
                                                    Anterior:
                                                    <?php echo e(number_format(
                                                        $quantidadeEntregueAnterior,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>


                                                    <?php if($quantidadeEntregueAtual > 0): ?>
                                                        · Atual:
                                                        <?php echo e(number_format(
                                                            $quantidadeEntregueAtual,
                                                            2,
                                                            ',',
                                                            '.'
                                                        )); ?>

                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="<?php echo e($saldo > 0
                                                ? 'text-danger fw-bold'
                                                : 'text-success fw-bold'); ?>">
                                                <?php echo e(number_format(
                                                    $saldo,
                                                    2,
                                                    ',',
                                                    '.'
                                                )); ?>

                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($statusItemClasses[$statusItem]
                                                ?? 'bg-secondary'); ?>">
                                                <?php echo e($statusItemLabel); ?>

                                            </span>

                                            <?php if($quantidadeEncaminhadaProxima > 0): ?>
                                                <small class="d-block text-muted mt-1">
                                                    Próxima:
                                                    <?php echo e(number_format(
                                                        $quantidadeEncaminhadaProxima,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>

                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8"
                                            class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum item encontrado nesta entrega.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="col-md-4">

            
            <!-- <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-calendar-check me-2"></i>
                        Resumo Operacional
                    </strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">
                            Data prevista
                        </small>

                        <div class="fw-semibold">
                            <?php echo e($dataPrevista
                                ? $dataPrevista->format('d/m/Y')
                                : '-'); ?>

                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Período</small>

                        <div class="fw-semibold">
                            <?php echo e($periodoEntrega
                                ? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $periodoEntrega
                                    )
                                )
                                : '-'); ?>

                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Observação</small>
                        <div><?php echo e($observacaoEntrega ?? '-'); ?></div>
                    </div>
                </div>
            </div> -->

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-diagram-3 me-2"></i>
                        Fluxo da Entrega
                    </strong>
                </div>

                <div class="card-body">

                    <?php if(! $romaneio): ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Esta entrega ainda não possui romaneio relacionado.
                        </div>
                    <?php endif; ?>

                    <div class="timeline-entrega">

                        <?php $__currentLoopData = $etapasFluxo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $classeEtapa = '';

                                if ($etapa['concluida']) {
                                    $classeEtapa = 'concluida';
                                } elseif ($etapa['atual']) {
                                    $classeEtapa = 'atual';
                                }
                            ?>

                            <div class="timeline-item <?php echo e($classeEtapa); ?>">
                                <div class="timeline-icon">
                                    <i class="bi <?php echo e($etapa['concluida']
                                        ? 'bi-check'
                                        : $etapa['icone']); ?>">
                                    </i>
                                </div>

                                <div class="<?php echo e($etapa['concluida']
                                    ? 'text-success'
                                    : (
                                        $etapa['atual']
                                            ? 'text-primary'
                                            : 'text-muted'
                                    )); ?>">

                                    <div class="timeline-titulo">
                                        <?php echo e($etapa['titulo']); ?>

                                    </div>

                                    <div class="timeline-data">
                                        <?php if($etapa['concluida']): ?>
                                            Concluída
                                        <?php elseif($etapa['atual']): ?>
                                            Etapa atual
                                        <?php else: ?>
                                            Aguardando
                                        <?php endif; ?>

                                        <?php if($etapa['data']): ?>
                                            ·
                                            <?php echo e($formatarDataHora(
                                                $etapa['data']
                                            )); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(
                            $statusEntrega === 'cancelada'
                            || $statusRomaneio === 'cancelado'
                        ): ?>
                            <div class="timeline-item cancelada">
                                <div class="timeline-icon">
                                    <i class="bi bi-x-lg"></i>
                                </div>

                                <div>
                                    <div class="timeline-titulo text-danger">
                                        <?php echo e($statusEntrega === 'cancelada'
                                            ? 'Entrega cancelada'
                                            : 'Romaneio cancelado'); ?>

                                    </div>

                                    <div class="timeline-data">
                                        <?php echo e($formatarDataHora(
                                            $romaneio?->cancelado_em
                                            ?? $entrega->updated_at
                                        ) ?? 'Data não registrada'); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="border-top pt-2 mt-2 small text-muted">
                        Status atual da entrega:

                        <strong>
                            <?php echo e($statusLabels[$statusEntrega]
                                ?? $entrega->status); ?>

                        </strong>

                        <br>

                        Status atual do romaneio:

                        <strong>
                            <?php echo e($romaneio?->status
                                ?? 'Não localizado'); ?>

                        </strong>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico
                    </strong>
                </div>

                <div class="card-body">

                    <?php if($historicoEntregasFracionadas->isNotEmpty()): ?>
                        <div class="mb-3">
                            <div class="small text-uppercase fw-semibold text-muted mb-2">
                                Entregas anteriores do fracionamento
                            </div>

                            <?php $__currentLoopData = $historicoEntregasFracionadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registroHistorico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $entregaHistorica =
                                        $registroHistorico['entrega'];

                                    $romaneioHistorico =
                                        $registroHistorico['romaneio'];

                                    $statusHistorico = strtolower(
                                        trim(
                                            str_replace(
                                                ' ',
                                                '_',
                                                (string) (
                                                    $entregaHistorica->status
                                                    ?? ''
                                                )
                                            )
                                        )
                                    );

                                    $dataHistorica =
                                        $entregaHistorica->data_realizada
                                        ?? $romaneioHistorico?->data_retorno
                                        ?? $romaneioHistorico?->data_emissao
                                        ?? $entregaHistorica->updated_at;

                                    $itensHistoricosMovimentados = collect(
                                        $registroHistorico['itens']
                                        ?? []
                                    )->filter(
                                        fn ($itemHistorico) =>
                                            (float) (
                                                $itemHistorico['entregue']
                                                ?? 0
                                            ) > 0
                                            || (float) (
                                                $itemHistorico['ocorrencia']
                                                ?? 0
                                            ) > 0
                                            || (float) (
                                                $itemHistorico['encaminhado']
                                                ?? 0
                                            ) > 0
                                    );
                                ?>

                                <div class="border-start border-3 ps-3 mb-3">
                                    <small class="text-muted">
                                        <?php echo e($formatarDataHora(
                                            $dataHistorica
                                        ) ?? '-'); ?>

                                    </small>

                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <strong>
                                            <?php echo e($entregaHistorica->codigo_entrega
                                                ?? 'Entrega #' . $entregaHistorica->id); ?>

                                        </strong>

                                        <span class="badge <?php echo e($statusClasses[$statusHistorico]
                                            ?? 'bg-secondary'); ?>">
                                            <?php echo e($statusLabels[$statusHistorico]
                                                ?? $entregaHistorica->status); ?>

                                        </span>
                                    </div>

                                    <?php if($romaneioHistorico): ?>
                                        <div class="small text-muted">
                                            Romaneio:
                                            <?php echo e($romaneioHistorico->codigo_romaneio
                                                ?? '#' . $romaneioHistorico->id); ?>

                                        </div>
                                    <?php endif; ?>

                                    <?php $__empty_1 = true; $__currentLoopData = $itensHistoricosMovimentados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemHistorico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="small mt-2">
                                            <span class="fw-semibold">
                                                <?php echo e($itemHistorico['produto']); ?>

                                            </span>

                                            <span class="d-block text-success">
                                                Entregue:
                                                <?php echo e(number_format(
                                                    (float) $itemHistorico['entregue'],
                                                    2,
                                                    ',',
                                                    '.'
                                                )); ?>

                                            </span>

                                            <?php if(
                                                (float) $itemHistorico[
                                                    'ocorrencia'
                                                ] > 0
                                            ): ?>
                                                <span class="d-block text-warning">
                                                    Ocorrência:
                                                    <?php echo e(number_format(
                                                        (float) $itemHistorico[
                                                            'ocorrencia'
                                                        ],
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>

                                                </span>
                                            <?php endif; ?>

                                            <?php if(
                                                (float) (
                                                    $itemHistorico[
                                                        'encaminhado'
                                                    ]
                                                    ?? 0
                                                ) > 0
                                            ): ?>
                                                <span class="d-block text-info">
                                                    Para a próxima:
                                                    <?php echo e(number_format(
                                                        (float) $itemHistorico[
                                                            'encaminhado'
                                                        ],
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="small text-muted mt-2">
                                            Nenhuma quantidade entregue neste documento.
                                        </div>
                                    <?php endif; ?>

                                    <?php if(
                                        (float) $registroHistorico[
                                            'quantidade_encaminhada'
                                        ] > 0
                                    ): ?>
                                        <div class="small text-info mt-2">
                                            Encaminhado à próxima entrega:
                                            <strong>
                                                <?php echo e(number_format(
                                                    (float) $registroHistorico[
                                                        'quantidade_encaminhada'
                                                    ],
                                                    2,
                                                    ',',
                                                    '.'
                                                )); ?>

                                            </strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <hr>

                        <div class="small text-uppercase fw-semibold text-muted mb-2">
                            Documento atual
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <small class="text-muted">
                            <?php echo e($entrega->created_at
                                ? $entrega->created_at->format('d/m/Y H:i')
                                : '-'); ?>

                        </small>

                        <div class="fw-semibold">
                            Entrega criada
                        </div>
                    </div>

                    <?php if($entrega->orcamento_id): ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                Orçamento #<?php echo e($entrega->orcamento_id); ?>

                            </small>

                            <div>
                                Entrega vinculada ao orçamento.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($entrega->venda_id): ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                Venda #<?php echo e($entrega->venda_id); ?>

                            </small>

                            <div>
                                Venda faturada e entrega liberada.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($romaneio): ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                <?php echo e($formatarDataHora(
                                    $romaneio->data_emissao
                                    ?? $romaneio->created_at
                                )); ?>

                            </small>

                            <div>
                                Romaneio
                                <strong>
                                    <?php echo e($romaneio->codigo_romaneio); ?>

                                </strong>
                                vinculado.
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php $__currentLoopData = collect($romaneio?->eventos ?? [])
                            ->sortBy(
                                fn ($evento) =>
                                    $evento->ocorrido_em
                                    ?? $evento->created_at
                            )
                            ->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-3">
                            <small class="text-muted">
                                <?php echo e($formatarDataHora(
                                    $evento->ocorrido_em
                                    ?? $evento->created_at
                                )); ?>

                            </small>

                            <div class="fw-semibold">
                                <?php echo e($evento->evento
                                    ?? 'Evento registrado'); ?>

                            </div>

                            <?php if($evento->etapa): ?>
                                <div class="small text-muted">
                                    Etapa: <?php echo e($evento->etapa); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div>
                        <small class="text-muted">
                            <?php echo e($entrega->updated_at
                                ? $entrega->updated_at->format('d/m/Y H:i')
                                : '-'); ?>

                        </small>

                        <div>
                            Status atual:
                            <?php echo e($statusLabels[$statusEntrega]
                                ?? $entrega->status); ?>

                        </div>
                    </div>

                </div>
            </div>

            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i>
                Esta tela é de acompanhamento. As ações operacionais permanecem nos painéis de Entregas e Romaneios.
            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas\show.blade.php ENDPATH**/ ?>