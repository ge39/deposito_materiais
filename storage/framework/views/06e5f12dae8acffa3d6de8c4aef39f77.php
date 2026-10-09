

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Unidades de Medida</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <a href="<?php echo e(route('unidades.create')); ?>" class="btn btn-primary mb-3">Nova Unidade</a>
    <a href="<?php echo e(route('unidades.inativos')); ?>" class="btn btn-secondary mb-3">Unidades Desativadas</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Sigla</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($unidade->nome); ?></td>
                <td><?php echo e($unidade->sigla); ?></td>
                <td>
                    <a href="<?php echo e(route('unidades.edit', $unidade->id)); ?>" class="btn btn-sm btn-warning">Editar</a>
                    <form action="<?php echo e(route('unidades.destroy', $unidade->id)); ?>" method="POST" style="display:inline-block;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja desativar esta unidade?')">Desativar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="3" class="text-center">Nenhuma unidade encontrada.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php echo e($unidades->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\unidades\index.blade.php ENDPATH**/ ?>