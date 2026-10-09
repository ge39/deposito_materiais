<?php $__env->startSection('content'); ?>

<style>
.bi07-card {
    height:142px;
    min-height:142px;
    border-width:2px;
}
.bi07-card .card-body {
    height:100%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:space-between;
    text-align:center;
    padding:.55rem 1rem .6rem;
}
.bi07-icon {font-size:1.3rem}
.bi07-title {
    font-size:.77rem;
    font-weight:700;
    text-transform:uppercase;
}
.bi07-number {font-size:1.25rem;font-weight:700}
.bi07-link {font-size:.78rem;text-decoration:none}
.bi07-modal .modal-dialog {max-width:1450px}
.bi07-modal .modal-body {max-height:75vh;overflow-y:auto}
.bi07-modal th {white-space:nowrap;font-size:.78rem}
.bi07-modal td {white-space:nowrap;font-size:.82rem}
.bi07-chart-bar {height:18px;min-width:2px}
</style>

<?php
    $moeda = fn($v) =>
        'R$ '.number_format((float)$v,2,',','.');

    $numero = fn($v) =>
        number_format((float)$v,0,',','.');

    $pct = fn($v) =>
        number_format((float)$v,2,',','.').'%';

    $dataBR = fn($v) =>
        $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : 'Sem registro';

    $atrasoBR = function($dias) {
        $dias = (int)$dias;
        if ($dias <= 0) return 'Em dia / sem atraso';

        $meses = intdiv($dias,30);
        $resto = $dias % 30;

        return $meses > 0
            ? $meses.' mes(es), '.$resto.' dia(s)'
            : $dias.' dia(s)';
    };

    $formatarCard = function($card) use ($moeda,$numero,$pct) {
        if ($card['tipo'] === 'moeda') return $moeda($card['valor']);
        if ($card['tipo'] === 'percentual') return $pct($card['valor']);
        if ($card['tipo'] === 'score') {
            return $card['valor'] === null
                ? 'Nao avaliado'
                : number_format($card['valor'],1,',','.').' / 10';
        }
        return $numero($card['valor']);
    };
?>

<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-wallet2 me-2"></i>
                BI Financeiro - Carteira
            </h2>
            <div class="text-muted">
                Inteligencia de credito, recebimentos e inadimplencia.
            </div>
        </div>

        <a href="<?php echo e(route('bi.index')); ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Central BI
        </a>
    </div>

    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="<?php echo e(route('bi.financeiro.index')); ?>">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Data inicial</label>
                        <input class="form-control"
                               type="date"
                               name="data_inicio"
                               value="<?php echo e($inicio->format('Y-m-d')); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Data final</label>
                        <input class="form-control"
                               type="date"
                               name="data_fim"
                               value="<?php echo e($fim->format('Y-m-d')); ?>">
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit"
                                class="btn btn-primary flex-grow-1">
                            Aplicar
                        </button>
                        <a href="<?php echo e(route('bi.financeiro.index')); ?>"
                           class="btn btn-outline-secondary">
                            Limpar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    
    <div class="row g-4 mb-4">

        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-lg-4 col-md-6">
            <div class="card border-<?php echo e($card['cor']); ?> bi07-card shadow-sm">
                <div class="card-body">

                    <i class="bi <?php echo e($card['icone']); ?> bi07-icon text-<?php echo e($card['cor']); ?>"></i>

                    <div class="bi07-title">
                        <?php echo e($card['titulo']); ?>

                    </div>

                    <div class="bi07-number">
                        <?php echo e($formatarCard($card)); ?>

                    </div>

                    <a class="bi07-link text-<?php echo e($card['cor']); ?>"
                       href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#bi07Modal<?php echo e($loop->index); ?>">
                        Ver detalhes
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>

                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

    

    <div class="row g-4">

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">
                    Participacao da Carteira nas Vendas
                </div>

                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold text-primary">
                            <?php echo e($pct($participacaoCarteira)); ?>

                        </div>
                        <div class="small text-muted">
                            Parcela financeira das vendas em Carteira
                        </div>
                    </div>

                    <div class="progress mb-3" style="height:22px">
                        <div class="progress-bar bg-primary"
                             style="width:<?php echo e($participacaoCarteira); ?>%">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small">
                        <span>Total vendido: <?php echo e($moeda($totalVendasGerais)); ?></span>
                        <span>Carteira: <?php echo e($moeda($totalComprasCarteira)); ?></span>
                    </div>

                    <div class="text-center mt-3">
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#bi07Modal0">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">
                    Evolucao das Compras em Carteira
                </div>
                <div class="card-body">
                    <?php
                        $maiorMes = max(
                            1,
                            (float) $comprasMensais->max('total')
                        );
                    ?>

                    <?php $__empty_1 = true; $__currentLoopData = $comprasMensais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?php echo e($mes->periodo); ?></span>
                                <strong><?php echo e($moeda($mes->total)); ?></strong>
                            </div>
                            <div class="progress" style="height:16px">
                                <div class="progress-bar bg-primary"
                                     style="width:<?php echo e(min(100, $mes->total / $maiorMes * 100)); ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted text-center py-3">
                            Sem compras em Carteira no periodo.
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#bi07Modal0">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <p class="small text-muted mt-3">
        Compras e recebimentos usam o periodo informado.
        Valores a receber, vencimentos, limites e Score
        representam a posicao atual da Carteira.
    </p>

</div>



<?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<?php
    $campo = $card['campo'];

    $registros = $clientes
        ->filter(function($c) use ($campo) {
            if ($campo === 'score') return $c->score !== null;
            return (float)($c->{$campo} ?? 0) > 0;
        })
        ->sortByDesc(function($c) use ($campo) {
            return (float)($c->{$campo} ?? 0);
        })
        ->values();

    $totalIndicador = $registros->sum(function($c) use ($campo) {
        return (float)($c->{$campo} ?? 0);
    });

    $totalOperacoes = $registros->sum(
        fn($c) => $card['operacoes']
            ? (int)$c->{$card['operacoes']}
            : 0
    );

    $totalLimites = $registros->sum('limite');
    $totalUsado = $registros->sum('usado');
    $totalDisponivel = $registros->sum('disponivel');

    $scoreModal = $registros
        ->filter(fn($c) => $c->score !== null)
        ->avg('score');
?>

<div class="modal fade bi07-modal"
     id="bi07Modal<?php echo e($loop->index); ?>"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi <?php echo e($card['icone']); ?> text-<?php echo e($card['cor']); ?> me-2"></i>
                    <?php echo e($card['titulo']); ?>

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-<?php echo e($card['cor']); ?>">
                            <div class="card-body text-center">
                                <div class="small text-muted">
                                    Indicador
                                </div>
                                <h4 class="mb-0">
                                    <?php echo e($formatarCard($card)); ?>

                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="small text-muted">
                                    Clientes no detalhamento
                                </div>
                                <h4 class="mb-0">
                                    <?php echo e($numero($registros->filter(fn($c) => $c->id !== null)->count())); ?>

                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="small text-muted">
                                    Operacoes
                                </div>
                                <h4 class="mb-0">
                                    <?php echo e($card['operacoes']
                                        ? $numero($totalOperacoes)
                                        : '-'); ?>

                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered
                                  table-striped table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>Cliente</th>
                                <th class="text-center">Operacoes</th>
                                <th class="text-end">Total (R$)</th>
                                <th class="text-end">Limite Credito</th>
                                <th class="text-end">Gasto Atual</th>
                                <th class="text-end">Saldo Disponivel</th>
                                <th>Ultima Compra</th>
                                <th>Ultimo Pagamento</th>
                                <th>Tempo em Atraso</th>
                                <th class="text-center">Score</th>
                                <th class="text-end">Participacao</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <?php
                                $valor = (float)($cliente->{$campo} ?? 0);

                                $participacao = $totalIndicador > 0
                                    ? $valor / $totalIndicador * 100
                                    : 0;

                                $scoreClasse = $cliente->score === null
                                    ? 'secondary'
                                    : ($cliente->score >= 8
                                        ? 'success'
                                        : ($cliente->score >= 6
                                            ? 'info'
                                            : ($cliente->score >= 4
                                                ? 'warning'
                                                : 'danger')));
                            ?>

                            <tr>
                                <td class="fw-semibold">
                                    <?php echo e($cliente->nome); ?>

                                </td>

                                <td class="text-center">
                                    <?php echo e($card['operacoes']
                                        ? $numero($cliente->{$card['operacoes']})
                                        : '-'); ?>

                                </td>

                                <td class="text-end fw-bold">
                                    <?php if($campo === 'score'): ?>
                                        <?php echo e(number_format($valor,1,',','.')); ?>

                                    <?php else: ?>
                                        <?php echo e($moeda($valor)); ?>

                                    <?php endif; ?>
                                </td>

                                <td class="text-end">
                                    <?php echo e($cliente->limite !== null
                                        ? $moeda($cliente->limite)
                                        : '-'); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($cliente->usado !== null
                                        ? $moeda($cliente->usado)
                                        : '-'); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($cliente->disponivel !== null
                                        ? $moeda($cliente->disponivel)
                                        : '-'); ?>

                                </td>

                                <td><?php echo e($dataBR($cliente->ultima_compra)); ?></td>

                                <td><?php echo e($dataBR($cliente->ultimo_pagamento)); ?></td>

                                <td>
                                    <?php echo e($cliente->id === null
                                        ? 'Nao identificado'
                                        : $atrasoBR($cliente->dias_atraso)); ?>

                                </td>

                                <td class="text-center">
                                    <?php if($cliente->score !== null): ?>
                                        <span class="badge bg-<?php echo e($scoreClasse); ?>">
                                            <?php echo e(number_format($cliente->score,1,',','.')); ?>/10
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            Nao avaliado
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-end">
                                    <?php echo e($campo === 'score'
                                        ? '-'
                                        : $pct($participacao)); ?>

                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11"
                                    class="text-center text-muted py-4">
                                    Nenhum dado encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>

                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>TOTAL GERAL</td>

                                <td class="text-center">
                                    <?php echo e($card['operacoes']
                                        ? $numero($totalOperacoes)
                                        : '-'); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($campo === 'score'
                                        ? ($scoreModal !== null
                                            ? number_format($scoreModal,1,',','.').' (media)'
                                            : '-')
                                        : $moeda($totalIndicador)); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($moeda($totalLimites)); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($moeda($totalUsado)); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e($moeda($totalDisponivel)); ?>

                                </td>

                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>

                                <td class="text-end">
                                    <?php echo e($campo === 'score'
                                        ? '-'
                                        : ($totalIndicador > 0 ? '100,00%' : '0,00%')); ?>

                                </td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

                <div class="small text-muted mt-3">
                    Valores de credito obtidos do resumo de credito
                    existente no ERP. O total de limites, gastos e
                    disponibilidade refere-se aos clientes exibidos
                    neste modal. O Score e um indicador gerencial
                    preliminar, ainda nao homologado.
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\bi\financeiro\index.blade.php ENDPATH**/ ?>