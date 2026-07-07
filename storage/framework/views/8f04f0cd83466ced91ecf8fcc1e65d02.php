<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Romaneio <?php echo e($romaneio->codigo_romaneio ?? $romaneio->id); ?></title>

    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
        }

        .no-print {
            margin-bottom: 12px;
        }

        .pagina-romaneio {
            min-height: 270mm;
            page-break-after: always;
            position: relative;
        }

        .pagina-romaneio:last-child {
            page-break-after: auto;
        }

        .cabecalho {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }

        .titulo {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitulo {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .linha {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
        }

        .box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }

        .box-title {
            font-weight: bold;
            background: #eee;
            border-bottom: 1px solid #000;
            margin: -8px -8px 8px -8px;
            padding: 5px 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #eee;
            font-weight: bold;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .assinaturas {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 35px;
        }

        .assinatura {
            width: 33%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }

        .rodape {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #000;
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

<div class="no-print">
    <button onclick="window.print()">Imprimir Romaneio</button>
</div>

<?php
    $entrega = $romaneio->entrega;
    $orcamento = $entrega->orcamento ?? null;
    $venda = $entrega->venda ?? null;
    $cliente = $entrega->cliente ?? $orcamento->cliente ?? null;

    $codigoRomaneio = $romaneio->codigo_romaneio ?? 'ROM-' . $romaneio->id;

    $vias = [
        'VIA 1 - EXPEDIÇÃO',
        'VIA 2 - MOTORISTA / CLIENTE',
    ];
?>

<?php $__currentLoopData = $vias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indexVia => $via): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <section class="pagina-romaneio">

        <div class="cabecalho">
            <div class="titulo">ROMANEIO DE ENTREGA</div>
            <div class="subtitulo"><?php echo e($via); ?></div>

            <div class="linha">
                <div><strong>Romaneio:</strong> <?php echo e($codigoRomaneio); ?></div>
                <div><strong>Página:</strong> <?php echo e($indexVia + 1); ?> de <?php echo e(count($vias)); ?></div>
            </div>

            <div class="linha">
                <div><strong>Emissão:</strong> <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y H:i')); ?></div>
                <div><strong>Status:</strong> <?php echo e($romaneio->status ?? 'Gerado'); ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Documentos Vinculados</div>

            <div class="linha">
                <div><strong>Venda:</strong> <?php echo e($entrega && $entrega->venda_id ? 'VEN-' . $entrega->venda_id : '—'); ?></div>
                <div><strong>Entrega:</strong> <?php echo e($entrega ? 'ENT-' . $entrega->id : '—'); ?></div>
                <div><strong>Orçamento:</strong> <?php echo e($orcamento ? 'ORÇ-' . $orcamento->id : ($entrega->orcamento_id ?? '—')); ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Cliente e Destino</div>

            <div><strong>Cliente:</strong> <?php echo e($cliente->nome ?? 'Cliente não informado'); ?></div>
            <div><strong>Endereço:</strong> <?php echo e($entrega->endereco_entrega ?? $entrega->endereco_entrega_concatenado ?? 'Endereço não informado'); ?></div>
            <div><strong>Período:</strong> <?php echo e($entrega->periodo_entrega ?? 'Não informado'); ?></div>
            <div><strong>Observação:</strong> <?php echo e($entrega->observacao_entrega ?? $romaneio->observacao ?? '—'); ?></div>
        </div>

        <div class="box">
            <div class="box-title">Veículo e Responsáveis</div>

            <div class="linha">
                <div><strong>Motorista:</strong> <?php echo e($romaneio->motorista->name ?? $romaneio->motorista->nome ?? 'Não definido'); ?></div>
                <div><strong>Veículo:</strong> <?php echo e($romaneio->veiculo->placa ?? 'Não definido'); ?></div>
            </div>

            <div class="linha">
                <div><strong>Início Separação:</strong> <?php echo e(optional($romaneio->data_inicio_separacao)->format('d/m/Y H:i') ?? '—'); ?></div>
                <div><strong>Saída:</strong> <?php echo e(optional($romaneio->data_saida)->format('d/m/Y H:i') ?? '—'); ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Itens para Separação / Carregamento</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th style="width: 38%;">Produto</th>
                        <th style="width: 18%;">Localização</th>
                        <th style="width: 13%;" class="text-end">Prevista</th>
                        <th style="width: 13%;" class="text-end">Carregada</th>
                        <th style="width: 13%;" class="text-center">Conferência</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $entregaItem = $item->entregaItem;

                            $produto = $entregaItem->produto
                                ?? $entregaItem->vendaItem->produto
                                ?? $entregaItem->itemOrcamento->produto
                                ?? null;

                            $localizacao = $produto->localizacao_estoque ?? '—';
                        ?>

                        <tr>
                            <td class="text-center"><?php echo e($i + 1); ?></td>

                            <td>
                                <strong><?php echo e($produto->nome ?? 'Produto não identificado'); ?></strong><br>
                                <small>Cód.: <?php echo e($produto->id ?? '—'); ?></small>
                            </td>

                            <td><?php echo e($localizacao); ?></td>

                            <td class="text-end">
                                <?php echo e(number_format((float) ($item->quantidade_prevista ?? 0), 2, ',', '.')); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format((float) ($item->quantidade_carregada ?? 0), 2, ',', '.')); ?>

                            </td>

                            <td class="text-center">[ &nbsp;&nbsp; ]</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                Nenhum item encontrado para este romaneio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="assinaturas">
            <div class="assinatura">Expedição</div>
            <div class="assinatura">Motorista</div>
            <div class="assinatura">Cliente</div>
        </div>

        <div class="rodape">
            <span><?php echo e($codigoRomaneio); ?></span>
            <span><?php echo e($via); ?></span>
            <span>Página <?php echo e($indexVia + 1); ?> de <?php echo e(count($vias)); ?></span>
        </div>

    </section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/imprimir.blade.php ENDPATH**/ ?>