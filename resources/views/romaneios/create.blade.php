@extends('layouts.app')

@section('content')

<style>

    /* ===========================================================
       CARDS KPI
    =========================================================== */

    .kpi-card{
        min-height:96px;
        border-width:3px!important;
        border-radius:8px;
        transition:.20s;
    }

    .kpi-card:hover{
        transform:translateY(-2px);
        box-shadow:0 .35rem .75rem rgba(0,0,0,.16)!important;
    }

    .kpi-card .card-body{
        padding:.90rem 1rem;
    }

    /* ===========================================================
       CABEÇALHOS
    =========================================================== */

    .operational-header,
    .operational-table-header{
        background:#6c757d;
        color:#fff;
        padding:.60rem .90rem;
        font-size:.95rem;
        font-weight:700;
    }

    .form-label{
        font-weight:600;
        margin-bottom:.25rem;
    }

    /* ===========================================================
       LINHAS DA ENTREGA
    =========================================================== */

    .entrega-checkbox{
        cursor:pointer;
    }

    .entrega-row{
        transition:.18s;
    }

    .entrega-row:hover > *{
        background:#eef8ff!important;
    }

    .entrega-row.linha-selecionada > *{
        background:#dbeeff!important;
        border-top:2px solid #4d9cff;
        border-bottom:2px solid #4d9cff;
    }

    /* ===========================================================
       ÁREA EXPANDIDA DO ACCORDION
    =========================================================== */

    .collapse td{

        padding:0!important;

        background:
            linear-gradient(
                180deg,
                #d6ebff 0%,
                #c8e3ff 30%,
                #d6ebff 100%
            );

        border-top:3px solid #5aa7ff!important;

        border-bottom:3px solid #5aa7ff!important;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.90),
            inset 0 -1px 0 rgba(0,0,0,.05);

    }

    /* ===========================================================
       PAINEL DOS ITENS
    =========================================================== */

    .painel-itens{

        margin:14px;

        border:2px solid #5aa7ff;

        border-radius:10px;

        background:#ffffff;

        overflow:hidden;

        box-shadow:
            0 5px 16px rgba(0,0,0,.12);

    }

    .painel-itens-header{

        background:
            linear-gradient(
                180deg,
                #7fbfff 0%,
                #63afff 100%
            );

        color:#fff;

        padding:10px 15px;

        display:flex;

        justify-content:space-between;

        align-items:center;

        font-weight:700;

        border-bottom:2px solid #4b9dff;

    }

    .painel-itens-body{

        background:#ffffff;

        padding:12px;

    }

    /* ===========================================================
       TABELA DOS ITENS
    =========================================================== */

    .item-table{

        margin-bottom:0;

        background:#fff;

    }

    .item-table th,
    .item-table td{

        font-size:.83rem;

        vertical-align:middle;

    }

    .item-table thead th{

        background:
            linear-gradient(
                180deg,
                #6fb7ff,
                #4ea5ff
            );

        color:#fff;

        text-align:center;

        font-weight:700;

        border-color:#4d9cff;

    }

    .item-table tbody tr:nth-child(even){

        background:#f7fbff;

    }

    .item-table tbody tr:hover{

        background:#dceeff;

    }

    /* ===========================================================
       QUANTIDADES
    =========================================================== */

    .qtd-solicitada{

        font-weight:700;

        color:#212529;

        font-size:.90rem;

    }

    .qtd-atendida{

        font-weight:700;

        color:#0d6efd;

        font-size:.90rem;

    }

    .qtd-pendente{

        font-weight:800;

        color:#dc3545;

        font-size:.95rem;

    }

    .qtd-zero{

        font-weight:800;

        color:#198754;

        font-size:.95rem;

    }

    /* ===========================================================
       BADGES
    =========================================================== */

    .badge-itens{

        font-size:.82rem;

        padding:.45rem .70rem;

        border-radius:6px;

    }

    /* ===========================================================
       LINKS DOS DOCUMENTOS
    =========================================================== */

    .linha-documento a{

        display:block;

        font-size:.82rem;

        font-weight:600;

        text-decoration:none;

        margin-bottom:2px;

    }

    .linha-documento span{

        display:block;

        font-size:.82rem;

    }

    /* ===========================================================
       BOTÕES
    =========================================================== */

    .acao-btn{

        width:30px;

        height:30px;

        display:inline-flex;

        justify-content:center;

        align-items:center;

        padding:0;

    }

    /* ===========================================================
       TEXTO AUXILIAR
    =========================================================== */

    .small-muted{

        color:#6c757d;

        font-size:.75rem;

    }


    
    .entrega-checkbox {
        cursor: pointer;
    }

    .kpi-card {
        min-height: 96px;
        border-width: 3px !important;
        border-radius: 8px;
        transition: .2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .75rem rgba(0,0,0,.16) !important;
    }

    .kpi-card .card-body {
        padding: 0.85rem 1rem;
    }

    .operational-header,
    .operational-table-header {
        background: #6c757d;
        color: #fff;
        padding: 0.55rem 0.75rem;
        font-size: 0.95rem;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .entrega-row:hover > * {
        background-color: #cff4fc !important;
    }

    .entrega-row.linha-selecionada > * {
        background-color: #d6e9ff !important;
    }

    .accordion-button {
        padding: .45rem .75rem;
        font-size: .88rem;
        font-weight: 600;
    }

    .accordion-body {
        padding: .65rem;
        background: #f8f9fa;
    }

    .item-table th,
    .item-table td {
        font-size: .82rem;
        vertical-align: middle;
    }

    .small-muted {
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
</style>

<div class="container-fluid px-2 py-3">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-clipboard-plus me-2"></i>Novo Romaneio
            </h4>
            <small class="text-muted">
                Selecione as entregas e confira os itens antes de montar o romaneio.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('expedicao.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>

            <button type="submit" form="formRomaneio" class="btn btn-primary btn-sm">
                <i class="bi bi-box-seam me-1"></i>Criar Romaneio
            </button>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Verifique os campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-secondary shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Romaneio</small>
                        <div class="fw-semibold">Disponíveis</div>
                        <h3 class="fw-bold mb-0">{{ $entregasDisponiveis->count() }}</h3>
                    </div>
                    <i class="bi bi-clipboard-check fs-2 text-secondary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-primary shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Seleção</small>
                        <div class="fw-semibold">Selecionadas</div>
                        <h3 class="fw-bold mb-0 text-primary" id="contadorSelecionadasTopo">0</h3>
                    </div>
                    <i class="bi bi-check2-square fs-2 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-warning shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Separação</small>
                        <div class="fw-semibold">Separando</div>
                        <h3 class="fw-bold mb-0">{{ $entregasDisponiveis->where('status', 'Separando')->count() }}</h3>
                    </div>
                    <i class="bi bi-lightning-charge fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-info shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Equipe</small>
                        <div class="fw-semibold">Motoristas</div>
                        <h3 class="fw-bold mb-0">{{ $motoristas->count() }}</h3>
                    </div>
                    <i class="bi bi-person-badge fs-2 text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-dark shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Frota</small>
                        <div class="fw-semibold">Veículos</div>
                        <h3 class="fw-bold mb-0">{{ $veiculos->count() }}</h3>
                    </div>
                    <i class="bi bi-truck-front fs-2 text-dark"></i>
                </div>
            </div>
        </div>

        <div class="col-xl col-lg-4 col-md-6">
            <div class="card kpi-card border-success shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Operação</small>
                        <div class="fw-semibold">Status</div>
                        <h3 class="fw-bold mb-0 text-success">Novo</h3>
                    </div>
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <form id="formRomaneio" action="{{ route('romaneios.store') }}" method="POST">
        @csrf

        {{-- Dados da expedição --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header operational-header">
                <i class="bi bi-truck-front me-2"></i>
                <strong>Dados da Expedição</strong>
            </div>

            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Motorista</label>
                        <select name="motorista_id" class="form-select form-select-sm">
                            <option value="">Selecione o motorista</option>
                            @foreach($motoristas as $motorista)
                                <option value="{{ $motorista->id }}" @selected(old('motorista_id') == $motorista->id)>
                                    {{ $motorista->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Veículo</label>
                        <select name="veiculo_id" class="form-select form-select-sm">
                            <option value="">Selecione o veículo</option>
                            @foreach($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}" @selected(old('veiculo_id') == $veiculo->id)>
                                    {{ $veiculo->descricao ?? $veiculo->nome ?? 'Veículo #' . $veiculo->id }}
                                    @if(!empty($veiculo->placa))
                                        - {{ $veiculo->placa }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Observação</label>
                        <input type="text"
                               name="observacao"
                               class="form-control form-control-sm"
                               value="{{ old('observacao') }}"
                               placeholder="Observações da expedição, rota, prioridade ou carregamento...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header operational-header">
                <i class="bi bi-funnel me-2"></i>
                <strong>Filtros</strong>
            </div>

            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Buscar Entrega</label>
                        <input type="text"
                               id="filtroEntregas"
                               class="form-control form-control-sm"
                               placeholder="Cliente, endereço, código ou número da entrega...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="filtroStatus" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="separando">Separando</option>
                            <option value="aguardando_separacao">Aguardando separação</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Data Prevista</label>
                        <input type="date" id="filtroData" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="marcarTodas" class="btn btn-primary btn-sm">
                            <i class="bi bi-check2-square me-1"></i>Marcar Visíveis
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="limparSelecao" class="btn btn-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Entregas disponíveis --}}
        <div class="card shadow-sm">
            <div class="card-header operational-table-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-list-check me-2"></i>
                    <strong>Entregas Disponíveis para Romaneio</strong>
                </div>

                <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">Total: {{ $entregasDisponiveis->count() }}</span>
                    <span class="badge bg-warning text-dark">Separando: {{ $entregasDisponiveis->where('status', 'Separando')->count() }}</span>
                    <span class="badge bg-primary">Selecionadas: <span id="contadorSelecionadasTabela">0</span></span>
                </div>
            </div>

            <div class="table-responsive-lg">
                <table class="table table-bordered table-hover align-middle mb-0" id="tabelaEntregasRomaneio">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 45px;"></th>
                            <th style="width: 80px;">ID</th>
                            <th>Código / Cliente</th>
                            <th style="width: 140px;">Documentos</th>
                            <th>Endereço</th>
                            <th style="width: 110px;">Previsão</th>
                            <th style="width: 80px;">Itens</th>
                            <th style="width: 130px;">Status</th>
                            <th style="width: 60px;">Detalhes</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($entregasDisponiveis as $entrega)
                            @php
                                $statusOriginal = $entrega->status ?? 'Não informado';
                                $status = strtolower($statusOriginal);

                                $dataPrevista = !empty($entrega->data_prevista)
                                    ? \Carbon\Carbon::parse($entrega->data_prevista)->format('Y-m-d')
                                    : '';

                                $classeBadge = match($status) {
                                    'separando' => 'bg-warning text-dark',
                                    'aguardando_separacao' => 'bg-secondary text-light',
                                    'carregado' => 'bg-info text-dark',
                                    'em_rota' => 'bg-dark',
                                    'entregue' => 'bg-success',
                                    'cancelado' => 'bg-danger',
                                    default => 'bg-secondary text-light'
                                };

                                $labelStatus = match($status) {
                                    'separando' => 'Separando',
                                    'aguardando_separacao' => 'Aguardando separação',
                                    'carregado' => 'Carregado',
                                    'em_rota' => 'Em rota',
                                    'entregue' => 'Entregue',
                                    'cancelado' => 'Cancelado',
                                    default => ucfirst(str_replace('_', ' ', $statusOriginal))
                                };

                                $clienteNome = $entrega->orcamento->cliente->nome
                                    ?? $entrega->cliente->nome
                                    ?? 'Cliente não informado';

                                $telefoneCliente = $entrega->orcamento->cliente->telefone
                                    ?? $entrega->cliente->telefone
                                    ?? 'Telefone não informado';

                                $itensEntrega = $entrega->itens ?? collect();
                            @endphp

                            <tr class="entrega-row"
                                data-status="{{ $status }}"
                                data-data="{{ $dataPrevista }}"
                                data-search="{{ strtolower(
                                    ($entrega->venda_id ?? '') . ' ' .
                                    ($entrega->orcamento_id ?? '') . ' ' .
                                    $clienteNome . ' ' .
                                    $telefoneCliente . ' ' .
                                    ($entrega->endereco_entrega ?? '') . ' ' .
                                    ($entrega->codigo_entrega ?? '') . ' ' .
                                    $entrega->id . ' ' .
                                    ($entrega->status ?? '')
                                ) }}">

                                <td class="text-center">
                                    <input type="checkbox"
                                           name="entregas[]"
                                           value="{{ $entrega->id }}"
                                           class="form-check-input entrega-checkbox"
                                           @checked(is_array(old('entregas')) && in_array($entrega->id, old('entregas')))>
                                </td>

                                <td class="fw-bold text-center">{{ $entrega->id }}</td>

                                <td>
                                    <div class="fw-bold">{{ $entrega->codigo_entrega ?? 'Sem código' }}</div>
                                    <div class="fw-semibold">{{ $clienteNome }}</div>
                                    <small class="text-muted">{{ $telefoneCliente }}</small>
                                </td>

                                <td class="text-center">
                                    @if(!empty($entrega->venda_id))
                                        <a href="{{ url('/venda/' . $entrega->venda_id . '/cupom') }}"
                                           target="_blank"
                                           class="text-decoration-none fw-semibold d-block">
                                            <i class="bi bi-receipt me-1"></i>VEN-{{ $entrega->venda_id }}
                                        </a>
                                    @else
                                        <span class="text-muted d-block">VEN —</span>
                                    @endif

                                    @if(!empty($entrega->orcamento_id))
                                        @if(Route::has('orcamentos.show'))
                                            <a href="{{ route('orcamentos.show', $entrega->orcamento_id) }}"
                                               class="text-decoration-none fw-semibold d-block">
                                                <i class="bi bi-file-earmark-text me-1"></i>ORÇ-{{ $entrega->orcamento_id }}
                                            </a>
                                        @else
                                            <span class="text-muted d-block">ORÇ-{{ $entrega->orcamento_id }}</span>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    <small>{{ $entrega->endereco_entrega ?? 'Endereço não informado' }}</small>
                                </td>

                                <td class="text-center">
                                    @if(!empty($entrega->data_prevista))
                                        {{ \Carbon\Carbon::parse($entrega->data_prevista)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">Não informada</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $itensEntrega->count() }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $classeBadge }}">
                                        {{ $labelStatus }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm acao-btn"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#itens-entrega-{{ $entrega->id }}"
                                            aria-expanded="false"
                                            title="Ver itens da entrega">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse bg-light"
                                id="itens-entrega-{{ $entrega->id }}">
                                <td colspan="9" class="p-0">
                                    <div class="accordion-body bg-info">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>
                                                <i class="bi bi-box-seam me-1"></i>
                                                Itens da Entrega #{{ $entrega->id }}
                                            </strong>

                                            <span class="badge bg-dark">
                                                {{ $itensEntrega->count() }} item(ns)
                                            </span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 item-table">
                                                <thead class="table-secondary text-center">
                                                    <tr>
                                                        <th style="width: 35%;">Produto</th>
                                                        <th style="width: 15%;">Localização</th>
                                                        <th style="width: 12%;">Solicitada</th>
                                                        <th style="width: 12%;">Atendida</th>
                                                        <th style="width: 12%;">Pendente</th>
                                                        <th style="width: 8%;">Unid.</th>
                                                        <th style="width: 6%;">Status</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @forelse($itensEntrega as $item)
                                                        @php
                                                            $produto = $item->produto
                                                                ?? $item->vendaItem->produto
                                                                ?? $item->itemOrcamento->produto
                                                                ?? null;

                                                            $itemOrcamento = $item->itemOrcamento ?? null;
                                                            $itemVenda = $item->vendaItem ?? null;

                                                            $quantidadeSolicitada = $itemOrcamento->quantidade_solicitada
                                                                ?? $itemVenda->quantidade
                                                                ?? $item->quantidade_prevista
                                                                ?? 0;

                                                            $quantidadeAtendida = $itemOrcamento->quantidade_atendida
                                                                ?? $item->quantidade_prevista
                                                                ?? 0;

                                                            $quantidadePendente = $itemOrcamento->quantidade_pendente
                                                                ?? max(0, (float) $quantidadeSolicitada - (float) $quantidadeAtendida);

                                                            $unidade = $produto->unidade_medida->sigla
                                                                ?? $produto->unidade
                                                                ?? $produto->unidade_medida
                                                                ?? 'UN';

                                                            $localizacao = $produto->localizacao_estoque ?? '—';

                                                            $statusItem = $item->status
                                                                ?? $itemOrcamento->status
                                                                ?? 'Pendente';

                                                            $badgeItem = match(strtolower($statusItem)) {
                                                                'disponivel', 'separado', 'entregue', 'concluido', 'concluído' => 'bg-success',
                                                                'parcial' => 'bg-warning text-dark',
                                                                'indisponivel', 'pendente' => 'bg-secondary',
                                                                'cancelado' => 'bg-danger',
                                                                default => 'bg-secondary',
                                                            };
                                                        @endphp

                                                        <tr>
                                                            <td>
                                                                <div class="fw-semibold">
                                                                    {{ $produto->nome ?? $produto->descricao ?? 'Produto não identificado' }}
                                                                </div>
                                                                <small class="text-muted">
                                                                    Cód.: {{ $produto->id ?? '—' }}
                                                                </small>
                                                            </td>

                                                            <td class="text-center">{{ $localizacao }}</td>

                                                            <td class="text-end fw-semibold">
                                                                {{ number_format((float) $quantidadeSolicitada, 2, ',', '.') }}
                                                            </td>

                                                            <td class="text-end">
                                                                {{ number_format((float) $quantidadeAtendida, 2, ',', '.') }}
                                                            </td>

                                                            <td class="text-end">
                                                                {{ number_format((float) $quantidadePendente, 2, ',', '.') }}
                                                            </td>

                                                            <td class="text-center">{{ $unidade }}</td>

                                                            <td class="text-center">
                                                                <span class="badge {{ $badgeItem }}">
                                                                    {{ ucfirst($statusItem) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                Nenhum item encontrado para esta entrega.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Nenhuma entrega disponível para romaneio.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    O romaneio será criado somente com as entregas selecionadas.
                </small>

                <div class="d-flex gap-2">
                    <a href="{{ route('expedicao.index') }}" class="btn btn-outline-secondary btn-sm">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Criar Romaneio
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filtroTexto = document.getElementById('filtroEntregas');
        const filtroStatus = document.getElementById('filtroStatus');
        const filtroData = document.getElementById('filtroData');
        const linhas = document.querySelectorAll('#tabelaEntregasRomaneio tbody tr.entrega-row[data-search]');
        const checkboxes = document.querySelectorAll('.entrega-checkbox');
        const contadorTopo = document.getElementById('contadorSelecionadasTopo');
        const contadorTabela = document.getElementById('contadorSelecionadasTabela');
        const botaoMarcarTodas = document.getElementById('marcarTodas');
        const botaoLimpar = document.getElementById('limparSelecao');

        function atualizarContador() {
            const total = document.querySelectorAll('.entrega-checkbox:checked').length;

            if (contadorTopo) contadorTopo.textContent = total;
            if (contadorTabela) contadorTabela.textContent = total;

            document.querySelectorAll('.entrega-row').forEach(function (linha) {
                const checkbox = linha.querySelector('.entrega-checkbox');

                if (checkbox && checkbox.checked) {
                    linha.classList.add('linha-selecionada');
                } else {
                    linha.classList.remove('linha-selecionada');
                }
            });
        }

        function aplicarFiltros() {
            const termo = filtroTexto ? filtroTexto.value.toLowerCase().trim() : '';
            const status = filtroStatus ? filtroStatus.value.toLowerCase().trim() : '';
            const data = filtroData ? filtroData.value : '';

            linhas.forEach(function (linha) {
                const textoLinha = linha.dataset.search || '';
                const statusLinha = linha.dataset.status || '';
                const dataLinha = linha.dataset.data || '';

                const passaTexto = !termo || textoLinha.includes(termo);
                const passaStatus = !status || statusLinha.includes(status);
                const passaData = !data || dataLinha === data;

                const visivel = passaTexto && passaStatus && passaData;

                linha.style.display = visivel ? '' : 'none';

                const collapseId = linha.nextElementSibling && linha.nextElementSibling.classList.contains('collapse')
                    ? linha.nextElementSibling
                    : null;

                if (collapseId) {
                    collapseId.style.display = visivel ? '' : 'none';
                }
            });
        }

        function linhasVisiveis() {
            return Array.from(linhas).filter(function (linha) {
                return linha.style.display !== 'none';
            });
        }

        if (filtroTexto) filtroTexto.addEventListener('input', aplicarFiltros);
        if (filtroStatus) filtroStatus.addEventListener('change', aplicarFiltros);
        if (filtroData) filtroData.addEventListener('change', aplicarFiltros);

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', atualizarContador);
        });

        if (botaoMarcarTodas) {
            botaoMarcarTodas.addEventListener('click', function () {
                linhasVisiveis().forEach(function (linha) {
                    const checkbox = linha.querySelector('.entrega-checkbox');

                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                atualizarContador();
            });
        }

        if (botaoLimpar) {
            botaoLimpar.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                atualizarContador();
            });
        }

        atualizarContador();
    });
</script>

@endsection