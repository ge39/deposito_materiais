@extends('layouts.app')

@section('content')

<style>
.bi-orc-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}

.bi-orc-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .65rem 1rem .70rem 1rem;
}

.bi-orc-icon {
    margin: 0;
    font-size: 1.35rem;
    line-height: 1;
}

.bi-orc-label {
    margin: 0;
    font-size: .80rem;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.bi-orc-value {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.05;
}

.bi-orc-text {
    margin: 0;
    font-size: .77rem;
    line-height: 1.2;
}

.bi-section-title {
    font-size: 1.05rem;
    font-weight: 700;
}

.bi-progress-label {
    min-width: 170px;
}

.bi-progress-count {
    min-width: 75px;
    text-align: right;
}

.bi-progress-track {
    height: 11px;
}

.bi-mini-bar {
    height: 8px;
}

.table-bi th {
    white-space: nowrap;
}

.table-bi td {
    vertical-align: middle;
}

.bi-scroll-table {
    max-height: 470px;
    overflow: auto;
}
</style>


<div class="container py-4">

    {{-- =========================================================
         CABECALHO
         ========================================================= --}}

    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>
            <h2 class="mb-1">
                <i class="bi bi-file-earmark-text me-2"></i>
                Or&ccedil;amentos
            </h2>

            <div class="text-muted">
                Desempenho comercial das cota&ccedil;&otilde;es realizadas pelos clientes na loja.
            </div>
        </div>

        <a href="{{ route('bi.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>


    {{-- =========================================================
         FILTROS
         ========================================================= --}}

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET"
                  action="{{ route('bi.orcamentos.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Data inicial
                        </label>

                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="{{ $inicio->format('Y-m-d') }}"
                        >
                    </div>


                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Data final
                        </label>

                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="{{ $fim->format('Y-m-d') }}"
                        >
                    </div>


                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            @foreach($statusDisponiveis as $statusOpcao)

                                <option
                                    value="{{ $statusOpcao }}"
                                    @selected($status === $statusOpcao)
                                >
                                    {{ $statusOpcao }}
                                </option>

                            @endforeach
                        </select>
                    </div>


                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Cliente
                        </label>

                        <select
                            name="cliente_id"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            @foreach($clientes as $cliente)

                                <option
                                    value="{{ $cliente->id }}"
                                    @selected($clienteId === (int) $cliente->id)
                                >
                                    {{ $cliente->nome }}
                                </option>

                            @endforeach
                        </select>
                    </div>


                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Produto
                        </label>

                        <select
                            name="produto_id"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            @foreach($produtos as $produto)

                                <option
                                    value="{{ $produto->id }}"
                                    @selected($produtoId === (int) $produto->id)
                                >
                                    {{ $produto->nome }}
                                </option>

                            @endforeach
                        </select>
                    </div>


                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">
                            Bairro / Regi&atilde;o
                        </label>

                        <select
                            name="bairro"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            @foreach($bairros as $bairroOpcao)

                                <option
                                    value="{{ $bairroOpcao }}"
                                    @selected($bairro === $bairroOpcao)
                                >
                                    {{ $bairroOpcao }}
                                </option>

                            @endforeach
                        </select>
                    </div>


                    <div class="col-12">

                        <div class="d-flex flex-wrap gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-funnel me-1"></i>
                                Aplicar filtros
                            </button>

                            <a
                                href="{{ route('bi.orcamentos.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Limpar
                            </a>

                            <a
                                href="{{ route('bi.orcamentos.index', [
                                    'data_inicio' => now()->format('Y-m-d'),
                                    'data_fim' => now()->format('Y-m-d'),
                                ]) }}"
                                class="btn btn-outline-secondary btn-sm align-self-center"
                            >
                                Hoje
                            </a>

                            <a
                                href="{{ route('bi.orcamentos.index', [
                                    'data_inicio' => now()->startOfWeek()->format('Y-m-d'),
                                    'data_fim' => now()->format('Y-m-d'),
                                ]) }}"
                                class="btn btn-outline-secondary btn-sm align-self-center"
                            >
                                Esta semana
                            </a>

                            <a
                                href="{{ route('bi.orcamentos.index', [
                                    'data_inicio' => now()->startOfMonth()->format('Y-m-d'),
                                    'data_fim' => now()->format('Y-m-d'),
                                ]) }}"
                                class="btn btn-outline-secondary btn-sm align-self-center"
                            >
                                Este m&ecirc;s
                            </a>

                            <a
                                href="{{ route('bi.orcamentos.index', [
                                    'data_inicio' => now()->startOfYear()->format('Y-m-d'),
                                    'data_fim' => now()->format('Y-m-d'),
                                ]) }}"
                                class="btn btn-outline-secondary btn-sm align-self-center"
                            >
                                Este ano
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>
    </div>


    {{-- =========================================================
         12 KPIs
         ========================================================= --}}

    <div class="row g-4 mb-5">

        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-primary shadow-sm">
                <div class="card-body">
                    <i class="bi bi-currency-dollar bi-orc-icon text-primary"></i>
                    <div class="bi-orc-label">Valor Total Or&ccedil;ado</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($valorTotalOrcado, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        {{ $quantidadeOrcamentos }} or&ccedil;amentos
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-primary shadow-sm">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text bi-orc-icon text-primary"></i>
                    <div class="bi-orc-label">Or&ccedil;amentos Emitidos</div>
                    <div class="bi-orc-value">
                        {{ $quantidadeOrcamentos }}
                    </div>
                    <div class="bi-orc-text">
                        Volume comercial do per&iacute;odo
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-success shadow-sm">
                <div class="card-body">
                    <i class="bi bi-percent bi-orc-icon text-success"></i>
                    <div class="bi-orc-label">Taxa de Convers&atilde;o</div>
                    <div class="bi-orc-value">
                        {{ number_format($taxaConversao, 1, ',', '.') }}%
                    </div>
                    <div class="bi-orc-text">
                        {{ $quantidadeFaturados }} faturados
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-success shadow-sm">
                <div class="card-body">
                    <i class="bi bi-cash-stack bi-orc-icon text-success"></i>
                    <div class="bi-orc-label">Valor Convertido</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($valorFaturado, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        Or&ccedil;amentos faturados
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-info shadow-sm">
                <div class="card-body">
                    <i class="bi bi-receipt bi-orc-icon text-info"></i>
                    <div class="bi-orc-label">Ticket M&eacute;dio Or&ccedil;ado</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($ticketMedioOrcado, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        M&eacute;dia das cota&ccedil;&otilde;es
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-info shadow-sm">
                <div class="card-body">
                    <i class="bi bi-cart-check bi-orc-icon text-info"></i>
                    <div class="bi-orc-label">Ticket M&eacute;dio Vendido</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($ticketMedioVendido, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        {{ $quantidadeVendas }} vendas finalizadas
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-danger shadow-sm">
                <div class="card-body">
                    <i class="bi bi-graph-down-arrow bi-orc-icon text-danger"></i>
                    <div class="bi-orc-label">Valor Perdido</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($valorPerdido, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        {{ $quantidadePerdidos }} cancelados / expirados
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-warning shadow-sm">
                <div class="card-body">
                    <i class="bi bi-hourglass-split bi-orc-icon text-warning"></i>
                    <div class="bi-orc-label">Oportunidades em Aberto</div>
                    <div class="bi-orc-value">
                        {{ $quantidadeEmAberto }}
                    </div>
                    <div class="bi-orc-text">
                        R$ {{ number_format($valorEmAberto, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-primary shadow-sm">
                <div class="card-body">
                    <i class="bi bi-person bi-orc-icon text-primary"></i>
                    <div class="bi-orc-label">Cliente Destaque</div>
                    <div class="bi-orc-value">
                        {{ $clienteDestaque->nome ?? '—' }}
                    </div>
                    <div class="bi-orc-text">
                        @if($clienteDestaque)
                            R$ {{ number_format($clienteDestaque->valor_total, 2, ',', '.') }}
                        @else
                            Sem dados
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-primary shadow-sm">
                <div class="card-body">
                    <i class="bi bi-box-seam bi-orc-icon text-primary"></i>
                    <div class="bi-orc-label">Produto Mais Or&ccedil;ado</div>
                    <div class="bi-orc-value">
                        {{ $produtoMaisOrcado->nome ?? '—' }}
                    </div>
                    <div class="bi-orc-text">
                        @if($produtoMaisOrcado)
                            {{ number_format($produtoMaisOrcado->quantidade, 2, ',', '.') }} un.
                        @else
                            Sem dados
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card border-secondary shadow-sm">
                <div class="card-body">
                    <i class="bi bi-tags bi-orc-icon text-secondary"></i>
                    <div class="bi-orc-label">Descontos Concedidos</div>
                    <div class="bi-orc-value">
                        R$ {{ number_format($descontoTotal, 2, ',', '.') }}
                    </div>
                    <div class="bi-orc-text">
                        Descontos nos itens or&ccedil;ados
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 col-md-6">
            <div class="card bi-orc-card {{ $divergencias > 0 ? 'border-danger' : 'border-success' }} shadow-sm">
                <div class="card-body">
                    <i class="bi bi-shield-check bi-orc-icon {{ $divergencias > 0 ? 'text-danger' : 'text-success' }}"></i>
                    <div class="bi-orc-label">Acarea&ccedil;&atilde;o / Aten&ccedil;&atilde;o</div>
                    <div class="bi-orc-value">
                        {{ $divergencias }}
                    </div>
                    <div class="bi-orc-text">
                        diverg&ecirc;ncias encontradas
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- =========================================================
         FUNIL COMERCIAL
         ========================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white">
            <div class="bi-section-title">
                <i class="bi bi-funnel me-2"></i>
                Funil de Or&ccedil;amentos
            </div>

            <small class="text-muted">
                Situa&ccedil;&atilde;o real dos or&ccedil;amentos no fluxo comercial.
            </small>
        </div>

        <div class="card-body">

            @foreach($statusFunil as $statusLinha)

                @php
                    $registro = $funil->get($statusLinha);

                    $qtdStatus =
                        (int) ($registro->quantidade ?? 0);

                    $valorStatus =
                        (float) ($registro->valor_total ?? 0);

                    $percentualStatus =
                        $quantidadeOrcamentos > 0
                            ? ($qtdStatus / $quantidadeOrcamentos) * 100
                            : 0;
                @endphp

                <div class="d-flex align-items-center gap-3 mb-3">

                    <div class="bi-progress-label">
                        {{ $statusLinha }}
                    </div>

                    <div class="progress flex-grow-1 bi-progress-track">

                        <div
                            class="progress-bar"
                            role="progressbar"
                            style="width: {{ min(100, $percentualStatus) }}%"
                        ></div>

                    </div>

                    <div class="bi-progress-count">
                        <strong>{{ $qtdStatus }}</strong>
                    </div>

                    <div style="min-width: 130px;"
                         class="text-end text-muted">
                        R$ {{ number_format($valorStatus, 2, ',', '.') }}
                    </div>

                </div>

            @endforeach

        </div>
    </div>


    {{-- =========================================================
         ORCADO X VENDIDO
         ========================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white">
            <div class="bi-section-title">
                <i class="bi bi-arrow-left-right me-2"></i>
                Produtos Or&ccedil;ados × Vendidos
            </div>

            <small class="text-muted">
                Compara a demanda cotada com as vendas finalizadas no mesmo per&iacute;odo.
            </small>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive bi-scroll-table">

                <table class="table table-hover table-bi mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-end">Qtd. or&ccedil;ada</th>
                            <th class="text-end">Valor or&ccedil;ado</th>
                            <th class="text-end">Qtd. vendida</th>
                            <th class="text-end">Valor vendido</th>
                            <th class="text-end">&Iacute;ndice comparativo</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($comparativoProdutos as $linha)

                        <tr>
                            <td>{{ $linha->nome }}</td>

                            <td class="text-end">
                                {{ number_format($linha->quantidade_orcada, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                R$ {{ number_format($linha->valor_orcado, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($linha->quantidade_vendida, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                R$ {{ number_format($linha->valor_vendido, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($linha->indice_realizacao, 1, ',', '.') }}%
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="text-center text-muted py-4">
                                Sem dados no per&iacute;odo.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>


    {{-- =========================================================
         ABC ORCADO X ABC VENDIDO
         ========================================================= --}}

    <div class="row g-4 mb-4">

        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        Curva ABC — Or&ccedil;ado
                    </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-bi mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th>Produto</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>

                            <tbody>

                            @foreach($abcOrcado->take(15) as $linha)

                                <tr>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            {{ $linha->classe }}
                                        </span>
                                    </td>

                                    <td>{{ $linha->nome }}</td>

                                    <td class="text-end">
                                        R$ {{ number_format($linha->valor, 2, ',', '.') }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($linha->percentual, 1, ',', '.') }}%
                                    </td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        Curva ABC — Vendido
                    </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-bi mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th>Produto</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>

                            <tbody>

                            @foreach($abcVendido->take(15) as $linha)

                                <tr>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            {{ $linha->classe }}
                                        </span>
                                    </td>

                                    <td>{{ $linha->nome }}</td>

                                    <td class="text-end">
                                        R$ {{ number_format($linha->valor, 2, ',', '.') }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($linha->percentual, 1, ',', '.') }}%
                                    </td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         TEMPORAL
         ========================================================= --}}

    <div class="row g-4 mb-4">

        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        <i class="bi bi-calendar-week me-2"></i>
                        Or&ccedil;amentos por Dia da Semana
                    </div>
                </div>

                <div class="card-body">

                    @forelse($diasSemana as $linha)

                        @php
                            $largura =
                                ($linha->quantidade / $maiorDiaQuantidade) * 100;
                        @endphp

                        <div class="mb-3">

                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $linha->dia }}</span>

                                <strong>
                                    {{ $linha->quantidade }}
                                </strong>
                            </div>

                            <div class="progress bi-mini-bar">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $largura }}%"
                                ></div>
                            </div>

                        </div>

                    @empty

                        <div class="text-muted text-center">
                            Sem dados.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        <i class="bi bi-clock me-2"></i>
                        Hor&aacute;rios de Maior Procura
                    </div>
                </div>

                <div class="card-body">

                    @forelse($horarios as $linha)

                        @php
                            $largura =
                                ($linha->quantidade / $maiorHoraQuantidade) * 100;
                        @endphp

                        <div class="mb-2">

                            <div class="d-flex justify-content-between mb-1">

                                <span>
                                    {{ str_pad($linha->hora, 2, '0', STR_PAD_LEFT) }}h
                                    &ndash;
                                    {{ str_pad(($linha->hora + 1) % 24, 2, '0', STR_PAD_LEFT) }}h
                                </span>

                                <strong>
                                    {{ $linha->quantidade }}
                                </strong>

                            </div>

                            <div class="progress bi-mini-bar">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $largura }}%"
                                ></div>
                            </div>

                        </div>

                    @empty

                        <div class="text-muted text-center">
                            Sem dados.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CLIENTES / REGIAO
         ========================================================= --}}

    <div class="row g-4 mb-4">

        <div class="col-lg-7">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        Clientes com Maior Valor Or&ccedil;ado
                    </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-bi mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Regi&atilde;o</th>
                                    <th class="text-end">Or&ccedil;amentos</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>

                            <tbody>

                            @foreach($rankingClientes as $linha)

                                <tr>
                                    <td>{{ $linha->nome }}</td>

                                    <td>
                                        {{ $linha->bairro ?: '—' }}
                                        @if($linha->cidade)
                                            / {{ $linha->cidade }}
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        {{ $linha->quantidade }}
                                    </td>

                                    <td class="text-end">
                                        R$ {{ number_format($linha->valor_total, 2, ',', '.') }}
                                    </td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-5">

            <div class="card shadow-sm h-100">

                <div class="card-header bg-white">
                    <div class="bi-section-title">
                        Or&ccedil;amentos por Bairro / Regi&atilde;o
                    </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-bi mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Bairro</th>
                                    <th class="text-end">Qtd.</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>

                            <tbody>

                            @foreach($regioes as $linha)

                                <tr>
                                    <td>{{ $linha->bairro }}</td>

                                    <td class="text-end">
                                        {{ $linha->quantidade }}
                                    </td>

                                    <td class="text-end">
                                        R$ {{ number_format($linha->valor_total, 2, ',', '.') }}
                                    </td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ORCAMENTOS DO PERIODO
         ========================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <div>
                <div class="bi-section-title">
                    <i class="bi bi-files me-2"></i>
                    Or&ccedil;amentos do Per&iacute;odo
                </div>

                <small class="text-muted">
                    Consulte o or&ccedil;amento completo feito pelo cliente.
                </small>
            </div>

            <span class="badge text-bg-secondary">
                {{ $orcamentosLista->count() }} registros
            </span>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bi mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>N&ordm;</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Validade</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">A&ccedil;&atilde;o</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($orcamentosLista as $orcamento)

                        <tr>

                            <td>
                                <strong>
                                    {{ $orcamento->codigo_orcamento ?? $orcamento->id }}
                                </strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $orcamento->cliente_nome }}
                            </td>

                            <td>
                                {{ $orcamento->status }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                            </td>

                            <td class="text-end fw-semibold">
                                R$ {{ number_format($orcamento->total, 2, ',', '.') }}
                            </td>

                            <td class="text-center">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#orcamento{{ $orcamento->id }}"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    Abrir
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                Nenhum or&ccedil;amento encontrado.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACAREACAO
         ========================================================= --}}

    @if($divergencias > 0)

        <div class="card border-danger shadow-sm mb-4">

            <div class="card-header bg-white">
                <div class="bi-section-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Acarea&ccedil;&atilde;o
                </div>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-sm mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Or&ccedil;amento</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Itens</th>
                                <th class="text-end">Diferen&ccedil;a</th>
                            </tr>
                        </thead>

                        <tbody>

                        @foreach($detalhesDivergencias as $linha)

                            <tr>
                                <td>
                                    {{ $linha->codigo_orcamento ?? $linha->id }}
                                </td>

                                <td>
                                    {{ $linha->data_orcamento }}
                                </td>

                                <td>
                                    {{ $linha->status }}
                                </td>

                                <td class="text-end">
                                    R$ {{ number_format($linha->total, 2, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    R$ {{ number_format($linha->total_itens, 2, ',', '.') }}
                                </td>

                                <td class="text-end text-danger fw-bold">
                                    R$ {{ number_format($linha->diferenca, 2, ',', '.') }}
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif


    {{-- =========================================================
         MODAIS - ORCAMENTO COMPLETO
         ========================================================= --}}

    @foreach($orcamentosLista as $orcamento)

        @php
            $itensDocumento =
                $itensPorOrcamento->get(
                    $orcamento->id,
                    collect()
                );
        @endphp

        <div
            class="modal fade"
            id="orcamento{{ $orcamento->id }}"
            tabindex="-1"
        >

            <div class="modal-dialog modal-xl modal-dialog-scrollable">

                <div class="modal-content">

                    <div class="modal-header">

                        <div>

                            <h5 class="modal-title">
                                Or&ccedil;amento
                                {{ $orcamento->codigo_orcamento ?? $orcamento->id }}
                            </h5>

                            <small class="text-muted">
                                {{ $orcamento->cliente_nome }}
                            </small>

                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>

                    </div>


                    <div class="modal-body">

                        <div class="row g-3 mb-4">

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Data
                                </small>

                                <strong>
                                    {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Validade
                                </small>

                                <strong>
                                    {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Status
                                </small>

                                <strong>
                                    {{ $orcamento->status }}
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Entrega
                                </small>

                                <strong>
                                    {{ $orcamento->tipo_entrega ?? '—' }}
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Frete
                                </small>

                                <strong>
                                    R$ {{ number_format($orcamento->valor_frete ?? 0, 2, ',', '.') }}
                                </strong>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block">
                                    Total
                                </small>

                                <strong class="text-primary">
                                    R$ {{ number_format($orcamento->total, 2, ',', '.') }}
                                </strong>
                            </div>

                        </div>


                        <h6 class="border-bottom pb-2 mb-3">
                            Produtos do Or&ccedil;amento
                        </h6>


                        <div class="table-responsive">

                            <table class="table table-sm table-bordered align-middle">

                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-end">Solicitado</th>
                                        <th class="text-end">Atendido</th>
                                        <th class="text-end">Pendente</th>
                                        <th class="text-end">Pre&ccedil;o unit.</th>
                                        <th class="text-end">Desc. %</th>
                                        <th class="text-end">Desc. R$</th>
                                        <th class="text-end">L&iacute;quido</th>
                                        <th class="text-end">Subtotal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>

                                @forelse($itensDocumento as $item)

                                    <tr>

                                        <td>
                                            {{ $item->produto_nome }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item->quantidade_solicitada ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item->quantidade_atendida ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item->quantidade_pendente ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format($item->preco_unitario ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($item->desconto_percentual ?? 0, 2, ',', '.') }}%
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format($item->valor_desconto ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            R$ {{ number_format($item->preco_liquido ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            R$ {{ number_format($item->subtotal ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td>
                                            {{ $item->item_status ?? '—' }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td
                                            colspan="10"
                                            class="text-center text-muted"
                                        >
                                            Nenhum item encontrado.
                                        </td>
                                    </tr>

                                @endforelse

                                </tbody>

                            </table>

                        </div>


                        @if($orcamento->endereco_entrega)

                            <div class="mt-3">
                                <small class="text-muted d-block">
                                    Endere&ccedil;o de entrega
                                </small>

                                <div>
                                    {{ $orcamento->endereco_entrega }}
                                    @if($orcamento->bairro_entrega)
                                        — {{ $orcamento->bairro_entrega }}
                                    @endif
                                </div>
                            </div>

                        @endif


                        @if($orcamento->observacoes)

                            <div class="mt-3">
                                <small class="text-muted d-block">
                                    Observa&ccedil;&otilde;es
                                </small>

                                <div>
                                    {{ $orcamento->observacoes }}
                                </div>
                            </div>

                        @endif

                    </div>


                    <div class="modal-footer">

                        <div class="me-auto fs-5 fw-bold">
                            Total:
                            R$ {{ number_format($orcamento->total, 2, ',', '.') }}
                        </div>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Fechar
                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endforeach

</div>

@endsection