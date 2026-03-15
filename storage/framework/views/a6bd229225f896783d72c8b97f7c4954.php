<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<title>Cupom</title>

<style>

body{
    font-family: monospace;
    width:300px;
    margin:0;
    font-size:12px;
}

.cupom{
    padding-left:10px;
    padding-right:10px;
    margin: 20px;
}

.center{
    text-align:center;
}

.hr{
    border-top:1px dashed #000;
    margin:6px 0;
}

.row{
    display:flex;
    justify-content:space-between;
}

.item{
    margin-top:4px;
}

.total{
    font-size:16px;
    font-weight:bold;
}

.qrcode{
    margin-top:10px;
}

</style>
</head>

    <body onload="window.print()">

        <!-- EMPRESA -->

        <div class="center">

        <strong><?php echo e($empresa->nome); ?></strong><br>

        CNPJ: <?php echo e($empresa->cnpj); ?><br>

        <?php if($empresa->inscricao_estadual): ?>
        IE: <?php echo e($empresa->inscricao_estadual); ?><br>
        <?php endif; ?>

        <?php echo e($empresa->endereco); ?>, <?php echo e($empresa->numero); ?><br>

        <?php echo e($empresa->bairro); ?><br>

        <?php echo e($empresa->cidade); ?> - <?php echo e($empresa->estado); ?><br>

        <?php if($empresa->telefone): ?>
        Tel: <?php echo e($empresa->telefone); ?><br>
        <?php endif; ?>

        </div>

        <div class="hr"></div>

        <!-- DADOS DA VENDA -->

        <div>Venda: <?php echo e($venda->id); ?></div>
        <div>Data: <?php echo e($venda->created_at->format('d/m/Y H:i')); ?></div>
        <div>Cliente: <?php echo e($venda->cliente->nome ?? 'CONSUMIDOR'); ?></div>
        <div>Operador: <?php echo e($venda->funcionario->nome ?? 'PDV'); ?></div>
        <div>Caixa: <?php echo e($venda->caixa_id); ?></div>

        <div class="hr"></div>

        <!-- ITENS -->

        <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <div class="item">

        <div>
        <?php echo e($item->produto->nome); ?>

        </div>

        <div class="row">

        <div>
        <?php echo e($item->quantidade); ?> x <?php echo e(number_format($item->preco_unitario,2,',','.')); ?>

        </div>

        <div>
        <?php echo e(number_format($item->quantidade * $item->preco_unitario,2,',','.')); ?>

        </div>

        </div>

        </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="hr"></div>

        <!-- TOTAL -->

        <div class="row total">
        <div>TOTAL</div>
        <div>R$ <?php echo e(number_format($venda->total,2,',','.')); ?></div>
        </div>

        <div class="hr"></div>

        <!-- PAGAMENTOS -->

        <?php
        $valorDinheiro = 0;
        $temPix = false;
        ?>

        <?php $__currentLoopData = $venda->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php if($pag->valor > 0): ?>

        <div class="row">
        <div><?php echo e(strtoupper($pag->forma_pagamento)); ?></div>
        <div>R$ <?php echo e(number_format($pag->valor,2,',','.')); ?></div>
        </div>

        <?php if($pag->forma_pagamento == 'dinheiro'): ?>
        <?php $valorDinheiro = $pag->valor; ?>
        <?php endif; ?>

        <?php if($pag->forma_pagamento == 'pix'): ?>
        <?php $temPix = true; ?>
        <?php endif; ?>

        <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php
        $troco = $valorDinheiro - $venda->total;
        ?>

        <?php if($troco > 0): ?>

        <div class="hr"></div>

        <div class="row">
        <div>TROCO</div>
        <div>R$ <?php echo e(number_format($troco,2,',','.')); ?></div>
        </div>

        <?php endif; ?>

        <!-- QR CODE PIX -->

        <?php if($temPix): ?>

        <!-- <div class="center qrcode">

            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=PIX-<?php echo e($venda->id); ?>">

            <br>

            Pague com PIX

        </div> -->

        <?php endif; ?>

        <div class="hr"></div>

        <div class="center">

        OBRIGADO PELA PREFERÊNCIA

        <br><br>

        *** CUPOM NÃO FISCAL ***

        </div>

    </body>
    
</html><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/vendas/cupom.blade.php ENDPATH**/ ?>