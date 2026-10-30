

<?php $__env->startSection('title', 'Página não encontrada'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">

            <div class="card shadow-sm border-0">
                <div class="card-body p-5">

                    <h2 class="fw-bold text-secondary">
                        Página não encontrada (404)
                    </h2>

                    <p class="text-muted mt-3">
                        A rota acessada não existe no sistema.
                    </p>

                    <div class="alert alert-light border mt-4">
                        <strong>URL informada:</strong><br>
                        <code><?php echo e($url ?? request()->fullUrl()); ?></code>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo e(url('/')); ?>" class="btn btn-dark px-4">
                                Voltar ao Início
                            </a>

                        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
                            Voltar
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/errors/rota-invalida.blade.php ENDPATH**/ ?>