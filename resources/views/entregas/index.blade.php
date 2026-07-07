@extends('layouts.app')

@section('content')

<style>
    .page-shell {
        width: 100%;
    }

    .kpi-card {
        transition: .2s;
        cursor: pointer;
        border-radius: 8px;
        min-height: 108px;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .75rem rgba(0,0,0,.16) !important;
    }

    .kpi-card .card-body {
        padding: 14px 16px;
    }

    .kpi-card h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .kpi-card small {
        font-size: .72rem;
        margin-bottom: 4px;
        letter-spacing: .03rem;
        text-transform: uppercase;
    }

    .kpi-card .fw-semibold {
        font-size: .92rem;
    }

    .kpi-card i {
        font-size: 2.15rem !important;
        opacity: .6;
    }

    .mini-indicador {
        font-size: .75rem;
    }

    .table-entregas th,
    .table-entregas td {
        vertical-align: middle;
    }

    .table-entregas th {
        white-space: nowrap;
    }

    .entrega-codigo {
        font-size: .95rem;
        font-weight: 700;
    }

    .linha-secundaria {
        font-size: .75rem;
        color: #6c757d;
    }

    .acao-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .col-texto {
        max-width: 220px;
        white-space: normal;
    }

    .documentos a {
        display: block;
        font-size: .78rem;
        text-decoration: none;
        font-weight: 600;
    }

    .documentos span {
        display: block;
        font-size: .78rem;
    }

    .card-header {
        padding-top: .55rem;
        padding-bottom: .55rem;
    }

    .card-body {
        padding: .85rem;
    }

    @media (max-width: 1400px) {
        .kpi-card h3 {
            font-size: 1.65rem;
        }

        .kpi-card i {
            font-size: 1.8rem !important;
        }

        .col-texto {
            max-width: 180px;
        }
    }
</style>

<div class="container-fluid px-2 page-shell">

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-truck me-2"></i>Gerenciamento de Entregas
            </h4>
            <small class="text-muted">
                Controle operacional de separação, carregamento, rota e confirmação das entregas.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('romaneios.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-box-seam me-1"></i>Criar Romaneio
            </a>

            <a href="{{ route('entregas.index') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </a>

            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Exportar
            </button>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- CARDS RESUMO --}}
    <div class="row g-2 mb-3 ">
        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'pendente_pagamento']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Financeiro</small>
                            <span class="fw-semibold d-block mb-1">Pend. Pagto</span>
                            <h3>{{ $resumo['pendente_pagamento'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-cash text-danger"></i>
                         <i class="bi bi-cash-clock-charge text-warning"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'aguardando_separacao']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-warning border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Prioridade</small>
                            <span class="fw-semibold d-block mb-1">Aguard. Sep.</span>
                            <h3>{{ $resumo['aguardando_separacao'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-lightning-charge text-warning"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'separando']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Operação</small>
                            <span class="fw-semibold d-block mb-1">Separando</span>
                            <h3>{{ $resumo['separando'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-box-seam text-primary"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'carregado']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Expedição</small>
                            <span class="fw-semibold d-block mb-1">Carregados</span>
                            <h3>{{ $resumo['carregados'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-truck-front text-info"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'em_rota']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-dark border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Logística</small>
                            <span class="fw-semibold d-block mb-1">Em rota</span>
                            <h3>{{ $resumo['em_rota'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-geo-alt text-dark"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <a href="{{ route('entregas.index', ['status' => 'entregue']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Finalizadas</small>
                            <span class="fw-semibold d-block mb-1">Entregues</span>
                            <h3>{{ $resumo['entregues'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-check2-circle text-success"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white">
            <strong><i class="bi bi-funnel me-2"></i>Filtros de Consulta</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('entregas.index') }}" class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Código da Entrega</label>
                    <input type="text"
                           name="codigo_entrega"
                           class="form-control form-control-sm"
                           value="{{ request('codigo_entrega') }}"
                           placeholder="Ex: ENT-20260629">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="pendente_pagamento" {{ request('status') == 'pendente_pagamento' ? 'selected' : '' }}>Pendente Pagamento</option>
                        <option value="aguardando_separacao" {{ request('status') == 'aguardando_separacao' ? 'selected' : '' }}>Aguardando Separação</option>
                        <option value="separando" {{ request('status') == 'separando' ? 'selected' : '' }}>Separando</option>
                        <option value="carregado" {{ request('status') == 'carregado' ? 'selected' : '' }}>Carregado</option>
                        <option value="em_rota" {{ request('status') == 'em_rota' ? 'selected' : '' }}>Em rota</option>
                        <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Entregue</option>
                        <option value="parcial" {{ request('status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                        <option value="devolvido" {{ request('status') == 'devolvido' ? 'selected' : '' }}>Devolvido</option>
                        <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Data Prevista</label>
                    <input type="date"
                           name="data_prevista"
                           class="form-control form-control-sm"
                           value="{{ request('data_prevista') }}">
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>

                        <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABELA --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-check me-2"></i>Operações de Entrega</strong>

            <div class="d-flex gap-1 flex-wrap">
                <span class="badge bg-light text-dark mini-indicador">Total: {{ $entregas->total() }}</span>
                <span class="badge bg-warning text-dark mini-indicador">Prioridade: {{ $resumo['aguardando_separacao'] ?? 0 }}</span>
                <span class="badge bg-danger mini-indicador">Atrasadas: {{ $resumo['atrasadas'] ?? 0 }}</span>
                <span class="badge bg-success mini-indicador">Entregues: {{ $resumo['entregues'] ?? 0 }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table id="tabela-entregas" class="table table-hover table-bordered table-sm align-middle mb-0 table-entregas">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th style="width: 15%;">Entrega</th>
                            <th style="width: 18%;">Cliente / Contato</th>
                            <th style="width: 12%;">Documentos</th>
                            <th style="width: 12%;">Previsão</th>
                            <th style="width: 9%;">Tipo</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 8%;">Itens</th>
                            <th style="width: 14%;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($entregas as $entrega)
                            @php
                                $statusClasses = [
                                    'pendente_pagamento'   => 'bg-secondary',
                                    'aguardando_separacao' => 'bg-secondary',
                                    'separando'            => 'bg-primary',
                                    'carregado'            => 'bg-info text-dark',
                                    'em_rota'              => 'bg-dark',
                                    'entregue'             => 'bg-success',
                                    'parcial'              => 'bg-warning text-dark',
                                    'devolvido'            => 'bg-danger',
                                    'cancelado'            => 'bg-danger',
                                ];

                                $statusLabels = [
                                    'pendente_pagamento'   => 'Pendente pagamento',
                                    'aguardando_separacao' => 'Aguardando separação',
                                    'separando'            => 'Separando',
                                    'carregado'            => 'Carregado',
                                    'em_rota'              => 'Em rota',
                                    'entregue'             => 'Entregue',
                                    'parcial'              => 'Parcial',
                                    'devolvido'            => 'Devolvido',
                                    'cancelado'            => 'Cancelado',
                                ];

                                $dataPrevista = $entrega->data_prevista
                                    ? \Carbon\Carbon::parse($entrega->data_prevista)->startOfDay()
                                    : null;

                                $hoje = now()->startOfDay();

                                $statusFinalizado = in_array($entrega->status, ['entregue', 'cancelado', 'devolvido']);

                                $atrasada = $dataPrevista && $dataPrevista->lt($hoje) && !$statusFinalizado;
                                $venceHoje = $dataPrevista && $dataPrevista->equalTo($hoje) && !$statusFinalizado;
                                $venceAmanha = $dataPrevista && $dataPrevista->equalTo($hoje->copy()->addDay()) && !$statusFinalizado;

                                $totalItens = $entrega->itens ? $entrega->itens->count() : 0;

                                $itensEntregues = $entrega->itens
                                    ? $entrega->itens->where('status', 'entregue')->count()
                                    : 0;

                                $linhaClasse = match ($entrega->status) {
                                    'pendente_pagamento'   => 'table-light',
                                    'aguardando_separacao' => '',
                                    'separando'            => 'table-primary',
                                    'carregado'            => 'table-info',
                                    'em_rota'              => 'table-dark',
                                    'entregue'             => 'table-success',
                                    'parcial'              => 'table-warning',
                                    'devolvido', 'cancelado' => 'table-danger',
                                    default                => '',
                                };

                                if ($atrasada) {
                                    $linhaClasse = 'table-danger';
                                }

                                $prioridadeLabel = 'NORMAL';
                                $prioridadeClasse = 'bg-success';
                                $prioridadeIcone = 'bi-calendar-check';

                                if ($atrasada) {
                                    $prioridadeLabel = 'ATRASADA';
                                    $prioridadeClasse = 'bg-danger';
                                    $prioridadeIcone = 'bi-alarm';
                                } elseif ($entrega->status === 'pendente_pagamento') {
                                    $prioridadeLabel = 'BAIXA';
                                    $prioridadeClasse = 'bg-secondary';
                                    $prioridadeIcone = 'bi-clock-history';
                                } elseif ($venceHoje) {
                                    $prioridadeLabel = 'HOJE';
                                    $prioridadeClasse = 'bg-warning text-dark';
                                    $prioridadeIcone = 'bi-calendar-event';
                                } elseif ($venceAmanha) {
                                    $prioridadeLabel = 'AMANHÃ';
                                    $prioridadeClasse = 'bg-info text-dark';
                                    $prioridadeIcone = 'bi-calendar2-day';
                                }
                            @endphp

                            <tr class="{{ $linhaClasse }}">
                                <td>
                                    <div class="entrega-codigo">
                                        {{ $entrega->codigo_entrega ?? 'ENT-' . $entrega->id }}
                                    </div>
                                    <div class="linha-secundaria">
                                        ID interno: #{{ $entrega->id }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge {{ $prioridadeClasse }}">
                                            <i class="bi {{ $prioridadeIcone }} me-1"></i>{{ $prioridadeLabel }}
                                        </span>
                                    </div>
                                </td>

                                <td class="col-texto">
                                    <div class="fw-semibold">
                                        {{ $entrega->responsavel_recebimento ?? 'Responsável não informado' }}
                                    </div>
                                    <div class="linha-secundaria">
                                        <i class="bi bi-telephone me-1"></i>{{ $entrega->telefone_recebimento ?? 'Telefone não informado' }}
                                    </div>
                                </td>

                                <td class="documentos text-center">
                                    @if(!empty($entrega->venda_id))
                                        <a href="{{ url('/venda/' . $entrega->venda_id . '/cupom') }}"
                                        target="_self" rel="noopener noreferrer"
                                        class="text-decoration-none fw-semibold">
                                            <i class="bi bi-receipt me-1"></i>VEN-{{ $entrega->venda_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            <i class="bi bi-receipt me-1"></i>Venda —
                                        </span>
                                    @endif

                                    @if(!empty($entrega->orcamento_id))
                                        @if(Route::has('orcamentos.show'))
                                            <a href="{{ route('orcamentos.show', $entrega->orcamento_id) }}">
                                                <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $entrega->orcamento_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $entrega->orcamento_id }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">
                                            <i class="bi bi-file-earmark-text me-1"></i>Orç. —
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="fw-semibold">
                                        {{ $dataPrevista ? $dataPrevista->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="linha-secundaria">
                                        {{ $entrega->periodo_entrega ?? 'Período não informado' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($entrega->tipo_entrega === 'retira_loja')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-shop me-1"></i>Retira
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-truck me-1"></i>Entrega
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $statusClasses[$entrega->status] ?? 'bg-secondary' }}">
                                        {{ $statusLabels[$entrega->status] ?? ucfirst(str_replace('_', ' ', $entrega->status)) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $itensEntregues }}/{{ $totalItens }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <a href="{{ route('entregas.show', $entrega->id) }}"
                                           class="btn btn-outline-primary btn-sm acao-btn"
                                           title="Visualizar entrega">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($entrega->status === 'aguardando_separacao')
                                            <form method="POST" action="{{ route('entregas.separar', $entrega->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-outline-warning btn-sm acao-btn"
                                                        title="Iniciar separação">
                                                    <i class="bi bi-box-seam"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($entrega->status === 'separando')
                                            <form method="POST" action="{{ route('entregas.carregar', $entrega->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-outline-info btn-sm acao-btn"
                                                        title="Marcar como carregada">
                                                    <i class="bi bi-truck-front"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($entrega->status === 'carregado')
                                            <form method="POST" action="{{ route('entregas.rota', $entrega->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-outline-dark btn-sm acao-btn"
                                                        title="Enviar para rota">
                                                    <i class="bi bi-geo-alt"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if(in_array($entrega->status, ['em_rota', 'parcial']))
                                            <form method="POST" action="{{ route('entregas.confirmar', $entrega->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-outline-success btn-sm acao-btn"
                                                        title="Confirmar entrega"
                                                        onclick="return confirm('Confirmar esta entrega como concluída?')">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if(!in_array($entrega->status, ['entregue', 'cancelado', 'devolvido']))
                                            <form method="POST" action="{{ route('entregas.cancelar', $entrega->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="motivo" value="Cancelada pelo painel de entregas.">

                                                <button type="submit"
                                                        class="btn btn-outline-danger btn-sm acao-btn"
                                                        title="Cancelar entrega"
                                                        onclick="return confirm('Deseja realmente cancelar esta entrega?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($entrega->status === 'entregue')
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm acao-btn"
                                                    title="Impressão disponível em fase futura"
                                                    disabled>
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    Nenhuma entrega encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($entregas->hasPages())
            <div class="card-footer d-flex justify-content-center py-2">
                {{ $entregas->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>

@endsection