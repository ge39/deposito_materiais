

<?php $__env->startSection('content'); ?>
<div class="container mx-auto mt-4">
    <h2 class="mb-4">Devoluções</h2>

    <!-- Formulário de busca por código de venda -->
    <form action="<?php echo e(route('devolucoes.buscar')); ?>" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="codigo_venda" class="form-control" placeholder="Digite o código da venda" value="<?php echo e(request('codigo_venda')); ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <!-- Lista de vendas -->
    <div class="card">
        <div class="card-body">
            <?php if($vendas->isEmpty()): ?>
                <p>Nenhuma venda encontrada.</p>
            <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Cliente</th>
                            <th>Data da Venda</th>
                            <th>Valor Total</th>
                            <th>Quantidade Comprada</th>
                            <th>Quantidade Devolvida</th>
                            <th>Quantidade Disponível</th>
                            <th>Valor Extornado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $vendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($venda->venda_id); ?></td>
                            <td><?php echo e($venda->cliente_nome); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y')); ?></td>
                            <td>R$ <?php echo e(number_format($venda->valor_total, 2, ',', '.')); ?></td>
                            <td><?php echo e($venda->quantidade_comprada); ?></td>
                            <td><?php echo e($venda->quantidade_devolvida); ?></td>
                            <td><?php echo e($venda->quantidade_disponivel); ?></td>
                            <td>R$ <?php echo e(number_format($venda->valor_extornado, 2, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\index_grid.blade.php ENDPATH**/ ?>