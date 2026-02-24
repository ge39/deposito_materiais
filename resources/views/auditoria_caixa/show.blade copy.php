@extends('layouts.app')

@section('content')
<div class="container">

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                Auditoria de Caixa - {{ $auditoria->codigo_auditoria }}
            </h5>
        </div>

        <div class="card-body">

            {{-- IDENTIFICAÇÃO --}}
            <h6 class="border-bottom pb-2 mb-3">Identificação</h6>

            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>Caixa:</strong>
                    {{ $auditoria->caixa->nome ?? '-' }}
                </div>

                <div class="col-md-4">
                    <strong>Operador do Caixa:</strong>
                    {{ $auditoria->abertura->operador->name ?? 'Não identificado' }}
                </div>

                <div class="col-md-4">
                    <strong>Auditor:</strong>
                    {{ $auditoria->auditor->name }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Data da Auditoria:</strong>
                    {{ $auditoria->data_auditoria->format('d/m/Y H:i') }}
                </div>

                <div class="col-md-4">
                    <strong>Status:</strong>
                    <span class="badge bg-{{ 
                        $auditoria->status == 'concluida' ? 'success' :
                        ($auditoria->status == 'inconsistente' ? 'danger' :
                        ($auditoria->status == 'corrigida' ? 'warning' : 'secondary'))
                    }}">
                        {{ ucfirst($auditoria->status) }}
                    </span>
                </div>
            </div>

            {{-- RESUMO FINANCEIRO --}}
            <h6 class="border-bottom pb-2 mb-3">Resumo Financeiro</h6>

            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Total Sistema:</strong>
                    R$ {{ number_format($auditoria->total_sistema, 2, ',', '.') }}
                </div>

                <div class="col-md-4">
                    <strong>Total Físico:</strong>
                    R$ {{ number_format($auditoria->total_fisico, 2, ',', '.') }}
                </div>

                <div class="col-md-4">
                    <strong>Diferença:</strong>
                    <span class="{{ $auditoria->diferenca != 0 ? 'text-danger' : 'text-success' }}">
                        R$ {{ number_format($auditoria->diferenca, 2, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- DETALHAMENTO --}}
            <h6 class="border-bottom pb-2 mb-3">Detalhamento por Forma de Pagamento</h6>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Forma de Pagamento</th>
                            <th class="text-end">Sistema</th>
                            <th class="text-end">Físico</th>
                            <th class="text-end">Diferença</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditoria->detalhes as $detalhe)
                            <tr>
                                <td>{{ ucfirst($detalhe->forma_pagamento) }}</td>
                                <td class="text-end">
                                    R$ {{ number_format($detalhe->total_sistema, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    R$ {{ number_format($detalhe->total_fisico, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    R$ {{ number_format($detalhe->diferenca, 2, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $detalhe->status == 'correto' ? 'success' : 'danger' }}">
                                        {{ ucfirst($detalhe->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- CORREÇÃO --}}
            @if($auditoria->status === 'corrigida')
                @php
                    $correcao = $auditoria->movimentacoesAuditoria->last();
                @endphp

                <h6 class="border-bottom pb-2 mb-3">Correção de Divergência</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Corrigido por:</strong>
                        {{ $correcao->operador->name ?? '-' }}
                    </div>

                    <div class="col-md-6">
                        <strong>Data da Correção:</strong>
                        {{ \Carbon\Carbon::parse($correcao->data_movimentacao)->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection