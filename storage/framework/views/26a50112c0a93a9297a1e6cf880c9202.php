

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Filiais / Empresas</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <a href="<?php echo e(route('empresa.create')); ?>" class="btn btn-primary mb-4">Nova Empresa / Filial</a>
    <a href="<?php echo e(route('empresa.desativadas')); ?>" class="btn btn-secondary mb-4">
        <i class="bi bi-archive"></i> Ver Desativadas
    </a>
    <div class="row g-4">
        <?php $__empty_1 = true; $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4">
                <div class="card shadow-sm border rounded-2 h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo e($empresa->nome); ?></h5>
                        <p class="card-text mb-1"><strong>CNPJ:</strong> <?php echo e($empresa->cnpj); ?></p>
                        <p class="card-text mb-1"><strong>Telefone:</strong> <?php echo e($empresa->telefone); ?></p>
                        <p class="card-text mb-3"><strong>Cidade / Estado:</strong> <?php echo e($empresa->cidade); ?> / <?php echo e($empresa->estado); ?></p>

                        <div class="mt-auto d-flex justify-content-between">
                            <a href="<?php echo e(route('empresa.edit', $empresa->id)); ?>" class="btn btn-warning mb-4">Editar</a>

                            <form action="<?php echo e(route('empresa.desativar', $empresa->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <button type="submit" class="btn btn-danger mb-4"
                                    onclick="return confirm('Deseja realmente desativar esta Empresa?');">
                                    Desativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Nenhuma empresa cadastrada.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/empresa/index.blade.php ENDPATH**/ ?>