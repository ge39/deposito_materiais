

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Usuários Ativos</h2>
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Usuário
        </a>
    </div>

    <!-- Alertas -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php elseif(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if($users->count() > 0): ?>
        <div class="row g-4">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($user->funcionario->nome ?? '—'); ?></h5>
                            <p class="card-text mb-1"><strong>E-mail:</strong> <?php echo e($user->funcionario->email ?? '—'); ?></p>
                            <p class="card-text mb-1"><strong>Nível de Acesso:</strong> <?php echo e(ucfirst($user->nivel_acesso)); ?></p>
                            <span class="badge bg-success mb-2">Ativo</span>

                            <div class="card-footer text-center mt-3">
                                <a href="<?php echo e(route('users.show', $user->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                                <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>

                                <form action="<?php echo e(route('users.desativar', $user->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Deseja realmente desativar este usuário?')">
                                        <i class="bi bi-x-circle"></i> Desativar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($users->links('pagination::bootstrap-5')); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center py-4 shadow-sm rounded mt-3">
            Nenhum usuário ativo encontrado.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/users/index.blade.php ENDPATH**/ ?>