

<?php $__env->startSection('content'); ?>
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Promoções</h2>
        <a href="<?php echo e(route('promocoes.create')); ?>" class="btn btn-primary">Nova Promoção</a>
    </div>

    
   <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <?php if($errors->any()): ?>
        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    
    <?php if($promocoes->count() === 0): ?>
        <div class="alert alert-info">Nenhuma promoção encontrada.</div>
    <?php else: ?>

        <div class="row">
            <?php $__currentLoopData = $promocoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            
                            <h5 class="card-title fw-bold">

                                <?php if($promo->tipo_abrangencia === 'produto'): ?>
                                    <?php echo e($promo->produto->nome ?? 'Produto não encontrado'); ?>


                                <?php elseif($promo->tipo_abrangencia === 'categoria'): ?>
                                    Categoria: <?php echo e($promo->categoria->nome ?? 'Categoria não encontrada'); ?>


                                <?php else: ?>
                                    Promoção Geral
                                <?php endif; ?>

                            </h5>

                            
                            <p class="text-muted mb-1">
                                Produto ID: <strong>000<?php echo e($promo->produto_id); ?></strong>
                            </p>

                            <hr>

                            
                            <p class="mb-1">
                                Preço Original:
                                <strong>R$ <?php echo e(number_format($promo->preco_original, 2, ',', '.')); ?></strong>
                            </p>
                             
                            <p class="mb-1">
                                Desconto Aplicado:
                                <strong><?php echo e($promo->desconto_percentual); ?> %</strong>
                            </p>
                            <p class="mb-2">
                                Preço Promocional:
                                <strong class="text-success">
                                    R$ <?php echo e(number_format($promo->preco_promocional, 2, ',', '.')); ?>

                                </strong>
                            </p>

                            
                            <p class="mb-1">
                                Início: <strong><?php echo e(\Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y')); ?></strong>
                            </p>

                            <p>
                                Fim: <strong><?php echo e(\Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y')); ?></strong>
                            </p>

                            
                            <?php
                                $hoje = \Carbon\Carbon::today();
                                $expirada = $promo->promocao_fim < $hoje;
                            ?>

                            <?php if(!$promo->status): ?>
                                <span class="badge bg-secondary mb-3">Desativada</span>
                            <?php elseif($expirada): ?>
                                <span class="badge bg-danger mb-3">Expirada</span>
                            <?php else: ?>
                                <span class="badge bg-success mb-3">Ativa</span>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mt-2">

                                
                                <a href="<?php echo e(route('promocoes.edit', $promo->id)); ?>" class="btn btn-sm btn-warning">
                                    Editar
                                </a>

                                
                                <form action="<?php echo e(route('promocoes.toggle', $promo->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button class="btn btn-sm btn-info">
                                        <?php echo e($promo->status ? 'Encerrar Promoção' : 'Ativar'); ?>

                                    </button>
                                </form>

                                
                                <form action="<?php echo e(route('promocoes.destroy', $promo->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Excluir esta promoção?')">
                                        Excluir
                                    </button>
                                    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">
                                        Voltar
                                    </a> 
                                </form>

                            </div>

                        </div>

                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($promocoes->links()); ?>

        </div>

    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/index.blade.php ENDPATH**/ ?>