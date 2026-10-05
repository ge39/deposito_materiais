<?php $__env->startSection('content'); ?>

<style>

    /*
    |--------------------------------------------------------------------------
    | BI COMPRAS - CARDS MANCHETE
    |--------------------------------------------------------------------------
    */

    .bi-headline-card {
        height: 132px;
        min-height: 132px;

        border-style: solid !important;
        border-width: 2px !important;
        border-radius: .5rem;

        background: var(--bs-body-bg);

        font-family:
            var(--bs-body-font-family),
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Arial,
            sans-serif;

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    .bi-headline-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--bs-box-shadow) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | CORPO
    |--------------------------------------------------------------------------
    */

    .bi-headline-card .card-body {
        height: 100%;

        display: flex;
        flex-direction: column;

        align-items: center;
        justify-content: center;

        text-align: center;

        padding: .75rem 1rem;
    }


    /*
    |--------------------------------------------------------------------------
    | MANCHETE
    |--------------------------------------------------------------------------
    */


    .bi-headline-icon {
        width: 100%;

        display: flex;
        justify-content: center;
        align-items: center;

        margin-bottom: .28rem;

        font-size: 1.35rem;
        line-height: 1;
    }
    .bi-headline-label {
        width: 100%;
        min-height: 20px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-bottom: .18rem;

        font-size: .80rem;
        font-weight: 700;
        line-height: 1.1;

        letter-spacing: .02rem;
        text-transform: uppercase;
    }


    /*
    |--------------------------------------------------------------------------
    | VALOR PRINCIPAL
    |--------------------------------------------------------------------------
    */

    .bi-headline-value {
        width: 100%;
        min-height: 32px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-bottom: .12rem;

        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.1;

        color: var(--bs-body-color);

        text-align: center;
    }


    /*
    |--------------------------------------------------------------------------
    | TEXTO AUXILIAR
    |--------------------------------------------------------------------------
    */

    .bi-headline-text {
        width: 100%;
        min-height: 28px;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: .78rem;
        font-weight: 400;
        line-height: 1.25;

        text-align: center;

        color: var(--bs-secondary-color) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | ACAO
    |--------------------------------------------------------------------------
    */

    .bi-headline-action {
        width: 100%;

        display: flex;
        justify-content: center;
        align-items: center;

        margin-top: auto;
        padding-top: .10rem;
    }

    .bi-headline-action .btn {
        padding: .10rem .30rem;

        font-size: .76rem;
        font-weight: 600;
        line-height: 1.15;

        text-align: center;
        text-decoration: none;
    }

    .bi-headline-action .btn:hover {
        text-decoration: underline;
    }


    /*
    |--------------------------------------------------------------------------
    | MODAIS
    |--------------------------------------------------------------------------
    */

    .bi-modal-kpi {
        min-height: 105px;
    }

    .bi-modal-kpi .card-body {
        display: flex;
        flex-direction: column;
        justify-content: center;

        text-align: center;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLET
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1199.98px) {

        .bi-headline-card {
        height: 132px;
        min-height: 132px;

        border-style: solid !important;
        border-width: 2px !important;
        border-radius: .5rem;

        background: var(--bs-body-bg);

        font-family:
            var(--bs-body-font-family),
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Arial,
            sans-serif;

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    }


    /*
    |--------------------------------------------------------------------------
    | CELULAR
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {

        .bi-headline-card {
        height: 132px;
        min-height: 132px;

        border-style: solid !important;
        border-width: 2px !important;
        border-radius: .5rem;

        background: var(--bs-body-bg);

        font-family:
            var(--bs-body-font-family),
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Arial,
            sans-serif;

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

        .bi-headline-value {
        width: 100%;
        min-height: 32px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-bottom: .12rem;

        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.1;

        color: var(--bs-body-color);

        text-align: center;
    }

    }

</style>


<div class="container-fluid">

    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">
                <i class="bi bi-cart-check me-2"></i>
                BI de Compras
            </h1>

            <p class="text-muted mb-0">
                Capa gerencial de fornecedores, produtos, custos e hist&oacute;rico.
            </p>

        </div>

        <div class="d-flex gap-2 align-items-center">

            <span class="badge text-bg-primary">
                BI-03 Compras
            </span>

            <a
                href="<?php echo e(route('bi.index')); ?>"
                class="btn btn-outline-secondary btn-sm"
            >
                Voltar
            </a>

        </div>

    </div>


    
    <form
        method="GET"
        action="<?php echo e(route('bi.compras.index')); ?>"
        class="card shadow-sm mb-4"
    >

        <div class="card-body">

            <div class="row g-3 align-items-end">

                <div class="col-md-2">

                    <label class="form-label">
                        Data inicial
                    </label>

                    <input
                        type="date"
                        name="data_inicio"
                        class="form-control"
                        value="<?php echo e($dataInicio->format('Y-m-d')); ?>"
                    >

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Data final
                    </label>

                    <input
                        type="date"
                        name="data_fim"
                        class="form-control"
                        value="<?php echo e($dataFim->format('Y-m-d')); ?>"
                    >

                </div>


                <div class="col-md-3">

                    <label class="form-label">
                        Fornecedor
                    </label>

                    <select
                        name="fornecedor_id"
                        id="filtroFornecedor"
                        class="form-select"
                    >

                        <option value="">
                            Todos os fornecedores
                        </option>

                        <?php $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <option
                                value="<?php echo e($fornecedor->id); ?>"
                                <?php if(
                                    (int) $fornecedorId
                                    === (int) $fornecedor->id
                                ): echo 'selected'; endif; ?>
                            >
                                <?php echo e($fornecedor->nome); ?>

                            </option>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>

                </div>


                <div class="col-md-3">

                    <label class="form-label">
                        Produto
                    </label>

                    <select
                        name="produto_id"
                        id="filtroProduto"
                        class="form-select"
                    >

                        <option value="">
                            Todos os produtos
                        </option>

                        <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <option
                                value="<?php echo e($produto->id); ?>"
                                data-fornecedor="<?php echo e($produto->fornecedor_id); ?>"
                                <?php if(
                                    (int) $produtoId
                                    === (int) $produto->id
                                ): echo 'selected'; endif; ?>
                            >
                                <?php echo e($produto->nome); ?>

                            </option>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>

                </div>


                <div class="col-md-2">

                    <div class="d-grid gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-funnel me-1"></i>
                            Aplicar
                        </button>

                        <a
                            href="<?php echo e(route('bi.compras.index')); ?>"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Limpar
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>


    

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-primary border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-primary">
                        <i class="bi bi-cart-check"></i>
                    </div>
<div class="bi-headline-label text-primary">
                        COMPRAS NO PER&Iacute;ODO
                    </div>

                    <div class="bi-headline-value">
                        R$ <?php echo e(number_format(
                            (float) ($resumo->valor_total ?? 0),
                            2,
                            ',',
                            '.'
                        )); ?>

                    </div>

                    <div class="bi-headline-text text-muted">
                        Valor movimentado nas compras selecionadas.
                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCompras"
                        >
                            Ver compras &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-info border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-info">
                        <i class="bi bi-receipt"></i>
                    </div>
<div class="bi-headline-label text-info">
                        PEDIDOS REALIZADOS
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($resumo->total_pedidos ?? 0); ?>

                    </div>

                    <div class="bi-headline-text text-muted">
                        <?php echo e($pedidosRecebidos); ?> pedidos recebidos no per&iacute;odo.
                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalPedidos"
                        >
                            Ver pedidos &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-success border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-success">
                        <i class="bi bi-building"></i>
                    </div>
<div class="bi-headline-label text-success">
                        FORNECEDORES ATIVOS
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($resumo->fornecedores_ativos ?? 0); ?>

                    </div>

                    <div class="bi-headline-text text-muted">
                        Fornecedores com movimenta&ccedil;&atilde;o no recorte.
                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalFornecedores"
                        >
                            Ver fornecedores &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-primary border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-primary">
                        <i class="bi bi-star-fill"></i>
                    </div>
<div class="bi-headline-label text-primary">
                        FORNECEDOR EM DESTAQUE
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($fornecedorDestaque->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($fornecedorDestaque): ?>

                            &Iacute;ndice
                            <?php echo e($fornecedorDestaque->indice_atividade); ?>

                            &middot;
                            <?php echo e($fornecedorDestaque->classificacao); ?>


                        <?php else: ?>

                            Nenhuma movimenta&ccedil;&atilde;o.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDestaque"
                        >
                            Ver ranking &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-warning border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
<div class="bi-headline-label text-warning">
                        MAIOR TEMPO SEM COMPRA
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($fornecedorParado->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($fornecedorParado): ?>

                            <?php echo e($fornecedorParado->periodo_sem_compra); ?>

                            desde a &uacute;ltima compra.

                        <?php else: ?>

                            Nenhum dado dispon&iacute;vel.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalFornecedorParado"
                        >
                            Ver hist&oacute;rico &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-success border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
<div class="bi-headline-label text-success">
                        MAIOR VALOR POR FORNECEDOR
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($fornecedorMaiorValor->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($fornecedorMaiorValor): ?>

                            R$ <?php echo e(number_format(
                                $fornecedorMaiorValor->valor_comprado,
                                2,
                                ',',
                                '.'
                            )); ?>


                        <?php else: ?>

                            Sem movimenta&ccedil;&atilde;o.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalMaiorFornecedor"
                        >
                            Ver fornecedor &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-primary border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
<div class="bi-headline-label text-primary">
                        PRODUTO MAIS COMPRADO
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($produtoMaisComprado->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($produtoMaisComprado): ?>

                            <?php echo e(number_format(
                                $produtoMaisComprado->quantidade,
                                0,
                                ',',
                                '.'
                            )); ?>

                            unidades movimentadas.

                        <?php else: ?>

                            Nenhuma movimenta&ccedil;&atilde;o.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalProdutos"
                        >
                            Ver produtos &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-info border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-info">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
<div class="bi-headline-label text-info">
                        PRODUTO DE MAIOR GASTO
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($produtoMaiorGasto->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($produtoMaiorGasto): ?>

                            R$ <?php echo e(number_format(
                                $produtoMaiorGasto->valor_total,
                                2,
                                ',',
                                '.'
                            )); ?>

                            comprados no per&iacute;odo.

                        <?php else: ?>

                            Nenhuma movimenta&ccedil;&atilde;o.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalMaiorGasto"
                        >
                            Ver custos &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-warning border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-warning">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
<div class="bi-headline-label text-warning">
                        MAIOR VARIA&Ccedil;&Atilde;O DE CUSTO
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($maiorVariacao->nome ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($maiorVariacao): ?>

                            <?php echo e(number_format(
                                $maiorVariacao->variacao_percentual,
                                1,
                                ',',
                                '.'
                            )); ?>%
                            entre menor e maior custo.

                        <?php else: ?>

                            Sem varia&ccedil;&atilde;o calcul&aacute;vel.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalVariacoes"
                        >
                            Analisar custos &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-secondary border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-secondary">
                        <i class="bi bi-calculator"></i>
                    </div>
<div class="bi-headline-label text-secondary">
                        CUSTO M&Eacute;DIO
                    </div>

                    <div class="bi-headline-value">
                        R$ <?php echo e(number_format(
                            $custoMedioGeral,
                            2,
                            ',',
                            '.'
                        )); ?>

                    </div>

                    <div class="bi-headline-text text-muted">
                        Custo m&eacute;dio ponderado por unidade comprada.
                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-secondary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCustoMedio"
                        >
                            Ver composi&ccedil;&atilde;o &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start border-success border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon text-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
<div class="bi-headline-label text-success">
                        &Uacute;LTIMA COMPRA
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($ultimaCompra->fornecedor ?? 'Sem dados'); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($ultimaCompra): ?>

                            <?php echo e(\Carbon\Carbon::parse(
                                $ultimaCompra->data_pedido
                            )->format('d/m/Y')); ?>


                            &middot;

                            R$ <?php echo e(number_format(
                                $ultimaCompra->total,
                                2,
                                ',',
                                '.'
                            )); ?>


                        <?php else: ?>

                            Nenhuma compra encontrada.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link text-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalUltimaCompra"
                        >
                            Ver pedido &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col">

            <div class="card bi-headline-card h-100 border-start <?php echo e($divergenciasPedidos > 0 ? 'border-danger' : 'border-success'); ?> border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon <?php echo e($divergenciasPedidos > 0 ? 'text-danger' : 'text-success'); ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
<div class="bi-headline-label <?php echo e($divergenciasPedidos > 0 ? 'text-danger' : 'text-success'); ?>">
                        ACAREA&Ccedil;&Atilde;O / ATEN&Ccedil;&Atilde;O
                    </div>

                    <div class="bi-headline-value">
                        <?php echo e($divergenciasPedidos); ?>

                    </div>

                    <div class="bi-headline-text text-muted">

                        <?php if($divergenciasPedidos > 0): ?>

                            Pedidos com diverg&ecirc;ncia entre total e itens.

                        <?php else: ?>

                            Nenhuma diverg&ecirc;ncia encontrada.

                        <?php endif; ?>

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link <?php echo e($divergenciasPedidos > 0 ? 'text-danger' : 'text-success'); ?> btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAcareacao"
                        >
                            Conferir &rarr;
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    

    <div
        class="modal fade"
        id="modalCompras"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title">
                        Compras no per&iacute;odo
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">

                            <div class="card bi-modal-kpi border-primary">

                                <div class="card-body">

                                    <div class="small text-muted">
                                        Valor comprado
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        R$ <?php echo e(number_format(
                                            (float) ($resumo->valor_total ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="card bi-modal-kpi border-info">

                                <div class="card-body">

                                    <div class="small text-muted">
                                        Pedidos
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        <?php echo e($resumo->total_pedidos ?? 0); ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="card bi-modal-kpi border-success">

                                <div class="card-body">

                                    <div class="small text-muted">
                                        Ticket m&eacute;dio
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        R$ <?php echo e(number_format(
                                            $ticketMedio,
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-striped table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Fornecedor</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $ultimosPedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td><?php echo e($pedido->fornecedor); ?></td>

                                        <td>
                                            <?php echo e(\Carbon\Carbon::parse(
                                                $pedido->data_pedido
                                            )->format('d/m/Y')); ?>

                                        </td>

                                        <td>
                                            <?php echo e(ucfirst($pedido->status)); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $pedido->total,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum pedido encontrado.
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


    
    <div class="modal fade" id="modalPedidos" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-info text-dark">

                    <h5 class="modal-title">
                        Pedidos realizados
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        <?php echo e($resumo->total_pedidos ?? 0); ?>

                        pedidos no recorte selecionado,
                        sendo <?php echo e($pedidosRecebidos); ?> recebidos.
                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fornecedor</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $ultimosPedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>
                                        <td><?php echo e($pedido->id); ?></td>
                                        <td><?php echo e($pedido->fornecedor); ?></td>
                                        <td>
                                            <?php echo e(\Carbon\Carbon::parse(
                                                $pedido->data_pedido
                                            )->format('d/m/Y')); ?>

                                        </td>
                                        <td><?php echo e(ucfirst($pedido->status)); ?></td>
                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $pedido->total,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>
                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Nenhum pedido encontrado.
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


    
    <div class="modal fade" id="modalFornecedores" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-success text-white">

                    <h5 class="modal-title">
                        Fornecedores ativos
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>#</th>
                                    <th>Fornecedor</th>
                                    <th class="text-end">&Iacute;ndice</th>
                                    <th>Classifica&ccedil;&atilde;o</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Produtos</th>
                                    <th class="text-end">Valor</th>
                                    <th>&Uacute;ltima compra</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $rankingFornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td><?php echo e($loop->iteration); ?></td>

                                        <td class="fw-semibold">
                                            <?php echo e($fornecedor->nome); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($fornecedor->indice_atividade); ?>

                                        </td>

                                        <td>
                                            <?php echo e($fornecedor->classificacao); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($fornecedor->pedidos); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($fornecedor->produtos_comprados); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $fornecedor->valor_comprado,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td>
                                            <?php echo e($fornecedor->periodo_sem_compra); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            Nenhum fornecedor movimentado.
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


    
    <div class="modal fade" id="modalDestaque" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title">
                        Fornecedor em destaque
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($fornecedorDestaque): ?>

                        <h3>
                            <?php echo e($fornecedorDestaque->nome); ?>

                        </h3>

                        <div class="row g-3 mt-2">

                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <small class="text-muted">&Iacute;ndice</small>
                                        <div class="fs-4 fw-bold">
                                            <?php echo e($fornecedorDestaque->indice_atividade); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Pedidos</small>
                                        <div class="fs-4 fw-bold">
                                            <?php echo e($fornecedorDestaque->pedidos); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Produtos</small>
                                        <div class="fs-4 fw-bold">
                                            <?php echo e($fornecedorDestaque->produtos_comprados); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Valor</small>
                                        <div class="fw-bold">
                                            R$ <?php echo e(number_format(
                                                $fornecedorDestaque->valor_comprado,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            Sem informa&ccedil;&otilde;es no per&iacute;odo.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="modal fade" id="modalFornecedorParado" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        Maior tempo sem compra
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($fornecedorParado): ?>

                        <h3>
                            <?php echo e($fornecedorParado->nome); ?>

                        </h3>

                        <p class="lead mb-2">
                            <?php echo e($fornecedorParado->periodo_sem_compra); ?>

                            desde a &uacute;ltima compra.
                        </p>

                        <p class="mb-0">
                            Pedidos no recorte:
                            <strong><?php echo e($fornecedorParado->pedidos); ?></strong>
                            &middot;
                            Valor:
                            <strong>
                                R$ <?php echo e(number_format(
                                    $fornecedorParado->valor_comprado,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                            </strong>
                        </p>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            Sem informa&ccedil;&otilde;es.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="modal fade" id="modalMaiorFornecedor" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-success text-white">

                    <h5 class="modal-title">
                        Maior valor por fornecedor
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($fornecedorMaiorValor): ?>

                        <h3>
                            <?php echo e($fornecedorMaiorValor->nome); ?>

                        </h3>

                        <div class="display-6 fw-bold">
                            R$ <?php echo e(number_format(
                                $fornecedorMaiorValor->valor_comprado,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <p class="text-muted mt-2">
                            <?php echo e($fornecedorMaiorValor->pedidos); ?>

                            pedidos &middot;
                            <?php echo e($fornecedorMaiorValor->produtos_comprados); ?>

                            produtos diferentes.
                        </p>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            Sem movimenta&ccedil;&atilde;o.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="modal fade" id="modalProdutos" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title">
                        Produtos mais comprados
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">Custo m&eacute;dio</th>
                                    <th class="text-end">Valor</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $produtosMaisComprados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td><?php echo e($produto->nome); ?></td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                $produto->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $produto->custo_medio,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $produto->valor_total,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum produto encontrado.
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


    
    <div class="modal fade" id="modalMaiorGasto" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-info text-dark">

                    <h5 class="modal-title">
                        Produto de maior gasto
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($produtoMaiorGasto): ?>

                        <h3>
                            <?php echo e($produtoMaiorGasto->nome); ?>

                        </h3>

                        <div class="display-6 fw-bold">
                            R$ <?php echo e(number_format(
                                $produtoMaiorGasto->valor_total,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <p class="text-muted mt-2">
                            Quantidade:
                            <?php echo e(number_format(
                                $produtoMaiorGasto->quantidade,
                                0,
                                ',',
                                '.'
                            )); ?>

                            &middot;
                            Custo m&eacute;dio:
                            R$ <?php echo e(number_format(
                                $produtoMaiorGasto->custo_medio,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </p>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            Sem movimenta&ccedil;&atilde;o.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="modal fade" id="modalVariacoes" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        Varia&ccedil;&atilde;o de custos
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Menor custo</th>
                                    <th class="text-end">Maior custo</th>
                                    <th class="text-end">Varia&ccedil;&atilde;o</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $variacoes->sortByDesc('variacao_percentual'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td>
                                            <?php echo e($variacao->nome); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $variacao->menor_custo,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $variacao->maior_custo,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end fw-bold">
                                            <?php echo e(number_format(
                                                $variacao->variacao_percentual,
                                                1,
                                                ',',
                                                '.'
                                            )); ?>%
                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Sem varia&ccedil;&otilde;es calcul&aacute;veis.
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


    
    <div class="modal fade" id="modalCustoMedio" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-secondary text-white">

                    <h5 class="modal-title">
                        Composi&ccedil;&atilde;o do custo m&eacute;dio
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-secondary">

                        Custo m&eacute;dio ponderado do recorte:
                        <strong>
                            R$ <?php echo e(number_format(
                                $custoMedioGeral,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </strong>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover">

                            <thead class="table-light">

                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">Custo m&eacute;dio</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $produtosMaisComprados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td>
                                            <?php echo e($produto->nome); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                $produto->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $produto->custo_medio,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Sem produtos.
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


    
    <div class="modal fade" id="modalUltimaCompra" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-success text-white">

                    <h5 class="modal-title">
                        &Uacute;ltima compra
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($ultimaCompra): ?>

                        <dl class="row mb-0">

                            <dt class="col-sm-4">
                                Pedido
                            </dt>

                            <dd class="col-sm-8">
                                #<?php echo e($ultimaCompra->id); ?>

                            </dd>

                            <dt class="col-sm-4">
                                Fornecedor
                            </dt>

                            <dd class="col-sm-8">
                                <?php echo e($ultimaCompra->fornecedor); ?>

                            </dd>

                            <dt class="col-sm-4">
                                Data
                            </dt>

                            <dd class="col-sm-8">
                                <?php echo e(\Carbon\Carbon::parse(
                                    $ultimaCompra->data_pedido
                                )->format('d/m/Y')); ?>

                            </dd>

                            <dt class="col-sm-4">
                                Status
                            </dt>

                            <dd class="col-sm-8">
                                <?php echo e(ucfirst($ultimaCompra->status)); ?>

                            </dd>

                            <dt class="col-sm-4">
                                Valor
                            </dt>

                            <dd class="col-sm-8 fw-bold">
                                R$ <?php echo e(number_format(
                                    $ultimaCompra->total,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                            </dd>

                        </dl>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            Nenhuma compra encontrada.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="modal fade" id="modalAcareacao" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header <?php echo e($divergenciasPedidos > 0 ? 'bg-danger text-white' : 'bg-success text-white'); ?>">

                    <h5 class="modal-title">
                        Acarea&ccedil;&atilde;o dos dados
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <?php if($divergenciasPedidos > 0): ?>

                        <div class="alert alert-danger">

                            Foram identificados
                            <strong><?php echo e($divergenciasPedidos); ?></strong>
                            pedidos cuja soma dos itens n&atilde;o coincide
                            com o total registrado no pedido.

                        </div>

                        <p class="mb-0">
                            Estes registros devem ser conferidos antes
                            de usar seus valores em an&aacute;lises financeiras.
                        </p>

                    <?php else: ?>

                        <div class="alert alert-success mb-0">

                            <i class="bi bi-check-circle me-1"></i>

                            Nenhuma diverg&ecirc;ncia entre o total
                            dos pedidos e a soma dos itens foi encontrada
                            no recorte selecionado.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div>

                <div class="fw-semibold">
                    Hist&oacute;rico mensal
                </div>

                <div class="small text-muted">
                    Evolu&ccedil;&atilde;o das compras nos &uacute;ltimos meses.
                </div>

            </div>

            <button
                type="button"
                class="btn btn-outline-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalHistorico"
            >
                Ver hist&oacute;rico
            </button>

        </div>

    </div>


    <div class="modal fade" id="modalHistorico" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title">
                        Hist&oacute;rico mensal
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>M&ecirc;s</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Valor comprado</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $historicoMensal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <tr>

                                        <td>
                                            <?php echo e(str_pad(
                                                $mes->mes,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            )); ?>/<?php echo e($mes->ano); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($mes->pedidos); ?>

                                        </td>

                                        <td class="text-end">
                                            R$ <?php echo e(number_format(
                                                $mes->valor_total,
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Sem hist&oacute;rico.
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

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const fornecedor =
        document.getElementById('filtroFornecedor');

    const produto =
        document.getElementById('filtroProduto');

    if (!fornecedor || !produto) {
        return;
    }

    const opcoes =
        Array.from(produto.options);

    function filtrarProdutos() {

        const fornecedorId =
            fornecedor.value;

        opcoes.forEach(function (option) {

            if (!option.value) {
                option.hidden = false;
                return;
            }

            const fornecedorProduto =
                option.dataset.fornecedor || '';

            option.hidden =
                fornecedorId !== ''
                && fornecedorProduto !== fornecedorId;
        });

        const selecionado =
            produto.options[
                produto.selectedIndex
            ];

        if (
            selecionado
            && selecionado.hidden
        ) {
            produto.value = '';
        }
    }

    fornecedor.addEventListener(
        'change',
        filtrarProdutos
    );

    filtrarProdutos();
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/compras/index.blade.php ENDPATH**/ ?>