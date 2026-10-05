<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-exclamation-triangle me-2"></i>
                Produtos com estoque zero

            </h1>

            <p class="text-muted mb-0">

                Relação para análise e futura tomada de decisão.

            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">

            <a
                href="<?php echo e(route(
                    'bi.produtos.estoque-zero.pdf',
                    request()->query()
                )); ?>"
                class="btn btn-outline-danger">

                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF

            </a>


            <a
                href="<?php echo e(route(
                    'bi.produtos.estoque-zero.planilha',
                    request()->query()
                )); ?>"
                class="btn btn-outline-success">

                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                Exportar planilha

            </a>


            <a
                href="<?php echo e(route('bi.produtos.index')); ?>"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>
                Produtos & Estoque

            </a>

        </div>

    </div>


    <div class="alert alert-warning">

        <strong>
            <?php echo e(number_format(
                    $produtos->total(),
                    0,
                    ',',
                    '.'
                )); ?>

            produto(s)
        </strong>

        com estoque zerado encontrados.

        Estes registros são exibidos para análise,
        porém não entram no valor financeiro do estoque.

    </div>


    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?php echo e(route('bi.produtos.estoque-zero')); ?>"
                class="row g-3 align-items-end">

                <div class="col-lg-4">

                    <label
                        class="form-label"
                        for="q">

                        Produto / SKU / código

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="q"
                        name="q"
                        value="<?php echo e($filtros['q'] ?? ''); ?>"
                        placeholder="Pesquisar produto">

                </div>


                <div class="col-lg-3">

                    <label
                        class="form-label"
                        for="categoria_id">

                        Categoria

                    </label>

                    <select
                        class="form-select"
                        id="categoria_id"
                        name="categoria_id">

                        <option value="">
                            Todas
                        </option>

                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <option
                                value="<?php echo e($categoria->id); ?>"
                                <?php if(
                                    (string) (
                                        $filtros['categoria_id']
                                        ?? ''
                                    )
                                    ===
                                    (string) $categoria->id
                                ): echo 'selected'; endif; ?>>

                                <?php echo e($categoria->nome); ?>


                            </option>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>

                </div>


                <div class="col-lg-3">

                    <label
                        class="form-label"
                        for="ordenacao">

                        Ordenação

                    </label>

                    <select
                        class="form-select"
                        id="ordenacao"
                        name="ordenacao">

                        <option
                            value="nome"
                            <?php if(
                                ($filtros['ordenacao'] ?? 'nome')
                                === 'nome'
                            ): echo 'selected'; endif; ?>>

                            Produto

                        </option>

                        <option
                            value="ultima_venda"
                            <?php if(
                                ($filtros['ordenacao'] ?? '')
                                === 'ultima_venda'
                            ): echo 'selected'; endif; ?>>

                            Última venda

                        </option>

                        <option
                            value="estoque_minimo"
                            <?php if(
                                ($filtros['ordenacao'] ?? '')
                                === 'estoque_minimo'
                            ): echo 'selected'; endif; ?>>

                            Maior estoque mínimo

                        </option>

                        <option
                            value="vendido"
                            <?php if(
                                ($filtros['ordenacao'] ?? '')
                                === 'vendido'
                            ): echo 'selected'; endif; ?>>

                            Mais vendido historicamente

                        </option>

                    </select>

                </div>


                <div class="col-lg-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100">

                        <i class="bi bi-funnel me-1"></i>
                        Filtrar

                    </button>

                </div>

            </form>

        </div>

    </div>


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th class="text-end">Estoque</th>
                            <th class="text-end">Mínimo</th>
                            <th>Última venda</th>
                            <th class="text-end">Dias sem venda</th>
                            <th>Promoção</th>
                            <th>Situação</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>

                                    <div class="fw-semibold">
                                        <?php echo e($produto->nome); ?>

                                    </div>

                                    <?php if($produto->sku): ?>

                                        <div class="small text-muted">
                                            SKU: <?php echo e($produto->sku); ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?php echo e($produto->categoria
                                        ?? 'Sem categoria'); ?>

                                </td>


                                <td class="text-end fw-semibold text-danger">

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


                                <td>

                                    <?php if($produto->ultima_venda): ?>

                                        <?php echo e(\Carbon\Carbon::parse(
                                                $produto->ultima_venda
                                            )->format('d/m/Y')); ?>


                                    <?php else: ?>

                                        <span class="text-muted">
                                            Nunca
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td class="text-end">

                                    <?php if(
                                        $produto->dias_sem_venda
                                        === null
                                    ): ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php else: ?>

                                        <?php echo e(number_format(
                                                $produto->dias_sem_venda,
                                                0,
                                                ',',
                                                '.'
                                            )); ?>


                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if(
                                        (int) $produto->em_promocao
                                        === 1
                                    ): ?>

                                        <span class="badge text-bg-success">
                                            Sim
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-secondary">
                                            Não
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <span class="badge text-bg-warning">

                                        <?php echo e($produto->situacao); ?>


                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted py-5">

                                    Nenhum produto encontrado
                                    com os filtros informados.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <?php if($produtos->hasPages()): ?>

            <div class="card-footer">

                <?php echo e($produtos->links()); ?>


            </div>

        <?php endif; ?>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/produtos/estoque-zero.blade.php ENDPATH**/ ?>