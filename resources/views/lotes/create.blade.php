@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Adicionar Lote ao Produto: {{ $produto->nome }}</h2>

    {{-- Campos de referência do produto --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <strong>Marca:</strong>
            <input type="text" class="form-control" value="{{ $produto->marca->nome ?? '' }}" readonly>
        </div>
        <div class="col-md-3">
            <strong>Fornecedor:</strong>
            <input type="text" class="form-control" value="{{ $produto->fornecedor->nome ?? '' }}" readonly>
        </div>
        <div class="col-md-3">
            <strong>Unidade:</strong>
            <input type="text" class="form-control" value="{{ $produto->unidadeMedida->nome ?? '' }}" readonly>
        </div>
        <div class="col-md-3">
            <strong>SKU:</strong>
            <input type="text" class="form-control" value="{{ $produto->sku }}" readonly>
        </div>
    </div>

    {{-- Formulário do lote --}}
    <form action="{{ route('lotes.store', $produto->id) }}" method="POST">
        @csrf

        <!-- {{-- Inputs hidden para relacionamentos --}} -->
            <input type="hidden" name="fornecedor_id" value="{{ $produto->fornecedor_id }}">

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="preco_compra" class="form-label">Preço de Compra</label>
                <input type="number" step="0.01" name="preco_compra" id="preco_compra" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="data_compra" class="form-label">Data da Compra</label>
                <input type="date" name="data_compra" id="data_compra" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="validade" class="form-label">Validade (opcional)</label>
                <input type="date" name="validade" id="validade" class="form-control">
                <small class="text-muted">Se não informar, será adicionada automaticamente +3 meses.</small>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('lotes.index', $produto->id) }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
