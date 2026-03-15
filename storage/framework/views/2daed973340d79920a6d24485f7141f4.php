

<?php $__env->startSection('content'); ?>
<div class="container mt-5">

    
    <?php if($caixa->status === 'fechado'): ?>
        <div class="alert alert-success p-4 fs-5">
            <h3 class="alert-heading">✅ Caixa fechado com sucesso</h3>
            <p class="mb-0">
                O caixa foi encerrado corretamente, sem divergências.
            </p>
        </div>

    <?php elseif($caixa->status === 'inconsistente'): ?>
        <div class="alert alert-warning p-4 fs-5">
            <h3 class="alert-heading">⚠️ Caixa fechado com inconsistências</h3>
            <p class="mb-0 text-danger">
                Foram identificadas divergências.
                Este caixa será encaminhado para <strong>auditoria fiscal</strong>.
            </p>
        </div>

    <?php elseif($caixa->status === 'fechamento_sem_movimento'): ?>
        <div class="alert alert-danger p-4 fs-5">
            <h3 class="alert-heading">🚫 Caixa fechado sem movimentação</h3>
            <p class="mb-0">
                O caixa foi encerrado sem registro de vendas.
            </p>
        </div>
    <?php endif; ?>

    
    <div class="card shadow-lg">
        <div class="card-header fs-4">
            <strong>Resumo do Caixa</strong>
        </div>

        <div class="card-body fs-5">
            <ul class="list-group list-group-flush">

                <li class="list-group-item">
                    <strong>ID do Caixa:</strong> <?php echo e($caixa->id); ?>

                </li>

                <li class="list-group-item">
                    <strong>Status:</strong>
                    <span class="badge fs-6 px-3 py-2
                        <?php if($caixa->status === 'fechado'): ?> bg-success
                        <?php elseif($caixa->status === 'inconsistente'): ?> bg-warning text-dark
                        <?php else: ?> bg-danger
                        <?php endif; ?>
                    ">
                        <?php echo e(ucfirst(str_replace('_',' ', $caixa->status))); ?>

                    </span>
                </li>

                <li class="list-group-item">
                    <strong>Valor de Fechamento:</strong>
                    R$ <?php echo e(number_format($caixa->valor_fechamento, 2, ',', '.')); ?>

                </li>

                <li class="list-group-item">
                    <strong>Data de Fechamento:</strong>
                    <?php echo e($caixa->data_fechamento?->format('d/m/Y H:i')); ?>

                </li>
            </ul>
        </div>

        
        <div class="card-footer d-flex justify-content-end gap-3 p-3">

            <a href="<?php echo e(route('caixa.abrir')); ?>"
               class="btn btn-success btn-lg">
                🔁 Abrir novo caixa
            </a>

            <!-- <a href="<?php echo e(route('logout')); ?>"
               class="btn btn-outline-secondary btn-lg"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                🚪 Sair
            </a> -->
            <a href="<?php echo e(route('pdv.index')); ?>"
               class="btn btn-success btn-lg">
                🚪 Sair
            </a>
            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                <?php echo csrf_field(); ?>
            </form>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/fechamento_caixa/confirmacao_inconsistente.blade.php ENDPATH**/ ?>