@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Vendas</h1>
        <a href="{{ route('vendas.create') }}" class="btn btn-primary">Nova Venda</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->cliente->nome ?? '-' }}</td>
                        <td>{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                        <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                        <td>
                            @if($venda->status == 1)
                                <span class="badge bg-success">Concluída</span>
                            @elseif($venda->status == 0)
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @else
                                <span class="badge bg-secondary">Cancelada</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('vendas.edit', $venda->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhuma venda encontrada</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end">
        {{ $vendas->links() }}
    </div>

</div>
@endsection
