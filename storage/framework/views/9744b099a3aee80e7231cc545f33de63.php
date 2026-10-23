

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Ocorrência Pós-Venda #<?php echo e($posVenda->id); ?></h2>

    <div class="mb-3">
        <strong>Venda:</strong> <?php echo e($posVenda->venda_id); ?>

    </div>
    <div class="mb-3">
        <strong>Tipo:</strong> <?php echo e(ucfirst($posVenda->tipo)); ?>

    </div>
    <div class="mb-3">
        <strong>Valor Devolução:</strong> <?php echo e(number_format($posVenda->valor_devolucao, 2, ',', '.')); ?>

    </div>
    <div class="mb-3">
        <strong>Status:</strong> <?php echo e(ucfirst($posVenda->status)); ?>

    </div>
    <div class="mb-3">
        <strong>Descrição:</strong>
        <p><?php echo e($posVenda->descricao ?: '-'); ?></p>
    </div>

    <h4>Itens afetados</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Unitário</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $posVenda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($item->produto->nome); ?></td>
                    <td><?php echo e($item->quantidade); ?></td>
                    <td><?php echo e(number_format($item->valor_unitario, 2, ',', '.')); ?></td>
                    <td><?php echo e(number_format($item->total, 2, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center">Nenhum item registrado</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?php echo e(route('pos_vendas.index')); ?>" class="btn btn-secondary">Voltar</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pos_venda\show.blade.php ENDPATH**/ ?>