@extends('layouts.app')

@section('content')

<style>
    .kpi-card {
        border-radius: 8px;
        min-height: 105px;
        transition: .2s;
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
    }

    .kpi-card i {
        font-size: 2.1rem;
        opacity: .6;
    }

    .info-small {
        font-size: .76rem;
        color: #6c757d;
    }

    .documentos a,
    .documentos span {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
    }

    .acao-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .table-romaneios th,
    .table-romaneios td {
        vertical-align: middle;
    }

    .table-romaneios th {
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-2">

    @php
        $colecao = collect($romaneios->items());

        $totalRomaneios = method_exists($romaneios, 'total') ? $romaneios->total() : $romaneios->count();
        $totalGerados = $colecao->where('status', 'Gerado')->count();
        $totalCarregando = $colecao->where('status', 'Carregando')->count();
        $totalCarregados = $colecao->whereIn('status', ['Carregado', 'Finalizado', 'Concluido', 'Concluído'])->count();
    @endphp

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-clipboard-check me-2"></i>Gerenciamento de Romaneios
            </h4>
            <small class="text-muted">
                Painel operacional para separação, conferência, impressão e expedição.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-truck me-1"></i>Entregas
            </a>

            @if(Route::has('romaneios.create'))
                <a href="{{ route('romaneios.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Novo Romaneio
                </a>
            @endif
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

    {{-- KPIS --}}
    <div class="row g-2 mb-3">
        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total</small>
                        <h3>{{ $totalRomaneios }}</h3>
                    </div>
                    <i class="bi bi-clipboard-data text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Gerados</small>
                        <h3>{{ $totalGerados }}</h3>
                    </div>
                    <i class="bi bi-flag text-secondary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-info border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Carregando</small>
                        <h3>{{ $totalCarregando }}</h3>
                    </div>
                    <i class="bi bi-box-seam text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Finalizados</small>
                        <h3>{{ $totalCarregados }}</h3>
                    </div>
                    <i class="bi bi-check2-circle text-success"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white">
            <strong><i class="bi bi-funnel me-2"></i>Filtros de Consulta</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('romaneios.index') }}" class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Buscar</label>
                    <input type="text"
                           name="busca"
                           value="{{ request('busca') }}"
                           class="form-control form-control-sm"
                           placeholder="Cliente, romaneio, venda, orçamento...">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="Gerado" {{ request('status') == 'Gerado' ? 'selected' : '' }}>Gerado</option>
                        <option value="Carregando" {{ request('status') == 'Carregando' ? 'selected' : '' }}>Carregando</option>
                        <option value="Carregado" {{ request('status') == 'Carregado' ? 'selected' : '' }}>Carregado</option>
                        <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Data Inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="{{ request('data_inicio') }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold">Data Final</label>
                    <input type="date"
                           name="data_fim"
                           value="{{ request('data_fim') }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>

                        <a href="{{ route('romaneios.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABELA --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-ul me-2"></i>Painel Operacional de Romaneios</strong>

            <span class="badge bg-light text-dark">
                Total: {{ $totalRomaneios }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table class="table table-hover table-bordered table-sm align-middle mb-0 table-romaneios">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th style="width: 16%;">Romaneio</th>
                            <th style="width: 18%;">Cliente / Documentos</th>
                            <th style="width: 18%;">Destino</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 14%;">Carregamento</th>
                            <th style="width: 10%;">Equipe</th>
                            <th style="width: 8%;">Emissão</th>
                            <th style="width: 6%;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($romaneios as $romaneio)
                            @php
                                $entrega = $romaneio->entrega ?? null;
                                $orcamento = $entrega->orcamento ?? null;
                                $cliente = $orcamento->cliente ?? $entrega->cliente ?? null;
                                $itens = $romaneio->itens ?? collect();

                                $codigoRomaneio = $romaneio->codigo_romaneio
                                    ?? $romaneio->codigo
                                    ?? 'ROM-' . str_pad($romaneio->id, 4, '0', STR_PAD_LEFT);

                                $clienteNome = $cliente->nome
                                    ?? $entrega->cliente_nome
                                    ?? $entrega->nome_cliente
                                    ?? 'Cliente não informado';

                                $status = $romaneio->status ?? 'Gerado';

                                $badgeStatus = match($status) {
                                    'Gerado' => 'secondary',
                                    'Pendente' => 'warning text-dark',
                                    'Separando', 'Em separação' => 'primary',
                                    'Carregando' => 'info text-dark',
                                    'Carregado' => 'success',
                                    'Finalizado', 'Concluido', 'Concluído' => 'success',
                                    'Cancelado' => 'danger',
                                    default => 'secondary',
                                };

                                $totalItens = $itens->count();

                                $itensCarregados = $itens->whereIn('status', [
                                    'Carregado',
                                    'Conferido',
                                    'Finalizado'
                                ])->count();

                                $percentual = (float) ($romaneio->percentual_carregado ?? 0);

                                if ($percentual <= 0 && $totalItens > 0) {
                                    $percentual = ($itensCarregados / $totalItens) * 100;
                                }

                                $destino = $entrega->endereco_entrega
                                    ?? $entrega->endereco_entrega_concatenado
                                    ?? $entrega->bairro
                                    ?? $entrega->cidade
                                    ?? 'Destino não informado';
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $codigoRomaneio }}</div>
                                    <div class="info-small">ID interno: #{{ $romaneio->id }}</div>
                                </td>

                                <td>
                                    <div class="fw-bold mb-1">{{ $clienteNome }}</div>

                                    <div class="documentos">
                                        @if($entrega && !empty($entrega->venda_id))
                                            <a href="{{ url('/venda/' . $entrega->venda_id . '/cupom') }}" target="_self">
                                                <i class="bi bi-receipt me-1"></i>VEN-{{ $entrega->venda_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-receipt me-1"></i>Venda não vinculada
                                            </span>
                                        @endif

                                        @if($entrega)
                                            @if(Route::has('entregas.show'))
                                                <a href="{{ route('entregas.show', $entrega->id) }}">
                                                    <i class="bi bi-truck me-1"></i>ENT-{{ $entrega->id }}
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bi bi-truck me-1"></i>ENT-{{ $entrega->id }}
                                                </span>
                                            @endif
                                        @endif

                                        @if($orcamento)
                                            @if(Route::has('orcamentos.show'))
                                                <a href="{{ route('orcamentos.show', $orcamento->id) }}">
                                                    <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $orcamento->id }}
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $orcamento->id }}
                                                </span>
                                            @endif
                                        @elseif($entrega && !empty($entrega->orcamento_id))
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $entrega->orcamento_id }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $destino }}</div>
                                    @if(!empty($entrega->periodo_entrega))
                                        <div class="info-small">
                                            Período: {{ $entrega->periodo_entrega }}
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-{{ $badgeStatus }}">
                                        {{ $status }}
                                    </span>
                                </td>

                                <td style="min-width: 160px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-semibold">{{ $itensCarregados }}/{{ $totalItens }} itens</small>
                                        <small class="fw-bold">{{ number_format($percentual, 0, ',', '.') }}%</small>
                                    </div>

                                    <div class="progress" style="height: 12px; border-radius: 8px;">
                                        <div class="progress-bar bg-success"
                                             role="progressbar"
                                             style="width: {{ min($percentual, 100) }}%;">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $romaneio->motorista->name ?? $romaneio->motorista->nome ?? 'Motorista —' }}
                                    </div>
                                    <div class="info-small">
                                        Veículo: {{ $romaneio->veiculo->placa ?? '—' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="fw-semibold">
                                        {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y') }}
                                    </div>
                                    <div class="info-small">
                                        {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('H:i') }}
                                    </div>
                                </td>

                                <td class="text-end">

                                    <div class="d-flex justify-content-end gap-1 flex-wrap " >
                                        @if(Route::has('romaneios.show'))
                                            <a href="{{ route('romaneios.show', $romaneio->id) }}"
                                               class="btn btn-outline-primary btn-sm acao-btn"
                                               title="Visualizar romaneio">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif

                                        @if(Route::has('romaneios.imprimir'))
                                            <a href="{{ route('romaneios.imprimir', $romaneio->id) }}"
                                               class="btn btn-outline-dark btn-sm acao-btn"
                                               target="_blank"
                                               title="Imprimir romaneio">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @elseif(Route::has('romaneios.print'))
                                            <a href="{{ route('romaneios.print', $romaneio->id) }}"
                                               class="btn btn-outline-dark btn-sm acao-btn"
                                               target="_blank"
                                               title="Imprimir romaneio">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @endif

                                        @if(Route::has('expedicao.show'))
                                            <a href="{{ route('expedicao.show', $romaneio->id) }}"
                                               class="btn btn-outline-success btn-sm acao-btn"
                                               title="Abrir expedição">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Nenhum romaneio encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($romaneios, 'links'))
            <div class="card-footer bg-white d-flex justify-content-center py-2">
                {{ $romaneios->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection