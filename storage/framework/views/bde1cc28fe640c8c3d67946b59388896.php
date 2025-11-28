

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Detalhes do Produto</h1>

    <div class="row" style="border:1px solid #ddd; padding:15px; border-radius:5px; background-color:#f9f9f9;">
        <!-- Coluna 1: Informações básicas -->
        <div class="col-md-4">
            <div class="mb-3"><strong>Codigo:</strong> 000<?php echo e($produto->id); ?></div>
            <div class="mb-3"><strong>Nome:</strong> <?php echo e($produto->nome); ?></div>
            <div class="mb-3"><strong>Categoria:</strong> <?php echo e($produto->categoria->nome ?? '-'); ?></div>
            <div class="mb-3"><strong>Fornecedor:</strong> <?php echo e($produto->fornecedor->nome ?? '-'); ?></div>
            <div class="mb-3"style="color:blue"><strong>Preço médio de compra:</strong> R$ <?php echo e(number_format($produto->preco_medio_compra, 2, ',', '.')); ?></div>
            <div class="mb-3"><strong>Marca:</strong> <?php echo e($produto->marca->nome ?? '-'); ?></div>
            <div class="mb-3"><strong>Unidade de Medida:</strong> <?php echo e($produto->unidadeMedida->nome ?? '-'); ?></div>
            <div class="mb-3"><strong>Código de Barras:</strong> <?php echo e($produto->codigo_barras); ?></div>
            <div class="mb-3"><strong>SKU:</strong> <?php echo e($produto->sku); ?></div>
        </div>

        <!-- Coluna 2: Estoque, preços e datas -->
        <div class="col-md-4">
            <div class="mb-3"><strong>Quantidade em Estoque:</strong> <?php echo e($produto->quantidade_estoque); ?></div>
            <div class="mb-3"><strong>Estoque Mínimo:</strong> <?php echo e($produto->estoque_minimo); ?></div>
            <div class="mb-3" style="color:blue"><strong>Preço de Custo:</strong> R$ <?php echo e(number_format($produto->preco_custo, 2, ',', '.')); ?></div>
            <div class="mb-3"><strong>Preço de Venda:</strong> R$ <?php echo e(number_format($produto->preco_venda, 2, ',', '.')); ?></div>
            <div class="mb-3"><strong>Data da Compra:</strong> <?php echo e($produto->data_compra ? $produto->data_compra->format('d/m/Y') : '-'); ?></div>
            <!-- <div class="mb-3"><strong>Validade:</strong> <?php echo e($produto->validade_produto ? $produto->validade_produto->format('d/m/Y') : '-'); ?></div> -->
            <div class="mb-3"><strong>Peso:</strong> <?php echo e($produto->peso); ?> kg</div>
        </div>

       <!-- Coluna 3: Imagem -->
        <div class="col-md-4 d-flex flex-column align-items-center justify-content-center text-center">
           
            <div class="mt-3">
                <?php if($produto->imagem): ?>
                    <img src="<?php echo e(asset('storage/' . $produto->imagem)); ?>" alt="Imagem do Produto"
                        class="img-fluid" style="max-width: 300px; max-height: 300px; border:1px solid #ddd; padding:5px;">
                <?php else: ?>
                    <div style="width: 300px; height: 300px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center;">
                        Sem Imagem
                    </div>
                <?php endif; ?>
            </div>
             <strong>Imagem:</strong>
        </div>

    </div>

    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary mt-4">Voltar</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/produtos/show.blade.php ENDPATH**/ ?>