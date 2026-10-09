@extends('layouts.app')

@section('content')

<style>
.bi-fin-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}
.bi-fin-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}
.bi-fin-icon {
    font-size: 1.30rem;
    line-height: 1;
}
.bi-fin-label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
}
.bi-fin-value {
    font-size: 1.28rem;
    font-weight: 700;
}
.bi-fin-detail {
    font-size: .78rem;
    text-decoration: none;
}
.modal-bi .modal-dialog {
    max-width: 1200px;
}
.modal-bi .modal-body {
    max-height: 72vh;
    overflow-y: auto;
}
</style>

{{-- BI07_SCORE_FINANCEIRO_V1 --}}
@php
    $scoresFinanceiros = app(
        \App\Services\BI\Bi07ScoreService::class
    )->calcular();
@endphp
@php
    $moeda = fn($v) => 'R$ '.number_format((float)$v, 2, ',', '.');
    $percent = fn($v) => number_format((float)$v, 2, ',', '.').'%';

    $data = function($v) {
        return $v
            ? \Carbon\Carbon::parse($v)->format('d/m/Y')
            : 'Sem registro';
    };

    $atraso = function($linha) {
        $dias = (int)$linha->dias_atraso;

        if ($dias <= 0) {
            return 'Sem atraso identificado';
        }

        $meses = intdiv($dias, 30);
        $resto = $dias % 30;

        return $meses > 0
            ? $meses.' mes(es) e '.$resto.' dia(s)'
            : $dias.' dia(s)';
    };
@endphp

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-currency-dollar me-2"></i>
                Financeiro
            </h2>
            <div class="text-muted">
                Indicadores financeiros consolidados por cliente.
            </div>
        </div>
        <a href="{{ route('bi.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('bi.financeiro.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Data inicial</label>
                        <input type="date" name="data_inicio"
                               class="form-control"
                               value="{{ $inicio->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data final</label>
                        <input type="date" name="data_fim"
                               class="form-control"
                               value="{{ $fim->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1"
                                type="submit">Aplicar</button>
                        <a href="{{ route('bi.financeiro.index') }}"
                           class="btn btn-outline-secondary">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">

        @foreach($cards as $card)
        <div class="col-lg-4 col-md-6">
            <div class="card bi-fin-card border-{{ $card[4] }} shadow-sm">
                <div class="card-body">
                    <i class="bi {{ $card[3] }} bi-fin-icon text-{{ $card[4] }}"></i>

                    <div class="bi-fin-label">
                        {{ $card[0] }}
                    </div>

                    <div class="bi-fin-value">
                        {{ $card[2]
                            ? $moeda($card[1])
                            : number_format($card[1],0,',','.') }}
                    </div>

                    <a href="#"
                       class="bi-fin-detail text-{{ $card[4] }}"
                       data-bs-toggle="modal"
                       data-bs-target="#modalBI07{{ $loop->index }}">
                        Ver detalhes
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>

{{-- MODAIS GERENCIAIS AGRUPADOS POR CLIENTE --}}

@foreach($cards as $card)

@php
    $registros = $dadosModais[$card[5]];
    $totalGeral = $registros->sum('total');
    $operacoes = $registros->sum('operacoes');
    $semVinculo = in_array(
        $card[5], ['recebimentos','saidas','sem_tipo'], true
    );
@endphp

<div class="modal fade modal-bi"
     id="modalBI07{{ $loop->index }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi {{ $card[3] }} text-{{ $card[4] }} me-2"></i>
                    {{ $card[0] }}
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <div class="text-muted">Total geral</div>
                                <h4 class="fw-bold mb-0">
                                    {{ $moeda($totalGeral) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-secondary h-100">
                            <div class="card-body text-center">
                                <div class="text-muted">Operacoes</div>
                                <h4 class="fw-bold mb-0">
                                    {{ number_format($operacoes,0,',','.') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                @if($semVinculo)
                    <div class="alert alert-warning small">
                        Estas movimentacoes nao possuem um
                        relacionamento com cliente comprovado
                        na estrutura auditada. Seus valores
                        aparecem consolidados como nao vinculados,
                        sem atribuir um cliente incorreto.
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm table-hover
                                  table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th class="text-center">Operacoes</th>
                                <th class="text-end">Total (R$)</th>
                                <th>Ultima compra</th>
                                <th>Ultimo pagamento</th>
                                <th>Tempo em atraso</th>
                                <th>Score (0 a 10)</th>
                                <th class="text-end">Participacao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $linha)
                                @php
                                    $participacao = $totalGeral > 0
                                        ? $linha->total / $totalGeral * 100
                                        : 0;
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $linha->cliente }}
                                    </td>
                                    <td class="text-center">
                                        {{ $linha->operacoes }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ $moeda($linha->total) }}
                                    </td>
                                    <td>
                                        {{ $data($linha->ultima_compra) }}
                                    </td>
                                    <td>
                                        {{ $data($linha->ultimo_pagamento) }}
                                    </td>
                                    <td>
                                        {{ $linha->cliente_id === null
                                            ? 'Nao identificado'
                                            : $atraso($linha) }}
                                    </td>
                                    <td>
                                        @php
                                            $scoreCliente = $linha->cliente_id !== null
                                                ? ($scoresFinanceiros[$linha->cliente_id] ?? null)
                                                : null;
                                        @endphp

                                        @if($scoreCliente && $scoreCliente['score'] !== null)
                                            <span class="badge bg-{{ $scoreCliente['classe'] }}"
                                                  title="{{ $scoreCliente['situacao'] }}">
                                                {{ number_format($scoreCliente['score'],1,',','.') }}/10
                                            </span>
                                            <small class="d-block text-muted">
                                                {{ $scoreCliente['situacao'] }}
                                            </small>
                                        @else
                                            <span class="text-muted">
                                                Nao avaliado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ $percent($participacao) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="text-center text-muted">
                                        Nenhum dado encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>TOTAL GERAL</td>
                                <td class="text-center">
                                    {{ $operacoes }}
                                </td>
                                <td class="text-end">
                                    {{ $moeda($totalGeral) }}
                                </td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end">
                                    {{ $totalGeral > 0 ? '100,00%' : '0,00%' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="small text-muted mt-3 mb-0">
                    Ultima compra e ultimo pagamento consideram
                    o historico disponivel do cliente.
                    O atraso considera o vencimento pendente
                    mais antigo. A posicao da carteira e atual,
                    independente do filtro de datas.
                </p>

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

@endforeach

@endsection