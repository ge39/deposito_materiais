@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes do Produto</h1>

    <div class="row">
        <!-- Coluna 1: Informações básicas -->
        <div class="col-md-4">
            <div class="mb-3"><strong>Nome:</strong> {{ $produto->nome }}</div>
            <div class="mb-3"><strong>Categoria:</strong> {{ $produto->categoria->nome ?? '-' }}</div>
            <div class="mb-3"><strong>Fornecedor:</strong> {{ $produto->fornecedor->nome ?? '-' }}</div>
            <div class="mb-3"><strong>Marca:</strong> {{ $produto->marca->nome ?? '-' }}</div>
            <div class="mb-3"><strong>Unidade de Medida:</strong> {{ $produto->unidadeMedida->nome ?? '-' }}</div>
            <div class="mb-3"><strong>Código de Barras:</strong> {{ $produto->codigo_barras }}</div>
            <div class="mb-3"><strong>SKU:</strong> {{ $produto->sku }}</div>
        </div>

        <!-- Coluna 2: Estoque, preços e datas -->
        <div class="col-md-4">
            <div class="mb-3"><strong>Quantidade em Estoque:</strong> {{ $produto->quantidade_estoque }}</div>
            <div class="mb-3"><strong>Estoque Mínimo:</strong> {{ $produto->estoque_minimo }}</div>
            <div class="mb-3"><strong>Preço de Custo:</strong> R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</div>
            <div class="mb-3"><strong>Preço de Venda:</strong> R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</div>
            <div class="mb-3"><strong>Data da Compra:</strong> {{ $produto->data_compra ? $produto->data_compra->format('d/m/Y') : '-' }}</div>
            <div class="mb-3"><strong>Validade:</strong> {{ $produto->validade ? $produto->validade->format('d/m/Y') : '-' }}</div>
            <div class="mb-3"><strong>Peso:</strong> {{ $produto->peso }} kg</div>
        </div>

       <!-- Coluna 3: Imagem -->
        <div class="col-md-4 d-flex flex-column align-items-center justify-content-center text-center">
           
            <div class="mt-3">
                @if($produto->imagem)
                    <img src="{{ asset('storage/' . $produto->imagem) }}" alt="Imagem do Produto"
                        class="img-fluid" style="max-width: 300px; max-height: 300px; border:1px solid #ddd; padding:5px;">
                @else
                    <div style="width: 300px; height: 300px; border:1px solid #ddd; display:flex; align-items:center; justify-content:center;">
                        Sem Imagem
                    </div>
                @endif
            </div>
             <strong>Imagem:</strong>
        </div>

    </div>

    <a href="{{ route('produtos.index') }}" class="btn btn-secondary mt-4">Voltar</a>
</div>
@endsection
