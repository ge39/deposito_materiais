<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Auditoria - <?php echo e($auditoria->codigo_auditoria); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .bg-success { background-color: #d4edda; }
        .bg-danger { background-color: #f8d7da; }
        .bg-warning { background-color: #fff3cd; }
        .border { border: 1px solid #999; }
        h4, h6 { margin: 2px 0; }
        .badge { padding: 2px 5px; border-radius: 4px; color: #fff; font-size: 10px; }
        .bg-secondary { background-color: #6c757d; }
        .bg-dark { background-color: #343a40; }
        .bg-info { background-color: #17a2b8; }
        .bg-primary { background-color: #0d6efd; }
    </style>
</head>
<body>

    
    <h2>Relatório de Auditoria</h2>
    <p>
        <strong>Código:</strong> <?php echo e($auditoria->codigo_auditoria); ?><br>
        <strong>Caixa:</strong> #<?php echo e($auditoria->caixa_id); ?><br>
        <strong>Auditor:</strong> <?php echo e($auditoria->usuario->name ?? '-'); ?><br>
        <strong>Data:</strong> <?php echo e($auditoria->data_auditoria->format('d/m/Y H:i')); ?><br>
        <strong>Status:</strong> <?php echo e(strtoupper($auditoria->status)); ?>

    </p>

    
    <h4>Resumo Financeiro</h4>
    <?php
        $totalVendas = $auditoria->caixa->vendas
            ->flatMap->pagamentos
            ->where('status', 'confirmado')
            ->sum('valor');

        $percentual = $totalVendas > 0
            ? ($auditoria->diferenca / $totalVendas) * 100
            : 0;
    ?>

    <table>
        <tr>
            <th>Total Vendas</th>
            <th>Troco Caixa</th>
            <th>Total Físico Informado</th>
            <th>Diferença</th>
        </tr>
        <tr>
            <td>R$ <?php echo e(number_format($totalVendas, 2, ',', '.')); ?></td>
            <td>R$ <?php echo e(number_format($auditoria->caixa->fundo_troco,2,',','.')); ?></td>
            <td>R$ <?php echo e(number_format($auditoria->total_fisico,2,',','.')); ?></td>
            <td>
                R$ <?php echo e(number_format($auditoria->diferenca,2,',','.')); ?> <br>
                (<?php echo e(number_format($percentual,2,',','.')); ?>%)
            </td>
        </tr>
    </table>

    
    <h4>Detalhamento por Forma de Pagamento</h4>
    <table>
        <thead>
            <tr>
                <th>Forma</th>
                <th>Total Sistema</th>
                <th>Total Físico Informado</th>
                <th>Diferença</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $auditoria->detalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalhe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="<?php echo e($detalhe->status == 'divergente' ? 'bg-danger' : 'bg-success'); ?>">
                <td><?php echo e(strtoupper($detalhe->forma_pagamento)); ?></td>
                <td>R$ <?php echo e(number_format($detalhe->total_sistema,2,',','.')); ?></td>
                <td>R$ <?php echo e(number_format($detalhe->total_fisico,2,',','.')); ?></td>
                <td class="fw-bold">R$ <?php echo e(number_format($detalhe->diferenca,2,',','.')); ?></td>
                <td><?php echo e($detalhe->status); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center">Nenhum detalhamento encontrado.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    
    <h4>Lançamentos Manuais</h4>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Observação</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $lancamentosManuais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="<?php echo e($mov->tipo == 'entrada_manual' ? 'bg-success' : 'bg-danger'); ?>">
                <td><?php echo e(strtoupper($mov->tipo)); ?></td>
                <td>R$ <?php echo e(number_format($mov->valor,2,',','.')); ?></td>
                <td><?php echo e($mov->observacao ?? '-'); ?></td>
                <td><?php echo e($mov->data_movimentacao->format('d/m/Y H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="4" class="text-center">Nenhum lançamento manual registrado.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    
    <h4>Correções Realizadas pela Auditoria</h4>
    <table>
        <thead>
            <tr>
                <th>Forma</th>
                <th>Valor</th>
                <th>Auditor</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $movimentacoesAuditoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if($mov->valor > 0): ?>
            <tr class="bg-success">
                <td><?php echo e(strtoupper($mov->forma_pagamento ?? '-')); ?></td>
                <td>R$ <?php echo e(number_format($mov->valor,2,',','.')); ?></td>
                <td><?php echo e($mov->usuario->name ?? '-'); ?></td>
                <td><?php echo e($mov->data_movimentacao->format('d/m/Y H:i')); ?></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="4" class="text-center">Nenhuma correção foi registrada.</td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Fundo - Troco Caixa</th>
                <td colspan="3">R$ <?php echo e(number_format($auditoria->caixa->fundo_troco,2,',','.')); ?></td>
            </tr>
        </tbody>
    </table>

</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/auditoria_caixa/pdf.blade.php ENDPATH**/ ?>