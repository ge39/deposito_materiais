

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Nova Ocorrência Pós-Venda</h2>

    <form action="<?php echo e(route('pos_vendas.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="venda_id" value="<?php echo e($venda_id); ?>">

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" id="tipo" class="form-control" required>
                <option value="">Selecione</option>
                <option value="devolucao">Devolução</option>
                <option value="troca">Troca</option>
                <option value="atendimento">Atendimento</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="valor_devolucao" class="form-label">Valor Devolução</label>
            <input type="number" step="0.01" name="valor_devolucao" id="valor_devolucao" class="form-control" value="0">
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar Ocorrência</button>
        <a href="<?php echo e(route('pos_vendas.index')); ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pos_venda\create.blade.php ENDPATH**/ ?>