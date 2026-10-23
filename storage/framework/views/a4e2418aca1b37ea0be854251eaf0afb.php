<h2>Histórico do Orçamento #<?php echo e($orcamentoId); ?></h2>

<?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="margin-bottom:10px; padding:10px; border:1px solid #ccc;">
        <strong><?php echo e(strtoupper($mov->tipo)); ?></strong><br>

        <?php echo e($mov->descricao); ?><br>

        <?php if($mov->quantidade): ?>
            Quantidade: <?php echo e($mov->quantidade); ?><br>
        <?php endif; ?>

        <small>
            <?php echo e($mov->created_at->format('d/m/Y H:i')); ?>

        </small>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\movimentacoes\show.blade.php ENDPATH**/ ?>