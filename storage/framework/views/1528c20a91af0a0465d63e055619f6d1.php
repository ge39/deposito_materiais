<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'icon' => 'bi-bar-chart',
    'value' => null,
    'description' => null,
    'badge' => null,
    'badgeClass' => 'text-bg-secondary',
    'modal' => null,
    'action' => 'Ver detalhes',
    'actionRoute' => null,
    'accentClass' => 'text-primary',
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
    'icon' => 'bi-bar-chart',
    'value' => null,
    'description' => null,
    'badge' => null,
    'badgeClass' => 'text-bg-secondary',
    'modal' => null,
    'action' => 'Ver detalhes',
    'actionRoute' => null,
    'accentClass' => 'text-primary',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card jmf-bi-card h-100">

    <div class="card-body">

        <div class="jmf-bi-card-header">

            <div class="jmf-bi-card-title">

                <i class="bi <?php echo e($icon); ?> <?php echo e($accentClass); ?>"></i>

                <span>
                    <?php echo $title; ?>

                </span>

            </div>

        </div>


        <?php if(!is_null($value)): ?>

            <div class="jmf-bi-card-value">
                <?php echo $value; ?>

            </div>

        <?php endif; ?>


        <?php if($description): ?>

            <div class="jmf-bi-card-description">
                <?php echo $description; ?>

            </div>

        <?php endif; ?>


        <div class="jmf-bi-card-footer">

            <div>

                <?php if($badge): ?>

                    <span class="badge <?php echo e($badgeClass); ?>">
                        <?php echo $badge; ?>

                    </span>

                <?php endif; ?>

            </div>


            <div>

                <?php if($modal): ?>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#<?php echo e($modal); ?>"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        <?php echo e($action); ?>

                    </button>

                <?php elseif($actionRoute): ?>

                    <a
                        href="<?php echo e($actionRoute); ?>"
                        class="btn btn-sm btn-primary"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        <?php echo e($action); ?>

                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\bi\card.blade.php ENDPATH**/ ?>