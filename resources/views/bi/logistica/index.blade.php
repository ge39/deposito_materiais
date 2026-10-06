@extends('layouts.app')

@section('content')

<style>
.bi-logistica-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}

.bi-logistica-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}

.bi-logistica-icon {
    font-size: 1.30rem;
    line-height: 1;
}

.bi-logistica-label {
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.bi-logistica-value {
    font-size: 1.28rem;
    font-weight: 700;
    line-height: 1.05;
}

.bi-logistica-detail {
    font-size: .78rem;
    text-decoration: none;
}

.bi-logistica-detail:hover {
    text-decoration: underline;
}

.modal-bi .modal-dialog {
    max-width: 1200px;
}

.modal-bi .modal-body {
    max-height: 72vh;
    overflow-y: auto;
}

.modal-bi table th {
    white-space: nowrap;
}
</style>


<div class="container py-4">

    {{-- ======================================================
         CABECALHO
         ====================================================== --}}

    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>
            <h2 class="mb-1">
                <i class="bi bi-truck me-2"></i>
                Entregas &amp; Log&iacute;stica
            </h2>

            <div class="text-muted">
                Entregas realizadas, atrasos, ocorr&ecirc;ncias,
                romaneios, ve&iacute;culos e motoristas.
            </div>
        </div>

        <a
            href="{{ route('bi.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>


    {{-- ======================================================
         FILTROS
         ====================================================== --}}

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET" action="{{ route('bi.logistica.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data inicial</label>

                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="{{ $inicio->format('Y-m-d') }}"
                        >
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data final</label>

                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="{{ $fim->format('Y-m-d') }}"
                        >
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">

                            <option value="">Todos</option>

                            @foreach($statusDisponiveis as $opcao)

                                <option
                                    value="{{ $opcao }}"
                                    @selected($status === $opcao)
                                >
                                    {{ $opcao }}
                                </option>

                            @endforeach

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Per&iacute;odo</label>

                        <select name="periodo" class="form-select">

                            <option value="">Todos</option>

                            <option value="manha" @selected($periodo === 'manha')>
                                Manh&atilde;
                            </option>

                            <option value="tarde" @selected($periodo === 'tarde')>
                                Tarde
                            </option>

                            <option value="comercial" @selected($periodo === 'comercial')>
                                Comercial
                            </option>

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Motorista</label>

                        <select name="motorista_id" class="form-select">

                            <option value="">Todos</option>

                            @foreach($motoristas as $motorista)

                                <option
                                    value="{{ $motorista->id }}"
                                    @selected($motoristaId === (int) $motorista->id)
                                >
                                    {{ $motorista->nome }}
                                </option>

                            @endforeach

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Ve&iacute;culo</label>

                        <select name="veiculo_id" class="form-select">

                            <option value="">Todos</option>

                            @foreach($veiculos as $veiculo)

                                <option
                                    value="{{ $veiculo->id }}"
                                    @selected($veiculoId === (int) $veiculo->id)
                                >
                                    {{ $veiculo->placa }}
                                    @if($veiculo->modelo)
                                        - {{ $veiculo->modelo }}
                                    @endif
                                </option>

                            @endforeach

                        </select>
                    </div>


                    <div class="col-12">

                        <div class="d-flex gap-2 justify-content-end">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Aplicar
                            </button>

                            <a
                                href="{{ route('bi.logistica.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Limpar
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ======================================================
         SOMENTE OS 12 CARDS FICAM VISIVEIS
         ====================================================== --}}

    <div class="row g-4">

        @php
            $cards = [
                [
                    'titulo' => 'Entregas Previstas',
                    'valor' => $totalEntregas,
                    'icone' => 'bi-calendar-check',
                    'cor' => 'primary',
                    'modal' => 'modalEntregas',
                ],
                [
                    'titulo' => 'Entregues',
                    'valor' => $entregues,
                    'icone' => 'bi-check-circle',
                    'cor' => 'success',
                    'modal' => 'modalEntregues',
                ],
                [
                    'titulo' => 'Entregues c/ Ocorrência',
                    'valor' => $entreguesComOcorrencia,
                    'icone' => 'bi-exclamation-circle',
                    'cor' => 'warning',
                    'modal' => 'modalEntreguesOcorrencia',
                ],
                [
                    'titulo' => 'Entregas Parciais',
                    'valor' => $entregasParciais,
                    'icone' => 'bi-circle-half',
                    'cor' => 'warning',
                    'modal' => 'modalParciais',
                ],
                [
                    'titulo' => 'Não Entregues',
                    'valor' => $naoEntregues,
                    'icone' => 'bi-x-circle',
                    'cor' => 'danger',
                    'modal' => 'modalNaoEntregues',
                ],
                [
                    'titulo' => 'Em Rota',
                    'valor' => $emRota,
                    'icone' => 'bi-truck',
                    'cor' => 'primary',
                    'modal' => 'modalEmRota',
                ],
                [
                    'titulo' => 'Atrasadas',
                    'valor' => $atrasadas,
                    'icone' => 'bi-clock-history',
                    'cor' => 'danger',
                    'modal' => 'modalAtrasadas',
                ],
                [
                    'titulo' => 'Ocorrências',
                    'valor' => $totalOcorrencias,
                    'icone' => 'bi-exclamation-triangle',
                    'cor' => 'warning',
                    'modal' => 'modalOcorrencias',
                ],
                [
                    'titulo' => 'Romaneios',
                    'valor' => $totalRomaneios,
                    'icone' => 'bi-clipboard-check',
                    'cor' => 'secondary',
                    'modal' => 'modalRomaneios',
                ],
                [
                    'titulo' => 'Saídas Registradas',
                    'valor' => $saidasRegistradas,
                    'icone' => 'bi-box-arrow-right',
                    'cor' => 'info',
                    'modal' => 'modalSaidas',
                ],
                [
                    'titulo' => 'Motoristas Utilizados',
                    'valor' => $motoristasUtilizados,
                    'icone' => 'bi-person-badge',
                    'cor' => 'info',
                    'modal' => 'modalMotoristas',
                ],
                [
                    'titulo' => 'Veículos Utilizados',
                    'valor' => $veiculosUtilizados,
                    'icone' => 'bi-truck-front',
                    'cor' => 'info',
                    'modal' => 'modalVeiculos',
                ],
            ];
        @endphp


        @foreach($cards as $card)

            <div class="col-lg-4 col-md-6">

                <div class="card bi-logistica-card border-{{ $card['cor'] }} shadow-sm">

                    <div class="card-body">

                        <i class="bi {{ $card['icone'] }} bi-logistica-icon text-{{ $card['cor'] }}"></i>

                        <div class="bi-logistica-label">
                            {{ $card['titulo'] }}
                        </div>

                        <div class="bi-logistica-value">
                            {{ $card['valor'] }}
                        </div>

                        <a
                            href="#"
                            class="bi-logistica-detail text-{{ $card['cor'] }}"
                            data-bs-toggle="modal"
                            data-bs-target="#{{ $card['modal'] }}"
                        >
                            Ver detalhes
                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>


{{-- ==========================================================
     MACRO VISUAL - MODAIS DE ENTREGAS
     ========================================================== --}}

@php
    $modaisEntrega = [
        [
            'id' => 'modalEntregas',
            'titulo' => 'Entregas Previstas',
            'icone' => 'bi-calendar-check',
            'cor' => 'primary',
            'dados' => $entregasPrevistasDetalhes,
        ],
        [
            'id' => 'modalEntregues',
            'titulo' => 'Entregas Realizadas',
            'icone' => 'bi-check-circle',
            'cor' => 'success',
            'dados' => $entreguesDetalhes,
        ],
        [
            'id' => 'modalEntreguesOcorrencia',
            'titulo' => 'Entregues com Ocorrência',
            'icone' => 'bi-exclamation-circle',
            'cor' => 'warning',
            'dados' => $entreguesComOcorrenciaDetalhes,
        ],
        [
            'id' => 'modalParciais',
            'titulo' => 'Entregas Parciais',
            'icone' => 'bi-circle-half',
            'cor' => 'warning',
            'dados' => $parciaisDetalhes,
        ],
        [
            'id' => 'modalNaoEntregues',
            'titulo' => 'Não Entregues',
            'icone' => 'bi-x-circle',
            'cor' => 'danger',
            'dados' => $naoEntreguesDetalhes,
        ],
        [
            'id' => 'modalEmRota',
            'titulo' => 'Entregas em Rota',
            'icone' => 'bi-truck',
            'cor' => 'primary',
            'dados' => $emRotaDetalhes,
        ],
    ];
@endphp


@foreach($modaisEntrega as $modal)

<div class="modal fade modal-bi" id="{{ $modal['id'] }}" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi {{ $modal['icone'] }} text-{{ $modal['cor'] }} me-2"></i>

                    {{ $modal['titulo'] }}

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-{{ $modal['cor'] }}">

                    Total:
                    <strong>
                        {{ $modal['dados']->count() }}
                    </strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th>Realizada</th>
                                <th>Per&iacute;odo</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Endere&ccedil;o</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($modal['dados'] as $linha)

                            <tr>

                                <td>
                                    {{ $linha->codigo_entrega ?: ('#' . $linha->id) }}
                                </td>

                                <td>
                                    {{ $linha->data_prevista
                                        ? \Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    {{ $linha->data_realizada
                                        ? \Carbon\Carbon::parse($linha->data_realizada)->format('d/m/Y')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    {{ ucfirst($linha->periodo_entrega ?: '—') }}
                                </td>

                                <td>
                                    {{ $linha->status }}
                                </td>

                                <td>
                                    {{ $linha->motorista_nome ?: '—' }}
                                </td>

                                <td>
                                    {{ $linha->placa ?: '—' }}
                                </td>

                                <td>
                                    {{ $linha->endereco_entrega ?: '—' }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted"
                                >
                                    Nenhum registro encontrado.
                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endforeach


{{-- ==========================================================
     MODAL - ATRASADAS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalAtrasadas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-clock-history text-danger me-2"></i>

                    Entregas Atrasadas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-danger">

                    Total:
                    <strong>{{ $atrasadas }}</strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th class="text-end">Dias atraso</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Endere&ccedil;o</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($atrasadasDetalhes as $linha)

                            <tr>

                                <td>
                                    {{ $linha->codigo_entrega ?: ('#' . $linha->id) }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y') }}
                                </td>

                                <td class="text-end fw-bold text-danger">
                                    {{ $linha->dias_atraso }}
                                </td>

                                <td>
                                    {{ $linha->status }}
                                </td>

                                <td>
                                    {{ $linha->motorista_nome ?: '—' }}
                                </td>

                                <td>
                                    {{ $linha->placa ?: '—' }}
                                </td>

                                <td>
                                    {{ $linha->endereco_entrega ?: '—' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma entrega atrasada.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ==========================================================
     MODAL - OCORRENCIAS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalOcorrencias" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>

                    Ocorr&ecirc;ncias

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-warning">

                    Total:
                    <strong>{{ $totalOcorrencias }}</strong>

                </div>


                <h6>Resumo por categoria</h6>

                <div class="table-responsive mb-4">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Categoria</th>
                                <th class="text-end">Quantidade</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($ocorrenciasPorCategoria as $linha)

                            <tr>
                                <td>{{ $linha->categoria ?: 'N&atilde;o informada' }}</td>
                                <td class="text-end">{{ $linha->total }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    Nenhuma ocorr&ecirc;ncia encontrada.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                <h6>Detalhamento</h6>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Romaneio</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Classifica&ccedil;&atilde;o</th>
                                <th>Criticidade</th>
                                <th>Status</th>
                                <th class="text-end">Quantidade</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($ocorrenciasDetalhes as $linha)

                            <tr>

                                <td>{{ $linha->codigo_entrega ?: '—' }}</td>

                                <td>#{{ $linha->romaneio_id }}</td>

                                <td>{{ $linha->categoria ?: '—' }}</td>

                                <td>{{ $linha->tipo ?: '—' }}</td>

                                <td>{{ $linha->classificacao_inicial ?: '—' }}</td>

                                <td>{{ $linha->criticidade ?: '—' }}</td>

                                <td>{{ $linha->status ?: '—' }}</td>

                                <td class="text-end">
                                    {{ number_format((float) ($linha->quantidade_envolvida ?? 0), 2, ',', '.') }}
                                </td>

                                <td>{{ $linha->motorista_nome ?: '—' }}</td>

                                <td>{{ $linha->placa ?: '—' }}</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="10" class="text-center text-muted">
                                    Nenhuma ocorr&ecirc;ncia encontrada.
                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ==========================================================
     MODAL - ROMANEIOS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalRomaneios" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-clipboard-check text-secondary me-2"></i>

                    Romaneios

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-secondary">

                    Total:
                    <strong>{{ $totalRomaneios }}</strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Romaneio</th>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Sa&iacute;da</th>
                                <th>Retorno</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($romaneiosDetalhes as $linha)

                            <tr>

                                <td>#{{ $linha->id }}</td>

                                <td>{{ $linha->codigo_entrega ?: ('#' . $linha->entrega_id) }}</td>

                                <td>
                                    {{ $linha->data_prevista
                                        ? \Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y')
                                        : '—'
                                    }}
                                </td>

                                <td>{{ $linha->status }}</td>

                                <td>{{ $linha->motorista_nome ?: '—' }}</td>

                                <td>{{ $linha->placa ?: '—' }}</td>

                                <td>
                                    {{ $linha->data_saida
                                        ? \Carbon\Carbon::parse($linha->data_saida)->format('d/m/Y H:i')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    {{ $linha->data_retorno
                                        ? \Carbon\Carbon::parse($linha->data_retorno)->format('d/m/Y H:i')
                                        : '—'
                                    }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Nenhum romaneio encontrado.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ==========================================================
     MODAL - SAIDAS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalSaidas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-box-arrow-right text-info me-2"></i>

                    Sa&iacute;das Registradas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Total:
                    <strong>{{ $saidasRegistradas }}</strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Data / Hora</th>
                                <th>Romaneio</th>
                                <th>Entrega</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Status anterior</th>
                                <th>Status novo</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($saidasDetalhes as $linha)

                            <tr>

                                <td>
                                    {{ \Carbon\Carbon::parse($linha->ocorrido_em)->format('d/m/Y H:i') }}
                                </td>

                                <td>#{{ $linha->romaneio_id }}</td>

                                <td>{{ $linha->codigo_entrega ?: '—' }}</td>

                                <td>{{ $linha->motorista_nome ?: '—' }}</td>

                                <td>
                                    {{ $linha->placa ?: '—' }}
                                    @if($linha->modelo)
                                        - {{ $linha->modelo }}
                                    @endif
                                </td>

                                <td>{{ $linha->status_anterior ?: '—' }}</td>

                                <td>{{ $linha->status_novo ?: '—' }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma sa&iacute;da registrada.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ==========================================================
     MODAL - MOTORISTAS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalMotoristas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-person-badge text-info me-2"></i>

                    Ranking de Motoristas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Motoristas utilizados:
                    <strong>{{ $motoristasUtilizados }}</strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Motorista</th>
                                <th class="text-end">Romaneios</th>
                                <th class="text-end">Entregues</th>
                                <th class="text-end">C/ ocorr.</th>
                                <th class="text-end">Parciais</th>
                                <th class="text-end">N&atilde;o entregues</th>
                                <th class="text-end">Em rota</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($rankingMotoristas as $linha)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $linha->nome ?: ('Motorista #' . $linha->motorista_id) }}
                                </td>

                                <td class="text-end">{{ $linha->romaneios }}</td>

                                <td class="text-end">{{ $linha->entregues }}</td>

                                <td class="text-end">{{ $linha->entregues_com_ocorrencia }}</td>

                                <td class="text-end">{{ $linha->parciais }}</td>

                                <td class="text-end">{{ $linha->nao_entregues }}</td>

                                <td class="text-end">{{ $linha->em_rota }}</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-center text-muted">
                                    Nenhum motorista encontrado.
                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ==========================================================
     MODAL - VEICULOS
     ========================================================== --}}

<div class="modal fade modal-bi" id="modalVeiculos" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-truck-front text-info me-2"></i>

                    Ranking de Ve&iacute;culos

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Ve&iacute;culos utilizados:
                    <strong>{{ $veiculosUtilizados }}</strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th class="text-end">Romaneios</th>
                                <th class="text-end">Entregues</th>
                                <th class="text-end">C/ ocorr.</th>
                                <th class="text-end">Parciais</th>
                                <th class="text-end">N&atilde;o entregues</th>
                                <th class="text-end">Em rota</th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($rankingVeiculos as $linha)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $linha->placa ?: ('Veículo #' . $linha->veiculo_id) }}
                                </td>

                                <td>{{ $linha->modelo ?: '—' }}</td>

                                <td class="text-end">{{ $linha->romaneios }}</td>

                                <td class="text-end">{{ $linha->entregues }}</td>

                                <td class="text-end">{{ $linha->entregues_com_ocorrencia }}</td>

                                <td class="text-end">{{ $linha->parciais }}</td>

                                <td class="text-end">{{ $linha->nao_entregues }}</td>

                                <td class="text-end">{{ $linha->em_rota }}</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center text-muted">
                                    Nenhum ve&iacute;culo encontrado.
                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection