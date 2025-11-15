

<?php $__env->startSection('content'); ?>
<div class="text-center mt-5">
    <h1 class="text-danger">403 - Acesso negado</h1>
    <p>Você não tem permissão para acessar esta página.</p>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary mt-3">Voltar</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/errors/403.blade.php ENDPATH**/ ?>