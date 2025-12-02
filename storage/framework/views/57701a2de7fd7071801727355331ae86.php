

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Visualizar Pedido de Compra #<?php echo e($pedido->id); ?></h2>

    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Código</h6>
                    <p class="card-text fw-bold"><?php echo e($pedido->id); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Fornecedor</h6>
                    <p class="card-text fw-bold">
                        <?php echo e($pedido->fornecedor->nome ?? $pedido->fornecedor->nome_fantasia ?? $pedido->fornecedor->razao_social); ?>

                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Data do Pedido</h6>
                    <p class="card-text fw-bold"><?php echo e($pedido->data_pedido->format('d/m/Y h:i:s')); ?></p>
                </div>
            </div>
        </div>
         <div class="col-md-3">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Data Recebimento</h6>
                    <p class="card-text fw-bold"><?php echo e($pedido->updated_at->format('d/m/Y h:i:s')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Status</h6>
                    <?php
                        $statusClasses = [
                            'pendente' => 'badge bg-warning text-dark',
                            'aprovado' => 'badge bg-primary',
                            'recebido' => 'badge bg-success',
                            'cancelado' => 'badge bg-danger'
                        ];
                    ?>
                    <span class="<?php echo e($statusClasses[$pedido->status] ?? 'badge bg-secondary'); ?>">
                        <?php echo e(ucfirst($pedido->status)); ?>

                    </span>
                       
                 </div>
                 
            </div>
        </div>
       <div style="display: flex; gap: 20px; margin-bottom: 5px;">

            <?php if($pedido->status === 'recebido'): ?>
                <div>
                    <strong>Recebido e conferido por:</strong> <?php echo e($pedido->user->name ?? '-'); ?>

                </div>
            <?php elseif($pedido->status === 'aprovado'): ?>
                <div>
                    <strong>Pedido Compra aprovado por:</strong> <?php echo e($pedido->user->name ?? '-'); ?>

                </div>
            <?php elseif($pedido->status === 'cancelado'): ?>
                <div>
                    <strong>Cancelado por:</strong> <?php echo e($pedido->user->name ?? '-'); ?>

                </div>
            <?php endif; ?>
        </div>


    </div>

    <hr>

    <h5 class="mt-4 mb-3">Itens do Pedido</h5>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-borderless align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 250px;">Produto</th>
                    <th style="width: 100px;">Unidade</th>
                    <th style="width: 100px;">Quantidade</th>
                    <th style="width: 120px;">Valor Unitário (R$)</th>
                    <th style="width: 120px;">Subtotal (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $pedido->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e($item->produto->nome); ?></td>
                        <td><?php echo e($item->produto->unidadeMedida->nome ?? '-'); ?></td>
                        <td><?php echo e(number_format($item->quantidade, 2, ',', '.')); ?></td>
                        <td><?php echo e(number_format($item->valor_unitario, 2, ',', '.')); ?></td>
                        <td><?php echo e(number_format($item->subtotal, 2, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-3 mt-3">
        <a href="<?php echo e(route('pedidos.index')); ?>" class="btn btn-secondary">Voltar</a>
        <!-- <h5 class="mb-0">Total: R$ <span id="totalGeral"><?php echo e(number_format($pedido->total, 2, ',', '.')); ?></span></h5> -->
        <h5 class="mb-0">Total: R$ <span id="totalGeral"><?php echo e(number_format($totalGeral, 2, ',', '.')); ?></span></h5>
    </div>
 

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pedidos/show.blade.php ENDPATH**/ ?>