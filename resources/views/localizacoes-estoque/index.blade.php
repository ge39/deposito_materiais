@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-geo-alt me-2"></i>Localizações de Estoque
            </h3>
            <small class="text-muted">
                Gerenciamento das posições físicas do depósito para coleta, romaneio e inventário.
            </small>
        </div>

        <a href="{{ route('localizacoes-estoque.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nova Localização
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Total</small>
                        <h4 class="fw-bold mb-0">{{ $localizacoes->total() }}</h4>
                    </div>
                    <i class="bi bi-grid-3x3-gap fs-1 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Ativas na página</small>
                        <h4 class="fw-bold mb-0">{{ $localizacoes->where('ativo', 1)->count() }}</h4>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Com produtos</small>
                        <h4 class="fw-bold mb-0">{{ $localizacoes->filter(fn($l) => ($l->produtos_count ?? 0) > 0)->count() }}</h4>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Livres na página</small>
                        <h4 class="fw-bold mb-0">{{ $localizacoes->filter(fn($l) => ($l->produtos_count ?? 0) == 0)->count() }}</h4>
                    </div>
                    <i class="bi bi-inbox fs-1 text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-funnel me-1"></i> Filtros
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('localizacoes-estoque.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Busca</label>
                        <input
                            type="text"
                            name="busca"
                            class="form-control"
                            placeholder="Código, descrição, setor ou rua"
                            value="{{ request('busca') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo_localizacao" class="form-select">
                            <option value="">Todos</option>
                            @foreach(['Galpao','Patio','Area Externa','Pulmao','Picking','Devolucao','Quarentena'] as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo_localizacao') == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="ativo" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>

                        <a href="{{ route('localizacoes-estoque.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-ul me-1"></i> Mapa de Localizações
            </span>

            <span class="badge bg-light text-dark">
                {{ $localizacoes->total() }} registros
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th>Setor</th>
                        <th>Rua</th>
                        <th class="text-center">Ordem</th>
                        <th class="text-center">Produtos</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($localizacoes as $localizacao)
                        <tr>
                            <td class="fw-bold">
                                {{ $localizacao->codigo }}
                            </td>

                            <td>
                                {{ $localizacao->descricao ?? '-' }}
                            </td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $localizacao->tipo_localizacao }}
                                </span>
                            </td>

                            <td>{{ $localizacao->setor ?? '-' }}</td>
                            <td>{{ $localizacao->rua ?? '-' }}</td>

                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    {{ $localizacao->ordem_coleta }}
                                </span>
                            </td>

                            <td class="text-center">
                                @php
                                    $qtdProdutos = $localizacao->produtos_count ?? 0;
                                @endphp

                                @if($qtdProdutos == 0)
                                    <span class="badge bg-success">Livre</span>
                                @elseif($qtdProdutos <= 20)
                                    <span class="badge bg-warning text-dark">{{ $qtdProdutos }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $qtdProdutos }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($localizacao->ativo)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a
                                        href="{{ route('localizacoes-estoque.edit', $localizacao->id) }}"
                                        class="btn btn-outline-primary"
                                        title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form
                                        action="{{ route('localizacoes-estoque.destroy', $localizacao->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Deseja realmente excluir esta localização?');">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-outline-danger"
                                            title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Nenhuma localização de estoque encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($localizacoes->hasPages())
            <div class="card-footer">
                {{ $localizacoes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection