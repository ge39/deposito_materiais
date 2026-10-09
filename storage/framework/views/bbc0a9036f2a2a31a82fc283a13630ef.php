<!DOCTYPE html>
<html>
<head>
    <title>Recibo da Venda <?php echo e($venda->id); ?></title>
    <style>
        body { font-family: Arial; font-size: 14px; padding: 20px; }
        .linha { border-bottom: 1px dashed #aaa; margin: 5px 0; }
        .titulo { text-align: center; font-size: 18px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="titulo">RECIBO DA VENDA Nº <?php echo e($venda->id); ?></div>

<div class="linha"></div>
<?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <p>
        <?php echo e($item->produto->nome); ?> <br>
        <?php echo e($item->quantidade); ?> x R$ <?php echo e(number_format($item->preco,2,',','.')); ?>

        <strong class="float-end">
            R$ <?php echo e(number_format($item->total,2,',','.')); ?>

        </strong>
    </p>
    <div class="linha"></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h3>Total: R$ <?php echo e(number_format($venda->total,2,',','.')); ?></h3>

<p>Forma de pagamento: <strong><?php echo e($venda->forma_pagamento); ?></strong></p>

<br>
<p>Obrigado pela preferência!</p>

<script>
    window.print();
</script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\pdv\recibo.blade.php ENDPATH**/ ?>