@extends('layouts.app')

@section('content')
<div class="container pt-4" style="border:1px solid #ddd; padding:15px; border-radius:5px; background-color:#f9f9f9;">
    <h1 class="mb-3">Editar Produto 000{{ $produto->id }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Seção 1: Nome, Código de Barras, SKU -->
        <div class="row mb-2 p-2" style="background-color:#ffffff; border-radius:6px;">
            <div class="col-md-4 mb-2 mb-md-0">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $produto->nome) }}" readOnly>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <label for="codigo_barras" class="form-label">Código de Barras</label>
                <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}">
            </div>
            <div class="col-md-4">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" class="form-control text-muted" id="sku" value="{{ old('sku', $produto->sku) }}" disabled>
            </div>
        </div>

        <!-- Seção 2: Descrição -->
        <div class="row mb-2 p-2 bg-white rounded">
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="2">{{ old('descricao', $produto->descricao) }}</textarea>
            </div>
        </div>

        <!-- Seção 3: Categoria, Fornecedor, Unidade, Marca -->
        <div class="row mb-2 p-2 bg-white rounded">
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="categoria_id" class="form-label">Categoria</label>
                <select class="form-control" id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione...</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $produto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                <select class="form-control" id="fornecedor_id" name="fornecedor_id" required>
                    <option value="">Selecione...</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ old('fornecedor_id', $produto->fornecedor_id) == $fornecedor->id ? 'selected' : '' }}>
                            {{ $fornecedor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="unidade_medida_id" class="form-label">Unidade de Medida</label>
                <select class="form-control" id="unidade_medida_id" name="unidade_medida_id" required>
                    <option value="">Selecione...</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ old('unidade_medida_id', $produto->unidade_medida_id) == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="marca_id" class="form-label">Marca</label>
                <select class="form-control" id="marca_id" name="marca_id" required>
                    <option value="">Selecione...</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}" {{ old('marca_id', $produto->marca_id) == $marca->id ? 'selected' : '' }}>
                            {{ $marca->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Seção 4: Estoque e Preços -->
        <!-- <div class="row mb-2 p-2 bg-white rounded">
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="quantidade_estoque" class="form-label">Qtd Estoque</label>
                <input type="number" class="form-control text-muted" id="quantidade_estoque" value="{{ $produto->quantidade_estoque }}" required>
            </div>
            <div class="col-md-3">
                <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                <input type="number" class="form-control text-muted" id="estoque_minimo" value="{{ $produto->estoque_minimo }}" required>
                
            </div>
            <div class="row md-3">
                <div class="col-md-3">
                    <label for="data_compra" class="form-label">Data da Compra</label>
                    <input type="date" class="form-control" id="data_compra" name="data_compra" value="{{ old('data_compra', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="validade_produto" class="form-label">Validade</label>
                    <input type="date" name="validade_produto"
                    value="{{ old('validade_produto', optional($produto->validade_produto)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="preco_custo" class="form-label">Preço Custo</label>
                    <input type="number" step="0.01" class="form-control text-muted" id="preco_custo" 
                    value="{{ $produto->preco_custo }}" disabled>
                </div>
                <div class="col-md-3">
                    <label for="preco_venda" class="form-label">Preço Venda</label>
                    <input type="number" step="0.01" class="form-control text-muted" id="preco_venda" value="{{ $produto->preco_venda }}" disabled>
                </div>
            </div>
        </div> -->

        <!-- Seção 4: Estoque e Preços -->
<div class="row mb-2 p-2 bg-white rounded">

    <div class="col-md-3 mb-2 mb-md-0">
        <label for="quantidade_estoque" class="form-label">Qtd Estoque</label>
        <input type="number" class="form-control text-muted" 
               id="quantidade_estoque" 
               name="quantidade_estoque"
               value="{{ $produto->quantidade_estoque }}" required>
    </div>

    <div class="col-md-3">
        <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
        <input type="number" class="form-control text-muted" 
               id="estoque_minimo"
               name="estoque_minimo"
               value="{{ $produto->estoque_minimo }}" required>
    </div>

    <!-- Linha com 4 campos alinhados -->
    <div class="row mt-3">

        <div class="col-md-3">
            <label for="data_compra" class="form-label">Data da Compra</label>
            <input type="date" class="form-control"
                   id="data_compra" name="data_compra"
                   value="{{ old('data_compra', date('Y-m-d')) }}">
        </div>

        <div class="col-md-3">
            <label for="validade_produto" class="form-label">Validade</label>
            <input type="date" class="form-control"
                   id="validade_produto" name="validade_produto"
                   value="{{ old('validade_produto', optional($produto->validade_produto)->format('Y-m-d')) }}">
        </div>

        <div class="col-md-3">
            <label for="preco_custo" class="form-label">Preço Custo</label>
            <input type="number" step="0.01" class="form-control text-muted"
                   id="preco_custo"
                   name="preco_custo"
                   value="{{ $produto->preco_custo }}" disabled>
        </div>

        <div class="col-md-3">
            <label for="preco_venda" class="form-label">Preço Venda</label>
            <input type="number" step="0.01" class="form-control text-muted"
                   id="preco_venda"
                   name="preco_venda"
                   value="{{ $produto->preco_venda }}" disabled>
        </div>

        </div>
    </div>


        <!-- Seção 5: Imagem e Dimensões -->
        <div class="row mb-2 p-2 bg-white rounded">
            <div class="col-md-4 mb-2 mb-md-0 text-center">
                <label for="imagem" class="form-label">Imagem</label>
                <input type="file" class="form-control mb-1" id="imagem" name="imagem" accept="image/*" onchange="previewImage(event)">
                <img id="imagemPreview" src="{{ $produto->imagem ? asset('storage/' . $produto->imagem) : asset('storage/produtos/default.png') }}" 
                     alt="Prévia" style="max-width:150px; max-height:150px; border:1px solid #ccc; padding:3px;">
            </div>
            <div class="col-md-8">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label for="peso" class="form-label">Peso (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="peso" name="peso" value="{{ old('peso', $produto->peso) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="largura" class="form-label">Largura (m)</label>
                        <input type="number" step="0.01" class="form-control" id="largura" name="largura" value="{{ old('largura', $produto->largura) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="altura" class="form-label">Altura (m)</label>
                        <input type="number" step="0.01" class="form-control" id="altura" name="altura" value="{{ old('altura', $produto->altura) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="profundidade" class="form-label">Profundidade (m)</label>
                        <input type="number" step="0.01" class="form-control" id="profundidade" name="profundidade" value="{{ old('profundidade', $produto->profundidade) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="localizacao_estoque" class="form-label">Localização Estoque</label>
                        <input type="text" class="form-control" id="localizacao_estoque" name="localizacao_estoque" value="{{ old('localizacao_estoque', $produto->localizacao_estoque) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 6: Ativo e Botões -->
        <div class="row mb-2 p-2 bg-white rounded align-items-center">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" {{ $produto->ativo ? 'checked' : '' }}>
                    <label class="form-check-label" for="ativo">Ativo</label>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ url()->previous() }}" id="btn-voltar"  class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </form>
</div>

<script src="{{ asset('js/produto.js') }}"></script>
<script>
    let inactivityTime = function () {
        let time;
        const timeout = 30000; // 30 segundos

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;

        function logout() {
            // Limpa flag via AJAX
            fetch("{{ route('produtos.limparEdicao', $produto->id) }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).finally(() => {
                // alert('Você ficou inativo por 30 segundos. Voltando à lista de produtos.');
                window.location.href = "{{ url()->previous() }}";
            });
        }

        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, timeout);
        }
    };

    inactivityTime();

    document.addEventListener('DOMContentLoaded', function() {
    const limparEdicao = () => {
        fetch("{{ route('produtos.limparEdicao', $produto->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            }
        });
    };

    // Botão Voltar
    const btnVoltar = document.querySelector('#btn-voltar');
    if (btnVoltar) {
        btnVoltar.addEventListener('click', function(e) {
            e.preventDefault(); // impede o redirecionamento imediato
            limparEdicao();
            window.location.href = btnVoltar.href; // depois vai para a index
        });
    }

    // Antes de fechar a aba
    window.addEventListener('beforeunload', function() {
        limparEdicao();
    });
    });
</script>

<style>
    body { background-color:#f0f2f5; }
    .form-control { background-color:#fff; border-radius:4px; padding:5px 8px; }
    .form-control[disabled] { background-color:#e9ecef; }
    label { font-weight:500; margin-bottom:2px; }
    .bg-white { background-color:#fff !important; }
    .row { margin-bottom:0; }
</style>
@endsection
