

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Unidades de Medida Desativadas</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <a href="<?php echo e(route('unidades.index')); ?>" class="btn btn-secondary mb-3">Voltar</a>

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
                    <form action="<?php echo e(route('unidades.reativar', $unidade->id)); ?>" method="POST" style="display:inline-block;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <button class="btn btn-sm btn-success" onclick="return confirm('Deseja reativar esta unidade?')">Reativar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="3" class="text-center">Nenhuma unidade desativada.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php echo e($unidades->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\unidades\inativos.blade.php ENDPATH**/ ?>