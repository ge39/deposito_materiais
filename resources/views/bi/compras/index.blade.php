@extends('layouts.app')

@section('content')

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

    {{-- CABECALHO --}}
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
                href="{{ route('bi.index') }}"
                class="btn btn-outline-secondary btn-sm"
            >
                Voltar
            </a>

        </div>

    </div>


    {{-- FILTROS --}}
    <form
        method="GET"
        action="{{ route('bi.compras.index') }}"
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
                        value="{{ $dataInicio->format('Y-m-d') }}"
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
                        value="{{ $dataFim->format('Y-m-d') }}"
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

                        @foreach($fornecedores as $fornecedor)

                            <option
                                value="{{ $fornecedor->id }}"
                                @selected(
                                    (int) $fornecedorId
                                    === (int) $fornecedor->id
                                )
                            >
                                {{ $fornecedor->nome }}
                            </option>

                        @endforeach

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

                        @foreach($produtos as $produto)

                            <option
                                value="{{ $produto->id }}"
                                data-fornecedor="{{ $produto->fornecedor_id }}"
                                @selected(
                                    (int) $produtoId
                                    === (int) $produto->id
                                )
                            >
                                {{ $produto->nome }}
                            </option>

                        @endforeach

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
                            href="{{ route('bi.compras.index') }}"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Limpar
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>


    {{-- =====================================================
         12 MANCHETES
         ===================================================== --}}

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">


        {{-- 01 --}}
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
                        R$ {{ number_format(
                            (float) ($resumo->valor_total ?? 0),
                            2,
                            ',',
                            '.'
                        ) }}
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


        {{-- 02 --}}
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
                        {{ $resumo->total_pedidos ?? 0 }}
                    </div>

                    <div class="bi-headline-text text-muted">
                        {{ $pedidosRecebidos }} pedidos recebidos no per&iacute;odo.
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


        {{-- 03 --}}
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
                        {{ $resumo->fornecedores_ativos ?? 0 }}
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


        {{-- 04 --}}
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
                        {{ $fornecedorDestaque->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($fornecedorDestaque)

                            &Iacute;ndice
                            {{ $fornecedorDestaque->indice_atividade }}
                            &middot;
                            {{ $fornecedorDestaque->classificacao }}

                        @else

                            Nenhuma movimenta&ccedil;&atilde;o.

                        @endif

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


        {{-- 05 --}}
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
                        {{ $fornecedorParado->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($fornecedorParado)

                            {{ $fornecedorParado->periodo_sem_compra }}
                            desde a &uacute;ltima compra.

                        @else

                            Nenhum dado dispon&iacute;vel.

                        @endif

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


        {{-- 06 --}}
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
                        {{ $fornecedorMaiorValor->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($fornecedorMaiorValor)

                            R$ {{ number_format(
                                $fornecedorMaiorValor->valor_comprado,
                                2,
                                ',',
                                '.'
                            ) }}

                        @else

                            Sem movimenta&ccedil;&atilde;o.

                        @endif

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


        {{-- 07 --}}
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
                        {{ $produtoMaisComprado->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($produtoMaisComprado)

                            {{ number_format(
                                $produtoMaisComprado->quantidade,
                                0,
                                ',',
                                '.'
                            ) }}
                            unidades movimentadas.

                        @else

                            Nenhuma movimenta&ccedil;&atilde;o.

                        @endif

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


        {{-- 08 --}}
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
                        {{ $produtoMaiorGasto->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($produtoMaiorGasto)

                            R$ {{ number_format(
                                $produtoMaiorGasto->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}
                            comprados no per&iacute;odo.

                        @else

                            Nenhuma movimenta&ccedil;&atilde;o.

                        @endif

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


        {{-- 09 --}}
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
                        {{ $maiorVariacao->nome ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($maiorVariacao)

                            {{ number_format(
                                $maiorVariacao->variacao_percentual,
                                1,
                                ',',
                                '.'
                            ) }}%
                            entre menor e maior custo.

                        @else

                            Sem varia&ccedil;&atilde;o calcul&aacute;vel.

                        @endif

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


        {{-- 10 --}}
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
                        R$ {{ number_format(
                            $custoMedioGeral,
                            2,
                            ',',
                            '.'
                        ) }}
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


        {{-- 11 --}}
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
                        {{ $ultimaCompra->fornecedor ?? 'Sem dados' }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($ultimaCompra)

                            {{ \Carbon\Carbon::parse(
                                $ultimaCompra->data_pedido
                            )->format('d/m/Y') }}

                            &middot;

                            R$ {{ number_format(
                                $ultimaCompra->total,
                                2,
                                ',',
                                '.'
                            ) }}

                        @else

                            Nenhuma compra encontrada.

                        @endif

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


        {{-- 12 --}}
        <div class="col">

            <div class="card bi-headline-card h-100 border-start {{ $divergenciasPedidos > 0 ? 'border-danger' : 'border-success' }} border-4">

                <div class="card-body">

                                        <div class="bi-headline-icon {{ $divergenciasPedidos > 0 ? 'text-danger' : 'text-success' }}">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
<div class="bi-headline-label {{ $divergenciasPedidos > 0 ? 'text-danger' : 'text-success' }}">
                        ACAREA&Ccedil;&Atilde;O / ATEN&Ccedil;&Atilde;O
                    </div>

                    <div class="bi-headline-value">
                        {{ $divergenciasPedidos }}
                    </div>

                    <div class="bi-headline-text text-muted">

                        @if($divergenciasPedidos > 0)

                            Pedidos com diverg&ecirc;ncia entre total e itens.

                        @else

                            Nenhuma diverg&ecirc;ncia encontrada.

                        @endif

                    </div>

                    <div class="bi-headline-action">

                        <button
                            type="button"
                            class="btn btn-link {{ $divergenciasPedidos > 0 ? 'text-danger' : 'text-success' }} btn-sm"
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


    {{-- =====================================================
         MODAL 01 - COMPRAS
         ===================================================== --}}

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
                                        R$ {{ number_format(
                                            (float) ($resumo->valor_total ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        ) }}
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
                                        {{ $resumo->total_pedidos ?? 0 }}
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
                                        R$ {{ number_format(
                                            $ticketMedio,
                                            2,
                                            ',',
                                            '.'
                                        ) }}
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

                                @forelse($ultimosPedidos as $pedido)

                                    <tr>

                                        <td>{{ $pedido->fornecedor }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse(
                                                $pedido->data_pedido
                                            )->format('d/m/Y') }}
                                        </td>

                                        <td>
                                            {{ ucfirst($pedido->status) }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $pedido->total,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum pedido encontrado.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 02 - PEDIDOS --}}
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
                        {{ $resumo->total_pedidos ?? 0 }}
                        pedidos no recorte selecionado,
                        sendo {{ $pedidosRecebidos }} recebidos.
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

                                @forelse($ultimosPedidos as $pedido)

                                    <tr>
                                        <td>{{ $pedido->id }}</td>
                                        <td>{{ $pedido->fornecedor }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse(
                                                $pedido->data_pedido
                                            )->format('d/m/Y') }}
                                        </td>
                                        <td>{{ ucfirst($pedido->status) }}</td>
                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $pedido->total,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Nenhum pedido encontrado.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 03 - FORNECEDORES --}}
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

                                @forelse($rankingFornecedores as $fornecedor)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td class="fw-semibold">
                                            {{ $fornecedor->nome }}
                                        </td>

                                        <td class="text-end">
                                            {{ $fornecedor->indice_atividade }}
                                        </td>

                                        <td>
                                            {{ $fornecedor->classificacao }}
                                        </td>

                                        <td class="text-end">
                                            {{ $fornecedor->pedidos }}
                                        </td>

                                        <td class="text-end">
                                            {{ $fornecedor->produtos_comprados }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $fornecedor->valor_comprado,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td>
                                            {{ $fornecedor->periodo_sem_compra }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            Nenhum fornecedor movimentado.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 04 - DESTAQUE --}}
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

                    @if($fornecedorDestaque)

                        <h3>
                            {{ $fornecedorDestaque->nome }}
                        </h3>

                        <div class="row g-3 mt-2">

                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <small class="text-muted">&Iacute;ndice</small>
                                        <div class="fs-4 fw-bold">
                                            {{ $fornecedorDestaque->indice_atividade }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Pedidos</small>
                                        <div class="fs-4 fw-bold">
                                            {{ $fornecedorDestaque->pedidos }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Produtos</small>
                                        <div class="fs-4 fw-bold">
                                            {{ $fornecedorDestaque->produtos_comprados }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        <small class="text-muted">Valor</small>
                                        <div class="fw-bold">
                                            R$ {{ number_format(
                                                $fornecedorDestaque->valor_comprado,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    @else

                        <div class="alert alert-info mb-0">
                            Sem informa&ccedil;&otilde;es no per&iacute;odo.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 05 - PARADO --}}
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

                    @if($fornecedorParado)

                        <h3>
                            {{ $fornecedorParado->nome }}
                        </h3>

                        <p class="lead mb-2">
                            {{ $fornecedorParado->periodo_sem_compra }}
                            desde a &uacute;ltima compra.
                        </p>

                        <p class="mb-0">
                            Pedidos no recorte:
                            <strong>{{ $fornecedorParado->pedidos }}</strong>
                            &middot;
                            Valor:
                            <strong>
                                R$ {{ number_format(
                                    $fornecedorParado->valor_comprado,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </p>

                    @else

                        <div class="alert alert-info mb-0">
                            Sem informa&ccedil;&otilde;es.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 06 - MAIOR FORNECEDOR --}}
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

                    @if($fornecedorMaiorValor)

                        <h3>
                            {{ $fornecedorMaiorValor->nome }}
                        </h3>

                        <div class="display-6 fw-bold">
                            R$ {{ number_format(
                                $fornecedorMaiorValor->valor_comprado,
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>

                        <p class="text-muted mt-2">
                            {{ $fornecedorMaiorValor->pedidos }}
                            pedidos &middot;
                            {{ $fornecedorMaiorValor->produtos_comprados }}
                            produtos diferentes.
                        </p>

                    @else

                        <div class="alert alert-info mb-0">
                            Sem movimenta&ccedil;&atilde;o.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 07 - PRODUTOS --}}
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

                                @forelse($produtosMaisComprados as $produto)

                                    <tr>

                                        <td>{{ $produto->nome }}</td>

                                        <td class="text-end">
                                            {{ number_format(
                                                $produto->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $produto->custo_medio,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $produto->valor_total,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum produto encontrado.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 08 - MAIOR GASTO --}}
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

                    @if($produtoMaiorGasto)

                        <h3>
                            {{ $produtoMaiorGasto->nome }}
                        </h3>

                        <div class="display-6 fw-bold">
                            R$ {{ number_format(
                                $produtoMaiorGasto->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>

                        <p class="text-muted mt-2">
                            Quantidade:
                            {{ number_format(
                                $produtoMaiorGasto->quantidade,
                                0,
                                ',',
                                '.'
                            ) }}
                            &middot;
                            Custo m&eacute;dio:
                            R$ {{ number_format(
                                $produtoMaiorGasto->custo_medio,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>

                    @else

                        <div class="alert alert-info mb-0">
                            Sem movimenta&ccedil;&atilde;o.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 09 - VARIACOES --}}
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

                                @forelse(
                                    $variacoes->sortByDesc('variacao_percentual')
                                    as $variacao
                                )

                                    <tr>

                                        <td>
                                            {{ $variacao->nome }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $variacao->menor_custo,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $variacao->maior_custo,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-end fw-bold">
                                            {{ number_format(
                                                $variacao->variacao_percentual,
                                                1,
                                                ',',
                                                '.'
                                            ) }}%
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Sem varia&ccedil;&otilde;es calcul&aacute;veis.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 10 - CUSTO MEDIO --}}
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
                            R$ {{ number_format(
                                $custoMedioGeral,
                                2,
                                ',',
                                '.'
                            ) }}
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

                                @forelse($produtosMaisComprados as $produto)

                                    <tr>

                                        <td>
                                            {{ $produto->nome }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format(
                                                $produto->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $produto->custo_medio,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Sem produtos.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 11 - ULTIMA COMPRA --}}
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

                    @if($ultimaCompra)

                        <dl class="row mb-0">

                            <dt class="col-sm-4">
                                Pedido
                            </dt>

                            <dd class="col-sm-8">
                                #{{ $ultimaCompra->id }}
                            </dd>

                            <dt class="col-sm-4">
                                Fornecedor
                            </dt>

                            <dd class="col-sm-8">
                                {{ $ultimaCompra->fornecedor }}
                            </dd>

                            <dt class="col-sm-4">
                                Data
                            </dt>

                            <dd class="col-sm-8">
                                {{ \Carbon\Carbon::parse(
                                    $ultimaCompra->data_pedido
                                )->format('d/m/Y') }}
                            </dd>

                            <dt class="col-sm-4">
                                Status
                            </dt>

                            <dd class="col-sm-8">
                                {{ ucfirst($ultimaCompra->status) }}
                            </dd>

                            <dt class="col-sm-4">
                                Valor
                            </dt>

                            <dd class="col-sm-8 fw-bold">
                                R$ {{ number_format(
                                    $ultimaCompra->total,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </dd>

                        </dl>

                    @else

                        <div class="alert alert-info mb-0">
                            Nenhuma compra encontrada.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- MODAL 12 - ACAREACAO --}}
    <div class="modal fade" id="modalAcareacao" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header {{ $divergenciasPedidos > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">

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

                    @if($divergenciasPedidos > 0)

                        <div class="alert alert-danger">

                            Foram identificados
                            <strong>{{ $divergenciasPedidos }}</strong>
                            pedidos cuja soma dos itens n&atilde;o coincide
                            com o total registrado no pedido.

                        </div>

                        <p class="mb-0">
                            Estes registros devem ser conferidos antes
                            de usar seus valores em an&aacute;lises financeiras.
                        </p>

                    @else

                        <div class="alert alert-success mb-0">

                            <i class="bi bi-check-circle me-1"></i>

                            Nenhuma diverg&ecirc;ncia entre o total
                            dos pedidos e a soma dos itens foi encontrada
                            no recorte selecionado.

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- HISTORICO - ACESSO SECUNDARIO --}}
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

                                @forelse($historicoMensal as $mes)

                                    <tr>

                                        <td>
                                            {{ str_pad(
                                                $mes->mes,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) }}/{{ $mes->ano }}
                                        </td>

                                        <td class="text-end">
                                            {{ $mes->pedidos }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format(
                                                $mes->valor_total,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Sem hist&oacute;rico.
                                        </td>
                                    </tr>

                                @endforelse

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

@endsection