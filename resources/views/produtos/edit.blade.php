@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Produto</h2>

    <form action="{{ route('produtos.update', $produto->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" name="descricao" id="descricao" class="form-control" value="{{ $produto->descricao }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="preco" class="form-label">Preço (R$)</label>
                <input type="number" step="0.01" name="preco" id="preco" class="form-control" value="{{ $produto->preco }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="estoque" class="form-label">Estoque</label>
                <input type="number" name="estoque" id="estoque" class="form-control" value="{{ $produto->estoque }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="validade" class="form-label">Validade</label>
                <input type="date" name="validade" id="validade" class="form-control" value="{{ $produto->validade }}">
            </div>

        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="categoria_id" class="form-label">Categoria</label>
                <select name="categoria_id" id="categoria_id" class="form-select">
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ $categoria->id == $produto->categoria_id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                <select name="fornecedor_id" id="fornecedor_id" class="form-select">
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ $fornecedor->id == $produto->fornecedor_id ? 'selected' : '' }}>{{ $fornecedor->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="codigo_barras" class="form-label">Código de Barras</label>
                <input type="text" name="codigo_barras" id="codigo_barras" class="form-control" value="{{ $produto->codigo_barras }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="2" class="form-control">{{ $produto->observacoes }}</textarea>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
