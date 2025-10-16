@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Lista de Vendas</h2>
    <a href="{{ route('vendas.create') }}" class="btn btn-primary mb-3">Nova Venda</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Total (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendas as $venda)
                <tr>
                    <td>{{ $venda->id }}</td>
                    <td>{{ $venda->cliente->nome ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda->data)->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('vendas.edit', $venda->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Excluir venda?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Nenhuma venda cadastrada</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
