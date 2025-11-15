

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h2 class="mb-3">Lista de Produtos</h2>

    <!-- Mensagens de sucesso -->
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <!-- Form de busca -->
    <form action="<?php echo e(route('produtos.search_grid')); ?>" method="GET" class="mb-3 d-flex">
        <input type="text" name="query" class="form-control me-2" placeholder="Buscar produto..." value="<?php echo e(request('query')); ?>">
        <button class="btn btn-primary" type="submit">Buscar</button>
        <div class="me-2" style="margin-left: 5px;">
            <a href="<?php echo e(route('produtos.index-grid')); ?>" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>
    </form>

    <div class="d-flex gap-1 align-items-center mb-2">
        <a href="<?php echo e(route('produtos.create')); ?>" class="btn btn-success btn-sm">Novo</a>
        <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-warning btn-sm">Visão em Cards</a>
    </div>

    <!-- Grid linear usando div -->
    <div class="border rounded overflow-hidden">
        <!-- Cabeçalho -->
        <div class="d-flex bg-light fw-bold border-bottom p-2">
            <div class="col-1" style="width:70px">Código</div>
            <div class="col-2" style="width:150px">Nome</div>
            <div class="col-1" style="width:70px">Estoque</div>
            <div class="col-1" style="width:80px">Preço</div>
            <div class="col-1" style="width:100px">Unidade</div>
            <div class="col-2" style="width:180px">Categoria</div>
            <div class="col-2" style="width:110px">Marca</div>
            <div class="col-2" style="width:110px">Fornecedor</div>
            <div class="col-1" style="width:150px">Ações</div>
        </div>

        <!-- Produtos -->
        <?php $__empty_1 = true; $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="d-flex align-items-center border-bottom p-2 
                        <?php echo e($loop->even ? 'bg-light' : 'bg-white'); ?> hover-row" style="font-size: 14px;">
                <div class="col-1" style="width:50px">000<?php echo e($produto->id); ?></div>
                <div class="col-2" style="width:180px"><?php echo e($produto->nome); ?></div>
                <div class="col-1" style="width:50px"><?php echo e($produto->quantidade_estoque); ?></div>
                <div class="col-1" style="width:100px">R$ <?php echo e(number_format($produto->precoAtual(), 2, ',', '.')); ?></div>
                <div class="col-2" style="width:100px"><?php echo e($produto->unidadeMedida->nome ?? '-'); ?></div>
                <div class="col-2" style="width:180px"><?php echo e($produto->categoria->nome ?? '-'); ?></div>
                <div class="col-2" style="width:100px"><?php echo e($produto->marca->nome ?? '-'); ?></div>
                <div class="col-2" style="width:100px"><?php echo e($produto->fornecedor->nome ?? '-'); ?></div>

                <div class="d-flex gap-1 align-items-center">
                    <a href="<?php echo e(route('produtos.show', $produto->id)); ?>" class="btn btn-info btn-sm">Ver</a>
                    <a href="<?php echo e(route('lotes.index', $produto->id)); ?>" class="btn btn-primary btn-sm">Lotes</a>

                    <?php if(optional(auth()->user())->nivel_acesso == 'admin' || optional(auth()->user())->nivel_acesso == 'gerente'): ?>
                        <a href="<?php echo e(route('produtos.edit', $produto->id)); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form action="<?php echo e(route('produtos.desativar', $produto->id)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Deseja realmente desativar este produto?')">Inativar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="d-flex justify-content-center p-3">
                Nenhum produto encontrado.
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginação -->
    <div class="mt-3">
        <?php echo e($produtos->links()); ?>

    </div>
</div>

<!-- Estilo hover opcional -->
<style>
    .hover-row:hover {
        background-color: #e9f5ff !important;
        transition: background-color 0.2s ease-in-out;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/produtos/index-grid.blade.php ENDPATH**/ ?>