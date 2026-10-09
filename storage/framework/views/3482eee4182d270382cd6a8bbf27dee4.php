

<?php $__env->startSection('content'); ?>
<div class="container">

    <h3>Extrato - <?php echo e($cliente->nome); ?></h3>

    <div class="mb-3">
        <strong>Saldo Atual:</strong> 
        R$ <?php echo e(number_format($saldo, 2, ',', '.')); ?>

    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Origem</th>
                <th>Valor</th>
                <th>Saldo Após</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($mov->created_at->format('d/m/Y H:i')); ?></td>
                <td><?php echo e(ucfirst($mov->tipo)); ?></td>
                <td><?php echo e(ucfirst($mov->origem)); ?></td>
                <td>R$ <?php echo e(number_format($mov->valor, 2, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($mov->saldo_apos, 2, ',', '.')); ?></td>
                <td><?php echo e($mov->descricao); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($movimentacoes->links()); ?>


</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\clientes\conta_corrente\extrato.blade.php ENDPATH**/ ?>