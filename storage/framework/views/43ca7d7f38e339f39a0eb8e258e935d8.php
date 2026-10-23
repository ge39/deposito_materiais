<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Histórico de Backups</h3>
            <p class="text-muted mb-0">
                Geração, recuperação e integridade das cópias.
            </p>
        </div>
        <a href="<?php echo e(route('backups.index')); ?>"
           class="btn btn-outline-secondary">
            Backup Manual
        </a>
    </div>

    <div class="alert alert-info small">
        O resultado da geração local é independente
        do resultado da segunda cópia.
        Registros antigos podem não possuir identificação de origem.
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Ação</th>
                            <th>Origem</th>
                            <th>Status</th>
                            <th>Arquivo ZIP</th>
                            <th>Horário programado</th>
                            <th>Cópia principal</th>
                            <th>Segunda cópia</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($log->id); ?></td>

                                <td class="text-nowrap">
                                    <?php echo e($log->created_at?->format('d/m/Y H:i:s')); ?>

                                </td>

                                <td class="small">
                                    <?php echo e($log->acao ?? '-'); ?>

                                </td>

                                <td>
                                    <?php if($log->historico_origem === 'Automático'): ?>
                                        <span class="badge bg-primary">
                                            Automático
                                        </span>
                                    <?php elseif($log->historico_origem === 'Recuperação'): ?>
                                        <span class="badge bg-warning text-dark">
                                            Recuperação
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            Não identificada
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                        $status = strtolower(
                                            (string) ($log->status ?? '')
                                        );
                                    ?>

                                    <?php if($status === 'sucesso'): ?>
                                        <span class="badge bg-success">
                                            Sucesso
                                        </span>
                                    <?php elseif($status === 'pendente'): ?>
                                        <span class="badge bg-warning text-dark">
                                            Pendente
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <?php echo e($log->status ?? 'Erro'); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="small">
                                    <?php echo e($log->historico_zip); ?>

                                </td>

                                <td class="small text-nowrap">
                                    <?php $__empty_2 = true; $__currentLoopData = $log->historico_horarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $horario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <div><?php echo e($horario); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo e($log->historico_principal); ?>

                                </td>

                                <td>
                                    <?php if($log->historico_secundaria === 'Validada'): ?>
                                        <span class="badge bg-success">
                                            Validada
                                        </span>
                                    <?php elseif($log->historico_secundaria === 'Pendente'): ?>
                                        <span class="badge bg-warning text-dark">
                                            Pendente
                                        </span>
                                    <?php elseif($log->historico_secundaria === 'Divergente'): ?>
                                        <span class="badge bg-danger">
                                            Divergente
                                        </span>
                                    <?php else: ?>
                                        <span class="small text-muted">
                                            <?php echo e($log->historico_secundaria); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="small">
                                    <?php echo e($log->mensagem ?? '-'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10"
                                    class="text-center text-muted py-4">
                                    Nenhum registro localizado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($logs->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\backups-admin\historico.blade.php ENDPATH**/ ?>