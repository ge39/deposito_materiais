@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pós-Vendas</h2>
    <a href="{{ route('pos_vendas.create', ['venda_id' => 0]) }}" class="btn btn-primary mb-3">Nova Ocorrência</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Venda</th>
                <th>Tipo</th>
                <th>Valor Devolução</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posVendas as $pos)
                <tr>
                    <td>{{ $pos->id }}</td>
                    <td>{{ $pos->venda_id }}</td>
                    <td>{{ ucfirst($pos->tipo) }}</td>
                    <td>{{ number_format($pos->valor_devolucao, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($pos->status) }}</td>
                    <td>{{ $pos->data_registro }}</td>
                    <td>
                        <a href="{{ route('pos_vendas.show', $pos->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('pos_vendas.edit', $pos->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('pos_vendas.destroy', $pos->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esta ocorrência?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Nenhuma ocorrência registrada</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
