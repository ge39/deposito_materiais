@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Lista de Clientes</h2>
        <a href="{{ route('clientes.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Cliente
        </a>
    </div>

    <!-- 🔍 Formulário de Busca -->
    <form action="{{ route('clientes.index') }}" method="GET" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="nome" class="form-label fw-semibold">Nome ou CPF/CNPJ</label>
                <input type="text" name="busca" id="busca" value="{{ request('busca') }}"
                    class="form-control" placeholder="Digite o nome ou CPF/CNPJ">
            </div>
            <div class="col-md-3">
                <label for="tipo" class="form-label fw-semibold">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="fisica" {{ request('tipo') == 'fisica' ? 'selected' : '' }}>Física</option>
                    <option value="juridica" {{ request('tipo') == 'juridica' ? 'selected' : '' }}>Jurídica</option>
                </select>
            </div>
            
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
                <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary flex-grow-1">Limpar</a>
            </div>
        </div>
    </form>

    <!-- 🟢 Mensagens -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- 🧾 Lista de Clientes -->
    @if($clientes->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($clientes as $cliente)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">{{ $cliente->nome }}</h5>
                            <p class="card-text mb-1"><strong>Tipo:</strong> {{ ucfirst($cliente->tipo) }}</p>
                            <p class="card-text mb-1"><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> {{ $cliente->telefone }}</p>
                            <p class="card-text mb-1"><strong>Email:</strong> {{ $cliente->email }}</p>
                            <p class="card-text mb-1"><strong>Limite de Crédito:</strong> 
                                R$ {{ number_format($cliente->limite_credito, 2, ',', '.') }}
                            </p>
                            <p class="card-text"><strong>Observações:</strong> {{ $cliente->observacoes }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('clientes.desativar', $cliente->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja desativar este Cliente?');">
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
            {{ $clientes->appends(request()->input())->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center">Nenhum cliente encontrado.</div>
    @endif
</div>
@endsection
