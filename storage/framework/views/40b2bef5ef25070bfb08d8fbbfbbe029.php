

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card border-dark mx-auto" style="max-width: 380px;">
        <div class="card-body text-center" style="font-family: monospace; font-size: 14px; line-height: 1.4; color: #000;">
            
            
            <h5 class="fw-bold mb-1"><?php echo e($empresa->nome ?? config('app.name')); ?></h5>
            <?php if($empresa): ?>
                <p class="mb-0">CNPJ: <?php echo e($empresa->cnpj); ?></p>
                <p class="mb-0"><?php echo e($empresa->endereco); ?>, <?php echo e($empresa->numero); ?></p>
                <p class="mb-0"><?php echo e($empresa->cidade); ?> - <?php echo e($empresa->estado); ?></p>
                <p class="mb-0">Tel: <?php echo e($empresa->telefone); ?></p>
            <?php endif; ?>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 fw-bold">CUPOM NÃO FISCAL</p>
            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="text-start mb-2">
                <p class="mb-0"><strong>CÓDIGO:</strong> <?php echo e(str_pad($venda->id, 6, '0', STR_PAD_LEFT)); ?></p>
                <p class="mb-0"><strong>DATA:</strong> <?php echo e($venda->created_at->format('d/m/Y H:i:s')); ?></p>
                <p class="mb-0"><strong>VENDEDOR:</strong> <?php echo e($venda->funcionario_id ?? 'Balcão'); ?></p>
                <p class="mb-0"><strong>CLIENTE:</strong> <?php echo e($venda->cliente->nome ?? 'VENDA BALCAO'); ?></p>
            </div>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div style="font-family: monospace; font-size: 12px; margin-top: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #000; font-weight: bold; padding-bottom: 3px;">
                    <span style="flex: 2; text-align: left;">PRODUTO</span>
                    <span style="flex: 1.5; text-align: center;">QTD x UN</span>
                    <span style="flex: 1; text-align: right;">TOTAL</span>
                </div>

                
                <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="margin-top: 4px; padding-bottom: 4px;font-size:14px; border-bottom: 1px dotted #eee; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            
                            
                            <div style="flex: 2; display: flex; flex-direction: column; text-align: left;">
                                <span style="font-weight: bold; line-height: 1.2;">
                                    <?php echo e($item->produto->nome ?? 'Item não identificado'); ?>

                                </span>
                                <span style="font-size: 11px; color: #555; margin-top: 1px;">
                                    Cod: <?php echo e($item->produto_id); ?>

                                </span>
                            </div>

                            
                            <div style="flex: 1.5; text-align: center; white-space: nowrap;">
                                <span><?php echo e((int)$item->quantidade); ?> x </span>
                                <span style="font-weight: bold;">
                                    <?php echo e(strtoupper($item->unidade ?? $item->produto->unidade ?? 'UN')); ?>

                                </span>
                            </div>

                            
                            <div style="flex: 1;text-align: right; font-weight: bold;">
                                <span>R$ <?php echo e(number_format(($item->quantidade * $item->preco_unitario) - ($item->desconto ?? 0), 2, ',', '.')); ?></span>
                            </div>
                        </div>

                        
                        <?php if(($item->desconto ?? 0) > 0): ?>
                            <div style="text-align: right; font-size: 10px; color: red; margin-top: 2px;">
                                <span>(-) Desc. Item: R$ <?php echo e(number_format($item->desconto, 2, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="text-start mb-2">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                <?php $__currentLoopData = $venda->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-uppercase"><?php echo e($pagamento->forma_pagamento ?? $pagamento->tipo); ?></span>
                        <span>R$ <?php echo e(number_format($pagamento->valor, 2, ',', '.')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px;font-family: monospace;">
                <?php
                    $descontoTotal = $venda->itens->sum('desconto');
                    $pagoEmDinheiro = $venda->pagamentos->whereIn('forma_pagamento', ['dinheiro', 'DINHEIRO', 'Dinheiro'])->sum('valor');
                    $totalPago = $venda->pagamentos->sum('valor');
                    $troco = $totalPago > $venda->total ? ($totalPago - $venda->total) : 0;
                ?>

                <?php if($descontoTotal > 0): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span>DESCONTO TOTAL ITEMS:</span>
                        <span>R$ <?php echo e(number_format($descontoTotal, 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; font-weight: bold; ">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?></span>
                </div>

                <hr style="border-top: 1px dashed #000; margin: 5px 0;">

                <?php if($pagoEmDinheiro > 0): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span>PAGO EM DINHEIRO:</span>
                        <span>R$ <?php echo e(number_format($pagoEmDinheiro, 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>

                <?php if($troco > 0): ?>
                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                        <span>TROCO:</span>
                        <span>R$ <?php echo e(number_format($troco, 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 text-muted fst-italic">Obrigado pela preferência, volte sempre!</p>

            
            <button class="btn btn-primary btn-sm mt-3" onclick="window.print()">Reimprimir</button>
        </div>
    </div>
</div>


<script>
    // window.onload = function() {
    //     window.focus();
    //     window.print();
    //     window.onafterprint = function() {
    //         window.close();
    //     };
    // };
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        box-shadow: none !important;
    }
    .btn {
        display: none !important;
    }
    @page {
        margin: 0;
    }
    body {
        margin: 0.2cm;
        background-color: #fff;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/vendas/cupom.blade.php ENDPATH**/ ?>