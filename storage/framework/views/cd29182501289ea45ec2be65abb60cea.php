

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Pós-Vendas</h2>
    <a href="<?php echo e(route('pos_vendas.create', ['venda_id' => 0])); ?>" class="btn btn-primary mb-3">Nova Ocorrência</a>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Venda</th>
                <th>Tipo</th>
                <th>Valor Devolução</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $posVendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($pos->id); ?></td>
                    <td><?php echo e($pos->venda_id); ?></td>
                    <td><?php echo e(ucfirst($pos->tipo)); ?></td>
                    <td><?php echo e(number_format($pos->valor_devolucao, 2, ',', '.')); ?></td>
                    <td><?php echo e(ucfirst($pos->status)); ?></td>
                    <td><?php echo e($pos->data_registro); ?></td>
                    <td>
                        <a href="<?php echo e(route('pos_vendas.show', $pos->id)); ?>" class="btn btn-info btn-sm">Ver</a>
                        <a href="<?php echo e(route('pos_vendas.edit', $pos->id)); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form action="<?php echo e(route('pos_vendas.destroy', $pos->id)); ?>" method="POST" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esta ocorrência?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center">Nenhuma ocorrência registrada</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pos_venda\index.blade.php ENDPATH**/ ?>