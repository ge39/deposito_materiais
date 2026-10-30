<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Cupom da Venda #<?php echo e(str_pad($venda->id, 6, '0', STR_PAD_LEFT)); ?>

    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #f1f3f5;
            color: #000;
            font-family: "Courier New", Courier, monospace;
        }

        body {
            padding: 24px 12px;
        }

        .cupom-acoes {
            width: 100%;
            max-width: 380px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .cupom-acoes button,
        .cupom-acoes a {
            border: 1px solid #343a40;
            border-radius: 4px;
            padding: 8px 14px;
            background: #fff;
            color: #212529;
            font-family: Arial, sans-serif;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }

        .cupom-acoes button:hover,
        .cupom-acoes a:hover {
            background: #e9ecef;
        }

        .cupom {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 14px 12px;
            background: #fff;
            border: 1px solid #343a40;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
            font-size: 12px;
            line-height: 1.35;
        }

        .texto-centro {
            text-align: center;
        }

        .texto-direita {
            text-align: right;
        }

        .texto-negrito {
            font-weight: 700;
        }

        .titulo-empresa {
            margin: 0 0 3px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .linha {
            margin: 0;
        }

        .separador {
            width: 100%;
            margin: 8px 0;
            border: 0;
            border-top: 1px dashed #000;
        }

        .secao-titulo {
            margin: 0;
            text-align: center;
            font-weight: 700;
        }

        .dados-venda p {
            margin: 1px 0;
            overflow-wrap: anywhere;
        }

        .cabecalho-itens {
            display: grid;
            grid-template-columns:
                minmax(0, 1.7fr)
                minmax(100px, 1fr)
                minmax(62px, .7fr);
            gap: 5px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #000;
            font-weight: 700;
        }

        .item-cupom {
            padding: 5px 0;
            border-bottom: 1px dotted #999;
        }

        .item-linha {
            display: grid;
            grid-template-columns:
                minmax(0, 1.7fr)
                minmax(100px, 1fr)
                minmax(62px, .7fr);
            gap: 5px;
            align-items: start;
        }

        .item-produto {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .item-descricao {
            display: block;
            font-weight: 700;
        }

        .item-lote {
            display: block;
            margin-top: 1px;
            font-size: 10px;
            color: #333;
        }

        .item-quantidade {
            text-align: center;
            white-space: nowrap;
        }

        .item-total {
            text-align: right;
            white-space: nowrap;
        }

        .titulo-pagamentos {
            margin: 0 0 4px;
            font-weight: 700;
        }

        .linha-valor {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin: 2px 0;
        }

        .linha-valor span:first-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .linha-valor span:last-child {
            flex-shrink: 0;
            text-align: right;
            white-space: nowrap;
        }

        .linha-dinheiro {
            font-weight: 700;
        }

        .linha-troco {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dotted #000;
            font-weight: 700;
        }

        .linha-desconto {
            font-weight: 700;
        }

        .linha-total-liquido {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 15px;
            font-weight: 700;
        }

        .rodape-cupom {
            margin: 0;
            text-align: center;
            font-weight: 700;
        }

        .rodape-secundario {
            margin: 3px 0 0;
            text-align: center;
            font-size: 10px;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm;
            }

            html,
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            body {
                padding: 0;
            }

            .cupom-acoes {
                display: none !important;
            }

            .cupom {
                width: 76mm;
                max-width: 76mm;
                margin: 0;
                padding: 2mm;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

<?php
    /*
    |--------------------------------------------------------------------------
    | TOTAIS HOMOLOGADOS PELO CONTROLLER
    |--------------------------------------------------------------------------
    */

    $totalLiquidoExibicao = round(
        (float) ($totalLiquidoVenda ?? $venda->total ?? 0),
        2
    );

    $descontoTotalExibicao = round(
        (float) ($descontoTotal ?? 0),
        2
    );

    $totalBrutoExibicao = round(
        (float) (
            $totalBrutoVenda
            ?? ($totalLiquidoExibicao + $descontoTotalExibicao)
        ),
        2
    );

    /*
    |--------------------------------------------------------------------------
    | PERCENTUAL DO DESCONTO
    |--------------------------------------------------------------------------
    */

    $percentualDesconto = $totalBrutoExibicao > 0
        ? round(
            ($descontoTotalExibicao / $totalBrutoExibicao) * 100,
            2
        )
        : 0;

    /*
    |--------------------------------------------------------------------------
    | PAGAMENTO EM DINHEIRO E TROCO
    |--------------------------------------------------------------------------
    */

    $trocoExibicao = round(
        (float) ($troco ?? 0),
        2
    );

    $pagoEmDinheiroExibicao = round(
        (float) ($pagoEmDinheiro ?? 0),
        2
    );
?>

<div class="cupom-acoes">
    <button
        type="button"
        onclick="window.print()"
    >
        Reimprimir
    </button>

    <a href="<?php echo e(route('pdv.index')); ?>">
        Voltar ao PDV
    </a>
</div>

<main class="cupom">

    

    <header>
        <h1 class="titulo-empresa">
            <?php echo e($empresa->nome ?? config('app.name', 'Depósito de Materiais')); ?>

        </h1>

        <?php if($empresa): ?>
            <?php if(!empty($empresa->cnpj)): ?>
                <p class="linha texto-centro">
                    CNPJ: <?php echo e($empresa->cnpj); ?>

                </p>
            <?php endif; ?>

            <?php if(!empty($empresa->endereco)): ?>
                <p class="linha texto-centro">
                    <?php echo e($empresa->endereco); ?>


                    <?php if(!empty($empresa->numero)): ?>
                        , <?php echo e($empresa->numero); ?>

                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if(!empty($empresa->cidade) || !empty($empresa->estado)): ?>
                <p class="linha texto-centro">
                    <?php echo e($empresa->cidade ?? ''); ?>


                    <?php if(!empty($empresa->cidade) && !empty($empresa->estado)): ?>
                        -
                    <?php endif; ?>

                    <?php echo e($empresa->estado ?? ''); ?>

                </p>
            <?php endif; ?>

            <?php if(!empty($empresa->telefone)): ?>
                <p class="linha texto-centro">
                    Tel: <?php echo e($empresa->telefone); ?>

                </p>
            <?php endif; ?>
        <?php endif; ?>
    </header>

    <hr class="separador">

    <p class="secao-titulo">
        CUPOM NÃO FISCAL
    </p>

    <hr class="separador">

    

    <section class="dados-venda">
        <p>
            <strong>CÓDIGO:</strong>
            <?php echo e(str_pad($venda->id, 6, '0', STR_PAD_LEFT)); ?>

        </p>

        <p>
            <strong>DATA:</strong>

            <?php echo e($venda->created_at
                    ? $venda->created_at->format('d/m/Y H:i:s')
                    : now()->format('d/m/Y H:i:s')); ?>

        </p>

        <p>
            <strong>TERMINAL:</strong>
            <?php echo e($terminalId ?? 0); ?>

        </p>

        <p>
            <strong>VENDEDOR:</strong>

            <?php echo e($venda->funcionario->nome
                ?? $venda->funcionario->name
                ?? auth()->user()?->nome
                ?? auth()->user()?->name
                ?? 'Balcão'); ?>

        </p>

        <p>
            <strong>CLIENTE:</strong>
            <?php echo e($venda->cliente->nome ?? 'VENDA BALCÃO'); ?>

        </p>
    </section>

    <hr class="separador">

    

    <section>
        <div class="cabecalho-itens">
            <span>PRODUTO</span>

            <span class="texto-centro">
                QTD × VL/UN
            </span>

            <span class="texto-direita">
                TOTAL
            </span>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $precoUnitario = round(
                    (float) ($item->preco_unitario ?? 0),
                    2
                );

                $quantidade = (float) (
                    $item->quantidade
                    ?? $item->quantidade_solicitada
                    ?? 0
                );

                $totalItem = round(
                    $quantidade * $precoUnitario,
                    2
                );

                $siglaMedida =
                    $item->produto->unidadeMedida->sigla
                    ?? $item->unidade
                    ?? 'UN';

                $numeroLote =
                    $item->lote->numero_lote
                    ?? "S/N";
            ?>

            <div class="item-cupom">
                <div class="item-linha">

                    <div class="item-produto">
                        <span class="item-descricao">
                            <?php echo e($item->produto->nome ?? 'Item não identificado'); ?>

                        </span>

                        <?php if($numeroLote): ?>
                            <span class="item-lote" style="width:170px;">
                                Lote: <?php echo e($numeroLote); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="item-quantidade">
                        <?php echo e(number_format(
                                $quantidade,
                                floor($quantidade) == $quantidade ? 0 : 2,
                                ',',
                                '.'
                            )); ?>

                        ×
                        <?php echo e(number_format($precoUnitario, 2, ',', '.')); ?>

                        <?php echo e(strtoupper($siglaMedida)); ?>

                    </div>

                    <div class="item-total">
                        <?php echo e(number_format($totalItem, 2, ',', '.')); ?>

                    </div>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="texto-centro">
                Nenhum item encontrado.
            </p>
        <?php endif; ?>
    </section>

    <hr class="separador">

    

    <section>
        <p class="titulo-pagamentos">
            FORMA(S) DE PAGAMENTO:
        </p>

        <?php $__empty_1 = true; $__currentLoopData = $venda->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $formaPagamentoOriginal = trim(
                    (string) (
                        $pagamento->forma_pagamento
                        ?? $pagamento->tipo
                        ?? ''
                    )
                );

                $formaPagamentoNormalizada = strtolower(
                    $formaPagamentoOriginal
                );

                $ehDinheiro =
                    $formaPagamentoNormalizada === 'dinheiro';

                $valorPagamento = round(
                    (float) ($pagamento->valor ?? 0),
                    2
                );
            ?>

            <?php if($ehDinheiro): ?>
                <div class="linha-valor linha-dinheiro">
                    <span>DINHEIRO RECEBIDO:</span>

                    <span>
                        R$
                        <?php echo e(number_format(
                                $pagoEmDinheiroExibicao > 0
                                    ? $pagoEmDinheiroExibicao
                                    : ($valorPagamento + $trocoExibicao),
                                2,
                                ',',
                                '.'
                            )); ?>

                    </span>
                </div>
            <?php else: ?>
                <div class="linha-valor">
                    <span>
                        <?php echo e(strtoupper(
                                $formaPagamentoOriginal
                                ?: 'NÃO ESPECIFICADO'
                            )); ?>

                    </span>

                    <span>
                        R$ <?php echo e(number_format($valorPagamento, 2, ',', '.')); ?>

                    </span>
                </div>
            <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if(!empty($venda->forma_pagamento)): ?>
                <div class="linha-valor">
                    <span>
                        <?php echo e(strtoupper($venda->forma_pagamento)); ?>

                    </span>

                    <span>
                        R$
                        <?php echo e(number_format(
                                $totalLiquidoExibicao,
                                2,
                                ',',
                                '.'
                            )); ?>

                    </span>
                </div>
            <?php else: ?>
                <div class="linha-valor">
                    <span>NÃO INFORMADO</span>
                    <span>R$ 0,00</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($trocoExibicao > 0): ?>
            <div class="linha-valor linha-troco">
                <span>TROCO:</span>

                <span>
                    R$ <?php echo e(number_format($trocoExibicao, 2, ',', '.')); ?>

                </span>
            </div>
        <?php endif; ?>
    </section>

    <hr class="separador">

    

    <section>
        <div class="linha-valor">
            <span>TOTAL BRUTO:</span>

            <span>
                R$ <?php echo e(number_format($totalBrutoExibicao, 2, ',', '.')); ?>

            </span>
        </div>

        <?php if($descontoTotalExibicao > 0.05): ?>
            <div class="linha-valor linha-desconto">
                <span>
                    DESCONTO
                    <?php if($percentualDesconto > 0): ?>
                        (<?php echo e(number_format(
                                $percentualDesconto,
                                2,
                                ',',
                                '.'
                            )); ?>%)
                    <?php endif; ?>
                    :
                </span>

                <span>
                    (-) R$
                    <?php echo e(number_format(
                            $descontoTotalExibicao,
                            2,
                            ',',
                            '.'
                        )); ?>

                </span>
            </div>
        <?php endif; ?>

        <div class="linha-valor linha-total-liquido">
            <span>TOTAL LÍQUIDO:</span>

            <span>
                R$ <?php echo e(number_format($totalLiquidoExibicao, 2, ',', '.')); ?>

            </span>
        </div>
    </section>

    <hr class="separador">

    

    <footer>
        <p class="rodape-cupom">
            OBRIGADO PELA PREFERÊNCIA!
        </p>

        <p class="rodape-secundario">
            Volte sempre.
        </p>
    </footer>

</main>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        window.focus();

        setTimeout(function () {
            window.print();
        }, 350);
    });
</script> -->

</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/vendas/cupom.blade.php ENDPATH**/ ?>