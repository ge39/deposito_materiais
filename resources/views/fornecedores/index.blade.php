@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">Fornecedores Ativos</h2>
       
    </div>

    {{-- Campo de busca --}}
    <form method="GET" action="{{ route('fornecedores.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="busca" class="form-control"
                placeholder="Buscar por nome, telefone, CNPJ ou RG..."
                value="{{ $busca ?? '' }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if(!empty($busca))
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
         {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $fornecedores->links('pagination::bootstrap-5') }}
        </div>
        @forelse($fornecedores as $fornecedor)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-2">{{ $fornecedor->nome }}</h5>
                        <p class="mb-1"><strong>CNPJ:</strong> {{ $fornecedor->cnpj ?? '—' }}</p>
                        <p class="mb-1"><strong>RG:</strong> {{ $fornecedor->rg ?? '—' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $fornecedor->email ?? '—' }}</p>
                        <p class="mb-1"><strong>Telefone:</strong> {{ $fornecedor->telefone ?? '—' }}</p>
                        <p class="mb-1"><strong>Cidade:</strong> {{ $fornecedor->cidade ?? '—' }}</p>
                        <p class="mb-3"><strong>Status:</strong> <span class="text-success">Ativo</span></p>

                        <div class="d-flex gap-2">
                            <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <a href="{{ route('fornecedores.inativos') }}" class="btn btn-secondary">Inativos</a>
                                <a href="{{ route('fornecedores.create') }}" class="btn btn-success">Novo</a>
                            <form action="{{ route('fornecedores.desativar', $fornecedor->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-danger btn-bg" onclick="return confirm('Deseja inativar este fornecedor?')">
                                    Inativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted mt-4">
                Nenhum fornecedor encontrado para "{{ $busca ?? '' }}".
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $fornecedores->links('pagination::bootstrap-5') }}
    </div>

</div>
@endsection
