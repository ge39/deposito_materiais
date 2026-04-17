

<?php $__env->startSection('content'); ?>
<div class="container">

    <h3 class="mb-4">📦 Relatório de Reposição de Estoque</h3>

    
    <form method="GET" class="row mb-4">

        <div class="col-md-2">
            <label>Data início</label>
            <input type="date" name="data_inicio" class="form-control"
                value="<?php echo e(request('data_inicio')); ?>">
        </div>

        <div class="col-md-2">
            <label>Data fim</label>
            <input type="date" name="data_fim" class="form-control"
                value="<?php echo e(request('data_fim')); ?>">
        </div>

        <div class="col-md-3">
            <label>Produto (início do nome)</label>
            <input type="text" name="produto" class="form-control"
                value="<?php echo e(request('produto')); ?>"
                placeholder="Ex: Areia">
        </div>

        <div class="col-md-3">
            <label>Código de barras</label>
            <input type="text" name="codigo_barras" class="form-control"
                value="<?php echo e(request('codigo_barras')); ?>">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary w-50">Filtrar</button>
            <a href="<?php echo e(route('relatorio.reposicao')); ?>" class="btn btn-secondary w-50">Limpar</a>
        </div>

    </form>
    <!-- <a href="<?php echo e(route('relatorio.reposicao.pdf', request()->query->all())); ?>" 
        class="btn btn-success mb-3" target="_blank">
        📄 Gerar PDF
    </a> -->

    <div class="mb-3 d-flex gap-2">

    <a href="<?php echo e(route('relatorio.reposicao.pdf', array_merge(request()->all(), ['orientacao' => 'portrait']))); ?>"
       class="btn btn-outline-secondary"
       target="_blank">
        📄 PDF Retrato
    </a>

    <a href="<?php echo e(route('relatorio.reposicao.pdf', array_merge(request()->all(), ['orientacao' => 'landscape']))); ?>"
       class="btn btn-outline-secondary"
       target="_blank">
        📄 PDF Paisagem
    </a>

</div>
    
    
    <div class="row mb-4">

        <div class="col-md-6">
            <div class="card p-3">
                <strong>Total de itens Pendentes:</strong>
                <h4><?php echo e(number_format($totais->total_pendente ?? 0, 2, ',', '.')); ?></h4>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <strong>Valor Total - Orçamentos Pendentes:</strong>
                <h4>R$ <?php echo e(number_format($totais->valor_total ?? 0, 2, ',', '.')); ?></h4>
            </div>
        </div>

    </div>

    
    <div class="card mb-4">
        <div class="card-header">
            🔥 Produtos com maior necessidade de compra
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Orcamento - Link gerador pdf</th>
                        <th>Unidade</th>
                        <th>Pendente</th>
                        <!-- <th>Estoque</th> -->
                        <th>Compra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $resumo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($r->nome); ?></td>
                            <td style="width: 600px; white-space: normal;">
                                <?php
                                    $ids = explode(',', $r->ids_orcamento ?? '');
                                    $codigos = explode(',', $r->codigos_orcamento ?? '');
                                ?>

                                <div class="d-flex flex-wrap gap-1">
                                    <?php $__currentLoopData = $codigos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $codigo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $id = $ids[$index] ?? null; ?>
                                        <?php if($id): ?>
                                            <a href="<?php echo e(url("/orcamentos/{$id}/pdf")); ?>" 
                                            class="badge bg-primary text-decoration-none" target="_blank" 
                                            rel="noopener noreferrer">
                                                <?php echo e(trim($codigo)); ?>

                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                            <td><?php echo e($r->unidade ?? '-'); ?></td>
                            <td><?php echo e(number_format($r->total_pendente, 2, ',', '.')); ?></td>
                            <!-- <td><?php echo e(number_format($r->quantidade_estoque, 2, ',', '.')); ?></td> -->
                             <!-- <td><?php echo e(number_format($r->total_pendente, 2, ',', '.')); ?></td> -->
                            <td>
                                <span class="badge bg-danger text-center">
                                    <?php echo e(number_format($r->total_pendente, 2, ',', '.')); ?> - <?php echo e($r->unidade ?? '-'); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </tbody>
            </table>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            📋 Detalhamento por Produto
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Cód. Barras</th>
                        <th>Unidade</th>
                        <th>Total Pedido</th>
                        <th>Atendido</th>
                        <th>Pendente</th>
                        <th>Prv.Entrega</th>
                        <th>Necessario</th>
                        <th>Valor Orcamento</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $dados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>

                            <td><?php echo e($item->produto_nome); ?></td>

                            <td><?php echo e($item->codigo_barras); ?></td>

                            <td><?php echo e($item->unidade ?? '-'); ?></td>

                            <td><?php echo e(number_format($item->total_quantidade, 2, ',', '.')); ?></td>

                            <td><?php echo e(number_format($item->total_atendida, 2, ',', '.')); ?></td>

                            <td>
                                <strong>
                                    <?php echo e(number_format($item->total_pendente, 2, ',', '.')); ?>

                                </strong>
                            </td>
            
                            <!-- <td><?php echo e(number_format($item->estoque_disponivel, 2, ',', '.')); ?></td> -->
                            <td>
                                <?php if($item->previsao_entrega): ?>
                                    <?php
                                        $dataEntrega = \Carbon\Carbon::parse($item->previsao_entrega);
                                        $diasRestantes = now()->diffInDays($dataEntrega, false); // negativo se já passou
                                        // Calcula cor: quanto menor o prazo, mais vermelho
                                        if ($diasRestantes <= 0) {
                                            $cor = '#ff4d4d'; // vermelho forte (atrasado ou hoje)
                                        } elseif ($diasRestantes <= 3) {
                                            $cor = '#ff9999'; // vermelho claro
                                        } elseif ($diasRestantes <= 7) {
                                            $cor = '#ffcc66'; // laranja
                                        } elseif ($diasRestantes <= 14) {
                                            $cor = '#ffff99'; // amarelo
                                        } else {
                                            $cor = '#99ff99'; // verde
                                        }
                                    ?>
                                    <span style="background-color: <?php echo e($cor); ?>; padding: 0.25em 0.5em; border-radius: 0.25rem;">
                                        <?php echo e($dataEntrega->format('d/m/Y')); ?>

                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($item->necessidade_reposicao > 0): ?>
                                    <span class="badge bg-danger">
                                        <?php echo e(number_format($item->necessidade_reposicao, 2, ',', '.')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success">OK</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                R$ <?php echo e(number_format($item->valor_total, 2, ',', '.')); ?>

                            </td>

                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                Nenhum resultado encontrado
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <div class="p-3">
            <?php echo e($dados->links()); ?>

        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/relatorios/reposicao.blade.php ENDPATH**/ ?>