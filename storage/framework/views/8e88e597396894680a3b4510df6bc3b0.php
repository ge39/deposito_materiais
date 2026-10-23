<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'icon' => 'bi bi-grid',
    'badge' => null,
    'compact' => false,
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
    'title',
    'description' => null,
    'icon' => 'bi bi-grid',
    'badge' => null,
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section
    <?php echo e($attributes->class([
        'erp-cadastro-group',
        'erp-cadastro-group-compact' => $compact,
    ])); ?>

>
    <header class="erp-cadastro-group-header">

        <div class="erp-cadastro-group-heading">

            <span
                class="erp-cadastro-group-icon"
                aria-hidden="true"
            >
                <i class="<?php echo e($icon); ?>"></i>
            </span>

            <div class="erp-cadastro-group-title-wrapper">

                <h3 class="erp-cadastro-group-title">
                    <?php echo e($title); ?>

                </h3>

                <?php if($description): ?>
                    <p class="erp-cadastro-group-description">
                        <?php echo e($description); ?>

                    </p>
                <?php endif; ?>

            </div>

        </div>

        <div class="erp-cadastro-group-actions">

            <?php if($badge): ?>
                <span class="erp-cadastro-group-badge">
                    <?php echo e($badge); ?>

                </span>
            <?php endif; ?>

            <?php echo e($actions ?? ''); ?>


        </div>

    </header>

    <div class="erp-cadastro-group-body">
        <?php echo e($slot); ?>

    </div>

</section><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\group.blade.php ENDPATH**/ ?>