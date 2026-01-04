 <!-- Ajuste para seu layout principal -->

<?php $__env->startSection('title', 'Fechamento de Caixa'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4">Caixas Abertos / Inconsistentes</h1>

    <?php if($caixas->isEmpty()): ?>
        <div class="alert alert-info">Não há caixas abertos ou inconsistentes no momento.</div>
    <?php else: ?>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID (Caixa)</th>
                    <th>Operador</th>
                    <th>Terminal ID</th>
                    <th>Abertura</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $caixas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caixa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($caixa->id); ?></td>
                    <td><?php echo e($caixa->usuario->name); ?></td>
                    <td><?php echo e($caixa->terminal_id ?? 'N/A'); ?></td>
                    <td><?php echo e($caixa->data_abertura->format('d/m/Y H:i')); ?></td>
                    <td><?php echo e(ucfirst($caixa->status)); ?></td>
                    <td>
                        <a href="<?php echo e(route('fechamento.auditar', $caixa->id)); ?>" class="btn btn-primary btn-sm">
                            Auditar / Fechar
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/listaCaixas.blade.php ENDPATH**/ ?>