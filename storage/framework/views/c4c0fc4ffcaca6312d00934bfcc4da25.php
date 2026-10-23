<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'steps' => [],
    'current' => 1,
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
    'steps' => [],
    'current' => 1,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $currentStep = max(1, (int) $current);
?>

<?php if(count($steps) > 0): ?>
    <nav
        class="erp-wizard"
        aria-label="Etapas do cadastro"
    >
        <div class="erp-wizard-track">

            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $stepNumber = $index + 1;

                    $stepClass = match (true) {
                        $stepNumber < $currentStep => 'is-completed',
                        $stepNumber === $currentStep => 'is-active',
                        default => '',
                    };

                    $stepLabel = is_array($step)
                        ? ($step['label'] ?? 'Etapa ' . $stepNumber)
                        : $step;
                ?>

                <div
                    class="erp-wizard-step <?php echo e($stepClass); ?>"
                    <?php if($stepNumber === $currentStep): ?>
                        aria-current="step"
                    <?php endif; ?>
                >
                    <span class="erp-wizard-number">
                        <?php if($stepNumber < $currentStep): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?php echo e($stepNumber); ?>

                        <?php endif; ?>
                    </span>

                    <span class="erp-wizard-label">
                        <?php echo e($stepLabel); ?>

                    </span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </nav>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\wizard.blade.php ENDPATH**/ ?>