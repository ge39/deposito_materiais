@extends('layouts.app')

@section('content')

@php
    $percentual = $auditoria->total_sistema > 0
        ? ($auditoria->diferenca / $auditoria->total_sistema) * 100
        : 0;

    $statusClass = match($auditoria->status) {
        'concluida' => 'bg-success',
        'corrigida' => 'bg-warning text-dark',
        'inconsistente' => 'bg-danger',
        default => 'bg-secondary'
    };
@endphp

<div class="container">


    {{-- ================= BOTÕES PDF E IMPRIMIR ================= --}}
    <div class="mb-3 d-flex justify-content-end gap-2">
        <a href="{{ route('auditoria_caixa.exportar', $auditoria->id) }}" 
           class="btn btn-outline-primary" target="_blank">
           <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>

        <button class="btn btn-outline-secondary" onclick="window.print()">
           <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>

    {{-- ================= HEADER ================= --}}
    <div class="card mb-4 shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Relatório de Auditoria</h4>
            <span class="badge {{ $statusClass }} fs-6 text-uppercase">
                {{ $auditoria->status }}
            </span>
        </div>

        <div class="card-body row">
            <div class="col-md-3">
                <strong>Código:</strong><br>
                {{ $auditoria->codigo_auditoria }}
            </div>

            <div class="col-md-2">
                <strong>Caixa:</strong><br>
                #{{ $auditoria->caixa_id }}
            </div>

            <div class="col-md-3">
                <strong>Auditor:</strong><br>
                {{ $auditoria->usuario->name ?? '-' }}
            </div>

            <div class="col-md-4">
                <strong>Data:</strong><br>
                {{ $auditoria->data_auditoria->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>


    {{-- ================= RESUMO FINANCEIRO ================= --}}
    <div class="row mb-4">

        <div class="col-md-2">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Total Vendas</h6>
                <h4 class="text-primary">
                    @php
                        $totalVendas = $auditoria->caixa->vendas
                            ->flatMap->pagamentos
                            ->where('status', 'confirmado')
                            ->sum('valor');
                    @endphp
                    R$ {{ number_format($totalVendas, 2, ',', '.') }}
                </h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Troco Caixa</h6>
                    <h4 class="text-primary">
                        R$ {{ number_format($auditoria->caixa->fundo_troco,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <h6>Sangrias</h6>
                    <h4 class="text-primary">
                        R$ {{ number_format($total_sangrias,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-dark shadow-sm">
                <div class="card-body">
                    <h6>Total Informado</h6>
                    <h4 class="text-primary">
                        R$ {{ number_format($auditoria->total_fisico,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card text-center shadow-sm {{ $auditoria->diferenca != 0 ? 'border-danger' : 'border-success' }}">
                <div class="card-body">
                    <h6>Quebra em R$</h6>
                    <h4 class="{{ $auditoria->diferenca != 0 ? 'text-danger' : 'text-success' }}">
                        R$ {{ number_format($auditoria->diferenca,2,',','.') }}
                    </h4>
                   
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center shadow-sm {{ $auditoria->diferenca != 0 ? 'border-danger' : 'border-success' }}">
                <div class="card-body">
                    <h6>Quebra em %</h6>
                    
                    <h4 class="{{ $auditoria->diferenca != 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($percentual,2,',','.') }}%
                    </h4>
                </div>
            </div>
        </div>

    </div>


    {{-- ================= GRÁFICO ================= --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            Comparativo Financeiro
        </div>
        <div class="card-body">
            <canvas id="graficoAuditoria" height="100"></canvas>
        </div>
    </div>


    {{-- ================= FORMAS DE PAGAMENTO ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            Detalhamento Auditoria - Total das Entradas Manuais do Caixa {{ $auditoria->caixa->id}}
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Forma</th>
                        <th>Total Sistema</th>
                        <th >Total Informado Operador</th>
                        <th >Ajustes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditoria->detalhes as $detalhe)

                        <tr class="{{ $detalhe->status == 'divergente' ? 'table-danger' : 'table-success' }}">
                            <td class="text-uppercase">
                                {{ $detalhe->forma_pagamento }}
                            </td>
                            <td>
                                R$ {{ number_format($detalhe->total_sistema,2,',','.') }}
                            </td>
                            <td>
                                R$ {{ number_format($detalhe->total_fisico,2,',','.') }}
                            </td>
                            <td class="fw-bold">
                                <!-- R$ {{  number_format( + $detalhe->diferenca,2,',','.') }} -->
                                @php
                                    $valor = $detalhe->diferenca;
                                @endphp

                                @if($valor < 0)
                                    <span class="text-success fw-bold">
                                        + R$ {{ number_format(abs($valor), 2, ',', '.') }}
                                    </span>
                                @elseif($valor > 0)
                                    <span class="text-danger fw-bold">
                                        - R$ {{ number_format(abs($valor), 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted fw-bold">
                                        R$ 0,00
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $status = strtolower($detalhe->status);
                                @endphp

                                @if($status === 'correto')
                                    <span class="badge bg-success">
                                        {{ $detalhe->status }}
                                    </span>
                                @elseif($status === 'divergente')
                                    <span class="badge bg-danger">
                                        {{ $detalhe->status }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        {{ $detalhe->status }}
                                    </span>
                                @endif
                            </td>
                           
                        </tr>
                       
                    @empty
                      <div><tr>Total:  {{ number_format($detalhe->diferenca,2,',','.') }}</tr></div>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nenhum detalhamento encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= LANÇAMENTOS MANUAIS ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-normal">
            Lançamentos Manuais - Retiradas do Caixa
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Observação</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lancamentosManuais as $mov)

                        <tr class="{{ $mov->tipo == 'entrada_manual' ? 'table-success' : 'table-danger' }}">
                            <td class="text-uppercase">
                                {{ $mov->tipo }}
                            </td>
                            <td>
                                R$ {{ number_format($mov->valor,2,',','.') }}
                            </td>
                            <td>
                                {{ $mov->observacao ?? '-' }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i') }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Nenhum lançamento manual registrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= CORREÇÕES DA AUDITORIA ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-light ">
            Correções Realizadas pela Auditoria  - R$ {{ number_format($auditoria->total_sistema,2,',','.') }}
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Forma</th>
                        <th>Valor</th>
                        <th>Auditor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimentacoesAuditoria as $mov)

                        <tr class="{{ $mov->valor >= 0 ? 'table-success' : 'table-danger' }}">
                            <td class="text-uppercase">
                                {{ $mov->forma_pagamento ?? '-' }}
                            </td>

                            <td class="fw-bold">
                                R$ {{ number_format($mov->valor,2,',','.') }}
                            </td>

                            <td>
                                {{ $mov->usuario->name ?? '-' }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i') }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Nenhuma correção foi registrada.
                            </td>
                        </tr>
                    @endforelse
                     <th>Fundo  - Troco Caixa</th>
                    <td class="fw-bold">
                       
                         R$ {{ number_format($auditoria->caixa->fundo_troco,2,',','.') }}
                    </td>
                </tbody>
            </table>
        </div>
    </div>


    {{-- ================= BOTÃO VOLTAR ================= --}}
    <div class="mt-4">
        <a href="{{ route('auditoria_caixa.index') }}" class="btn btn-outline-dark">
            ← Voltar para Relatórios
        </a>
    </div>

</div>


{{-- ================= SCRIPT GRÁFICO ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById('graficoAuditoria');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Sistema', 'Total Físico'],
            datasets: [{
                label: 'Valores (R$)',
                data: [
                    {{ $auditoria->total_sistema }},
                    {{ $auditoria->total_fisico }}
                ],
                backgroundColor: [
                    'rgba(13,110,253,0.6)',
                    'rgba(25,135,84,0.6)'
                ],
                borderColor: [
                    'rgba(13,110,253,1)',
                    'rgba(25,135,84,1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

});
</script>

@endsection