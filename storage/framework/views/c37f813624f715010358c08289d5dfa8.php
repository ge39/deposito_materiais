

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Detalhes da Categoria</h2>

    <div class="card p-3">
        <p><strong>ID:</strong> <?php echo e($categoria->id); ?></p>
        <p><strong>Nome:</strong> <?php echo e($categoria->nome); ?></p>
        <p><strong>Descrição:</strong> <?php echo e($categoria->descricao); ?></p>
        <p><strong>Criado em:</strong> <?php echo e($categoria->created_at->format('d/m/Y H:i')); ?></p>
    </div>

    <div class="mt-3">
        <a href="<?php echo e(route('categorias.edit', $categoria->id)); ?>" class="btn btn-warning">Editar</a>
        <a href="<?php echo e(route('categorias.index')); ?>" class="btn btn-secondary">Voltar</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\categorias\show.blade.php ENDPATH**/ ?>