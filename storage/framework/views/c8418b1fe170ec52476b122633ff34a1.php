

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

            <div class="col-md-2">
                <strong>Auditor:</strong><br>
                <?php echo e($auditoria->usuario->name ?? '-'); ?>

            </div>

            <div class="col-md-2">
                <strong>Op.Caixa:</strong><br>
                <?php echo e($auditoria->usuario->name ?? '-'); ?>

            </div>

            <div class="col-md-3">
                <strong>Data:</strong><br>
                <?php echo e($auditoria->data_auditoria->format('d/m/Y H:i')); ?>

            </div>
        </div>
    </div>


    
    <div class="row mb-4">

        <div class="col-md-2">
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

        <div class="col-md-2">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Troco Caixa</h6>
                    <h4 class="text-primary">
                        R$ <?php echo e(number_format($auditoria->caixa->fundo_troco,2,',','.')); ?>

                    </h4>
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Sangrias</h6>
                    <h4 class="text-primary">
                        R$ <?php echo e(number_format($total_sangrias,2,',','.')); ?>

                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-dark shadow-sm">
                <div class="card-body">
                    <h6>Total Informado</h6>
                    <h4 class="text-primary">
                        R$ <?php echo e(number_format($auditoria->total_fisico,2,',','.')); ?>

                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card text-center shadow-sm <?php echo e($auditoria->diferenca != 0 ? 'border-danger' : 'border-success'); ?>">
                <div class="card-body">
                    <h6>Quebra em R$</h6>
                    <h4 class="<?php echo e($auditoria->diferenca != 0 ? 'text-danger' : 'text-success'); ?>">
                        R$ <?php echo e(number_format($auditoria->diferenca,2,',','.')); ?>

                    </h4>
                   
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center shadow-sm <?php echo e($auditoria->diferenca != 0 ? 'border-danger' : 'border-success'); ?>">
                <div class="card-body">
                    <h6>Quebra em %</h6>
                    
                    <h4 class="<?php echo e($auditoria->diferenca != 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e(number_format($percentual,2,',','.')); ?>%
                    </h4>
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
        <div class="card-header bg-primary text-white">
            Detalhamento Auditoria - Total das Entradas Manuais do Caixa <?php echo e($auditoria->caixa->id); ?>

        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Forma</th>
                        <th>Total Sistema</th>
                        <th >Total Informado Operador</th>
                        <th >Ajustes</th>
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
                                <!-- R$ <?php echo e(number_format( + $detalhe->diferenca,2,',','.')); ?> -->
                                <?php
                                    $valor = $detalhe->diferenca;
                                ?>

                                <?php if($valor < 0): ?>
                                    <span class="text-success fw-bold">
                                        + R$ <?php echo e(number_format(abs($valor), 2, ',', '.')); ?>

                                    </span>
                                <?php elseif($valor > 0): ?>
                                    <span class="text-danger fw-bold">
                                        - R$ <?php echo e(number_format(abs($valor), 2, ',', '.')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fw-bold">
                                        R$ 0,00
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $status = strtolower($detalhe->status);
                                ?>

                                <?php if($status === 'correto'): ?>
                                    <span class="badge bg-success">
                                        <?php echo e($detalhe->status); ?>

                                    </span>
                                <?php elseif($status === 'divergente'): ?>
                                    <span class="badge bg-danger">
                                        <?php echo e($detalhe->status); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <?php echo e($detalhe->status); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                           
                        </tr>
                       
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                      <div><tr>Total:  <?php echo e(number_format($detalhe->diferenca,2,',','.')); ?></tr></div>
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
        <div class="card-header bg-primary text-white fw-normal">
            Lançamentos Manuais - Retiradas do Caixa
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
                            <td>
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
        <div class="card-header bg-primary text-light ">
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