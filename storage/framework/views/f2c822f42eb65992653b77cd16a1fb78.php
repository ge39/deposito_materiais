

<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-4">Relatório de Auditoria de Caixa</h3>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Caixa</th>
                <th>Auditor</th>
                <th>Data</th>
                <th>Total Sistema</th>
                <th>Total Físico</th>
                <th>Diferença</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <?php
                    $rowClass = match($auditoria->status) {
                        'concluida' => 'table-success',
                        'corrigida' => 'table-warning',
                        'inconsistente' => 'table-danger',
                        default => 'table-secondary'
                    };
                ?>

                <tr class="<?php echo e($rowClass); ?>">
                    <td><?php echo e($auditoria->codigo_auditoria); ?></td>
                    <td>#<?php echo e($auditoria->caixa_id); ?></td>
                    <td><?php echo e($auditoria->usuario->name ?? '-'); ?></td>
                    <td><?php echo e($auditoria->data_auditoria->format('d/m/Y H:i')); ?></td>

                    <td>R$ <?php echo e(number_format($auditoria->total_sistema,2,',','.')); ?></td>
                    <td>R$ <?php echo e(number_format($auditoria->total_fisico,2,',','.')); ?></td>

                    <td class="fw-bold">
                        <?php if($auditoria->diferenca != 0): ?>
                            <span class="text-danger">
                                R$ <?php echo e(number_format($auditoria->diferenca,2,',','.')); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-success">R$ 0,00</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="badge bg-dark text-uppercase">
                            <?php echo e($auditoria->status); ?>

                        </span>
                    </td>

                    <td>
                        <a href="<?php echo e(route('auditoria_caixa.show',$auditoria->id)); ?>"
                           class="btn btn-sm btn-primary">
                           Ver Relatório
                        </a>
                    </td>
                </tr>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($auditorias->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/auditoria_caixa/index.blade.php ENDPATH**/ ?>