@extends('layouts.app')

@section('content')

{{-- ESTDIV04_REPOSICAO_EXCLUSIVA --}}

@php
    $reposicao = collect(
        app(\App\Services\Estoque\ReposicaoPromessasService::class)
            ->calcular()
    );

    // Mostrar somente produtos sem cobertura integral.
    $faltas = $reposicao
        ->filter(fn ($p) => (float) $p['a_repor'] > 0.0005)
        ->values();

    $entregasSemCobertura = $faltas
        ->flatMap(
            fn ($p) => collect($p['detalhes'])
                ->filter(fn ($d) => (float) $d['a_repor'] > 0.0005)
                ->pluck('entrega_id')
        )
        ->unique()
        ->count();

    $entregasAnalisadas = $reposicao
        ->flatMap(
            fn ($p) => collect($p['detalhes'])
                ->pluck('entrega_id')
        )
        ->unique()
        ->count();
@endphp

<div class="container-fluid px-3 px-lg-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-box-seam text-primary me-2"></i>
                Reposição de Estoque
            </h4>

            <p class="text-muted mb-0">
                Necessidades reais de cobertura das promessas de entrega.
            </p>
        </div>
    </div>

    {{-- INDICADORES --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-0
                        border-start border-danger border-4">
                <div class="card-body d-flex justify-content-between
                            align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            PRODUTOS A REPOR
                        </small>
                        <h3 class="fw-bold text-danger mb-0 mt-2">
                            {{ $faltas->count() }}
                        </h3>
                    </div>
                    <i class="bi bi-boxes fs-1 text-danger opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-0
                        border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between
                            align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            ENTREGAS SEM COBERTURA
                        </small>
                        <h3 class="fw-bold mb-0 mt-2">
                            {{ $entregasSemCobertura }}
                        </h3>
                    </div>
                    <i class="bi bi-truck fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-0
                        border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between
                            align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            PROMESSAS ANALISADAS
                        </small>
                        <h3 class="fw-bold mb-0 mt-2">
                            {{ $entregasAnalisadas }}
                        </h3>
                    </div>
                    <i class="bi bi-clipboard-check fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- QUANTIDADES REALMENTE A REPOR --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-primary text-white py-3">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-exclamation-circle me-2"></i>
                Produtos com necessidade de reposição
            </h6>
        </div>

        <div class="card-body">

            <p class="text-muted small mb-3">
                São exibidos somente produtos cuja necessidade das
                promessas de entrega supera a cobertura calculada pelo
                saldo líquido atual, respeitando a prioridade FIFO.
                Registros históricos de vendas PDV não entram neste cálculo.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover
                              align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-end">Demanda analisada</th>
                            <th class="text-end">Saldo líquido</th>
                            <th class="text-end">Cobertura FIFO</th>
                            <th class="text-end">A REPOR</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($faltas as $p)

                            <tr>
                                <td>
                                    <strong>{{ $p['produto'] }}</strong>
                                    <div class="text-muted small">
                                        Produto #{{ $p['produto_id'] }}
                                    </div>

                                    @foreach($p['avisos'] as $aviso)
                                        <div class="text-danger small mt-1">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            {{ $aviso }}
                                        </div>
                                    @endforeach
                                </td>

                                <td class="text-end">
                                    {{ number_format($p['solicitado'], 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($p['saldo_liquido'], 3, ',', '.') }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    {{ number_format($p['coberto'], 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    <span class="text-danger fw-bold">
                                        {{ number_format($p['a_repor'], 3, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    <details>
                                        <summary class="text-primary fw-semibold"
                                                 style="cursor:pointer">
                                            Ver orçamentos
                                        </summary>

                                        <div class="mt-2">

                                            @foreach($p['detalhes'] as $d)

                                                @if((float) $d['a_repor'] > 0.0005)

                                                    <div class="border rounded p-2 mb-2 small">
                                                        <strong>
                                                            Orçamento #{{ $d['orcamento_id'] }}
                                                        </strong>

                                                        <div>
                                                            Entrega #{{ $d['entrega_id'] }}
                                                        </div>

                                                        <div>
                                                            Quantidade prevista:
                                                            {{ number_format($d['solicitado'], 3, ',', '.') }}
                                                        </div>

                                                        <div>
                                                            Coberta pelo estoque:
                                                            {{ number_format($d['coberto'], 3, ',', '.') }}
                                                        </div>

                                                        <div class="text-danger fw-bold">
                                                            A repor:
                                                            {{ number_format($d['a_repor'], 3, ',', '.') }}
                                                        </div>
                                                    </div>

                                                @endif

                                            @endforeach

                                        </div>
                                    </details>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-check-circle
                                              text-success fs-2 d-block mb-2"></i>

                                    <strong class="text-success">
                                        Nenhuma reposição necessária
                                    </strong>

                                    <div class="text-muted small mt-2">
                                        As promessas analisadas possuem
                                        cobertura suficiente no estoque virtual.
                                        Isso não significa que todas as
                                        entregas foram concluídas.
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

@endsection