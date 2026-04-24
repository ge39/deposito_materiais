@extends('layouts.app')

@section('content')
<div class="container-fluid mt-3">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Dashboard de Movimentações</h3>
    </div>

    {{-- ===================== FILTROS ===================== --}}
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">

            <div class="col-md-3">
                <label>Data início</label>
                <input type="date" name="inicio" class="form-control"
                    value="{{ request('inicio', $inicio) }}">
            </div>

            <div class="col-md-3">
                <label>Data fim</label>
                <input type="date" name="fim" class="form-control"
                    value="{{ request('fim', $fim) }}">
            </div>

            <div class="col-md-3">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="aprovado" @selected(request('tipo')=='aprovado')>Aprovado</option>
                    <option value="cancelamento" @selected(request('tipo')=='cancelamento')>Cancelamento</option>
                    <option value="aguardando_estoque" @selected(request('tipo')=='aguardando_estoque')>Aguardando Estoque</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100">Filtrar</button>
                <a href="{{ route('dashboard.movimentacoes') }}" class="btn btn-secondary w-100">Limpar</a>
            </div>

        </div>
    </form>

    {{-- ===================== KPIs ===================== --}}
    <div class="row g-3 mb-3">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total</h6>
                    <h3>{{ $total }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Aprovados</h6>
                    <h3 class="text-success">{{ $aprovados }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Cancelamentos</h6>
                    <h3 class="text-danger">{{ $cancelamentos }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Taxa Cancelamento</h6>
                    <h3 class="text-warning">{{ $taxaCancelamento }}%</h3>
                </div>
            </div>
        </div>

    </div>

    {{-- ===================== GRÁFICOS ===================== --}}
    <div class="row mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Movimentações por Tipo</div>
                <div class="card-body">
                    <canvas id="chartTipo"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Movimentações por Dia</div>
                <div class="card-body">
                    <canvas id="chartDia"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ===================== TOP USUÁRIOS ===================== --}}
    <div class="card mb-4">
        <div class="card-header">Top Usuários</div>
        <div class="card-body table-responsive">

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($topUsuarios as $item)
                        <tr>
                            <td>{{ $item['user']->name ?? 'N/A' }}</td>
                            <td>{{ $item['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>

    {{-- ===================== TABELA ===================== --}}
    <div class="card">
        <div class="card-header">Últimas Movimentações</div>

        <div class="card-body table-responsive">

            <table class="table table-hover table-sm align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Antes</th>
                        <th>Depois</th>
                        <th>Usuário</th>
                        <th>Data</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ultimas as $mov)

                        @php
                            $produto = $mov->item->produto ?? null;
                        @endphp

                        <tr>
                            <td>{{ $mov->id }}</td>

                            <td>
                                {{ $produto->nome ?? $mov->descricao ?? 'Sem produto' }}
                            </td>

                            <td>
                                @if($mov->tipo == 'aprovado')
                                    <span class="badge bg-success">Aprovado</span>
                                @elseif($mov->tipo == 'cancelamento')
                                    <span class="badge bg-danger">Cancelamento</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $mov->tipo }}</span>
                                @endif
                            </td>

                            <td>{{ $mov->quantidade_antes }}</td>
                            <td>{{ $mov->quantidade_depois }}</td>

                            <td>{{ $mov->user->name ?? '-' }}</td>

                            <td>{{ $mov->created_at->format('d/m H:i') }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma movimentação encontrada</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

{{-- ===================== CHART JS ===================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const porTipo = @json($porTipo);
    const porDia = @json($porDia);

    new Chart(document.getElementById('chartTipo'), {
        type: 'pie',
        data: {
            labels: Object.keys(porTipo),
            datasets: [{
                data: Object.values(porTipo)
            }]
        }
    });

    new Chart(document.getElementById('chartDia'), {
        type: 'line',
        data: {
            labels: porDia.map(i => i.data),
            datasets: [{
                label: 'Movimentações',
                data: porDia.map(i => i.total),
                fill: true
            }]
        }
    });
</script>

@endsection