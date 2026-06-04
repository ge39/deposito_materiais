

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-primary text-center">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="container mt-4">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">⚙️ Configuração de Sangria</h5>
                </div>

                <div class="card-body">

                    
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('sangria-config.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Valor limite do caixa  R$ <?php echo e($config->valor_limite ?? '0,00'); ?>

                            </label>

                            <input 
                                type="number" 
                                step="0.01" 
                                name="valor_limite" 
                                class="form-control <?php $__errorArgs = ['valor_limite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Ex: 500.00"
                                value="<?php echo e(old('valor_limite', $config->valor_limite ?? '')); ?>"
                                required
                            >

                            <?php $__errorArgs = ['valor_limite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <div class="form-text">
                                Quando o caixa ultrapassar esse valor, a sangria será sugerida automaticamente.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">
                                Voltar
                            </a>

                            <button type="submit" class="btn btn-success">
                                💾 Salvar Configuração
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/sangria-config/index.blade.php ENDPATH**/ ?>