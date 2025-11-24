@extends('layouts.app')

@section('content')
<div class="container pt-4" style="border:1px solid #ddd; padding:15px; border-radius:5px; background-color:#f9f9f9;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Produtos Ativos</h2>
        <!-- <div>
            <a href="{{ route('produtos.create') }}" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Novo Produto
            </a>
            <a href="{{ route('produtos.inativos') }}" class="btn btn-secondary">
                Produtos Inativos
            </a>
        </div> -->
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- Formulário de busca -->
    <form action="{{ route('produtos.search') }}" method="GET" class="mb-3 row g-2 align-items-end">
        <div class="col-md-8">
            <input type="text" name="query" class="form-control" placeholder="Buscar por nome, categoria ou fornecedor..." value="{{ request('q') }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
            <a href="{{ route('produtos.index') }}" class="btn btn-secondary flex-grow-1">Limpar</a>
        </div>
        
    </form>
    <div class="col-md-4 d-flex gap-2">
            <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm "style="width: 6.3rem">
                <i class="bi bi-plus-circle"></i> Novo
            </a>
    </div>
    @if($produtos->count() > 0)
        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-6">
            {{ $produtos->links('pagination::bootstrap-5') }}
        </div>
        <div class="row g-4">
            @foreach($produtos as $produto)
                
                <div class="col-md-5 col-lg-4">
                    <div class="card h-100 
                            @if($produto->promocao && $produto->promocao->preco_promocional < $produto->preco_venda)
                                border border-danger shadow" style="background-color:#fff5f5;"
                            @else
                                shadow-sm"
                            @endif
                        >
                        <div class="card-body">
                            <h5 class="card-title">{{ $produto->nome }}</h5>
                            <p class="card-text mb-1"><strong>Produto ID:</strong> 000{{ $produto->id ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Categoria:</strong> {{ $produto->categoria->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Fornecedor:</strong> {{ $produto->fornecedor->nome ?? '-' }}</p>
                            <p class="card-text mb-1" style="color:blue"><strong>Preço de Custo:</strong> R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</p>
                            <p class="card-text mb-1" style="color:blue"><strong>Preço Médio de Compra:</strong> R$ {{ number_format($produto->preco_medio_compra, 2, ',', '.') }}</p>
                            <p class="card-text mb-1"><strong>Marca:</strong> {{ $produto->marca->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Unidade:</strong> {{ $produto->unidadeMedida->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Estoque:</strong> {{ $produto->quantidade_estoque }}</p>
                            <p class="card-text mb-1"><strong>Mínimo:</strong> {{ $produto->estoque_minimo }}</p>
                            <p class="card-text mb-1"><strong>Compra:</strong> {{ \Carbon\Carbon::parse($produto->data_compra)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Validade:</strong> {{ \Carbon\Carbon::parse($produto->validade_produto)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1">
                                <strong>Preço Venda:</strong>
                                @if($produto->promocao)
                                    <span style="text-decoration: line-through; color: #888;">
                                        R$ {{ number_format($produto->promocao->preco_original, 2, ',', '.') }}
                                    </span>
                                    <span style="color: green; font-weight: bold;">
                                        por R$ {{ number_format($produto->promocao->preco_promocional, 2, ',', '.') }}
                                    </span>
                                @else
                                    R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                                @endif
                            </p>

                                                
                            {{-- EXIBIR SOMENTE SE EXISTIR PROMOÇÃO VÁLIDA --}}
                            @if ($produto->promocao)
                                <p class="card-text mb-1" style="color:orange; font-weight:bold;font-size:1.5rem">
                                    <strong>Valor Promoção:</strong>
                                    R$ {{ number_format($produto->promocao->preco_promocional, 2, ',', '.') }}
                                </p>

                                <p class="card-text mb-1" style="color:green;">
                                    <strong>Válido Até:</strong>
                                    {{ \Carbon\Carbon::parse($produto->promocao->promocao_fim)->format('d/m/Y') }}
                                </p>
                            @endif
                            
                            <div class="d-flex flex-wrap gap-1 mt-3">
                                <div>
                                    @if($produto->imagem)
                                    <label for="imagem" class="form-label">Imagem do Produto</label>
                                        <img id="imagemPreview" src="{{ asset('storage/' . $produto->imagem) }}" alt="Imagem Atual" style="max-width:200px; max-height:200px; border:1px solid #ddd; padding:5px;">
                                    @else
                                        <img id="imagemPreview" src="#" alt="Prévia da Imagem" style="display:none; max-width:200px; max-height:200px; border:1px solid #ddd; padding:5px;">
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-3">
                                <a href="{{ route('produtos.show', $produto->id) }}" class="btn btn-sm btn-info" style="width: 6.3rem">Ver</a>
                                <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning "style="width: 6.3rem">Editar</a>
                                <a href="{{ route('lotes.index', $produto->id) }}" class="btn btn-sm btn-primary "style="width: 6.3rem">Lotes</a>
                                
                                <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm "style="width: 6.3rem">
                                     <i class="bi bi-plus-circle"></i> Novo
                                 </a>

                                 <a href="{{ route('produtos.index-grid') }}" class="btn btn-warning btn-sm "style="width: 6.3rem">
                                     <i class="bi bi-plus-circle"></i> Grid
                                 </a>
                                 <a href="{{ route('produtos.inativos') }}" class="btn btn-secondary btn-sm"style="width: 6.3rem">
                                    Inativados
                                </a>
                                <form action="{{ route('produtos.desativar', $produto->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-danger"style="width: 6.3rem"
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
        <div class="d-flex justify-content-center mt-4">
            {{ $produtos->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhum produto ativo encontrado.
        </div>
    @endif
</div>
@endsection
