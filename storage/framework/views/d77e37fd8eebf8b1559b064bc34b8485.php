

<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Relatório de Caixa
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Caixa:</strong> #<?php echo e($caixa->id); ?></div>
                <div class="col-md-3"><strong>Terminal:</strong> <?php echo e($caixa->terminal_id); ?></div>
                <div class="col-md-3"><strong>Abertura:</strong> <?php echo e($caixa->data_abertura); ?></div>
                <div class="col-md-3"><strong>Fechamento:</strong> <?php echo e($caixa->data_fechamento); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Resumo Financeiro
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <td>Total Vendas</td>
                    <td class="text-end">R$ <?php echo e(number_format($totais_por_tipo['venda'] ?? 0, 2, ',', '.')); ?></td>
                </tr>
                <tr>
                    <td>Entradas Manuais</td>
                    <td class="text-end">R$ <?php echo e(number_format($totais_por_tipo['entrada_manual'] ?? 0, 2, ',', '.')); ?></td>
                </tr>
                <tr>
                    <td>Saídas Manuais</td>
                    <td class="text-end">R$ <?php echo e(number_format($totais_por_tipo['saida_manual'] ?? 0, 2, ',', '.')); ?></td>
                </tr>
                <tr class="table-secondary fw-bold">
                    <td>Saldo Final</td>
                    <td class="text-end">R$ <?php echo e(number_format($saldo_sistema, 2, ',', '.')); ?></td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Pagamentos por Forma
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Forma</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $pagamentos_por_forma; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e(ucfirst(str_replace('_', ' ', $pagamento->forma_pagamento))); ?></td>
                            <td class="text-end">
                                R$ <?php echo e(number_format($pagamento->total, 2, ',', '.')); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header fw-bold">
            Movimentações do Caixa
        </div>
        <div class="card-body">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Observação</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e(\Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i')); ?></td>
                            <td><?php echo e(ucfirst(str_replace('_',' ', $mov->tipo))); ?></td>
                            <td><?php echo e($mov->observacao); ?></td>
                            <td class="text-end">
                                R$ <?php echo e(number_format($mov->valor, 2, ',', '.')); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\caixa\relatorio.blade.php ENDPATH**/ ?>