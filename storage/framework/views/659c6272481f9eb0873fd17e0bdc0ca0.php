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
                ];

                $possuiRastreador = (bool) old(
                    'possui_rastreador',
                    $veiculo->possui_rastreador ?? false
                );

                $rastreamentoAtivo = (bool) old(
                    'rastreamento_ativo',
                    $veiculo->exists
                        ? ($veiculo->rastreamento_ativo ?? false)
                        : true
                );
            ?>

            <?php $__currentLoopData = $recursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $dados): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="form-check form-switch">
                            <input
                                type="hidden"
                                name="<?php echo e($campo); ?>"
                                value="0"
                            >

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

                            <label
                                class="form-check-label fw-semibold"
                                for="<?php echo e($campo); ?>"
                            >
                                <?php echo e($dados['label']); ?>

                            </label>

                            <?php $__errorArgs = [$campo];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block">
                                    <?php echo e($message); ?>

                                </div>
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
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
            Rastreamento
        </h6>

        <small class="text-muted">
            Vincule o veículo ao identificador exibido no cadastro do dispositivo no Traccar.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3 align-items-stretch">

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="form-check form-switch">
                        <input
                            type="hidden"
                            name="possui_rastreador"
                            value="0"
                        >

                        <input
                            class="form-check-input <?php $__errorArgs = ['possui_rastreador'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            type="checkbox"
                            role="switch"
                            name="possui_rastreador"
                            id="possui_rastreador"
                            value="1"
                            <?php echo e($possuiRastreador ? 'checked' : ''); ?>

                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="possui_rastreador"
                        >
                            Possui rastreador
                        </label>

                        <?php $__errorArgs = ['possui_rastreador'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Indica que este veículo possui um dispositivo de rastreamento vinculado.
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <label
                        for="traccar_unique_id"
                        class="form-label fw-semibold"
                    >
                        Identificador do dispositivo Traccar
                    </label>

                    <input
                        type="text"
                        maxlength="128"
                        class="form-control <?php $__errorArgs = ['traccar_unique_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        id="traccar_unique_id"
                        name="traccar_unique_id"
                        value="<?php echo e(old('traccar_unique_id', $veiculo->traccar_unique_id ?? '')); ?>"
                        placeholder="Ex.: 87431092"
                        autocomplete="off"
                        <?php echo e($possuiRastreador ? '' : 'disabled'); ?>

                    >

                    <?php $__errorArgs = ['traccar_unique_id'];
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

                    <small class="text-muted d-block mt-2">
                        Informe o identificador visível no Traccar
                        (<strong>uniqueId</strong>).
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="form-check form-switch">
                        <input
                            type="hidden"
                            name="rastreamento_ativo"
                            value="0"
                        >

                        <input
                            class="form-check-input <?php $__errorArgs = ['rastreamento_ativo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            type="checkbox"
                            role="switch"
                            name="rastreamento_ativo"
                            id="rastreamento_ativo"
                            value="1"
                            <?php echo e($rastreamentoAtivo ? 'checked' : ''); ?>

                            <?php echo e($possuiRastreador ? '' : 'disabled'); ?>

                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="rastreamento_ativo"
                        >
                            Rastreamento ativo
                        </label>

                        <?php $__errorArgs = ['rastreamento_ativo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback d-block">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Desative para manter o vínculo com o Traccar sem exibir o veículo no rastreamento operacional.
                    </small>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const possuiRastreador =
                document.getElementById(
                    'possui_rastreador'
                );

            const traccarUniqueId =
                document.getElementById(
                    'traccar_unique_id'
                );

            const rastreamentoAtivo =
                document.getElementById(
                    'rastreamento_ativo'
                );

            if (
                ! possuiRastreador
                || ! traccarUniqueId
                || ! rastreamentoAtivo
            ) {
                return;
            }

            function atualizarEstadoRastreamento() {
                const habilitado =
                    possuiRastreador.checked;

                traccarUniqueId.disabled =
                    ! habilitado;

                rastreamentoAtivo.disabled =
                    ! habilitado;

                if (! habilitado) {
                    rastreamentoAtivo.checked =
                        false;
                }
            }

            possuiRastreador.addEventListener(
                'change',
                atualizarEstadoRastreamento
            );

            atualizarEstadoRastreamento();
        }
    );
</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\veiculos\partials\_recursos.blade.php ENDPATH**/ ?>