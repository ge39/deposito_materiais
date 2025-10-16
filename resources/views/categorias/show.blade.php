@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes da Categoria</h2>

    <div class="card p-3">
        <p><strong>ID:</strong> {{ $categoria->id }}</p>
        <p><strong>Nome:</strong> {{ $categoria->nome }}</p>
        <p><strong>Descrição:</strong> {{ $categoria->descricao }}</p>
        <p><strong>Criado em:</strong> {{ $categoria->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="mt-3">
        <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>
@endsection
