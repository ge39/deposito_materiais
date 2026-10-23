

<?php $__env->startSection('title', 'Página não encontrada'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="text-center">

        <h2 class="text-secondary fw-bold">Página não encontrada (404)</h2>

        <p class="text-muted mt-3">
            A rota acessada não existe ou foi removida.
        </p>

        <div class="alert alert-light border mt-4 text-start">
            <strong>Você pode:</strong>
            <ul class="mb-0">
                <li>Verificar se digitou o endereço corretamente.</li>
                <li>Retornar ao painel principal.</li>
                <li>Utilizar o menu do sistema para navegar.</li>
            </ul>
        </div>

        <div class="mt-4">
            <a href="<?php echo e(url('/')); ?>" class="btn btn-dark px-4">
                Voltar ao Início
            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\errors\404-ajuda.blade.php ENDPATH**/ ?>