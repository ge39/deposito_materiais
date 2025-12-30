

<?php $__env->startSection('content'); ?>

<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="max-width: 520px; width: 100%;">

        <div class="card-header text-center">
            <h4 class="mb-0 fw-bold">Abertura de Caixa</h4>
        </div>

        <div class="card-body">

            
            <div class="mb-3">
                <label class="form-label fw-semibold">Terminal</label>
                <input type="text"
                       class="form-control"
                       value="<?php echo e($terminal->nome ?? $terminal->uuid ?? 'Terminal não identificado'); ?>"
                       readOnly>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Operador</label>
                <input type="text"
                       class="form-control"
                       value="<?php echo e(auth()->user()->name); ?>"
                       disabled>
            </div>

            <hr>

            <form method="POST" action="<?php echo e(route('caixa.store')); ?>">
                <?php echo csrf_field(); ?>

                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Valor do fundo anterior</label>
                    <input type="text"
                           class="form-control"
                           value="<?php echo e(number_format($ultimoCaixa->valor_fechamento ?? 0, 2, ',', '.')); ?>"
                           disabled>
                </div>

                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Fundo de troco (R$)</label>
                    <input type="number"
                           name="fundo_troco"
                           step="0.01"
                           min="0"
                           class="form-control"
                           required>
                </div>

                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Observação (opcional)</label>
                    <textarea name="observacao"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                
                <input type="hidden" name="terminal_id" value="<?php echo e($terminal->id); ?>">
                <input type="hidden" name="terminal" value="<?php echo e($terminal->nome ?? null); ?>">
                <input type="hidden" name="valor_fundo_anterior"
                       value="<?php echo e($ultimoCaixa->valor_fechamento ?? 0); ?>">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg fw-bold">
                        ABRIR CAIXA
                    </button>
                </div>
            </form>

        </div>

        <div class="card-footer text-center text-muted small">
            Data/Hora da abertura: <?php echo e(now()->format('d/m/Y H:i')); ?>

        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/caixa/abrir.blade.php ENDPATH**/ ?>