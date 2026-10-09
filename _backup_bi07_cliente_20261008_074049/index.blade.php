@extends('layouts.app')

@section('content')

<style>
.bi-fin-card {
    height: 142px; min-height: 142px;
    border-width: 2px;
}
.bi-fin-card .card-body {
    display: flex;
    height: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}
.bi-fin-card .bi { font-size: 1.4rem; }
.bi-fin-label {
    font-size: .80rem;
    font-weight: 700;
    text-transform: uppercase;
}
.bi-fin-value {
    font-size: 1.28rem;
    font-weight: 700;
}
</style>

<div class="container py-4">

    <div class="d-flex justify-content-between mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-currency-dollar me-2"></i>
                Financeiro
            </h2>
            <div class="text-muted">
                Receitas, recebimentos, carteira,
                inadimpl&ecirc;ncia e fluxo financeiro.
            </div>
        </div>
        <div>
            <a href="{{ route('bi.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Central BI
            </a>
        </div>
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
                        <button class="btn btn-primary flex-fill">
                            Aplicar
                        </button>
                        <a href="{{ route('bi.financeiro.index') }}"
                           class="btn btn-outline-secondary">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $moeda = fn($valor) =>
            'R$ ' . number_format((float)$valor,2,',','.');

        $cards = [
            ['Faturamento',$faturamento,'bi-receipt','primary',true],
            ['Vendas finalizadas',$quantidadeVendas,'bi-bag-check','primary',false],
            ['Recebido carteira',$recebidoCarteira,'bi-wallet2','success',true],
            ['Saidas operacionais',$saidas,'bi-arrow-up-circle','danger',true],
            ['Carteira pendente',$carteiraPendente,'bi-credit-card','warning',true],
            ['Carteira vencida',$carteiraVencida,'bi-exclamation-triangle','danger',true],
            ['Carteira a vencer',$carteiraAVencer,'bi-calendar-check','info',true],
            ['Sem vencimento',$carteiraSemVencimento,'bi-calendar-x','warning',true],
            ['Clientes devedores',$clientesDevedores,'bi-people','secondary',false],
            ['Mov. sem tipo',$semClassificacao->quantidade,'bi-shield-exclamation','danger',false],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach($cards as $card)
            <div class="col-lg-4 col-md-6">
                <div class="card bi-fin-card border-{{ $card[3] }} shadow-sm">
                    <div class="card-body">
                        <i class="bi {{ $card[2] }} text-{{ $card[3] }}"></i>
                        <div class="bi-fin-label">{{ $card[0] }}</div>
                        <div class="bi-fin-value">
                            {{ $card[4] ? $moeda($card[1]) : $card[1] }}
                        </div>
                        <a href="#"
                           class="small text-decoration-none text-{{ $card[3] }}"
                           data-bs-toggle="modal"
                           data-bs-target="#biFinModal{{ $loop->index }}">
                            Ver detalhes
                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-info">
        <strong>Crit&eacute;rio cont&aacute;bil:</strong>
        faturamento e recebimentos s&atilde;o indicadores
        distintos. Vendas em carteira n&atilde;o representam
        entrada imediata de dinheiro. Sangrias n&atilde;o
        s&atilde;o tratadas como despesas.
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            <i class="bi bi-graph-up-arrow me-2"></i>
            Fluxo de movimenta&ccedil;&otilde;es classificadas
        </div>
        <div class="card-body">
            <p class="small text-muted">
                Vis&atilde;o parcial dos lan&ccedil;amentos
                classificados: vendas recebidas, quita&ccedil;&otilde;es
                de carteira e sa&iacute;das operacionais.
                N&atilde;o representa concilia&ccedil;&atilde;o
                banc&aacute;ria ou saldo total da empresa.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th class="text-end">Entradas</th>
                            <th class="text-end">Sa&iacute;das</th>
                            <th class="text-end">L&iacute;quido parcial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fluxo as $linha)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($linha->data)->format('d/m/Y') }}</td>
                                <td class="text-end text-success">{{ $moeda($linha->entradas) }}</td>
                                <td class="text-end text-danger">{{ $moeda($linha->saidas) }}</td>
                                <td class="text-end fw-bold">
                                    {{ $moeda($linha->entradas - $linha->saidas) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Nenhuma movimenta&ccedil;&atilde;o classificada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">
                    Recebimentos de carteira por forma
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Forma</th><th>Qtd.</th>
                                <th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse($carteiraPorForma as $item)
                                <tr>
                                    <td>{{ $item->forma }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td class="text-end">{{ $moeda($item->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Sem recebimentos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">
                    Sa&iacute;das por tipo
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Tipo</th><th>Qtd.</th>
                                <th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse($saidasPorTipo as $item)
                                <tr>
                                    <td>{{ $item->tipo }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td class="text-end">{{ $moeda($item->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Sem sa&iacute;das.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            <i class="bi bi-person-lines-fill me-2"></i>
            Carteira - contas pendentes por cliente
        </div>
        <div class="card-body">
            <p class="small text-muted">
                Posi&ccedil;&atilde;o atual da carteira, independente
                do filtro de per&iacute;odo. At&eacute; 100 registros
                agrupados por cliente e vencimento.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Vencimento</th>
                            <th>Situa&ccedil;&atilde;o</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devedores as $item)
                            @php
                                $vencida = $item->data_vencimento
                                    && $item->data_vencimento < now()->toDateString();
                            @endphp
                            <tr>
                                <td>{{ $item->cliente ?? 'N&atilde;o identificado' }}</td>
                                <td>{{ $item->data_vencimento
                                    ? \Carbon\Carbon::parse($item->data_vencimento)->format('d/m/Y')
                                    : 'N&atilde;o informado' }}</td>
                                <td>
                                    {{ !$item->data_vencimento
                                        ? 'Sem vencimento'
                                        : ($vencida ? 'Vencida' : 'A vencer') }}
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $moeda($item->saldo) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Nenhuma carteira pendente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>Auditoria financeira:</strong>
        {{ $semClassificacao->quantidade }}
        lan&ccedil;amentos sem tipo no per&iacute;odo,
        totalizando {{ $moeda($semClassificacao->valor) }}.
        Esses registros est&atilde;o exclu&iacute;dos
        dos indicadores classificados.
        Pend&ecirc;ncias da carteira s&atilde;o
        calculadas pelos pagamentos marcados como pendentes.
    </div>

</div>
{{-- BI07_MODAL_DETALHES_V1 --}}

@php
    $modalTitulos = [
        'Faturamento',
        'Vendas finalizadas',
        'Recebimentos de carteira',
        'Saidas operacionais',
        'Carteira pendente',
        'Carteira vencida',
        'Carteira a vencer',
        'Carteira sem vencimento',
        'Clientes devedores',
        'Movimentacoes sem classificacao'
    ];
@endphp

@foreach($modalTitulos as $indice => $tituloModal)
<div class="modal fade" id="biFinModal{{ $indice }}"
     tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-currency-dollar me-2"></i>
                    {{ $tituloModal }}
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border">
                    <strong>Total do indicador:</strong>
                    {{ $cards[$indice][4]
                        ? $moeda($cards[$indice][1])
                        : $cards[$indice][1] }}
                </div>

                @php
                    if (in_array($indice, [0,1], true)) {
                        $linhasModal = $vendasDetalhes;
                    } elseif ($indice === 2) {
                        $linhasModal = $recebimentosDetalhes;
                    } elseif ($indice === 3) {
                        $linhasModal = $saidasDetalhes;
                    } elseif (in_array($indice, [4,5,6,7,8], true)) {
                        $linhasModal = $pendenciasDetalhes->filter(
                            function($p) use ($indice) {
                                $v = $p->data_vencimento;
                                $hoje = now()->toDateString();

                                if ($indice === 5) {
                                    return $v && $v < $hoje;
                                }
                                if ($indice === 6) {
                                    return $v && $v >= $hoje;
                                }
                                if ($indice === 7) {
                                    return !$v;
                                }
                                return true;
                            }
                        );
                    } else {
                        $linhasModal = $semTipoDetalhes;
                    }
                @endphp

                <div class="small text-muted mb-3">
                    Exibindo ate 200 registros por consulta.
                    Os indicadores mantem os totais completos.
                    Os registros de carteira representam
                    a posicao atual, independentemente
                    do periodo selecionado.
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover
                                  table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Referencia</th>
                                <th>Data</th>
                                <th>Descricao / Cliente</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($linhasModal as $linha)
                            <tr>
                                <td>
                                    @if(isset($linha->venda_id))
                                        Venda #{{ $linha->venda_id }}
                                    @else
                                        #{{ $linha->id ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $dataLinha =
                                            $linha->data_venda
                                            ?? $linha->data_movimentacao
                                            ?? $linha->data_vencimento
                                            ?? null;
                                    @endphp
                                    {{ $dataLinha
                                        ? \Carbon\Carbon::parse($dataLinha)
                                            ->format('d/m/Y H:i')
                                        : '-' }}
                                </td>
                                <td>
                                    {{ $linha->cliente
                                        ?? $linha->observacao
                                        ?? $linha->forma_pagamento
                                        ?? $linha->tipo
                                        ?? '-' }}
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $moeda($linha->total
                                        ?? $linha->valor ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="text-center text-muted py-3">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($indice === 8)
                    <div class="small text-muted">
                        Os registros estao organizados
                        por pagamento pendente.
                        O total de clientes considera
                        clientes distintos.
                    </div>
                @endif
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