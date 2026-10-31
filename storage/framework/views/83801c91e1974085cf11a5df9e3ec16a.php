<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'icon' => 'bi bi-grid',
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="erp-section">

    <header class="erp-section-header">

        <div class="erp-section-icon" aria-hidden="true">
            <i class="<?php echo e($icon); ?>"></i>
        </div>

        <div class="erp-section-heading">
            <h2 class="erp-section-title">
                <?php echo e($title); ?>

            </h2>

            <?php if($description): ?>
                <p class="erp-section-description">
                    <?php echo e($description); ?>

                </p>
            <?php endif; ?>
        </div>

        <?php if(isset($headerActions)): ?>
            <div class="ms-auto">
                <?php echo e($headerActions); ?>

            </div>
        <?php endif; ?>

    </header>

    <div class="erp-section-body">
        <?php echo e($slot); ?>

    </div>

</section><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/components/erp/cadastro/section.blade.php ENDPATH**/ ?>