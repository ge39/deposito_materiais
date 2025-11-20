

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Rastrear Devoluções Venda</h2>

    <!-- Formulário de filtros -->
    <form action="<?php echo e(route('devolucoes.buscar')); ?>" method="GET" class="mb-4">
        <div class="row g-4">
            <div class="col-md-3">
                <label>Venda</label>
                <select name="venda_id" class="form-control">
                    <option value="">Todas</option>
                    <?php $__currentLoopData = $vendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($venda->id); ?>" <?php echo e(request('venda_id') == $venda->id ? 'selected' : ''); ?>>
                            Venda #<?php echo e($venda->id); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Cliente</label>
                <select name="cliente_id" class="form-control">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cliente->id); ?>" <?php echo e(request('cliente_id') == $cliente->id ? 'selected' : ''); ?>>
                            <?php echo e($cliente->nome); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Produto</label>
                <select name="produto_id" class="form-control">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($produto->id); ?>" <?php echo e(request('produto_id') == $produto->id ? 'selected' : ''); ?>>
                            <?php echo e($produto->nome); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Lote</label>
                <select name="lote_id" class="form-control">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lote->id); ?>" <?php echo e(request('lote_id') == $lote->id ? 'selected' : ''); ?>>
                            Lote #<?php echo e($lote->id); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary">Limpar</a>
                <a href="<?php echo e(route('devolucoes.pendentes')); ?>" class="btn btn-warning">Devoluções Pendentes</a>
            </div>
        </div>
    </form>

    <!-- Cards de resultados -->
    <?php if($itens->isNotEmpty()): ?>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $qtdDevolvida = $item->devolucoes
                        ->whereIn('status', ['aprovada','concluida'])
                        ->sum('quantidade');
                    $qtdDisponivel = $item->quantidade - $qtdDevolvida;
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm 
                        <?php if($qtdDisponivel == 0): ?> border-success <?php endif; ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                Venda #<?php echo e($item->venda->id); ?> - <?php echo e($item->produto->nome); ?>

                            </h5>
                            <p class="card-text mb-1"><strong>Data da Venda:</strong> <?php echo e($item->venda->created_at->format('d/m/Y')); ?></p>
                            <p class="card-text mb-1"><strong>Cliente:</strong> <?php echo e($item->venda->cliente->nome); ?></p>
                            <p class="card-text mb-1"><strong>Lote Rastreio:</strong> <?php echo e($item->lote->id ?? '-'); ?></p>
                            <p class="card-text mb-1"><strong>Valor Compra:</strong> R$<?php echo e(number_format($item->subtotal,2,',','.')); ?></p>
                            <p class="card-text mb-1">
                                <strong>Qtde Comprada:</strong> <?php echo e($item->quantidade); ?> |
                                <strong>Devolvida:</strong> <?php echo e($qtdDevolvida); ?> |
                                <strong>Disponível:</strong> <?php echo e($qtdDisponivel); ?>

                            </p>                            
                            <p class="card-text mb-1"><strong>Valor Unidade:</strong> R$<?php echo e(number_format($item->preco_unitario,2,',','.')); ?></p>
                                <?php
                                    $valorExtornado = $qtdDevolvida * $item->preco_unitario;
                                ?>
                            <p class="card-text mb-1">
                                <strong>Valor Extornado:</strong>
                                R$<?php echo e(number_format($valorExtornado, 2, ',', '.')); ?>

                            </p>
                            <p class="card-text mb-1"><strong>Subtotal:</strong> R$<?php echo e(number_format($item->subtotal - $valorExtornado,2,',','.')); ?></p>
                            <div class="mt-2 d-flex gap-2 align-items-start">
                                <?php if($qtdDisponivel > 0): ?>
                                    <a href="<?php echo e(route('devolucoes.registrar', ['item_id' => $item->id])); ?>" 
                                    class="btn btn-sm mt-3 btn-danger">
                                        <i class="bi bi-x-circle"></i> Devolver
                                    </a>
                                <?php else: ?>
                                    <div class="d-flex flex-column">
                                        <p class="card-text mb-1">
                                            Última Devolução:
                                            <?php if($item->devolucoes->count() > 0): ?>
                                                <?php echo e($item->devolucoes->last()->updated_at->format('d/m/Y')); ?>

                                            <?php else: ?>
                                                — 
                                            <?php endif; ?>
                                        </p>
                                        
                                        <?php if($item->devolucoes->count() > 0): ?>
                                            <p class="text-success fw-bold mb-0 mt-2 text-start">
                                                Totalmente devolvido
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-sm mt-3 btn-secondary">Voltar</a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center py-3" style="background-color: #f0d791;">
            Nenhum item encontrado
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/index.blade.php ENDPATH**/ ?>