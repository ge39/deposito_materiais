@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Detalhes do Usuário</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Funcionário:</strong> {{ $user->funcionario->nome ?? '—' }}</p>
            <p><strong>Email:</strong> {{ $user->funcionario->email ?? '—' }}</p>
            <p><strong>Nível de Acesso:</strong> {{ ucfirst($user->nivel_acesso) }}</p>
            <p><strong>Status:</strong> 
                @if($user->ativo)
                    <span class="badge bg-success">Ativo</span>
                @else
                    <span class="badge bg-danger">Inativo</span>
                @endif
            </p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>
@endsection
