@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes da Venda</h2>

    <div class="card p-3 mb-3">
        <p><strong>ID:</strong> {{ $venda->id }}</p>
        <p><strong>Cliente:</strong> {{ $venda->cliente->nome ?? '-' }}</p>
        <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($venda->data)->format('d/m/Y') }}</p>
        <p><strong>Observações:</strong> {{ $venda->observacoes ?: '-' }}</p>
        <p><strong>Total:</strong> R$ {{ number_format($venda->total, 2, ',', '.') }}</p>
    </div>

    <h5>Produtos</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário (R$)</th>
                <th>Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venda->itens as $item)
                <tr>
                    <td>{{ $item->produto->descricao ?? '-' }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('vendas.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
