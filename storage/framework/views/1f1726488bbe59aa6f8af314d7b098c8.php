

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Orçamentos</h2>
        <a href="<?php echo e(route('orcamentos.create')); ?>" class="btn btn-primary">
            Novo Orçamento
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
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
            <?php $__currentLoopData = $orcamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orcamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($orcamento->id); ?></td>
                <td><?php echo e($orcamento->cliente->nome ?? '-'); ?></td>
                <td><?php echo e($orcamento->data_orcamento); ?></td>
                <td>R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?></td>
                <td><?php echo e(ucfirst($orcamento->status)); ?></td>
                <td>
                    <a href="<?php echo e(route('orcamentos.show', $orcamento->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                    <a href="<?php echo e(route('orcamentos.edit', $orcamento->id)); ?>" class="btn btn-sm btn-warning">Editar</a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/index.blade.php ENDPATH**/ ?>