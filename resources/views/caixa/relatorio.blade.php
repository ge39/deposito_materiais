@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- CABEÇALHO --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Relatório de Caixa
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Caixa:</strong> #{{ $caixa->id }}</div>
                <div class="col-md-3"><strong>Terminal:</strong> {{ $caixa->terminal_id }}</div>
                <div class="col-md-3"><strong>Abertura:</strong> {{ $caixa->data_abertura }}</div>
                <div class="col-md-3"><strong>Fechamento:</strong> {{ $caixa->data_fechamento }}</div>
            </div>
        </div>
    </div>

    {{-- RESUMO FINANCEIRO --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Resumo Financeiro
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <td>Total Vendas</td>
                    <td class="text-end">R$ {{ number_format($totais_por_tipo['venda'] ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Entradas Manuais</td>
                    <td class="text-end">R$ {{ number_format($totais_por_tipo['entrada_manual'] ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Saídas Manuais</td>
                    <td class="text-end">R$ {{ number_format($totais_por_tipo['saida_manual'] ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr class="table-secondary fw-bold">
                    <td>Saldo Final</td>
                    <td class="text-end">R$ {{ number_format($saldo_sistema, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- PAGAMENTOS POR FORMA --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">
            Pagamentos por Forma
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Forma</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagamentos_por_forma as $pagamento)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $pagamento->forma_pagamento)) }}</td>
                            <td class="text-end">
                                R$ {{ number_format($pagamento->total, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MOVIMENTAÇÕES --}}
    <div class="card">
        <div class="card-header fw-bold">
            Movimentações do Caixa
        </div>
        <div class="card-body">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Observação</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimentacoes as $mov)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i') }}</td>
                            <td>{{ ucfirst(str_replace('_',' ', $mov->tipo)) }}</td>
                            <td>{{ $mov->observacao }}</td>
                            <td class="text-end">
                                R$ {{ number_format($mov->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
