<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Editar Regra de Comissão
        </h1>

        <div class="text-muted">
            Atualize os parâmetros da regra.
        </div>

    </div>


    <form
        method="POST"
        action="<?php echo e(route(
                'comissoes.regras.update',
                $regra
            )); ?>"
    >

        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php echo $__env->make('comissoes.regras._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\comissoes\regras\edit.blade.php ENDPATH**/ ?>