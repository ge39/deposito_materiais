

<?php $__env->startSection('title', 'Erro Interno'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">

                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 60px;"></i>
                    </div>

                    <h3 class="fw-bold text-danger">
                        Erro Interno do Sistema (500)
                    </h3>

                    <p class="text-muted mt-3">
                        <?php echo e($exception->getMessage() 
                            ?: 'Ocorreu uma falha inesperada durante o processamento da operação.'); ?>

                    </p>

                    <div class="alert alert-light border mt-4 text-start">
                        <strong>Ação recomendada:</strong>
                        <ul class="mb-0">
                            <li>Tente atualizar a página.</li>
                            <li>Verifique se o caixa está aberto.</li>
                            <li>Se o problema persistir, contate o administrador.</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <button onclick="location.reload()" class="btn btn-outline-secondary me-2">
                            Recarregar
                        </button>

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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\errors\500.blade.php ENDPATH**/ ?>