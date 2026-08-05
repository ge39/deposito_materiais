<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'method' => 'POST',
    'enctype' => null,
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
    'action',
    'method' => 'POST',
    'enctype' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (! $__env->hasRenderedOnce('2b5dd823-efdc-45e4-95c0-969c23afbc1e')): $__env->markAsRenderedOnce('2b5dd823-efdc-45e4-95c0-969c23afbc1e'); ?>
    <?php $__env->startPush('styles'); ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/erp-cadastros.css')); ?>">
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<?php
    $formMethod = strtoupper($method);
    $htmlMethod = in_array($formMethod, ['GET', 'POST'], true)
        ? $formMethod
        : 'POST';
?>

<div class="erp-cadastro-page">
    <div class="erp-cadastro-container">

        <?php echo e($header ?? ''); ?>


        <?php echo e($wizard ?? ''); ?>


        <?php if (isset($component)) { $__componentOriginalbe183a8bed371dd5963290384498ac64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe183a8bed371dd5963290384498ac64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.alert-errors','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.alert-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe183a8bed371dd5963290384498ac64)): ?>
<?php $attributes = $__attributesOriginalbe183a8bed371dd5963290384498ac64; ?>
<?php unset($__attributesOriginalbe183a8bed371dd5963290384498ac64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe183a8bed371dd5963290384498ac64)): ?>
<?php $component = $__componentOriginalbe183a8bed371dd5963290384498ac64; ?>
<?php unset($__componentOriginalbe183a8bed371dd5963290384498ac64); ?>
<?php endif; ?>

        <form
            action="<?php echo e($action); ?>"
            method="<?php echo e($htmlMethod); ?>"
            class="erp-cadastro-form"
            <?php if($enctype): ?>
                enctype="<?php echo e($enctype); ?>"
            <?php endif; ?>
            <?php echo e($attributes); ?>

        >
            <?php if($htmlMethod !== 'GET'): ?>
                <?php echo csrf_field(); ?>
            <?php endif; ?>

            <?php if(! in_array($formMethod, ['GET', 'POST'], true)): ?>
                <?php echo method_field($formMethod); ?>
            <?php endif; ?>

            <div class="erp-cadastro-content">
                <?php echo e($slot); ?>

            </div>

            <?php echo e($actions ?? ''); ?>

        </form>

    </div>
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\page.blade.php ENDPATH**/ ?>