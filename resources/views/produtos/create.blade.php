@extends('layouts.app')

@section('content')
<div class="container" style="border:1px solid #ddd; padding:25px; border-radius:8px; background-color:#f9f9f9;">
    
    <h2 class="mb-4">Cadastro de Produto</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- ===================== LINHA 1 ===================== -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="nome" class="form-label">Nome do Produto</label>
                <input list="listaProdutos" class="form-control" id="nome" name="nome"
                       value="{{ old('nome') }}" placeholder="Digite ou selecione" required>

                <datalist id="listaProdutos">
                    @foreach($produtosExistentes as $produtoExistente)
                        <option value="{{ $produtoExistente->nome }}">
                    @endforeach
                </datalist>
            </div>

            <div class="col-md-4">
                <label for="codigo_barras" class="form-label">Código de Barras</label>
                <input type="text" class="form-control" id="codigo_barras"
                       name="codigo_barras" value="{{ old('codigo_barras') }}">
            </div>

            <div class="col-md-4">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" class="form-control" id="sku"
                       name="sku" value="{{ old('sku') }}">
            </div>
        </div>

        <!-- ===================== LINHA 2 ===================== -->
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" class="form-control"
                       name="descricao" required value="{{ old('descricao') }}">
            </div>
        </div>

        <!-- ===================== LINHA 3 ===================== -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label for="categoria_id" class="form-label">Categoria</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Selecione...</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}"
                            {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                <select class="form-select" id="fornecedor_id" name="fornecedor_id" required>
                    <option value="">Selecione...</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}"
                            {{ old('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                            {{ $fornecedor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="unidade_medida_id" class="form-label">Unidade de Medida</label>
                <select class="form-select" id="unidade_medida_id" name="unidade_medida_id" required>
                    <option value="">Selecione...</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}"
                            {{ old('unidade_medida_id') == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="marca_id" class="form-label">Marca</label>
                <select class="form-select" id="marca_id" name="marca_id" required>
                    <option value="">Selecione...</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}"
                            {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                            {{ $marca->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Linha 4 -->
        <div class="row mb-4 align-items-start">
            <div class="col-md-8">

                <!-- Quantidade e Estoque Mínimo -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quantidade_estoque" class="form-label">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque"
                            value="{{ old('quantidade_estoque', 0) }}">
                    </div>

                    <div class="col-md-6">
                        <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                        <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo"
                            value="{{ old('estoque_minimo', 0) }}">
                    </div>
                </div>

                <!-- Linha com 4 campos horizontalmente alinhados -->
                <div class="row mb-3">

                    <div class="col-md-3">
                        <label for="preco_venda" class="form-label">Preço de Venda</label>
                        <input type="number" step="0.01" class="form-control" id="preco_venda"
                            name="preco_venda" value="{{ old('preco_venda', 0.00) }}">
                    </div>

                    <div class="col-md-3">
                        <label for="preco_custo" class="form-label">Preço de Compra</label>
                        <input type="number" step="0.01" class="form-control" id="preco_custo"
                            name="preco_custo" value="{{ old('preco_custo', 0.00) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label for="data_compra" class="form-label">Data da Compra</label>
                        <input type="date" class="form-control" id="data_compra" name="data_compra"
                            value="{{ old('data_compra', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label for="validade_produto" class="form-label">Validade</label>
                        <input type="date" class="form-control" id="validade_produto" name="validade_produto"
                            value="{{ old('validade_produto', \Carbon\Carbon::now()->addDays(30)->format('Y-m-d')) }}" required>
                    </div>

                </div>


            </div>

            <!-- Área da imagem -->
            <div class="col-md-4 d-flex flex-column align-items-center text-center">

                <label for="imagem" class="form-label">Imagem do Produto</label>

                <div style="
                    width: 130px;
                    height: 130px;
                    border: 1px solid #dcdcdc;
                    background: #fafafa;
                    border-radius: 6px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    overflow: hidden;
                    margin-bottom: 10px;">
                    
                    <img id="imagemPreview" 
                        src="{{ asset('img/placeholder.png') }}" 
                        alt="Prévia da Imagem"
                        style="max-width:100%; max-height:100%; object-fit:contain;">
                </div>

                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*"
                    onchange="previewImage(event)" style="max-width: 230px;">
            </div>
        </div>

        <!-- ===================== LINHA 5 ===================== -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Peso (kg)</label>
                <input type="number" step="0.01" class="form-control" name="peso" value="{{ old('peso') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Largura (m)</label>
                <input type="number" step="0.01" class="form-control" name="largura" value="{{ old('largura') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Altura (m)</label>
                <input type="number" step="0.01" class="form-control" name="altura" value="{{ old('altura') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Profundidade (m)</label>
                <input type="number" step="0.01" class="form-control" name="profundidade" value="{{ old('profundidade') }}">
            </div>
        </div>

        <!-- ===================== LOCALIZAÇÃO ===================== -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Localização no Estoque</label>
                <input type="text" class="form-control" name="localizacao_estoque"
                       value="{{ old('localizacao_estoque') }}">
            </div>
        </div>

        <!-- Ativo -->
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" checked>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>

        <!-- BOTÕES -->
        <div class="d-flex gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Voltar</a>
        </div>

    </form>
</div>

<script src="{{ asset('js/produto.js') }}"></script>
@endsection
