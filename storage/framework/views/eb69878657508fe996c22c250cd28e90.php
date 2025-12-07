<!-- pdv/abrir.blade.php -->

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0">Abrir Venda</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('pdv.abrir')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <select name="cliente_id" class="form-select" required>
                                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 py-2">Abrir Venda</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

        <!-- Painel direito: lista de itens -->
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                    <span>Itens da Venda</span>
                    <span class="fw-bold">Total: R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Qtd.</th>
                                <th class="text-end">Preço</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->produto->nome); ?></td>
                                    <td class="text-center"><?php echo e($item->quantidade); ?></td>
                                    <td class="text-end">R$ <?php echo e(number_format($item->preco_unitario, 2, ',', '.')); ?></td>
                                    <td class="text-end fw-bold">R$ <?php echo e(number_format($item->subtotal, 2, ',', '.')); ?></td>
                                    <td class="text-center">
                                        <form action="<?php echo e(route('pdv.removerItem', $item->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-danger">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-end d-flex justify-content-between">
                    <a href="<?php echo e(route('pdv.cancelar', $venda->id)); ?>" class="btn btn-warning px-4 py-2 rounded-3">Cancelar Venda</a>
                    <a href="<?php echo e(route('pdv.finalizar', $venda->id)); ?>" class="btn btn-success px-4 py-2 rounded-3">Finalizar Venda</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/abrir.blade.php ENDPATH**/ ?>