@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Lista de Produtos</h2>

    <!-- Mensagens de sucesso -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Form de busca -->
    <form action="{{ route('produtos.search_grid') }}" method="GET" class="mb-3 d-flex">
        <input type="text" name="query" class="form-control me-2" 
        placeholder="Pesquisar por ID 00020, produto, código, categoria, fornecedor ou marca"
        value="{{ request('query') }}">
        <button class="btn btn-primary" type="submit">Buscar</button>
        <div class="me-2" style="margin-left: 5px;">
            <a href="{{ route('produtos.index-grid') }}" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>
    </form>

    <div class="d-flex gap-1 align-items-center mb-2">
        <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm">Novo</a>
        <a href="{{ route('produtos.index') }}" class="btn btn-warning btn-sm">Visão em Cards</a>
    </div>

    <!-- Grid linear usando div -->
    <div class="border rounded overflow-hidden">
        <!-- Cabeçalho -->
        <div class="d-flex bg-light fw-bold border-bottom p-2">
            <div class="col-1" style="width:70px">Código</div>
            <div class="col-2" style="width:150px">Nome</div>
            <div class="col-1" style="width:70px">Estoque</div>
            <div class="col-1" style="width:80px">Preço</div>
            <div class="col-1" style="width:100px">Unidade</div>
            <div class="col-2" style="width:180px">Categoria</div>
            <div class="col-2" style="width:110px">Marca</div>
            <div class="col-2" style="width:110px">Fornecedor</div>
            <div class="col-1" style="width:150px">Ações</div>
        </div>

        <!-- Produtos -->
        @forelse($produtos as $index => $produto)
            <div class="d-flex align-items-center border-bottom p-2 
                        {{ $loop->even ? 'bg-light' : 'bg-white' }} hover-row" style="font-size: 14px;">
                <div class="col-1" style="width:50px">000{{ $produto->id }}</div>
                <div class="col-2" style="width:180px">{{ $produto->nome }}</div>
                <div class="col-1" style="width:50px">{{ $produto->lotes->sum('quantidade_disponivel') }}</div>
                <div class="col-1" style="width:100px">
                   <p class="card-text mb-1">
                        @if($produto->promocao)
                            <span style="text-decoration: line-through; color: #888;">
                                R$ {{ number_format($produto->promocao->preco_original, 2, ',', '.') }}
                            </span>
                            <span style="color: green; font-weight: bold;">
                             {{ number_format($produto->promocao->preco_promocional, 2, ',', '.') }}
                            </span>
                        @else
                            R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                        @endif
                    </p>
                </div>

                
                <div class="col-2" style="width:100px">{{ $produto->unidadeMedida->nome ?? '-'}}</div>
                <div class="col-2" style="width:180px">{{ $produto->categoria->nome ?? '-' }}</div>
                <div class="col-2" style="width:100px">{{ $produto->marca->nome ?? '-' }}</div>
                <div class="col-2" style="width:100px">{{ $produto->fornecedor->nome ?? '-' }}</div>

                <div class="d-flex gap-1 align-items-center">
                    <a href="{{ route('produtos.show', $produto->id) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('lotes.index', $produto->id) }}" class="btn btn-primary btn-sm">Lotes</a>

                    @if(optional(auth()->user())->nivel_acesso == 'admin' || optional(auth()->user())->nivel_acesso == 'gerente')
                        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('produtos.desativar', $produto->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Deseja realmente desativar este produto?')">Inativar</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="d-flex justify-content-center p-3">
                Nenhum produto encontrado.
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    <div class="mt-3">
        {{ $produtos->links() }}
    </div>
</div>

<!-- Estilo hover opcional -->
<style>
    .hover-row:hover {
        background-color: #e9f5ff !important;
        transition: background-color 0.2s ease-in-out;
    }
</style>
@endsection
