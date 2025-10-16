@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes do Fornecedor</h2>

    <div class="card p-3">
        <p><strong>ID:</strong> {{ $fornecedor->id }}</p>
        <p><strong>Nome:</strong> {{ $fornecedor->nome }}</p>
        <p><strong>Telefone:</strong> {{ $fornecedor->telefone }}</p>
        <p><strong>Email:</strong> {{ $fornecedor->email }}</p>
        <p><strong>Cidade:</strong> {{ $fornecedor->cidade }}</p>
        <p><strong>Observações:</strong> {{ $fornecedor->observacoes ?: '-' }}</p>
        <p><strong>Criado em:</strong> {{ $fornecedor->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="mt-3">
        <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>
@endsection
