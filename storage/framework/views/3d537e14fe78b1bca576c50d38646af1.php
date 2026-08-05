

<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <h4 class="mb-3">Detalhes da Divergência de Estoque</h4>

    <div class="card">
        <div class="card-body">

            <p><strong>ID:</strong> <?php echo e($divergencia->id); ?></p>
            <p><strong>Produto:</strong> <?php echo e($divergencia->produto->nome ?? '-'); ?></p>
            <p><strong>Venda:</strong> <?php echo e($divergencia->venda_id ?? '-'); ?></p>
            <p><strong>Caixa:</strong> <?php echo e($divergencia->caixa_id ?? '-'); ?></p>

            <hr>

            <p><strong>Quantidade Solicitada:</strong> <?php echo e(number_format($divergencia->quantidade_solicitada, 3, ',', '.')); ?></p>
            <p><strong>Quantidade Atendida:</strong> <?php echo e(number_format($divergencia->quantidade_atendida, 3, ',', '.')); ?></p>
            <p><strong>Diferença:</strong> <?php echo e(number_format($divergencia->diferenca, 3, ',', '.')); ?></p>

            <hr>

            <p><strong>Tipo:</strong> <?php echo e(ucfirst($divergencia->tipo)); ?></p>
            <p><strong>Observação:</strong> <?php echo e($divergencia->observacao ?? '-'); ?></p>
            <p><strong>Usuário:</strong> <?php echo e($divergencia->usuario->name ?? '-'); ?></p>
            <p><strong>Data:</strong> <?php echo e(optional($divergencia->created_at)->format('d/m/Y H:i')); ?></p>

            <a href="<?php echo e(route('estoque-divergencias.index')); ?>" class="btn btn-secondary">
                Voltar
            </a>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/estoque_divergencias/show.blade.php ENDPATH**/ ?>