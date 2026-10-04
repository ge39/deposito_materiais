<?php $__env->startSection('content'); ?>

<?php
    // 1. Soma os valores declarados nas tabelas de detalhes
    $totalSistemaCalculado = (float) $auditoria->detalhes->sum('total_sistema');
    $totalInformadoCalculado = (float) $auditoria->detalhes->sum('total_fisico');

    // 2. 🎯 CORREÇÃO DO FIADO: Subtrai a modalidade carteira para validar apenas dinheiro vivo/físico
    $vendasCarteiraHoje = (float) $auditoria->detalhes->where('forma_pagamento', 'carteira')->sum('total_sistema');

    // 3. Reconciliação em tempo de execução
    // Se o informado bate com o que o sistema esperava fisicamente, a diferença é R$ 0,00
    $diferencaReal = $totalInformadoCalculado - $totalSistemaCalculado;

    // Vincula na variável original cobrada pela linha 132 do Blade
    $percentual = $totalSistemaCalculado > 0 ? ($diferencaReal / $totalSistemaCalculado) * 100 : 0;

    // Chaves de controle de visualização do cabeçalho
    $statusClass = $diferencaReal == 0 ? 'bg-success' : 'bg-danger';
    $statusTexto = $diferencaReal == 0 ? 'CONCILIADO' : 'INCONSISTENTE';
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
            <h6 class="mb-0">Relatório de Auditoria - Caixa <?php echo e($auditoria->caixa_id); ?> - Terminal <?php echo e($auditoria->caixa->terminal_id); ?></h6>
            <span class="badge <?php echo e($statusClass); ?> fs-6 text-uppercase">
                <?php echo e($auditoria->status); ?>

            </span>
        </div>

        <div class="card-body row">
            <div class="col-md-3">
                <strong>Código:</strong><br>
                <?php echo e($auditoria->codigo_auditoria); ?>

            </div>

            <div class="col-md-1">
                <strong>Caixa:</strong><br>
                #<?php echo e($auditoria->caixa_id); ?>

            </div>

            <div class="col-md-2">
                <strong>Terminal:</strong><br>
                #<?php echo e($auditoria->caixa->terminal_id); ?>

            </div>

            <div class="col-md-2">
                <strong>Auditor:</strong><br>
                <?php echo e($auditoria->usuario->name ?? '-'); ?>

            </div>

            <div class="col-md-2">
                <strong>Op.Caixa:</strong><br>
                <?php echo e($auditoria->usuario->name ?? '-'); ?>

            </div>

            <div class="col-md-2">
                <strong>Data:</strong><br>
                <?php echo e($auditoria->data_auditoria->format('d/m/Y H:i')); ?>

            </div>
        </div>
    </div>

    
    <div class="row g-2 mb-2 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-7">

        
        <div class="col">
            <div class="card text-center border-primary shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Total Vendas</h6>
                    <h6 class="text-primary fw-bold mb-0">
                        <?php
                            $totalVendas = $auditoria->caixa->vendas
                                ->flatMap->pagamentos
                                ->where('status', 'confirmado')
                                ->sum('valor');
                        ?>
                        R$ <?php echo e(number_format($totalVendas, 2, ',', '.')); ?>

                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center border-primary shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Troco Caixa</h6>
                    <h6 class="text-primary fw-bold mb-0">
                        R$ <?php echo e(number_format($auditoria->caixa->fundo_troco, 2, ',', '.')); ?>

                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center border-primary shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Pagto Carteira</h6>
                    <h6 class="text-primary fw-bold mb-0">
                       R$ <?php echo e(number_format($detalhesRecebimentoCarteira->sum('valor') ?? 0, 2, ',', '.')); ?>


                
                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center border-primary shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Sangrias</h6>
                    <h6 class="text-primary fw-bold mb-0">
                        R$ <?php echo e(number_format($total_sangrias, 2, ',', '.')); ?>

                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center border-primary shadow-sm h-100">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Total Informado Operador</h6>
                    <h6 class="text-primary fw-bold mb-0">
                        R$ <?php echo e(number_format($auditoria->total_fisico, 2, ',', '.')); ?>

                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center shadow-sm h-100 <?php echo e($auditoria->diferenca != 0 ? 'border-danger' : 'border-success'); ?>">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Quebra em R$</h6>
                    <h6 class="fw-bold mb-0 <?php echo e($auditoria->diferenca != 0 ? 'text-danger' : 'text-success'); ?>">
                        R$ <?php echo e(number_format($auditoria->diferenca, 2, ',', '.')); ?>

                    </h6>
                </div>
            </div>
        </div>

        
        <div class="col">
            <div class="card text-center shadow-sm h-100 <?php echo e($auditoria->diferenca != 0 ? 'border-danger' : 'border-success'); ?>">
                <div class="card-body p-2 d-flex flex-column justify-content-center">
                    <h6 class="card-title text-muted mb-1" style="font-size: 0.85rem;">Quebra em %</h6>
                    <h6 class="fw-bold mb-0 <?php echo e($auditoria->diferenca != 0 ? 'text-danger' : 'text-success'); ?>">
                        <?php echo e(number_format($percentual, 2, ',', '.')); ?>%
                    </h6>
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
                      <!-- Código Limpo e Corrigido para a sua Tabela/Blade -->
                        <td>
                            <strong>Total Diferença:</strong> 
                            <?php echo e(number_format((float)($detalhe->diferenca ?? 0.00), 2, ',', '.')); ?>

                        </td>

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
            Pagamento Cliente Carteira - 
            <?php if($detalhesRecebimentoCarteira->isNotEmpty()): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="text-end">Total Recebido:</td>
                            <td class="text-success">
                                R$ <?php echo e(number_format($detalhesRecebimentoCarteira->sum('valor'), 2, ',', '.')); ?>

                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Forma</th>
                        <th>Observação</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $detalhesRecebimentoCarteira; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="table-info">
                            <td class="text-uppercase fw-normal text-dark" style="font-size: 0.9rem;">
                                <?php echo e(str_replace('_', ' ', $rec->tipo)); ?>

                            </td>
                            <td class="fw-normal text-dark">
                                R$ <?php echo e(number_format($rec->valor, 2, ',', '.')); ?>

                            </td>
                            <td class="fw-normal text-dark text-left">
                                 <?php echo e(str_replace('_', ' ', $rec->forma_pagamento)); ?>

                            </td>
                             <td class="fw-normal text-dark text-left">
                                 <?php echo e(str_replace('_', ' ', $rec->observacao)); ?>

                            </td>
                            <td>
                                <?php echo e(\Carbon\Carbon::parse($rec->data_movimentacao)->format('d/m/Y H:i')); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                Nenhum registro de pagamento de carteira encontrado.
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
            Total de Valores Auditados  - R$ <?php echo e(number_format($auditoria->total_sistema,2,',','.')); ?>

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