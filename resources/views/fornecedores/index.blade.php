@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Fornecedores Ativos</h2>
        <a href="{{ route('fornecedores.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Fornecedor
        </a>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($fornecedores->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($fornecedores as $fornecedor)
                <div class="col">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $fornecedor->nome }}</h5>
                            <p class="card-text mb-1"><strong>Email:</strong> {{ $fornecedor->email }}</p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> {{ $fornecedor->telefone }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('fornecedores.show', $fornecedor->id) }}" class="btn btn-sm btn-info mb-1">Ver</a>
                            <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-sm btn-warning mb-1">Editar</a>
                            <form action="{{ route('fornecedores.desativar', $fornecedor->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-danger mb-1"
                                    onclick="return confirm('Deseja realmente desativar este fornecedor?');">
                                    Desativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $fornecedores->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center py-4 shadow-sm rounded">
            Nenhum fornecedor ativo encontrado.
        </div>
    @endif
</div>
@endsection
