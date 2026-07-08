

<?php $__env->startSection('content'); ?>

<style>
    .kpi-card {
        border-radius: 8px;
        min-height: 105px;
        transition: .2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .75rem rgba(0,0,0,.16) !important;
    }

    .kpi-card .card-body {
        padding: 14px 16px;
    }

    .kpi-card h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
    }

    .kpi-card i {
        font-size: 2.1rem;
        opacity: .6;
    }

    .info-small {
        font-size: .76rem;
        color: #6c757d;
    }

    .documentos a,
    .documentos span {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
    }

    .acao-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .table-romaneios th,
    .table-romaneios td {
        vertical-align: middle;
    }

    .table-romaneios th {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-2">

    <?php
        $colecao = collect($romaneios->items());

        $totalRomaneios = method_exists($romaneios, 'total') ? $romaneios->total() : $romaneios->count();
        $totalGerados = $colecao->where('status', 'Gerado')->count();
        $totalCarregando = $colecao->where('status', 'Carregando')->count();
        $totalCarregados = $colecao->whereIn('status', ['Carregado', 'Finalizado', 'Concluido', 'Concluído'])->count();
    ?>

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-clipboard-check me-2"></i>Gerenciamento de Romaneios
            </h4>
            <small class="text-muted">
                Painel operacional para separação, conferência, impressão e expedição.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-truck me-1"></i>Entregas
            </a>

            <?php if(Route::has('romaneios.create')): ?>
                <a href="<?php echo e(route('romaneios.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Novo Romaneio
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-3">
        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total</small>
                        <h3><?php echo e($totalRomaneios); ?></h3>
                    </div>
                    <i class="bi bi-clipboard-data text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Gerados</small>
                        <h3><?php echo e($totalGerados); ?></h3>
                    </div>
                    <i class="bi bi-flag text-secondary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Carregando</small>
                        <h3><?php echo e($totalCarregando); ?></h3>
                    </div>
                    <i class="bi bi-box-seam text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Finalizados</small>
                        <h3><?php echo e($totalCarregados); ?></h3>
                    </div>
                    <i class="bi bi-check2-circle text-success"></i>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white">
            <strong><i class="bi bi-funnel me-2"></i>Filtros de Consulta</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="<?php echo e(route('romaneios.index')); ?>" class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Buscar</label>
                    <input type="text"
                           name="busca"
                           value="<?php echo e(request('busca')); ?>"
                           class="form-control form-control-sm"
                           placeholder="Cliente, romaneio, venda, orçamento...">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="Gerado" <?php echo e(request('status') == 'Gerado' ? 'selected' : ''); ?>>Gerado</option>
                        <option value="Carregando" <?php echo e(request('status') == 'Carregando' ? 'selected' : ''); ?>>Carregando</option>
                        <option value="Carregado" <?php echo e(request('status') == 'Carregado' ? 'selected' : ''); ?>>Carregado</option>
                        <option value="Finalizado" <?php echo e(request('status') == 'Finalizado' ? 'selected' : ''); ?>>Finalizado</option>
                        <option value="Cancelado" <?php echo e(request('status') == 'Cancelado' ? 'selected' : ''); ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Data Inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="<?php echo e(request('data_inicio')); ?>"
                           class="form-control form-control-sm">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Data Final</label>
                    <input type="date"
                           name="data_fim"
                           value="<?php echo e(request('data_fim')); ?>"
                           class="form-control form-control-sm">
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>

                        <a href="<?php echo e(route('romaneios.index')); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-ul me-2"></i>Painel Operacional de Romaneios</strong>

            <span class="badge bg-light text-dark">
                Total: <?php echo e($totalRomaneios); ?>

            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table class="table table-hover table-bordered table-sm align-middle mb-0 table-romaneios">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th style="width: 16%;">Romaneio</th>
                            <th style="width: 18%;">Cliente / Documentos</th>
                            <th style="width: 18%;">Destino</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 14%;">Carregamento</th>
                            <th style="width: 10%;">Equipe</th>
                            <th style="width: 8%;">Emissão</th>
                            <th style="width: 6%;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $romaneios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $romaneio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $entrega = $romaneio->entrega ?? null;
                                $orcamento = $entrega->orcamento ?? null;
                                $cliente = $orcamento->cliente ?? $entrega->cliente ?? null;
                                $itens = $romaneio->itens ?? collect();

                                $codigoRomaneio = $romaneio->codigo_romaneio
                                    ?? $romaneio->codigo
                                    ?? 'ROM-' . str_pad($romaneio->id, 4, '0', STR_PAD_LEFT);

                                $clienteNome = $cliente->nome
                                    ?? $entrega->cliente_nome
                                    ?? $entrega->nome_cliente
                                    ?? 'Cliente não informado';

                                $status = $romaneio->status ?? 'Gerado';

                                $badgeStatus = match($status) {
                                    'Gerado' => 'secondary',
                                    'Pendente' => 'warning text-dark',
                                    'Separando', 'Em separação' => 'primary',
                                    'Carregando' => 'info text-dark',
                                    'Carregado' => 'success',
                                    'Finalizado', 'Concluido', 'Concluído' => 'success',
                                    'Cancelado' => 'danger',
                                    default => 'secondary',
                                };

                                $totalItens = $itens->count();

                                $itensCarregados = $itens->whereIn('status', [
                                    'Carregado',
                                    'Conferido',
                                    'Finalizado'
                                ])->count();

                                $percentual = (float) ($romaneio->percentual_carregado ?? 0);

                                if ($percentual <= 0 && $totalItens > 0) {
                                    $percentual = ($itensCarregados / $totalItens) * 100;
                                }

                                $destino = $entrega->endereco_entrega
                                    ?? $entrega->endereco_entrega_concatenado
                                    ?? $entrega->bairro
                                    ?? $entrega->cidade
                                    ?? 'Destino não informado';
                            ?>

                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($codigoRomaneio); ?></div>
                                    <div class="info-small">ID interno: #<?php echo e($romaneio->id); ?></div>
                                </td>

                                <td>
                                    <div class="fw-bold mb-1"><?php echo e($clienteNome); ?></div>

                                    <div class="documentos">
                                        <?php if($entrega && !empty($entrega->venda_id)): ?>
                                            <a href="<?php echo e(url('/venda/' . $entrega->venda_id . '/cupom')); ?>" target="_self">
                                                <i class="bi bi-receipt me-1"></i>VEN-<?php echo e($entrega->venda_id); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-receipt me-1"></i>Venda não vinculada
                                            </span>
                                        <?php endif; ?>

                                        <?php if($entrega): ?>
                                            <?php if(Route::has('entregas.show')): ?>
                                                <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>">
                                                    <i class="bi bi-truck me-1"></i>ENT-<?php echo e($entrega->id); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="bi bi-truck me-1"></i>ENT-<?php echo e($entrega->id); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if($orcamento): ?>
                                            <?php if(Route::has('orcamentos.show')): ?>
                                                <a href="<?php echo e(route('orcamentos.show', $orcamento->id)); ?>">
                                                    <i class="bi bi-file-earmark-text me-1"></i>ORÇ-<?php echo e($orcamento->id); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="bi bi-file-earmark-text me-1"></i>ORÇ-<?php echo e($orcamento->id); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php elseif($entrega && !empty($entrega->orcamento_id)): ?>
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i>ORÇ-<?php echo e($entrega->orcamento_id); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold"><?php echo e($destino); ?></div>
                                    <?php if(!empty($entrega->periodo_entrega)): ?>
                                        <div class="info-small">
                                            Período: <?php echo e($entrega->periodo_entrega); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-<?php echo e($badgeStatus); ?>">
                                        <?php echo e($status); ?>

                                    </span>
                                </td>

                                <td style="min-width: 160px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-semibold"><?php echo e($itensCarregados); ?>/<?php echo e($totalItens); ?> itens</small>
                                        <small class="fw-bold"><?php echo e(number_format($percentual, 0, ',', '.')); ?>%</small>
                                    </div>

                                    <div class="progress" style="height: 12px; border-radius: 8px;">
                                        <div class="progress-bar bg-success"
                                             role="progressbar"
                                             style="width: <?php echo e(min($percentual, 100)); ?>%;">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?php echo e($romaneio->motorista->name ?? $romaneio->motorista->nome ?? 'Motorista —'); ?>

                                    </div>
                                    <div class="info-small">
                                        Veículo: <?php echo e($romaneio->veiculo->placa ?? '—'); ?>

                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="fw-semibold">
                                        <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y')); ?>

                                    </div>
                                    <div class="info-small">
                                        <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('H:i')); ?>

                                    </div>
                                </td>

                                <td class="text-end">

                                    <div class="d-flex justify-content-end gap-1 flex-wrap " >
                                        <?php if(Route::has('romaneios.show')): ?>
                                            <a href="<?php echo e(route('romaneios.show', $romaneio->id)); ?>"
                                               class="btn btn-outline-primary btn-sm acao-btn"
                                               title="Visualizar romaneio">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if(Route::has('romaneios.imprimir')): ?>
                                            <a href="<?php echo e(route('romaneios.imprimir', $romaneio->id)); ?>"
                                               class="btn btn-outline-dark btn-sm acao-btn"
                                               target="_blank"
                                               title="Imprimir romaneio">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php elseif(Route::has('romaneios.print')): ?>
                                            <a href="<?php echo e(route('romaneios.print', $romaneio->id)); ?>"
                                               class="btn btn-outline-dark btn-sm acao-btn"
                                               target="_blank"
                                               title="Imprimir romaneio">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if(Route::has('expedicao.show')): ?>
                                            <a href="<?php echo e(route('expedicao.show', $romaneio->id)); ?>"
                                               class="btn btn-outline-success btn-sm acao-btn"
                                               title="Abrir expedição">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Nenhum romaneio encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if(method_exists($romaneios, 'links')): ?>
            <div class="card-footer bg-white d-flex justify-content-center py-2">
                <?php echo e($romaneios->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/index.blade.php ENDPATH**/ ?>