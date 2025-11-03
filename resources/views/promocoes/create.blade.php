@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Nova Promoção</h4>
            <a href="{{ route('promocoes.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Ops!</strong> Verifique os erros abaixo:<br><br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('promocoes.store') }}" method="POST">
                @csrf

                {{-- Tipo de Abrangência --}}
                <div class="mb-3">
                    <label for="tipo_abrangencia" class="form-label">Tipo de Abrangência</label>
                    <select name="tipo_abrangencia" id="tipo_abrangencia" class="form-select" required onchange="toggleCampos(this.value)">
                        <option value="">Selecione...</option>
                        <option value="produto">Por Produto</option>
                        <option value="categoria">Por Categoria</option>
                    </select>
                </div>

                {{-- Produto --}}
                <div class="mb-3 d-none" id="campo_produto">
                    <label for="produto_id" class="form-label">Produto</label>
                    <select name="produto_id" id="produto_id" class="form-select">
                        <option value="">Selecione um produto...</option>
                        @foreach($produtos as $produto)
                            <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Categoria --}}
                <div class="mb-3 d-none" id="campo_categoria">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-select">
                        <option value="">Selecione uma categoria...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                {{-- Campos de valores --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="desconto_percentual" class="form-label">Desconto (%)</label>
                        <input type="number" name="desconto_percentual" id="desconto_percentual" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="acrescimo_percentual" class="form-label">Acréscimo (%)</label>
                        <input type="number" name="acrescimo_percentual" id="acrescimo_percentual" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="acrescimo_valor" class="form-label">Acréscimo (R$)</label>
                        <input type="number" name="acrescimo_valor" id="acrescimo_valor" class="form-control" step="0.01" min="0">
                    </div>
                </div>

                <hr>

                {{-- Datas --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="promocao_inicio" class="form-label">Data de Início</label>
                        <input type="date" name="promocao_inicio" id="promocao_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="promocao_fim" class="form-label">Data de Fim</label>
                        <input type="date" name="promocao_fim" id="promocao_fim" class="form-control" required>
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="em_promocao" id="em_promocao" checked>
                    <label class="form-check-label" for="em_promocao">Ativar promoção imediatamente</label>
                </div>

                {{-- Botões --}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Salvar Promoção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script para alternar campos --}}
<script>
    function toggleCampos(valor) {
        const campoProduto = document.getElementById('campo_produto');
        const campoCategoria = document.getElementById('campo_categoria');

        campoProduto.classList.add('d-none');
        campoCategoria.classList.add('d-none');

        if (valor === 'produto') {
            campoProduto.classList.remove('d-none');
        } else if (valor === 'categoria') {
            campoCategoria.classList.remove('d-none');
        }
    }
</script>
@endsection
