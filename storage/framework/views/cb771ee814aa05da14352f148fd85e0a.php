

<?php $__env->startSection('content'); ?>

<style>
    .kpi-card {
        transition: .20s;
        cursor: pointer;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.18) !important;
    }

    .mini-indicador {
        font-size: .78rem;
    }

    .table-entregas th,
    .table-entregas td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .acao-btn {
        width: 31px;
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container-fluid">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-truck me-2"></i>Gerenciamento de Entregas
            </h4>
            <small class="text-muted">
                Controle de separação, carregamento, rota e confirmação das entregas.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </a>

            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Exportar
            </button>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e($errors->first()); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row mb-3">

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'pendente'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-warning border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">ENTREGAS</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Pendentes</span>
                                <h4 class="mb-0"><?php echo e($resumo['pendentes'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'separando'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">OPERAÇÃO</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Separando</span>
                                <h4 class="mb-0"><?php echo e($resumo['separando'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-box-seam fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'carregado'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">EXPEDIÇÃO</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Carregados</span>
                                <h4 class="mb-0"><?php echo e($resumo['carregados'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-truck-front fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'em_rota'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-dark border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">LOGÍSTICA</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Em rota</span>
                                <h4 class="mb-0"><?php echo e($resumo['em_rota'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-geo-alt fs-2 text-dark"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'parcial'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">PENDÊNCIA</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Parciais</span>
                                <h4 class="mb-0"><?php echo e($resumo['parciais'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-exclamation-circle fs-2 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-2 col-md-4 mb-2">
            <a href="<?php echo e(route('entregas.index', ['status' => 'entregue'])); ?>" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">FINALIZADAS</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Entregues</span>
                                <h4 class="mb-0"><?php echo e($resumo['entregues'] ?? 0); ?></h4>
                            </div>
                            <i class="bi bi-check2-circle fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white py-2">
            <strong><i class="bi bi-funnel me-2"></i>Filtros</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="<?php echo e(route('entregas.index')); ?>" class="row g-2">

                <div class="col-md-3">
                    <label class="form-label mb-1">Código da Entrega</label>
                    <input type="text"
                           name="codigo_entrega"
                           class="form-control form-control-sm"
                           value="<?php echo e(request('codigo_entrega')); ?>"
                           placeholder="Ex: ENT-20260629">
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="pendente" <?php echo e(request('status') == 'pendente' ? 'selected' : ''); ?>>Pendente</option>
                        <option value="separando" <?php echo e(request('status') == 'separando' ? 'selected' : ''); ?>>Separando</option>
                        <option value="carregado" <?php echo e(request('status') == 'carregado' ? 'selected' : ''); ?>>Carregado</option>
                        <option value="em_rota" <?php echo e(request('status') == 'em_rota' ? 'selected' : ''); ?>>Em rota</option>
                        <option value="entregue" <?php echo e(request('status') == 'entregue' ? 'selected' : ''); ?>>Entregue</option>
                        <option value="parcial" <?php echo e(request('status') == 'parcial' ? 'selected' : ''); ?>>Parcial</option>
                        <option value="devolvido" <?php echo e(request('status') == 'devolvido' ? 'selected' : ''); ?>>Devolvido</option>
                        <option value="cancelado" <?php echo e(request('status') == 'cancelado' ? 'selected' : ''); ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1">Data Prevista</label>
                    <input type="date"
                           name="data_prevista"
                           class="form-control form-control-sm"
                           value="<?php echo e(request('data_prevista')); ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>

                    <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpar
                    </a>
                </div>

            </form>
        </div>
    </div>

    
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-check me-2"></i>Operações de Entrega</strong>

            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark mini-indicador">Total: <?php echo e($entregas->total()); ?></span>
                <span class="badge bg-warning text-dark mini-indicador">Pendentes: <?php echo e($resumo['pendentes'] ?? 0); ?></span>
                <span class="badge bg-danger mini-indicador">Atrasadas: <?php echo e($resumo['atrasadas'] ?? 0); ?></span>
                <span class="badge bg-success mini-indicador">Entregues: <?php echo e($resumo['entregues'] ?? 0); ?></span>
            </div>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover table-sm align-middle mb-0 table-entregas">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Prioridade</th>
                        <th>Código</th>
                        <th>Venda</th>
                        <th>Orçamento</th>
                        <th>Previsão</th>
                        <th>Itens</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Responsável</th>
                        <th>Telefone</th>
                        <th style="width: 210px;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $entregas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <?php
                            $statusClasses = [
                                'pendente'  => 'bg-warning text-dark',
                                'separando' => 'bg-primary',
                                'carregado' => 'bg-info text-dark',
                                'em_rota'   => 'bg-dark',
                                'entregue'  => 'bg-success',
                                'parcial'   => 'bg-secondary',
                                'devolvido' => 'bg-danger',
                                'cancelado' => 'bg-danger',
                            ];

                            $statusLabels = [
                                'pendente'  => 'Pendente',
                                'separando' => 'Separando',
                                'carregado' => 'Carregado',
                                'em_rota'   => 'Em rota',
                                'entregue'  => 'Entregue',
                                'parcial'   => 'Parcial',
                                'devolvido' => 'Devolvido',
                                'cancelado' => 'Cancelado',
                            ];

                            $dataPrevista = $entrega->data_prevista
                                ? \Carbon\Carbon::parse($entrega->data_prevista)->startOfDay()
                                : null;

                            $hoje = now()->startOfDay();

                            $statusFinalizado = in_array($entrega->status, ['entregue', 'cancelado', 'devolvido']);

                            $atrasada = $dataPrevista && $dataPrevista->lt($hoje) && !$statusFinalizado;
                            $venceHoje = $dataPrevista && $dataPrevista->equalTo($hoje) && !$statusFinalizado;
                            $venceAmanha = $dataPrevista && $dataPrevista->equalTo($hoje->copy()->addDay()) && !$statusFinalizado;

                            $totalItens = $entrega->itens ? $entrega->itens->count() : 0;

                            $itensEntregues = $entrega->itens
                                ? $entrega->itens->where('status', 'entregue')->count()
                                : 0;
                        ?>

                        <tr class="<?php echo e($atrasada ? 'table-danger' : ''); ?>">
                            <td class="text-center fw-semibold"><?php echo e($entrega->id); ?></td>

                            <td class="text-center">
                                <?php if($atrasada): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-alarm me-1"></i>ATRASADA
                                    </span>
                                <?php elseif($venceHoje): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-calendar-event me-1"></i>HOJE
                                    </span>
                                <?php elseif($venceAmanha): ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-calendar2-day me-1"></i>AMANHÃ
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-calendar-check me-1"></i>NORMAL
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center fw-bold"><?php echo e($entrega->codigo_entrega ?? '-'); ?></td>
                            <td class="text-center"><?php echo e($entrega->venda_id ?? '-'); ?></td>
                            <td class="text-center"><?php echo e($entrega->orcamento_id ?? '-'); ?></td>
                            <td class="text-center"><?php echo e($dataPrevista ? $dataPrevista->format('d/m/Y') : '-'); ?></td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <?php echo e($itensEntregues); ?>/<?php echo e($totalItens); ?>

                                </span>
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
                                    <?php echo e($statusLabels[$entrega->status] ?? ucfirst($entrega->status)); ?>

                                </span>
                            </td>

                            <td><?php echo e($entrega->responsavel_recebimento ?? '-'); ?></td>
                            <td class="text-center"><?php echo e($entrega->telefone_recebimento ?? '-'); ?></td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-wrap">

                                    <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>"
                                       class="btn btn-outline-primary btn-sm acao-btn"
                                       title="Visualizar entrega">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if($entrega->status === 'pendente'): ?>
                                        <form method="POST" action="<?php echo e(route('entregas.separar', $entrega->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-outline-warning btn-sm acao-btn" title="Iniciar separação">
                                                <i class="bi bi-box-seam"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if($entrega->status === 'separando'): ?>
                                        <form method="POST" action="<?php echo e(route('entregas.carregar', $entrega->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-outline-info btn-sm acao-btn" title="Marcar como carregada">
                                                <i class="bi bi-truck-front"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if($entrega->status === 'carregado'): ?>
                                        <form method="POST" action="<?php echo e(route('entregas.rota', $entrega->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-outline-dark btn-sm acao-btn" title="Enviar para rota">
                                                <i class="bi bi-geo-alt"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if(in_array($entrega->status, ['em_rota', 'parcial'])): ?>
                                        <form method="POST" action="<?php echo e(route('entregas.confirmar', $entrega->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit"
                                                    class="btn btn-outline-success btn-sm acao-btn"
                                                    title="Confirmar entrega"
                                                    onclick="return confirm('Confirmar esta entrega como concluída?')">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if(!in_array($entrega->status, ['entregue', 'cancelado', 'devolvido'])): ?>
                                        <form method="POST" action="<?php echo e(route('entregas.cancelar', $entrega->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="motivo" value="Cancelada pelo painel de entregas.">

                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm acao-btn"
                                                    title="Cancelar entrega"
                                                    onclick="return confirm('Deseja realmente cancelar esta entrega?')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if($entrega->status === 'entregue'): ?>
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm acao-btn"
                                                title="Impressão disponível em fase futura"
                                                disabled>
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                Nenhuma entrega encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

        <?php if($entregas->hasPages()): ?>
            <div class="card-footer d-flex justify-content-center">
                <?php echo e($entregas->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas/index.blade.php ENDPATH**/ ?>