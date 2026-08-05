<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-tools me-2 text-warning"></i>
            Recursos do Veículo
        </h6>
        <small class="text-muted">
            Recursos físicos e operacionais disponíveis para carregamento, transporte e entrega.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <?php
                $recursos = [
                    'possui_munck' => [
                        'label' => 'Possui munck',
                        'desc' => 'Indicado para cargas pesadas ou descarga mecanizada.'
                    ],
                    'possui_carroceria_aberta' => [
                        'label' => 'Carroceria aberta',
                        'desc' => 'Útil para areia, pedra, blocos e materiais grandes.'
                    ],
                    'possui_carroceria_fechada' => [
                        'label' => 'Carroceria fechada',
                        'desc' => 'Melhor para produtos sensíveis ou protegidos.'
                    ],
                    'possui_rastreador' => [
                        'label' => 'Possui rastreador',
                        'desc' => 'Permite controle operacional e acompanhamento futuro.'
                    ],
                ];
            ?>

            <?php $__currentLoopData = $recursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $dados): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="form-check form-switch">
                            <input type="hidden" name="<?php echo e($campo); ?>" value="0">

                            <input
                                class="form-check-input <?php $__errorArgs = [$campo];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                type="checkbox"
                                role="switch"
                                name="<?php echo e($campo); ?>"
                                id="<?php echo e($campo); ?>"
                                value="1"
                                <?php echo e(old($campo, $veiculo->$campo ?? false) ? 'checked' : ''); ?>

                            >

                            <label class="form-check-label fw-semibold" for="<?php echo e($campo); ?>">
                                <?php echo e($dados['label']); ?>

                            </label>

                            <?php $__errorArgs = [$campo];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <?php echo e($dados['desc']); ?>

                        </small>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/partials/_recursos.blade.php ENDPATH**/ ?>