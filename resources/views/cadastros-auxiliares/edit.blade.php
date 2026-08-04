@extends('layouts.app')

@section('content')
@include('cadastros-auxiliares._styles')

<div class="container-fluid px-3 px-xl-4 py-4 cadastro-auxiliar-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">
                <i class="bi {{ $icone }} me-2 text-primary"></i>{{ $titulo }}
            </h1>
            <p class="page-subtitle mb-0">Atualize os dados do registro #{{ $registro->id }}.</p>
        </div>

        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bi bi-exclamation-circle me-1"></i>Revise os campos destacados.
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
            <h2 class="h6 fw-bold mb-1">Dados da {{ mb_strtolower($singular) }}</h2>
            <p class="text-muted small mb-0">Os campos com asterisco são obrigatórios.</p>
        </div>

        <div class="card-body p-3 p-lg-4">
            <form action="{{ route($routePrefix . '.update', $registro) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="{{ $possuiSigla ? 'col-md-8' : 'col-12' }}">
                        <label for="nome" class="form-label">
                            Nome <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('nome') is-invalid @enderror"
                            id="nome"
                            name="nome"
                            value="{{ old('nome', $registro->nome) }}"
                            required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($possuiSigla)
                        <div class="col-md-4">
                            <label for="sigla" class="form-label">
                                Sigla <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control text-uppercase @error('sigla') is-invalid @enderror"
                                id="sigla"
                                name="sigla"
                                maxlength="10"
                                value="{{ old('sigla', $registro->sigla) }}"
                                required>
                            @error('sigla')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if($possuiDescricao)
                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea
                                class="form-control @error('descricao') is-invalid @enderror"
                                id="descricao"
                                name="descricao"
                                rows="4">{{ old('descricao', $registro->descricao) }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <input type="hidden" name="ativo" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="ativo"
                                name="ativo"
                                value="1"
                                {{ old('ativo', (string) $registro->ativo) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="ativo">Registro ativo</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end flex-wrap gap-2 border-top mt-4 pt-3">
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
