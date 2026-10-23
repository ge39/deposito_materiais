

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-3">
         Clientes
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Gerencie seus clientes</h5>
                    <p class="card-text">Clientes</p>
                    <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Nossa Empresa -->
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Nossa Empresa</h5>
                    <p class="card-text">Gerencie seus clientes</p>
                    <a href="<?php echo e(route('empresa.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Funcionários -->
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Funcionários</h5>
                    <p class="card-text">Gerencie os funcionários</p>
                    <a href="<?php echo e(route('funcionarios.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Categorias -->
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Categorias</h5>
                    <p class="card-text">Gerencie categorias de produtos</p>
                    <a href="<?php echo e(route('categorias.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Fornecedores -->
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Fornecedores</h5>
                    <p class="card-text">Gerencie fornecedores</p>
                    <a href="<?php echo e(route('fornecedores.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Produtos -->
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Produtos</h5>
                    <p class="card-text">Gerencie seus produtos</p>
                    <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Vendas -->
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
                    <p class="card-text">Gerencie vendas realizadas</p>
                    <a href="<?php echo e(route('vendas.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Pedidos de Compras -->
        <div class="col-md-3">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Pedidos de Compras</h5>
                    <p class="card-text">Gerencie pedidos aos fornecedores</p>
                    <a href="<?php echo e(route('pedidos_compras.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Pós-Venda -->
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Pós-Venda</h5>
                    <p class="card-text">Gerencie devoluções, trocas e atendimentos</p>
                    <a href="<?php echo e(route('pos_vendas.index')); ?>" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\dashboard.blade.php ENDPATH**/ ?>