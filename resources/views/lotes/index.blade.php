@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Lotes do Produto: {{ $produto->nome }}</h2>

    <a href="{{ route('lotes.create', $produto->id) }}" class="btn btn-primary mb-3">Adicionar Novo Lote</a>

    @if($produto->lotes->isEmpty())
        <div class="alert alert-warning">Nenhum lote cadastrado para este produto.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Quantidade</th>
                    <th>Preço de Compra</th>
                    <th>Data da Compra</th>
                    <th>Validade</th>
                    <th>Cadastrado em</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produto->lotes as $lote)
                    <tr>
                        <td>{{ $lote->id }}</td>
                        <td>{{ $lote->quantidade }}</td>
                        <td>R$ {{ number_format($lote->preco_compra, 2, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($lote->data_compra)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($lote->validade)->format('d/m/Y') }}</td>
                        <td>{{ $lote->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <div class="row mb-3">
        <div class="col-md-12 d-flex align-items-center gap-3">
            <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</div>
@endsection
