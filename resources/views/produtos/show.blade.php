@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes do Produto</h2>

    <div class="card p-3">
        <p><strong>ID:</strong> {{ $produto->id }}</p>
        <p><strong>Descrição:</strong> {{ $produto->descricao }}</p>
        <p><strong>Categoria:</strong> {{ $produto->categoria->nome ?? '-' }}</p>
        <p><strong>Fornecedor:</strong> {{ $produto->fornecedor->nome ?? '-' }}</p>
        <p><strong>Preço:</strong> R$ {{ number_format($produto->preco, 2, ',', '.') }}</p>
        <p><strong>Estoque:</strong> {{ $produto->estoque }}</p>
        <p><strong>Validade:</strong> {{ $produto->validade }}</p>
        <p><strong>Código de Barras:</strong> {{ $produto->codigo_barras }}</p>
        <p><strong>Observações:</strong> {{ $produto->observacoes }}</p>
    </div>

    <div class="mt-3">
        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>
@endsection
