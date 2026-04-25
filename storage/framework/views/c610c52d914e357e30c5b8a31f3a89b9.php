

<?php $__env->startSection('content'); ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">

    <div class="card shadow-lg border-0" style="max-width: 520px; width: 100%;">
        <div class="card-body text-center p-5">

            <!-- Ícone -->
            <div class="mb-4">
                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center"
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-check-lg text-white" style="font-size: 40px;"></i>
                </div>
            </div>

            <!-- Título -->
            <h3 class="fw-bold text-success mb-2">
                Caixa fechado com sucesso!
            </h3>

            <!-- Texto -->
            <p class="text-muted mb-4">
                O fechamento do caixa foi concluído corretamente e registrado no sistema.
            </p>

            <!-- Informações -->
            <div class="text-start bg-light rounded p-3 mb-4">
                <div class="mb-2">
                    <strong>Caixa:</strong> #<?php echo e($caixa->id); ?>

                </div>

                <div class="mb-2">
                    <strong>Fechado por:</strong> <?php echo e(auth()->user()->name); ?>

                </div>

                <div class="mb-2">
                    <strong>Data:</strong> <?php echo e($caixa->data_fechamento->format('d/m/Y H:i')); ?>

                </div>

                <div>
                    <strong>Status:</strong>
                    <span class="badge bg-success">
                        <?php echo e($caixa->status === 'fechado_sem_movimento'
                            ? 'Fechado sem movimento'
                            : 'Fechado com movimento'); ?>

                    </span>
                </div>
            </div>

            <!-- Botões -->
            <div class="d-grid gap-2">
                <a href="<?php echo e(route('caixa.abrir')); ?>" class="btn btn-success btn-lg">
                    Abrir novo caixa
                </a>

                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">
                    Sair desta página
                </a>
            </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/confirmacao.blade.php ENDPATH**/ ?>