<?php $__env->startSection('content'); ?>

<style>
.bi-caixa-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}

.bi-caixa-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}

.bi-caixa-icon {
    font-size: 1.30rem;
    line-height: 1;
}

.bi-caixa-label {
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.bi-caixa-value {
    font-size: 1.28rem;
    font-weight: 700;
    line-height: 1.05;
}

.bi-caixa-detail {
    font-size: .78rem;
    text-decoration: none;
}

.bi-caixa-detail:hover {
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
                <i class="bi bi-cash-coin me-2"></i>
                Caixa
            </h2>

            <div class="text-muted">
                Entradas, sa&iacute;das, sangrias, diferen&ccedil;as, fechamentos e auditoria.
            </div>
        </div>

        <a
            href="<?php echo e(route('bi.index')); ?>"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>


    

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET" action="<?php echo e(route('bi.caixa.index')); ?>">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data inicial</label>
                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="<?php echo e($inicio->format('Y-m-d')); ?>"
                        >
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data final</label>
                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="<?php echo e($fim->format('Y-m-d')); ?>"
                        >
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">
                            <option value="">Todos</option>

                            <?php $__currentLoopData = $statusDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($opcao); ?>"
                                    <?php if($status === $opcao): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($opcao); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Operador</label>

                        <select name="user_id" class="form-select">
                            <option value="">Todos</option>

                            <?php $__currentLoopData = $operadores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operador): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($operador->id); ?>"
                                    <?php if($userId === (int) $operador->id): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($operador->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Terminal</label>

                        <select name="terminal" class="form-select">
                            <option value="">Todos</option>

                            <?php $__currentLoopData = $terminais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $terminalOpcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($terminalOpcao); ?>"
                                    <?php if($terminal === $terminalOpcao): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($terminalOpcao); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                Aplicar
                            </button>

                            <a
                                href="<?php echo e(route('bi.caixa.index')); ?>"
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


    

    <div class="row g-4">

        <?php
            $cards = [
                [
                    'titulo' => 'Entradas',
                    'valor' => 'R$ ' . number_format($totalEntradas, 2, ',', '.'),
                    'icone' => 'bi-arrow-down-circle',
                    'cor' => 'success',
                    'modal' => 'modalEntradas',
                ],
                [
                    'titulo' => 'Saídas',
                    'valor' => 'R$ ' . number_format($totalSaidas, 2, ',', '.'),
                    'icone' => 'bi-arrow-up-circle',
                    'cor' => 'danger',
                    'modal' => 'modalSaidas',
                ],
                [
                    'titulo' => 'Movimento Líquido',
                    'valor' => 'R$ ' . number_format($movimentoLiquido, 2, ',', '.'),
                    'icone' => 'bi-arrow-left-right',
                    'cor' => 'primary',
                    'modal' => 'modalMovimentoLiquido',
                ],
                [
                    'titulo' => 'Sangrias',
                    'valor' => 'R$ ' . number_format($totalSangrias, 2, ',', '.'),
                    'icone' => 'bi-cash-stack',
                    'cor' => 'warning',
                    'modal' => 'modalSangrias',
                ],
                [
                    'titulo' => 'Entradas Manuais',
                    'valor' => 'R$ ' . number_format($entradasManuais, 2, ',', '.'),
                    'icone' => 'bi-plus-circle',
                    'cor' => 'info',
                    'modal' => 'modalEntradasManuais',
                ],
                [
                    'titulo' => 'Caixas Abertos',
                    'valor' => $caixasAbertos,
                    'icone' => 'bi-unlock',
                    'cor' => 'primary',
                    'modal' => 'modalCaixasAbertos',
                ],
                [
                    'titulo' => 'Caixas Fechados',
                    'valor' => $caixasFechados,
                    'icone' => 'bi-lock',
                    'cor' => 'secondary',
                    'modal' => 'modalCaixasFechados',
                ],
                [
                    'titulo' => 'Total Sistema',
                    'valor' => 'R$ ' . number_format($totalSistema, 2, ',', '.'),
                    'icone' => 'bi-pc-display',
                    'cor' => 'primary',
                    'modal' => 'modalTotalSistema',
                ],
                [
                    'titulo' => 'Total Físico',
                    'valor' => 'R$ ' . number_format($totalFisico, 2, ',', '.'),
                    'icone' => 'bi-cash',
                    'cor' => 'success',
                    'modal' => 'modalTotalFisico',
                ],
                [
                    'titulo' => 'Diferença',
                    'valor' => 'R$ ' . number_format($diferencaTotal, 2, ',', '.'),
                    'icone' => 'bi-calculator',
                    'cor' => abs($diferencaTotal) > 0.01 ? 'danger' : 'success',
                    'modal' => 'modalDiferenca',
                ],
                [
                    'titulo' => 'Caixas Inconsistentes',
                    'valor' => $caixasInconsistentes,
                    'icone' => 'bi-exclamation-triangle',
                    'cor' => 'danger',
                    'modal' => 'modalInconsistentes',
                ],
                [
                    'titulo' => 'Acareação / Atenção',
                    'valor' => $totalAlertas,
                    'icone' => 'bi-shield-exclamation',
                    'cor' => $totalAlertas > 0 ? 'danger' : 'success',
                    'modal' => 'modalAcareacao',
                ],
            ];
        ?>


        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="col-lg-4 col-md-6">

                <div class="card bi-caixa-card border-<?php echo e($card['cor']); ?> shadow-sm">

                    <div class="card-body">

                        <i class="bi <?php echo e($card['icone']); ?> bi-caixa-icon text-<?php echo e($card['cor']); ?>"></i>

                        <div class="bi-caixa-label">
                            <?php echo e($card['titulo']); ?>

                        </div>

                        <div class="bi-caixa-value">
                            <?php echo e($card['valor']); ?>

                        </div>

                        <a
                            href="#"
                            class="bi-caixa-detail text-<?php echo e($card['cor']); ?>"
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

</div>




<div class="modal fade modal-bi" id="modalEntradas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-down-circle text-success me-2"></i>
                    Entradas do Caixa
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-success">
                    Total:
                    <strong>
                        R$ <?php echo e(number_format($totalEntradas, 2, ',', '.')); ?>

                    </strong>
                </div>


                <h6>Resumo por forma de pagamento</h6>

                <div class="table-responsive mb-4">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Forma</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php $__currentLoopData = $formasPagamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($linha->forma); ?></td>
                                <td class="text-end"><?php echo e($linha->quantidade); ?></td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->valor_total, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>


                <h6>Lan&ccedil;amentos</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Caixa</th>
                                <th>Terminal</th>
                                <th>Usu&aacute;rio</th>
                                <th>Tipo</th>
                                <th>Forma</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $entradasDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_movimentacao)->format('d/m/Y H:i')); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e($linha->usuario_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->tipo); ?></td>
                                <td><?php echo e($linha->forma_pagamento ?: '—'); ?></td>
                                <td class="text-end fw-bold">
                                    R$ <?php echo e(number_format($linha->valor, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma entrada encontrada.
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




<div class="modal fade modal-bi" id="modalSaidas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-up-circle text-danger me-2"></i>
                    Sa&iacute;das do Caixa
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-danger">
                    Total:
                    <strong>
                        R$ <?php echo e(number_format($totalSaidas, 2, ',', '.')); ?>

                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Caixa</th>
                                <th>Terminal</th>
                                <th>Usu&aacute;rio</th>
                                <th>Tipo</th>
                                <th>Forma</th>
                                <th>Observa&ccedil;&atilde;o</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $saidasDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_movimentacao)->format('d/m/Y H:i')); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e($linha->usuario_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->tipo); ?></td>
                                <td><?php echo e($linha->forma_pagamento ?: '—'); ?></td>
                                <td><?php echo e($linha->observacao ?: '—'); ?></td>

                                <td class="text-end fw-bold text-danger">
                                    R$ <?php echo e(number_format($linha->valor, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Nenhuma sa&iacute;da encontrada.
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




<div class="modal fade modal-bi" id="modalMovimentoLiquido" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Movimento L&iacute;quido
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <small>Entradas</small>
                                <div class="fw-bold text-success">
                                    R$ <?php echo e(number_format($totalEntradas, 2, ',', '.')); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <small>Sa&iacute;das</small>
                                <div class="fw-bold text-danger">
                                    R$ <?php echo e(number_format($totalSaidas, 2, ',', '.')); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <small>L&iacute;quido</small>
                                <div class="fw-bold text-primary">
                                    R$ <?php echo e(number_format($movimentoLiquido, 2, ',', '.')); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <h6>Evolu&ccedil;&atilde;o por dia</h6>

                <div class="table-responsive mb-4">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th class="text-end">Entradas</th>
                                <th class="text-end">Sa&iacute;das</th>
                                <th class="text-end">L&iacute;quido</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php $__currentLoopData = $evolucao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data)->format('d/m/Y')); ?></td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->entradas, 2, ',', '.')); ?>

                                </td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->saidas, 2, ',', '.')); ?>

                                </td>
                                <td class="text-end fw-bold">
                                    R$ <?php echo e(number_format($linha->liquido, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>

                    </table>

                </div>


                <h6>Movimenta&ccedil;&otilde;es por tipo</h6>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php $__currentLoopData = $movimentacoesPorTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($linha->tipo_exibicao); ?></td>
                                <td class="text-end"><?php echo e($linha->quantidade); ?></td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->valor_total, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalSangrias" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Sangrias
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-warning">
                    <?php echo e($qtdSangrias); ?> opera&ccedil;&otilde;es —
                    <strong>
                        R$ <?php echo e(number_format($totalSangrias, 2, ',', '.')); ?>

                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Opera&ccedil;&atilde;o</th>
                                <th>Caixa</th>
                                <th>PDV</th>
                                <th>Usu&aacute;rio</th>
                                <th>Motivo</th>
                                <th class="text-end">Antes</th>
                                <th class="text-end">Sangria</th>
                                <th class="text-end">Depois</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $sangrias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td>
                                    <?php echo e($linha->created_at
                                        ? \Carbon\Carbon::parse($linha->created_at)->format('d/m/Y H:i')
                                        : '—'); ?>

                                </td>

                                <td><?php echo e($linha->codigo_operacao ?: '#' . $linha->id); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->numero_pdv); ?></td>
                                <td><?php echo e($linha->usuario_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->motivo); ?></td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->saldo_antes, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end fw-bold text-danger">
                                    R$ <?php echo e(number_format($linha->valor, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->saldo_depois, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Nenhuma sangria encontrada.
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




<div class="modal fade modal-bi" id="modalEntradasManuais" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Entradas Manuais</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info">
                    <?php echo e($qtdEntradasManuais); ?> lan&ccedil;amentos —
                    <strong>
                        R$ <?php echo e(number_format($entradasManuais, 2, ',', '.')); ?>

                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Caixa</th>
                                <th>Terminal</th>
                                <th>Usu&aacute;rio</th>
                                <th>Forma</th>
                                <th>Observa&ccedil;&atilde;o</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $entradasManuaisDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_movimentacao)->format('d/m/Y H:i')); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e($linha->usuario_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->forma_pagamento ?: '—'); ?></td>
                                <td><?php echo e($linha->observacao ?: '—'); ?></td>

                                <td class="text-end fw-bold">
                                    R$ <?php echo e(number_format($linha->valor, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma entrada manual encontrada.
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




<div class="modal fade modal-bi" id="modalCaixasAbertos" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Caixas Abertos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Caixa</th>
                                <th>Operador</th>
                                <th>Terminal</th>
                                <th>Abertura</th>
                                <th class="text-end">Fundo</th>
                                <th class="text-end">Valor Abertura</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $caixasAbertosDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td>#<?php echo e($linha->id); ?></td>
                                <td><?php echo e($linha->operador_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_abertura)->format('d/m/Y H:i')); ?></td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->fundo_troco, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->valor_abertura, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhum caixa aberto.
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




<div class="modal fade modal-bi" id="modalCaixasFechados" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Caixas Fechados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Caixa</th>
                                <th>Operador</th>
                                <th>Terminal</th>
                                <th>Abertura</th>
                                <th>Fechamento</th>
                                <th>Status</th>
                                <th class="text-end">Valor Fechamento</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $caixasFechadosDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td>#<?php echo e($linha->id); ?></td>
                                <td><?php echo e($linha->operador_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_abertura)->format('d/m/Y H:i')); ?></td>

                                <td>
                                    <?php echo e($linha->data_fechamento
                                        ? \Carbon\Carbon::parse($linha->data_fechamento)->format('d/m/Y H:i')
                                        : '—'); ?>

                                </td>

                                <td><?php echo e($linha->status); ?></td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->valor_fechamento ?? 0, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhum caixa fechado.
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




<?php $__currentLoopData = [
    ['id' => 'modalTotalSistema', 'titulo' => 'Total Sistema', 'campo' => 'total_sistema'],
    ['id' => 'modalTotalFisico', 'titulo' => 'Total Físico', 'campo' => 'total_fisico'],
    ['id' => 'modalDiferenca', 'titulo' => 'Diferenças de Caixa', 'campo' => 'diferenca'],
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalAuditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<div class="modal fade modal-bi" id="<?php echo e($modalAuditoria['id']); ?>" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <?php echo e($modalAuditoria['titulo']); ?>

                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Auditoria</th>
                                <th>Caixa</th>
                                <th>Terminal</th>
                                <th>Auditor</th>
                                <th class="text-end">Sistema</th>
                                <th class="text-end">F&iacute;sico</th>
                                <th class="text-end">Diferen&ccedil;a</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr class="<?php echo e($linha->status === 'inconsistente' ? 'table-danger' : ''); ?>">

                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($linha->data_auditoria)->format('d/m/Y H:i')); ?>

                                </td>

                                <td><?php echo e($linha->codigo_auditoria); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e($linha->auditor_nome ?: '—'); ?></td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->total_sistema, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->total_fisico, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end fw-bold <?php echo e(abs($linha->diferenca) > 0.01 ? 'text-danger' : 'text-success'); ?>">
                                    R$ <?php echo e(number_format($linha->diferenca, 2, ',', '.')); ?>

                                </td>

                                <td><?php echo e($linha->status); ?></td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Nenhuma auditoria encontrada.
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

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>




<div class="modal fade modal-bi" id="modalInconsistentes" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Caixas Inconsistentes
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <h6>Caixas</h6>

                <div class="table-responsive mb-4">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Caixa</th>
                                <th>Operador</th>
                                <th>Terminal</th>
                                <th>Abertura</th>
                                <th>Observa&ccedil;&atilde;o</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $caixasInconsistentesDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr class="table-danger">
                                <td>#<?php echo e($linha->id); ?></td>
                                <td><?php echo e($linha->operador_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_abertura)->format('d/m/Y H:i')); ?></td>
                                <td><?php echo e($linha->observacao_divergencia ?: '—'); ?></td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhum caixa inconsistente.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <h6>Auditorias inconsistentes</h6>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Caixa</th>
                                <th>Auditoria</th>
                                <th class="text-end">Sistema</th>
                                <th class="text-end">F&iacute;sico</th>
                                <th class="text-end">Diferen&ccedil;a</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $auditorias->where('status', 'inconsistente'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr class="table-danger">
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->codigo_auditoria); ?></td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->total_sistema, 2, ',', '.')); ?>

                                </td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->total_fisico, 2, ',', '.')); ?>

                                </td>
                                <td class="text-end fw-bold">
                                    R$ <?php echo e(number_format($linha->diferenca, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhuma auditoria inconsistente.
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




<div class="modal fade modal-bi" id="modalAcareacao" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Acarea&ccedil;&atilde;o / Aten&ccedil;&atilde;o
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <small>Movimentos sem tipo</small>
                                <div class="fw-bold">
                                    <?php echo e($movimentacoesSemTipo); ?>

                                </div>
                                <small>
                                    R$ <?php echo e(number_format($valorSemTipo, 2, ',', '.')); ?>

                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <small>Caixas sem status</small>
                                <div class="fw-bold">
                                    <?php echo e($caixasSemStatus); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <small>Auditorias inconsistentes</small>
                                <div class="fw-bold">
                                    <?php echo e($auditoriasInconsistentes); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <h6>Movimenta&ccedil;&otilde;es sem classifica&ccedil;&atilde;o</h6>

                <div class="table-responsive mb-4">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Caixa</th>
                                <th>Terminal</th>
                                <th>Usu&aacute;rio</th>
                                <th>Forma</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $movimentosSemTipoDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr class="table-warning">
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_movimentacao)->format('d/m/Y H:i')); ?></td>
                                <td>#<?php echo e($linha->caixa_id); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e($linha->usuario_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->forma_pagamento ?: '—'); ?></td>
                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->valor, 2, ',', '.')); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhum movimento sem tipo.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <h6>Caixas sem status</h6>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Caixa</th>
                                <th>Operador</th>
                                <th>Terminal</th>
                                <th>Abertura</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $caixasSemStatusDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr class="table-warning">
                                <td>#<?php echo e($linha->id); ?></td>
                                <td><?php echo e($linha->operador_nome ?: '—'); ?></td>
                                <td><?php echo e($linha->terminal ?: '—'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($linha->data_abertura)->format('d/m/Y H:i')); ?></td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Nenhum caixa sem status.
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/caixa/index.blade.php ENDPATH**/ ?>