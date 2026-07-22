

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Editar Unidade de Medida</h1>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('unidades.update', $unidade->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="nome" class="form-control" value="<?php echo e(old('nome', $unidade->nome)); ?>" required>
        </div>

        <div class="mb-3">
            <label>Sigla</label>
            <input type="text" name="sigla" class="form-control" value="<?php echo e(old('sigla', $unidade->sigla)); ?>" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="ativo" value="1" class="form-check-input" <?php echo e($unidade->ativo ? 'checked' : ''); ?>>
            <label class="form-check-label">Ativo</label>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="<?php echo e(route('unidades.index')); ?>" class="btn btn-secondary">Voltar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\unidades\edit.blade.php ENDPATH**/ ?>