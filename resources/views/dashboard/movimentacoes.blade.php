@extends('layouts.app')

@section('content')
<div class="container">

<h2 class="mb-4">📊 Dashboard de Movimentações</h2>

{{-- FILTROS --}}
<form method="GET" class="row mb-4">
    <div class="col-md-3">
        <input type="date" name="inicio" value="{{ $inicio }}" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" name="fim" value="{{ $fim }}" class="form-control">
    </div>

    <div class="col-md-3">
        <select name="tipo" class="form-control">
            <option value="">Todos</option>
            <option value="reserva" @selected($tipo=='reserva')>Reserva</option>
            <option value="cancelamento" @selected($tipo=='cancelamento')>Cancelamento</option>
            <option value="edicao" @selected($tipo=='edicao')>Edição</option>
        </select>
    </div>

    <div class="col-md-3">
        <button class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>

{{-- KPIs --}}
<div class="row mb-4">

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Total</small>
            <h3>{{ $total }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Reservas</small>
            <h3>{{ $reservas }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Cancelamentos</small>
            <h3>{{ $cancelamentos }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Taxa Cancelamento</small>
            <h3>{{ number_format($taxaCancelamento, 1) }}%</h3>
        </div>
    </div>

</div>

{{-- ALERTA INTELIGENTE --}}
@if($taxaCancelamento > 30)
    <div class="alert alert-danger">
        ⚠️ Alta taxa de cancelamento — verifique estoque ou preços
    </div>
@endif

{{-- GRÁFICO --}}
<div class="card mb-4 p-3 shadow-sm">
    <canvas id="grafico"></canvas>
</div>

{{-- TOP USUÁRIOS --}}
<div class="card mb-4 p-3 shadow-sm">
    <h5>👤 Top usuários</h5>

    @foreach($topUsuarios as $u)
        <div class="d-flex justify-content-between">
            <span>{{ $u->user->name ?? 'Sistema' }}</span>
            <strong>{{ $u->total }}</strong>
        </div>
    @endforeach
</div>

{{-- TABELA --}}
<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Antes</th>
                    <th>Depois</th>
                    <th>Usuário</th>
                    <th>Data</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ultimas as $mov)
                    <tr>
                        <td>{{ $mov->id }}</td>

                        <td>
                            <span class="badge
                                @if($mov->tipo=='reserva') bg-primary
                                @elseif($mov->tipo=='cancelamento') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ $mov->tipo }}
                            </span>
                        </td>

                        <td>{{ $mov->quantidade_antes }}</td>
                        <td>{{ $mov->quantidade_depois }}</td>
                        <td>{{ $mov->user->name ?? '-' }}</td>
                        <td>{{ $mov->created_at->format('d/m H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('grafico'), {
    type: 'line',
    data: {
        labels: {!! json_encode($porDia->pluck('data')) !!},
        datasets: [{
            label: 'Movimentações',
            data: {!! json_encode($porDia->pluck('total')) !!},
            tension: 0.4
        }]
    }
});
</script>

@endsection