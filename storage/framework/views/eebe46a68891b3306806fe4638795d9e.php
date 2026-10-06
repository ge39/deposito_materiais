<?php $__env->startSection('content'); ?>

<style>
    .bi-vendas-card {
        height: 132px;
        min-height: 132px;
        border-width: 2px !important;
        border-radius: .55rem;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .bi-vendas-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 .25rem .75rem rgba(0,0,0,.08);
    }

    .bi-vendas-card .card-body {
        height: 100%;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;

        text-align: center;

        padding: .55rem 1rem .60rem 1rem;
    }

    .bi-headline-icon {
        margin: 0;
        font-size: 1.35rem;
        line-height: 1;
    }

    .bi-headline-label {
        margin: 0;

        font-size: .80rem;
        font-weight: 700;
        line-height: 1.1;

        text-transform: uppercase;
    }

    .bi-headline-value {
        margin: 0;

        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.05;

        color: var(--bs-body-color);
        text-align: center;
    }

    .bi-headline-text {
        margin: 0;

        font-size: .78rem;
        line-height: 1.2;

        color: var(--bs-secondary-color);
        text-align: center;
    }

    .bi-headline-action .btn {
        margin: 0;
        padding: 0 .25rem;

        font-size: .76rem;
        font-weight: 600;
        line-height: 1.1;
    }

    .bi-modal-table {
        font-size: .85rem;
    }

    .bi-filter-label {
        font-size: .78rem;
        font-weight: 600;
    }
    .bi-vendas-grid-spacing {
        margin-top: 2rem !important;
        margin-bottom: 2rem !important;
        row-gap: 1.5rem !important;
    }

</style>


<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-stack me-2"></i>
                BI Vendas
            </h1>

            <div class="text-muted">
                Performance comercial, clientes, produtos e comportamento das vendas.
            </div>
        </div>

        <a href="<?php echo e(route('bi.index')); ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>

    <form method="GET"
          action="<?php echo e(route('bi.vendas.index')); ?>"
          class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-2 align-items-end">

                <div class="col-12 col-md-2">
                    <label class="form-label bi-filter-label">
                        Data inicial
                    </label>

                    <input type="date"
                           name="data_inicio"
                           class="form-control form-control-sm"
                           value="<?php echo e($dataInicio->format('Y-m-d')); ?>">
                </div>


                <div class="col-12 col-md-2">
                    <label class="form-label bi-filter-label">
                        Data final
                    </label>

                    <input type="date"
                           name="data_fim"
                           class="form-control form-control-sm"
                           value="<?php echo e($dataFim->format('Y-m-d')); ?>">
                </div>


                <div class="col-12 col-md-2">
                    <label class="form-label bi-filter-label">
                        Cliente
                    </label>

                    <select name="cliente_id"
                            class="form-select form-select-sm">

                        <option value="">Todos</option>

                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cliente->id); ?>"
                                <?php if($clienteId === (int) $cliente->id): echo 'selected'; endif; ?>>
                                <?php echo e($cliente->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>
                </div>


                <div class="col-12 col-md-2">
                    <label class="form-label bi-filter-label">
                        Produto
                    </label>

                    <select name="produto_id"
                            class="form-select form-select-sm">

                        <option value="">Todos</option>

                        <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($produto->id); ?>"
                                <?php if($produtoId === (int) $produto->id): echo 'selected'; endif; ?>>
                                <?php echo e($produto->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>
                </div>


                <div class="col-12 col-md-2">
                    <label class="form-label bi-filter-label">
                        Respons&aacute;vel
                    </label>

                    <select name="responsavel_id"
                            class="form-select form-select-sm">

                        <option value="">Todos</option>

                        <?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($responsavel->id); ?>"
                                <?php if($responsavelId === (int) $responsavel->id): echo 'selected'; endif; ?>>

                                <?php echo e($responsavel->funcionario ?: $responsavel->usuario); ?>


                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>
                </div>


                <div class="col-12 col-md-2">
                    <div class="d-flex gap-1">

                        <button type="submit"
        class="btn btn-primary btn-sm">
        <i class="bi bi-funnel me-1"></i>
        Aplicar filtros
    </button>

                            <a href="<?php echo e(route('bi.vendas.index')); ?>"
    class="btn btn-outline-secondary btn-sm">
        Limpar
    </a>

                    </div>
                </div>

            </div>

        </div>
    </form>


    <div class="row g-4 mt-5 mb-5 bi-vendas-grid-spacing">


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-primary shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="bi-headline-label text-primary">
                        Faturamento Bruto
                    </div>

                    <div class="bi-headline-value">
                        R$ <?php echo e(number_format($resumo->faturamento, 2, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-text">
                        <?php if($variacaoFaturamento !== null): ?>
                            <?php echo e($variacaoFaturamento >= 0 ? '+' : ''); ?>

                            <?php echo e(number_format($variacaoFaturamento, 1, ',', '.')); ?>%
                            vs. per&iacute;odo anterior
                        <?php else: ?>
                            Sem base anterior compar&aacute;vel
                        <?php endif; ?>
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalFaturamento">
                            Ver evolu&ccedil;&atilde;o
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4" >
            <div class="card bi-vendas-card border-info shadow-sm">
                <div class="card-body" >

                    <div class="bi-headline-icon text-info">
                        <i class="bi bi-receipt"></i>
                    </div>

                    <div class="bi-headline-label text-info">
                        Vendas Realizadas
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e(number_format($resumo->vendas, 0, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-text">
                        <?php echo e(number_format($resumo->itens, 3, ',', '.')); ?>

                        itens vendidos
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalVendas">
                            Ver detalhes
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-success shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-success">
                        <i class="bi bi-calculator"></i>
                    </div>

                    <div class="bi-headline-label text-success">
                        Ticket M&eacute;dio
                    </div>

                    <div class="bi-headline-value">
                        R$ <?php echo e(number_format($resumo->ticket_medio, 2, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-text">
                        Valor m&eacute;dio por venda finalizada
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalTicket">
                            Comparar
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-warning shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-warning">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>

                    <div class="bi-headline-label text-warning">
                        Evolu&ccedil;&atilde;o Temporal
                    </div>

                    <div class="bi-headline-value">
                        <?php if($variacaoFaturamento !== null): ?>
                            <?php echo e($variacaoFaturamento >= 0 ? '+' : ''); ?>

                            <?php echo e(number_format($variacaoFaturamento, 1, ',', '.')); ?>%
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </div>

                    <div class="bi-headline-text">
                        Per&iacute;odo atual vs. anterior equivalente
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEvolucao">
                            Ver hist&oacute;rico
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-danger shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-danger">
                        <i class="bi bi-clock-fill"></i>
                    </div>

                    <div class="bi-headline-label text-danger">
                        Hor&aacute;rio de Maior Volume
                    </div>

                    <div class="bi-headline-value">
                        <?php if($horarioMaiorVolume): ?>
                            <?php echo e(str_pad($horarioMaiorVolume->hora, 2, '0', STR_PAD_LEFT)); ?>h
                            &agrave;s
                            <?php echo e(str_pad(($horarioMaiorVolume->hora + 1) % 24, 2, '0', STR_PAD_LEFT)); ?>h
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </div>

                    <div class="bi-headline-text">
                        <?php echo e($horarioMaiorVolume ? number_format($horarioMaiorVolume->quantidade_vendas, 0, ',', '.') : 0); ?>

                        vendas
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalHorarios">
                            Ver hor&aacute;rios
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-secondary shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-secondary">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div class="bi-headline-label text-secondary">
                        Hor&aacute;rio de Menor Volume
                    </div>

                    <div class="bi-headline-value">
                        <?php if($horarioMenorVolume): ?>
                            <?php echo e(str_pad($horarioMenorVolume->hora, 2, '0', STR_PAD_LEFT)); ?>h
                            &agrave;s
                            <?php echo e(str_pad(($horarioMenorVolume->hora + 1) % 24, 2, '0', STR_PAD_LEFT)); ?>h
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </div>

                    <div class="bi-headline-text">
                        <?php echo e($horarioMenorVolume ? number_format($horarioMenorVolume->quantidade_vendas, 0, ',', '.') : 0); ?>

                        vendas
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalHorarios">
                            Ver hor&aacute;rios
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-primary shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-primary">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>

                    <div class="bi-headline-label text-primary">
                        Respons&aacute;vel em Destaque
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($responsavelDestaque
                            ? ($responsavelDestaque->funcionario ?: $responsavelDestaque->usuario)
                            : '--'); ?>

                    </div>

                    <div class="bi-headline-text">
                        R$ <?php echo e(number_format($responsavelDestaque->faturamento ?? 0, 2, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalResponsaveis">
                            Ver ranking
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-success shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="bi-headline-label text-success">
                        Cliente de Maior Faturamento
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($clienteDestaque->nome ?? '--'); ?>

                    </div>

                    <div class="bi-headline-text">
                        R$ <?php echo e(number_format($clienteDestaque->faturamento ?? 0, 2, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalClientes">
                            Ver clientes
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-info shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-info">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>

                    <div class="bi-headline-label text-info">
                        Taxa de Recompra
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e(number_format($taxaRecompra, 1, ',', '.')); ?>%
                    </div>

                    <div class="bi-headline-text">
                        <?php echo e($clientesComRecompra); ?>

                        de <?php echo e($rankingClientes->count()); ?>

                        clientes compraram novamente
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalClientes">
                            Ver reten&ccedil;&atilde;o
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-warning shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-warning">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>

                    <div class="bi-headline-label text-warning">
                        Produto de Maior Faturamento
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($produtoDestaque->nome ?? '--'); ?>

                    </div>

                    <div class="bi-headline-text">
                        R$ <?php echo e(number_format($produtoDestaque->faturamento ?? 0, 2, ',', '.')); ?>

                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalProdutos">
                            Ver produtos
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card border-dark shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon text-dark">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>

                    <div class="bi-headline-label text-dark">
                        Curva ABC
                    </div>

                    <div class="bi-headline-value">
                        Classe A: <?php echo e($produtosClasseA); ?>

                    </div>

                    <div class="bi-headline-text">
                        <?php echo e(number_format($participacaoClasseA, 1, ',', '.')); ?>%
                        do faturamento
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCurvaAbc">
                            Ver curva ABC
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 col-xl-4">
            <div class="card bi-vendas-card <?php echo e($totalDivergencias > 0 ? 'border-danger' : 'border-success'); ?> shadow-sm">
                <div class="card-body">

                    <div class="bi-headline-icon <?php echo e($totalDivergencias > 0 ? 'text-danger' : 'text-success'); ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div class="bi-headline-label <?php echo e($totalDivergencias > 0 ? 'text-danger' : 'text-success'); ?>">
                        Acarea&ccedil;&atilde;o / Aten&ccedil;&atilde;o
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($totalDivergencias); ?>

                    </div>

                    <div class="bi-headline-text">
                        diverg&ecirc;ncia(s) entre venda e itens
                    </div>

                    <div class="bi-headline-action">
                        <button type="button"
                                class="btn btn-link"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAcareacao">
                            Ver acarea&ccedil;&atilde;o
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>




<div class="modal fade" id="modalFaturamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Faturamento Bruto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm table-striped bi-modal-table">
                    <thead>
                        <tr>
                            <th>Per&iacute;odo</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>Atual</td>
                            <td class="text-end"><?php echo e(number_format($resumo->vendas, 0, ',', '.')); ?></td>
                            <td class="text-end">R$ <?php echo e(number_format($resumo->faturamento, 2, ',', '.')); ?></td>
                            <td class="text-end">R$ <?php echo e(number_format($resumo->ticket_medio, 2, ',', '.')); ?></td>
                        </tr>

                        <tr>
                            <td>Anterior equivalente</td>
                            <td class="text-end"><?php echo e(number_format($resumoAnterior->vendas, 0, ',', '.')); ?></td>
                            <td class="text-end">R$ <?php echo e(number_format($resumoAnterior->faturamento, 2, ',', '.')); ?></td>
                            <td class="text-end">R$ <?php echo e(number_format($resumoAnterior->ticket_medio, 2, ',', '.')); ?></td>
                        </tr>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalVendas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Vendas Realizadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-4 mt-5 mb-5 bi-vendas-grid-spacing">

                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center">
                            <div class="small text-muted">Vendas</div>
                            <strong><?php echo e(number_format($resumo->vendas, 0, ',', '.')); ?></strong>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center">
                            <div class="small text-muted">Itens</div>
                            <strong><?php echo e(number_format($resumo->itens, 3, ',', '.')); ?></strong>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 text-center">
                            <div class="small text-muted">Clientes</div>
                            <strong><?php echo e(number_format($resumo->clientes, 0, ',', '.')); ?></strong>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalTicket" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Ticket M&eacute;dio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm">
                    <tr>
                        <th>Atual</th>
                        <td class="text-end">
                            R$ <?php echo e(number_format($resumo->ticket_medio, 2, ',', '.')); ?>

                        </td>
                    </tr>

                    <tr>
                        <th>Anterior</th>
                        <td class="text-end">
                            R$ <?php echo e(number_format($resumoAnterior->ticket_medio, 2, ',', '.')); ?>

                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalEvolucao" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Evolu&ccedil;&atilde;o Mensal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm table-striped bi-modal-table">
                    <thead>
                        <tr>
                            <th>M&ecirc;s</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket m&eacute;dio</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $historicoMensal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <?php echo e(str_pad($linha->mes, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($linha->ano); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->ticket_medio, 2, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Sem dados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalHorarios" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">An&aacute;lise por Hor&aacute;rio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-2 mb-3">

                    <div class="col-md-4">
                        <div class="alert alert-primary mb-0">
                            <strong>Maior volume</strong><br>
                            <?php echo e($horarioMaiorVolume
                                ? str_pad($horarioMaiorVolume->hora, 2, '0', STR_PAD_LEFT) . 'h'
                                : '--'); ?>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-success mb-0">
                            <strong>Maior faturamento</strong><br>
                            <?php echo e($horarioMaiorFaturamento
                                ? str_pad($horarioMaiorFaturamento->hora, 2, '0', STR_PAD_LEFT) . 'h'
                                : '--'); ?>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <strong>Maior ticket</strong><br>
                            <?php echo e($horarioMaiorTicket
                                ? str_pad($horarioMaiorTicket->hora, 2, '0', STR_PAD_LEFT) . 'h'
                                : '--'); ?>

                        </div>
                    </div>

                </div>

                <table class="table table-sm table-striped bi-modal-table">

                    <thead>
                        <tr>
                            <th>Faixa hor&aacute;ria</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $horarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <?php echo e(str_pad($linha->hora, 2, '0', STR_PAD_LEFT)); ?>h
                                    &agrave;s
                                    <?php echo e(str_pad(($linha->hora + 1) % 24, 2, '0', STR_PAD_LEFT)); ?>h
                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->quantidade_vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->ticket_medio, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalResponsaveis" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Respons&aacute;veis pelas Vendas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info small">
                    O ranking utiliza o respons&aacute;vel gravado na venda
                    e o relacionamento users &rarr; funcionarios.
                </div>

                <table class="table table-sm table-striped bi-modal-table">

                    <thead>
                        <tr>
                            <th>Respons&aacute;vel</th>
                            <th>Fun&ccedil;&atilde;o</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $rankingResponsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <?php echo e($linha->funcionario ?: $linha->usuario); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->funcao ?: '--'); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->ticket_medio, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalClientes" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Clientes e Geografia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <h6>Ranking de clientes</h6>

                <table class="table table-sm table-striped bi-modal-table mb-4">

                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Ticket</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $rankingClientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td><?php echo e($linha->nome); ?></td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->ticket_medio, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>


                <h6>Geografia</h6>

                <div class="small text-muted mb-2">
                    Baseada na cidade e UF cadastradas no cliente.
                </div>

                <table class="table table-sm table-striped bi-modal-table">

                    <thead>
                        <tr>
                            <th>Cidade</th>
                            <th>UF</th>
                            <th class="text-end">Clientes</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $geografia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td><?php echo e($linha->cidade); ?></td>
                                <td><?php echo e($linha->estado); ?></td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->clientes, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalProdutos" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Produtos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm table-striped bi-modal-table">

                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th class="text-end">Quantidade</th>
                            <th class="text-end">Vendas</th>
                            <th class="text-end">Faturamento</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $rankingProdutos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td><?php echo e($linha->nome); ?></td>
                                <td><?php echo e($linha->categoria ?: '--'); ?></td>
                                <td><?php echo e($linha->marca ?: '--'); ?></td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->quantidade, 3, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->vendas, 0, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalCurvaAbc" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Curva ABC de Produtos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm table-striped bi-modal-table">

                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Produto</th>
                            <th class="text-end">Faturamento</th>
                            <th class="text-end">Participa&ccedil;&atilde;o</th>
                            <th class="text-end">Acumulado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__currentLoopData = $curvaAbc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <span class="badge
                                        <?php echo e($linha->classe === 'A'
                                            ? 'text-bg-success'
                                            : ($linha->classe === 'B'
                                                ? 'text-bg-warning'
                                                : 'text-bg-secondary')); ?>">
                                        <?php echo e($linha->classe); ?>

                                    </span>
                                </td>

                                <td><?php echo e($linha->nome); ?></td>

                                <td class="text-end">
                                    R$ <?php echo e(number_format($linha->faturamento, 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->participacao, 2, ',', '.')); ?>%
                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format($linha->acumulado, 2, ',', '.')); ?>%
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalAcareacao" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Acarea&ccedil;&atilde;o de Vendas
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <?php if($totalDivergencias === 0): ?>

                    <div class="alert alert-success">
                        Nenhuma diverg&ecirc;ncia encontrada no per&iacute;odo.
                    </div>

                <?php else: ?>

                    <div class="alert alert-warning">
                        Foram encontradas
                        <strong><?php echo e($totalDivergencias); ?></strong>
                        venda(s) cujo total difere da soma dos itens.
                    </div>

                    <table class="table table-sm table-striped bi-modal-table">

                        <thead>
                            <tr>
                                <th>Venda</th>
                                <th>Data</th>
                                <th class="text-end">Total venda</th>
                                <th class="text-end">Total itens</th>
                                <th class="text-end">Diferen&ccedil;a</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php $__currentLoopData = $divergencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>

                                    <td>#<?php echo e($linha->id); ?></td>

                                    <td>
                                        <?php echo e(\Carbon\Carbon::parse($linha->data_venda)->format('d/m/Y H:i')); ?>

                                    </td>

                                    <td class="text-end">
                                        R$ <?php echo e(number_format($linha->total_venda, 2, ',', '.')); ?>

                                    </td>

                                    <td class="text-end">
                                        R$ <?php echo e(number_format($linha->total_itens, 2, ',', '.')); ?>

                                    </td>

                                    <td class="text-end fw-semibold text-danger">
                                        R$ <?php echo e(number_format($linha->diferenca, 2, ',', '.')); ?>

                                    </td>

                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/vendas/index.blade.php ENDPATH**/ ?>