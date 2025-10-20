@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Lista de Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary mb-4">Novo Cliente</a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($clientes->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($clientes as $cliente)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $cliente->nome }}</h5>
                            <p class="card-text mb-1"><strong>Tipo:</strong> {{ ucfirst($cliente->tipo) }}</p>
                            <p class="card-text mb-1"><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
                            <p class="card-text mb-1"><strong>Telefone:</strong> {{ $cliente->telefone }}</p>
                            <p class="card-text mb-1"><strong>Email:</strong> {{ $cliente->email }}</p>
                            <p class="card-text mb-1"><strong>Limite de Crédito:</strong> R$ {{ number_format($cliente->limite_credito, 2, ',', '.') }}</p>
                            <p class="card-text"><strong>Observações:</strong> {{ $cliente->observacoes }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('cliente.desativar', $cliente->id) }}" method="POST" style="display:inline-block;">
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
            {{ $clientes->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center">Nenhum cliente cadastrado</div>
    @endif
</div>
@endsection
