<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div
        class="d-flex justify-content-between
               align-items-center mb-4"
    >
        <div>
            <h1 class="h3 mb-1">
                Regras de Comissão
            </h1>

            <div class="text-muted">
                Configuração das regras comerciais
                dos vendedores.
            </div>
        </div>

        <a
            href="<?php echo e(route('comissoes.regras.create')); ?>"
            class="btn btn-primary"
        >
            Nova regra
        </a>
    </div>


    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Escopo</th>
                            <th>Referência</th>
                            <th>Percentual</th>
                            <th>Vigência</th>
                            <th>Status</th>
                            <th class="text-end">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $regras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>
                                <?php echo e($regra->funcionario?->nome ?? '-'); ?>

                            </td>

                            <td>
                                <?php echo e(ucfirst($regra->escopo)); ?>

                            </td>

                            <td>
                                <?php if($regra->escopo === 'produto'): ?>
                                    <?php echo e($regra->produto?->nome ?? '-'); ?>


                                <?php elseif($regra->escopo === 'categoria'): ?>
                                    <?php echo e($regra->categoria?->nome ?? '-'); ?>


                                <?php else: ?>
                                    Todos os produtos
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if(
                                    $regra->escopo === 'produto'
                                    && $regra->usar_percentual_produto
                                ): ?>
                                    Percentual do produto
                                <?php else: ?>
                                    <?php echo e(number_format(
                                            (float) $regra->percentual,
                                            2,
                                            ',',
                                            '.'
                                        )); ?>%
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo e($regra->vigencia_inicio
                                        ? $regra->vigencia_inicio
                                            ->format('d/m/Y')
                                        : 'Sem início'); ?>


                                até

                                <?php echo e($regra->vigencia_fim
                                        ? $regra->vigencia_fim
                                            ->format('d/m/Y')
                                        : 'Sem fim'); ?>

                            </td>

                            <td>
                                <?php if($regra->ativo): ?>
                                    <span class="badge bg-success">
                                        Ativa
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        Inativa
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">

                                <a
                                    href="<?php echo e(route(
                                            'comissoes.regras.edit',
                                            $regra
                                        )); ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Editar
                                </a>

                                <form
                                    method="POST"
                                    action="<?php echo e(route(
                                            'comissoes.regras.destroy',
                                            $regra
                                        )); ?>"
                                    class="d-inline"
                                    onsubmit="
                                        return confirm(
                                            'Deseja excluir/desativar esta regra?'
                                        );
                                    "
                                >
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        Excluir
                                    </button>
                                </form>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >
                                Nenhuma regra de comissão cadastrada.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>


    <div class="mt-3">
        <?php echo e($regras->links()); ?>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\comissoes\regras\index.blade.php ENDPATH**/ ?>