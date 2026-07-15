@extends('layouts.app')

@section('content')

@php
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

    $totalItens = collect($entrega->itens ?? [])->count();

    $itensEntregues = collect($entrega->itens ?? [])
        ->filter(function ($item) {
            return strtolower(
                trim((string) $item->status)
            ) === 'entregue';
        })
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
@endphp

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

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                Fluxo da Entrega
                #{{ $entrega->codigo_entrega ?? $entrega->id }}
            </h4>

            <small class="text-muted">
                Acompanhamento da entrega e da operação logística vinculada.
            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="{{ route('entregas.index') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

            <button type="button"
                    onclick="window.print()"
                    class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>
                Imprimir
            </button>

            <a href="{{ route(
                    'entregas.show',
                    $entrega->id
                ) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Atualizar
            </a>

            @if(
                in_array(
                    $statusEntrega,
                    [
                        'pendente_pagamento',
                        'aguardando_faturamento',
                        'aguardando_separacao',
                    ],
                    true
                )
            )
                <a href="{{ route(
                        'entregas.atribuir-equipe',
                        $entrega->id
                    ) }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-truck me-1"></i>
                    Equipe
                </a>
            @endif
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    @if(
        $statusEntrega === 'cancelada'
        || $statusRomaneio === 'cancelado'
    )
        <div class="alert alert-danger">
            <div class="fw-bold">
                <i class="bi bi-x-octagon me-1"></i>

                {{ $statusEntrega === 'cancelada'
                    ? 'Entrega cancelada'
                    : 'Romaneio cancelado' }}
            </div>

            @if($romaneio?->motivo_cancelamento)
                <div class="small mt-1">
                    {{ $romaneio->motivo_cancelamento }}
                </div>
            @endif
        </div>
    @endif

    {{-- CARDS --}}
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>STATUS</small>

                    <h5>
                        <span class="badge {{ $statusClasses[$statusEntrega]
                            ?? 'bg-secondary' }}">
                            {{ $statusLabels[$statusEntrega]
                                ?? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $statusEntrega
                                    )
                                ) }}
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
                        {{ $dataPrevista
                            ? $dataPrevista->format('d/m/Y')
                            : '-' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PERÍODO</small>

                    <h5>
                        {{ $periodoEntrega
                            ? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $periodoEntrega
                                )
                            )
                            : '-' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>ITENS</small>
                    <h5>{{ $itensEntregues }}/{{ $totalItens }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PROGRESSO</small>
                    <h5>{{ $percentualFluxo }}%</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>TIPO</small>

                    <h5>
                        @if($entrega->tipo_entrega === 'retira_loja')
                            <span class="badge bg-secondary">
                                Retira loja
                            </span>
                        @else
                            <span class="badge bg-info text-dark">
                                Entrega
                            </span>
                        @endif
                    </h5>
                </div>
            </div>
        </div>

    </div>

    {{-- PROGRESSO --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
                <strong>Andamento da entrega</strong>
                <span>{{ $percentualFluxo }}%</span>
            </div>

            <div class="progress" style="height: 14px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: {{ min(
                         $percentualFluxo,
                         100
                     ) }}%;">
                    {{ $percentualFluxo }}%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- COLUNA PRINCIPAL --}}
        <div class="col-md-8">

            {{-- DADOS DA ENTREGA --}}
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
                                {{ $entrega->codigo_entrega ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Venda</small>

                            <div class="fw-semibold">
                                {{ $entrega->venda_id ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Orçamento</small>

                            <div class="fw-semibold">
                                {{ $entrega->orcamento?->codigo_orcamento
                                    ?? $entrega->orcamento_id
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">
                                Data prevista
                            </small>

                            <div class="fw-semibold">
                                {{ $dataPrevista
                                    ? $dataPrevista->format('d/m/Y')
                                    : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Período da entrega
                            </small>

                            <div class="fw-semibold">
                                {{ $periodoEntrega
                                    ? ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $periodoEntrega
                                        )
                                    )
                                    : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Data realizada
                            </small>

                            <div>
                                {{ $dataRealizada
                                    ? $dataRealizada->format('d/m/Y')
                                    : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">
                                Responsável
                            </small>

                            <div class="fw-semibold">
                                {{ $entrega->responsavel_recebimento
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Telefone</small>

                            <div>
                                {{ $entrega->telefone_recebimento
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Motorista</small>

                            <div>
                                {{ $entrega->motorista?->name
                                    ?? $entrega->motorista?->nome
                                    ?? $romaneio?->motorista?->name
                                    ?? $romaneio?->motorista?->nome
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Veículo</small>

                            <div>
                                {{ $entrega->veiculo?->placa
                                    ?? $romaneio?->veiculo?->placa
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Frete</small>

                            <div>
                                @if($entrega->cobrar_frete)
                                    R$
                                    {{ number_format(
                                        $entrega->valor_frete ?? 0,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                @else
                                    Sem cobrança
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <small class="text-muted">
                                Observação da entrega
                            </small>

                            <div>
                                {{ $observacaoEntrega ?? '-' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- CLIENTE --}}
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
                                {{ $entrega->venda?->cliente?->nome
                                    ?? $entrega->orcamento?->cliente?->nome
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Telefone</small>

                            <div>
                                {{ $entrega->venda?->cliente?->telefone
                                    ?? $entrega->orcamento?->cliente?->telefone
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Documento</small>

                            <div>
                                {{ $entrega->venda?->cliente?->cpf_cnpj
                                    ?? $entrega->orcamento?->cliente?->cpf_cnpj
                                    ?? '-' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ENDEREÇO --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-geo-alt me-2"></i>
                        Endereço de Entrega
                    </strong>

                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}"
                           target="_blank"
                           class="btn btn-light btn-sm">
                            <i class="bi bi-map me-1"></i>
                            Abrir no Maps
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        {{ $entrega->endereco_entrega
                            ?? 'Endereço não informado' }}
                    </div>

                    <small class="text-muted">
                        Usar endereço do cliente:
                        {{ $entrega->usar_endereco_cliente
                            ? 'Sim'
                            : 'Não' }}
                    </small>
                </div>
            </div>

            {{-- ITENS --}}
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
                                    <th>Observação</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
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

                                    $statusItemClasses = [
                                        'pendente' => 'bg-secondary',
                                        'preparando' => 'bg-warning text-dark',
                                        'separado' => 'bg-primary',
                                        'carregado' => 'bg-info text-dark',
                                        'em_rota' => 'bg-dark',
                                        'entregue' => 'bg-success',
                                        'entregue_parcial' => 'bg-warning text-dark',
                                        'recusado' => 'bg-danger',
                                        'devolvido' => 'bg-danger',
                                        'avariado' => 'bg-danger',
                                        'nao_entregue' => 'bg-danger',
                                        'cancelado' => 'bg-danger',
                                    ];
                                @endphp

                                @forelse($itensBase as $itemBase)
                                    @php
                                        $entregaItem = null;

                                        if ($origemItens === 'Venda') {
                                            $entregaItem = $itensOperacionais
                                                ->first(function ($itemOperacional) use ($itemBase) {
                                                    return (int) $itemOperacional->venda_item_id
                                                        === (int) $itemBase->id;
                                                });
                                        }

                                        if (
                                            ! $entregaItem
                                            && $origemItens === 'Orçamento'
                                        ) {
                                            $entregaItem = $itensOperacionais
                                                ->first(function ($itemOperacional) use ($itemBase) {
                                                    return (int) $itemOperacional->item_orcamento_id
                                                        === (int) $itemBase->id;
                                                });
                                        }

                                        $produtoNome =
                                            $itemBase?->produto?->nome
                                            ?? $itemBase?->produto_nome
                                            ?? $itemBase?->descricao
                                            ?? $itemBase?->nome_produto
                                            ?? 'Produto não identificado';

                                        $quantidadeBase = (float) (
                                            $itemBase?->quantidade
                                            ?? $itemBase?->qtd
                                            ?? $itemBase?->quantidade_vendida
                                            ?? $itemBase?->quantidade_orcada
                                            ?? $itemBase?->quantidade_solicitada
                                            ?? 0
                                        );

                                        $quantidadePrevista = (float) (
                                            $entregaItem?->quantidade_prevista
                                            ?? $quantidadeBase
                                        );

                                        $quantidadeEntregue = (float) (
                                            $entregaItem?->quantidade_entregue
                                            ?? 0
                                        );

                                        $saldo = max(
                                            $quantidadePrevista
                                            - $quantidadeEntregue,
                                            0
                                        );

                                        $statusItem = strtolower(
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

                                        $statusItemLabel = ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $statusItem
                                            )
                                        );

                                        $observacaoItem =
                                            $entregaItem?->observacao
                                            ?? '-';
                                    @endphp

                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $produtoNome }}
                                        </td>

                                        <td class="text-center">
                                            <span class="badge {{ $origemItens === 'Venda'
                                                ? 'bg-success'
                                                : 'bg-secondary' }}">
                                                {{ $origemItens }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            {{ number_format(
                                                $quantidadeBase,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-center">
                                            {{ number_format(
                                                $quantidadePrevista,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-center">
                                            {{ number_format(
                                                $quantidadeEntregue,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-center">
                                            <span class="{{ $saldo > 0
                                                ? 'text-danger fw-bold'
                                                : 'text-success fw-bold' }}">
                                                {{ number_format(
                                                    $saldo,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge {{ $statusItemClasses[$statusItem]
                                                ?? 'bg-secondary' }}">
                                                {{ $statusItemLabel }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $observacaoItem }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum item encontrado nesta entrega.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUNA LATERAL --}}
        <div class="col-md-4">

            {{-- RESUMO --}}
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
                            Data prevista
                        </small>

                        <div class="fw-semibold">
                            {{ $dataPrevista
                                ? $dataPrevista->format('d/m/Y')
                                : '-' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Período</small>

                        <div class="fw-semibold">
                            {{ $periodoEntrega
                                ? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $periodoEntrega
                                    )
                                )
                                : '-' }}
                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Observação</small>
                        <div>{{ $observacaoEntrega ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- FLUXO --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-diagram-3 me-2"></i>
                        Fluxo da Entrega
                    </strong>
                </div>

                <div class="card-body">

                    @if(! $romaneio)
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Esta entrega ainda não possui romaneio relacionado.
                        </div>
                    @endif

                    <div class="timeline-entrega">

                        @foreach($etapasFluxo as $etapa)
                            @php
                                $classeEtapa = '';

                                if ($etapa['concluida']) {
                                    $classeEtapa = 'concluida';
                                } elseif ($etapa['atual']) {
                                    $classeEtapa = 'atual';
                                }
                            @endphp

                            <div class="timeline-item {{ $classeEtapa }}">
                                <div class="timeline-icon">
                                    <i class="bi {{ $etapa['concluida']
                                        ? 'bi-check'
                                        : $etapa['icone'] }}">
                                    </i>
                                </div>

                                <div class="{{ $etapa['concluida']
                                    ? 'text-success'
                                    : (
                                        $etapa['atual']
                                            ? 'text-primary'
                                            : 'text-muted'
                                    ) }}">

                                    <div class="timeline-titulo">
                                        {{ $etapa['titulo'] }}
                                    </div>

                                    <div class="timeline-data">
                                        @if($etapa['concluida'])
                                            Concluída
                                        @elseif($etapa['atual'])
                                            Etapa atual
                                        @else
                                            Aguardando
                                        @endif

                                        @if($etapa['data'])
                                            ·
                                            {{ $formatarDataHora(
                                                $etapa['data']
                                            ) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if(
                            $statusEntrega === 'cancelada'
                            || $statusRomaneio === 'cancelado'
                        )
                            <div class="timeline-item cancelada">
                                <div class="timeline-icon">
                                    <i class="bi bi-x-lg"></i>
                                </div>

                                <div>
                                    <div class="timeline-titulo text-danger">
                                        {{ $statusEntrega === 'cancelada'
                                            ? 'Entrega cancelada'
                                            : 'Romaneio cancelado' }}
                                    </div>

                                    <div class="timeline-data">
                                        {{ $formatarDataHora(
                                            $romaneio?->cancelado_em
                                            ?? $entrega->updated_at
                                        ) ?? 'Data não registrada' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="border-top pt-2 mt-2 small text-muted">
                        Status atual da entrega:

                        <strong>
                            {{ $statusLabels[$statusEntrega]
                                ?? $entrega->status }}
                        </strong>

                        <br>

                        Status atual do romaneio:

                        <strong>
                            {{ $romaneio?->status
                                ?? 'Não localizado' }}
                        </strong>
                    </div>
                </div>
            </div>

            {{-- HISTÓRICO --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico
                    </strong>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <small class="text-muted">
                            {{ $entrega->created_at
                                ? $entrega->created_at->format('d/m/Y H:i')
                                : '-' }}
                        </small>

                        <div class="fw-semibold">
                            Entrega criada
                        </div>
                    </div>

                    @if($entrega->orcamento_id)
                        <div class="mb-3">
                            <small class="text-muted">
                                Orçamento #{{ $entrega->orcamento_id }}
                            </small>

                            <div>
                                Entrega vinculada ao orçamento.
                            </div>
                        </div>
                    @endif

                    @if($entrega->venda_id)
                        <div class="mb-3">
                            <small class="text-muted">
                                Venda #{{ $entrega->venda_id }}
                            </small>

                            <div>
                                Venda faturada e entrega liberada.
                            </div>
                        </div>
                    @endif

                    @if($romaneio)
                        <div class="mb-3">
                            <small class="text-muted">
                                {{ $formatarDataHora(
                                    $romaneio->data_emissao
                                    ?? $romaneio->created_at
                                ) }}
                            </small>

                            <div>
                                Romaneio
                                <strong>
                                    {{ $romaneio->codigo_romaneio }}
                                </strong>
                                vinculado.
                            </div>
                        </div>
                    @endif

                    @foreach(
                        collect($romaneio?->eventos ?? [])
                            ->sortByDesc(
                                fn ($evento) =>
                                    $evento->ocorrido_em
                                    ?? $evento->created_at
                            )
                            ->take(10)
                        as $evento
                    )
                        <div class="mb-3">
                            <small class="text-muted">
                                {{ $formatarDataHora(
                                    $evento->ocorrido_em
                                    ?? $evento->created_at
                                ) }}
                            </small>

                            <div class="fw-semibold">
                                {{ $evento->evento
                                    ?? 'Evento registrado' }}
                            </div>

                            @if($evento->etapa)
                                <div class="small text-muted">
                                    Etapa: {{ $evento->etapa }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div>
                        <small class="text-muted">
                            {{ $entrega->updated_at
                                ? $entrega->updated_at->format('d/m/Y H:i')
                                : '-' }}
                        </small>

                        <div>
                            Status atual:
                            {{ $statusLabels[$statusEntrega]
                                ?? $entrega->status }}
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

@endsection