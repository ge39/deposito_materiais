@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ocorrência Pós-Venda #{{ $posVenda->id }}</h2>

    <div class="mb-3">
        <strong>Venda:</strong> {{ $posVenda->venda_id }}
    </div>
    <div class="mb-3">
        <strong>Tipo:</strong> {{ ucfirst($posVenda->tipo) }}
    </div>
    <div class="mb-3">
        <strong>Valor Devolução:</strong> {{ number_format($posVenda->valor_devolucao, 2, ',', '.') }}
    </div>
    <div class="mb-3">
        <strong>Status:</strong> {{ ucfirst($posVenda->status) }}
    </div>
    <div class="mb-3">
        <strong>Descrição:</strong>
        <p>{{ $posVenda->descricao ?: '-' }}</p>
    </div>

    <h4>Itens afetados</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Unitário</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posVenda->itens as $item)
                <tr>
                    <td>{{ $item->produto->nome }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Nenhum item registrado</td></tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('pos_vendas.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
