

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Lista de Categorias</h2>
    <a href="<?php echo e(route('categorias.create')); ?>" class="btn btn-primary mb-3">Nova Categoria</a>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($categoria->id); ?></td>
                    <td><?php echo e($categoria->nome); ?></td>
                    <td><?php echo e($categoria->descricao); ?></td>
                    <td>
                        <a href="<?php echo e(route('categorias.show', $categoria->id)); ?>" class="btn btn-info btn-sm">Ver</a>
                        <a href="<?php echo e(route('categorias.edit', $categoria->id)); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form action="<?php echo e(route('categorias.destroy', $categoria->id)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Excluir categoria?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center">Nenhuma categoria cadastrada</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\categorias\index.blade.php ENDPATH**/ ?>