@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-bar-chart-line me-2"></i>
                BI &amp; Relat&oacute;rios
            </h1>

            <p class="text-muted mb-0">
                Central de intelig&ecirc;ncia gerencial e relat&oacute;rios do ERP.
            </p>
        </div>

        <span class="badge text-bg-primary fs-6">
            BI-01
        </span>

    </div>

    <div class="alert alert-info shadow-sm">
        <i class="bi bi-info-circle-fill me-2"></i>

        Esta central ser&aacute; constru&iacute;da sobre os dados e regras j&aacute; homologados
        no ERP, sem criar uma segunda fonte de verdade.
    </div>

    <div class="row g-4">

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Vis&atilde;o Executiva
                    </h5>

                    <p class="card-text text-muted">
                        Faturamento, margem, compras, caixa, estoque,
                        entregas e principais indicadores da empresa.
                    </p>

                    <div class="d-flex justify-content-between align-items-center gap-2">

                        <span class="badge text-bg-success">
                            BI-02
                        </span>

                        <a
                            href="{{ route('bi.executivo.index') }}"
                            class="btn btn-sm btn-primary">

                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            Abrir dashboard

                        </a>

                    </div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-boxes me-2"></i>
                        Produtos & Estoque
                    </h5>

                    <p class="card-text text-muted">
                        Giro, curva ABC, margem, estoque parado,
                        cobertura e ruptura.
                    </p>

                    <div class="d-flex justify-content-between align-items-center gap-2">

                        <span class="badge text-bg-success">
                            BI-03
                        </span>

                        <a
                            href="{{ route('bi.produtos.index') }}"
                            class="btn btn-sm btn-primary">

                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            Abrir dashboard

                        </a>

                    </div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-cart-check me-2"></i>
                        Compras
                    </h5>

                    <p class="card-text text-muted">
                        Compras por fornecedor, produto, custo m&eacute;dio,
                        varia&ccedil;&atilde;o de pre&ccedil;os e hist&oacute;rico.
                    </p>

                    <div class="d-flex justify-content-between align-items-center gap-2">

                        <span class="badge text-bg-secondary">
                            BI-03
                        </span>

                        <a
                            href="{{ route('bi.compras.index') }}"
                            class="btn btn-sm btn-primary">

                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            Abrir dashboard

                        </a>

                    </div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-cash-stack me-2"></i>
                        Vendas
                    </h5>

                    <p class="card-text text-muted">
                        Faturamento, margem, ticket m&eacute;dio, vendedores,
                        produtos e clientes.
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
    <span class="badge text-bg-secondary">BI-04</span>

    <a href="{{ route('bi.vendas.index') }}"
       class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>
        Abrir dashboard
    </a>
</div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Or&ccedil;amentos
                    </h5>

                    <p class="card-text text-muted">
                        Convers&atilde;o, valores or&ccedil;ados, aprovados,
                        perdidos e desempenho comercial.
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
    <span class="badge text-bg-secondary">BI-04</span>

    <a href="{{ route('bi.orcamentos.index') }}"
       class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>
        Abrir dashboard
    </a>
</div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-safe me-2"></i>
                        Caixa
                    </h5>

                    <p class="card-text text-muted">
                        Entradas, sa&iacute;das, sangrias, diferen&ccedil;as,
                        fechamentos e auditoria.
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
    <span class="badge text-bg-secondary">BI-04</span>

    <a href="{{ route('bi.caixa.index') }}"
       class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>
        Abrir dashboard
    </a>
</div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-truck me-2"></i>
                        Entregas &amp; Log&iacute;stica
                    </h5>

                    <p class="card-text text-muted">
                        Entregas realizadas, atrasos, reagendamentos,
                        devolu&ccedil;&otilde;es, ve&iacute;culos e motoristas.
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto">

                        <span class="badge text-bg-secondary">
                            BI-05
                        </span>

                        <a
                            href="{{ route('bi.logistica.index') }}"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            Abrir dashboard
                        </a>

                    </div>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-people me-2"></i>
                        Funcion&aacute;rios &amp; Comiss&otilde;es
                    </h5>

                    <p class="card-text text-muted">
                        Produtividade, opera&ccedil;&otilde;es realizadas,
                        desempenho e comiss&otilde;es.
                    </p>

                    <span class="badge text-bg-secondary">
                        BI-06
                    </span>

<a
    href="{{ route('bi.funcionarios-comissoes.index') }}"
    class="btn btn-primary btn-sm"
>
    <i class="bi bi-box-arrow-up-right me-1"></i>
    Abrir dashboard
</a>

                </div>

            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title">
                        <i class="bi bi-currency-dollar me-2"></i>
                        Financeiro
                    </h5>

                    <p class="card-text text-muted">
                        Receitas, despesas, contas a receber,
                        inadimpl&ecirc;ncia e fluxo de caixa.
                    </p>

                    <span class="badge text-bg-secondary">
                        BI-07
                    </span>

                </div>

            </div>
        </div>

    </div>

</div>

@endsection