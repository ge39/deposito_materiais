<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-shield-check me-2 text-danger"></i>
            Tipos de Carga Aceitos
        </h6>
        <small class="text-muted">
            Define quais materiais podem ser transportados por este veículo.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <?php
                $restricoes = [
                    'aceita_areia_pedra' => 'Areia / Pedra',
                    'aceita_blocos_tijolos' => 'Blocos / Tijolos',
                    'aceita_cimento_argamassa' => 'Cimento / Argamassa',
                    'aceita_tintas_quimicos' => 'Tintas / Químicos',
                    'aceita_madeiras' => 'Madeiras',
                    'aceita_ferragens' => 'Ferragens',
                    'aceita_pisos_revestimentos' => 'Pisos / Revestimentos',
                    'aceita_hidraulica_eletrica' => 'Hidráulica / Elétrica',
                ];
            ?>

            <?php $__currentLoopData = $restricoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3">
                    <input type="hidden" name="<?php echo e($campo); ?>" value="0">

                    <div class="form-check border rounded-3 p-3 bg-light-subtle h-100">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="<?php echo e($campo); ?>"
                            id="<?php echo e($campo); ?>"
                            value="1"
                            <?php echo e(old($campo, $veiculo->$campo ?? false) ? 'checked' : ''); ?>

                        >
                        <label class="form-check-label fw-semibold" for="<?php echo e($campo); ?>">
                            <?php echo e($label); ?>

                        </label>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\veiculos\partials\_restricoes.blade.php ENDPATH**/ ?>