

<?php $__env->startSection('title', 'Rota do PDV não encontrada'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="text-center">

        <h2 class="text-danger fw-bold">Rota do PDV não encontrada</h2>

        <p class="text-muted mt-3">
            O recurso solicitado dentro do módulo PDV não existe.
        </p>

        <div class="alert alert-warning mt-4 text-start">
            <strong>Possíveis causas:</strong>
            <ul class="mb-0">
                <li>Terminal não configurado corretamente.</li>
                <li>Rota removida ou alterada.</li>
                <li>Acesso manual via URL incorreta.</li>
            </ul>
        </div>

        <div class="mt-4">
            <a href="<?php echo e(route('pdv.index')); ?>" class="btn btn-danger me-2">
                Voltar ao PDV
            </a>

            <a href="<?php echo e(url('/')); ?>" class="btn btn-dark px-4">
                Voltar ao Início
            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/errors/404-pdv.blade.php ENDPATH**/ ?>