<?php $__env->startSection('content'); ?>

<div class="container mt-4">
    <div class="card border-dark mx-auto" style="max-width: 380px;">
        <div class="card-body text-center" style="font-family: monospace; font-size: 14px; line-height: 1.4; color: #000;">
            
            
            <h5 class="fw-bold mb-1 fs-12px"><?php echo e($empresa->nome ?? config('app.name')); ?></h5>
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
                <p class="mb-0"><strong>DATA:</strong> <?php echo e($venda->created_at ? $venda->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s')); ?></p>
                <p class="mb-0"><strong>TERMINAL:</strong> <?php echo e($terminalId); ?></p>
                <p class="mb-0"><strong>VENDEDOR:</strong> <?php echo e(auth()->user()->name ?? auth()->user()->nome ?? $venda->funcionario->name ?? 'Balcão'); ?></p>
                <p class="mb-0"><strong>CLIENTE:</strong> <?php echo e($venda->cliente->nome ?? 'VENDA BALCAO'); ?></p>
            </div>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div style="font-family: monospace; font-size: 12px; margin-top: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #000; font-weight:normal; padding-bottom: 3px;">
                    <span style="flex: 2; text-align: left;">PRODUTO</span>
                    <span style="flex: 1.5; text-align: center;">QTD x VL x UN</span>
                    <span style="flex: 1; text-align: right;">TOTAL</span>
                </div>

                
                <?php 
                    $totalBrutoAcumulado = 0; 
                ?>
                <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $precoUnitario = (float) $item->preco_unitario;
                        $quantidade    = (float) ($item->quantidade ?? $item->quantidade_solicitada ?? 0);
                        $totalItem     = $quantidade * $precoUnitario;
                        
                        $totalBrutoAcumulado += $totalItem;

                        $siglaMedida = $item->produto->unidadeMedida->sigla ?? $item->unidade ?? 'UN';
                        $numeroLote  = $item->lote->numero_lote ?? 'N/A';
                    ?>
                    <div style="margin-top: 4px; padding-bottom: 4px; font-size:12px; border-bottom: 1px dotted #eee; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            
                            
                            <div style="flex: 2; display: flex; flex-direction: column; text-align: left; margin-bottom: -10px;">
                                <span style="font-weight:normal; font-size: 11px; line-height: 1.2;">
                                    <?php echo e($item->produto->nome ?? 'Item não identificado'); ?>

                                </span>
                                <span style="font-size: 11px; color: #555; margin-top: -1px;">
                                    Lote: <?php echo e($numeroLote); ?>

                                </span>
                            </div>
                            
                            
                            <div style="flex: 1.5; text-align: center; white-space: nowrap;">
                                <span><?php echo e((int)$quantidade); ?> x </span>
                                <span style="font-weight:normal;">
                                    <?php echo e($precoUnitario > 0 ? number_format($precoUnitario, 2, ',', '.') : 'Grátis'); ?>

                                    <?php echo e(strtoupper($siglaMedida)); ?>

                                </span>
                            </div>

                            
                            <div style="flex: 1; text-align: right; font-weight:normal;">
                                <span><?php echo e(number_format($totalItem, 2, ',', '.')); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="text-start mb-2" style="font-family: monospace; font-size: 13px;">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                <div id="formas-pagamento-render">
                    <?php if(count($venda->pagamentos) > 0): ?>
                        <?php $__currentLoopData = $venda->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <span class="text-uppercase"><?php echo e($pagamento->forma_pagamento ?? 'NÃO ESPECIFICADO'); ?></span>
                                <span>
                                    <?php if(strtolower($pagamento->forma_pagamento ?? '') === 'dinheiro'): ?>
                                        R$ <?php echo e(number_format($pagoEmDinheiro, 2, ',', '.')); ?>

                                    <?php else: ?>
                                        R$ <?php echo e(number_format($pagamento->valor, 2, ',', '.')); ?>

                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif(!empty($venda->forma_pagamento)): ?>
                        <div style="display: flex; justify-content: space-between;">
                            <span class="text-uppercase"><?php echo e($venda->forma_pagamento); ?></span>
                            <span>R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>

                    
                    <?php if(isset($troco) && (float)$troco > 0): ?>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 6px; border-top: 1px dotted #000; padding-top: 4px;">
                            <span>TROCO:</span>
                            <span>R$ <?php echo e(number_format($troco, 2, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px; font-family: monospace;">
                <?php
                    $totalLiquidoVenda = (float) $venda->total;
                    $totalBrutoExibicao = $totalBrutoAcumulado;
                    $valorDescontoReal = (float) ($descontoTotal ?? 0);

                    // Se a venda veio de um orçamento, recompõe os descontos específicos caso necessário
                    $orcamentoId = $venda->orcamento_id ?? null;
                    if ($orcamentoId) {
                        $valorDescontoReal = (float) \DB::table('item_orcamentos')
                            ->where('orcamento_id', $orcamentoId)
                            ->sum('valor_desconto');
                    }
                ?>
                
                <div style="display: flex; justify-content: space-between;">
                    <span>TOTAL BRUTO:</span>
                    <span>R$ <?php echo e(number_format($totalBrutoExibicao, 2, ',', '.')); ?></span>
                </div>

                <?php if($valorDescontoReal > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: #555;">
                        <span>DESCONTOS:</span>
                        <span>- R$ <?php echo e(number_format($valorDescontoReal, 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 4px; border-top: 1px solid #000; padding-top: 4px;">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ <?php echo e(number_format($totalLiquidoVenda, 2, ',', '.')); ?></span>
                </div>
            </div>

            <hr class="my-3" style="border-top: 1px dashed #000;">
            <p class="mb-0 text-center fw-bold">OBRIGADO PELA PREFERÊNCIA!</p>

        </div>
    </div>
</div>

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