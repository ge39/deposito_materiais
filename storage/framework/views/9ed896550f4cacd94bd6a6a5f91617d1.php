



<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Empresas / Filiais Desativadas</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($empresas->isEmpty()): ?>
        <div class="alert alert-warning text-center" style="background-color: #f5deb3;">
            Nenhuma empresa desativada encontrada.
        </div>
        <a href="<?php echo e(route('empresa.index')); ?>" class="btn btn-secondary mb-4">
            <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>
    <?php else: ?>
        <div class="row">
            <?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?php echo e($empresa->nome); ?></h5>
                            <p class="card-text mb-1"><strong>CNPJ:</strong> <?php echo e($empresa->cnpj ?? 'Não informado'); ?></p>
                            <p class="card-text mb-1"><strong>Cidade:</strong> <?php echo e($empresa->cidade ?? '-'); ?></p>
                            <p class="card-text mb-3"><strong>Estado:</strong> <?php echo e($empresa->estado ?? '-'); ?></p>

                            <div class="d-flex justify-content-between">
                                 <form action="<?php echo e(route('empresa.ativar', $empresa->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-success mb-4">
                                        <i class="bi bi-check-circle"></i> Ativar
                                    </button>
                                </form>
                                <a href="<?php echo e(route('empresa.index')); ?>" class="btn btn-secondary mb-4">
                                    <i class="bi bi-arrow-left-circle"></i> Voltar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/empresa/desativadas.blade.php ENDPATH**/ ?>