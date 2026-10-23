<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'icon' => 'bi bi-pencil-square',
    'backUrl' => null,
    'backLabel' => 'Voltar',
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
    'subtitle' => null,
    'icon' => 'bi bi-pencil-square',
    'backUrl' => null,
    'backLabel' => 'Voltar',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header class="erp-cadastro-header">

    <div class="erp-cadastro-header-main">

        <div class="erp-cadastro-header-icon" aria-hidden="true">
            <i class="<?php echo e($icon); ?>"></i>
        </div>

        <div class="erp-cadastro-header-text">
            <h1 class="erp-cadastro-title">
                <?php echo e($title); ?>

            </h1>

            <?php if($subtitle): ?>
                <p class="erp-cadastro-subtitle">
                    <?php echo e($subtitle); ?>

                </p>
            <?php endif; ?>
        </div>

    </div>

    <div class="erp-cadastro-header-actions">

        <?php echo e($actions ?? ''); ?>


        <?php if($backUrl): ?>
            <a
                href="<?php echo e($backUrl); ?>"
                class="erp-btn erp-btn-outline"
            >
                <i class="bi bi-arrow-left"></i>
                <?php echo e($backLabel); ?>

            </a>
        <?php endif; ?>

    </div>

</header><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\header.blade.php ENDPATH**/ ?>