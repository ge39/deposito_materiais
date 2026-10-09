@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-boxes me-2"></i>
                Produtos & Estoque
            </h1>

            <p class="text-muted mb-0">
                Giro, Curva ABC, estoque parado, cobertura e ruptura.
            </p>
        </div>

        <a
            href="{{ route('bi.index') }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>


    {{-- FILTROS --}}

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('bi.produtos.index') }}"
                class="row g-3 align-items-end">

                <div class="col-md-2">

                    <label class="form-label">
                        Data inicial
                    </label>

                    <input
                        type="date"
                        name="data_inicio"
                        value="{{ $filtros['data_inicio'] }}"
                        class="form-control">

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Data final
                    </label>

                    <input
                        type="date"
                        name="data_fim"
                        value="{{ $filtros['data_fim'] }}"
                        class="form-control">

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Produto / SKU
                    </label>

                    <input
                        type="text"
                        name="q"
                        value="{{ $filtros['q'] ?? '' }}"
                        class="form-control">

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Categoria
                    </label>

                    <select
                        name="categoria_id"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        @foreach ($categorias as $categoria)

                            <option
                                value="{{ $categoria->id }}"
                                @selected(
                                    (string) (
                                        $filtros['categoria_id']
                                        ?? ''
                                    )
                                    ===
                                    (string) $categoria->id
                                )>

                                {{ $categoria->nome }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Fornecedor
                    </label>

                    <select
                        name="fornecedor_id"
                        class="form-select">

                        <option value="">
                            Todos
                        </option>

                        @foreach ($fornecedores as $fornecedor)

                            <option
                                value="{{ $fornecedor->id }}"
                                @selected(
                                    (string) (
                                        $filtros['fornecedor_id']
                                        ?? ''
                                    )
                                    ===
                                    (string) $fornecedor->id
                                )>

                                {{ $fornecedor->nome }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Marca
                    </label>

                    <select
                        name="marca_id"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        @foreach ($marcas as $marca)

                            <option
                                value="{{ $marca->id }}"
                                @selected(
                                    (string) (
                                        $filtros['marca_id']
                                        ?? ''
                                    )
                                    ===
                                    (string) $marca->id
                                )>

                                {{ $marca->nome }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Promoção
                    </label>

                    <select
                        name="promocao"
                        class="form-select">

                        <option value="">
                            Todas
                        </option>

                        <option
                            value="1"
                            @selected(
                                ($filtros['promocao'] ?? '')
                                === '1'
                            )>

                            Sim
                        </option>

                        <option
                            value="0"
                            @selected(
                                ($filtros['promocao'] ?? '')
                                === '0'
                            )>

                            Não
                        </option>

                    </select>

                </div>


                <div class="col-md-4">

                    <button
                        class="btn btn-primary"
                        type="submit">

                        <i class="bi bi-funnel me-1"></i>
                        Aplicar filtros
                    </button>

                    <a
                        href="{{ route('bi.produtos.index') }}"
                        class="btn btn-outline-secondary ms-1">

                        Limpar
                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- RESUMO --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Produtos ativos
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $dados['resumo']['produtos_ativos'] }}
                    </div>

                    <a
                        href="{{ route(
                            'bi.produtos.ativos',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-primary mt-3">

                        Ver produtos
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card shadow-sm h-100 border-danger">

                <div class="card-body">

                    <div class="small text-muted">
                        Ruptura / estoque zero
                    </div>

                    <div class="fs-2 fw-bold text-danger">
                        {{ $dados['resumo']['estoque_zero'] }}
                    </div>

                    <a
                        href="{{ route(
                            'bi.produtos.estoque-zero',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-danger mt-3">

                        Ver produtos
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Abaixo do mínimo
                    </div>

                    <div class="fs-2 fw-bold">
                        {{ $dados['resumo']['abaixo_minimo'] }}
                    </div>

                    <a
                        href="{{ route(
                            'bi.produtos.abaixo-minimo',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-warning mt-3">

                        Ver produtos
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Valor financeiro do estoque
                    </div>

                    <div class="fs-4 fw-bold">
                        R$
                        {{
                            number_format(
                                $dados['resumo']['valor_estoque'],
                                2,
                                ',',
                                '.'
                            )
                        }}
                    </div>

                    <a
                        href="{{ route(
                            'bi.produtos.valor-estoque',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-success mt-3">

                        Ver composição
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- GIRO / FATURAMENTO --}}

    <div class="row g-4 mb-4">

        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Top 10 — Mais vendidos
                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-end">
                                    Quantidade
                                </th>
                                <th class="text-end">
                                    Faturamento
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach (
                                $dados['mais_vendidos']
                                as $produto
                            )

                                <tr>
                                    <td>
                                        {{ $produto->nome }}
                                    </td>

                                    <td class="text-end">
                                        {{
                                            number_format(
                                                $produto->quantidade_vendida,
                                                3,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-end">
                                        R$
                                        {{
                                            number_format(
                                                $produto->faturamento,
                                                2,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Top 10 — Menos vendidos
                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-end">
                                    Quantidade
                                </th>
                                <th class="text-end">
                                    Estoque
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach (
                                $dados['menos_vendidos']
                                as $produto
                            )

                                <tr>
                                    <td>
                                        {{ $produto->nome }}
                                    </td>

                                    <td class="text-end">
                                        {{
                                            number_format(
                                                $produto->quantidade_vendida,
                                                3,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-end">
                                        {{
                                            number_format(
                                                $produto->estoque_disponivel,
                                                3,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Top 10 — Maior faturamento
                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-end">
                                    Faturamento
                                </th>
                                <th class="text-end">
                                    Vendas
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach (
                                $dados['maior_faturamento']
                                as $produto
                            )

                                <tr>
                                    <td>
                                        {{ $produto->nome }}
                                    </td>

                                    <td class="text-end">
                                        R$
                                        {{
                                            number_format(
                                                $produto->faturamento,
                                                2,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-end">
                                        {{ $produto->vendas }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Menor faturamento com venda
                    </strong>
                </div>

                <div class="table-responsive">

                    <table class="table table-sm table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-end">
                                    Faturamento
                                </th>
                                <th class="text-end">
                                    Quantidade
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach (
                                $dados['menor_faturamento']
                                as $produto
                            )

                                <tr>
                                    <td>
                                        {{ $produto->nome }}
                                    </td>

                                    <td class="text-end">
                                        R$
                                        {{
                                            number_format(
                                                $produto->faturamento,
                                                2,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-end">
                                        {{
                                            number_format(
                                                $produto->quantidade_vendida,
                                                3,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    {{-- CURVA ABC --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>
                Curva ABC por faturamento
            </strong>

            <span class="small text-muted">
                A até 80% • B até 95% • C restante
            </span>

        </div>

        <div class="card-body">

            <div class="row g-3 mb-3">

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">
                            Classe A
                        </div>
                        <div class="fs-3 fw-bold text-success">
                            {{ $dados['abc']['classe_a'] }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">
                            Classe B
                        </div>
                        <div class="fs-3 fw-bold text-warning">
                            {{ $dados['abc']['classe_b'] }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">
                            Classe C
                        </div>
                        <div class="fs-3 fw-bold">
                            {{ $dados['abc']['classe_c'] }}
                        </div>
                    </div>
                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-sm table-hover">

                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Classe</th>
                            <th class="text-end">
                                Faturamento
                            </th>
                            <th class="text-end">
                                Acumulado
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach (
                            $dados['abc']['ranking']
                            as $produto
                        )

                            <tr>

                                <td>
                                    {{ $produto->nome }}
                                </td>

                                <td>
                                    <span class="badge text-bg-secondary">
                                        {{ $produto->abc }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    R$
                                    {{
                                        number_format(
                                            $produto->faturamento,
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </td>

                                <td class="text-end">
                                    {{
                                        number_format(
                                            $produto->percentual_acumulado,
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}%
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ESTOQUE PARADO / COBERTURA / RUPTURA --}}

    <div class="row g-4 mb-4">

        <div class="col-xl-4">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Estoque parado
                    </strong>
                </div>

                <div class="card-body">

                    <div class="display-6 fw-bold mb-2">
                        {{ $dados['resumo']['estoque_parado'] }}
                    </div>

                    <p class="text-muted">
                        Produtos com saldo positivo e nenhuma
                        venda no período selecionado.
                    </p>

                    <a
                        href="{{ route(
                            'bi.produtos.estoque-parado',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-primary">

                        Ver produtos
                    </a>

                </div>

            </div>

        </div>


        <div class="col-xl-4">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>
                        Cobertura de estoque
                    </strong>
                </div>

                <div class="card-body">

                    <p class="text-muted">
                        Estimativa em dias baseada na média
                        diária de vendas do período.
                    </p>

                    @forelse (
                        $dados['cobertura']['criticos']
                        as $produto
                    )

                        <div class="d-flex justify-content-between border-bottom py-1">

                            <span>
                                {{ $produto->nome }}
                            </span>

                            <strong>
                                {{
                                    number_format(
                                        $produto->cobertura_dias,
                                        1,
                                        ',',
                                        '.'
                                    )
                                }}
                                dias
                            </strong>

                        </div>

                    @empty

                        <span class="text-muted">
                            Sem dados suficientes para calcular.
                        </span>

                    @endforelse

                </div>

            </div>

        </div>


        <div class="col-xl-4">

            <div class="card shadow-sm h-100 border-danger">

                <div class="card-header">
                    <strong>
                        Ruptura
                    </strong>
                </div>

                <div class="card-body">

                    <div class="display-6 fw-bold text-danger mb-2">
                        {{ $dados['resumo']['estoque_zero'] }}
                    </div>

                    <p class="text-muted">
                        Produtos com saldo disponível consolidado
                        dos lotes igual ou menor que zero.
                    </p>

                    <a
                        href="{{ route(
                            'bi.produtos.estoque-zero',
                            $filtros
                        ) }}"
                        class="btn btn-sm btn-outline-danger">

                        Ver produtos
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- MARGEM --}}

    <div class="card shadow-sm border-warning mb-4">

        <div class="card-header">
            <strong>
                Margem e valor agregado
            </strong>
        </div>

        <div class="card-body">

            <div class="alert alert-warning mb-0">

                <i class="bi bi-lock me-1"></i>

                Indicador ainda não publicado.
                O custo histórico das vendas precisa ser homologado
                antes de calcular margem, lucro e valor agregado,
                evitando usar custo atual em vendas antigas.

            </div>

        </div>

    </div>


    <div class="small text-muted">

        Período analítico:
        {{
            $dados['periodo']['inicio']
                ->format('d/m/Y')
        }}
        até
        {{
            $dados['periodo']['fim']
                ->format('d/m/Y')
        }}
        —
        {{ $dados['periodo']['dias'] }}
        dia(s).

        O estoque físico é o saldo atual consolidado dos lotes.

    </div>

</div>

@endsection