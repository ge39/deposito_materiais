

<?php $__env->startSection('content'); ?>

<style>
    .page-shell {
        width: 100%;
    }

    .kpi-card {
        transition: .2s;
        cursor: pointer;
        border-radius: 8px;
        min-height: 108px;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .75rem rgba(0, 0, 0, .16) !important;
    }

    .kpi-card .card-body {
        padding: 14px 16px;
    }

    .kpi-card h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .kpi-card small {
        font-size: .72rem;
        margin-bottom: 4px;
        letter-spacing: .03rem;
        text-transform: uppercase;
    }

    .kpi-card .fw-semibold {
        font-size: .92rem;
    }

    .kpi-card i {
        font-size: 2.15rem !important;
        opacity: .6;
    }

    .mini-indicador {
        font-size: .75rem;
    }

    .table-entregas th,
    .table-entregas td {
        vertical-align: middle;
    }

    .table-entregas th {
        white-space: nowrap;
    }

    .entrega-codigo {
        font-size: .95rem;
        font-weight: 700;
    }

    .linha-secundaria {
        font-size: .75rem;
        color: #6c757d;
    }

    .acao-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .col-texto {
        max-width: 220px;
        white-space: normal;
    }

    .documentos a {
        display: block;
        font-size: .78rem;
        text-decoration: none;
        font-weight: 600;
    }

    .documentos span {
        display: block;
        font-size: .78rem;
    }

    .card-header {
        padding-top: .55rem;
        padding-bottom: .55rem;
    }

    .card-body {
        padding: .85rem;
    }

    .itens-toggle {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .itens-toggle .bi-chevron-down {
        transition: transform .2s ease;
    }

    .itens-toggle[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    .linha-itens td {
        padding: 0 !important;
        background: #dceeff;
        border-top: 3px solid #5aa7ff !important;
        border-bottom: 3px solid #5aa7ff !important;
    }

    .painel-itens {
        margin: 12px;
        border: 2px solid #5aa7ff;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .10);
    }

    .painel-itens-header {
        background: linear-gradient(180deg, #6fb7ff, #4ea5ff);
        color: #fff;
        padding: 9px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
    }

    .painel-itens-body {
        padding: 10px;
        background: #fff;
    }

    .item-table {
        margin-bottom: 0;
    }

    .item-table th,
    .item-table td {
        font-size: .82rem;
        vertical-align: middle;
    }

    .item-table thead th {
        background: #e9ecef;
        text-align: center;
        white-space: nowrap;
    }

    @media (max-width: 1400px) {
        .kpi-card h3 {
            font-size: 1.65rem;
        }

        .kpi-card i {
            font-size: 1.8rem !important;
        }

        .col-texto {
            max-width: 180px;
        }
    }
</style>

<div class="container-fluid px-2 page-shell">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-truck me-2"></i>Gerenciamento de Entregas
            </h4>

            <small class="text-muted">
                Controle operacional e acompanhamento das entregas, prazos,
                destinos, prioridades e situação junto ao cliente.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('romaneios.create')); ?>"
               class="btn btn-primary btn-sm">
                <i class="bi bi-box-seam me-1"></i>Criar Romaneio
            </a>

            <a href="<?php echo e(route('entregas.index')); ?>"
               class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </a>

            <button type="button"
                    class="btn btn-outline-secondary btn-sm"
                    disabled>
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Exportar
            </button>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo e(session('success')); ?>


            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo e(session('error')); ?>


            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo e($errors->first()); ?>


            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-3">

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'pendente_pagamento'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Financeiro</small>
                            <span class="fw-semibold d-block mb-1">Pend. Pagto</span>
                            <h3><?php echo e($resumo['pendente_pagamento'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-cash text-danger"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'aguardando_separacao'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-warning border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Prioridade</small>
                            <span class="fw-semibold d-block mb-1">Aguard. Sep.</span>
                            <h3><?php echo e($resumo['aguardando_separacao'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-lightning-charge text-warning"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'separando'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Operação</small>
                            <span class="fw-semibold d-block mb-1">Separando</span>
                            <h3><?php echo e($resumo['separando'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-box-seam text-primary"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'carregado'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Expedição</small>
                            <span class="fw-semibold d-block mb-1">Carregados</span>
                            <h3><?php echo e($resumo['carregados'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-truck-front text-info"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'em_rota'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-dark border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Logística</small>
                            <span class="fw-semibold d-block mb-1">Em rota</span>
                            <h3><?php echo e($resumo['em_rota'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-geo-alt text-dark"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="<?php echo e(route('entregas.index', ['status' => 'entregue'])); ?>"
               class="text-decoration-none text-dark">

                <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Finalizadas</small>
                            <span class="fw-semibold d-block mb-1">Entregues</span>
                            <h3><?php echo e($resumo['entregues'] ?? 0); ?></h3>
                        </div>

                        <i class="bi bi-check2-circle text-success"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white">
            <strong>
                <i class="bi bi-funnel me-2"></i>Filtros de Consulta
            </strong>
        </div>

        <div class="card-body">
            <form method="GET"
                  action="<?php echo e(route('entregas.index')); ?>"
                  class="row g-2 align-items-end">

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">
                        Código da Entrega
                    </label>

                    <input type="text"
                           name="codigo_entrega"
                           class="form-control form-control-sm"
                           value="<?php echo e(request('codigo_entrega')); ?>"
                           placeholder="Ex: ENT-20260629">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">
                        Status
                    </label>

                    <select name="status"
                            class="form-select form-select-sm">

                        <option value="">Todos</option>

                        <option value="pendente_pagamento"
                            <?php if(request('status') === 'pendente_pagamento'): echo 'selected'; endif; ?>>
                            Pendente Pagamento
                        </option>

                        <option value="aguardando_separacao"
                            <?php if(request('status') === 'aguardando_separacao'): echo 'selected'; endif; ?>>
                            Aguardando Separação
                        </option>

                        <option value="separando"
                            <?php if(request('status') === 'separando'): echo 'selected'; endif; ?>>
                            Separando
                        </option>

                        <option value="carregado"
                            <?php if(request('status') === 'carregado'): echo 'selected'; endif; ?>>
                            Carregado
                        </option>

                        <option value="em_rota"
                            <?php if(request('status') === 'em_rota'): echo 'selected'; endif; ?>>
                            Em rota
                        </option>

                        <option value="entregue"
                            <?php if(request('status') === 'entregue'): echo 'selected'; endif; ?>>
                            Entregue
                        </option>

                        <option value="parcial"
                            <?php if(request('status') === 'parcial'): echo 'selected'; endif; ?>>
                            Parcial
                        </option>

                        <option value="devolvido"
                            <?php if(request('status') === 'devolvido'): echo 'selected'; endif; ?>>
                            Devolvido
                        </option>

                        <option value="cancelado"
                            <?php if(request('status') === 'cancelado'): echo 'selected'; endif; ?>>
                            Cancelado
                        </option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">
                        Data Prevista
                    </label>

                    <input type="date"
                           name="data_prevista"
                           class="form-control form-control-sm"
                           value="<?php echo e(request('data_prevista')); ?>">
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn btn-primary btn-sm">

                            <i class="bi bi-search me-1"></i>Buscar
                        </button>

                        <a href="<?php echo e(route('entregas.index')); ?>"
                           class="btn btn-outline-secondary btn-sm">

                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <strong>
                <i class="bi bi-list-check me-2"></i>Operações de Entrega
            </strong>

            <div class="d-flex gap-1 flex-wrap">
                <span class="badge bg-light text-dark mini-indicador">
                    Total: <?php echo e($entregas->total()); ?>

                </span>

                <span class="badge bg-warning text-dark mini-indicador">
                    Prioridade: <?php echo e($resumo['aguardando_separacao'] ?? 0); ?>

                </span>

                <span class="badge bg-danger mini-indicador">
                    Atrasadas: <?php echo e($resumo['atrasadas'] ?? 0); ?>

                </span>

                <span class="badge bg-success mini-indicador">
                    Entregues: <?php echo e($resumo['entregues'] ?? 0); ?>

                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table id="tabela-entregas"
                       class="table table-hover table-bordered table-sm align-middle mb-0 table-entregas">

                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th style="width: 20%;">Entrega</th>
                            <th style="width: 18%;">Cliente / Contato</th>
                            <th style="width: 12%;">Documentos</th>
                            <th style="width: 12%;">Previsão</th>
                            <th style="width: 9%;">Tipo</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 5%;">Itens</th>
                            <th style="width: 14%;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $entregas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $statusClasses = [
                                    'pendente_pagamento'   => 'bg-secondary',
                                    'aguardando_separacao' => 'bg-secondary',
                                    'separando'            => 'bg-primary',
                                    'carregado'            => 'bg-info text-dark',
                                    'em_rota'              => 'bg-dark',
                                    'entregue'             => 'bg-success',
                                    'parcial'              => 'bg-warning text-dark',
                                    'devolvido'            => 'bg-danger',
                                    'cancelado'            => 'bg-danger',
                                ];

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

                                $dataPrevista = $entrega->data_prevista
                                    ? \Carbon\Carbon::parse($entrega->data_prevista)->startOfDay()
                                    : null;

                                $hoje = now()->startOfDay();

                                $statusFinalizado = in_array(
                                    $entrega->status,
                                    ['entregue', 'cancelado', 'devolvido'],
                                    true
                                );

                                $atrasada = $dataPrevista
                                    && $dataPrevista->lt($hoje)
                                    && !$statusFinalizado;

                                $venceHoje = $dataPrevista
                                    && $dataPrevista->equalTo($hoje)
                                    && !$statusFinalizado;

                                $venceAmanha = $dataPrevista
                                    && $dataPrevista->equalTo($hoje->copy()->addDay())
                                    && !$statusFinalizado;

                                $itensEntrega = $entrega->itens ?? collect();
                                $totalItens = $itensEntrega->count();

                                $itensEntregues = $itensEntrega
                                    ->filter(function ($item) {
                                        return strtolower($item->status ?? '') === 'entregue';
                                    })
                                    ->count();

                                $linhaClasse = match ($entrega->status) {
                                    'pendente_pagamento'   => 'table-light',
                                    'aguardando_separacao' => '',
                                    'separando'            => 'table-primary',
                                    'carregado'            => 'table-info',
                                    'em_rota'              => 'table-dark',
                                    'entregue'             => 'table-success',
                                    'parcial'              => 'table-warning',
                                    'devolvido',
                                    'cancelado'            => 'table-danger',
                                    default                => '',
                                };

                                if ($atrasada) {
                                    $linhaClasse = 'table-danger';
                                }

                                $prioridadeLabel = 'NORMAL';
                                $prioridadeClasse = 'bg-success';
                                $prioridadeIcone = 'bi-calendar-check';

                                if ($atrasada) {
                                    $prioridadeLabel = 'ATRASADA';
                                    $prioridadeClasse = 'bg-danger';
                                    $prioridadeIcone = 'bi-alarm';
                                } elseif ($entrega->status === 'pendente_pagamento') {
                                    $prioridadeLabel = 'BAIXA';
                                    $prioridadeClasse = 'bg-secondary';
                                    $prioridadeIcone = 'bi-clock-history';
                                } elseif ($venceHoje) {
                                    $prioridadeLabel = 'HOJE';
                                    $prioridadeClasse = 'bg-warning text-dark';
                                    $prioridadeIcone = 'bi-calendar-event';
                                } elseif ($venceAmanha) {
                                    $prioridadeLabel = 'AMANHÃ';
                                    $prioridadeClasse = 'bg-info text-dark';
                                    $prioridadeIcone = 'bi-calendar2-day';
                                }
                            ?>

                            <tr class="<?php echo e($linhaClasse); ?>">
                                <td>
                                    <div class="entrega-codigo">
                                        <?php echo e($entrega->codigo_entrega ?? 'ENT-' . $entrega->id); ?>

                                    </div>

                                    <div class="linha-secundaria">
                                        ID interno: #<?php echo e($entrega->id); ?>

                                    </div>

                                    <div class="mt-1">
                                        <span class="badge <?php echo e($prioridadeClasse); ?>">
                                            <i class="bi <?php echo e($prioridadeIcone); ?> me-1"></i>
                                            <?php echo e($prioridadeLabel); ?>

                                        </span>
                                    </div>
                                </td>

                                <td class="col-texto">
                                    <div class="fw-semibold">
                                        <?php echo e($entrega->responsavel_recebimento ?? 'Responsável não informado'); ?>

                                    </div>

                                    <div class="linha-secundaria">
                                        <i class="bi bi-telephone me-1"></i>
                                        <?php echo e($entrega->telefone_recebimento ?? 'Telefone não informado'); ?>

                                    </div>
                                </td>

                                <td class="documentos text-center">
                                    <?php if(!empty($entrega->venda_id)): ?>
                                        <a href="<?php echo e(url('/venda/' . $entrega->venda_id . '/cupom')); ?>"
                                           target="_self"
                                           rel="noopener noreferrer"
                                           class="text-decoration-none fw-semibold">

                                            <i class="bi bi-receipt me-1"></i>
                                            VEN-<?php echo e($entrega->venda_id); ?>

                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-receipt me-1"></i>
                                            Venda —
                                        </span>
                                    <?php endif; ?>

                                    <?php if(!empty($entrega->orcamento_id)): ?>
                                        <?php if(Route::has('orcamentos.show')): ?>
                                            <a href="<?php echo e(route('orcamentos.show', $entrega->orcamento_id)); ?>">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                ORÇ-<?php echo e($entrega->orcamento_id); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                ORÇ-<?php echo e($entrega->orcamento_id); ?>

                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            Orç. —
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <div class="fw-semibold">
                                        <?php echo e($dataPrevista ? $dataPrevista->format('d/m/Y') : '—'); ?>

                                    </div>

                                    <div class="linha-secundaria">
                                        <?php echo e($entrega->periodo_entrega ?? 'Período não informado'); ?>

                                    </div>
                                </td>

                                <td class="text-center">
                                    <?php if($entrega->tipo_entrega === 'retira_loja'): ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-shop me-1"></i>Retira
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-truck me-1"></i>Entrega
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge <?php echo e($statusClasses[$entrega->status] ?? 'bg-secondary'); ?>">
                                        <?php echo e($statusLabels[$entrega->status]
                                            ?? ucfirst(str_replace('_', ' ', $entrega->status))); ?>

                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <span class="badge bg-light text-dark border">
                                            <?php echo e($itensEntregues); ?>/<?php echo e($totalItens); ?>

                                        </span>

                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm itens-toggle"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#itens-entrega-<?php echo e($entrega->id); ?>"
                                                aria-expanded="false"
                                                aria-controls="itens-entrega-<?php echo e($entrega->id); ?>"
                                                title="Exibir itens da entrega">

                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <?php
                                        $statusEntrega = strtolower(trim((string) $entrega->status));

                                        $acaoOperacional = match ($statusEntrega) {
                                            'aguardando_separacao' => [
                                                'titulo' => 'Montar romaneio para esta entrega',
                                                'icone' => 'bi-clipboard-plus',
                                                'classe' => 'btn-outline-secondary',
                                            ],

                                            'separando' => [
                                                'titulo' => 'Continuar separação',
                                                'icone' => 'bi-box-seam',
                                                'classe' => 'btn-outline-warning',
                                            ],

                                            'aguardando_carregamento',
                                            'carregando' => [
                                                'titulo' => 'Continuar carregamento',
                                                'icone' => 'bi-truck-front',
                                                'classe' => 'btn-outline-info',
                                            ],

                                            'aguardando_conferencia',
                                            'conferindo' => [
                                                'titulo' => 'Continuar conferência',
                                                'icone' => 'bi-clipboard-check',
                                                'classe' => 'btn-outline-primary',
                                            ],

                                            'aguardando_liberacao' => [
                                                'titulo' => 'Continuar liberação do veículo',
                                                'icone' => 'bi-sign-turn-right',
                                                'classe' => 'btn-outline-success',
                                            ],

                                            'em_rota',
                                            'parcial' => [
                                                'titulo' => 'Confirmar entrega',
                                                'icone' => 'bi-check2-circle',
                                                'classe' => 'btn-outline-success',
                                            ],

                                            default => null,
                                        };

                                        $podeCancelar = !in_array(
                                            $statusEntrega,
                                            ['entregue', 'cancelado', 'devolvido'],
                                            true
                                        );
                                    ?>

                                    <div class="d-flex justify-content-center gap-1 flex-nowrap">

                                        
                                        <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>"
                                        class="btn btn-outline-primary btn-sm acao-btn"
                                        title="Visualizar entrega">

                                            <i class="bi bi-eye"></i>
                                        </a>

                                        
                                        <?php if(in_array($statusEntrega, ['em_rota', 'parcial'], true)): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('entregas.confirmar', $entrega->id)); ?>">

                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>

                                                <button type="submit"
                                                        class="btn <?php echo e($acaoOperacional['classe']); ?> btn-sm acao-btn"
                                                        title="<?php echo e($acaoOperacional['titulo']); ?>"
                                                        onclick="return confirm('Confirmar esta entrega como concluída?')">

                                                    <i class="bi <?php echo e($acaoOperacional['icone']); ?>"></i>
                                                </button>
                                            </form>

                                        <?php elseif($acaoOperacional): ?>
                                            <a href="<?php echo e(route('romaneios.create', ['entrega_id' => $entrega->id])); ?>"
                                            class="btn <?php echo e($acaoOperacional['classe']); ?> btn-sm acao-btn"
                                            title="<?php echo e($acaoOperacional['titulo']); ?>">

                                                <i class="bi <?php echo e($acaoOperacional['icone']); ?>"></i>
                                            </a>

                                        <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm acao-btn"
                                                    title="Nenhuma operação disponível para este status"
                                                    disabled>

                                                <i class="bi bi-clipboard-x"></i>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <?php if($podeCancelar): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('entregas.cancelar', $entrega->id)); ?>">

                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>

                                                <input type="hidden"
                                                    name="motivo"
                                                    value="Cancelada pelo painel de entregas.">

                                                <button type="submit"
                                                        class="btn btn-outline-danger btn-sm acao-btn"
                                                        title="Cancelar entrega"
                                                        onclick="return confirm('Deseja realmente cancelar esta entrega?')">

                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm acao-btn"
                                                    title="Cancelamento indisponível para este status"
                                                    disabled>

                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                            <tr class="collapse linha-itens"
                                id="itens-entrega-<?php echo e($entrega->id); ?>">

                                <td colspan="8">
                                    <div class="painel-itens">
                                        <div class="painel-itens-header">
                                            <span>
                                                <i class="bi bi-box-seam me-2"></i>
                                                Itens da Entrega
                                                <?php echo e($entrega->codigo_entrega ?? '#' . $entrega->id); ?>

                                            </span>

                                            <span class="badge bg-dark">
                                                <?php echo e($totalItens); ?> item(ns)
                                            </span>
                                        </div>

                                        <div class="painel-itens-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered item-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 38%;">Produto</th>
                                                            <th style="width: 14%;">Localização</th>
                                                            <th style="width: 12%;">Prevista</th>
                                                            <th style="width: 12%;">Entregue</th>
                                                            <th style="width: 12%;">Saldo</th>
                                                            <th style="width: 6%;">Unid.</th>
                                                            <th style="width: 6%;">Status</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        <?php $__empty_2 = true; $__currentLoopData = $itensEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                            <?php
                                                                $produto = $item->vendaItem?->produto
                                                                    ?? $item->itemOrcamento?->produto;

                                                                $quantidadePrevista = (float) (
                                                                    $item->quantidade_prevista ?? 0
                                                                );

                                                                $quantidadeEntregue = (float) (
                                                                    $item->quantidade_entregue ?? 0
                                                                );

                                                                $saldo = max(
                                                                    0,
                                                                    $quantidadePrevista - $quantidadeEntregue
                                                                );

                                                                $unidade = $produto?->unidade_medida?->sigla
                                                                    ?? $produto?->unidade
                                                                    ?? 'UN';

                                                                $localizacao = $produto?->localizacao_estoque
                                                                    ?? '—';

                                                                $statusItem = strtolower(
                                                                    $item->status ?? 'pendente'
                                                                );

                                                                $statusItemClasse = match ($statusItem) {
                                                                    'separado',
                                                                    'carregado',
                                                                    'entregue' => 'bg-success',

                                                                    'parcial' => 'bg-warning text-dark',

                                                                    'devolvido',
                                                                    'cancelado' => 'bg-danger',

                                                                    default => 'bg-secondary',
                                                                };

                                                                $statusItemLabel = ucfirst(
                                                                    str_replace(
                                                                        '_',
                                                                        ' ',
                                                                        $item->status ?? 'Pendente'
                                                                    )
                                                                );
                                                            ?>

                                                            <tr>
                                                                <td>
                                                                    <div class="fw-semibold">
                                                                        <?php echo e($produto?->nome
                                                                            ?? $produto?->descricao
                                                                            ?? 'Produto não identificado'); ?>

                                                                    </div>

                                                                    <div class="linha-secundaria">
                                                                        Código: <?php echo e($produto?->id ?? '—'); ?>

                                                                    </div>

                                                                    <?php if(!empty($item->observacao)): ?>
                                                                        <div class="linha-secundaria mt-1">
                                                                            <i class="bi bi-chat-left-text me-1"></i>
                                                                            <?php echo e($item->observacao); ?>

                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($localizacao); ?>

                                                                </td>

                                                                <td class="text-end fw-semibold">
                                                                    <?php echo e(number_format(
                                                                            $quantidadePrevista,
                                                                            2,
                                                                            ',',
                                                                            '.'
                                                                        )); ?>

                                                                </td>

                                                                <td class="text-end">
                                                                    <?php echo e(number_format(
                                                                            $quantidadeEntregue,
                                                                            2,
                                                                            ',',
                                                                            '.'
                                                                        )); ?>

                                                                </td>

                                                                <td class="text-end fw-bold <?php echo e($saldo > 0 ? 'text-danger' : 'text-success'); ?>">
                                                                    <?php echo e(number_format(
                                                                            $saldo,
                                                                            2,
                                                                            ',',
                                                                            '.'
                                                                        )); ?>

                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($unidade); ?>

                                                                </td>

                                                                <td class="text-center">
                                                                    <span class="badge <?php echo e($statusItemClasse); ?>">
                                                                        <?php echo e($statusItemLabel); ?>

                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                            <tr>
                                                                <td colspan="7"
                                                                    class="text-center text-muted py-3">

                                                                    Nenhum item encontrado para esta entrega.
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8"
                                    class="text-center text-muted py-4">

                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    Nenhuma entrega encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($entregas->hasPages()): ?>
            <div class="card-footer d-flex justify-content-center py-2">
                <?php echo e($entregas->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas/index.blade.php ENDPATH**/ ?>