@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Resultados do Rastreio</h2>

    @if($itens->isEmpty())
        <div class="card p-3">
            <p>Nenhuma venda encontrada com os filtros selecionados.</p>
            <a href="{{ route('rastreio.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    @else
        @php
            $itensPorVenda = $itens->groupBy('venda_id');
        @endphp

        @foreach($itensPorVenda as $vendaId => $itensVenda)
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5>Venda #{{ $vendaId }} - Cliente: {{ $itensVenda->first()->venda->cliente->nome }}</h5>
                    <p>Data: {{ \Carbon\Carbon::parse($itensVenda->first()->created_at)->format('d/m/Y H:i:s') }}</p>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-3">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Lote</th>
                                <th>Qtde</th>
                                <th>UN.</th>
                                <th>Desc.</th>
                                <th>Total</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itensVenda as $item)
                                <tr>
                                    <td>{{ $item->produto->nome }}</td>
                                    <td>{{ $item->lote_id ?? '-' }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td>R$ {{ $item->preco_unitario }}</td>
                                    <td>R$ {{ $item->desconto }}</td>
                                    <td>R$ {{ $item->subtotal }}</td>
                                    <td>
                                        <form action="{{ route('rastreio.devolucao', $item->id) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="motivo" placeholder="Motivo da devolução" class="form-control" required>
                                            <button type="submit" class="btn btn-danger btn-sm">Registrar Devolução</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <a href="{{ route('rastreio.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
