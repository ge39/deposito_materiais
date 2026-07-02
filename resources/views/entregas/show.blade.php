@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-truck me-2"></i>Central da Entrega
                <span class="text-muted">#{{ $entrega->codigo_entrega ?? $entrega->id }}</span>
            </h4>
            <small class="text-muted">
                Acompanhamento operacional da entrega, itens, responsável e status logístico.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('entregas.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $statusClasses = [
            'pendente'  => 'bg-warning text-dark',
            'separando' => 'bg-primary',
            'carregado' => 'bg-info text-dark',
            'em_rota'   => 'bg-dark',
            'entregue'  => 'bg-success',
            'parcial'   => 'bg-secondary',
            'devolvido' => 'bg-danger',
            'cancelado' => 'bg-danger',
        ];

        $statusLabels = [
            'pendente'  => 'Pendente',
            'separando' => 'Separando',
            'carregado' => 'Carregado',
            'em_rota'   => 'Em rota',
            'entregue'  => 'Entregue',
            'parcial'   => 'Parcial',
            'devolvido' => 'Devolvido',
            'cancelado' => 'Cancelado',
        ];

        $statusAtual = $entrega->status;
    @endphp

    {{-- RESUMO OPERACIONAL --}}
    <div class="row mb-3">

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Status Atual</small>
                    <h5 class="mb-0">
                        <span class="badge {{ $statusClasses[$statusAtual] ?? 'bg-secondary' }}">
                            {{ $statusLabels[$statusAtual] ?? ucfirst($statusAtual) }}
                        </span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-dark border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Data Prevista</small>
                    <h5 class="mb-0">
                        {{ $entrega->data_prevista ? $entrega->data_prevista->format('d/m/Y') : '-' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-success border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Data Realizada</small>
                    <h5 class="mb-0">
                        {{ $entrega->data_realizada ? $entrega->data_realizada->format('d/m/Y') : '-' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-info border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Itens</small>
                    <h5 class="mb-0">
                        {{ $entrega->itens->count() }}
                    </h5>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        {{-- DADOS PRINCIPAIS --}}
        <div class="col-md-8">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-info-circle me-2"></i>Dados da Entrega
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-3">
                            <small class="text-muted">Código</small>
                            <div class="fw-bold">{{ $entrega->codigo_entrega ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Venda</small>
                            <div>{{ $entrega->venda_id ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Orçamento</small>
                            <div>{{ $entrega->orcamento_id ?? '-' }}</div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Tipo</small>
                            <div>
                                @if($entrega->tipo_entrega === 'retira_loja')
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-shop me-1"></i>Retira loja
                                    </span>
                                @else
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-truck me-1"></i>Entrega
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Responsável pelo recebimento</small>
                            <div class="fw-bold">{{ $entrega->responsavel_recebimento ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Telefone</small>
                            <div>{{ $entrega->telefone_recebimento ?? '-' }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ENDEREÇO --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-geo-alt me-2"></i>Endereço de Entrega
                    </strong>
                </div>

                <div class="card-body">
                    <p class="mb-1">
                        {{ $entrega->endereco_entrega ?? 'Endereço não informado.' }}
                    </p>

                    <small class="text-muted">
                        Usa endereço do cliente:
                        <strong>{{ $entrega->usar_endereco_cliente ? 'Sim' : 'Não' }}</strong>
                    </small>
                </div>
            </div>

            {{-- ITENS --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-box-seam me-2"></i>Itens da Entrega
                    </strong>

                    <span class="badge bg-light text-dark">
                        Total: {{ $entrega->itens->count() }}
                    </span>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered table-hover table-sm align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Item</th>
                                <th>Venda Item</th>
                                <th>Previsto</th>
                                <th>Entregue</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th>Observação</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($entrega->itens as $item)
                                <tr>
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center">{{ $item->venda_item_id }}</td>
                                    <td class="text-center">{{ number_format($item->quantidade_prevista, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ number_format($item->quantidade_entregue, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ number_format($item->saldo, 2, ',', '.') }}</td>

                                    <td class="text-center">
                                        <span class="badge {{ $statusClasses[$item->status] ?? 'bg-secondary' }}">
                                            {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                        </span>
                                    </td>

                                    <td>{{ $item->observacao ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Nenhum item vinculado a esta entrega.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

        {{-- PAINEL OPERACIONAL --}}
        <div class="col-md-4">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-lightning-charge me-2"></i>Ações Operacionais
                    </strong>
                </div>

                <div class="card-body d-grid gap-2">

                    <form method="POST" action="{{ route('entregas.status', $entrega->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="separando">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-box-seam me-1"></i>Iniciar Separação
                        </button>
                    </form>

                    <form method="POST" action="{{ route('entregas.status', $entrega->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="carregado">
                        <button type="submit" class="btn btn-info btn-sm w-100">
                            <i class="bi bi-truck-flatbed me-1"></i>Marcar como Carregado
                        </button>
                    </form>

                    <form method="POST" action="{{ route('entregas.status', $entrega->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="em_rota">
                        <button type="submit" class="btn btn-dark btn-sm w-100">
                            <i class="bi bi-signpost-split me-1"></i>Saiu para Entrega
                        </button>
                    </form>

                    <form method="POST" action="{{ route('entregas.confirmar', $entrega->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-check2-circle me-1"></i>Confirmar Entrega Total
                        </button>
                    </form>

                    <button type="button"
                            class="btn btn-secondary btn-sm w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEntregaParcial">
                        <i class="bi bi-sliders me-1"></i>Registrar Entrega Parcial
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-sm w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCancelarEntrega">
                        <i class="bi bi-x-octagon me-1"></i>Cancelar Entrega
                    </button>

                </div>
            </div>

            {{-- TIMELINE --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-clock-history me-2"></i>Fluxo da Entrega
                    </strong>
                </div>

                <div class="card-body">

                    @php
                        $fluxo = [
                            'pendente'  => 'Entrega criada',
                            'separando' => 'Separação iniciada',
                            'carregado' => 'Carga preparada',
                            'em_rota'   => 'Saiu para entrega',
                            'entregue'  => 'Entrega concluída',
                        ];

                        $ordem = array_keys($fluxo);
                        $indiceAtual = array_search($statusAtual, $ordem);
                    @endphp

                    @foreach($fluxo as $status => $label)
                        @php
                            $indice = array_search($status, $ordem);
                            $feito = $indiceAtual !== false && $indice <= $indiceAtual;
                        @endphp

                        <div class="d-flex align-items-start mb-2">
                            <div class="me-2">
                                @if($feito)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>

                            <div>
                                <div class="{{ $feito ? 'fw-bold' : 'text-muted' }}">
                                    {{ $label }}
                                </div>
                                <small class="text-muted">{{ $statusLabels[$status] ?? $status }}</small>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>

    </div>

</div>

{{-- MODAL ENTREGA PARCIAL --}}
<div class="modal fade" id="modalEntregaParcial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('entregas.confirmar-parcial', $entrega->id) }}" class="modal-content">
            @csrf
            @method('PATCH')

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-sliders me-2"></i>Registrar Entrega Parcial
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Item</th>
                            <th>Previsto</th>
                            <th>Já Entregue</th>
                            <th>Quantidade Entregue Agora</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($entrega->itens as $index => $item)
                            <tr>
                                <td class="text-center">
                                    #{{ $item->id }}
                                    <input type="hidden"
                                           name="itens[{{ $index }}][entrega_item_id]"
                                           value="{{ $item->id }}">
                                </td>

                                <td class="text-center">
                                    {{ number_format($item->quantidade_prevista, 2, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($item->quantidade_entregue, 2, ',', '.') }}
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="{{ $item->quantidade_prevista }}"
                                           name="itens[{{ $index }}][quantidade_entregue]"
                                           class="form-control form-control-sm"
                                           value="{{ $item->quantidade_entregue }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Salvar Parcial
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CANCELAR --}}
<div class="modal fade" id="modalCancelarEntrega" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('entregas.cancelar', $entrega->id) }}" class="modal-content">
            @csrf
            @method('PATCH')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-octagon me-2"></i>Cancelar Entrega
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Motivo do cancelamento</label>
                <textarea name="motivo"
                          class="form-control"
                          rows="4"
                          placeholder="Informe o motivo do cancelamento..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

@endsection