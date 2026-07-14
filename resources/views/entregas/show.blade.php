@extends('layouts.app')

@section('content')

@php
    $statusAtual = strtolower($entrega->status ?? '');

    $statusLabels = [
        'pendente_pagamento'   => 'Pendente pagamento',
        'aguardando_separacao' => 'Aguardando separação',
        'separando'            => 'Separando',
        'carregado'            => 'Carregado',
        'em_rota'              => 'Em rota',
        'entregue'             => 'Entregue',
        'parcial'              => 'Parcial',
        'devolvido'            => 'Devolvido',
        'cancelado'            => 'Cancelado',
    ];

    $statusClasses = [
        'pendente_pagamento'   => 'bg-secondary',
        'aguardando_separacao' => 'bg-warning text-dark',
        'separando'            => 'bg-primary',
        'carregado'            => 'bg-info text-dark',
        'em_rota'              => 'bg-dark',
        'entregue'             => 'bg-success',
        'parcial'              => 'bg-warning text-dark',
        'devolvido'            => 'bg-danger',
        'cancelado'            => 'bg-danger',
    ];

    $progressoStatus = [
        'pendente_pagamento'   => 10,
        'aguardando_separacao' => 20,
        'separando'            => 40,
        'carregado'            => 60,
        'em_rota'              => 80,
        'parcial'              => 85,
        'entregue'             => 100,
        'devolvido'            => 100,
        'cancelado'            => 100,
    ];

    $percentual = $progressoStatus[$statusAtual] ?? 0;

    $dataPrevista = $entrega->data_prevista_entrega
        ? \Carbon\Carbon::parse($entrega->data_prevista_entrega)
        : ($entrega->data_prevista ? \Carbon\Carbon::parse($entrega->data_prevista) : null);

    $dataRealizada = $entrega->data_realizada
        ? \Carbon\Carbon::parse($entrega->data_realizada)
        : null;

    $periodoEntrega = $entrega->periodo_entrega ?? null;

    $observacaoEntrega = $entrega->observacao_entrega
        ?? $entrega->observacao
        ?? null;

    $totalItens = $entrega->itens ? $entrega->itens->count() : 0;

    $itensEntregues = $entrega->itens
        ? $entrega->itens->where('status', 'entregue')->count()
        : 0;

    $mapsUrl = $entrega->endereco_entrega
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($entrega->endereco_entrega)
        : null;

    $etapas = [
        'Entrega criada'       => ['pendente_pagamento', 'aguardando_separacao', 'separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Venda faturada'       => ['aguardando_separacao', 'separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Separação iniciada'   => ['separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Carga preparada'      => ['carregado', 'em_rota', 'parcial', 'entregue'],
        'Saiu para entrega'    => ['em_rota', 'parcial', 'entregue'],
        'Entrega concluída'    => ['entregue'],
    ];
@endphp

<style>
    .kpi-card {
        border-radius: 6px;
    }

    .kpi-card .card-body {
        padding: 10px 12px;
    }

    .kpi-card small {
        font-size: .72rem;
        color: #6c757d;
    }

    .kpi-card h5 {
        margin: 0;
        font-weight: 700;
    }

    .timeline-entrega {
        position: relative;
        padding-left: 28px;
    }

    .timeline-entrega::before {
        content: "";
        position: absolute;
        left: 9px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 18px;
    }

    .timeline-icon {
        position: absolute;
        left: -28px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
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
                Fluxo da Entrega #{{ $entrega->codigo_entrega ?? $entrega->id }}
            </h4>
            <small class="text-muted">
                Acompanhe todas as etapas da entrega, desde a geração até a conclusão.
            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>

            <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>Imprimir
            </button>

            <a href="{{ route('entregas.show', $entrega->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </a>
            @if(
                in_array($entrega->status, [
                    'Pendente_pagamento',
                    'Aguardando_faturamento',
                    'Aguardando_separacao'
                ])
            )

            <a href="{{ route('entregas.atribuir-equipe', $entrega->id) }}"
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
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- CARDS --}}
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>STATUS</small>
                    <h5>
                        <span class="badge {{ $statusClasses[$statusAtual] ?? 'bg-secondary' }}">
                            {{ $statusLabels[$statusAtual] ?? ucfirst(str_replace('_', ' ', $entrega->status)) }}
                        </span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PREVISÃO</small>
                    <h5>{{ $dataPrevista ? $dataPrevista->format('d/m/Y') : '-' }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PERÍODO</small>
                    <h5>{{ $periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-' }}</h5>
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
                    <h5>{{ $percentual }}%</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>TIPO</small>
                    <h5>
                        @if($entrega->tipo_entrega === 'retira_loja')
                            <span class="badge bg-secondary">Retira loja</span>
                        @else
                            <span class="badge bg-info text-dark">Entrega</span>
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
                <span>{{ $percentual }}%</span>
            </div>

            <div class="progress" style="height: 14px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: {{ $percentual }}%;">
                    {{ $percentual }}%
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
                    <strong><i class="bi bi-info-circle me-2"></i>Dados da Entrega</strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Código</small>
                            <div class="fw-semibold">{{ $entrega->codigo_entrega ?? '-' }}</div>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Venda</small>
                            <div class="fw-semibold">{{ $entrega->venda_id ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Orçamento</small>
                            <div class="fw-semibold">{{ $entrega->orcamento->codigo_orcamento ?? $entrega->orcamento_id }}</div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Data prevista</small>
                            <div class="fw-semibold">
                                {{ $dataPrevista ? $dataPrevista->format('d/m/Y') : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Período da entrega</small>
                            <div class="fw-semibold">
                                {{ $periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Data realizada</small>
                            <div>
                                {{ $dataRealizada ? $dataRealizada->format('d/m/Y') : '-' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Responsável</small>
                            <div class="fw-semibold">{{ $entrega->responsavel_recebimento ?? '-' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Telefone</small>
                            <div>{{ $entrega->telefone_recebimento ?? '-' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Motorista</small>
                            <div>{{ $entrega->motorista->name ?? $entrega->motorista->nome ?? '-' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Veículo</small>
                            <div>{{ $entrega->veiculo->placa ?? '-' }}</div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Frete</small>
                            <div>
                                @if($entrega->cobrar_frete)
                                    R$ {{ number_format($entrega->valor_frete ?? 0, 2, ',', '.') }}
                                @else
                                    Sem cobrança
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <small class="text-muted">Observação da entrega</small>
                            <div>{{ $observacaoEntrega ?? '-' }}</div>
                        </div>

                    </div>
                </div>
            </div>
            {{-- CLIENTE --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-person-vcard me-2"></i>Dados do Cliente</strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                {{ $entrega->venda->cliente->nome 
                                    ?? $entrega->orcamento->cliente->nome 
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Telefone</small>
                            <div>
                                {{ $entrega->venda->cliente->telefone 
                                    ?? $entrega->orcamento->cliente->telefone 
                                    ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Documento</small>
                            <div>
                                {{ $entrega->venda->cliente->cpf_cnpj 
                                    ?? $entrega->orcamento->cliente->cpf_cnpj 
                                    ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ENDEREÇO --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-geo-alt me-2"></i>Endereço de Entrega</strong>

                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}" target="_blank" class="btn btn-light btn-sm">
                            <i class="bi bi-map me-1"></i>Abrir no Maps
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        {{ $entrega->endereco_entrega ?? 'Endereço não informado' }}
                    </div>

                    <small class="text-muted">
                        Usar endereço do cliente:
                        {{ $entrega->usar_endereco_cliente ? 'Sim' : 'Não' }}
                    </small>
                </div>
            </div>

            {{-- ITENS --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-box-seam me-2"></i>Itens da Entrega</strong>
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
                                        $entrega->venda_id &&
                                        $entrega->venda &&
                                        $entrega->venda->itens
                                    ) {
                                        $itensBase = collect($entrega->venda->itens);
                                        $origemItens = 'Venda';
                                    } elseif (
                                        $entrega->orcamento_id &&
                                        $entrega->orcamento &&
                                        $entrega->orcamento->itens
                                    ) {
                                        $itensBase = collect($entrega->orcamento->itens);
                                        $origemItens = 'Orçamento';
                                    }

                                    $itensOperacionais = collect($entrega->itens ?? []);

                                    $statusItemClasses = [
                                        'pendente'   => 'bg-secondary',
                                        'separando'  => 'bg-warning text-dark',
                                        'separado'   => 'bg-primary',
                                        'carregando' => 'bg-info text-dark',
                                        'carregado'  => 'bg-info text-dark',
                                        'conferido'  => 'bg-success',
                                        'divergente' => 'bg-warning text-dark',
                                        'entregue'   => 'bg-success',
                                        'parcial'    => 'bg-warning text-dark',
                                        'devolvido'  => 'bg-danger',
                                        'cancelado'  => 'bg-danger',
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
                                            ! $entregaItem &&
                                            $origemItens === 'Orçamento'
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
                                            $quantidadePrevista - $quantidadeEntregue,
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
                                            str_replace('_', ' ', $statusItem)
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

                                            Nenhum item encontrado na venda ou no orçamento desta entrega.

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

            {{-- RESUMO OPERACIONAL --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-calendar-check me-2"></i>Resumo Operacional</strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Data prevista</small>
                        <div class="fw-semibold">
                            {{ $dataPrevista ? $dataPrevista->format('d/m/Y') : '-' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Período</small>
                        <div class="fw-semibold">
                            {{ $periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-' }}
                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Observação</small>
                        <div>{{ $observacaoEntrega ?? '-' }}</div>
                    </div>
                </div>
            </div>

           {{-- FLUXO --}}
            @php
                $romaneioEntrega = $entrega->romaneio;

                $statusRomaneio = strtolower(
                    trim(
                        str_replace(
                            ' ',
                            '_',
                            (string) ($romaneioEntrega?->status ?? '')
                        )
                    )
                );

                $ordemStatusRomaneio = [
                    'montagem'                => 0,
                    'rascunho'                => 0,
                    'pendente'                => 0,
                    'gerado'                  => 1,
                    'aguardando_separacao'    => 1,
                    'em_separacao'            => 2,
                    'separando'               => 2,
                    'separado'                => 3,
                    'na_doca'                 => 4,
                    'aguardando_carregamento' => 4,
                    'carregando'              => 5,
                    'carregado'               => 6,
                    'aguardando_conferencia'  => 6,
                    'conferindo'              => 7,
                    'conferido'               => 7,
                    'aguardando_liberacao'    => 8,
                    'liberado'                => 8,
                    'saiu_para_entrega'       => 9,
                    'em_rota'                 => 9,
                    'entregue'                => 10,
                    'parcial'                 => 10,
                    'devolvido'               => 10,
                    'cancelado'               => 10,
                ];

                $ordemAtualRomaneio =
                    $ordemStatusRomaneio[$statusRomaneio] ?? 0;

                $etapasFluxoEntrega = [
                    [
                        'titulo' => 'Entrega criada',
                        'concluida' => ! empty($entrega->id),
                    ],
                    [
                        'titulo' => 'Venda faturada',
                        'concluida' => ! empty($entrega->venda_id),
                    ],
                    [
                        'titulo' => 'Separação iniciada',
                        'concluida' => $ordemAtualRomaneio >= 2,
                    ],
                    [
                        'titulo' => 'Carga preparada',
                        'concluida' => $ordemAtualRomaneio >= 6,
                    ],
                    [
                        'titulo' => 'Saiu para entrega',
                        'concluida' => $ordemAtualRomaneio >= 9,
                    ],
                    [
                        'titulo' => 'Entrega concluída',
                        'concluida' => in_array(
                            $statusRomaneio,
                            [
                                'entregue',
                                'parcial',
                                'devolvido',
                            ],
                            true
                        ),
                    ],
                ];
            @endphp

            <div class="card shadow-sm mb-3">

                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-diagram-3 me-2"></i>
                        Fluxo da Entrega
                    </strong>
                </div>

                <div class="card-body">

                    @if(! $romaneioEntrega)
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Esta entrega ainda não possui romaneio relacionado.
                        </div>
                    @endif

                    <div class="timeline-entrega">

                        @foreach($etapasFluxoEntrega as $etapaFluxo)

                            @php
                                $concluida = (bool) $etapaFluxo['concluida'];

                                $classeIcone = $concluida
                                    ? 'bg-success text-white'
                                    : 'bg-light border text-muted';

                                $icone = $concluida
                                    ? 'bi-check'
                                    : 'bi-circle';
                            @endphp

                            <div class="timeline-item">

                                <div class="timeline-icon {{ $classeIcone }}">
                                    <i class="bi {{ $icone }}"></i>
                                </div>

                                <div class="{{ $concluida
                                    ? 'fw-semibold text-success'
                                    : 'text-muted' }}">

                                    {{ $etapaFluxo['titulo'] }}

                                </div>

                            </div>

                        @endforeach

                    </div>

                    <div class="border-top pt-2 mt-2 small text-muted">

                        Status atual do romaneio:

                        <strong>
                            {{ $romaneioEntrega?->status ?? 'Não localizado' }}
                        </strong>

                    </div>

                </div>

            </div>

            {{-- HISTÓRICO SIMPLES --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-clock-history me-2"></i>Histórico</strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">
                            {{ $entrega->created_at ? $entrega->created_at->format('d/m/Y H:i') : '-' }}
                        </small>
                        <div class="fw-semibold">Entrega criada</div>
                    </div>

                    @if($entrega->orcamento_id)
                        <div class="mb-2">
                            <small class="text-muted">
                                Orçamento #{{ $entrega->orcamento_id }}
                            </small>
                            <div>Entrega vinculada ao orçamento.</div>
                        </div>
                    @endif

                    @if($entrega->venda_id)
                        <div class="mb-2">
                            <small class="text-muted">
                                Venda #{{ $entrega->venda_id }}
                            </small>
                            <div>Entrega vinculada à venda.</div>
                        </div>
                    @endif

                    <div>
                        <small class="text-muted">
                            {{ $entrega->updated_at ? $entrega->updated_at->format('d/m/Y H:i') : '-' }}
                        </small>
                        <div>Status atual: {{ $statusLabels[$statusAtual] ?? $entrega->status }}</div>
                    </div>
                </div>
            </div>

            {{-- OBSERVAÇÃO DE ARQUITETURA --}}
            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i>
                Esta tela é apenas de acompanhamento. As ações operacionais ficam no painel de entregas.
            </div>

        </div>

    </div>

</div>

@endsection