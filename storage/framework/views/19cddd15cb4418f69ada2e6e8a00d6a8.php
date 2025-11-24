

<?php $__env->startSection('content'); ?>
<div class="container" style="border:1px solid #ddd; padding:15px; border-radius:5px; background-color:#f9f9f9;">
    <h1>Produtos Inativos</h1>
    <a href="<?php echo e(route('produtos.create')); ?>" class="btn btn-primary mb-3">Novo Produto</a>
    <!-- <a href="<?php echo e(route('produtos.inativos')); ?>" class="btn btn-secondary mb-3">Produtos Inativos</a> -->

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($produtos->count()): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Fornecedor</th>
                    <th>Marca</th>
                    <th>Unidade</th>
                    <th>Estoque</th>
                    <th>Preço Venda</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($produto->nome); ?></td>
                    <td><?php echo e($produto->categoria->nome ?? ''); ?></td>
                    <td><?php echo e($produto->fornecedor->nome ?? ''); ?></td>
                    <td><?php echo e($produto->marca->nome ?? ''); ?></td>
                    <td><?php echo e($produto->unidade->nome ?? ''); ?></td>
                    <td><?php echo e($produto->quantidade_estoque); ?></td>
                    <td>R$ <?php echo e(number_format($produto->preco_venda, 2, ',', '.')); ?></td>
                    <td>
                        <!-- <a href="<?php echo e(route('produtos.edit', $produto->id)); ?>" class="btn btn-sm btn-warning">Editar</a> -->
                        <form action="<?php echo e(route('produtos.reativar', $produto->id)); ?>" method="POST" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Deseja reativar este produto?')">Ativar</button>
                        </form>


                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($produtos->links()); ?>

    <?php else: ?>
        <div class="alert alert-info">Nenhum produto ativo encontrado.</div>
    <?php endif; ?>
    <div class="row mb-3">
            <div class="col-md-12 d-flex align-items-center gap-3">
                <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-secondary"style="width: 6.3rem">Voltar</a>
            </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/produtos/inativos.blade.php ENDPATH**/ ?>