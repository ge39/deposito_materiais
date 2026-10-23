

<?php $__env->startSection('content'); ?>
<div class="container text-center mt-5">

    <h3 class="text-danger">Venda cancelada com sucesso</h3>
    <a href="<?php echo e(route('pdv.index')); ?>" class="btn btn-primary mt-3">Voltar ao PDV</a>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pdv\cancelado.blade.php ENDPATH**/ ?>