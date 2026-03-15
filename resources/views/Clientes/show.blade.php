@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Cliente: {{ $cliente->nome }}</h2>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">&laquo; Voltar</a>
    </div>

    {{-- Cards resumo --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">💰 Limite de Crédito</h5>
                    <p class="display-6">R$ {{ number_format($cliente->limite_credito,2,',','.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php
                $saldoPerc = $cliente->limite_credito > 0 ? ($saldo / $cliente->limite_credito) * 100 : 0;
                $saldoClass = $saldoPerc >= 80 ? 'bg-danger text-white animate__animated animate__pulse' : '';
            @endphp
            <div class="card shadow-sm h-100 {{ $saldoClass }}">
                <div class="card-body">
                    <h5 class="card-title {{ $saldoPerc >= 80 ? 'text-white' : 'text-danger' }}">
                        {{ $saldoPerc >= 80 ? '⚠️ Saldo Crítico' : '🔴 Saldo Atual' }}
                    </h5>
                    <p class="display-6 {{ $saldoPerc >= 80 ? 'text-white' : ($saldo > 0 ? 'text-danger' : 'text-success') }}">
                        R$ {{ number_format($saldo,2,',','.') }}
                    </p>
                    @if($saldoPerc >= 80)
                        <small>Atenção: saldo próximo do limite!</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">🟢 Disponível</h5>
                    <p class="display-6">
                        R$ {{ number_format(max($cliente->limite_credito - $saldo, 0),2,',','.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Abas --}}
    <ul class="nav nav-tabs mb-3" id="clienteTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="dados-tab" data-bs-toggle="tab" href="#dados" role="tab">Dados do Cliente</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="conta-tab" data-bs-toggle="tab" href="#conta" role="tab">Conta Corrente</a>
        </li>
    </ul>

    <div class="tab-content" id="clienteTabsContent">

        {{-- Aba Dados --}}
        <div class="tab-pane fade show active" id="dados" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">Informações Básicas</div>
                        <div class="card-body">
                            <p><strong>Nome:</strong> {{ $cliente->nome }}</p>
                            <p><strong>Tipo:</strong> <span class="badge bg-info">👤 {{ ucfirst($cliente->tipo) }}</span></p>
                            <p><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
                            <p><strong>Telefone:</strong> {{ $cliente->telefone }}</p>
                            <p><strong>Email:</strong> {{ $cliente->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">Crédito e Observações</div>
                        <div class="card-body">
                            <p><strong>Limite de Crédito:</strong> 💰 R$ {{ number_format($cliente->limite_credito,2,',','.') }}</p>
                            <p><strong>Saldo Atual:</strong> 
                                <span class="{{ $saldo > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                    {{ $saldoPerc >= 80 ? '⚠️ ' : '' }}R$ {{ number_format($saldo,2,',','.') }}
                                </span>
                            </p>
                            <p><strong>Disponível:</strong> 🟢 R$ {{ number_format(max($cliente->limite_credito - $saldo,0),2,',','.') }}</p>
                            <p><strong>Observações:</strong> {{ $cliente->observacoes ?? '—' }}</p>
                            <p><strong>Criado em:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aba Conta Corrente --}}
        <div class="tab-pane fade" id="conta" role="tabpanel">
            <div class="row g-3 mb-3">

                {{-- Gráfico --}}
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">Evolução do Saldo</div>
                        <div class="card-body">
                            <canvas id="saldoChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Tabela Extrato --}}
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body bg-light">
                            <h5 class="card-title">Extrato da Conta Corrente</h5>
                            @if($movimentacoes->count())
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Origem</th>
                                            <th>Valor</th>
                                            <th>Saldo Após</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movimentacoes as $mov)
                                            <tr class="{{ $mov->tipo === 'debito' ? 'table-danger' : 'table-success' }}">
                                                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge {{ $mov->tipo === 'debito' ? 'bg-danger' : 'bg-success' }}">
                                                        {{ $mov->tipo === 'debito' ? '🔴 Débito' : '🟢 Crédito' }}
                                                    </span>
                                                </td>
                                                <td>{{ ucfirst($mov->origem) }}</td>
                                                <td>R$ {{ number_format($mov->valor,2,',','.') }}</td>
                                                <td>R$ {{ number_format($mov->saldo_apos,2,',','.') }}</td>
                                                <td>{{ $mov->descricao }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $movimentacoes->links() }}
                            @else
                                <p class="text-muted">Nenhuma movimentação encontrada.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('saldoChart').getContext('2d');

    const saldoData = {
        labels: [
            @foreach($movimentacoes as $mov)
                '{{ $mov->created_at->format("d/m H:i") }}',
            @endforeach
        ],
        datasets: [{
            label: 'Saldo',
            data: [
                @foreach($movimentacoes as $mov)
                    {{ $mov->saldo_apos }},
                @endforeach
            ],
            fill: true,
            borderColor: 'rgba(40, 167, 69, 1)',
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            tension: 0.3,
            pointRadius: 4,
            pointBackgroundColor: 'rgba(220,53,69,1)'
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: saldoData,
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { display: true, title: { display: true, text: 'Data' } },
                y: { display: true, title: { display: true, text: 'Saldo (R$)' } }
            }
        }
    });
</script>
@endsection