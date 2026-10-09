<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label',
    'value' => 1,
    'checked' => false,
    'description' => null,
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
    'name',
    'label',
    'value' => 1,
    'checked' => false,
    'description' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $checkboxId = $attributes->get('id', $name);

    $isChecked = old($name) !== null
        ? (bool) old($name)
        : (bool) $checked;
?>

<label
    for="<?php echo e($checkboxId); ?>"
    class="erp-check-card"
>

    <input
        type="hidden"
        name="<?php echo e($name); ?>"
        value="0"
    >

    <input
        type="checkbox"
        name="<?php echo e($name); ?>"
        id="<?php echo e($checkboxId); ?>"
        value="<?php echo e($value); ?>"
        <?php if($isChecked): echo 'checked'; endif; ?>
        <?php echo e($attributes->class([
            'form-check-input',
            'is-invalid' => $errors->has($name),
        ])); ?>

    >

    <span class="erp-check-content">

        <span class="erp-check-label">
            <?php echo e($label); ?>

        </span>

        <?php if($description): ?>
            <span class="erp-check-description">
                <?php echo e($description); ?>

            </span>
        <?php endif; ?>

        <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <span class="erp-form-error">
                <?php echo e($message); ?>

            </span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    </span>

</label><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\form\checkbox.blade.php ENDPATH**/ ?>