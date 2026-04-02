

<?php $__env->startSection('content'); ?>
<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0 fw-semibold">
            Pedido / Orçamento #<?php echo e($orcamento->id); ?>

        </h4>

        <div class="d-flex gap-2 flex-wrap">

            <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-outline-secondary btn-sm">
                ← Voltar
            </a>

            <?php if($orcamento->status === 'Aberto'): ?>
                <form action="<?php echo e(route('orcamentos.aprovar', $orcamento->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-success btn-sm">Aprovar</button>
                </form>

                <form action="<?php echo e(route('orcamentos.cancelar', $orcamento->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-danger btn-sm">Cancelar</button>
                </form>
            <?php endif; ?>

            <a href="<?php echo e(route('orcamentos.gerarPdf', $orcamento->id)); ?>" target="_blank" class="btn btn-primary btn-sm">
                PDF
            </a>

        </div>
    </div>

    <!-- RESUMO -->
    <div class="row mb-4 g-3">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Status</small><br>
                    <span class="badge bg-info text-dark">
                        <?php echo e($orcamento->status); ?>

                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Data</small><br>
                    <?php echo e(\Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')); ?>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Validade</small><br>
                    <?php echo e(\Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y')); ?>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Total</small><br>
                    <span class="fw-bold text-success">
                        R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?>

                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 👤 CLIENTE -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-light fw-semibold">
            👤 Dados do Cliente
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <small class="text-muted">Nome</small><br>
                    <strong><?php echo e($orcamento->cliente->nome ?? '-'); ?></strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Telefone</small><br>
                    <?php echo e($orcamento->cliente->telefone ?? '-'); ?>

                </div>

                <div class="col-md-4">
                    <small class="text-muted">Cidade</small><br>
                    <?php echo e($orcamento->cliente->cidade ?? '-'); ?>

                </div>

                <div class="col-md-12">
                    <small class="text-muted">Endereço</small><br>
                    <?php echo e($orcamento->cliente->endereco ?? '-'); ?>

                </div>

            </div>
        </div>
    </div>

    <!-- 📦 ITENS ATENDIDOS -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-success text-white fw-semibold">
            📦 Itens Atendidos
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Produto</th>
                            <th>Qtd</th>
                            <th>Lote</th>
                            <th>Preço</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orcamento->itens->where('quantidade_atendida', '>', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="text-center">

                                <td class="text-start">
                                    <?php echo e($item->produto->descricao ?? '-'); ?>

                                </td>

                                <td>
                                    <?php echo e(number_format($item->quantidade_atendida, 2, ',', '.')); ?>

                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo e($item->lote->numero_lote ?? '-'); ?>

                                    </span>
                                </td>

                                <td>
                                    R$ <?php echo e(number_format($item->preco_unitario, 2, ',', '.')); ?>

                                </td>

                                <td class="fw-semibold text-success">
                                    R$ <?php echo e(number_format($item->quantidade_atendida * $item->preco_unitario, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Nenhum item atendido.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ⏳ ITENS PENDENTES -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning fw-semibold">
            ⏳ Itens Pendentes / Aguardando Estoque
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Produto</th>
                            <th>Pendente</th>
                            <th>Previsão</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orcamento->itens->where('quantidade_pendente', '>', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="text-center">

                                <td class="text-start">
                                    <?php echo e($item->produto->descricao ?? '-'); ?>

                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        <?php echo e(number_format($item->quantidade_pendente, 2, ',', '.')); ?>

                                    </span>
                                </td>

                                <td>
                                    <?php echo e($item->previsao_entrega 
                                        ? \Carbon\Carbon::parse($item->previsao_entrega)->format('d/m/Y')
                                        : '-'); ?>

                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Aguardando
                                    </span>
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Nenhum item pendente.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/orcamentos/show.blade.php ENDPATH**/ ?>