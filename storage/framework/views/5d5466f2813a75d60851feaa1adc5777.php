<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Reposição</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h2 {
            margin-bottom: 5px;
        }

        .subtitulo {
            margin-bottom: 15px;
            color: #666;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
        }

        .totais {
            display: flex;
            justify-content: space-between;
        }

        .totais div {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: #fff;
        }

        .danger { background: #e74c3c; }
        .warning { background: #f39c12; }
        .success { background: #27ae60; }

        .data-box {
            padding: 3px 5px;
            border-radius: 4px;
            text-align: center;
        }

        .linha-par {
            background: #fafafa;
        }

    </style>
</head>
<body>
<div class="col-md-2">
    <label>Orientação PDF</label>
    <select name="orientacao" class="form-control">
        <option value="portrait" <?php echo e(request('orientacao') == 'portrait' ? 'selected' : ''); ?>>
            Retrato
        </option>
        <option value="landscape" <?php echo e(request('orientacao') == 'landscape' ? 'selected' : ''); ?>>
            Paisagem
        </option>
    </select>
</div>

<h2>📦 Relatório de Reposição de Estoque</h2>
<div class="subtitulo">
    Gerado em: <?php echo e(now()->format('d/m/Y H:i')); ?>

</div>


<div class="card totais">
    <div>
        <strong>Total de itens Pendentes:</strong><br>
        <?php echo e(number_format($totais->total_pendente ?? 0, 2, ',', '.')); ?>

    </div>

    <div>
        <strong>Valor Total - Orçamentos Pendentes:</strong><br>
        R$ <?php echo e(number_format($totais->valor_total ?? 0, 2, ',', '.')); ?>

    </div>
</div>


<div class="card">
    <strong>🔥 Produtos com maior necessidade de compra</strong>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Orçamentos</th>
                <th>Unidade</th>
                <th>Pendente</th>
                <th>Compra</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $resumo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($loop->even ? 'linha-par' : ''); ?>">
                    <td><?php echo e($r->nome); ?></td>
                    <td style="max-width: 180px; word-wrap: break-word;">
                        <?php
                            $codigos = explode(',', $r->codigos_orcamento ?? '');
                        ?>

                        <?php $__currentLoopData = $codigos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="
                                display: inline-block;
                                background: #3490dc;
                                color: #fff;
                                padding: 2px 6px;
                                margin: 2px;
                                border-radius: 3px;
                                font-size: 10px;
                            ">
                                <?php echo e(trim($codigo)); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td class="text-center"><?php echo e($r->unidade ?? '-'); ?></td>
                    <td class="text-right ">
                        <strong><?php echo e(number_format($r->total_pendente, 2, ',', '.')); ?></strong>
                    </td>
                    <td class="text-center">
                        <span class="badge danger">
                            <?php echo e(number_format($r->total_pendente, 2, ',', '.')); ?> <?php echo e($r->unidade); ?>

                        </span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>


<div class="card">
    <strong>📋 Detalhamento por Produto</strong>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Cód. Barras</th>
                <th>Un</th>
                <th>Total</th>
                <th>Qtd Atendida</th>
                <th>Pendente</th>
                <th>Entrega</th>
                <th>Necessário</th>
                <th>Valor</th>
            </tr>
        </thead>

        <tbody>
            <?php $__currentLoopData = $dados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $dataEntrega = $item->previsao_entrega
                        ? \Carbon\Carbon::parse($item->previsao_entrega)
                        : null;

                    $dias = $dataEntrega
                        ? now()->diffInDays($dataEntrega, false)
                        : null;

                    if ($dias === null) {
                        $cor = '#eeeeee';
                    } elseif ($dias <= 0) {
                        $cor = '#ff4d4d';
                    } elseif ($dias <= 3) {
                        $cor = '#ff9999';
                    } elseif ($dias <= 7) {
                        $cor = '#ffcc66';
                    } elseif ($dias <= 14) {
                        $cor = '#ffff99';
                    } else {
                        $cor = '#99ff99';
                    }
                ?>

                <tr class="<?php echo e($loop->even ? 'linha-par' : ''); ?>">

                    <td><?php echo e($item->produto_nome); ?></td>

                    <td><?php echo e($item->codigo_barras); ?></td>

                    <td class="text-center">
                        <?php echo e($item->unidade ?? '-'); ?>

                    </td>

                    <td class="text-right">
                        <?php echo e(number_format($item->total_quantidade, 2, ',', '.')); ?>

                    </td>

                    <td class="text-right">
                        <?php echo e(number_format($item->total_atendida, 2, ',', '.')); ?>

                    </td>

                    <td class="text-right">
                        <strong>
                            <?php echo e(number_format($item->total_pendente, 2, ',', '.')); ?>

                        </strong>
                    </td>

                    <td class="text-center">
                        <?php if($dataEntrega): ?>
                            <div class="data-box" style="background: <?php echo e($cor); ?>">
                                <?php echo e($dataEntrega->format('d/m/Y')); ?>

                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <?php if($item->necessidade_reposicao > 0): ?>
                            <span class="badge danger">
                                <?php echo e(number_format($item->necessidade_reposicao, 2, ',', '.')); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge success">OK</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-right">
                        R$ <?php echo e(number_format($item->valor_total, 2, ',', '.')); ?>

                    </td>

                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

</body>
</html><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/relatorios/reposicao_pdf.blade.php ENDPATH**/ ?>