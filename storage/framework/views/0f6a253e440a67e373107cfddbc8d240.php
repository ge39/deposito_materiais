

<?php $__env->startSection('content'); ?>
<div class="container">

    
    
    
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
                <h5 class="mb-0">Promoções Vigentes (Hoje)</h5>
                <a href="<?php echo e(route('promocoes.create')); ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Nova Promoção
                </a>
            </div>

            <div class="card-body">

                <?php if($promocoesAtivas->isEmpty()): ?>
                    <p class="text-muted mb-0">Nenhuma promoção ativa hoje.</p>

                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php $__currentLoopData = $promocoesAtivas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">

                                        
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title mb-1">
                                                <?php echo e($promo->produto->nome ?? ($promo->categoria->nome ?? '—')); ?>

                                            </h6>
                                            <span class="badge bg-success align-self-start">Ativa</span>
                                        </div>

                                        <p class="mb-1 small text-muted">
                                            <?php echo e('Produto ID: '. $promo->produto_id); ?>

                                        </p>

                                        
                                        <dl class="row mb-2 small">

                                            <dt class="col-5">Início</dt>
                                            <dd class="col-7">
                                                <?php echo e($promo->promocao_inicio ? \Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y') : '—'); ?>

                                            </dd>

                                            <dt class="col-5">Fim</dt>
                                            <dd class="col-7">
                                                <?php echo e($promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—'); ?>

                                            </dd>

                                            <dt class="col-5">Tipo desconto</dt>
                                            <dd class="col-7">
                                                <?php if(!empty($promo->desconto_percentual) && $promo->desconto_percentual > 0): ?>
                                                    Percentual (<?php echo e(number_format($promo->desconto_percentual, 2, ',', '.')); ?>%)
                                                <?php elseif(!empty($promo->preco_promocional) && $promo->preco_promocional > 0): ?>
                                                    Valor Fixo
                                                <?php elseif(!empty($promo->acrescimo_percentual) && $promo->acrescimo_percentual > 0): ?>
                                                    Acréscimo (<?php echo e(number_format($promo->acrescimo_percentual, 2, ',', '.')); ?>%)
                                                <?php elseif(!empty($promo->acrescimo_valor) && $promo->acrescimo_valor > 0): ?>
                                                    Acréscimo R$ (<?php echo e(number_format($promo->acrescimo_valor, 2, ',', '.')); ?>)
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </dd>

                                            <dt class="col-5">Estoque</dt>
                                            <dd class="col-7">
                                                <?php echo e(isset($promo->quantidade_estoque)
                                                    ? $promo->quantidade_estoque
                                                    : (isset($promo->produto->quantidade_estoque)
                                                        ? $promo->produto->quantidade_estoque
                                                        : '—')); ?>

                                            </dd>

                                            <dt class="col-5">Preço original</dt>
                                            <dd class="col-7">
                                                <?php echo e(isset($promo->preco_original)
                                                    ? 'R$ '.number_format($promo->preco_original,2,',','.')
                                                    : (isset($promo->produto->preco)
                                                        ? 'R$ '.number_format($promo->produto->preco,2,',','.')
                                                        : '—')); ?>

                                            </dd>

                                            <dt class="col-5">Preço promoção</dt>
                                            <dd class="col-7">
                                                <?php if(isset($promo->preco_promocional) && $promo->preco_promocional > 0): ?>
                                                    R$ <?php echo e(number_format($promo->preco_promocional, 2, ',', '.')); ?>

                                                <?php elseif(isset($promo->preco_final)): ?>
                                                    R$ <?php echo e(number_format($promo->preco_final, 2, ',', '.')); ?>

                                                <?php elseif(!empty($promo->desconto_percentual) && isset($promo->produto->preco)): ?>
                                                    R$ <?php echo e(number_format($promo->produto->preco * (1 - $promo->desconto_percentual/100), 2, ',', '.')); ?>

                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </dd>
                                        </dl>

                                        
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">Criado: <?php echo e($promo->created_at->format('d/m/Y H:i')); ?></small>
                                            <div>
                                                <a href="<?php echo e(route('promocoes.show', $promo->id)); ?>" class="btn btn-outline-primary btn-sm">Ver</a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>



    
    
    
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Promoções Encerradas (mês atual)</h5>
            </div>

            <div class="card-body">
                <?php if($promocoesEncerradas->isEmpty()): ?>
                    <p class="text-muted mb-0">Nenhuma promoção encerrada neste mês.</p>

                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php $__currentLoopData = $promocoesEncerradas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">

                                        
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title mb-1">
                                                <?php echo e($promo->produto->nome ?? ($promo->categoria->nome ?? '—')); ?>

                                            </h6>
                                            <span class="badge bg-secondary">Encerrada</span>
                                        </div>

                                        <p class="mb-1 small text-muted">
                                            <!-- <?php echo e($promo->tipo_abrangencia === 'produto' ? 'Por produto' : ($promo->tipo_abrangencia === 'categoria' ? 'Por categoria' : 'Geral')); ?> -->
                                              Produto ID: <?php echo e($promo->produto_id); ?>

                                        </p>

                                        
                                        <dl class="row mb-2 small">

                                            <dt class="col-5">Início</dt>
                                            <dd class="col-7">
                                                <?php echo e($promo->promocao_inicio ? \Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y') : '—'); ?>

                                            </dd>

                                            <dt class="col-5">Fim</dt>
                                            <dd class="col-7">
                                                <?php echo e($promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—'); ?>

                                            </dd>

                                            <dt class="col-5">Tipo desconto</dt>
                                            <dd class="col-7">
                                                <?php if(!empty($promo->desconto_percentual) && $promo->desconto_percentual > 0): ?>
                                                    Percentual (<?php echo e(number_format($promo->desconto_percentual, 2, ',', '.')); ?>%)
                                                <?php elseif(!empty($promo->preco_promocional) && $promo->preco_promocional > 0): ?>
                                                    Valor fixo
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </dd>

                                            <dt class="col-5">Estoque</dt>
                                            <dd class="col-7">
                                                <?php echo e(isset($promo->quantidade_estoque)
                                                    ? $promo->quantidade_estoque
                                                    : (isset($promo->produto->quantidade_estoque)
                                                        ? $promo->produto->quantidade_estoque
                                                        : '—')); ?>

                                            </dd>

                                            <dt class="col-5">Preço original</dt>
                                            <dd class="col-7">
                                                <?php echo e(isset($promo->preco_original)
                                                    ? 'R$ '.number_format($promo->preco_original,2,',','.')
                                                    : (isset($promo->produto->preco)
                                                        ? 'R$ '.number_format($promo->produto->preco,2,',','.')
                                                        : '—')); ?>

                                            </dd>

                                            <dt class="col-5">Preço promoção</dt>
                                            <dd class="col-7">
                                                <?php echo e(isset($promo->preco_promocional) && $promo->preco_promocional > 0
                                                    ? 'R$ '.number_format($promo->preco_promocional,2,',','.')
                                                    : (isset($promo->preco_final)
                                                        ? 'R$ '.number_format($promo->preco_final,2,',','.')
                                                        : '—')); ?>

                                            </dd>
                                        </dl>

                                        
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">
                                                Encerrada em: 
                                                <?php echo e($promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—'); ?>

                                            </small>

                                            <a href="<?php echo e(route('promocoes.show', $promo->id)); ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/painel_promocao/index.blade.php ENDPATH**/ ?>