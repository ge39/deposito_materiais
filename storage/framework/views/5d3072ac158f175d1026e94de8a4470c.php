<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => 'Selecione...',
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
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => 'Selecione...',
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
    $selectId = $attributes->get('id', $name);
    $selectedValue = old($name, $value);
?>

<div class="erp-form-group">

    <label
        for="<?php echo e($selectId); ?>"
        class="erp-form-label"
    >
        <?php echo e($label); ?>


        <?php if($required): ?>
            <span class="erp-required">*</span>
        <?php endif; ?>
    </label>

    <select
        name="<?php echo e($name); ?>"
        id="<?php echo e($selectId); ?>"
        <?php if($required): ?>
            required
        <?php endif; ?>
        <?php echo e($attributes->class([
            'form-select',
            'erp-form-select',
            'is-invalid' => $errors->has($name),
        ])); ?>

    >
        <?php if($placeholder !== false): ?>
            <option value="">
                <?php echo e($placeholder); ?>

            </option>
        <?php endif; ?>

        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionValue => $optionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option
                value="<?php echo e($optionValue); ?>"
                <?php if((string) $selectedValue === (string) $optionValue): echo 'selected'; endif; ?>
            >
                <?php echo e($optionLabel); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

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

</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\form\select.blade.php ENDPATH**/ ?>