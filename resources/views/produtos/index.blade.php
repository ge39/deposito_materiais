@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Produtos Ativos</h2>
        <div>
            <a href="{{ route('produtos.create') }}" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Novo Produto
            </a>
            <a href="{{ route('produtos.inativos') }}" class="btn btn-secondary">
                Produtos Inativos
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($produtos->count() > 0)
        <div class="row g-4">
            @foreach($produtos as $produto)
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $produto->nome }}</h5>
                            <p class="card-text mb-1"><strong>Categoria:</strong> {{ $produto->categoria->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Fornecedor:</strong> {{ $produto->fornecedor->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Marca:</strong> {{ $produto->marca->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Unidade:</strong> {{ $produto->unidadeMedida->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Estoque:</strong> {{ $produto->estoque_total }}</p>
                            <p class="card-text mb-1"><strong>Mínimo:</strong> {{ $produto->estoque_minimo }}</p>
                            <p class="card-text mb-1"><strong>Compra:</strong> {{ \Carbon\Carbon::parse($produto->data_compra)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Validade:</strong> {{ \Carbon\Carbon::parse($produto->validade)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Preço:</strong> R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</p>

                            <div class="d-flex flex-wrap gap-1 mt-3">
                                <a href="{{ route('produtos.show', $produto->id) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <a href="{{ route('lotes.index', $produto->id) }}" class="btn btn-sm btn-primary">Lotes</a>
                                <form action="{{ route('produtos.desativar', $produto->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Deseja desativar este produto?')">
                                        Desativar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="card-flex justify-content-center mt-4">
            {{ $produtos->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhum produto ativo encontrado.
        </div>
    @endif
</div>
@endsection
