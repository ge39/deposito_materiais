@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-truck me-2"></i>Cadastro de Veículos
            </h2>
            <p class="text-muted mb-0">
                Gestão da frota própria, agregada e terceirizada para apoio à expedição e logística.
            </p>
        </div>

        <a href="{{ route('veiculos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Novo Veículo
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>Corrija os campos abaixo:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Total</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['total'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-truck fs-2 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Ativos</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['ativos'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Disponíveis</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['disponiveis'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-geo-alt fs-2 text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Em operação</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['em_operacao'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-box-seam fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Manutenção</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['manutencao'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-tools fs-2 text-danger"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Pendências</span>
                        <h4 class="fw-bold mb-0">{{ $kpis['pendencia_documental'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-2 text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-funnel me-2"></i>Filtros de Consulta
            </span>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('veiculos.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Busca</label>
                    <input type="text"
                           name="busca"
                           value="{{ request('busca') }}"
                           class="form-control"
                           placeholder="Placa, modelo, marca, proprietário...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Ativo" @selected(request('status') === 'Ativo')>Ativo</option>
                        <option value="Inativo" @selected(request('status') === 'Inativo')>Inativo</option>
                        <option value="Manutencao" @selected(request('status') === 'Manutencao')>Manutenção</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Disponibilidade</label>
                    <select name="disponibilidade" class="form-select">
                        <option value="">Todas</option>
                        <option value="Disponivel" @selected(request('disponibilidade') === 'Disponivel')>Disponível</option>
                        <option value="Reservado" @selected(request('disponibilidade') === 'Reservado')>Reservado</option>
                        <option value="Carregando" @selected(request('disponibilidade') === 'Carregando')>Carregando</option>
                        <option value="Em_rota" @selected(request('disponibilidade') === 'Em_rota')>Em rota</option>
                        <option value="Manutencao" @selected(request('disponibilidade') === 'Manutencao')>Manutenção</option>
                        <option value="Indisponivel" @selected(request('disponibilidade') === 'Indisponivel')>Indisponível</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo Frota</label>
                    <select name="tipo_frota" class="form-select">
                        <option value="">Todos</option>
                        <option value="Frota" @selected(request('tipo_frota') === 'Frota')>Frota</option>
                        <option value="Agregado" @selected(request('tipo_frota') === 'Agregado')>Agregado</option>
                        <option value="Terceirizado" @selected(request('tipo_frota') === 'Terceirizado')>Terceirizado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Operação</label>
                    <select name="operacao_preferencial" class="form-select">
                        <option value="">Todas</option>
                        <option value="Urbana" @selected(request('operacao_preferencial') === 'Urbana')>Urbana</option>
                        <option value="Rodoviaria" @selected(request('operacao_preferencial') === 'Rodoviaria')>Rodoviária</option>
                        <option value="Mista" @selected(request('operacao_preferencial') === 'Mista')>Mista</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>

                        <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-check me-2"></i>Veículos Cadastrados
            </span>

            <span class="badge bg-light text-dark">
                {{ $veiculos->total() }} registro(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Capacidade</th>
                            <th>Motorista</th>
                            <th>Operação</th>
                            <th>Status</th>
                            <th>Docs</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                       @forelse($veiculos as $veiculo)
<tr>

    <td>
        <div class="fw-bold">{{ $veiculo->placa }}</div>

        <div class="text-muted small">
            {{ trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? '')) ?: 'Modelo não informado' }}
        </div>

        <div class="text-muted small">
            {{ $veiculo->ano_fabricacao ?? '-' }}
            ·
            {{ $veiculo->cor ?? '-' }}
        </div>
    </td>

    <td>

        <span class="badge bg-primary">
            {{ $veiculo->tipo_frota }}
        </span>

        <div class="small mt-1 fw-semibold">
            {{ $veiculo->tipoVeiculo->descricao ?? '-' }}
        </div>

        <div class="small text-muted">
            {{ $veiculo->classeVeiculo->descricao ?? '-' }}
        </div>

        <div class="small text-muted">
            {{ $veiculo->tipoCarroceria->descricao ?? '-' }}
        </div>

    </td>

    <td>

        <div class="small">
            <strong>Kg:</strong>

            {{ $veiculo->capacidade_kg
                ? number_format($veiculo->capacidade_kg,2,',','.')
                : '-' }}
        </div>

        <div class="small">
            <strong>m³:</strong>

            {{ $veiculo->capacidade_m3
                ? number_format($veiculo->capacidade_m3,2,',','.')
                : '-' }}
        </div>

    </td>

    <td>

        <div class="small">
            <strong>Motorista:</strong>

            {{ $veiculo->motoristaPadrao->nome ?? '-' }}
        </div>

    </td>

    <td>

        @php

            $statusClass = match($veiculo->status){

                'Ativo'       => 'success',

                'Inativo'     => 'secondary',

                'Manutenção'  => 'danger',

                default       => 'secondary',

            };

            $disponibilidadeClass = match($veiculo->disponibilidade){

                'Disponível'    => 'success',

                'Reservado'     => 'warning',

                'Carregando'    => 'info',

                'Em rota'       => 'primary',

                'Manutenção'    => 'danger',

                'Indisponível'  => 'secondary',

                default         => 'secondary',

            };

                            @endphp

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $veiculo->status }}
                                    </span>

                                    <div class="mt-1">
                                        <span class="badge bg-{{ $disponibilidadeClass }}">
                                            {{ $veiculo->disponibilidade }}
                                        </span>
                                    </div>

                                </td>

                                <td class="text-end">

                                    <div class="btn-group">

                                        <a href="{{ route('veiculos.show',$veiculo) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="Visualizar">

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        <a href="{{ route('veiculos.edit',$veiculo) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar">

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        <form
                                            action="{{ route('veiculos.destroy',$veiculo) }}"
                                            method="POST"
                                            onsubmit="return confirm('Deseja remover este veículo?')">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                    Nenhum veículo encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($veiculos->hasPages())
            <div class="card-footer bg-white">
                {{ $veiculos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection