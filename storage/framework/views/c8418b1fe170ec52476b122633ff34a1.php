

<?php $__env->startSection('content'); ?>

<?php
    $percentual = $auditoria->total_sistema > 0
        ? ($auditoria->diferenca / $auditoria->total_sistema) * 100
        : 0;

    $statusClass = match($auditoria->status) {
        'concluida' => 'bg-success',
        'corrigida' => 'bg-warning text-dark',
        'inconsistente' => 'bg-danger',
        default => 'bg-secondary'
    };
?>

<div class="container">


    
    <div class="mb-3 d-flex justify-content-end gap-2">
        <a href="<?php echo e(route('auditoria_caixa.exportar', $auditoria->id)); ?>" 
           class="btn btn-outline-primary" target="_blank">
           <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>

        <button class="btn btn-outline-secondary" onclick="window.print()">
           <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>

    
    <div class="card mb-4 shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Relatório de Auditoria</h4>
            <span class="badge <?php echo e($statusClass); ?> fs-6 text-uppercase">
                <?php echo e($auditoria->status); ?>

            </span>
        </div>

        <div class="card-body row">
            <div class="col-md-3">
                <strong>Código:</strong><br>
                <?php echo e($auditoria->codigo_auditoria); ?>

            </div>

            <div class="col-md-2">
                <strong>Caixa:</strong><br>
                #<?php echo e($auditoria->caixa_id); ?>

            </div>

            <div class="col-md-3">
                <strong>Auditor:</strong><br>
                <?php echo e($auditoria->usuario->name ?? '-'); ?>

            </div>

            <div class="col-md-4">
                <strong>Data:</strong><br>
                <?php echo e($auditoria->data_auditoria->format('d/m/Y H:i')); ?>

            </div>
        </div>
    </div>


    
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Total Vendas</h6>
                <h4 class="text-primary">
                    <?php
                        $totalVendas = $auditoria->caixa->vendas
                            ->flatMap->pagamentos
                            ->where('status', 'confirmado')
                            ->sum('valor');
                    ?>
                    R$ <?php echo e(number_format($totalVendas, 2, ',', '.')); ?>

                </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Troco Caixa</h6>
                    <h4 class="text-primary">
                        R$ <?php echo e(number_format($auditoria->caixa->fundo_troco,2,',','.')); ?>

                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center border-dark shadow-sm">
                <div class="card-body">
                    <h6>Total Físico Informado</h6>
                    <h4>
                        R$ <?php echo e(number_format($auditoria->total_fisico,2,',','.')); ?>

                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm <?php echo e($auditoria->diferenca != 0 ? 'border-danger' : 'border-success'); ?>">
                <div class="card-body">
                    <h6>Diferença</h6>
                    <h4 class="<?php echo e($auditoria->diferenca != 0 ? 'text-danger' : 'text-success'); ?>">
                        R$ <?php echo e(number_format($auditoria->diferenca,2,',','.')); ?>

                    </h4>
                    <small>
                        <?php echo e(number_format($percentual,2,',','.')); ?>%
                    </small>
                </div>
            </div>
        </div>

    </div>


    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            Comparativo Financeiro
        </div>
        <div class="card-body">
            <canvas id="graficoAuditoria" height="100"></canvas>
        </div>
    </div>


    
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            Detalhamento por Forma de Pagamento
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Forma</th>
                        <th>Total Sistema</th>
                        <th>Total Fisico Informado</th>
                        <th>Diferença</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $auditoria->detalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalhe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr class="<?php echo e($detalhe->status == 'divergente' ? 'table-danger' : 'table-success'); ?>">
                            <td class="text-uppercase">
                                <?php echo e($detalhe->forma_pagamento); ?>

                            </td>
                            <td>
                                R$ <?php echo e(number_format($detalhe->total_sistema,2,',','.')); ?>

                            </td>
                            <td>
                                R$ <?php echo e(number_format($detalhe->total_fisico,2,',','.')); ?>

                            </td>
                            <td class="fw-bold">
                                R$ <?php echo e(number_format($detalhe->diferenca,2,',','.')); ?>

                            </td>
                            <td>
                                <span class="badge bg-dark">
                                    <?php echo e($detalhe->status); ?>

                                </span>
                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nenhum detalhamento encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            Lançamentos Manuais
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Observação</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $lancamentosManuais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr class="<?php echo e($mov->tipo == 'entrada_manual' ? 'table-success' : 'table-danger'); ?>">
                            <td class="text-uppercase">
                                <?php echo e($mov->tipo); ?>

                            </td>
                            <td class="fw-bold">
                                R$ <?php echo e(number_format($mov->valor,2,',','.')); ?>

                            </td>
                            <td>
                                <?php echo e($mov->observacao ?? '-'); ?>

                            </td>
                            <td>
                                <?php echo e(\Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i')); ?>

                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Nenhum lançamento manual registrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark fw-bold">
            Correções Realizadas pela Auditoria  - R$ <?php echo e(number_format($auditoria->total_sistema,2,',','.')); ?>

        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Forma</th>
                        <th>Valor</th>
                        <th>Auditor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movimentacoesAuditoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr class="<?php echo e($mov->valor >= 0 ? 'table-success' : 'table-danger'); ?>">
                            <td class="text-uppercase">
                                <?php echo e($mov->forma_pagamento ?? '-'); ?>

                            </td>

                            <td class="fw-bold">
                                R$ <?php echo e(number_format($mov->valor,2,',','.')); ?>

                            </td>

                            <td>
                                <?php echo e($mov->usuario->name ?? '-'); ?>

                            </td>

                            <td>
                                <?php echo e(\Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i')); ?>

                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Nenhuma correção foi registrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                     <th>Fundo  - Troco Caixa</th>
                    <td class="fw-bold">
                       
                         R$ <?php echo e(number_format($auditoria->caixa->fundo_troco,2,',','.')); ?>

                    </td>
                </tbody>
            </table>
        </div>
    </div>


    
    <div class="mt-4">
        <a href="<?php echo e(route('auditoria_caixa.index')); ?>" class="btn btn-outline-dark">
            ← Voltar para Relatórios
        </a>
    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById('graficoAuditoria');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Sistema', 'Total Físico'],
            datasets: [{
                label: 'Valores (R$)',
                data: [
                    <?php echo e($auditoria->total_sistema); ?>,
                    <?php echo e($auditoria->total_fisico); ?>

                ],
                backgroundColor: [
                    'rgba(13,110,253,0.6)',
                    'rgba(25,135,84,0.6)'
                ],
                borderColor: [
                    'rgba(13,110,253,1)',
                    'rgba(25,135,84,1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/auditoria_caixa/show.blade.php ENDPATH**/ ?>