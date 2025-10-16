@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pedidos de Compras</h2>
    <a href="{{ route('pedidos_compras.create') }}" class="btn btn-primary mb-3">Novo Pedido</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Fornecedor</th>
                <th>Data</th>
                <th>Status</th>
                <th>Total (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->id }}</td>
                    <td>{{ $pedido->fornecedor->nome }}</td>
                    <td>{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($pedido->status) }}</td>
                    <td>{{ number_format($pedido->total,2,',','.') }}</td>
                    <td>
                        <a href="{{ route('pedidos_compras.show',$pedido->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('pedidos_compras.edit',$pedido->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Nenhum pedido registrado</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
