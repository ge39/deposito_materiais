

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Lista de Clientes</h2>
        <a href="<?php echo e(route('clientes.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Cliente
        </a>
    </div>

    <!-- 🔍 Formulário de Busca -->
    <form action="<?php echo e(route('clientes.index')); ?>" method="GET" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="nome" class="form-label fw-semibold">Nome ou CPF/CNPJ</label>
                <input type="text" name="busca" id="busca" value="<?php echo e(request('busca')); ?>"
                    class="form-control" placeholder="Digite o nome ou CPF/CNPJ">
            </div>
            <div class="col-md-3">
                <label for="tipo" class="form-label fw-semibold">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="fisica" <?php echo e(request('tipo') == 'fisica' ? 'selected' : ''); ?>>Física</option>
                    <option value="juridica" <?php echo e(request('tipo') == 'juridica' ? 'selected' : ''); ?>>Jurídica</option>
                </select>
            </div>
            
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary flex-grow-1">Limpar</a>
            </div>
        </div>
    </form>

    <!-- 🟢 Mensagens -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <!-- 🧾 Lista de Clientes -->
    <?php if($clientes->count() > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo e($cliente->nome); ?></h5>
                            <p class="card-text mb-1"><strong>Tipo:</strong> <?php echo e(ucfirst($cliente->tipo)); ?></p>
                            <p class="card-text mb-1"><strong>CPF/CNPJ:</strong> <?php echo e($cliente->cpf_cnpj); ?></p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> <?php echo e($cliente->telefone); ?></p>
                            <p class="card-text mb-1"><strong>Email:</strong> <?php echo e($cliente->email); ?></p>
                            <p class="card-text mb-1"><strong>Limite de Crédito:</strong> 
                                R$ <?php echo e(number_format($cliente->limite_credito, 2, ',', '.')); ?>

                            </p>
                            <p class="card-text"><strong>Observações:</strong> <?php echo e($cliente->observacoes); ?></p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="<?php echo e(route('clientes.show', $cliente->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                            <a href="<?php echo e(route('clientes.edit', $cliente->id)); ?>" class="btn btn-sm btn-warning">Editar</a>
                            <form action="<?php echo e(route('clientes.desativar', $cliente->id)); ?>" method="POST" style="display:inline-block;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja desativar este Cliente?');">
                                    Desativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($clientes->appends(request()->input())->links('pagination::bootstrap-5')); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">Nenhum cliente encontrado.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/index.blade.php ENDPATH**/ ?>