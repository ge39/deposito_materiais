

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Detalhes da Devolução #<?php echo e($devolucao->id); ?></h2>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            Informações da Devolução
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Cliente:</strong> <?php echo e($devolucao->cliente ? $devolucao->cliente->nome : 'Não informado'); ?></div>
                <div class="col-md-4"><strong>CPF:</strong> <?php echo e($devolucao->cliente ? $devolucao->cliente->cpf : '-'); ?></div>
                <div class="col-md-4"><strong>Status:</strong> <?php echo e(ucfirst($devolucao->status)); ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4"><strong>Produto:</strong> <?php echo e($devolucao->item ? $devolucao->item->produto->nome : '-'); ?></div>
                <div class="col-md-4"><strong>Código Produto:</strong> <?php echo e($devolucao->item && $devolucao->item->produto ? $devolucao->item->produto->codigo : '-'); ?></div>
                <div class="col-md-4"><strong>Quantidade:</strong> <?php echo e($devolucao->item ? $devolucao->item->quantidade : '-'); ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4"><strong>Venda #:</strong> <?php echo e($devolucao->venda_id ?? '-'); ?></div>
                <div class="col-md-4"><strong>Lote:</strong> <?php echo e($devolucao->item && $devolucao->item->lote ? $devolucao->item->lote->codigo : '-'); ?></div>
                <div class="col-md-4"><strong>Data da Devolução:</strong> <?php echo e($devolucao->created_at->format('d/m/Y H:i')); ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-12"><strong>Motivo:</strong> <?php echo e($devolucao->motivo); ?></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary">Voltar</a>
        <a href="<?php echo e(route('devolucoes.edit', $devolucao)); ?>" class="btn btn-primary">Editar</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\show.blade.php ENDPATH**/ ?>