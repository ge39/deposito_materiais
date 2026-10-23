<?php $__env->startSection('content'); ?>

<style>
.bi-comissao-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}

.bi-comissao-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}

.bi-comissao-icon {
    font-size: 1.30rem;
    line-height: 1;
}

.bi-comissao-label {
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.bi-comissao-value {
    font-size: 1.28rem;
    font-weight: 700;
    line-height: 1.05;
}

.bi-comissao-detail {
    font-size: .78rem;
    text-decoration: none;
}

.bi-comissao-detail:hover {
    text-decoration: underline;
}

.modal-bi .modal-dialog {
    max-width: 1200px;
}

.modal-bi .modal-body {
    max-height: 72vh;
    overflow-y: auto;
}

.modal-bi table th {
    white-space: nowrap;
}
</style>


<div class="container py-4">

    
    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>
            <h2 class="mb-1">
                <i class="bi bi-people me-2"></i>
                Funcionários & Comissões
            </h2>

            <div class="text-muted">
                Produtividade, operações realizadas, desempenho e comissões.
            </div>
        </div>

        <div class="d-flex gap-2">

            <a
                href="<?php echo e(route('comissoes.regras.index')); ?>"
                class="btn btn-outline-primary"
            >
                <i class="bi bi-percent me-1"></i>
                Regras de Comissão
            </a>

            <a
                href="<?php echo e(route('bi.index')); ?>"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Central BI
            </a>

        </div>

    </div>


    
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?php echo e(route('bi.funcionarios-comissoes.index')); ?>"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Data inicial</label>

                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="<?php echo e($dataInicio->format('Y-m-d')); ?>"
                        >
                    </div>


                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Data final</label>

                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="<?php echo e($dataFim->format('Y-m-d')); ?>"
                        >
                    </div>


                    <div class="col-lg-4 col-md-6">
                        <label class="form-label">Vendedor</label>

                        <select
                            name="funcionario_id"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            <?php $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($vendedor->id); ?>"
                                    <?php if(
                                        $funcionarioId === (int) $vendedor->id
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($vendedor->nome); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>


                    <div class="col-lg-2 col-md-6">

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                Aplicar
                            </button>

                            <a
                                href="<?php echo e(route('bi.funcionarios-comissoes.index')); ?>"
                                class="btn btn-outline-secondary"
                            >
                                Limpar
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    
    <div class="row g-4 mb-4">

        <?php
            $cards = [
                [
                    'titulo' => 'Vendedores Ativos',
                    'valor' => number_format($vendedoresAtivos, 0, ',', '.'),
                    'icone' => 'bi-people',
                    'cor' => 'primary',
                    'modal' => 'modalVendedores',
                ],
                [
                    'titulo' => 'Vendas Finalizadas',
                    'valor' => number_format($vendasFinalizadas, 0, ',', '.'),
                    'icone' => 'bi-cart-check',
                    'cor' => 'success',
                    'modal' => 'modalVendas',
                ],
                [
                    'titulo' => 'Faturamento',
                    'valor' => 'R$ ' . number_format($faturamento, 2, ',', '.'),
                    'icone' => 'bi-cash-stack',
                    'cor' => 'success',
                    'modal' => 'modalFaturamento',
                ],
                [
                    'titulo' => 'Ticket Médio',
                    'valor' => 'R$ ' . number_format($ticketMedio, 2, ',', '.'),
                    'icone' => 'bi-receipt',
                    'cor' => 'info',
                    'modal' => 'modalTicket',
                ],
                [
                    'titulo' => 'Comissões Geradas',
                    'valor' => 'R$ ' . number_format($comissoesGeradas, 2, ',', '.'),
                    'icone' => 'bi-percent',
                    'cor' => 'primary',
                    'modal' => 'modalGeradas',
                ],
                [
                    'titulo' => 'Comissões Pendentes',
                    'valor' => 'R$ ' . number_format($comissoesPendentes, 2, ',', '.'),
                    'icone' => 'bi-hourglass-split',
                    'cor' => 'warning',
                    'modal' => 'modalPendentes',
                ],
                [
                    'titulo' => 'Comissões Pagas',
                    'valor' => 'R$ ' . number_format($comissoesPagas, 2, ',', '.'),
                    'icone' => 'bi-check-circle',
                    'cor' => 'success',
                    'modal' => 'modalPagas',
                ],
                [
                    'titulo' => 'Regras Ativas',
                    'valor' => number_format($regrasAtivas, 0, ',', '.'),
                    'icone' => 'bi-sliders',
                    'cor' => 'secondary',
                    'modal' => 'modalRegras',
                ],
            ];
        ?>


        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="col-lg-4 col-md-6">

                <div
                    class="card bi-comissao-card border-<?php echo e($card['cor']); ?> shadow-sm"
                >

                    <div class="card-body">

                        <i
                            class="bi <?php echo e($card['icone']); ?> bi-comissao-icon text-<?php echo e($card['cor']); ?>"
                        ></i>

                        <div class="bi-comissao-label">
                            <?php echo e($card['titulo']); ?>

                        </div>

                        <div class="bi-comissao-value">
                            <?php echo e($card['valor']); ?>

                        </div>

                        <a
                            href="#"
                            class="bi-comissao-detail text-<?php echo e($card['cor']); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#<?php echo e($card['modal']); ?>"
                        >
                            Ver detalhes
                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>

                    </div>

                </div>

            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>


    
    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-trophy text-warning me-2"></i>
                Ranking de Vendedores
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-hover mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket Médio</th>
                            <th class="text-end">Comissão</th>
                            <th class="text-end">Pendente</th>
                            <th class="text-end">Pago</th>
                            <th>Última venda</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $ranking; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>
                            <td><?php echo e($indice + 1); ?></td>

                            <td class="fw-semibold">
                                <?php echo e($linha->nome); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e($linha->quantidade_vendas); ?>

                            </td>

                            <td class="text-end">
                                R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                            </td>

                            <td class="text-end">
                                R$ <?php echo e(number_format($linha->ticket_medio, 2, ',', '.')); ?>

                            </td>

                            <td class="text-end text-primary fw-semibold">
                                R$ <?php echo e(number_format($linha->total_comissao, 2, ',', '.')); ?>

                            </td>

                            <td class="text-end text-warning fw-semibold">
                                R$ <?php echo e(number_format($linha->total_pendente, 2, ',', '.')); ?>

                            </td>

                            <td class="text-end text-success fw-semibold">
                                R$ <?php echo e(number_format($linha->total_pago, 2, ',', '.')); ?>

                            </td>

                            <td>
                                <?php if($linha->ultima_venda): ?>
                                    <?php echo e(\Carbon\Carbon::parse($linha->ultima_venda)->format('d/m/Y H:i')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td
                                colspan="9"
                                class="text-center text-muted py-4"
                            >
                                Nenhuma venda encontrada no período.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    
    <div class="row g-4 mb-4">

        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                        Evolução no Período
                    </h5>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-hover mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Competência</th>
                                    <th class="text-end">Vendas</th>
                                    <th class="text-end">Faturamento</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php $__empty_1 = true; $__currentLoopData = $mensal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                <tr>
                                    <td>
                                        <?php echo e(\Carbon\Carbon::createFromFormat(
                                                'Y-m',
                                                $linha->competencia
                                            )->format('m/Y')); ?>

                                    </td>

                                    <td class="text-end">
                                        <?php echo e($linha->quantidade_vendas); ?>

                                    </td>

                                    <td class="text-end fw-semibold">
                                        R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                    </td>
                                </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                <tr>
                                    <td
                                        colspan="3"
                                        class="text-center text-muted py-4"
                                    >
                                        Sem movimentação no período.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-percent text-success me-2"></i>
                        Situação das Comissões
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <small>Geradas</small>

                                    <div class="fw-bold text-primary">
                                        R$ <?php echo e(number_format($comissoesGeradas, 2, ',', '.')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <small>Pendentes</small>

                                    <div class="fw-bold text-warning">
                                        R$ <?php echo e(number_format($comissoesPendentes, 2, ',', '.')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <small>Pagas</small>

                                    <div class="fw-bold text-success">
                                        R$ <?php echo e(number_format($comissoesPagas, 2, ',', '.')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-body text-center">
                                    <small>Regras ativas</small>

                                    <div class="fw-bold text-secondary">
                                        <?php echo e($regrasAtivas); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    
    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Últimas Comissões
            </h5>

            <a
                href="<?php echo e(route('comissoes.regras.index')); ?>"
                class="btn btn-sm btn-outline-primary"
            >
                Gerenciar regras
            </a>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-hover mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Venda</th>
                            <th>Vendedor</th>
                            <th>Produto</th>
                            <th class="text-end">Base</th>
                            <th class="text-end">%</th>
                            <th class="text-end">Comissão</th>
                            <th>Status</th>
                            <th>Competência</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $ultimasComissoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comissao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>
                            <td>#<?php echo e($comissao->venda_id); ?></td>

                            <td><?php echo e($comissao->vendedor); ?></td>

                            <td><?php echo e($comissao->produto); ?></td>

                            <td class="text-end">
                                R$ <?php echo e(number_format($comissao->valor_base, 2, ',', '.')); ?>

                            </td>

                            <td class="text-end">
                                <?php echo e(number_format($comissao->percentual_aplicado, 2, ',', '.')); ?>%
                            </td>

                            <td class="text-end fw-bold">
                                R$ <?php echo e(number_format($comissao->valor_comissao, 2, ',', '.')); ?>

                            </td>

                            <td>
                                <?php echo e(ucfirst($comissao->status)); ?>

                            </td>

                            <td>
                                <?php echo e(\Carbon\Carbon::parse($comissao->data_competencia)->format('d/m/Y')); ?>

                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td
                                colspan="8"
                                class="text-center text-muted py-4"
                            >
                                Nenhuma comissão gerada no período.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>




<?php
    $modaisResumo = [
        [
            'id' => 'modalVendas',
            'titulo' => 'Vendas Finalizadas',
            'cor' => 'success',
            'icone' => 'bi-cart-check',
            'valor' => $vendasFinalizadas,
        ],
        [
            'id' => 'modalFaturamento',
            'titulo' => 'Faturamento',
            'cor' => 'success',
            'icone' => 'bi-cash-stack',
            'valor' => 'R$ ' . number_format($faturamento, 2, ',', '.'),
        ],
        [
            'id' => 'modalTicket',
            'titulo' => 'Ticket Médio',
            'cor' => 'info',
            'icone' => 'bi-receipt',
            'valor' => 'R$ ' . number_format($ticketMedio, 2, ',', '.'),
        ],
        [
            'id' => 'modalGeradas',
            'titulo' => 'Comissões Geradas',
            'cor' => 'primary',
            'icone' => 'bi-percent',
            'valor' => 'R$ ' . number_format($comissoesGeradas, 2, ',', '.'),
        ],
        [
            'id' => 'modalPendentes',
            'titulo' => 'Comissões Pendentes',
            'cor' => 'warning',
            'icone' => 'bi-hourglass-split',
            'valor' => 'R$ ' . number_format($comissoesPendentes, 2, ',', '.'),
        ],
        [
            'id' => 'modalPagas',
            'titulo' => 'Comissões Pagas',
            'cor' => 'success',
            'icone' => 'bi-check-circle',
            'valor' => 'R$ ' . number_format($comissoesPagas, 2, ',', '.'),
        ],
        [
            'id' => 'modalRegras',
            'titulo' => 'Regras Ativas',
            'cor' => 'secondary',
            'icone' => 'bi-sliders',
            'valor' => $regrasAtivas,
        ],
    ];
?>


<div class="modal fade modal-bi" id="modalVendedores" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="bi bi-people text-primary me-2"></i>
                    Vendedores Ativos
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Vendedor</th>
                                <th>User ID</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td><?php echo e($vendedor->nome); ?></td>
                                <td>#<?php echo e($vendedor->user_id); ?></td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td
                                    colspan="2"
                                    class="text-center text-muted"
                                >
                                    Nenhum vendedor ativo.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<?php $__currentLoopData = $modaisResumo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <div
        class="modal fade modal-bi"
        id="<?php echo e($modal['id']); ?>"
        tabindex="-1"
    >

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i
                            class="bi <?php echo e($modal['icone']); ?> text-<?php echo e($modal['cor']); ?> me-2"
                        ></i>

                        <?php echo e($modal['titulo']); ?>


                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-<?php echo e($modal['cor']); ?> mb-0">
                        Total:
                        <strong><?php echo e($modal['valor']); ?></strong>
                    </div>

                </div>

            </div>

        </div>

    </div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\bi\funcionarios-comissoes\index.blade.php ENDPATH**/ ?>