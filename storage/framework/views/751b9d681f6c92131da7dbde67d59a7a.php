

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Promoções & Descontos</h4>
            <a href="<?php echo e(route('promocoes.create')); ?>" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Nova Promoção
            </a>
        </div>

        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if($promocoes->isEmpty()): ?>
                <p class="text-muted text-center mt-3">Nenhuma promoção cadastrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th class="text-center" style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $promocoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promocao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($promocao->id); ?></td>
                                    <td><?php echo e($promocao->produto->nome ?? '—'); ?></td>
                                    <td>
                                        <?php if($promocao->tipo_desconto === 'percentual'): ?>
                                            Percentual
                                        <?php else: ?>
                                            Valor Fixo
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($promocao->tipo_desconto === 'percentual'): ?>
                                            <?php echo e($promocao->valor_desconto); ?>%
                                        <?php else: ?>
                                            R$ <?php echo e(number_format($promocao->valor_desconto, 2, ',', '.')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::parse($promocao->data_inicio)->format('d/m/Y')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($promocao->data_fim)->format('d/m/Y')); ?></td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="<?php echo e(route('promocoes.show', $promocao->id)); ?>" class="btn btn-info btn-sm">
                                                Ver
                                            </a>
                                            <a href="<?php echo e(route('promocoes.edit', $promocao->id)); ?>" class="btn btn-warning btn-sm">
                                                Editar
                                            </a>
                                            <form action="<?php echo e(route('promocoes.destroy', $promocao->id)); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta promoção?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($promocoes->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/index.blade.php ENDPATH**/ ?>