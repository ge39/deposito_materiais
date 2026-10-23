

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Editar Categoria</h2>

    <form action="<?php echo e(route('categorias.update', $categoria->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?php echo e($categoria->nome); ?>" required>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control"><?php echo e($categoria->descricao); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="<?php echo e(route('categorias.index')); ?>" class="btn btn-secondary">Voltar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\categorias\edit.blade.php ENDPATH**/ ?>