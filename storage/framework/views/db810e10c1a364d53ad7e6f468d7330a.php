


<main class="container-fluid px-4 mt-4">
    <?php $__env->startSection('content'); ?>
</main>

<div class="container-fluid">

    <h4 class="mb-3">Divergências de Estoque</h4>

    <form method="GET" class="card card-body mb-3">
        <div class="row align-items-end">

            <div class="col-md-2 mb-2">
                <label>Produto</label>
                <input type="text" name="produto" class="form-control"
                       value="<?php echo e(request('produto')); ?>"
                       placeholder="Buscar por produto">
            </div>

            <div class="col-md-2 mb-2">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="venda" <?php echo e(request('tipo') == 'venda' ? 'selected' : ''); ?>>Venda</option>
                    <option value="inventario" <?php echo e(request('tipo') == 'inventario' ? 'selected' : ''); ?>>Inventário</option>
                    <option value="ajuste_manual" <?php echo e(request('tipo') == 'ajuste_manual' ? 'selected' : ''); ?>>Ajuste Manual</option>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <label>Data Inicial</label>
               <input type="date"
                name="data_inicial"
                class="form-control"
                value="<?php echo e(request('data_inicial', now()->format('Y-m-d'))); ?>">
            </div>

            <div class="col-md-2 mb-2">
                <label>Data Final</label>
                <input type="date"
            name="data_final"
            class="form-control"
            value="<?php echo e(request('data_final', now()->format('Y-m-d'))); ?>">
            </div>

            <div class="col-md-3 mb-2 ms-auto d-flex align-items-end justify-content-end gap-2">

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-search"></i>
                    Filtrar
                </button>

                <a href="<?php echo e(route('estoque-divergencias.index')); ?>"
                class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-clockwise"></i>
                    Limpar
                </a>

                <a href="<?php echo e(route('estoque-divergencias.pdf', request()->query())); ?>"
                target="_blank"
                class="btn btn-danger"
                style="width: 120px;">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF
                </a>
            </div>

        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
    <thead class="thead-light">
        <tr>
            <th style="width: 40px;"></th>
            <th>Produto</th>
            <th>Ocorrências</th>
            <th>Total Solicitado</th>
            <th>Total Atendido</th>
            <th>Total Diferença</th>
            <th>Última Data</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $divergencias->groupBy('produto_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produtoId => $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $primeira = $grupo->first();
                $produtoNome = $primeira->produto->nome ?? 'Produto não encontrado';

                $totalSolicitado = $grupo->sum('quantidade_solicitada');
                $totalAtendido = $grupo->sum('quantidade_atendida');
                $totalDiferenca = $grupo->sum('diferenca');

                $ultimaData = optional($grupo->max('created_at'))->format('d/m/Y H:i');
                $linhaId = 'detalhes-produto-' . $produtoId;
            ?>

            <tr class="linha-resumo" data-target="<?php echo e($linhaId); ?>">
                <td class="text-center fw-bold text-primary">
                    <span class="icone-toggle">+</span>
                </td>

                <td>
                    <strong><?php echo e($produtoNome); ?></strong>
                </td>

                <td><?php echo e($grupo->count()); ?></td>

                <td><?php echo e(number_format($totalSolicitado, 3, ',', '.')); ?></td>

                <td><?php echo e(number_format($totalAtendido, 3, ',', '.')); ?></td>

                <td class="text-danger fw-bold">
                    <?php echo e(number_format($totalDiferenca, 3, ',', '.')); ?>

                </td>

                <td><?php echo e($ultimaData); ?></td>

                <td>
                    <button type="button" class="btn btn-sm btn-success" disabled>
                        Gerar Pedido
                    </button>
                </td>
            </tr>

            <tr id="<?php echo e($linhaId); ?>" class="linha-detalhes">
                <td colspan="8" class="p-0">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Venda</th>
                                <th>Caixa</th>
                                <th>Solicitado</th>
                                <th>Atendido</th>
                                <th>Diferença</th>
                                <th>Tipo</th>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $grupo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $divergencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($divergencia->id); ?></td>
                                    <td><?php echo e($divergencia->venda_id ?? '-'); ?></td>
                                    <td><?php echo e($divergencia->caixa_id ?? '-'); ?></td>
                                    <td><?php echo e(number_format($divergencia->quantidade_solicitada, 3, ',', '.')); ?></td>
                                    <td><?php echo e(number_format($divergencia->quantidade_atendida, 3, ',', '.')); ?></td>
                                    <td class="text-danger fw-bold">
                                        <?php echo e(number_format($divergencia->diferenca, 3, ',', '.')); ?>

                                    </td>
                                    <td><?php echo e(ucfirst($divergencia->tipo)); ?></td>
                                    <td><?php echo e($divergencia->usuario->name ?? '-'); ?></td>
                                    <td><?php echo e(optional($divergencia->created_at)->format('d/m/Y H:i')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('estoque-divergencias.show', $divergencia->id)); ?>"
                                           class="btn btn-sm btn-info">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </td>
            </tr>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Nenhuma divergência encontrada.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
    .linha-resumo {
        cursor: pointer;
    }

    .linha-resumo:hover {
        background-color: #f1f7ff;
    }

    .linha-detalhes {
        display: none;
        background-color: #fff;
    }

    .icone-toggle {
        display: inline-block;
        width: 18px;
        font-size: 18px;
        line-height: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.linha-resumo').forEach(function (linha) {
            linha.addEventListener('click', function () {
                const targetId = this.dataset.target;
                const detalhes = document.getElementById(targetId);
                const icone = this.querySelector('.icone-toggle');

                if (!detalhes) {
                    return;
                }

                if (detalhes.style.display === 'table-row') {
                    detalhes.style.display = 'none';
                    icone.textContent = '+';
                } else {
                    detalhes.style.display = 'table-row';
                    icone.textContent = '−';
                }
            });
        });
    });
</script>
        </div>
    </div>

    <div class="mt-3">
        <?php echo e($divergencias->links()); ?>

    </div>

</div>
<style>
    .linha-resumo {
        cursor: pointer;
        transition: .2s;
    }

    .linha-resumo:hover {
        background: #eef5ff;
    }

    .linha-detalhes {
        display: none;
        background: #fcfcfc;
    }

    .icone-toggle{
        width:20px;
        display:inline-block;
        font-weight:bold;
        color:#0d6efd;
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/estoque_divergencias/index.blade.php ENDPATH**/ ?>