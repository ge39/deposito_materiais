

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php
        $colecao = collect($romaneios->items());

        $totalRomaneios = method_exists($romaneios, 'total') ? $romaneios->total() : $romaneios->count();

        $totalGerados = $colecao->where('status', 'Gerado')->count();
        $totalCarregando = $colecao->where('status', 'Carregando')->count();
        $totalCarregados = $colecao->whereIn('status', ['Carregado', 'Finalizado', 'Concluido', 'Concluído'])->count();
        $totalCancelados = $colecao->where('status', 'Cancelado')->count();
    ?>

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clipboard-check me-2"></i>Gerenciamento de Romaneios
            </h3>
            <small class="text-muted">
                Painel operacional para separação, conferência, impressão e expedição dos romaneios.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-truck me-1"></i> Entregas
            </a>

            <?php if(Route::has('romaneios.create')): ?>
                <a href="<?php echo e(route('romaneios.create')); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Novo Romaneio
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Romaneios</small>
                        <h3 class="fw-bold mb-0"><?php echo e($totalRomaneios); ?></h3>
                    </div>
                    <i class="bi bi-clipboard-data fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Gerados</small>
                        <h3 class="fw-bold mb-0"><?php echo e($totalGerados); ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Carregando</small>
                        <h3 class="fw-bold mb-0"><?php echo e($totalCarregando); ?></h3>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Finalizados</small>
                        <h3 class="fw-bold mb-0"><?php echo e($totalCarregados); ?></h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-funnel me-2"></i>
            <strong>Filtros de Consulta</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="<?php echo e(route('romaneios.index')); ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Buscar</label>
                    <input type="text"
                           name="busca"
                           value="<?php echo e(request('busca')); ?>"
                           class="form-control"
                           placeholder="Cliente, romaneio, orçamento...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Gerado" <?php echo e(request('status') == 'Gerado' ? 'selected' : ''); ?>>Gerado</option>
                        <option value="Carregando" <?php echo e(request('status') == 'Carregando' ? 'selected' : ''); ?>>Carregando</option>
                        <option value="Carregado" <?php echo e(request('status') == 'Carregado' ? 'selected' : ''); ?>>Carregado</option>
                        <option value="Finalizado" <?php echo e(request('status') == 'Finalizado' ? 'selected' : ''); ?>>Finalizado</option>
                        <option value="Cancelado" <?php echo e(request('status') == 'Cancelado' ? 'selected' : ''); ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Data Inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="<?php echo e(request('data_inicio')); ?>"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Data Final</label>
                    <input type="date"
                           name="data_fim"
                           value="<?php echo e(request('data_fim')); ?>"
                           class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>

                    <a href="<?php echo e(route('romaneios.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-ul me-2"></i>
                <strong>Painel Operacional de Romaneios</strong>
            </div>

            <span class="badge bg-light text-dark">
                <?php echo e($totalRomaneios); ?> registro(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Romaneio</th>
                            <th>Cliente / Documentos</th>
                            <th>Destino</th>
                            <th>Status</th>
                            <th>Carregamento</th>
                            <th>Emissão</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $romaneios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $romaneio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $entrega = $romaneio->entrega ?? null;
                                $orcamento = $entrega->orcamento ?? null;
                                $cliente = $orcamento->cliente ?? $entrega->cliente ?? null;
                                $itens = $romaneio->itens ?? collect();

                                $codigoRomaneio = $romaneio->codigo_romaneio ?? $romaneio->codigo ?? 'ROM-' . str_pad($romaneio->id, 4, '0', STR_PAD_LEFT);

                                $clienteNome = $cliente->nome
                                    ?? $entrega->cliente_nome
                                    ?? $entrega->nome_cliente
                                    ?? 'Cliente não informado';

                                $status = $romaneio->status ?? 'Gerado';

                                $badgeStatus = match($status) {
                                    'Gerado' => 'secondary',
                                    'Pendente' => 'warning',
                                    'Separando', 'Em separação' => 'info',
                                    'Carregando' => 'primary',
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

                                $destino = $entrega->bairro
                                    ?? $entrega->cidade
                                    ?? $entrega->endereco_entrega
                                    ?? $entrega->endereco_entrega_concatenado
                                    ?? 'Destino não informado';
                            ?>

                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?php echo e($codigoRomaneio); ?>

                                    </div>
                                    <small class="text-muted">
                                        ID interno: #<?php echo e($romaneio->id); ?>

                                    </small>
                                </td>

                               <td>
                                    <div class="fw-bold mb-1">
                                        <?php echo e($clienteNome); ?>

                                    </div>

                                    <div class="d-flex flex-column gap-1">

                                        <?php if($entrega && !empty($entrega->venda_id)): ?>
                                            <a href="<?php echo e(url('/venda/' . $entrega->venda_id . '/cupom')); ?>"
                                            target="_self"
                                            class="text-decoration-none small fw-semibold">
                                                <i class="bi bi-receipt me-1"></i> VEN-<?php echo e($entrega->venda_id); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="small text-muted">
                                                <i class="bi bi-receipt me-1"></i> Venda não vinculada
                                            </span>
                                        <?php endif; ?>

                                        <?php if($entrega): ?>
                                            <?php if(Route::has('entregas.show')): ?>
                                                <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>"
                                                class="text-decoration-none small fw-semibold">
                                                    <i class="bi bi-truck me-1"></i> ENT-<?php echo e($entrega->id); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="small text-muted">
                                                    <i class="bi bi-truck me-1"></i> ENT-<?php echo e($entrega->id); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if($orcamento): ?>
                                            <?php if(Route::has('orcamentos.show')): ?>
                                                <a href="<?php echo e(route('orcamentos.show', $orcamento->id)); ?>"
                                                class="text-decoration-none small fw-semibold">
                                                    <i class="bi bi-file-earmark-text me-1"></i> ORÇ-<?php echo e($orcamento->id); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="small text-muted">
                                                    <i class="bi bi-file-earmark-text me-1"></i> ORÇ-<?php echo e($orcamento->id); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php elseif($entrega && !empty($entrega->orcamento_id)): ?>
                                            <span class="small text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i> ORÇ-<?php echo e($entrega->orcamento_id); ?>

                                            </span>
                                        <?php endif; ?>

                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?php echo e($destino); ?>

                                    </div>

                                    <?php if(!empty($entrega->periodo_entrega)): ?>
                                        <small class="text-muted">
                                            Período: <?php echo e($entrega->periodo_entrega); ?>

                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?php echo e($badgeStatus); ?> px-3 py-2">
                                        <?php echo e($status); ?>

                                    </span>
                                </td>

                                <td style="min-width: 170px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-semibold">
                                            <?php echo e($itensCarregados); ?> / <?php echo e($totalItens); ?> itens
                                        </small>
                                        <small class="fw-bold">
                                            <?php echo e(number_format($percentual, 0, ',', '.')); ?>%
                                        </small>
                                    </div>

                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success"
                                             role="progressbar"
                                             style="width: <?php echo e(min($percentual, 100)); ?>%;">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y')); ?>

                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('H:i')); ?>

                                    </small>
                                </td>

                                <td class="text-end">
    <div class="btn-group btn-group-sm">

        <?php if(Route::has('romaneios.show')): ?>
            <a href="<?php echo e(route('romaneios.show', $romaneio->id)); ?>"
               class="btn btn-outline-primary"
               title="Visualizar romaneio">
                <i class="bi bi-eye"></i>
            </a>
        <?php endif; ?>

        <?php if(Route::has('romaneios.imprimir')): ?>
            <a href="<?php echo e(route('romaneios.imprimir', $romaneio->id)); ?>"
               class="btn btn-outline-dark"
               target="_blank"
               title="Imprimir romaneio">
                <i class="bi bi-printer"></i>
            </a>
        <?php elseif(Route::has('romaneios.print')): ?>
            <a href="<?php echo e(route('romaneios.print', $romaneio->id)); ?>"
               class="btn btn-outline-dark"
               target="_blank"
               title="Imprimir romaneio">
                <i class="bi bi-printer"></i>
            </a>
        <?php endif; ?>

        <?php if(Route::has('expedicao.show')): ?>
            <a href="<?php echo e(route('expedicao.show', $romaneio->id)); ?>"
               class="btn btn-outline-success"
               title="Abrir expedição">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        <?php endif; ?>

    </div>
</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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
            <div class="card-footer bg-white">
                <?php echo e($romaneios->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/index.blade.php ENDPATH**/ ?>