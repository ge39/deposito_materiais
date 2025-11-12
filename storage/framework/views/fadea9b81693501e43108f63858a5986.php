

<?php $__env->startSection('content'); ?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0 text-danger">Fornecedores Inativos</h2>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('fornecedores.index')); ?>" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-muted mb-2"><?php echo e($fornecedor->nome); ?></h5>
                        <p class="mb-1"><strong>CNPJ:</strong> <?php echo e($fornecedor->cnpj ?? '—'); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo e($fornecedor->email ?? '—'); ?></p>
                        <p class="mb-1"><strong>Telefone:</strong> <?php echo e($fornecedor->telefone ?? '—'); ?></p>
                        <p class="mb-1"><strong>Cidade:</strong> <?php echo e($fornecedor->cidade ?? '—'); ?></p>
                        <p class="mb-3"><strong>Status:</strong> <span class="text-danger">Inativo</span></p>

                        <div class="d-flex gap-2">
                            <form action="<?php echo e(route('fornecedores.ativar', $fornecedor->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <button class="btn btn-success btn-sm" onclick="return confirm('Deseja reativar este fornecedor?')">
                                    Reativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-muted mt-4">
                Nenhum fornecedor inativo encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fornecedores/inativos.blade.php ENDPATH**/ ?>