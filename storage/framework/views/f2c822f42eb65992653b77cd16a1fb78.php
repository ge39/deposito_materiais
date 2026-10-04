

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Painel de Auditoria de Caixas Encerrados</h3>
            <small class="text-muted">Acompanhamento e homologação fiscal de divergências de caixas</small>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark small text-uppercase">
                        <tr>
                            <th class="ps-3">Código Auditoria</th>
                            <th>Caixa ID</th>
                            <th>Auditor / Fiscal</th>
                            <th>Data Fechamento</th>
                            <th class="text-end">Total Sistema</th>
                            <th class="text-end">Total Físico</th>
                            <th class="text-end">Divergência</th>
                            <th class="text-center">Status Fiscal</th>
                            <th class="text-center pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                // Define a cor de fundo da linha com base no status cadastrado no banco
                                $rowClass = match($auditoria->status) {
                                    'concluida'     => 'table-success-subtle table-success', // Verde suave para caixas perfeitos
                                    'corrigida'     => 'table-warning-subtle table-warning', // Amarelo para caixas ajustados pelo fiscal
                                    'inconsistente' => 'table-danger-subtle table-danger',   // Vermelho para quebras de caixa ativas
                                    default         => 'table-secondary'
                                };
                            ?>

                            <tr class="<?php echo e($rowClass); ?>">
                                <td class="ps-3 fw-semibold"><?php echo e($auditoria->codigo_auditoria); ?></td>
                                <td class="fw-bold">#<?php echo e($auditoria->caixa_id); ?></td>
                                
                                
                                <td><?php echo e($auditoria->auditor_nome ?? $auditoria->usuario->name ?? 'Operador ID #' . $auditoria->user_id); ?></td>
                                
                                
                                <td>
                                    <?php echo e($auditoria->data_auditoria instanceof \Carbon\Carbon 
                                        ? $auditoria->data_auditoria->format('d/m/Y H:i') 
                                        : \Carbon\Carbon::parse($auditoria->data_auditoria)->format('d/m/Y H:i')); ?>

                                </td>

                                <td class="text-end text-muted">R$ <?php echo e(number_format($auditoria->total_sistema, 2, ',', '.')); ?></td>
                                <td class="text-end fw-semibold">R$ <?php echo e(number_format($auditoria->total_fisico, 2, ',', '.')); ?></td>

                                <td class="text-end fw-bold">
                                    <?php if((float)$auditoria->diferenca != 0): ?>
                                        <span class="<?php echo e($auditoria->diferenca < 0 ? 'text-danger' : 'text-primary'); ?>">
                                            R$ <?php echo e(number_format($auditoria->diferenca, 2, ',', '.')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-success">R$ 0,00</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-dark text-uppercase font-monospace px-2 py-1">
                                        <?php echo e($auditoria->status); ?>

                                    </span>
                                </td>

                                <td class="text-center pe-3">
                                   
                                    <a href="/auditoria-caixa/<?php echo e($auditoria->id); ?>" class="btn btn-sm btn-primary fw-bold px-3">
                                        🔍 Ver Relatório
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">Nenhum registro de auditoria arquivado no sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="mt-3 d-flex justify-content-end">
        <?php echo e($auditorias->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/auditoria_caixa/index.blade.php ENDPATH**/ ?>