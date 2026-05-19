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
                <p class="mb-0"><strong>DATA:</strong> <?php echo e($venda->created_at ? $venda->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s')); ?></p>
                <p class="mb-0"><strong>VENDEDOR:</strong> <?php echo e(auth()->user()->name ?? auth()->user()->nome ?? $venda->funcionario->name ?? 'Balcão'); ?></p>
                <p class="mb-0"><strong>CLIENTE:</strong> <?php echo e($venda->cliente->nome ?? 'VENDA BALCAO'); ?></p>
            </div>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div style="font-family: monospace; font-size: 12px; margin-top: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #000; font-weight: bold; padding-bottom: 3px;">
                    <span style="flex: 2; text-align: left;">PRODUTO</span>
                    <span style="flex: 1.5; text-align: center;">QTD x UN</span>
                    <span style="flex: 1; text-align: right;">TOTAL</span>
                </div>

                
                <?php $totalLiquidoCalculado = 0; ?>
                <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $precoUnitario = (float) $item->preco_unitario;
                        $quantidade    = (float) $item->quantidade;
                        $descontoItem  = (float) ($item->desconto ?? 0);
                        $totalItem     = ($quantidade * $precoUnitario) - $descontoItem;
                        
                        $totalLiquidoCalculado += $totalItem;

                        // Busca dinamicamente a unidade de medida das tabelas relacionadas
                        $siglaMedida = $item->produto->unidadeMedida->sigla ?? $item->unidade ?? 'UN';
                        
                        // Busca dinamicamente o número do lote relacionado ao item
                        $numeroLote = $item->lote->numero_lote ?? 'N/A';
                    ?>
                    <div style="margin-top: 4px; padding-bottom: 4px;font-size:14px; border-bottom: 1px dotted #eee; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            
                            
                            <div style="flex: 2; display: flex; flex-direction: column; text-align: left;">
                                <span style="font-weight: bold; line-height: 1.2;">
                                    <?php echo e($item->produto->nome ?? 'Item não identificado'); ?>

                                </span>
                                
                                <span style="font-size: 11px; color: #555; margin-top: 1px;">
                                    Cod: <?php echo e($item->produto_id); ?> | Lote: <?php echo e($numeroLote); ?>

                                </span>
                            </div>

                            
                            <div style="flex: 1.5; text-align: center; white-space: nowrap;">
                                <span><?php echo e((int)$quantidade); ?> x </span>
                                <span style="font-weight: bold;">
                                    <?php echo e(strtoupper($siglaMedida)); ?>

                                </span>
                            </div>

                            
                            <div style="flex: 1;text-align: right; font-weight: bold;">
                                <span>R$ <?php echo e(number_format($totalItem, 2, ',', '.')); ?></span>
                            </div>
                        </div>

                        
                        <?php if($descontoItem > 0): ?>
                            <div style="text-align: right; font-size: 10px; color: red; margin-top: 2px;">
                                <span>(-) Desc. Item: R$ <?php echo e(number_format($descontoItem, 2, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="text-start mb-2">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                <div id="formas-pagamento-render">
                    
                    <?php if(count($venda->pagamentos) > 0): ?>
                        <?php $__currentLoopData = $venda->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="d-flex justify-content-between">
                                <span class="text-uppercase"><?php echo e($pagamento->forma_pagamento ?? $pagamento->tipo); ?></span>
                                <span>R$ <?php echo e(number_format($pagamento->valor, 2, ',', '.')); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif(!empty($venda->forma_pagamento)): ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-uppercase"><?php echo e($venda->forma_pagamento); ?></span>
                            <span>R$ <?php echo e(number_format($totalLiquidoCalculado, 2, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px;font-family: monospace;">
                <?php
                    $descontoTotal = $venda->itens->sum('desconto');
                ?>

                <?php if($descontoTotal > 0): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span>DESCONTO TOTAL ITEMS:</span>
                        <span>R$ <?php echo e(number_format($descontoTotal, 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; font-weight: bold; ">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ <?php echo e(number_format($totalLiquidoCalculado, 2, ',', '.')); ?></span>
                </div>

                <hr style="border-top: 1px dashed #000; margin: 5px 0;">

                
                <div id="bloco-calculo-troco-real"></div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 text-muted fst-italic">Obrigado pela preferência, volte sempre!</p>

            <button class="btn btn-primary btn-sm mt-3" onclick="window.print()">Reimprimir</button>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Captura o objeto de pagamento preenchido no caixa antes de fechar o modal
        let dadosOrigem = window.opener?.pagamentoDataFinal || window.parent?.pagamentoDataFinal;
        let totalLiquido = parseFloat("<?php echo e($totalLiquidoCalculado); ?>") || 0;

        if (dadosOrigem) {
            let htmlFormas = '';
            let valorDinheiro = parseFloat(dadosOrigem.dinheiro) || 0;
            let valorCredito = parseFloat(dadosOrigem.cartao_credito) || 0;
            let valorDebito = parseFloat(dadosOrigem.cartao_debito) || 0;
            let valorPix = parseFloat(dadosOrigem.pix) || 0;
            let valorCarteira = parseFloat(dadosOrigem.carteira) || 0;

            // Gera as linhas correspondentes a cada pagamento utilizado
            if (valorDinheiro > 0)  htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">DINHEIRO</span><span>R$ ${valorDinheiro.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorCredito > 0)   htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTÃO CRÉDITO</span><span>R$ ${valorCredito.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorDebito > 0)    htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTÃO DÉBITO</span><span>R$ ${valorDebito.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorPix > 0)       htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">PIX</span><span>R$ ${valorPix.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorCarteira > 0)  htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTEIRA</span><span>R$ ${valorCarteira.toFixed(2).replace('.', ',')}</span></div>`;

            if (htmlFormas !== '') {
                document.getElementById('formas-pagamento-render').innerHTML = htmlFormas;
            }

            // Se houve dinheiro, calcula e injeta o troco e o valor pago em tempo de execução
            if (valorDinheiro > 0) {
                let somaOutros = valorCredito + valorDebito + valorPix + valorCarteira;
                let tetoNecessarioDinheiro = totalLiquido - somaOutros;
                if (tetoNecessarioDinheiro < 0) tetoNecessarioDinheiro = 0;

                let trocoCalculado = valorDinheiro - tetoNecessarioDinheiro;
                if (trocoCalculado < 0) trocoCalculado = 0;

                let htmlTroco = `
                    <div style="display: flex; justify-content: space-between;">
                        <span>PAGO EM DINHEIRO:</span>
                        <span>R$ ${valorDinheiro.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;

                if (trocoCalculado > 0) {
                    htmlTroco += `
                        <div style="display: flex; justify-content: space-between; font-weight: bold;">
                            <span>TROCO:</span>
                            <span>R$ ${trocoCalculado.toFixed(2).replace('.', ',')}</span>
                        </div>
                    `;
                }
                document.getElementById('bloco-calculo-troco-real').innerHTML = htmlTroco;
            }
        }

        // Executa o foco e o comando de impressão do navegador de forma silenciosa
        window.focus();
        window.print();
    });
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