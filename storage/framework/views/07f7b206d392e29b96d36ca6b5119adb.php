

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Nova Categoria</h2>

    <form action="<?php echo e(route('categorias.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="<?php echo e(route('categorias.index')); ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\categorias\create.blade.php ENDPATH**/ ?>