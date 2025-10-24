@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Detalhes do Pedido #{{ $pedido->id }}</h2>

    <div class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-4">
                <strong>Fornecedor:</strong> {{ $pedido->fornecedor->nome ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Data do Pedido:</strong> {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}
            </div>
            <div class="col-md-4">
                <strong>Status:</strong> {{ ucfirst($pedido->status) }}
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4">
                <strong>Criado por:</strong> {{ $pedido->user->name ?? '-' }}
            </div>
            <div class="col-md-8">
                <strong>Total do Pedido:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}
            </div>
        </div>
        @if(!empty($pedido->observacoes))
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>Observações:</strong> {{ $pedido->observacoes }}
            </div>
        </div>
        @endif
    </div>

    <div class="card p-3">
        <h4>Itens do Pedido</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Unidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Total (R$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedido->itens as $item)
                    <tr>
                        <td>{{ $item->produto->nome ?? '-' }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->produto->unidade->nome ?? '-' }}</td>
                        <td>{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Nenhum item cadastrado neste pedido.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-warning">Editar Pedido</a>
    </div>
</div>
@endsection
