<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-rulers me-2 text-info"></i>
            Dimensões do Veículo
        </h6>
        <small class="text-muted">
            Medidas úteis para carga, acesso, altura máxima e futuras restrições logísticas.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label for="comprimento_m" class="form-label fw-semibold">Comprimento (m)</label>
                <input type="number" step="0.01" min="0" name="comprimento_m" id="comprimento_m"
                       class="form-control <?php $__errorArgs = ['comprimento_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('comprimento_m', $veiculo->comprimento_m ?? '')); ?>"
                       placeholder="Ex: 6.50">
                <?php $__errorArgs = ['comprimento_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="col-md-4">
                <label for="largura_m" class="form-label fw-semibold">Largura (m)</label>
                <input type="number" step="0.01" min="0" name="largura_m" id="largura_m"
                       class="form-control <?php $__errorArgs = ['largura_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('largura_m', $veiculo->largura_m ?? '')); ?>"
                       placeholder="Ex: 2.20">
                <?php $__errorArgs = ['largura_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="col-md-4">
                <label for="altura_m" class="form-label fw-semibold">Altura (m)</label>
                <input type="number" step="0.01" min="0" name="altura_m" id="altura_m"
                       class="form-control <?php $__errorArgs = ['altura_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('altura_m', $veiculo->altura_m ?? '')); ?>"
                       placeholder="Ex: 3.10">
                <?php $__errorArgs = ['altura_m'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/partials/_dimensoes.blade.php ENDPATH**/ ?>