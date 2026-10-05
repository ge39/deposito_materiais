@extends('layouts.app')

@section('content')

@php
    $comparacao = $indicadores['comparacao'];

    $formatarVariacao = function ($item) {
        if ($item['percentual'] === null) {
            return 'Sem base anterior';
        }

        $sinal =
            $item['percentual'] > 0
                ? '+'
                : '';

        return
            $sinal
            . number_format(
                $item['percentual'],
                2,
                ',',
                '.'
            )
            . '%';
    };

    $classeVariacao = function ($item) {
        if ($item['direcao'] === 'alta') {
            return 'text-success';
        }

        if ($item['direcao'] === 'queda') {
            return 'text-danger';
        }

        return 'text-muted';
    };

    $iconeVariacao = function ($item) {
        if ($item['direcao'] === 'alta') {
            return 'bi-arrow-up-right';
        }

        if ($item['direcao'] === 'queda') {
            return 'bi-arrow-down-right';
        }

        return 'bi-dash';
    };
@endphp

<div class="container-fluid">

    {{-- CABEÇALHO --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-speedometer2 me-2"></i>
                Visão Executiva

            </h1>

            <p class="text-muted mb-0">

                Indicadores consolidados do ERP.

            </p>

        </div>

        <a
            href="{{ route('bi.index') }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Central BI

        </a>

    </div>


    {{-- FILTRO --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('bi.executivo.index') }}"
                class="row g-3 align-items-end">

                <div class="col-md-4">

                    <label
                        for="data_inicio"
                        class="form-label">

                        Data inicial

                    </label>

                    <input
                        type="date"
                        class="form-control
                               @error('data_inicio')
                                   is-invalid
                               @enderror"
                        id="data_inicio"
                        name="data_inicio"
                        value="{{ old(
                            'data_inicio',
                            $inicio->format('Y-m-d')
                        ) }}">

                    @error('data_inicio')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                <div class="col-md-4">

                    <label
                        for="data_fim"
                        class="form-label">

                        Data final

                    </label>

                    <input
                        type="date"
                        class="form-control
                               @error('data_fim')
                                   is-invalid
                               @enderror"
                        id="data_fim"
                        name="data_fim"
                        value="{{ old(
                            'data_fim',
                            $fim->format('Y-m-d')
                        ) }}">

                    @error('data_fim')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-primary w-100">

                        <i class="bi bi-funnel me-1"></i>
                        Aplicar período

                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- PERÍODOS --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-6">

            <div class="alert alert-light border shadow-sm mb-0">

                <i class="bi bi-calendar3 me-2"></i>

                Período atual:

                <strong>
                    {{ $inicio->format('d/m/Y') }}
                </strong>

                até

                <strong>
                    {{ $fim->format('d/m/Y') }}
                </strong>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="alert alert-light border shadow-sm mb-0">

                <i class="bi bi-arrow-left-right me-2"></i>

                Comparação:

                <strong>
                    {{
                        $comparacao[
                            'inicio_anterior'
                        ]->format('d/m/Y')
                    }}
                </strong>

                até

                <strong>
                    {{
                        $comparacao[
                            'fim_anterior'
                        ]->format('d/m/Y')
                    }}
                </strong>

            </div>

        </div>

    </div>


    {{-- KPIs --}}
    <div class="row g-4">


        {{-- FATURAMENTO --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Faturamento
                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        {{ number_format(
                            $indicadores['faturamento'],
                            2,
                            ',',
                            '.'
                        ) }}

                    </div>

                    <div class="small mt-2 {{ $classeVariacao(
                        $comparacao['faturamento']
                    ) }}">

                        <i class="bi {{
                            $iconeVariacao(
                                $comparacao['faturamento']
                            )
                        }}"></i>

                        {{
                            $formatarVariacao(
                                $comparacao['faturamento']
                            )
                        }}

                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        {{-- VENDAS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Vendas finalizadas
                    </div>

                    <div class="fs-3 fw-bold">

                        {{
                            number_format(
                                $indicadores[
                                    'vendas_finalizadas'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small mt-2 {{ $classeVariacao(
                        $comparacao[
                            'vendas_finalizadas'
                        ]
                    ) }}">

                        <i class="bi {{
                            $iconeVariacao(
                                $comparacao[
                                    'vendas_finalizadas'
                                ]
                            )
                        }}"></i>

                        {{
                            $formatarVariacao(
                                $comparacao[
                                    'vendas_finalizadas'
                                ]
                            )
                        }}

                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        {{-- TICKET --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Ticket médio
                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        {{
                            number_format(
                                $indicadores[
                                    'ticket_medio'
                                ],
                                2,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small mt-2 {{ $classeVariacao(
                        $comparacao[
                            'ticket_medio'
                        ]
                    ) }}">

                        <i class="bi {{
                            $iconeVariacao(
                                $comparacao[
                                    'ticket_medio'
                                ]
                            )
                        }}"></i>

                        {{
                            $formatarVariacao(
                                $comparacao[
                                    'ticket_medio'
                                ]
                            )
                        }}

                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        {{-- ESTOQUE --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Estoque a custo atual

                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        {{
                            number_format(
                                $indicadores[
                                    'valor_estoque_atual'
                                ],
                                2,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small text-muted mt-2">

                        Estoque atual × custo real de entrada

                    </div>

                </div>

            </div>

        </div>


        {{-- COMPRAS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Compras recebidas

                    </div>

                    <div class="fs-4 fw-bold">

                        R$
                        {{
                            number_format(
                                $indicadores[
                                    'compras_recebidas_valor'
                                ],
                                2,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small mt-2 {{ $classeVariacao(
                        $comparacao[
                            'compras_recebidas_valor'
                        ]
                    ) }}">

                        <i class="bi {{
                            $iconeVariacao(
                                $comparacao[
                                    'compras_recebidas_valor'
                                ]
                            )
                        }}"></i>

                        {{
                            $formatarVariacao(
                                $comparacao[
                                    'compras_recebidas_valor'
                                ]
                            )
                        }}

                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        {{-- ORÇAMENTOS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Orçamentos faturados

                    </div>

                    <div class="fs-3 fw-bold">

                        {{
                            number_format(
                                $indicadores[
                                    'orcamentos_faturados_quantidade'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small text-muted mt-2">

                        R$
                        {{
                            number_format(
                                $indicadores[
                                    'orcamentos_faturados_valor'
                                ],
                                2,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                </div>

            </div>

        </div>


        {{-- ENTREGAS CONCLUÍDAS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Entregas concluídas

                    </div>

                    <div class="fs-3 fw-bold">

                        {{
                            number_format(
                                $indicadores[
                                    'entregas_concluidas'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small mt-2 {{ $classeVariacao(
                        $comparacao[
                            'entregas_concluidas'
                        ]
                    ) }}">

                        <i class="bi {{
                            $iconeVariacao(
                                $comparacao[
                                    'entregas_concluidas'
                                ]
                            )
                        }}"></i>

                        {{
                            $formatarVariacao(
                                $comparacao[
                                    'entregas_concluidas'
                                ]
                            )
                        }}

                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        {{-- ENTREGAS ABERTAS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Entregas em aberto

                    </div>

                    <div class="fs-3 fw-bold">

                        {{
                            number_format(
                                $indicadores[
                                    'entregas_em_aberto'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small text-muted mt-2">

                        Situação operacional atual

                    </div>

                </div>

            </div>

        </div>


        {{-- MOVIMENTAÇÕES --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Movimentações de caixa

                    </div>

                    <div class="fs-3 fw-bold">

                        {{
                            number_format(
                                $indicadores[
                                    'movimentacoes_caixa'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small text-muted mt-2">

                        Registros no período

                    </div>

                </div>

            </div>

        </div>


        {{-- SANGRIAS --}}
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Sangrias

                    </div>

                    <div class="fs-4 fw-bold">

                        R$
                        {{
                            number_format(
                                $indicadores[
                                    'valor_sangrias'
                                ],
                                2,
                                ',',
                                '.'
                            )
                        }}

                    </div>

                    <div class="small text-muted mt-2">

                        Valor registrado no período

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- GRÁFICOS --}}
    <div class="row g-4 mt-1">

        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-graph-up me-2"></i>
                        Faturamento por dia

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoFaturamento"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-bar-chart me-2"></i>
                        Vendas por dia

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoVendas"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-truck me-2"></i>
                        Entregas por status

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoEntregas"></canvas>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="alert alert-secondary mt-4 mb-0">

        <i class="bi bi-info-circle me-2"></i>

        Lucro e margem permanecem fora desta versão.
        O custo histórico da venda será homologado antes
        desses indicadores serem disponibilizados.

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js">
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const faturamento = @json(
        $indicadores['series']['faturamento_diario']
    );

    const vendas = @json(
        $indicadores['series']['vendas_diarias']
    );

    const entregas = @json(
        $indicadores['series']['entregas_por_status']
    );


    new Chart(
        document.getElementById('graficoFaturamento'),
        {
            type: 'line',

            data: {
                labels: faturamento.labels,

                datasets: [
                    {
                        label: 'Faturamento (R$)',
                        data: faturamento.valores,
                        tension: 0.25,
                        fill: false
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        ticks: {
                            callback: function (value) {
                                return 'R$ ' +
                                    Number(value).toLocaleString(
                                        'pt-BR',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }
                                    );
                            }
                        }
                    }
                },

                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'R$ ' +
                                    Number(
                                        context.raw
                                    ).toLocaleString(
                                        'pt-BR',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }
                                    );
                            }
                        }
                    }
                }
            }
        }
    );


    new Chart(
        document.getElementById('graficoVendas'),
        {
            type: 'bar',

            data: {
                labels: vendas.labels,

                datasets: [
                    {
                        label: 'Vendas finalizadas',
                        data: vendas.valores
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );


    new Chart(
        document.getElementById('graficoEntregas'),
        {
            type: 'bar',

            data: {
                labels: entregas.labels,

                datasets: [
                    {
                        label: 'Entregas',
                        data: entregas.valores
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                indexAxis: 'y',

                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );

});
</script>

@endsection