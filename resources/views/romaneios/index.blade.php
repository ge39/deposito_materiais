@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    @php
        $colecao = collect($romaneios->items());

        $totalRomaneios = method_exists($romaneios, 'total') ? $romaneios->total() : $romaneios->count();

        $totalGerados = $colecao->where('status', 'Gerado')->count();
        $totalCarregando = $colecao->where('status', 'Carregando')->count();
        $totalCarregados = $colecao->whereIn('status', ['Carregado', 'Finalizado', 'Concluido', 'Concluído'])->count();
        $totalCancelados = $colecao->where('status', 'Cancelado')->count();
    @endphp

    {{-- Cabeçalho operacional --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clipboard-check me-2"></i>Gerenciamento de Romaneios
            </h3>
            <small class="text-muted">
                Painel operacional para separação, conferência, impressão e expedição dos romaneios.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-truck me-1"></i> Entregas
            </a>

            @if(Route::has('romaneios.create'))
                <a href="{{ route('romaneios.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Novo Romaneio
                </a>
            @endif
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Romaneios</small>
                        <h3 class="fw-bold mb-0">{{ $totalRomaneios }}</h3>
                    </div>
                    <i class="bi bi-clipboard-data fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Gerados</small>
                        <h3 class="fw-bold mb-0">{{ $totalGerados }}</h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Carregando</small>
                        <h3 class="fw-bold mb-0">{{ $totalCarregando }}</h3>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Finalizados</small>
                        <h3 class="fw-bold mb-0">{{ $totalCarregados }}</h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-funnel me-2"></i>
            <strong>Filtros de Consulta</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('romaneios.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Buscar</label>
                    <input type="text"
                           name="busca"
                           value="{{ request('busca') }}"
                           class="form-control"
                           placeholder="Cliente, romaneio, orçamento...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Gerado" {{ request('status') == 'Gerado' ? 'selected' : '' }}>Gerado</option>
                        <option value="Carregando" {{ request('status') == 'Carregando' ? 'selected' : '' }}>Carregando</option>
                        <option value="Carregado" {{ request('status') == 'Carregado' ? 'selected' : '' }}>Carregado</option>
                        <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Data Inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="{{ request('data_inicio') }}"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Data Final</label>
                    <input type="date"
                           name="data_fim"
                           value="{{ request('data_fim') }}"
                           class="form-control">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>

                    <a href="{{ route('romaneios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela operacional --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-ul me-2"></i>
                <strong>Painel Operacional de Romaneios</strong>
            </div>

            <span class="badge bg-light text-dark">
                {{ $totalRomaneios }} registro(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Romaneio</th>
                            <th>Cliente / Documentos</th>
                            <th>Destino</th>
                            <th>Status</th>
                            <th>Carregamento</th>
                            <th>Emissão</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($romaneios as $romaneio)
                            @php
                                $entrega = $romaneio->entrega ?? null;
                                $orcamento = $entrega->orcamento ?? null;
                                $cliente = $orcamento->cliente ?? $entrega->cliente ?? null;
                                $itens = $romaneio->itens ?? collect();

                                $codigoRomaneio = $romaneio->codigo_romaneio ?? $romaneio->codigo ?? 'ROM-' . str_pad($romaneio->id, 4, '0', STR_PAD_LEFT);

                                $clienteNome = $cliente->nome
                                    ?? $entrega->cliente_nome
                                    ?? $entrega->nome_cliente
                                    ?? 'Cliente não informado';

                                $status = $romaneio->status ?? 'Gerado';

                                $badgeStatus = match($status) {
                                    'Gerado' => 'secondary',
                                    'Pendente' => 'warning',
                                    'Separando', 'Em separação' => 'info',
                                    'Carregando' => 'primary',
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

                                $destino = $entrega->bairro
                                    ?? $entrega->cidade
                                    ?? $entrega->endereco_entrega
                                    ?? $entrega->endereco_entrega_concatenado
                                    ?? 'Destino não informado';
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ $codigoRomaneio }}
                                    </div>
                                    <small class="text-muted">
                                        ID interno: #{{ $romaneio->id }}
                                    </small>
                                </td>

                               <td>
                                    <div class="fw-bold mb-1">
                                        {{ $clienteNome }}
                                    </div>

                                    <div class="d-flex flex-column gap-1">

                                        @if($entrega && !empty($entrega->venda_id))
                                            <a href="{{ url('/venda/' . $entrega->venda_id . '/cupom') }}"
                                            target="_self"
                                            class="text-decoration-none small fw-semibold">
                                                <i class="bi bi-receipt me-1"></i> VEN-{{ $entrega->venda_id }}
                                            </a>
                                        @else
                                            <span class="small text-muted">
                                                <i class="bi bi-receipt me-1"></i> Venda não vinculada
                                            </span>
                                        @endif

                                        @if($entrega)
                                            @if(Route::has('entregas.show'))
                                                <a href="{{ route('entregas.show', $entrega->id) }}"
                                                class="text-decoration-none small fw-semibold">
                                                    <i class="bi bi-truck me-1"></i> ENT-{{ $entrega->id }}
                                                </a>
                                            @else
                                                <span class="small text-muted">
                                                    <i class="bi bi-truck me-1"></i> ENT-{{ $entrega->id }}
                                                </span>
                                            @endif
                                        @endif

                                        @if($orcamento)
                                            @if(Route::has('orcamentos.show'))
                                                <a href="{{ route('orcamentos.show', $orcamento->id) }}"
                                                class="text-decoration-none small fw-semibold">
                                                    <i class="bi bi-file-earmark-text me-1"></i> ORÇ-{{ $orcamento->id }}
                                                </a>
                                            @else
                                                <span class="small text-muted">
                                                    <i class="bi bi-file-earmark-text me-1"></i> ORÇ-{{ $orcamento->id }}
                                                </span>
                                            @endif
                                        @elseif($entrega && !empty($entrega->orcamento_id))
                                            <span class="small text-muted">
                                                <i class="bi bi-file-earmark-text me-1"></i> ORÇ-{{ $entrega->orcamento_id }}
                                            </span>
                                        @endif

                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $destino }}
                                    </div>

                                    @if(!empty($entrega->periodo_entrega))
                                        <small class="text-muted">
                                            Período: {{ $entrega->periodo_entrega }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-{{ $badgeStatus }} px-3 py-2">
                                        {{ $status }}
                                    </span>
                                </td>

                                <td style="min-width: 170px;">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-semibold">
                                            {{ $itensCarregados }} / {{ $totalItens }} itens
                                        </small>
                                        <small class="fw-bold">
                                            {{ number_format($percentual, 0, ',', '.') }}%
                                        </small>
                                    </div>

                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success"
                                             role="progressbar"
                                             style="width: {{ min($percentual, 100) }}%;">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('H:i') }}
                                    </small>
                                </td>

                                <td class="text-end">
    <div class="btn-group btn-group-sm">

        @if(Route::has('romaneios.show'))
            <a href="{{ route('romaneios.show', $romaneio->id) }}"
               class="btn btn-outline-primary"
               title="Visualizar romaneio">
                <i class="bi bi-eye"></i>
            </a>
        @endif

        @if(Route::has('romaneios.imprimir'))
            <a href="{{ route('romaneios.imprimir', $romaneio->id) }}"
               class="btn btn-outline-dark"
               target="_blank"
               title="Imprimir romaneio">
                <i class="bi bi-printer"></i>
            </a>
        @elseif(Route::has('romaneios.print'))
            <a href="{{ route('romaneios.print', $romaneio->id) }}"
               class="btn btn-outline-dark"
               target="_blank"
               title="Imprimir romaneio">
                <i class="bi bi-printer"></i>
            </a>
        @endif

        @if(Route::has('expedicao.show'))
            <a href="{{ route('expedicao.show', $romaneio->id) }}"
               class="btn btn-outline-success"
               title="Abrir expedição">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        @endif

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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
            <div class="card-footer bg-white">
                {{ $romaneios->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection