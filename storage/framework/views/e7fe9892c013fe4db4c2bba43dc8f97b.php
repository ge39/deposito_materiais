<?php
    $statusFinaisReagendamento = [
        'Entregue',
        'Entregue_finalizada_com_ocorrencia',
        'Devolvida',
        'Cancelada',
    ];

    $podeReagendar = ! in_array(
        $entrega->status,
        $statusFinaisReagendamento,
        true
    );

    $dataAtualEntrega =
        $entrega->data_prevista_entrega
        ?? $entrega->data_prevista;

    $dataAtualInput = $dataAtualEntrega
        ? \Carbon\Carbon::parse($dataAtualEntrega)->format('Y-m-d')
        : '';
?>

<?php if($podeReagendar): ?>
    <div class="card mt-3">
        <div class="card-header">
            <strong>Reagendar entrega</strong>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="<?php echo e(route('entregas.reagendar', $entrega)); ?>"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>

                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label
                            for="data_prevista_entrega"
                            class="form-label"
                        >
                            Nova data de entrega
                        </label>

                        <input
                            type="date"
                            class="form-control <?php $__errorArgs = ['data_prevista_entrega'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="data_prevista_entrega"
                            name="data_prevista_entrega"
                            value="<?php echo e(old('data_prevista_entrega', $dataAtualInput)); ?>"
                            required
                        >

                        <?php $__errorArgs = ['data_prevista_entrega'];
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
                    </div>

                    <div class="col-md-6">
                        <label
                            for="motivo_reagendamento"
                            class="form-label"
                        >
                            Justificativa
                        </label>

                        <textarea
                            class="form-control <?php $__errorArgs = ['motivo_reagendamento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="motivo_reagendamento"
                            name="motivo_reagendamento"
                            rows="2"
                            required
                        ><?php echo e(old('motivo_reagendamento')); ?></textarea>

                        <?php $__errorArgs = ['motivo_reagendamento'];
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
                    </div>

                    <div class="col-md-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Alterar data
                        </button>
                    </div>
                </div>

                <div class="mt-2 text-muted">
                    Data atual:
                    <strong>
                        <?php echo e($dataAtualEntrega
                            ? \Carbon\Carbon::parse($dataAtualEntrega)->format('d/m/Y')
                            : '-'); ?>

                    </strong>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas\partials\reagendar.blade.php ENDPATH**/ ?>