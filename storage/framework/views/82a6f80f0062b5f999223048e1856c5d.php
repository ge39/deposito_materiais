<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-activity me-2 text-secondary"></i>
            Status Operacional
        </h6>
        <small class="text-muted">
            Situação atual do veículo para uso na expedição.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">
                    Status <span class="text-danger">*</span>
                </label>

                <?php
                    $statusSelecionado = old('status', $veiculo->status ?? 'Ativo');
                ?>

                <select
                    name="status"
                    id="status"
                    class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    required
                >
                    <option value="Ativo" <?php echo e($statusSelecionado === 'Ativo' ? 'selected' : ''); ?>>Ativo</option>
                    <option value="Inativo" <?php echo e($statusSelecionado === 'Inativo' ? 'selected' : ''); ?>>Inativo</option>
                    <option value="Manutenção" <?php echo e($statusSelecionado === 'Manutenção' ? 'selected' : ''); ?>>Manutenção</option>
                    <option value="Indisponível" <?php echo e($statusSelecionado === 'Indisponível' ? 'selected' : ''); ?>>Indisponível</option>
                </select>

                <?php $__errorArgs = ['status'];
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

            <div class="col-md-8">
                <label for="observacao" class="form-label fw-semibold">Observações</label>
                <textarea
                    name="observacao"
                    id="observacao"
                    rows="3"
                    class="form-control <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="Ex: veículo reservado para entregas pesadas, restrição de acesso, manutenção preventiva..."
                ><?php echo e(old('observacao', $veiculo->observacao ?? '')); ?></textarea>

                <?php $__errorArgs = ['observacao'];
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
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\veiculos\partials\_status.blade.php ENDPATH**/ ?>