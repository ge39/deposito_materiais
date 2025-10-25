@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Lotes do Produto: {{ $produto->nome }}</h2>

    <!-- <a href="{{ route('lotes.create', $produto->id) }}" class="btn btn-success mb-3">
        <i class="bi bi-plus-circle"></i> Novo Lote
    </a> -->

    @if($produto->lotes->isEmpty())
        <div class="alert alert-warning text-center py-3">
            Nenhum lote cadastrado para este produto.
        </div>
    @else
        <div class="row">
            @foreach ($produto->lotes as $lote)
                <div class="col-md-4 mb-3">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-success fw-bold">
                                <i class="bi bi-box-seam"></i> Lote #{{ $lote->numero_lote }}
                            </h5>
                            <p class="card-text mb-1"><strong>Codigo:</strong> 000{{ $lote->produto_id }}</p>
                            <p class="card-text mb-1"><strong>Quantidade:</strong> {{ $lote->quantidade }}</p>
                            <p class="card-text mb-1"><strong>Preço de Compra:</strong> R$ {{ number_format($lote->preco_compra, 2, ',', '.') }}</p>
                            <p class="card-text mb-1"><strong>Data da Compra:</strong> {{ \Carbon\Carbon::parse($lote->data_compra)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Validade:</strong> {{ \Carbon\Carbon::parse($lote->validade)->format('d/m/Y') }}</p>
                            <p class="card-text text-muted small"><strong>Cadastrado em:</strong> {{ $lote->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@endsection
