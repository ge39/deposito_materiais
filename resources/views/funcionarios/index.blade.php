@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Funcionários Ativos</h2>
        <a href="{{ route('funcionarios.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Funcionário
        </a>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    <form action="{{ route('funcionarios.search') }}" method="GET" class="mb-3 row g-2 align-items-end">
    
        <div class="col-md-8">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nome, CPF ou e-mail..." value="{{ request('q') }}">
        </div>

        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
            <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>

    </form>


@if(isset($mensagem))
    <div class="alert alert-warning text-center">
        {{ $mensagem }}
    </div>
@endif

    @if($funcionarios->count() > 0)
        <div class="row g-4">
            @foreach($funcionarios as $funcionario)
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $funcionario->nome }}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{ $funcionario->funcao }}</h6>
                            <p class="card-text mb-1"><strong>CPF:</strong> {{ $funcionario->cpf }}</p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> {{ $funcionario->telefone }}</p>
                            <p class="card-text mb-1"><strong>E-mail:</strong> {{ $funcionario->email }}</p>
                            <span class="badge bg-success mb-2">Ativo</span>

                            <div class="card-footer text-center">
                                <a href="{{ route('funcionarios.show', $funcionario->id) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>

                                <form action="{{ route('funcionarios.desativar', $funcionario->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Deseja realmente desativar este funcionário?')">
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
            {{ $funcionarios->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhum funcionário ativo encontrado.
        </div>
    @endif
</div>
@endsection
