

<?php $__env->startSection('content'); ?>
<div class="container">

    <h3 class="mb-4">Venda Nº <?php echo e($venda->id); ?></h3>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-body">

            <form action="<?php echo e(route('pdv.adicionarItem', $venda->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="row g-2">

                    <div class="col-md-8">
                        <input type="text" name="produto"
                               class="form-control"
                               placeholder="Nome, código ou código de barras">
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="quantidade"
                               min="1" value="1"
                               class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Adicionar</button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Produto</th>
                <th width="70">Qtd</th>
                <th width="120">Preço</th>
                <th width="120">Total</th>
                <th width="60">Remover</th>
            </tr>
        </thead>

        <tbody>

            <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->produto->nome); ?></td>
                    <td><?php echo e($item->quantidade); ?></td>
                    <td>R$ <?php echo e(number_format($item->preco, 2, ',', '.')); ?></td>
                    <td>R$ <?php echo e(number_format($item->total, 2, ',', '.')); ?></td>
                    <td>
                        <form method="POST"
                              action="<?php echo e(route('pdv.removerItem', $item->id)); ?>"
                              onsubmit="return confirm('Remover item?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm w-100">X</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </tbody>
    </table>

    
    <div class="alert alert-primary text-end fs-4">
        Total: <strong>R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?></strong>
    </div>

    <div class="d-flex gap-2">

        <a href="<?php echo e(route('pdv.finalizar', $venda->id)); ?>"
           class="btn btn-success w-100">
            Finalizar venda
        </a>

        <form method="POST" action="<?php echo e(route('pdv.cancelar', $venda->id)); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn btn-danger w-100"
                onclick="return confirm('Cancelar venda?')">
                Cancelar
            </button>
        </form>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pdv\itens.blade.php ENDPATH**/ ?>