<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
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
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inputId = $attributes->get('id', $name);
    $fieldValue = old($name, $value);
?>

<div class="erp-form-group">

    <label
        for="<?php echo e($inputId); ?>"
        class="erp-form-label"
    >
        <?php echo e($label); ?>


        <?php if($required): ?>
            <span class="erp-required">*</span>
        <?php endif; ?>
    </label>

    <input
        type="<?php echo e($type); ?>"
        name="<?php echo e($name); ?>"
        id="<?php echo e($inputId); ?>"
        value="<?php echo e($fieldValue); ?>"
        <?php if($placeholder): ?>
            placeholder="<?php echo e($placeholder); ?>"
        <?php endif; ?>
        <?php if($required): ?>
            required
        <?php endif; ?>
        <?php echo e($attributes->class([
            'form-control',
            'erp-form-control',
            'is-invalid' => $errors->has($name),
        ])); ?>

    >

    <?php if($help): ?>
        <small class="erp-form-help">
            <?php echo e($help); ?>

        </small>
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

</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\form\input.blade.php ENDPATH**/ ?>