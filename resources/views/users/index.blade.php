@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Usuários Ativos</h2>
        <a href="{{ route('users.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Usuário
        </a>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($users->count() > 0)
        <div class="row g-4">
            @foreach($users as $user)
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $user->funcionario->nome ?? '—' }}</h5>
                            <p class="card-text mb-1"><strong>E-mail:</strong> {{ $user->funcionario->email ?? '—' }}</p>
                            <p class="card-text mb-1"><strong>Nível de Acesso:</strong> {{ ucfirst($user->nivel_acesso) }}</p>
                            <span class="badge bg-success mb-2">Ativo</span>

                            <div class="card-footer text-center mt-3">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>

                                <form action="{{ route('users.desativa', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Deseja realmente desativar este usuário?')">
                                        <i class="bi bi-x-circle"></i> Desativar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-warning text-center py-4 shadow-sm rounded mt-3">
            Nenhum usuário ativo encontrado.
        </div>
    @endif
</div>
@endsection
