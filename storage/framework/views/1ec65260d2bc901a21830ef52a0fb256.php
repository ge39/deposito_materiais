

<?php $__env->startSection('title', 'Acesso Negado'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">

                    <div class="mb-4">
                        <i class="bi bi-shield-lock-fill text-warning" style="font-size: 60px;"></i>
                    </div>

                    <h3 class="fw-bold text-warning">
                        Acesso Negado (403)
                    </h3>

                    <p class="text-muted mt-3">
                        <?php echo e($exception->getMessage() 
                            ?: 'Você não possui permissão ou há uma regra de negócio impedindo esta operação.'); ?>

                    </p>

                    <div class="alert alert-light border mt-4 text-start">
                        <strong>Possíveis causas:</strong>
                        <ul class="mb-0">
                            <li>Usuário sem permissão adequada.</li>
                            <li>Cliente padrão "VENDA BALCAO" não está ativo.</li>
                            <li>Terminal não identificado.</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <div class="mt-4">
                            <a href="<?php echo e(url('/')); ?>" class="btn btn-dark px-4">
                                Voltar ao Início
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/errors/403.blade.php ENDPATH**/ ?>