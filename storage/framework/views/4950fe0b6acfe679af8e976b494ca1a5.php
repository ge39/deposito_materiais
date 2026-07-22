<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'cancelUrl' => null,
    'cancelLabel' => 'Cancelar',
    'submitLabel' => 'Salvar cadastro',
    'submitIcon' => 'bi bi-check-lg',
    'showSubmit' => true,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'cancelUrl' => null,
    'cancelLabel' => 'Cancelar',
    'submitLabel' => 'Salvar cadastro',
    'submitIcon' => 'bi bi-check-lg',
    'showSubmit' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<footer class="erp-cadastro-actions">

    <div class="erp-cadastro-actions-left">

        <?php if($cancelUrl): ?>
            <a
                href="<?php echo e($cancelUrl); ?>"
                class="erp-btn erp-btn-outline"
            >
                <i class="bi bi-arrow-left"></i>
                <?php echo e($cancelLabel); ?>

            </a>
        <?php endif; ?>

        <?php echo e($left ?? ''); ?>


    </div>

    <div class="erp-cadastro-actions-right">

        <?php echo e($right ?? ''); ?>


        <?php if($showSubmit): ?>
            <button
                type="submit"
                class="erp-btn erp-btn-primary"
            >
                <i class="<?php echo e($submitIcon); ?>"></i>
                <?php echo e($submitLabel); ?>

            </button>
        <?php endif; ?>

    </div>

</footer><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\actions.blade.php ENDPATH**/ ?>