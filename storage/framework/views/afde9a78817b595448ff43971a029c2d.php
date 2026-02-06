

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Rastrear Devoluções Venda</h2>

    <!-- Formulário de busca unificado -->
    <form action="<?php echo e(route('devolucoes.buscar')); ?>" method="GET" class="mb-4">
        <div class="row g-4">
            <div class="col-md-12">
                <label>Pesquisar Venda ou Cliente</label>
                <input type="text" name="search" class="form-control" placeholder="Digite ID da venda ou nome do cliente" value="<?php echo e(request('search')); ?>">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary">Limpar</a>
                <a href="<?php echo e(route('devolucoes.pendentes')); ?>" class="btn btn-warning">Devoluções Pendentes</a>
            </div>
        </div>
    </form>

    <!-- Cards de resultados -->
    <?php if($vendas->isNotEmpty()): ?>
        
        <div class="row row-cols-1 row-cols-md-2 g-3">
           
            <?php $__currentLoopData = $vendas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Já temos tudo calculado na query
                    $qtdDisponivel = $item->quantidade_disponivel;
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm 
                        <?php if($qtdDisponivel == 0): ?> border-success <?php endif; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Venda #<?php echo e($item->venda_id); ?></h5>
                            <p class="card-text mb-1"><strong>Data da Venda:</strong> <?php echo e(\Carbon\Carbon::parse($item->data_venda)->format('d/m/Y')); ?></p>
                            <p class="card-text mb-1"><strong>Pessoa:</strong> <?php echo e($item->cliente_tipo); ?></p>
                            <p class="card-text mb-1"><strong>Nome:</strong> <?php echo e($item->cliente_nome); ?></p>
                             <p class="card-text mb-1"><strong>Doc:</strong> <?php echo e($item->cliente_cpf_cnpj); ?></p>
                            <p class="card-text mb-1">
                                <strong>Qtde Comprada:</strong> <?php echo e($item->quantidade_comprada); ?> |
                                <strong>Devolvida:</strong> <?php echo e($item->quantidade_devolvida); ?> |
                                <strong>Disponível:</strong> <?php echo e($qtdDisponivel); ?>

                            </p>
                            <p class="card-text mb-1"><strong>Valor Total:</strong> R$<?php echo e(number_format($item->valor_total,2,',','.')); ?></p>
                            <p class="card-text mb-1"><strong>Valor Extornado:</strong> R$<?php echo e(number_format($item->valor_extornado,2,',','.')); ?></p>

                            <div class="mt-2 d-flex gap-2 align-items-start">
                                <?php if($qtdDisponivel > 0): ?>
                                    <a href="<?php echo e(route('devolucoes.registrar', ['item_id' => $item->venda_id])); ?>" 
                                       class="btn btn-sm mt-3 btn-danger">
                                        <i class="bi bi-x-circle"></i> Devolver
                                    </a>
                                <?php else: ?>
                                    <p class="text-success fw-bold mb-0 mt-2">Totalmente devolvido</p>
                                <?php endif; ?>

                                <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-sm mt-3 btn-secondary">Voltar</a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <!-- Links de paginação -->
        <div class="mt-4 d-flex justify-content-center">
          <?php echo e($vendas->links('pagination::bootstrap-5')); ?>

        </div
    <?php else: ?>
        <div class="alert alert-warning text-center py-3" style="background-color: #f0d791;">
            Nenhuma venda encontrada
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/index.blade.php ENDPATH**/ ?>