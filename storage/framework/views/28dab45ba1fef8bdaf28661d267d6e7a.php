

<?php $__env->startSection('content'); ?>

<?php
    $entrega = $romaneio->entrega;
    $orcamento = $entrega?->orcamento;
    $venda = $entrega?->venda;

    $cliente = $entrega?->cliente
        ?? $orcamento?->cliente
        ?? $venda?->cliente;

    $itens = collect($romaneio->itens ?? []);
    $ocorrencias = collect($romaneio->ocorrencias ?? []);
    $eventos = collect($romaneio->eventos ?? [])
        ->sortByDesc(function ($evento) {
            return $evento->ocorrido_em
                ?? $evento->created_at;
        })
        ->values();

    $codigoRomaneio = $romaneio->codigo_romaneio
        ?? 'ROM-' . $romaneio->id;

    $statusRomaneio = (string) (
        $romaneio->status
        ?? 'Montagem'
    );

    $statusNormalizado = strtolower(
        trim(
            str_replace(
                ' ',
                '_',
                $statusRomaneio
            )
        )
    );

    $statusClasses = [
        'montagem' => 'bg-secondary',
        'aguardando_separacao' => 'bg-warning text-dark',
        'em_separacao' => 'bg-warning text-dark',
        'aguardando_conferencia_separacao' => 'bg-info text-dark',
        'em_conferencia_separacao' => 'bg-info text-dark',
        'separacao_conferida' => 'bg-primary',
        'aguardando_carregamento' => 'bg-primary',
        'carregando' => 'bg-primary',
        'aguardando_conferencia_saida' => 'bg-info text-dark',
        'em_conferencia_saida' => 'bg-info text-dark',
        'aguardando_liberacao' => 'bg-success',
        'liberado' => 'bg-success',
        'em_rota' => 'bg-dark',
        'retornando' => 'bg-dark',
        'aguardando_conferencia_retorno' => 'bg-warning text-dark',
        'em_conferencia_retorno' => 'bg-warning text-dark',
        'aguardando_prestacao_contas' => 'bg-info text-dark',
        'em_prestacao_contas' => 'bg-info text-dark',
        'aguardando_fechamento' => 'bg-primary',
        'fechado' => 'bg-success',
        'cancelado' => 'bg-danger',
    ];

    $badgeStatus = $statusClasses[$statusNormalizado]
        ?? 'bg-secondary';

    $totalItens = $itens->count();

    $itensSeparados = $itens
        ->filter(function ($item) {
            return (float) $item->quantidade_separada > 0;
        })
        ->count();

    $itensConferidosSeparacao = $itens
        ->filter(function ($item) {
            return (float) $item->quantidade_conferida_separacao > 0;
        })
        ->count();

    $itensCarregados = $itens
        ->filter(function ($item) {
            return (float) $item->quantidade_carregada > 0;
        })
        ->count();

    $itensConferidosSaida = $itens
        ->filter(function ($item) {
            return (float) $item->quantidade_conferida_saida > 0;
        })
        ->count();

    $totalPrevisto = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_prevista
    );

    $totalSeparado = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_separada
    );

    $totalConferidoSeparacao = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_conferida_separacao
    );

    $totalCarregado = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_carregada
    );

    $totalConferidoSaida = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_conferida_saida
    );

    $totalEntregue = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_entregue
    );

    $totalDevolvido = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_devolvida
    );

    $totalRecusado = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_recusada
    );

    $totalAvariado = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_avariada
    );

    $totalPerdido = (float) $itens->sum(
        fn ($item) => (float) $item->quantidade_perdida
    );

    $percentualCarregado = $totalPrevisto > 0
        ? min(
            ($totalCarregado / $totalPrevisto) * 100,
            100
        )
        : 0;

    $etapasFluxo = [
        [
            'titulo' => 'Montagem',
            'icone' => 'bi-clipboard-check',
            'concluida' => ! empty($romaneio->id),
            'atual' => $statusNormalizado === 'montagem',
            'data' => $romaneio->data_emissao
                ?? $romaneio->created_at,
        ],
        [
            'titulo' => 'Separação',
            'icone' => 'bi-box-seam',
            'concluida' => ! empty(
                $romaneio->data_fim_separacao
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_separacao',
                    'em_separacao',
                ],
                true
            ),
            'data' => $romaneio->data_inicio_separacao,
        ],
        [
            'titulo' => 'Conferência da Separação',
            'icone' => 'bi-clipboard2-check',
            'concluida' => ! empty(
                $romaneio->data_fim_conferencia_separacao
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_conferencia_separacao',
                    'em_conferencia_separacao',
                    'separacao_conferida',
                ],
                true
            ),
            'data' => $romaneio->data_inicio_conferencia_separacao,
        ],
        [
            'titulo' => 'Carregamento',
            'icone' => 'bi-truck-front',
            'concluida' => ! empty(
                $romaneio->data_fim_carregamento
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_carregamento',
                    'carregando',
                ],
                true
            ),
            'data' => $romaneio->data_inicio_carregamento,
        ],
        [
            'titulo' => 'Conferência de Saída',
            'icone' => 'bi-clipboard-data',
            'concluida' => ! empty(
                $romaneio->data_fim_conferencia_saida
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_conferencia_saida',
                    'em_conferencia_saida',
                ],
                true
            ),
            'data' => $romaneio->data_inicio_conferencia_saida,
        ],
        [
            'titulo' => 'Liberação',
            'icone' => 'bi-shield-check',
            'concluida' => in_array(
                $statusNormalizado,
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
            'atual' => $statusNormalizado === 'aguardando_liberacao',
            'data' => $romaneio->impresso_em,
        ],
        [
            'titulo' => 'Em Rota',
            'icone' => 'bi-sign-turn-right',
            'concluida' => ! empty(
                $romaneio->data_saida
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'liberado',
                    'em_rota',
                    'retornando',
                ],
                true
            ),
            'data' => $romaneio->data_saida,
        ],
        [
            'titulo' => 'Retorno',
            'icone' => 'bi-arrow-return-left',
            'concluida' => ! empty(
                $romaneio->data_retorno
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_conferencia_retorno',
                    'em_conferencia_retorno',
                ],
                true
            ),
            'data' => $romaneio->data_retorno,
        ],
        [
            'titulo' => 'Prestação de Contas',
            'icone' => 'bi-journal-check',
            'concluida' => ! empty(
                $romaneio->data_fim_prestacao_contas
            ),
            'atual' => in_array(
                $statusNormalizado,
                [
                    'aguardando_prestacao_contas',
                    'em_prestacao_contas',
                ],
                true
            ),
            'data' => $romaneio->data_inicio_prestacao_contas,
        ],
        [
            'titulo' => 'Fechamento',
            'icone' => 'bi-lock',
            'concluida' => $statusNormalizado === 'fechado',
            'atual' => $statusNormalizado === 'aguardando_fechamento',
            'data' => $romaneio->fechado_em,
        ],
    ];

    $etapasConcluidas = collect($etapasFluxo)
        ->where('concluida', true)
        ->count();

    $percentualFluxo = count($etapasFluxo) > 0
        ? ($etapasConcluidas / count($etapasFluxo)) * 100
        : 0;

    $formatarDataHora = function ($data) {
        if (empty($data)) {
            return null;
        }

        return \Carbon\Carbon::parse($data)
            ->format('d/m/Y H:i');
    };

    $resolverNome = function ($pessoa) {
        return $pessoa?->name
            ?? $pessoa?->nome
            ?? null;
    };
?>

<style>
    .romaneio-show {
        --erp-border: #d8dde3;
        --erp-soft: #f5f6f8;
        --erp-dark: #343a40;
    }

    .kpi-card {
        border-radius: 8px;
        min-height: 105px;
    }

    .kpi-card .card-body {
        padding: 14px 16px;
    }

    .kpi-card h3 {
        font-size: 1.9rem;
        font-weight: 800;
        margin: 0;
    }

    .kpi-card i {
        font-size: 2.1rem;
        opacity: .6;
    }

    .info-label {
        color: #6c757d;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .info-value {
        font-size: .88rem;
        font-weight: 650;
    }

    .linha-secundaria {
        color: #6c757d;
        font-size: .72rem;
    }

    .table-romaneio th,
    .table-romaneio td {
        font-size: .76rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-romaneio thead th {
        background: #343a40;
        color: #fff;
        font-size: .66rem;
        text-align: center;
        text-transform: uppercase;
    }

    .timeline-operacional {
        position: relative;
    }

    .timeline-etapa {
        align-items: flex-start;
        display: flex;
        gap: .75rem;
        padding-bottom: 1rem;
        position: relative;
    }

    .timeline-etapa:not(:last-child)::before {
        background: #dee2e6;
        content: "";
        height: calc(100% - 18px);
        left: 17px;
        position: absolute;
        top: 34px;
        width: 2px;
    }

    .timeline-icone {
        align-items: center;
        background: #fff;
        border: 2px solid #ced4da;
        border-radius: 50%;
        color: #6c757d;
        display: flex;
        flex: 0 0 36px;
        height: 36px;
        justify-content: center;
        position: relative;
        width: 36px;
        z-index: 1;
    }

    .timeline-etapa.concluida .timeline-icone {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    .timeline-etapa.atual .timeline-icone {
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        color: #fff;
    }

    .timeline-etapa.cancelada .timeline-icone {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

    .timeline-titulo {
        font-size: .83rem;
        font-weight: 750;
    }

    .timeline-data {
        color: #6c757d;
        font-size: .7rem;
    }

    .evento-item {
        border-left: 3px solid #0d6efd;
        padding: .15rem 0 .8rem .75rem;
    }

    .ocorrencia-card {
        border-left: 4px solid #dc3545;
    }

    .section-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
    }

    @media (max-width: 1199.98px) {
        .table-romaneio th,
        .table-romaneio td {
            white-space: normal;
        }
    }
</style>

<div class="container-fluid px-2 romaneio-show">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-clipboard-check me-2"></i>
                Romaneio <?php echo e($codigoRomaneio); ?>

            </h4>

            <small class="text-muted">
                Acompanhamento completo da operação logística e da auditoria do romaneio.
            </small>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('romaneios.index')); ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

            <?php if($entrega): ?>
                <a href="<?php echo e(route(
                        'romaneios.create',
                        ['entrega_id' => $entrega->id]
                    )); ?>"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-diagram-3 me-1"></i>
                    Abrir Operação
                </a>
            <?php endif; ?>

            <a href="<?php echo e(route('romaneios.separacao', $romaneio)); ?>"
               target="_blank"
               class="btn btn-warning btn-sm">
                <i class="bi bi-box-seam me-1"></i>
                Folha de Separação
            </a>

            <?php if(in_array(
                $statusNormalizado,
                [
                    'aguardando_liberacao',
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
            )): ?>
                <a href="<?php echo e(route('romaneios.imprimir', $romaneio)); ?>"
                   target="_blank"
                   class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i>
                    Romaneio
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($statusNormalizado === 'cancelado'): ?>
        <div class="alert alert-danger">
            <div class="fw-bold">
                <i class="bi bi-x-octagon me-1"></i>
                Romaneio cancelado
            </div>

            <div class="small mt-1">
                <?php echo e($romaneio->motivo_cancelamento
                    ?? 'Motivo não informado.'); ?>

            </div>

            <?php if($romaneio->cancelado_em): ?>
                <div class="small mt-1">
                    Cancelado em:
                    <?php echo e($formatarDataHora($romaneio->cancelado_em)); ?>


                    <?php if($resolverNome($romaneio->cancelador)): ?>
                        por <?php echo e($resolverNome($romaneio->cancelador)); ?>

                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-2 mb-3">

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">
                            Status
                        </small>

                        <div class="mt-2">
                            <span class="badge <?php echo e($badgeStatus); ?> px-3 py-2">
                                <?php echo e(str_replace('_', ' ', $statusRomaneio)); ?>

                            </span>
                        </div>
                    </div>

                    <i class="bi bi-flag text-secondary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">
                            Itens
                        </small>

                        <h3><?php echo e($totalItens); ?></h3>

                        <div class="linha-secundaria">
                            <?php echo e(number_format($totalPrevisto, 2, ',', '.')); ?>

                            unidades previstas
                        </div>
                    </div>

                    <i class="bi bi-box-seam text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-warning border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">
                            Separados
                        </small>

                        <h3><?php echo e($itensSeparados); ?></h3>

                        <div class="linha-secundaria">
                            Conferidos: <?php echo e($itensConferidosSeparacao); ?>

                        </div>
                    </div>

                    <i class="bi bi-boxes text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">
                            Carregados
                        </small>

                        <h3><?php echo e($itensCarregados); ?></h3>

                        <div class="linha-secundaria">
                            Conf. saída: <?php echo e($itensConferidosSaida); ?>

                        </div>
                    </div>

                    <i class="bi bi-truck-front text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-danger border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">
                            Ocorrências
                        </small>

                        <h3><?php echo e($ocorrencias->count()); ?></h3>

                        <div class="linha-secundaria">
                            Registros operacionais
                        </div>
                    </div>

                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
                <strong>Progresso operacional</strong>

                <span>
                    <?php echo e(number_format($percentualFluxo, 0, ',', '.')); ?>%
                </span>
            </div>

            <div class="progress" style="height: 14px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: <?php echo e(min($percentualFluxo, 100)); ?>%;">
                    <?php echo e(number_format($percentualFluxo, 0, ',', '.')); ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-xl-8">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-info-circle me-2"></i>
                        Dados Operacionais
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="info-label">Romaneio</div>
                            <div class="info-value">
                                <?php echo e($codigoRomaneio); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Entrega</div>
                            <div class="info-value">
                                <?php echo e($entrega?->codigo_entrega
                                    ?? ($entrega
                                        ? 'ENT-' . $entrega->id
                                        : 'Não vinculada')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Emissão</div>
                            <div class="info-value">
                                <?php echo e($formatarDataHora(
                                    $romaneio->data_emissao
                                    ?? $romaneio->created_at
                                ) ?? 'Não registrada'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Motorista</div>
                            <div class="info-value">
                                <?php echo e($resolverNome($romaneio->motorista)
                                    ?? 'Não definido'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Veículo</div>
                            <div class="info-value">
                                <?php echo e($romaneio->veiculo?->placa
                                    ?? $romaneio->veiculo?->descricao
                                    ?? 'Não definido'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Criado por</div>
                            <div class="info-value">
                                <?php echo e($resolverNome($romaneio->criador)
                                    ?? 'Não registrado'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Início da separação
                            </div>

                            <div class="info-value">
                                <?php echo e($formatarDataHora(
                                    $romaneio->data_inicio_separacao
                                ) ?? 'Não iniciado'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Conferência da separação
                            </div>

                            <div class="info-value">
                                <?php echo e($resolverNome(
                                    $romaneio->usuarioFimConferenciaSeparacao
                                )
                                    ?? $resolverNome(
                                        $romaneio->usuarioInicioConferenciaSeparacao
                                    )
                                    ?? 'Não definida'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Responsável pelo carregamento
                            </div>

                            <div class="info-value">
                                <?php echo e($resolverNome($romaneio->carregador)
                                    ?? 'Não definido'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Conferência de saída
                            </div>

                            <div class="info-value">
                                <?php echo e($resolverNome(
                                    $romaneio->usuarioFimConferenciaSaida
                                )
                                    ?? $resolverNome(
                                        $romaneio->usuarioInicioConferenciaSaida
                                    )
                                    ?? 'Não definida'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Prestação de contas
                            </div>

                            <div class="info-value">
                                <?php echo e($resolverNome(
                                    $romaneio->usuarioPrestacaoContas
                                )
                                    ?? 'Não realizada'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">
                                Fechado por
                            </div>

                            <div class="info-value">
                                <?php echo e($resolverNome(
                                    $romaneio->usuarioFechamento
                                )
                                    ?? 'Não fechado'); ?>

                            </div>
                        </div>

                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-semibold text-muted">
                                Percentual carregado
                            </small>

                            <small class="fw-bold">
                                <?php echo e(number_format(
                                    $percentualCarregado,
                                    2,
                                    ',',
                                    '.'
                                )); ?>%
                            </small>
                        </div>

                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: <?php echo e(min(
                                     $percentualCarregado,
                                     100
                                 )); ?>%;">
                            </div>
                        </div>
                    </div>

                    <?php if($romaneio->observacao): ?>
                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>Observação:</strong>
                            <?php echo e($romaneio->observacao); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-person-vcard me-2"></i>
                        Cliente e Destino
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-lg-3 col-md-6">
                            <div class="info-label">Cliente</div>

                            <div class="info-value">
                                <?php echo e($cliente?->nome
                                    ?? $cliente?->razao_social
                                    ?? 'Cliente não informado'); ?>

                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="info-label">
                                Responsável
                            </div>

                            <div class="info-value">
                                <?php echo e($entrega?->responsavel_recebimento
                                    ?? 'Não informado'); ?>

                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="info-label">Telefone</div>

                            <div class="info-value">
                                <?php echo e($entrega?->telefone_recebimento
                                    ?? 'Não informado'); ?>

                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="info-label">Previsão</div>

                            <div class="info-value">
                                <?php if(
                                    $entrega?->data_prevista_entrega
                                    || $entrega?->data_prevista
                                ): ?>
                                    <?php echo e(\Carbon\Carbon::parse(
                                        $entrega->data_prevista_entrega
                                        ?? $entrega->data_prevista
                                    )->format('d/m/Y')); ?>

                                <?php else: ?>
                                    Não informada
                                <?php endif; ?>

                                <?php if($entrega?->periodo_entrega): ?>
                                    <span class="linha-secundaria d-block">
                                        <?php echo e(ucfirst(
                                            $entrega->periodo_entrega
                                        )); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-label">Endereço</div>

                            <div class="info-value">
                                <?php echo e($entrega?->endereco_entrega
                                    ?? 'Endereço não informado'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white section-header">
                    <strong>
                        <i class="bi bi-list-check me-2"></i>
                        Itens do Romaneio
                    </strong>

                    <div class="d-flex gap-1">
                        <span class="badge bg-light text-dark">
                            Itens: <?php echo e($totalItens); ?>

                        </span>

                        <span class="badge bg-success">
                            Carregados: <?php echo e($itensCarregados); ?>

                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-sm mb-0 table-romaneio">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Produto</th>
                                    <th>Local</th>
                                    <th>Prevista</th>
                                    <th>Separada</th>
                                    <th>Conf. Sep.</th>
                                    <th>Carregada</th>
                                    <th>Conf. Saída</th>
                                    <th>Entregue</th>
                                    <th>Devolvida</th>
                                    <th>Recusada</th>
                                    <th>Avariada</th>
                                    <th>Perdida</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $entregaItem = $item->entregaItem;

                                        $produto = $entregaItem?->produto
                                            ?? $entregaItem?->vendaItem?->produto
                                            ?? $entregaItem?->itemOrcamento?->produto;

                                        $statusItem = strtolower(
                                            trim(
                                                str_replace(
                                                    ' ',
                                                    '_',
                                                    (string) (
                                                        $item->status
                                                        ?? 'Pendente'
                                                    )
                                                )
                                            )
                                        );

                                        $statusItemClasses = [
                                            'pendente' => 'bg-secondary',
                                            'separando' => 'bg-warning text-dark',
                                            'separado' => 'bg-primary',
                                            'divergente_separacao' => 'bg-danger',
                                            'separacao_conferida' => 'bg-success',
                                            'carregando' => 'bg-info text-dark',
                                            'carregado' => 'bg-primary',
                                            'divergente_saida' => 'bg-danger',
                                            'saida_conferida' => 'bg-success',
                                            'entregue' => 'bg-success',
                                            'entregue_parcial' => 'bg-warning text-dark',
                                            'recusado' => 'bg-danger',
                                            'devolvido' => 'bg-secondary',
                                            'avariado' => 'bg-danger',
                                            'perdido' => 'bg-dark',
                                            'divergente_retorno' => 'bg-danger',
                                            'cancelado' => 'bg-danger',
                                        ];
                                    ?>

                                    <tr>
                                        <td class="text-center fw-semibold">
                                            <?php echo e($index + 1); ?>

                                        </td>

                                        <td>
                                            <div class="fw-semibold">
                                                <?php echo e($produto?->nome
                                                    ?? $produto?->descricao
                                                    ?? 'Produto não identificado'); ?>

                                            </div>

                                            <div class="linha-secundaria">
                                                Código:
                                                <?php echo e($produto?->codigo
                                                    ?? $produto?->id
                                                    ?? '—'); ?>

                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <?php echo e($produto?->localizacao_estoque
                                                ?? $produto?->localizacao
                                                ?? '—'); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_prevista,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_separada,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_conferida_separacao,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_carregada,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_conferida_saida,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_entregue,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_devolvida,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_recusada,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_avariada,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float) $item->quantidade_perdida,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($statusItemClasses[$statusItem]
                                                ?? 'bg-secondary'); ?>">
                                                <?php echo e(ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $statusItem
                                                    )
                                                )); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="14"
                                            class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum item encontrado para este romaneio.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                            <?php if($totalItens > 0): ?>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="3"
                                            class="text-end">
                                            Totais
                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalPrevisto, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalSeparado, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalConferidoSeparacao, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalCarregado, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalConferidoSaida, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalEntregue, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalDevolvido, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalRecusado, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalAvariado, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format($totalPerdido, 2, ',', '.')); ?>

                                        </td>

                                        <td></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-danger text-white section-header">
                    <strong>
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Ocorrências
                    </strong>

                    <span class="badge bg-light text-danger">
                        <?php echo e($ocorrencias->count()); ?>

                    </span>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $ocorrencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ocorrencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="card ocorrencia-card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-bold">
                                            <?php echo e($ocorrencia->titulo
                                                ?? $ocorrencia->tipo
                                                ?? $ocorrencia->categoria
                                                ?? 'Ocorrência operacional'); ?>

                                        </div>

                                        <div class="small text-muted">
                                            <?php echo e($ocorrencia->descricao
                                                ?? $ocorrencia->observacao
                                                ?? 'Sem descrição registrada.'); ?>

                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">
                                            <?php echo e($ocorrencia->status
                                                ?? 'Registrada'); ?>

                                        </span>

                                        <div class="linha-secundaria mt-1">
                                            <?php echo e($formatarDataHora(
                                                $ocorrencia->ocorrido_em
                                                ?? $ocorrencia->created_at
                                            )); ?>

                                        </div>
                                    </div>
                                </div>

                                <div class="linha-secundaria mt-2">
                                    Registrado por:
                                    <?php echo e($resolverNome(
                                        $ocorrencia->registrador
                                    ) ?? 'Não identificado'); ?>


                                    <?php if($ocorrencia->anexos?->count()): ?>
                                        ·
                                        <?php echo e($ocorrencia->anexos->count()); ?>

                                        anexo(s)
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            Nenhuma ocorrência registrada.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-xl-4">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-diagram-3 me-2"></i>
                        Fluxo do Romaneio
                    </strong>
                </div>

                <div class="card-body">
                    <div class="timeline-operacional">

                        <?php $__currentLoopData = $etapasFluxo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $classeEtapa = '';

                                if ($etapa['concluida']) {
                                    $classeEtapa = 'concluida';
                                } elseif ($etapa['atual']) {
                                    $classeEtapa = 'atual';
                                }
                            ?>

                            <div class="timeline-etapa <?php echo e($classeEtapa); ?>">
                                <div class="timeline-icone">
                                    <i class="bi <?php echo e($etapa['icone']); ?>"></i>
                                </div>

                                <div>
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

                        <?php if($statusNormalizado === 'cancelado'): ?>
                            <div class="timeline-etapa cancelada">
                                <div class="timeline-icone">
                                    <i class="bi bi-x-lg"></i>
                                </div>

                                <div>
                                    <div class="timeline-titulo text-danger">
                                        Romaneio Cancelado
                                    </div>

                                    <div class="timeline-data">
                                        <?php echo e($formatarDataHora(
                                            $romaneio->cancelado_em
                                        ) ?? 'Data não registrada'); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="border-top pt-2 mt-2 small text-muted">
                        Status atual:

                        <strong>
                            <?php echo e(str_replace(
                                '_',
                                ' ',
                                $statusRomaneio
                            )); ?>

                        </strong>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white">
                    <strong>
                        <i class="bi bi-files me-2"></i>
                        Documentos Vinculados
                    </strong>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Venda</div>

                        <div class="info-value">
                            <?php if($entrega?->venda_id): ?>
                                <a href="<?php echo e(url(
                                        '/venda/' .
                                        $entrega->venda_id .
                                        '/cupom'
                                    )); ?>"
                                   target="_blank"
                                   class="text-decoration-none">
                                    <i class="bi bi-receipt me-1"></i>
                                    VEN-<?php echo e($entrega->venda_id); ?>

                                </a>
                            <?php else: ?>
                                <span class="text-muted">
                                    Não vinculada
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="info-label">Orçamento</div>

                        <div class="info-value">
                            <?php if($orcamento && Route::has('orcamentos.show')): ?>
                                <a href="<?php echo e(route(
                                        'orcamentos.show',
                                        $orcamento->id
                                    )); ?>"
                                   class="text-decoration-none">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    ORÇ-<?php echo e($orcamento->id); ?>

                                </a>
                            <?php elseif($entrega?->orcamento_id): ?>
                                ORÇ-<?php echo e($entrega->orcamento_id); ?>

                            <?php else: ?>
                                <span class="text-muted">
                                    Não vinculado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="info-label">Entrega</div>

                        <div class="info-value">
                            <?php if($entrega): ?>
                                <a href="<?php echo e(route(
                                        'entregas.show',
                                        $entrega
                                    )); ?>"
                                   class="text-decoration-none">
                                    <i class="bi bi-truck me-1"></i>
                                    <?php echo e($entrega->codigo_entrega
                                        ?? 'ENT-' . $entrega->id); ?>

                                </a>
                            <?php else: ?>
                                <span class="text-muted">
                                    Não vinculada
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="info-label">
                            Identificação
                        </div>

                        <div class="linha-secundaria">
                            Token de abertura:
                            <?php echo e($romaneio->token_abertura
                                ? 'Gerado'
                                : 'Não gerado'); ?>

                        </div>

                        <div class="linha-secundaria">
                            Token de fechamento:
                            <?php echo e($romaneio->token_fechamento
                                ? 'Gerado'
                                : 'Não gerado'); ?>

                        </div>

                        <div class="linha-secundaria">
                            Método de fechamento:
                            <?php echo e($romaneio->metodo_fechamento
                                ? ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $romaneio->metodo_fechamento
                                    )
                                )
                                : 'Não fechado'); ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white section-header">
                    <strong>
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico Auditável
                    </strong>

                    <span class="badge bg-light text-dark">
                        <?php echo e($eventos->count()); ?>

                    </span>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="evento-item mb-2">
                            <div class="fw-semibold small">
                                <?php echo e($evento->evento
                                    ?? 'Evento registrado'); ?>

                            </div>

                            <div class="linha-secundaria">
                                <?php echo e($formatarDataHora(
                                    $evento->ocorrido_em
                                    ?? $evento->created_at
                                ) ?? 'Data não registrada'); ?>

                            </div>

                            <?php if($evento->etapa): ?>
                                <div class="linha-secundaria">
                                    Etapa: <?php echo e($evento->etapa); ?>

                                </div>
                            <?php endif; ?>

                            <?php if(
                                $evento->status_anterior
                                || $evento->status_novo
                            ): ?>
                                <div class="linha-secundaria">
                                    <?php echo e($evento->status_anterior
                                        ?? 'Inicial'); ?>


                                    <i class="bi bi-arrow-right mx-1"></i>

                                    <?php echo e($evento->status_novo
                                        ?? 'Não informado'); ?>

                                </div>
                            <?php endif; ?>

                            <div class="linha-secundaria">
                                Responsável:
                                <?php echo e($resolverNome($evento->funcionario)
                                    ?? $resolverNome($evento->usuario)
                                    ?? 'Sistema'); ?>

                            </div>

                            <?php if($evento->observacao): ?>
                                <div class="small mt-1">
                                    <?php echo e($evento->observacao); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-3">
                            Nenhum evento registrado.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/show.blade.php ENDPATH**/ ?>