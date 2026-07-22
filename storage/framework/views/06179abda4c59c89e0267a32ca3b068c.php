

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Editar Ocorrência Pós-Venda #<?php echo e($posVenda->id); ?></h2>

    <form action="<?php echo e(route('pos_vendas.update', $posVenda->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control" required>
                <option value="pendente" <?php echo e($posVenda->status == 'pendente' ? 'selected' : ''); ?>>Pendente</option>
                <option value="concluido" <?php echo e($posVenda->status == 'concluido' ? 'selected' : ''); ?>>Concluído</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control"><?php echo e($posVenda->descricao); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Atualizar Ocorrência</button>
        <a href="<?php echo e(route('pos_vendas.index')); ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pos_venda\edit.blade.php ENDPATH**/ ?>