<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Nova Regra de Comissão
        </h1>

        <div class="text-muted">
            Defina vendedor, escopo,
            percentual e vigência.
        </div>

    </div>


    <form
        method="POST"
        action="<?php echo e(route('comissoes.regras.store')); ?>"
    >
        <?php echo csrf_field(); ?>

        <?php echo $__env->make('comissoes.regras._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\comissoes\regras\create.blade.php ENDPATH**/ ?>