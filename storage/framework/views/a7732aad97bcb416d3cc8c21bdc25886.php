

<?php $__env->startSection('content'); ?>

<div class="container">

    <h2 class="mb-4">Abrir nova venda</h2>

    <form action="<?php echo e(route('pdv.abrirConfirmar')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label class="form-label">Cliente (opcional)</label>
            <input type="text" class="form-control" name="cliente_nome"
                   placeholder="Nome do cliente">
        </div>

        <button class="btn btn-success w-100">Confirmar e abrir venda</button>
    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pdv\abrir.blade.php ENDPATH**/ ?>