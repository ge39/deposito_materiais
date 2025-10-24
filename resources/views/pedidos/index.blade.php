@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Pedidos de Compras</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Novo Pedido</a>
    </div>

    <div class="card p-3">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fornecedor</th>
                    <th>Data do Pedido</th>
                    <th>Total (R$)</th>
                    <th>Status</th>
                    <th>Criado por</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->id }}</td>
                        <td>{{ $pedido->fornecedor->nome ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</td>
                        <td>{{ number_format($pedido->total, 2, ',', '.') }}</td>
                        <td>{{ ucfirst($pedido->status) }}</td>
                        <td>{{ $pedido->user->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-info btn-sm">Visualizar</a>
                            <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum pedido encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
