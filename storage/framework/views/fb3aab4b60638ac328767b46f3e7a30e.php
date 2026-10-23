

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Registrar Devolução</h2>

    
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Dados da Venda</h5>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="<?php echo e($item->venda->cliente->nome); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Produto</label>
                    <input type="text" class="form-control" value="<?php echo e($item->produto->nome); ?>" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qtde Vendida</label>
                    <input type="text" class="form-control" value="<?php echo e($item->quantidade); ?>" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor Unitário</label>
                    <input type="text" class="form-control" value="R$ <?php echo e(number_format($item->preco_unitario, 2, ',', '.')); ?>" disabled>
                </div>
            </div>

            
            <form action="<?php echo e(route('devolucoes.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="venda_item_id" value="<?php echo e($item->id); ?>">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="quantidade" class="form-label">Quantidade a Devolver</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" max="<?php echo e($item->quantidade); ?>" required>
                    </div>

                    <div class="col-md-5">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" name="motivo" id="motivo" class="form-control" placeholder="Ex: Defeito no produto" required>
                    </div>

                    <div class="col-md-4">
                        <label for="tipo" class="form-label">Tipo de Devolução</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="defeito">Defeito</option>
                            <option value="erro">Erro de envio</option>
                            <option value="insatisfacao">Insatisfação</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observacao" class="form-label">Observações (opcional)</label>
                    <textarea name="observacao" id="observacao" rows="3" class="form-control"></textarea>
                </div>

                
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('rastreio.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger">Registrar Devolução</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\create.blade.php ENDPATH**/ ?>