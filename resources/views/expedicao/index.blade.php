@extends('layouts.app')

@section('content')

<div class="container-fluid px-2">

    {{-- CABEÇALHO OPERACIONAL --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-truck-front me-2"></i>
                Painel da Expedição
            </h4>
            <small class="text-muted">
                Visão operacional de separação, conferência, carregamento, doca e liberação dos veículos.
            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="{{ route('entregas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Entregas
            </a>

            <a href="{{ route('romaneios.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-box-seam me-1"></i>
                Separar Itens
            </a>

            <button type="button" onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Atualizar
            </button>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPIS --}}
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">AGUARDANDO SEPARAÇÃO</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['entregas_disponiveis'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-box-seam fs-3 text-primary opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-secondary h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">ROMANEIOS GERADOS</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['romaneios_abertos'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-clipboard-check fs-3 text-secondary opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">EM SEPARAÇÃO</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['romaneios_em_separacao'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-hourglass-split fs-3 text-warning opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-info h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">SEPARADOS</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['romaneios_separados'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-check2-square fs-3 text-info opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-dark h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">CARREGANDO</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['romaneios_carregando'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-truck fs-3 text-dark opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-4 border-success h-100">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">EM ROTA</small>
                        <h4 class="mb-0 fw-bold">{{ $kpis['romaneios_em_rota'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-geo-alt fs-3 text-success opacity-75"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- FILTROS --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
            <strong class="small text-muted">
                <i class="bi bi-funnel me-1"></i>
                Filtros da Expedição
            </strong>

            <span class="badge bg-secondary">
                {{ $romaneios->count() ?? 0 }} romaneio(s) no período
            </span>
        </div>

        <div class="card-body py-2">
            <form method="GET" action="{{ route('expedicao.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-1 small fw-semibold">Data inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="{{ $dataInicio ?? now()->subDays(15)->toDateString() }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                    <label class="form-label mb-1 small fw-semibold">Data final</label>
                    <input type="date"
                           name="data_fim"
                           value="{{ $dataFim ?? now()->toDateString() }}"
                           class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1 small fw-semibold">Status do romaneio</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>

                        @foreach([
                            'Gerado' => 'Gerado',
                            'Em_separacao' => 'Em separação',
                            'Separado' => 'Separado',
                            'Na_doca' => 'Na doca',
                            'Carregando' => 'Carregando',
                            'Carregado' => 'Carregado',
                            'Saiu_para_entrega' => 'Saiu para entrega',
                            'Entregue' => 'Entregue',
                            'Parcial' => 'Parcial',
                            'Devolvido' => 'Devolvido',
                            'Cancelado' => 'Cancelado',
                        ] as $statusValor => $statusTexto)
                            <option value="{{ $statusValor }}" @selected(($status ?? '') === $statusValor)>
                                {{ $statusTexto }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-1">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>
                        Filtrar
                    </button>

                    <a href="{{ route('expedicao.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">

        {{-- ROMANEIOS OPERACIONAIS --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-clipboard-check me-2"></i>
                        Romaneios em Operação
                    </strong>

                    <small>{{ $romaneios->count() }} registro(s)</small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Romaneio</th>
                                    <th>Cliente / Entrega</th>
                                    <th>Motorista</th>
                                    <th>Itens</th>
                                    <th>Status</th>
                                    <th>Progresso</th>
                                    <th>Veículo</th>
                                    <th style="width: 150px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($romaneios as $romaneio)
                                    @php
                                        $totalItens = $romaneio->itens->count() ?? 0;

                                        $itensConcluidos = $romaneio->itens
                                            ->whereIn('status', ['Carregado', 'Parcial'])
                                            ->count();

                                        $percentual = $romaneio->percentual_carregado ?? (
                                            $totalItens > 0
                                                ? round(($itensConcluidos / $totalItens) * 100, 2)
                                                : 0
                                        );

                                        $entregasIds = $romaneio->itens
                                            ->pluck('entregaItem.entrega.id')
                                            ->filter()
                                            ->unique();

                                        $totalEntregas = $entregasIds->count();

                                        $clienteNome =
                                            $romaneio->entrega->cliente->nome
                                            ?? $romaneio->entrega->Orcamento->cliente->nome
                                            ?? 'Cliente não informado';

                                        $codigoEntrega = $romaneio->entrega->codigo
                                            ?? '#' . ($romaneio->entrega->id ?? '-');

                                        $codigoRomaneio = $romaneio->codigo_romaneio
                                            ?? $romaneio->codigo
                                            ?? '#' . $romaneio->id;

                                        $statusClass = match($romaneio->status) {
                                            'Gerado' => 'bg-primary',
                                            'Em_separacao' => 'bg-warning text-dark',
                                            'Separado' => 'bg-info text-dark',
                                            'Na_doca' => 'bg-secondary',
                                            'Carregando' => 'bg-warning text-dark',
                                            'Carregado' => 'bg-success',
                                            'Saiu_para_entrega' => 'bg-dark',
                                            'Entregue' => 'bg-success',
                                            'Parcial' => 'bg-warning text-dark',
                                            'Devolvido' => 'bg-danger',
                                            'Cancelado' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };

                                        $statusLabel = match($romaneio->status) {
                                            'Gerado' => 'Gerado',
                                            'Em_separacao' => 'Em separação',
                                            'Separado' => 'Separado',
                                            'Na_doca' => 'Na doca',
                                            'Carregando' => 'Carregando',
                                            'Carregado' => 'Carregado',
                                            'Saiu_para_entrega' => 'Saiu para entrega',
                                            'Entregue' => 'Entregue',
                                            'Parcial' => 'Parcial',
                                            'Devolvido' => 'Devolvido',
                                            'Cancelado' => 'Cancelado',
                                            default => $romaneio->status,
                                        };
                                    @endphp

                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $codigoRomaneio }}
                                            <br>

                                            <small class="text-muted">
                                                Emitido:
                                                {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y H:i') }}
                                            </small>

                                            @if(!empty($romaneio->token_abertura) && !empty($romaneio->token_fechamento))
                                                <br>
                                                <span class="badge bg-light text-dark border mt-1">
                                                    Tokens OK
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="fw-semibold">
                                                {{ $clienteNome }}
                                            </div>

                                            <small class="text-muted">
                                                Entrega: {{ $codigoEntrega }}
                                            </small>

                                            <br>

                                            <small class="text-muted">
                                                {{ $totalEntregas }} entrega(s) vinculada(s)
                                            </small>
                                        </td>

                                        <td>
                                            {{ $romaneio->motorista->nome ?? 'Não informado' }}
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                {{ $totalItens }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <small>{{ number_format($percentual, 2, ',', '.') }}%</small>
                                            </div>

                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" style="width: {{ $percentual }}%;"></div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            {{ $romaneio->veiculo->placa ?? $romaneio->veiculo->descricao ?? '-' }}
                                        </td>
                                       
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('expedicao.atribuir-equipe', $romaneio->id) }}"
                                                    class="btn btn-primary"
                                                    title="Atribuir equipe">
                                                        <i class="bi bi-truck"></i>
                                                </a>
                                            </div>

                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('expedicao.show', $romaneio->id) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <a href="{{ route('romaneios.imprimir', $romaneio->id) }}"
                                                   target="_blank"
                                                   class="btn btn-outline-dark"
                                                   title="Imprimir romaneio">
                                                    <i class="bi bi-printer"></i>
                                                </a>

                                                <a href="{{ route('expedicao.operacao', $romaneio->id) }}"
                                                   class="btn btn-primary"
                                                   title="Operar romaneio">
                                                    <i class="bi bi-box-arrow-in-right"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum romaneio encontrado para o período selecionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ENTREGAS AGUARDANDO SEPARAÇÃO --}}
        <div class="col-md-4">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-box-seam me-2"></i>
                        Entregas Aguardando Separação
                    </strong>

                    <span class="badge bg-light text-dark">
                        {{ $entregasDisponiveis->count() ?? 0 }}
                    </span>
                </div>

                <div class="card-body">
                    @forelse($entregasDisponiveis as $entrega)
                        @php
                            $clienteEntrega =
                            $entrega->orcamento->cliente->nome
                            ?? $entrega->orcamento->cliente->razao_social
                            ?? 'Cliente não informado';

                            $codigoEntrega = $entrega->codigo ?? '#' . $entrega->id;

                           $codigoOrcamento =
                            $entrega->orcamento->codigo_orcamento
                            ?? $entrega->orcamento->codigo
                            ?? $entrega->orcamento->numero
                            ?? '-';

                            $dataPrevista = !empty($entrega->data_prevista)
                            ? \Carbon\Carbon::parse($entrega->data_prevista_entrega)->format('d/m/Y')
                            : '-';

                        $periodo = $entrega->periodo_entrega ?? '-';
                        @endphp

                        <div class="border rounded p-2 mb-2 bg-light">
    @php
        $badgeData = 'bg-secondary';
        $textoData = $dataPrevista;

        if (!empty($entrega->data_prevista_entrega)) {
            $hoje = now()->startOfDay();
            $prevista = \Carbon\Carbon::parse($entrega->data_prevista_entrega)->startOfDay();

            if ($prevista->lt($hoje)) {
                $badgeData = 'bg-danger';
                $textoData = 'ATRASADA';
            } elseif ($prevista->equalTo($hoje)) {
                $badgeData = 'bg-success';
                $textoData = 'HOJE';
            } elseif ($prevista->equalTo($hoje->copy()->addDay())) {
                $badgeData = 'bg-warning text-dark';
                $textoData = 'AMANHÃ';
            }
        }

        $badgePeriodo = match(strtolower($periodo)) {
            'manha', 'manhã' => 'bg-warning text-dark',
            'tarde' => 'bg-info text-dark',
            'noite' => 'bg-dark',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            <div class="fw-semibold">
                {{ $codigoEntrega }} - {{ $clienteEntrega }}
            </div>

            <small class="text-muted d-block">
                Orçamento: {{ $codigoOrcamento }}
            </small>

            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                <span class="badge {{ $badgeData }}">
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ $textoData }}
                </span>

                <span class="badge {{ $badgePeriodo }}">
                    <i class="bi bi-clock me-1"></i>
                    {{ ucfirst($periodo) }}
                </span>
            </div>

            <small class="text-primary d-block mt-1">
                <i class="bi bi-calendar-check me-1"></i>
                Data Entrega:
                <strong>{{ $dataPrevista }}</strong>
            </small>
        </div>

        <span class="badge bg-success">
            {{ str_replace('_', ' ', $entrega->status) }}
        </span>
    </div>

    <small class="text-muted d-block mt-1">
        <i class="bi bi-geo-alt me-1"></i>
        {{ $entrega->endereco_entrega ?? $entrega->endereco_entrega_concatenado ?? 'Endereço não informado' }}
    </small>

    <div class="d-flex justify-content-end align-items-end mt-2">
        <!-- <small class="text-muted">
            Itens: {{ $entrega->itens->count() }}
        </small> -->

        <a href="{{ route('romaneios.create', ['entrega_id' => $entrega->id]) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-box-seam me-1"></i>
            Separar
        </a>
    </div>
</div>
                    @empty
                        <div class="text-muted text-center py-3">
                            <i class="bi bi-check2-circle fs-4 d-block mb-2"></i>
                            Nenhuma entrega aguardando separação.
                        </div>
                    @endforelse

                    <a href="{{ route('romaneios.create') }}" class="btn btn-success btn-sm w-100 mt-2">
                        <i class="bi bi-plus-circle me-1"></i>
                        Separar Itens
                    </a>
                </div>
            </div>

            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i>
                A expedição inicia pela separação dos itens da entrega. O romaneio é gerado a partir dos itens selecionados e depois segue para conferência, doca e carregamento.
            </div>
        </div>

    </div>

</div>

@endsection