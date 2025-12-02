

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h2 class="mb-4">Lotes do Produto: 000<?php echo e($produto->id); ?> </h2>

    <div class="justify-content-end gap-2 text-primary">
         Produto: <strong> <?php echo e($produto->nome); ?> </strong>
    </div>

    <?php if($produto->lotes->isEmpty()): ?>
        <div class="alert alert-warning text-center py-3">
            Nenhum lote cadastrado para este produto.
        </div>
    <?php else: ?>
        <div class="row">
            <?php $__currentLoopData = $produto->lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-3">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-success fw-bold">
                                <i class="bi bi-box-seam"></i> Lote #<?php echo e($lote->numero_lote); ?> 
                            </h5>
                            <p class="card-text mb-1"><strong>Lote Criado por:</strong> <?php echo e($lote->usuario->name ?? '-'); ?></p>
                                <!-- <p class="card-text mb-1"><strong>Pedido Compra:</strong> 000<?php echo e($lote->pedido_compra_id); ?></p> -->
                             <p class="card-text mb-1">
                                <strong>Pedido Compra:</strong>
                                <a href="<?php echo e(route('pedidos.show', $lote->pedido_compra_id)); ?>">
                                    000<?php echo e($lote->pedido_compra_id); ?>

                                </a>
                            </p>

                            <p class="card-text mb-1"><strong>Produto ID:</strong> 000<?php echo e($lote->produto_id); ?></p>
                            <p class="card-text mb-1"><strong>Qtd Comprada:</strong> <?php echo e($lote->quantidade); ?></p>
                            <p class="card-text mb-1"><strong>Preço de Compra:</strong> R$ <?php echo e(number_format($lote->preco_compra, 2, ',', '.')); ?></p>
                            <p class="card-text mb-1"><strong>Data da Compra:</strong> <?php echo e(\Carbon\Carbon::parse($lote->data_compra)->format('d/m/Y')); ?></p>
                            <p class="card-text mb-1"><strong>Validade até::</strong> <?php echo e(\Carbon\Carbon::parse($lote->validade_lote)->format('d/m/Y')); ?></p>
                            <p class="card-text text-muted small"><strong>Cadastrado em:</strong> <?php echo e($lote->created_at->format('d/m/Y H:i')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

   <div class="container mt-4 d-flex justify-content-end gap-2">
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">Voltar</a>
        <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-secondary">Início</a>
    </div>


</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/lotes/index.blade.php ENDPATH**/ ?>