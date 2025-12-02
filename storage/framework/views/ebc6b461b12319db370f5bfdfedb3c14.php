

<?php $__env->startSection('content'); ?>
<div class="container pt-4" style="border:1px solid #ddd; padding:15px; border-radius:5px; background-color:#f9f9f9;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Produtos Ativos</h2>
        <!-- <div>
            <a href="<?php echo e(route('produtos.create')); ?>" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Novo Produto
            </a>
            <a href="<?php echo e(route('produtos.inativos')); ?>" class="btn btn-secondary">
                Produtos Inativos
            </a>
        </div> -->
    </div>

    <!-- Alertas -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Atenção:</strong> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?> -->

    <!-- Formulário de busca -->
    <form action="<?php echo e(route('produtos.search')); ?>" method="GET" class="mb-3 row g-2 align-items-end">
        <div class="col-md-8">
            <input type="text" name="query" class="form-control" placeholder="Buscar por nome, categoria ou fornecedor..." value="<?php echo e(request('q')); ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
            <a href="<?php echo e(route('produtos.index')); ?>" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>
        
    </form>
    <div class="col-md-4 d-flex gap-2">
            <a href="<?php echo e(route('produtos.create')); ?>" class="btn btn-success btn-sm "style="width: 6.3rem">
                <i class="bi bi-plus-circle"></i> Novo
            </a>
    </div>
    <?php if($produtos->count() > 0): ?>
        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-6">
            <?php echo e($produtos->links('pagination::bootstrap-5')); ?>

        </div>
        <div class="row g-4">
            <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <div class="col-md-5 col-lg-4">
                    <div class="card h-100 
                            <?php if($produto->promocao && $produto->promocao->preco_promocional < $produto->preco_venda): ?>
                                border border-danger shadow" style="background-color:#fff5f5;"
                            <?php else: ?>
                                shadow-sm"
                            <?php endif; ?>
                        >
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($produto->nome); ?></h5>
                            <p class="card-text mb-1"><strong>Produto ID:</strong> 000<?php echo e($produto->id ?? '-'); ?></p>
                            <p class="card-text mb-1"><strong>Categoria:</strong> <?php echo e($produto->categoria->nome ?? '-'); ?></p>
                            <p class="card-text mb-1"><strong>Fornecedor:</strong> <?php echo e($produto->fornecedor->nome ?? '-'); ?></p>
                             <div class="card-text mb-1">
                                <strong>Pedido Compra:</strong>

                                <?php if($produto->lotes->isEmpty()): ?>
                                    <span class="text-danger ms-1">Sem lote</span>
                                <?php else: ?>
                                    <span class="ms-1">
                                        <?php $__currentLoopData = $produto->lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(url('pedidos/' . $lote->pedido_compra_id)); ?>" class="me-2">
                                                <?php echo e($lote->pedido_compra_id); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- <p class="card-text mb-1" style="color:blue"><strong>Preço de Custo:</strong> R$ <?php echo e(number_format($produto->preco_custo, 2, ',', '.')); ?></p> -->
                            <p class="card-text mb-1 text-primary"><strong>Preço Médio de Compra:</strong> R$ <?php echo e(number_format($produto->preco_medio_compra, 2, ',', '.')); ?></p>
                            <p class="card-text mb-1"><strong>Marca:</strong> <?php echo e($produto->marca->nome ?? '-'); ?></p>
                            <p class="card-text mb-1"><strong>Unidade:</strong> <?php echo e($produto->unidadeMedida->nome ?? '-'); ?></p>
                            <p class="card-text mb-1"><strong>Estoque:</strong> <?php echo e($produto->quantidade_estoque); ?></p>
                            <p class="card-text mb-1"><strong>Mínimo:</strong> <?php echo e($produto->estoque_minimo); ?></p>
                            <p class="card-text mb-1"><strong>Compra:</strong> <?php echo e(\Carbon\Carbon::parse($produto->data_compra)->format('d/m/Y')); ?></p>
                            <p class="card-text mb-1 text-primary"><strong>Validade:</strong> <?php echo e(\Carbon\Carbon::parse($produto->validade_produto)->format('d/m/Y')); ?></p>
                            <p class="card-text mb-1 text-primary">
                                <strong>Preço Venda:</strong>
                                <?php if($produto->promocao): ?>
                                    <span style="text-decoration: line-through; color: #888;color:blue">
                                        R$ <?php echo e(number_format($produto->promocao->preco_original, 2, ',', '.')); ?>

                                    </span>
                                    <span style="color: green; font-weight: bold;">
                                        por R$ <?php echo e(number_format($produto->promocao->preco_promocional, 2, ',', '.')); ?>

                                    </span>
                                <?php else: ?>
                                    
                                        R$ <?php echo e(number_format($produto->preco_venda, 2, ',', '.')); ?>

                                   
                                <?php endif; ?>
                            </p>

                                                
                            
                            <?php if($produto->promocao): ?>
                                <p class="card-text mb-1" style="color:orange; font-weight:bold;font-size:1.5rem">
                                    <strong>Valor Promoção:</strong>
                                    R$ <?php echo e(number_format($produto->promocao->preco_promocional, 2, ',', '.')); ?>

                                </p>

                                <p class="card-text mb-1" style="color:green;">
                                    <strong>Válido Até:</strong>
                                    <?php echo e(\Carbon\Carbon::parse($produto->promocao->promocao_fim)->format('d/m/Y')); ?>

                                </p>
                            <?php endif; ?>
                            
                            <div class="d-flex flex-wrap gap-1 mt-3">
                                <div>
                                    <?php if($produto->imagem): ?>
                                    <label for="imagem" class="form-label">Imagem do Produto</label>
                                        <img id="imagemPreview" src="<?php echo e(asset('storage/' . $produto->imagem)); ?>" alt="Imagem Atual" style="max-width:200px; max-height:200px; border:1px solid #ddd; padding:5px;">
                                    <?php else: ?>
                                        <img id="imagemPreview" src="#" alt="Prévia da Imagem" style="display:none; max-width:200px; max-height:200px; border:1px solid #ddd; padding:5px;">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-3">
                                <a href="<?php echo e(route('produtos.show', $produto->id)); ?>" class="btn btn-sm btn-info" style="width: 6.3rem">Ver</a>
                                <a href="<?php echo e(route('produtos.edit', $produto->id)); ?>" class="btn btn-sm btn-warning "style="width: 6.3rem">Editar</a>
                                <a href="<?php echo e(route('lotes.index', $produto->id)); ?>" class="btn btn-sm btn-primary "style="width: 6.3rem">Lotes</a>
                                
                                <a href="<?php echo e(route('produtos.create')); ?>" class="btn btn-success btn-sm "style="width: 6.3rem">
                                     <i class="bi bi-plus-circle"></i> Novo
                                 </a>

                                 <a href="<?php echo e(route('produtos.index-grid')); ?>" class="btn btn-warning btn-sm "style="width: 6.3rem">
                                     <i class="bi bi-plus-circle"></i> Grid
                                 </a>
                                 <a href="<?php echo e(route('produtos.inativos')); ?>" class="btn btn-secondary btn-sm"style="width: 6.3rem">
                                    Inativados
                                </a>
                                <form action="<?php echo e(route('produtos.desativar', $produto->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger"style="width: 6.3rem"
                                        onclick="return confirm('Deseja desativar este produto?')">
                                        Desativar
                                    </button>
                                </form>
                                 
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($produtos->links('pagination::bootstrap-5')); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhum produto ativo encontrado.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/produtos/index.blade.php ENDPATH**/ ?>