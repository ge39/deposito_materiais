

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php
        $statusRomaneio = $romaneio->status ?? 'Aberto';
        $progresso = $resumo['progresso'] ?? 0;

        $statusBadge = match($statusRomaneio) {
            'Aberto' => 'bg-secondary',
            'Em Separação' => 'bg-warning text-dark',
            'Carregando' => 'bg-primary',
            'Carregado' => 'bg-success',
            'Liberado', 'Em Rota' => 'bg-dark',
            'Entregue' => 'bg-success',
            'Cancelado' => 'bg-danger',
            default => 'bg-secondary',
        };

        $totalEntregas = $romaneio->itens
            ->map(fn($item) => $item->entregaItem->entrega->id ?? null)
            ->filter()
            ->unique()
            ->count();

        $totalClientes = $romaneio->itens
            ->map(fn($item) => $item->entregaItem->entrega->cliente->id ?? null)
            ->filter()
            ->unique()
            ->count();
    ?>

    
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-box-seam me-2"></i>Expedição do Romaneio
            </h3>

            <div class="text-muted">
                Romaneio:
                <strong><?php echo e($romaneio->codigo ?? '#' . $romaneio->id); ?></strong>

                <span class="mx-2">|</span>

                Status:
                <span class="badge <?php echo e($statusBadge); ?>">
                    <?php echo e($statusRomaneio); ?>

                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <a href="<?php echo e(route('romaneios.imprimir', $romaneio->id)); ?>" target="_blank" class="btn btn-outline-dark">
                <i class="bi bi-printer"></i> Imprimir
            </a>

            <button type="button" onclick="window.location.reload()" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>

            <a href="<?php echo e(route('expedicao.operacao', $romaneio->id)); ?>" class="btn btn-primary">
                <i class="bi bi-gear"></i> Operação
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success shadow-sm border-0">
            <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger shadow-sm border-0">
            <i class="bi bi-exclamation-triangle me-1"></i><?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Status</small>
                    <h5 class="fw-bold mb-0"><?php echo e($statusRomaneio); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Entregas</small>
                    <h5 class="fw-bold mb-0"><?php echo e($totalEntregas); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Clientes</small>
                    <h5 class="fw-bold mb-0"><?php echo e($totalClientes); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Carregados</small>
                    <h5 class="fw-bold mb-0 text-success"><?php echo e($resumo['carregados'] ?? 0); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Pendentes</small>
                    <h5 class="fw-bold mb-0 text-secondary"><?php echo e($resumo['pendentes'] ?? 0); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Progresso</small>
                    <h5 class="fw-bold mb-0"><?php echo e($progresso); ?>%</h5>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <strong>Progresso da Expedição</strong>
                <span><?php echo e($progresso); ?>%</span>
            </div>

            <div class="progress" style="height: 22px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: <?php echo e($progresso); ?>%;"
                     aria-valuenow="<?php echo e($progresso); ?>"
                     aria-valuemin="0"
                     aria-valuemax="100">
                    <?php echo e($progresso); ?>%
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-person-badge me-2"></i>Motorista
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        <?php echo e($romaneio->motorista->nome ?? 'Não informado'); ?>

                    </div>

                    <small class="text-muted">
                        <?php echo e($romaneio->motorista->telefone ?? 'Telefone não informado'); ?>

                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-truck me-2"></i>Veículo
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        <?php echo e($romaneio->veiculo->descricao ?? $romaneio->veiculo->nome ?? 'Não informado'); ?>

                    </div>

                    <small class="text-muted">
                        Placa: <?php echo e($romaneio->veiculo->placa ?? 'Não informada'); ?>

                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-calendar-event me-2"></i>Dados do Romaneio
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        Data:
                        <?php echo e(!empty($romaneio->data_romaneio) ? \Carbon\Carbon::parse($romaneio->data_romaneio)->format('d/m/Y') : 'Não informada'); ?>

                    </div>

                    <small class="text-muted">
                        Criado em: <?php echo e(optional($romaneio->created_at)->format('d/m/Y H:i')); ?>

                    </small>
                </div>
            </div>
        </div>

    </div>

    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="bi bi-lightning-charge me-2"></i>Ações Operacionais
        </div>

        <div class="card-body d-flex flex-wrap gap-2">
            <?php if($statusRomaneio === 'Aberto'): ?>
                <form action="<?php echo e(route('expedicao.iniciar-separacao', $romaneio->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-warning">
                        <i class="bi bi-box"></i> Iniciar Separação
                    </button>
                </form>
            <?php endif; ?>

            <?php if(in_array($statusRomaneio, ['Aberto', 'Em Separação'])): ?>
                <form action="<?php echo e(route('expedicao.iniciar-carregamento', $romaneio->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-primary">
                        <i class="bi bi-truck"></i> Iniciar Carregamento
                    </button>
                </form>
            <?php endif; ?>

            <?php if($statusRomaneio === 'Carregando'): ?>
                <form action="<?php echo e(route('expedicao.finalizar-carregamento', $romaneio->id)); ?>"
                      method="POST"
                      onsubmit="return confirm('Deseja finalizar o carregamento deste romaneio?');">
                    <?php echo csrf_field(); ?>

                    <button class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Finalizar Carregamento
                    </button>
                </form>
            <?php endif; ?>

            <?php if($statusRomaneio === 'Carregado'): ?>
                <form action="<?php echo e(route('expedicao.liberar-rota', $romaneio->id)); ?>"
                      method="POST"
                      onsubmit="return confirm('Deseja liberar este romaneio para rota?');">
                    <?php echo csrf_field(); ?>

                    <button class="btn btn-dark">
                        <i class="bi bi-signpost-split"></i> Liberar para Rota
                    </button>
                </form>
            <?php endif; ?>

            <a href="<?php echo e(route('expedicao.operacao', $romaneio->id)); ?>" class="btn btn-outline-primary">
                <i class="bi bi-gear"></i> Abrir Tela de Operação
            </a>
        </div>
    </div>

    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-check me-2"></i>Itens do Romaneio
            </span>

            <small class="text-muted">
                Consulta dos itens vinculados
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Entrega</th>
                        <th>Cliente</th>
                        <th>Endereço</th>
                        <th>Produto</th>
                        <th class="text-center">Previsto</th>
                        <th class="text-center">Carregado</th>
                        <th class="text-center">Diferença</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $entregaItem = $item->entregaItem ?? null;
                            $entrega = $entregaItem->entrega ?? null;
                            $cliente = $entrega->cliente ?? null;
                            $produto = $entregaItem->produto ?? null;

                            $previsto = (float) ($item->quantidade_prevista ?? 0);
                            $carregado = (float) ($item->quantidade_carregada ?? 0);
                            $diferenca = $previsto - $carregado;

                            $itemBadge = match($item->status) {
                                'Carregado' => 'bg-success',
                                'Parcial' => 'bg-warning text-dark',
                                'Carregando' => 'bg-primary',
                                'Separado' => 'bg-info text-dark',
                                'Devolvido' => 'bg-dark',
                                'Cancelado' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        ?>

                        <tr>
                            <td>
                                <strong>#<?php echo e($entrega->id ?? '-'); ?></strong>
                            </td>

                            <td>
                                <?php echo e($entrega?->orcamento?->cliente->nome ?? 'Cliente não informado'); ?>

                            </td>

                            <td>
                                <small class="text-muted">
                                    <?php echo e($entrega->endereco_entrega ?? 'Endereço não informado'); ?>

                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                   <?php echo e($item->entregaItem?->itemOrcamento?->produto?->nome ?? 'Produto não informado'); ?>

                                </div>

                                <small class="text-muted">
                                    Item: #<?php echo e($item->entrega_item_id); ?>

                                </small>
                            </td>

                            <td class="text-center">
                                <?php echo e(number_format($previsto, 2, ',', '.')); ?>

                            </td>

                            <td class="text-center">
                                <?php echo e(number_format($carregado, 2, ',', '.')); ?>

                            </td>

                            <td class="text-center">
                                <?php if($diferenca > 0): ?>
                                    <span class="badge bg-warning text-dark">
                                        <?php echo e(number_format($diferenca, 2, ',', '.')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success">
                                        OK
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <span class="badge <?php echo e($itemBadge); ?>">
                                    <?php echo e($item->status); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Nenhum item vinculado a este romaneio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/expedicao/show.blade.php ENDPATH**/ ?>