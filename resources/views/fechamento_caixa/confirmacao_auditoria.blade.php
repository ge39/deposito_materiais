@extends('layouts.app')

@section('content')

<div class="container my-5">

    {{-- 🎯 TARJA DINÂMICA DE STATUS DA AUDITORIA FISCAL --}}
    @if(isset($auditoria))
        @if(abs((float)$auditoria->diferenca) <= 0.01)
            <div class="alert alert-success fw-bold text-center fs-4 shadow-sm mb-4 border-2 border-success">
                🎉 CAIXA HOMOLOGADO E CONSISTENTE (Divergência: R$ 0,00)
            </div>
        @else
            <div class="alert alert-warning fw-bold text-center fs-4 shadow-sm mb-4 border-2 border-warning text-dark">
                ⚠️ ATENÇÃO: CONFERÊNCIA FISCAL CONCLUÍDA COM AJUSTES 
                (Diferença residual: R$ {{ number_format($auditoria->diferenca, 2, ',', '.') }})
            </div>
        @endif
    @endif

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold text-success">Conclusão da Auditoria do Caixa #{{ $caixa->id }}</h3>
        <p class="text-muted fs-5">Os lançamentos e conciliações contábeis do turno foram encerrados e homologados pela gerência.</p>
    </div>

    {{-- Card principal --}}
    <div class="card shadow-sm border-success">
        <div class="card-header bg-success text-light fw-bold fs-5">
            Resumo e Balanço de Encerramento do Turno
        </div>

        <div class="card-body fs-5">

            {{-- Mensagem de sucesso dentro do card --}}
            @if(session('auditoria_sucesso'))
                <div class="alert alert-success d-flex align-items-center py-2 px-3 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        {{ session('auditoria_sucesso') }}
                    </div>
                </div>
            @endif

            {{-- Informações do caixa --}}
            <div class="row mb-4 border-bottom border-light-subtle pb-3">
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">ID do Caixa</span>
                    <strong>#{{ $caixa->id }}</strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Operador do Turno</span>
                    <strong>{{ $caixa->usuario->name ?? 'Não identificado' }}</strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Terminal de Vendas</span>
                    <strong>Caixa #{{ $caixa->terminal_id ?? $caixa->id }}</strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Data/Hora de Encerramento</span>
                    <strong>{{ $caixa->data_fechamento ? \Carbon\Carbon::parse($caixa->data_fechamento)->format('d/m/Y H:i') : '-' }}</strong>
                </div>
            </div>

            {{-- Blocos de Valores Monetários Oficiais --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Status Contábil</span>
                    <span class="badge {{ $caixa->status === 'fechado' ? 'bg-success' : 'bg-danger' }} fs-6 px-2 py-1">
                        {{ $caixa->status === 'fechado' ? 'FECHADO / CONSISTENTE' : ucfirst($caixa->status) }}
                    </span>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Fundo de Troco (Inicial)</span>
                    <strong class="text-secondary">R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}</strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Faturamento Esperado (Sistema)</span>
                    <strong class="text-primary">R$ {{ number_format($auditoria->total_sistema ?? $caixa->valor_fechamento, 2, ',', '.') }}</strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Valor de Fechamento (Físico)</span>
                    <strong class="text-success">R$ {{ number_format($caixa->valor_fechamento, 2, ',', '.') }}</strong>
                </div>
            </div>

            {{-- 📊 FITA DE AJUSTES CONTÁBEIS DA AUDITORIA --}}
            @if(isset($movimentacoes) && $movimentacoes->count() > 0)
                <div class="mt-4 pt-3 border-top">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text me-1"></i> Lançamentos Corretivos Efetuados pela Auditoria</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover border align-middle fs-6">
                            <thead class="table-light">
                                <tr>
                                    <th>Forma Corrigida</th>
                                    <th class="text-end">Valor Esperado</th>
                                    <th class="text-end">Valor Homologado</th>
                                    <th class="text-end">Diferença Ajustada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movimentacoes as $mov)
                                    @php
                                        $diffForma = (float)$mov->valor_auditado - (float)$mov->valor;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-secondary">{{ ucfirst(str_replace('_', ' ', $mov->forma_pagamento)) }}</td>
                                        <td class="text-end text-muted">R$ {{ number_format($mov->valor, 2, ',', '.') }}</td>
                                        <td class="text-end text-dark fw-bold">R$ {{ number_format($mov->valor_auditado, 2, ',', '.') }}</td>
                                        <td class="text-end fw-bold {{ $diffForma < 0 ? 'text-danger' : ($diffForma > 0 ? 'text-success' : 'text-muted') }}">
                                            {{ $diffForma > 0 ? '+' : '' }}R$ {{ number_format($diffForma, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Botões de Ação Organizadores do Fluxo --}}
            <div class="mt-4 pt-3 border-top text-end">
                <a href="{{ route('fechamento.lista') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left-short"></i> Voltar ao Painel Geral
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary me-2">
                    Ir para o Dashboard
                </a>
                <a href="{{ route('caixa.abrir') }}" class="btn btn-success px-4 fw-bold shadow-sm">
                    <i class="bi bi-plus-lg"></i> Abrir Novo Caixa
                </a>
            </div>

        </div>
    </div>

</div>

@endsection
