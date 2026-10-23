

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Gerar Cupom de Devolução</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form action="<?php echo e(route('devolucao.gerar_cupom')); ?>" method="GET">
        <div class="mb-3">
            <label>Selecione a Filial / Empresa</label>
            <select name="empresa_id" class="form-control" required>
                <option value="">-- Selecione --</option>
                <?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($empresa->id); ?>"><?php echo e($empresa->nome); ?> - <?php echo e($empresa->cidade); ?>/<?php echo e($empresa->estado); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <input type="hidden" name="devolucao_id" value="<?php echo e($devolucao->id); ?>">

        <button type="submit" class="btn btn-success">Gerar Cupom PDF</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\selecionar_filial.blade.php ENDPATH**/ ?>