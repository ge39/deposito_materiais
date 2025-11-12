

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    <p>Bem-vindo ao sistema! Use o menu para navegar entre clientes, fornecedores, produtos, vendas, pós-venda, etc.</p>

    <div class="row mt-4">
        
        <div class="col-md-3">
            <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-primary w-100">Clientes</a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo e(route('fornecedores.index')); ?>" class="btn btn-primary w-100">Fornecedores</a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-primary w-100">Produtos</a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo e(route('vendas.index')); ?>" class="btn btn-primary w-100">Vendas</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/dashboard/index.blade.php ENDPATH**/ ?>