

<?php $__env->startSection('content'); ?>
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Vendas</h1>
        <a href="<?php echo e(route('vendas.create')); ?>" class="btn btn-primary">Nova Venda</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $vendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($venda->id); ?></td>
                        <td><?php echo e($venda->cliente->nome ?? '-'); ?></td>
                        <td><?php echo e($venda->created_at->format('d/m/Y H:i')); ?></td>
                        <td>R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?></td>
                        <td>
                            <?php if($venda->status == 1): ?>
                                <span class="badge bg-success">Concluída</span>
                            <?php elseif($venda->status == 0): ?>
                                <span class="badge bg-warning text-dark">Pendente</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Cancelada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('vendas.show', $venda->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                            <a href="<?php echo e(route('vendas.edit', $venda->id)); ?>" class="btn btn-sm btn-warning">Editar</a>
                            <form action="<?php echo e(route('vendas.destroy', $venda->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhuma venda encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end">
        <?php echo e($vendas->links()); ?>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>