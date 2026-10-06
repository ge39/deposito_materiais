<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                <?php echo e($titulo); ?>

            </h1>

            <p class="text-muted mb-0">
                <?php echo e($subtitulo); ?>

            </p>
        </div>

        <a
            href="<?php echo e(route(
                'bi.produtos.index',
                $filtros
            )); ?>"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Produtos & Estoque
        </a>

    </div>

    <div class="alert alert-light border">
        <strong>
            <?php echo e(number_format(
                    $produtos->count(),
                    0,
                    ',',
                    '.'
                )); ?>

        </strong>
        produto(s) encontrado(s).
    </div>

    <div class="card shadow-sm">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Fornecedor</th>
                        <th>Marca</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Reservado</th>
                        <th class="text-end">Disponível</th>
                        <th class="text-end">Mínimo</th>
                        <th class="text-end">Vendido período</th>
                        <th class="text-end">Faturamento</th>
                        <th class="text-end">Valor estoque</th>
                    </tr>

                </thead>

                <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>
                                <strong>
                                    <?php echo e($produto->nome); ?>

                                </strong>
                            </td>

                            <td>
                                <?php echo e($produto->categoria ?? '—'); ?>

                            </td>

                            <td>
                                <?php echo e($produto->fornecedor ?? '—'); ?>

                            </td>

                            <td>
                                <?php echo e($produto->marca ?? '—'); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format(
                                        $produto->estoque_total,
                                        3,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format(
                                        $produto->estoque_reservado,
                                        3,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end fw-semibold">
                                <?php echo e(number_format(
                                        $produto->estoque_disponivel,
                                        3,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format(
                                        $produto->estoque_minimo,
                                        3,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format(
                                        $produto->quantidade_vendida,
                                        3,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end">
                                R$
                                <?php echo e(number_format(
                                        $produto->faturamento,
                                        2,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                            <td class="text-end">
                                R$
                                <?php echo e(number_format(
                                        $produto->valor_estoque,
                                        2,
                                        ',',
                                        '.'
                                    )); ?>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td
                                colspan="11"
                                class="text-center text-muted py-5">

                                Nenhum produto encontrado.

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/produtos/detalhe.blade.php ENDPATH**/ ?>