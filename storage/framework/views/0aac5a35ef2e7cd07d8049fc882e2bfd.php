

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Adicionar Lote ao Produto: <?php echo e($produto->nome); ?></h2>

    
    <div class="row mb-4">
        <div class="col-md-3">
            <strong>Marca:</strong>
            <input type="text" class="form-control" value="<?php echo e($produto->marca->nome ?? ''); ?>" readonly>
        </div>
        <div class="col-md-3">
            <strong>Fornecedor:</strong>
            <input type="text" class="form-control" value="<?php echo e($produto->fornecedor->nome ?? ''); ?>" readonly>
        </div>
        <div class="col-md-3">
            <strong>Unidade:</strong>
            <input type="text" class="form-control" value="<?php echo e($produto->unidadeMedida->nome ?? ''); ?>" readonly>
        </div>
        <div class="col-md-3">
            <strong>SKU:</strong>
            <input type="text" class="form-control" value="<?php echo e($produto->sku); ?>" readonly>
        </div>
    </div>

    
    <form action="<?php echo e(route('lotes.store', $produto->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <!--  -->
            <input type="hidden" name="fornecedor_id" value="<?php echo e($produto->fornecedor_id); ?>">

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="preco_compra" class="form-label">Preço de Compra</label>
                <input type="number" step="0.01" name="preco_compra" id="preco_compra" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="data_compra" class="form-label">Data da Compra</label>
                <input type="date" name="data_compra" id="data_compra" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="validade" class="form-label">Validade (opcional)</label>
                <input type="date" name="validade" id="validade" class="form-control">
                <small class="text-muted">Se não informar, será adicionada automaticamente +3 meses.</small>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="<?php echo e(route('lotes.index', $produto->id)); ?>" class="btn btn-secondary">Voltar</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\lotes\create.blade.php ENDPATH**/ ?>