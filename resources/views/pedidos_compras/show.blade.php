@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes do Pedido #{{ $pedido->id }}</h2>

    <div class="mb-3">
        <p><strong>Fornecedor:</strong> {{ $pedido->fornecedor->nome }}</p>
        <p><strong>Data do Pedido:</strong> {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($pedido->status) }}</p>
        <p><strong>Observações:</strong> {{ $pedido->observacoes ?: '-' }}</p>
        <p><strong>Total do Pedido (R$):</strong> {{ number_format($pedido->total,2,',','.') }}</p>
    </div>

    <h5>Itens do Pedido</h5>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário (R$)</th>
                <th>Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->itens as $item)
                <tr>
                    <td>{{ $item->produto->descricao }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ number_format($item->preco_unitario,2,',','.') }}</td>
                    <td>{{ number_format($item->total,2,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('pedidos_compras.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
