

<?php $__env->startSection('content'); ?>

<div class="container my-5">

    
    <div class="text-center mb-4">
        <h3 class="fw-bold text-success">Correção de Divergências do Caixa #<?php echo e($caixa->id); ?></h3>
        <p class="text-muted fs-4">Os valores divergentes do caixa <strong>#<?php echo e($caixa->id); ?></strong> foram corrigidos pela auditoria.</p>
    </div>

    
    <div class="card shadow-sm border-success">
        <div class="card-header bg-success text-light fw-bold">
            Detalhes do Caixa
        </div>

        <div class="card-body fs-5">

            
            <?php if(session('auditoria_sucesso')): ?>
                <div class="alert alert-success d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                    <div>
                        <?php echo e(session('auditoria_sucesso')); ?>

                    </div>
                </div>
            <?php endif; ?>

            
            <div class="row mb-4 border-bottom border-secondary p-1">
                <div class="col-md-3">
                    <strong>ID (Caixa)</strong><br>
                    <?php echo e($caixa->id); ?>

                </div>
                <div class="col-md-3">
                    <strong>Operador</strong><br>
                    <?php echo e($caixa->usuario->name ?? 'Não identificado'); ?>

                </div>
                <div class="col-md-3">
                    <strong>Terminal</strong><br>
                    <?php echo e($caixa->terminal_id); ?>

                </div>
                <div class="col-md-3">
                    <strong>Data de Fechamento</strong><br>
                    <?php echo e($caixa->data_fechamento?->format('d/m/Y H:i') ?? '-'); ?>

                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <strong>Status</strong><br>
                    <span class="badge bg-success"><?php echo e(ucfirst($caixa->status)); ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Fundo de Troco</strong><br>
                    R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?>

                </div>
                <div class="col-md-3">
                    <strong>Fechamento</strong><br>
                    R$ <?php echo e(number_format($caixa->valor_fechamento, 2, ',', '.')); ?>

                </div>
                
            </div>

            
            <div class="mt-4 text-end">
                <a href="<?php echo e(route('caixa.abrir')); ?>" class="btn btn-primary me-2">
                    Abrir Novo Caixa
                </a>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
                    Voltar
                </a>
            </div>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/confirmacao_auditoria.blade.php ENDPATH**/ ?>