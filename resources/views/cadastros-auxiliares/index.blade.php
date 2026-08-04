@extends('layouts.app')

@section('content')
@include('cadastros-auxiliares._styles')

<div class="container-fluid px-3 px-xl-4 py-4 cadastro-auxiliar-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">
                <i class="bi {{ $icone }} me-2 text-primary"></i>{{ $titulo }}
            </h1>
            <p class="page-subtitle mb-0">{{ $subtitulo }}</p>
        </div>

        @if(\Illuminate\Support\Facades\Route::has('produtos.create'))
            <a href="{{ route('produtos.create') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao produto
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

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

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="kpi-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <span class="kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-collection"></i>
                </span>
                <div>
                    <div class="text-muted small">Total cadastrado</div>
                    <div class="fs-4 fw-bold">{{ $totais['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <span class="kpi-icon bg-success-subtle text-success">
                    <i class="bi bi-check-circle"></i>
                </span>
                <div>
                    <div class="text-muted small">Ativos</div>
                    <div class="fs-4 fw-bold">{{ $totais['ativos'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <span class="kpi-icon bg-secondary-subtle text-secondary">
                    <i class="bi bi-pause-circle"></i>
                </span>
                <div>
                    <div class="text-muted small">Inativos</div>
                    <div class="fs-4 fw-bold">{{ $totais['inativos'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
            <h2 class="h6 fw-bold mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>
                Cadastrar {{ mb_strtolower($singular) }}
            </h2>
            <p class="text-muted small mb-0">Preencha os dados e salve o novo registro.</p>
        </div>

        <div class="card-body p-3 p-lg-4">
            <form action="{{ route($routePrefix . '.store') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="{{ $possuiDescricao ? 'col-lg-4' : ($possuiSigla ? 'col-lg-6' : 'col-lg-8') }}">
                        <label for="novo_nome" class="form-label">
                            Nome <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('nome') is-invalid @enderror"
                            id="novo_nome"
                            name="nome"
                            maxlength="{{ $routePrefix === 'marcas' ? 100 : ($routePrefix === 'unidades-medida' ? 50 : 255) }}"
                            value="{{ old('nome') }}"
                            required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($possuiSigla)
                        <div class="col-lg-2">
                            <label for="nova_sigla" class="form-label">
                                Sigla <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control text-uppercase @error('sigla') is-invalid @enderror"
                                id="nova_sigla"
                                name="sigla"
                                maxlength="10"
                                value="{{ old('sigla') }}"
                                required>
                            @error('sigla')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if($possuiDescricao)
                        <div class="col-lg-5">
                            <label for="nova_descricao" class="form-label">Descrição</label>
                            <input
                                type="text"
                                class="form-control @error('descricao') is-invalid @enderror"
                                id="nova_descricao"
                                name="descricao"
                                value="{{ old('descricao') }}">
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-sm-6 col-lg-2">
                        <input type="hidden" name="ativo" value="0">
                        <div class="form-check form-switch pt-lg-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="novo_ativo"
                                name="ativo"
                                value="1"
                                {{ old('ativo', '1') === '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="novo_ativo">Ativo</label>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-auto ms-lg-auto">
                        <button type="submit" class="btn btn-success w-100 px-4">
                            <i class="bi bi-check-circle me-1"></i>Salvar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="filter-bar p-3 mb-4">
        <form action="{{ route($routePrefix . '.index') }}" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-7">
                    <label for="busca" class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="busca"
                            name="busca"
                            value="{{ request('busca') }}"
                            placeholder="{{ $possuiSigla ? 'Nome ou sigla' : 'Nome' }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="todos" {{ request('status', 'todos') === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="ativos" {{ request('status') === 'ativos' ? 'selected' : '' }}>Ativos</option>
                        <option value="inativos" {{ request('status') === 'inativos' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h6 fw-bold mb-1">Registros cadastrados</h2>
                <p class="text-muted small mb-0">Resultado conforme os filtros aplicados.</p>
            </div>
            <span class="badge text-bg-light border">{{ $registros->total() }} registro(s)</span>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 90px;">ID</th>
                        <th scope="col">Nome</th>
                        @if($possuiSigla)
                            <th scope="col" style="width: 160px;">Sigla</th>
                        @endif
                        @if($possuiDescricao)
                            <th scope="col">Descrição</th>
                        @endif
                        <th scope="col" class="text-center" style="width: 130px;">Status</th>
                        <th scope="col" class="text-end" style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $registro)
                        @php($registroAtivo = (string) $registro->ativo === '1')
                        <tr>
                            <td class="text-muted">#{{ $registro->id }}</td>
                            <td class="fw-semibold">{{ $registro->nome }}</td>
                            @if($possuiSigla)
                                <td><span class="badge text-bg-light border">{{ $registro->sigla }}</span></td>
                            @endif
                            @if($possuiDescricao)
                                <td class="description-cell text-muted">{{ $registro->descricao ?: '—' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge status-badge {{ $registroAtivo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $registroAtivo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a
                                        href="{{ route($routePrefix . '.edit', $registro) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form
                                        action="{{ route($routePrefix . '.alternar-status', $registro) }}"
                                        method="POST"
                                        onsubmit="return confirm('{{ $registroAtivo ? 'Desativar' : 'Ativar' }} este registro?');">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $registroAtivo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $registroAtivo ? 'Desativar' : 'Ativar' }}">
                                            <i class="bi {{ $registroAtivo ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + ($possuiSigla ? 1 : 0) + ($possuiDescricao ? 1 : 0) }}" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                <span class="text-muted">Nenhum registro encontrado.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registros->hasPages())
            <div class="card-footer bg-white border-top px-3 px-lg-4 py-3">
                {{ $registros->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
