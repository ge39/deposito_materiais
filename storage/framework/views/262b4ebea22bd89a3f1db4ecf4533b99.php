<?php if($errors->any()): ?>
    <div
        class="erp-validation-alert"
        role="alert"
    >
        <h2 class="erp-validation-alert-title">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Verifique os campos informados
        </h2>

        <ul class="erp-validation-alert-list">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\components\erp\cadastro\alert-errors.blade.php ENDPATH**/ ?>