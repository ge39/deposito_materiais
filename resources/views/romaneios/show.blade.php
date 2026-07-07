@extends('layouts.app')

@section('content')

<style>
    .kpi-card {
        border-radius: 8px;
        min-height: 105px;
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

    .info-label {
        font-size: .75rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .info-value {
        font-weight: 600;
    }

    .acao-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .table-romaneio th,
    .table-romaneio td {
        vertical-align: middle;
    }

    .linha-secundaria {
        font-size: .75rem;
        color: #6c757d;
    }
</style>

@php
    $entrega = $romaneio->entrega ?? null;
    $orcamento = $entrega->orcamento ?? null;
    $venda = $entrega->venda ?? null;
    $cliente = $entrega->cliente ?? $orcamento->cliente ?? null;
    $itens = $romaneio->itens ?? collect();

    $codigoRomaneio = $romaneio->codigo_romaneio ?? 'ROM-' . $romaneio->id;

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

    $status = $romaneio->status ?? 'Gerado';

    $statusClasses = [
        'Gerado' => 'bg-secondary',
        'Pendente' => 'bg-warning text-dark',
        'Separando' => 'bg-primary',
        'Em separação' => 'bg-primary',
        'Carregando' => 'bg-info text-dark',
        'Carregado' => 'bg-success',
        'Finalizado' => 'bg-success',
        'Concluido' => 'bg-success',
        'Concluído' => 'bg-success',
        'Cancelado' => 'bg-danger',
    ];

    $badgeStatus = $statusClasses[$status] ?? 'bg-secondary';
@endphp

<div class="container-fluid px-2">

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-clipboard-check me-2"></i>Romaneio {{ $codigoRomaneio }}
            </h4>
            <small class="text-muted">
                Painel operacional de separação, conferência, carregamento e expedição.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('romaneios.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>

            @if(Route::has('romaneios.imprimir'))
                <a href="{{ route('romaneios.imprimir', $romaneio->id) }}"
                   target="_blank"
                   class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </a>
            @endif

            @if(Route::has('expedicao.show'))
                <a href="{{ route('expedicao.show', $romaneio->id) }}"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Expedição
                </a>
            @endif
        </div>
    </div>

    {{-- KPIS --}}
    <div class="row g-2 mb-3">
        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-secondary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Status</small>
                        <div class="mt-1">
                            <span class="badge {{ $badgeStatus }} px-3 py-2">
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                    <i class="bi bi-flag text-secondary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-primary border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Itens</small>
                        <h3>{{ $totalItens }}</h3>
                    </div>
                    <i class="bi bi-box-seam text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-success border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Carregados</small>
                        <h3>{{ $itensCarregados }}</h3>
                    </div>
                    <i class="bi bi-check2-circle text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card shadow-sm border-start border-warning border-4 h-100 kpi-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Progresso</small>
                        <h3>{{ number_format($percentual, 0, ',', '.') }}%</h3>
                    </div>
                    <i class="bi bi-graph-up-arrow text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- DADOS OPERACIONAIS --}}
    <div class="row g-2 mb-3">

        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-info-circle me-2"></i>Dados Operacionais</strong>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-label">Romaneio</div>
                            <div class="info-value">{{ $codigoRomaneio }}</div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Entrega</div>
                            <div class="info-value">
                                {{ $entrega ? 'ENT-' . $entrega->id : 'Não vinculada' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Emissão</div>
                            <div class="info-value">
                                {{ optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Motorista</div>
                            <div class="info-value">
                                {{ $romaneio->motorista->name ?? $romaneio->motorista->nome ?? 'Não definido' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Veículo</div>
                            <div class="info-value">
                                {{ $romaneio->veiculo->placa ?? 'Não definido' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-label">Última atualização</div>
                            <div class="info-value">
                                {{ optional($romaneio->updated_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-semibold text-muted">Percentual carregado</small>
                            <small class="fw-bold">{{ number_format($percentual, 2, ',', '.') }}%</small>
                        </div>

                        <div class="progress" style="height: 16px;">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: {{ min($percentual, 100) }}%;">
                                {{ number_format($percentual, 0, ',', '.') }}%
                            </div>
                        </div>
                    </div>

                    @if(!empty($romaneio->observacao))
                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>Observação:</strong> {{ $romaneio->observacao }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- DOCUMENTOS --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <strong><i class="bi bi-files me-2"></i>Documentos Vinculados</strong>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Venda</div>
                        <div class="info-value">
                            @if($entrega && !empty($entrega->venda_id))
                                <a href="{{ url('/venda/' . $entrega->venda_id . '/cupom') }}"
                                   target="_blank"
                                   class="text-decoration-none fw-semibold">
                                    <i class="bi bi-receipt me-1"></i>VEN-{{ $entrega->venda_id }}
                                </a>
                            @else
                                <span class="text-muted">Não vinculada</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="info-label">Orçamento</div>
                        <div class="info-value">
                            @if($orcamento && Route::has('orcamentos.show'))
                                <a href="{{ route('orcamentos.show', $orcamento->id) }}"
                                   class="text-decoration-none fw-semibold">
                                    <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $orcamento->id }}
                                </a>
                            @elseif($entrega && !empty($entrega->orcamento_id))
                                ORÇ-{{ $entrega->orcamento_id }}
                            @else
                                <span class="text-muted">Não vinculado</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="info-label">Entrega</div>
                        <div class="info-value">
                            @if($entrega && Route::has('entregas.show'))
                                <a href="{{ route('entregas.show', $entrega->id) }}"
                                   class="text-decoration-none fw-semibold">
                                    <i class="bi bi-truck me-1"></i>ENT-{{ $entrega->id }}
                                </a>
                            @elseif($entrega)
                                ENT-{{ $entrega->id }}
                            @else
                                <span class="text-muted">Não vinculada</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CLIENTE / DESTINO --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-secondary text-white">
            <strong><i class="bi bi-person-vcard me-2"></i>Cliente e Destino</strong>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="info-label">Cliente</div>
                    <div class="info-value">
                        {{ $cliente->nome ?? 'Cliente não informado' }}
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="info-label">Responsável</div>
                    <div class="info-value">
                        {{ $entrega->responsavel_recebimento ?? 'Não informado' }}
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="info-label">Telefone</div>
                    <div class="info-value">
                        {{ $entrega->telefone_recebimento ?? 'Não informado' }}
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="info-label">Previsão</div>
                    <div class="info-value">
                        {{ !empty($entrega->data_prevista) ? \Carbon\Carbon::parse($entrega->data_prevista)->format('d/m/Y') : 'Não informada' }}
                        @if(!empty($entrega->periodo_entrega))
                            <span class="linha-secundaria d-block">{{ $entrega->periodo_entrega }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="info-label">Endereço</div>
                    <div class="info-value">
                        {{ $entrega->endereco_entrega ?? $entrega->endereco_entrega_concatenado ?? 'Endereço não informado' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ITENS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-check me-2"></i>Itens do Romaneio</strong>

            <div class="d-flex gap-1">
                <span class="badge bg-light text-dark">Itens: {{ $totalItens }}</span>
                <span class="badge bg-success">Carregados: {{ $itensCarregados }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table class="table table-hover table-bordered table-sm align-middle mb-0 table-romaneio">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Produto</th>
                            <th style="width: 16%;">Localização</th>
                            <th style="width: 13%;">Qtd. Prevista</th>
                            <th style="width: 13%;">Qtd. Carregada</th>
                            <th style="width: 13%;">Status</th>
                            <th style="width: 18%;">Observação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($itens as $index => $item)
                            @php
                                $entregaItem = $item->entregaItem ?? null;

                                $produto = $entregaItem->produto
                                    ?? $entregaItem->vendaItem->produto
                                    ?? $entregaItem->itemOrcamento->produto
                                    ?? null;

                                $statusItem = $item->status ?? 'Pendente';

                                $statusItemClasses = [
                                    'Pendente' => 'bg-warning text-dark',
                                    'Carregando' => 'bg-info text-dark',
                                    'Carregado' => 'bg-success',
                                    'Conferido' => 'bg-success',
                                    'Parcial' => 'bg-primary',
                                    'Devolvido' => 'bg-secondary',
                                    'Cancelado' => 'bg-danger',
                                ];

                                $badgeItem = $statusItemClasses[$statusItem] ?? 'bg-secondary';
                            @endphp

                            <tr>
                                <td class="text-center fw-semibold">{{ $index + 1 }}</td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $produto->nome ?? 'Produto não identificado' }}
                                    </div>
                                    <div class="linha-secundaria">
                                        Código: {{ $produto->id ?? '—' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    {{ $produto->localizacao_estoque ?? '—' }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format((float) ($item->quantidade_prevista ?? 0), 2, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format((float) ($item->quantidade_carregada ?? 0), 2, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $badgeItem }}">
                                        {{ $statusItem }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->observacao ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    Nenhum item encontrado para este romaneio.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection