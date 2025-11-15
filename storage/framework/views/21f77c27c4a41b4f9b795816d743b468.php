

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Funcionários Ativos</h2>
        <a href="<?php echo e(route('funcionarios.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Funcionário
        </a>
    </div>

    <!-- Alertas -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <form action="<?php echo e(route('funcionarios.search')); ?>" method="GET" class="mb-3 row g-2 align-items-end">
    
        <div class="col-md-8">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nome, CPF ou e-mail..." value="<?php echo e(request('q')); ?>">
        </div>

        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
            <a href="<?php echo e(route('funcionarios.index')); ?>" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>

    </form>


<?php if(isset($mensagem)): ?>
    <div class="alert alert-warning text-center">
        <?php echo e($mensagem); ?>

    </div>
<?php endif; ?>

    <?php if($funcionarios->count() > 0): ?>
        <div class="row g-4">
            <?php $__currentLoopData = $funcionarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $funcionario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($funcionario->nome); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo e($funcionario->funcao); ?></h6>
                            <p class="card-text mb-1"><strong>CPF:</strong> <?php echo e($funcionario->cpf); ?></p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> <?php echo e($funcionario->telefone); ?></p>
                            <p class="card-text mb-1"><strong>E-mail:</strong> <?php echo e($funcionario->email); ?></p>
                            <span class="badge bg-success mb-2">Ativo</span>

                            <div class="card-footer text-center">
                                <a href="<?php echo e(route('funcionarios.show', $funcionario->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                                <a href="<?php echo e(route('funcionarios.edit', $funcionario->id)); ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>

                                <form action="<?php echo e(route('funcionarios.desativar', $funcionario->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Deseja realmente desativar este funcionário?')">
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
            <?php echo e($funcionarios->links('pagination::bootstrap-5')); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhum funcionário ativo encontrado.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/funcionarios/index.blade.php ENDPATH**/ ?>