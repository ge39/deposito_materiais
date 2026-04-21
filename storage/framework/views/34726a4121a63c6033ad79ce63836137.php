

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Detalhes do Usuário</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> <?php echo e($user->id); ?></p>
            <p><strong>Funcionário:</strong> <?php echo e($user->funcionario->nome ?? '—'); ?></p>
            <p><strong>Email:</strong> <?php echo e($user->funcionario->email ?? '—'); ?></p>
            <p><strong>Nível de Acesso:</strong> <?php echo e(ucfirst($user->nivel_acesso)); ?></p>
            <p><strong>Status:</strong> 
                <?php if($user->ativo): ?>
                    <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                    <span class="badge bg-danger">Inativo</span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-primary">Editar</a>
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Voltar</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/users/show.blade.php ENDPATH**/ ?>