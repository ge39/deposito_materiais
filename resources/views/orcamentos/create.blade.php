@extends('layouts.app')

@section('content')
<style>
    .orcamento-create-page {
        --orc-primary: #2563eb;
        --orc-primary-dark: #1d4ed8;
        --orc-success: #0f9f6e;
        --orc-warning: #f59e0b;
        --orc-danger: #dc2626;
        --orc-text: #172033;
        --orc-muted: #667085;
        --orc-border: #e4e9f2;
        --orc-surface: #ffffff;
        --orc-soft: #f7f9fc;

        color: var(--orc-text);
        width: calc(100vw - 24px);
        max-width: none;
        margin-left: calc(50% - 50vw + 12px);
        margin-right: 0;
        padding-left: clamp(.75rem, 1.4vw, 1.5rem) !important;
        padding-right: clamp(.75rem, 1.4vw, 1.5rem) !important;
    }

    .orcamento-create-page .page-heading {
        background:
            linear-gradient(
                135deg,
                rgba(37, 99, 235, .10),
                rgba(14, 165, 233, .03)
            ),
            #ffffff;

        border: 1px solid var(--orc-border);
        border-left: 5px solid var(--orc-primary);
        border-radius: 13px;
        padding: .85rem 1.05rem;
        box-shadow: 0 7px 22px rgba(16, 24, 40, .055);
    }

    .orcamento-create-page .page-heading h2 {
        color: #101828;
        font-weight: 750;
        letter-spacing: -.02em;
        font-size: clamp(1.35rem, 2vw, 1.7rem);
    }

    .orcamento-create-page .page-heading p {
        font-size: .86rem;
    }

    .orcamento-create-page .info-card {
        border: 1px solid var(--orc-border) !important;
        border-radius: 11px;
        box-shadow: 0 5px 16px rgba(16, 24, 40, .04) !important;
        transition:
            transform .18s ease,
            box-shadow .18s ease;
    }

    .orcamento-create-page .info-card .card-body {
        min-height: 66px;
        padding: .65rem .8rem;
    }

    .orcamento-create-page .info-card small {
        font-size: .76rem;
        line-height: 1.2;
    }

    .orcamento-create-page .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(16, 24, 40, .08) !important;
    }

    .orcamento-create-page .info-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #eef4ff;
        flex: 0 0 36px;
    }

    .orcamento-create-page .main-form-card {
        border: 1px solid var(--orc-border) !important;
        border-radius: 13px;
        overflow: hidden;
        box-shadow: 0 9px 28px rgba(16, 24, 40, .06) !important;
    }

    .orcamento-create-page .main-form-card > .card-header {
        min-height: 48px;
        padding: .65rem 1rem;
        background: linear-gradient(135deg, #172033, #25324a) !important;
    }

    .orcamento-create-page .main-form-card > .card-body {
        padding: .85rem;
    }

    .orcamento-create-page .form-section {
        background: var(--orc-surface) !important;
        border: 1px solid var(--orc-border) !important;
               border-radius: 11px !important;
        padding: .9rem !important;
        box-shadow: 0 5px 18px rgba(16, 24, 40, .035);
    }

    .orcamento-create-page .form-section h5 {
        display: flex;
        align-items: center;
        gap: .5rem;
        color: #1d4ed8 !important;
        font-weight: 700;
        letter-spacing: -.01em;
        margin-bottom: .75rem !important;
        font-size: 1.05rem;
    }

    .orcamento-create-page .form-label {
        color: #344054;
        font-size: .88rem;
        font-weight: 650;
        margin-bottom: .4rem;
    }

    .orcamento-create-page .form-control,
    .orcamento-create-page .form-select {
        min-height: 42px;
        border-color: #d5dce8;
        border-radius: 9px;
        box-shadow: none;
    }

    .orcamento-create-page textarea.form-control {
        min-height: auto;
    }

    .orcamento-create-page .form-control:focus,
    .orcamento-create-page .form-select:focus {
        border-color: #80a8ff;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .12);
    }

    .orcamento-create-page .delivery-address-panel {
        background: linear-gradient(180deg, #f8fbff, #f4f7fb);
        border: 1px solid #d9e5f7;
        border-radius: 12px;
        padding: 1rem;
    }

    .orcamento-create-page .registered-address {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: .85rem 1rem;
        color: #475467;
    }

    .orcamento-create-page .cep-group .input-group-text {
        color: var(--orc-primary);
        background: #eef4ff;
        border-color: #d5dce8;
    }

    .orcamento-create-page .cep-status {
        min-height: 20px;
        margin-top: .35rem;
        font-size: .8rem;
    }

    .orcamento-create-page .table-shell {
        overflow: hidden;
        border: 1px solid var(--orc-border);
        border-radius: 12px;
    }

    .orcamento-create-page .table-shell .table {
        --bs-table-hover-bg: #f5f8ff;
    }

    .orcamento-create-page .table-shell thead th {
        padding: .8rem .7rem;
        border: 0;
        background: #25324a;
        font-size: .78rem;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .orcamento-create-page .table-shell tbody td {
        padding: .65rem;
        border-color: #edf0f5;
    }

    /*
    |--------------------------------------------------------------------------
    | RESUMO FINANCEIRO
    |--------------------------------------------------------------------------
    */

    .orcamento-create-page .financial-summary-card {
        display: block;
        width: 50%;
        margin-top: 0;
        margin-right: 0;
        margin-bottom: .75rem;
        margin-left: auto;
        box-sizing: border-box;
        background: #ffffff;
        border: 1px solid #ccebdd;
        border-radius: 9px;
        box-shadow: 0 4px 13px rgba(16, 24, 40, .05);
    }

    /*
    | Linha 1: cabeçalho
    */

    .orcamento-create-page .financial-summary-header {
        width: 100%;
        min-height: 36px;
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .75rem;
        color: #ffffff;
        background: linear-gradient(135deg, #087f5b, #0f9f6e);
        border-radius: 8px 8px 0 0;
        font-size: .82rem;
        font-weight: 700;
    }

    /*
    | Linha 2: container flex com três divs laterais
    */

    .orcamento-create-page .financial-summary-data {
        width: 100%;
        min-height: 54px;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: stretch;
        border-radius: 0 0 8px 8px;
    }

    /*
    | Configuração comum das três divisões
    */

    .orcamento-create-page .financial-data-column {
        min-width: 0;
        min-height: 54px;
        display: inline-flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
        gap: .35rem;
        padding: .4rem .5rem;
        border-left: 1px solid #e3ede8;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .orcamento-create-page .financial-data-column:first-child {
        border-left: 0;
    }

    /*
    | Primeira divisão: Total Bruto
    */

    .orcamento-create-page .financial-data-column:nth-child(1) {
        flex: 0 0 23%;
    }

    /*
    | Segunda divisão: Desconto
    | Recebe mais espaço porque possui label, input e total.
    */

    .orcamento-create-page .financial-data-discount {
        flex: 0 0 42%;
        background: #fffafb;
    }

    /*
    | Terceira divisão: Valor final
    */

    .orcamento-create-page .financial-data-final {
        flex: 1 1 35%;
        background: #ecfdf5;
        border-radius: 0 0 8px 0;
    }

    .orcamento-create-page .financial-data-label {
        flex: 0 0 auto;
        margin: 0;
        color: #667085;
        font-size: .7rem;
        font-weight: 700;
        line-height: 1;
    }

    .orcamento-create-page .financial-data-value {
        flex: 0 0 auto;
        margin: 0;
        color: #172033;
        font-size: .9rem;
        font-weight: 750;
        line-height: 1;
    }

    /*
    | Conteúdo da divisão de desconto
    */

    .orcamento-create-page .financial-data-discount label {
        flex: 0 0 auto;
        margin: 0;
        color: var(--orc-danger);
    }

    .orcamento-create-page .financial-data-discount .form-control {
        flex: 0 0 52px;
        width: 52px;
        min-width: 52px;
        max-width: 52px;
        min-height: 30px;
        height: 30px;
        margin: 0;
        padding: .1rem .25rem;
        color: var(--orc-danger);
        background: #ffffff;
        border-color: #f2c9cf;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 750;
        line-height: 1;
        text-align: center;
    }

    .orcamento-create-page .financial-data-discount-value {
        flex: 0 0 auto;
        margin: 0;
        color: #667085;
        font-size: .66rem;
        line-height: 1;
        white-space: nowrap;
    }

    /*
    | Conteúdo da divisão de valor final
    */

    .orcamento-create-page .financial-data-final .financial-data-label,
    .orcamento-create-page .financial-data-final .financial-data-value {
        color: #087f5b;
    }

    .orcamento-create-page .financial-data-final .financial-data-value {
        font-size: .96rem;
    }

    /*
    |--------------------------------------------------------------------------
    | RODAPÉ
    |--------------------------------------------------------------------------
    */

    .orcamento-create-page .action-footer {
        position: sticky;
        bottom: 0;
        z-index: 20;
        margin: 0 -1rem -1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, .94);
        border-top: 1px solid var(--orc-border);
        backdrop-filter: blur(10px);
    }

    .orcamento-create-page .btn {
        border-radius: 9px;
        font-weight: 650;
    }

    .orcamento-create-page .btn-primary {
        background: var(--orc-primary);
        border-color: var(--orc-primary);
    }

    .orcamento-create-page .btn-primary:hover {
        background: var(--orc-primary-dark);
        border-color: var(--orc-primary-dark);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLET
    |--------------------------------------------------------------------------
    */

    @media (max-width: 991.98px) {
        .orcamento-create-page .financial-summary-card {
            width: 100%;
            margin-left: 0;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CELULAR
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {
        .orcamento-create-page {
            width: 100%;
            margin-left: 0;
            padding-left: .75rem !important;
            padding-right: .75rem !important;
        }

        .orcamento-create-page .page-heading {
            align-items: flex-start !important;
        }

        .orcamento-create-page .action-footer {
            position: static;
            margin-top: 1rem;
        }

        .orcamento-create-page .financial-summary-data {
            flex-direction: column;
        }

        .orcamento-create-page .financial-data-column,
        .orcamento-create-page .financial-data-column:nth-child(1),
        .orcamento-create-page .financial-data-discount,
        .orcamento-create-page .financial-data-final {
            width: 100%;
            flex: 0 0 auto;
            min-height: 48px;
            padding: .55rem .75rem;
            border-top: 1px solid #e3ede8;
            border-left: 0;
        }

        .orcamento-create-page .financial-data-column:first-child {
            border-top: 0;
        }

        .orcamento-create-page .financial-data-final {
            border-radius: 0 0 8px 8px;
        }
    }
</style>

<div class="container-fluid px-3 px-xl-4 orcamento-create-page">

    {{-- CABEÇALHO --}}
    <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                Novo Orçamento
            </h2>
            <p class="text-muted mb-0">
                Criação de orçamento com validade, atendimento, entrega, produtos, lotes e desconto global.
            </p>
        </div>

        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="bi bi-arrow-left-circle me-1"></i>
            Voltar
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Erro!
            </strong>
            Verifique os campos obrigatórios.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CARDS INFORMATIVOS --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card info-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="info-icon me-2 text-primary">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Cliente</div>
                        <small class="text-muted">Obrigatório para iniciar</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card info-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="info-icon me-2 text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Validade</div>
                        <small class="text-muted">Orçamento válido por 7 dias</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card info-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="info-icon me-2 text-success">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Produtos</div>
                        <small class="text-muted">Com controle por lote</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card info-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="info-icon me-2 text-info">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Atendimento</div>
                        <small class="text-muted">Retira loja ou entrega</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('orcamentos.store') }}" method="POST" id="formOrcamento">
        @csrf

        <div class="card main-form-card mb-3">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-pencil-square me-1"></i>
                    Dados do Orçamento
                </div>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-calendar-check me-1"></i>
                    Validade padrão: 7 dias
                </span>
            </div>

            <div class="card-body">

                {{-- Cliente e Datas --}}
                <div class="form-section mb-3">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person-lines-fill me-1"></i>
                        Cliente e Datas
                    </h5>

                    <div class="row g-3 mb-0">
                        <div class="col-md-4">
                            <label class="form-label">Cliente *</label>
                            <select name="cliente_id" id="clienteSelect" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($clientes as $cliente)
                                    <option
                                        value="{{ $cliente->id }}"
                                        @selected((string) old('cliente_id') === (string) $cliente->id)
                                        data-endereco="{{ $cliente->endereco ?? '' }}"
                                        data-numero="{{ $cliente->numero ?? '' }}"
                                        data-complemento="{{ $cliente->complemento ?? '' }}"
                                        data-bairro="{{ $cliente->bairro ?? '' }}"
                                        data-cidade="{{ $cliente->cidade ?? '' }}"
                                        data-estado="{{ $cliente->estado ?? '' }}"
                                        data-cep="{{ $cliente->cep ?? '' }}"
                                        data-telefone="{{ $cliente->telefone ?? '' }}"
                                    >
                                        {{ $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data</label>
                            <input type="date" name="data_orcamento" class="form-control"
                                   value="{{ old('data_orcamento', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control"
                                   value="{{ old('validade', date('Y-m-d', strtotime('+7 days'))) }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <span class="badge bg-warning text-dark p-2 w-100">
                                <i class="bi bi-clock me-1"></i>
                                7 dias
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tipo de Atendimento / Entrega --}}
                <div class="form-section mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-truck"></i>
                            Atendimento / Entrega
                        </h5>
                        <small class="text-muted">
                            Defina a modalidade e confirme o destino antes de adicionar os produtos.
                        </small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label">
                                Forma de Entrega <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_entrega" id="tipo_entrega" class="form-select" required>
                                <option value="" disabled @selected(! old('tipo_entrega'))>
                                    Selecione...
                                </option>
                                <option value="retira_loja" @selected(old('tipo_entrega') === 'retira_loja')>
                                    Retira Loja
                                </option>
                                <option value="entrega" @selected(old('tipo_entrega') === 'entrega')>
                                    Entrega
                                </option>
                            </select>
                        </div>
                    </div>

                    <div id="deliveryDetails" class="campo-entrega d-none mt-3">
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">
                                    Usar endereço cadastrado?
                                </label>
                                <select
                                    name="usar_endereco_cliente"
                                    id="usar_endereco_cadastrado"
                                    class="form-select"
                                >
                                    <option value="sim" @selected(old('usar_endereco_cliente', 'sim') === 'sim')>
                                        Sim, usar endereço cadastrado
                                    </option>
                                    <option value="nao" @selected(old('usar_endereco_cliente') === 'nao')>
                                        Não, informar outro endereço
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 col-xl-4">
                                <label class="form-label">
                                    Data Prevista <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="data_prevista_entrega"
                                    id="data_prevista_entrega"
                                    class="form-control"
                                    value="{{ old('data_prevista_entrega') }}"
                                >
                            </div>

                            <div class="col-md-3 col-xl-4">
                                <label class="form-label">
                                    Período <span class="text-danger">*</span>
                                </label>
                                <select
                                    name="periodo_entrega"
                                    id="periodo_entrega"
                                    class="form-select"
                                >
                                    <option value="">Selecione...</option>
                                    <option value="manha" @selected(old('periodo_entrega') === 'manha')>
                                        Manhã
                                    </option>
                                    <option value="tarde" @selected(old('periodo_entrega') === 'tarde')>
                                        Tarde
                                    </option>
                                    <option value="comercial" @selected(old('periodo_entrega') === 'comercial')>
                                        Horário Comercial
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div id="registeredAddressPanel" class="registered-address mt-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark mb-1">
                                        Endereço cadastrado do cliente
                                    </div>
                                    <div id="registeredAddressText">
                                        Selecione um cliente para visualizar o endereço.
                                    </div>
                                    <div id="registeredAddressWarning" class="small text-danger mt-1 d-none">
                                        O endereço cadastrado está incompleto. Corrija o cliente ou informe outro endereço.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="alternateAddressPanel" class="delivery-address-panel d-none mt-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <div>
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-signpost-2 me-1 text-primary"></i>
                                        Novo endereço desta entrega
                                    </div>
                                    <small class="text-muted">
                                        Informe o CEP para preencher automaticamente os dados do destino.
                                    </small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary align-self-start px-3 py-2">
                                    ViaCEP
                                </span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-5 col-lg-4 col-xl-3">
                                    <label class="form-label">
                                        CEP <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group cep-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-geo"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="cep_entrega"
                                            id="cep_entrega"
                                            class="form-control"
                                            value="{{ old('cep_entrega') }}"
                                            placeholder="00000-000"
                                            inputmode="numeric"
                                            maxlength="9"
                                            autocomplete="postal-code"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary"
                                            id="buscarCepEntrega"
                                        >
                                            <i class="bi bi-search me-1"></i>
                                            Buscar
                                        </button>
                                    </div>
                                    <div id="cepFeedback" class="cep-status text-muted">
                                        Digite os 8 números do CEP.
                                    </div>
                                </div>

                                <div class="col-md-7 col-lg-8 col-xl-6">
                                    <label class="form-label">
                                        Logradouro <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="endereco_entrega"
                                        id="endereco_entrega"
                                        class="form-control"
                                        value="{{ old('endereco_entrega') }}"
                                        placeholder="Rua, avenida ou estrada"
                                        autocomplete="address-line1"
                                    >
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label">
                                        Número <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="numero_entrega"
                                        id="numero_entrega"
                                        class="form-control"
                                        value="{{ old('numero_entrega') }}"
                                        placeholder="Número ou S/N"
                                        autocomplete="address-line2"
                                    >
                                </div>

                                <div class="col-md-8 col-lg-5">
                                    <label class="form-label">Complemento</label>
                                    <input
                                        type="text"
                                        name="complemento_entrega"
                                        id="complemento_entrega"
                                        class="form-control"
                                        value="{{ old('complemento_entrega') }}"
                                        placeholder="Casa, bloco, sala ou referência"
                                    >
                                </div>

                                <div class="col-md-5 col-lg-4">
                                    <label class="form-label">
                                        Bairro <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="bairro_entrega"
                                        id="bairro_entrega"
                                        class="form-control"
                                        value="{{ old('bairro_entrega') }}"
                                        autocomplete="address-level3"
                                    >
                                </div>

                                <div class="col-md-5 col-lg-5">
                                    <label class="form-label">
                                        Cidade <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="cidade_entrega"
                                        id="cidade_entrega"
                                        class="form-control"
                                        value="{{ old('cidade_entrega') }}"
                                        autocomplete="address-level2"
                                    >
                                </div>

                                <div class="col-md-2 col-lg-3">
                                    <label class="form-label">
                                        UF <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="uf_entrega"
                                        id="uf_entrega"
                                        class="form-control text-uppercase"
                                        value="{{ old('uf_entrega') }}"
                                        placeholder="UF"
                                        maxlength="2"
                                        autocomplete="address-level1"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">
                                    Responsável pelo Recebimento
                                </label>
                                <input
                                    type="text"
                                    name="contato_entrega"
                                    id="contato_entrega"
                                    class="form-control"
                                    value="{{ old('contato_entrega') }}"
                                    placeholder="Nome de quem receberá o pedido"
                                >
                            </div>

                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">
                                    Telefone do Responsável
                                </label>
                                <input
                                    type="text"
                                    name="telefone_entrega"
                                    id="telefone_entrega"
                                    class="form-control"
                                    value="{{ old('telefone_entrega') }}"
                                    placeholder="(00) 00000-0000"
                                    inputmode="tel"
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="col-xl-4">
                                <label class="form-label">
                                    Observação da Entrega
                                </label>
                                <textarea
                                    name="observacao_entrega"
                                    id="observacao_entrega"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Referência, restrição de acesso ou horário combinado"
                                >{{ old('observacao_entrega') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                            {{-- Itens --}}
                            <div class="form-section mb-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-box-seam"></i>
                                        Itens do Orçamento
                                    </h5>
                                </div>

                                <div class="table-responsive table-shell">
                                    <table class="table table-bordered table-hover align-middle bg-white mb-0">
                                        <thead class="table-dark text-center">
                                            <tr>
                                                <th>Produto</th>
                                                <th>Lote</th>
                                                <th style="width: 120px;">Quantidade</th>
                                                <th>Unidade</th>
                                                <th>Preço</th>
                                                <th>Subtotal</th>
                                                <th style="width: 80px;">Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensContainer"></tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-primary" id="addProduto">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Adicionar Produto
                                    </button>
                                </div>
                            </div>

                            {{-- Resumo Financeiro --}}
                            <div class="financial-summary-card">
                                {{-- Linha 1: cabeçalho --}}
                                <div class="financial-summary-header">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Resumo Financeiro</span>
                                </div>

                                {{-- Linha 2: três divisões laterais --}}
                                <div class="financial-summary-data">
                                    <div class="financial-data-column">
                                        <span class="financial-data-label">
                                            Total Bruto:
                                        </span>
                                        <span class="financial-data-value">
                                            R$ <span id="totalBruto">0,00</span>
                                        </span>
                                    </div>

                                    <div class="financial-data-column financial-data-discount">
                                        <label for="descontoGlobal" class="financial-data-label">
                                            Desconto (%):
                                        </label>
                                        <input
                                            type="number"
                                            name="desconto_global"
                                            id="descontoGlobal"
                                            class="form-control"
                                            min="0"
                                            max="100"
                                            value="0"
                                            step="1"
                                        >
                                        <span class="financial-data-discount-value">
                                            Total: R$ <span id="totalDesconto">0,00</span>
                                        </span>
                                    </div>

                                    <div class="financial-data-column financial-data-final">
                                        <span class="financial-data-label">
                                            Valor com Desconto:
                                        </span>
                                        <span class="financial-data-value">
                                            R$ <span id="totalComDesconto">0,00</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="total_bruto_calculado" id="totalBrutoInput" value="0.00">
                            <input type="hidden" name="total_desconto_calculado" id="totalDescontoInput" value="0.00">
                            <input type="hidden" name="total_liquido_calculado" id="totalLiquidoInput" value="0.00">

                            {{-- Botões --}}
                            <div class="action-footer d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                                <a href="{{ route('orcamentos.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-arrow-left-circle me-1"></i>
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-success px-4" id="btnSalvar">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Salvar Orçamento
                                </button>
                            </div>

                            <!-- <div class="mb-3 bg-secondary p-3 rounded">
                                <label class="form-label text-warning d-block fw-bold">Observações:</label>
                                <textarea name="observacoes" class="form-control" rows="1">Sem observações</textarea>
                            </div> -->
                        </div>
                    </div>
                </form>
    </div>

<script>
  document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const tableBody = document.getElementById('itensContainer');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');

        const totalBrutoSpan = document.getElementById('totalBruto');
        const descontoGlobalInput = document.getElementById('descontoGlobal');
        const totalDescontoSpan = document.getElementById('totalDesconto');
        const totalComDescontoSpan = document.getElementById('totalComDesconto');

        const totalBrutoInput = document.getElementById('totalBrutoInput');
        const totalDescontoInput = document.getElementById('totalDescontoInput');
        const totalLiquidoInput = document.getElementById('totalLiquidoInput');

        let index = 0;

        function getProdutosSelecionados() {
            const selecionados = [];

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });

            return selecionados;
        }

        function atualizarOpcoesProdutos() {
            const selecionados = getProdutosSelecionados();

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {

                const valorAtual = select.value;

                select.querySelectorAll('option').forEach(option => {

                    if (!option.value) return;

                    if (option.value === valorAtual) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selecionados.includes(option.value);
                });
            });
        }

        function criarItem() {

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                        <option value="">Selecione...</option>
                        ${produtos.map(p => `
                            <option value="${p.id}"
                                data-preco="${p.preco_venda}"
                                data-unidade="${p.unidade_medida?.nome || ''}">
                                ${p.id} - ${p.nome}
                            </option>
                        `).join('')}
                    </select>
                </td>

                <td>
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </td>

                <td>
                    <input type="number"
                        name="produtos[${index}][quantidade]"
                        class="form-control qtd"
                        value="1" min="1" required>
                </td>

                <td>
                    <span class="unidadeLabel"></span>
                    <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
                </td>

                <td>
                    <span class="precoLabel">0,00</span>
                    <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
                </td>

                <td>
                    <span class="subtotalLabel">0,00</span>
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(tr);
            index++;

            atualizarOpcoesProdutos();
        }

        function atualizarTotal() {
            let totalBruto = 0;
            let menorDescontoMaximoPermitido = 100;
            let produtoLimitanteNome = "";

            let percentualDesconto = parseFloat(descontoGlobalInput?.value) || 0;

            tableBody.querySelectorAll('tr').forEach(tr => {
                const produtoSelect = tr.querySelector('.produtoSelect');
                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const precoCobrado = parseFloat(tr.querySelector('.preco').value) || 0;
                const subtotal = qtd * precoCobrado;

                tr.querySelector('.subtotalLabel').textContent = "R$ " + subtotal.toFixed(2).replace('.', ',');
                totalBruto += subtotal;

                if (produtoSelect && produtoSelect.value) {
                    const produtoId = produtoSelect.value;
                    const produtoDados = produtos.find(p => p.id == produtoId);

                    if (produtoDados) {
                        const pv1 = parseFloat(produtoDados.preco_venda) || 0;
                        const pv2 = parseFloat(produtoDados.preco_venda_2) || 0;
                        const pv3 = parseFloat(produtoDados.preco_venda_3) || 0;

                        const descMax1 = parseFloat(produtoDados.desconto_max_1) || 0;
                        const descMax2 = parseFloat(produtoDados.desconto_max_2) || 0;
                        const descMax3 = parseFloat(produtoDados.desconto_max_3) || 0;

                        let descMaxProduto = descMax1;

                        if (Math.abs(precoCobrado - pv2) < 0.01) {
                            descMaxProduto = descMax2;
                        } else if (Math.abs(precoCobrado - pv3) < 0.01) {
                            descMaxProduto = descMax3;
                        } else if (Math.abs(precoCobrado - pv1) >= 0.01) {
                            descMaxProduto = Math.min(descMax1, descMax2, descMax3);
                        }

                        if (descMaxProduto < menorDescontoMaximoPermitido) {
                            menorDescontoMaximoPermitido = descMaxProduto;
                            produtoLimitanteNome = produtoDados.nome || "";
                        }
                    }
                }
            });

            if (percentualDesconto > menorDescontoMaximoPermitido) {
                alert(`Atenção! O desconto de ${percentualDesconto}% excede o limite máximo permitido de ${menorDescontoMaximoPermitido}% definido pelo Markup para o produto: ${produtoLimitanteNome}. Valor reajustado.`);
                percentualDesconto = menorDescontoMaximoPermitido;

                if (descontoGlobalInput) {
                    descontoGlobalInput.value = menorDescontoMaximoPermitido;
                }
            }

            const valorDesconto = totalBruto * (percentualDesconto / 100);
            const totalComDesconto = totalBruto - valorDesconto;

            if (totalBrutoSpan) {
                totalBrutoSpan.textContent = totalBruto.toFixed(2).replace('.', ',');
            }

            if (totalDescontoSpan) {
                totalDescontoSpan.textContent = valorDesconto.toFixed(2).replace('.', ',');
            }

            if (totalComDescontoSpan) {
                totalComDescontoSpan.textContent = totalComDesconto.toFixed(2).replace('.', ',');
            }

            if (totalBrutoInput) {
                totalBrutoInput.value = totalBruto.toFixed(2);
            }

            if (totalDescontoInput) {
                totalDescontoInput.value = valorDesconto.toFixed(2);
            }

            if (totalLiquidoInput) {
                totalLiquidoInput.value = totalComDesconto.toFixed(2);
            }
        }

        tableBody.addEventListener('change', e => {

            if (!e.target.classList.contains('produtoSelect')) return;

            if (!clienteSelect.value) {
                alert('Selecione o cliente primeiro!');
                e.target.value = '';
                return;
            }

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const tr = e.target.closest('tr');

            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

            tr.querySelector('.unidade').value = unidade;
            tr.querySelector('.unidadeLabel').textContent = unidade;

            const loteSelect = tr.querySelector('.loteSelect');
            loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

            if (!produto || !produto.lotes) return;

            const lotesValidos = produto.lotes.filter(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                return l.status == 1 && disponivel > 0;
            });

            if (lotesValidos.length === 0) {
                loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                return;
            }

            lotesValidos.forEach(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                loteSelect.innerHTML += `
                    <option value="${l.id}">
                        ${l.numero_lote} | Qtd: ${disponivel}
                    </option>
                `;
            });

            atualizarOpcoesProdutos();
            atualizarTotal();
        });

        tableBody.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        if (descontoGlobalInput) {
            descontoGlobalInput.addEventListener('input', atualizarTotal);
        }

       tableBody.addEventListener('click', e => {
            // Captura o botão mesmo se clicar no ícone <i> interno
            const botaoRemover = e.target.closest('.remover');

            if (botaoRemover) {
                // Remove o <tr> correspondente da tabela
                botaoRemover.closest('tr').remove();
                
                // Executa suas funções originais de recalcular o PDV
                atualizarOpcoesProdutos();
                atualizarTotal();
            }
        });


        addBtn.addEventListener('click', () => {

            if (!clienteSelect.value) {
                alert('Selecione um cliente primeiro!');
                return;
            }

            const lastRow = tableBody.querySelector('tr:last-child');

            if (lastRow) {
                const produto = lastRow.querySelector('.produtoSelect')?.value;
                const lote = lastRow.querySelector('.loteSelect')?.value;

                if (!produto || !lote) {
                    alert('Preencha o produto e o lote da linha anterior antes de adicionar um novo!');
                    return;
                }
            }

            criarItem();
        });

        atualizarTotal();
    });
</script>

<!-- Atendimento, endereço e busca por CEP -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoEntrega = document.getElementById('tipo_entrega');
        const deliveryDetails = document.getElementById('deliveryDetails');
        const usarEnderecoCadastrado = document.getElementById('usar_endereco_cadastrado');
        const clienteSelect = document.getElementById('clienteSelect');

        const registeredAddressPanel = document.getElementById('registeredAddressPanel');
        const registeredAddressText = document.getElementById('registeredAddressText');
        const registeredAddressWarning = document.getElementById('registeredAddressWarning');
        const alternateAddressPanel = document.getElementById('alternateAddressPanel');

        const dataPrevistaEntrega = document.getElementById('data_prevista_entrega');
        const periodoEntrega = document.getElementById('periodo_entrega');
        const enderecoEntrega = document.getElementById('endereco_entrega');
        const numeroEntrega = document.getElementById('numero_entrega');
        const complementoEntrega = document.getElementById('complemento_entrega');
        const bairroEntrega = document.getElementById('bairro_entrega');
        const cidadeEntrega = document.getElementById('cidade_entrega');
        const ufEntrega = document.getElementById('uf_entrega');
        const cepEntrega = document.getElementById('cep_entrega');
        const buscarCepEntrega = document.getElementById('buscarCepEntrega');
        const cepFeedback = document.getElementById('cepFeedback');
        const contatoEntrega = document.getElementById('contato_entrega');
        const telefoneEntrega = document.getElementById('telefone_entrega');
        const observacaoEntrega = document.getElementById('observacao_entrega');

        const camposEnderecoAlternativo = [
            cepEntrega,
            enderecoEntrega,
            numeroEntrega,
            complementoEntrega,
            bairroEntrega,
            cidadeEntrega,
            ufEntrega
        ].filter(Boolean);

        const camposObrigatoriosEndereco = [
            cepEntrega,
            enderecoEntrega,
            numeroEntrega,
            bairroEntrega,
            cidadeEntrega,
            ufEntrega
        ].filter(Boolean);

        let cepConsultado = '';
        let consultaCepController = null;

        function somenteNumeros(valor) {
            return String(valor || '').replace(/\D/g, '');
        }

        function formatarCep(valor) {
            const numeros = somenteNumeros(valor).slice(0, 8);

            if (numeros.length <= 5) {
                return numeros;
            }

            return `${numeros.slice(0, 5)}-${numeros.slice(5)}`;
        }

        function obterClienteSelecionado() {
            if (!clienteSelect?.value) {
                return null;
            }

            return clienteSelect.options[clienteSelect.selectedIndex];
        }

        function atualizarResumoEnderecoCadastrado() {
            const cliente = obterClienteSelecionado();

            if (!cliente) {
                registeredAddressText.textContent =
                    'Selecione um cliente para visualizar o endereço.';
                registeredAddressWarning.classList.add('d-none');
                return;
            }

            const endereco = (cliente.dataset.endereco || '').trim();
            const numero = (cliente.dataset.numero || '').trim();
            const complemento = (cliente.dataset.complemento || '').trim();
            const bairro = (cliente.dataset.bairro || '').trim();
            const cidade = (cliente.dataset.cidade || '').trim();
            const estado = (cliente.dataset.estado || '').trim().toUpperCase();
            const cep = formatarCep(cliente.dataset.cep || '');

            const enderecoCompleto = [
                endereco,
                numero ? `Nº ${numero}` : '',
                complemento,
                bairro,
                cidade && estado ? `${cidade} - ${estado}` : cidade || estado,
                cep ? `CEP ${cep}` : ''
            ].filter(Boolean);

            registeredAddressText.textContent =
                enderecoCompleto.length > 0
                    ? enderecoCompleto.join(', ')
                    : 'Nenhum endereço cadastrado.';

            const cadastroCompleto =
                endereco !== ''
                && numero !== ''
                && bairro !== ''
                && cidade !== ''
                && estado.length === 2
                && somenteNumeros(cep).length === 8;

            registeredAddressWarning.classList.toggle(
                'd-none',
                cadastroCompleto
            );

            if (!contatoEntrega.value.trim()) {
                contatoEntrega.value =
                    cliente.textContent.trim();
            }

            if (!telefoneEntrega.value.trim()) {
                telefoneEntrega.value =
                    cliente.dataset.telefone || '';
            }
        }

        function definirStatusCep(mensagem, tipo = 'muted') {
            if (!cepFeedback || !cepEntrega) {
                return;
            }

            const classes = {
                muted: 'text-muted',
                loading: 'text-primary',
                success: 'text-success',
                danger: 'text-danger'
            };

            cepFeedback.className =
                `cep-status ${classes[tipo] || classes.muted}`;

            cepFeedback.textContent = mensagem;
            cepEntrega.classList.toggle(
                'is-invalid',
                tipo === 'danger'
            );
            cepEntrega.classList.toggle(
                'is-valid',
                tipo === 'success'
            );
        }

        function configurarCamposEnderecoAlternativo(ativo) {
            camposEnderecoAlternativo.forEach(campo => {
                campo.disabled = !ativo;
            });

            camposObrigatoriosEndereco.forEach(campo => {
                campo.required = ativo;
            });

            if (buscarCepEntrega) {
                buscarCepEntrega.disabled = !ativo;
            }
        }

        function atualizarModoEndereco() {
            const usarCadastro =
                usarEnderecoCadastrado?.value === 'sim';

            registeredAddressPanel?.classList.toggle(
                'd-none',
                !usarCadastro
            );

            alternateAddressPanel?.classList.toggle(
                'd-none',
                usarCadastro
            );

            configurarCamposEnderecoAlternativo(
                !usarCadastro
            );

            if (usarCadastro) {
                atualizarResumoEnderecoCadastrado();
            } else if (!cepEntrega?.value) {
                definirStatusCep(
                    'Digite os 8 números do CEP.'
                );
            }
        }

        function atualizarModalidadeEntrega() {
            const entrega =
                tipoEntrega?.value === 'entrega';

            deliveryDetails?.classList.toggle(
                'd-none',
                !entrega
            );

            [
                usarEnderecoCadastrado,
                dataPrevistaEntrega,
                periodoEntrega,
                contatoEntrega,
                telefoneEntrega,
                observacaoEntrega
            ]
                .filter(Boolean)
                .forEach(campo => {
                    campo.disabled = !entrega;
                });

            if (dataPrevistaEntrega) {
                dataPrevistaEntrega.required = entrega;
            }

            if (periodoEntrega) {
                periodoEntrega.required = entrega;
            }

            if (!entrega) {
                configurarCamposEnderecoAlternativo(false);
                return;
            }

            atualizarModoEndereco();
        }

        async function consultarCep(forcarMensagem = false) {
            if (
                tipoEntrega?.value !== 'entrega'
                || usarEnderecoCadastrado?.value !== 'nao'
                || !cepEntrega
            ) {
                return;
            }

            const cep = somenteNumeros(
                cepEntrega.value
            );

            if (cep.length !== 8) {
                if (forcarMensagem || cep.length > 0) {
                    definirStatusCep(
                        'Informe um CEP com 8 números.',
                        'danger'
                    );
                }
                return;
            }

            if (
                cep === cepConsultado
                && enderecoEntrega?.value
                && cidadeEntrega?.value
                && ufEntrega?.value
            ) {
                return;
            }

            consultaCepController?.abort();
            consultaCepController = new AbortController();

            definirStatusCep(
                'Consultando CEP...',
                'loading'
            );

            if (buscarCepEntrega) {
                buscarCepEntrega.disabled = true;
                buscarCepEntrega.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Buscando';
            }

            try {
                const resposta = await fetch(
                    `https://viacep.com.br/ws/${cep}/json/`,
                    {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json'
                        },
                        signal: consultaCepController.signal
                    }
                );

                if (!resposta.ok) {
                    throw new Error(
                        'Não foi possível consultar o CEP.'
                    );
                }

                const dados = await resposta.json();

                if (dados.erro === true) {
                    throw new Error(
                        'CEP não encontrado.'
                    );
                }

                cepEntrega.value =
                    formatarCep(dados.cep || cep);

                enderecoEntrega.value =
                    dados.logradouro || '';

                bairroEntrega.value =
                    dados.bairro || '';

                cidadeEntrega.value =
                    dados.localidade || '';

                ufEntrega.value =
                    String(dados.uf || '').toUpperCase();

                cepConsultado = cep;

                definirStatusCep(
                    'Endereço localizado. Confira os dados e informe o número.',
                    'success'
                );

                if (numeroEntrega) {
                    numeroEntrega.focus();
                }
            } catch (erro) {
                if (erro.name === 'AbortError') {
                    return;
                }

                cepConsultado = '';

                definirStatusCep(
                    erro.message
                    || 'Não foi possível consultar o CEP. Tente novamente.',
                    'danger'
                );
            } finally {
                if (buscarCepEntrega) {
                    buscarCepEntrega.disabled = false;
                    buscarCepEntrega.innerHTML =
                        '<i class="bi bi-search me-1"></i> Buscar';
                }
            }
        }

        tipoEntrega?.addEventListener(
            'change',
            atualizarModalidadeEntrega
        );

        usarEnderecoCadastrado?.addEventListener(
            'change',
            atualizarModoEndereco
        );

        clienteSelect?.addEventListener(
            'change',
            atualizarResumoEnderecoCadastrado
        );

        cepEntrega?.addEventListener(
            'input',
            function () {
                const cepAnterior =
                    somenteNumeros(this.value);

                this.value =
                    formatarCep(this.value);

                const cepAtual =
                    somenteNumeros(this.value);

                if (
                    cepConsultado
                    && cepAtual !== cepConsultado
                ) {
                    cepConsultado = '';
                    this.classList.remove(
                        'is-valid',
                        'is-invalid'
                    );
                }

                if (
                    cepAtual.length === 8
                    && cepAtual !== cepAnterior.slice(0, 8)
                ) {
                    consultarCep();
                    return;
                }

                if (cepAtual.length === 8) {
                    consultarCep();
                }
            }
        );

        cepEntrega?.addEventListener(
            'blur',
            function () {
                consultarCep(
                    somenteNumeros(this.value).length > 0
                );
            }
        );

        buscarCepEntrega?.addEventListener(
            'click',
            function () {
                consultarCep(true);
            }
        );

        telefoneEntrega?.addEventListener(
            'input',
            function () {
                const numeros =
                    somenteNumeros(this.value)
                        .slice(0, 11);

                if (numeros.length <= 10) {
                    this.value = numeros
                        .replace(
                            /^(\d{0,2})(\d{0,4})(\d{0,4}).*/,
                            function (
                                _,
                                ddd,
                                primeiraParte,
                                segundaParte
                            ) {
                                let telefone = '';

                                if (ddd) {
                                    telefone += `(${ddd}`;
                                }

                                if (ddd.length === 2) {
                                    telefone += ') ';
                                }

                                telefone += primeiraParte;

                                if (segundaParte) {
                                    telefone += `-${segundaParte}`;
                                }

                                return telefone;
                            }
                        );
                    return;
                }

                this.value = numeros.replace(
                    /^(\d{2})(\d{5})(\d{4})$/,
                    '($1) $2-$3'
                );
            }
        );

        atualizarResumoEnderecoCadastrado();
        atualizarModalidadeEntrega();

        if (
            tipoEntrega?.value === 'entrega'
            && usarEnderecoCadastrado?.value === 'nao'
            && somenteNumeros(cepEntrega?.value).length === 8
        ) {
            consultarCep();
        }
    });
</script>

<!-- Bloqueio de Salvamento, clique ou enter acidental -->
<script>
    const btnSalvar = document.getElementById('btnSalvar');
    const formOrcamento = btnSalvar?.closest('form');

    let salvandoOrcamento = false;

    if (formOrcamento) {
        formOrcamento.addEventListener('submit', function (e) {

            if (salvandoOrcamento) {
                e.preventDefault();
                return false;
            }

            salvandoOrcamento = true;

            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
            }
        });
    }
</script>
<script src="{{ asset('js/orcamento.js') }}"></script>

@endsection