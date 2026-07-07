@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-pencil-square me-2"></i>Editar Localização de Estoque
            </h3>
            <small class="text-muted">
                Atualize as informações da localização física do depósito.
            </small>
        </div>

        <a href="{{ route('localizacoes-estoque.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Verifique os campos abaixo:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('localizacoes-estoque.update', $localizacao->id) }}">

        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-map me-1"></i>
                Identificação da Localização
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">
                            Código
                        </label>

                        <input
                            type="text"
                            name="codigo"
                            class="form-control"
                            value="{{ old('codigo', $localizacao->codigo) }}"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Descrição
                        </label>

                        <input
                            type="text"
                            name="descricao"
                            class="form-control"
                            value="{{ old('descricao', $localizacao->descricao) }}">
                    </div>

                    <div class="col-md-3">

                        <label class="form-label">
                            Tipo
                        </label>

                        <select
                            name="tipo_localizacao"
                            class="form-select"
                            required>

                            @foreach($tipos as $tipo)

                                <option
                                    value="{{ $tipo }}"
                                    {{ old('tipo_localizacao', $localizacao->tipo_localizacao) == $tipo ? 'selected' : '' }}>

                                    {{ $tipo }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </div>

        </div>


        <div class="card shadow-sm mb-4">

            <div class="card-header bg-light fw-bold">
                Estrutura Física
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">Setor</label>

                        <input
                            type="text"
                            name="setor"
                            class="form-control"
                            value="{{ old('setor', $localizacao->setor) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Rua</label>

                        <input
                            type="text"
                            name="rua"
                            class="form-control"
                            value="{{ old('rua', $localizacao->rua) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Lado</label>

                        <input
                            type="text"
                            name="lado"
                            class="form-control"
                            value="{{ old('lado', $localizacao->lado) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Módulo</label>

                        <input
                            type="text"
                            name="modulo"
                            class="form-control"
                            value="{{ old('modulo', $localizacao->modulo) }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Prateleira</label>

                        <input
                            type="text"
                            name="prateleira"
                            class="form-control"
                            value="{{ old('prateleira', $localizacao->prateleira) }}">
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Nível</label>

                        <input
                            type="text"
                            name="nivel"
                            class="form-control"
                            value="{{ old('nivel', $localizacao->nivel) }}">
                    </div>

                </div>

            </div>

        </div>


        <div class="card shadow-sm mb-4">

            <div class="card-header bg-light fw-bold">
                Ordem de Coleta
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3">

                        <label class="form-label">
                            Ordem de Coleta
                        </label>

                        <input
                            type="number"
                            name="ordem_coleta"
                            class="form-control"
                            value="{{ old('ordem_coleta', $localizacao->ordem_coleta) }}"
                            required>

                    </div>
                    <div class="col-md-3">

                        <div class="form-check form-switch mt-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="ativo"
                                value="1"
                                {{ old('ativo', $localizacao->ativo) ? 'checked' : '' }}>

                            <label class="form-check-label">

                                Localização Ativa

                            </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="text-end">

            <a
                href="{{ route('localizacoes-estoque.index') }}"
                class="btn btn-secondary">

                Cancelar

            </a>

            <button
                type="submit"
                class="btn btn-success">

                <i class="bi bi-check-circle me-1"></i>

                Salvar Alterações

            </button>

        </div>

    </form>

</div>

@endsection