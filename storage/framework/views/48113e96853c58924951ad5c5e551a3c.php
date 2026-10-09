

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Todas as Devoluções</h2>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Venda #</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Motivo</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $devolucoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devolucao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($devolucao->id); ?></td>
                    <td><?php echo e($devolucao->cliente->nome ?? '—'); ?></td>
                    <td><?php echo e($devolucao->venda->id ?? '—'); ?></td>
                    <td><?php echo e($devolucao->produto->nome ?? '—'); ?></td>
                    <td><?php echo e($devolucao->quantidade); ?></td>
                    <td><?php echo e($devolucao->motivo); ?></td>
                    <td>
                        <span class="badge 
                            <?php if($devolucao->status == 'pendente'): ?> bg-warning
                            <?php elseif($devolucao->status == 'aprovada'): ?> bg-success
                            <?php else: ?> bg-danger <?php endif; ?>">
                            <?php echo e(ucfirst($devolucao->status)); ?>

                        </span>
                    </td>
                    <td><?php echo e($devolucao->created_at->format('d/m/Y H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center" style="background-color: #f5deb3;">
                        Nenhuma devolução registrada.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\todas.blade.php ENDPATH**/ ?>