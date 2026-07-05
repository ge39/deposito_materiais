

<?php $__env->startSection('content'); ?>

<div class="container-fluid px-2">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-truck-front me-2"></i>
                Painel da Expedição
            </h4>
            <small class="text-muted">
                Controle operacional de romaneios, veículos, separação e carregamentos.
            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Entregas
            </a>

            <a href="<?php echo e(route('romaneios.create')); ?>" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Novo Romaneio
            </a>

            <button type="button" onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </button>
        </div>
    </div>

    
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">ENTREGAS FATURADAS</small>
                    <h4 class="mb-0"><?php echo e($kpis['entregas_disponiveis'] ?? 0); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">ABERTOS</small>
                    <h4 class="mb-0"><?php echo e($kpis['romaneios_abertos'] ?? 0); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">CARREGANDO</small>
                    <h4 class="mb-0"><?php echo e($kpis['romaneios_carregando'] ?? 0); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">CARREGADOS</small>
                    <h4 class="mb-0"><?php echo e($kpis['romaneios_carregados'] ?? 0); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">EM ROTA</small>
                    <h4 class="mb-0"><?php echo e($kpis['romaneios_em_rota'] ?? 0); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <small class="text-muted">ROMANEIOS HOJE</small>
                    <h4 class="mb-0"><?php echo e($romaneios->count() ?? 0); ?></h4>
                </div>
            </div>
        </div>

    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="<?php echo e(route('expedicao.index')); ?>" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Data</label>
                    <input type="date" name="data" value="<?php echo e($data ?? now()->toDateString()); ?>" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = ['Aberto', 'Em Separação', 'Carregando', 'Carregado', 'Parcial', 'Em Rota', 'Cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusOpcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($statusOpcao); ?>" <?php if(($status ?? '') === $statusOpcao): echo 'selected'; endif; ?>>
                                <?php echo e($statusOpcao); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>

                    <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">

        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-clipboard-check me-2"></i>
                        Romaneios da Expedição
                    </strong>

                    <small><?php echo e($romaneios->count()); ?> registro(s)</small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Romaneio</th>
                                    <th>Motorista</th>
                                    <th>Entregas</th>
                                    <th>Itens</th>
                                    <th>Status</th>
                                    <th>Carregado</th>
                                    <th>Veículo</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $romaneios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $romaneio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $totalItens = $romaneio->itens->count() ?? 0;

                                        $itensConcluidos = $romaneio->itens
                                            ->whereIn('status', ['Carregado', 'Parcial'])
                                            ->count();

                                        $percentual = $totalItens > 0
                                            ? round(($itensConcluidos / $totalItens) * 100, 2)
                                            : 0;

                                        $entregasIds = $romaneio->itens
                                            ->pluck('entregaItem.entrega.id')
                                            ->filter()
                                            ->unique();

                                        $totalEntregas = $entregasIds->count();

                                        $statusClass = match($romaneio->status) {
                                            'Aberto' => 'bg-primary',
                                            'Em Separação' => 'bg-warning text-dark',
                                            'Separado' => 'bg-info text-dark',
                                            'Carregando' => 'bg-warning text-dark',
                                            'Carregado' => 'bg-success',
                                            'Parcial' => 'bg-warning text-dark',
                                            'Em Rota' => 'bg-dark',
                                            'Entregue' => 'bg-success',
                                            'Cancelado' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    ?>

                                    <tr>
                                        <td class="fw-semibold">
                                            <?php echo e($romaneio->codigo ?? $romaneio->codigo_romaneio ?? '#' . $romaneio->id); ?>

                                            <br>
                                            <small class="text-muted">
                                                <?php echo e(optional($romaneio->created_at)->format('d/m/Y H:i')); ?>

                                            </small>
                                        </td>

                                        <td>
                                            <?php echo e($romaneio->motorista->nome ?? 'Não informado'); ?>

                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                <?php echo e($totalEntregas); ?>

                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                <?php echo e($totalItens); ?>

                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($statusClass); ?>">
                                                <?php echo e($romaneio->status); ?>

                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <small><?php echo e(number_format($percentual, 2, ',', '.')); ?>%</small>
                                            </div>

                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" style="width: <?php echo e($percentual); ?>%;"></div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <?php echo e($romaneio->veiculo->placa ?? $romaneio->veiculo->descricao ?? '-'); ?>

                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo e(route('expedicao.show', $romaneio->id)); ?>"
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <a href="<?php echo e(route('expedicao.operacao', $romaneio->id)); ?>"
                                                   class="btn btn-primary">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                                    Operar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum romaneio encontrado para expedição.
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

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong>
                        <i class="bi bi-box-seam me-2"></i>
                        Entregas Faturadas
                    </strong>
                </div>

                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $entregasDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">
                                        #<?php echo e($entrega->id); ?> -
                                        <?php echo e($entrega->cliente->nome ?? 'Cliente não informado'); ?>

                                    </div>

                                    <small class="text-muted">
                                        <?php echo e($entrega->endereco_entrega ?? 'Endereço não informado'); ?>

                                    </small>
                                </div>

                                <span class="badge bg-success">
                                    <?php echo e($entrega->status); ?>

                                </span>
                            </div>

                            <small class="text-muted d-block mt-1">
                                Itens: <?php echo e($entrega->itens->count()); ?>

                            </small>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted text-center py-3">
                            Nenhuma entrega faturada disponível.
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo e(route('romaneios.create')); ?>" class="btn btn-success btn-sm w-100 mt-2">
                        <i class="bi bi-plus-circle me-1"></i>
                        Criar Romaneio
                    </a>
                </div>
            </div>

            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i>
                O romaneio pode agrupar várias entregas. A operação item a item acontece na tela <strong>Operar</strong>.
            </div>
        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/expedicao/index.blade.php ENDPATH**/ ?>