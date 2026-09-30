

<?php $__env->startSection('content'); ?>
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
            linear-gradient(135deg, rgba(37, 99, 235, .10), rgba(14, 165, 233, .03)),
            #fff;
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
        transition: transform .18s ease, box-shadow .18s ease;
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

    .orcamento-create-page .coordinate-status {
        color: #667085;
        font-size: .82rem;
        font-weight: 650;
    }

    .orcamento-create-page .coordinate-status.is-loading {
        color: #2563eb;
    }

    .orcamento-create-page .coordinate-status.is-pending {
        color: #b45309;
    }

    .orcamento-create-page .coordinate-status.is-confirmed {
        color: #047857;
    }

    .orcamento-create-page .coordinate-status.is-error {
        color: #b42318;
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

    .orcamento-create-page .financial-summary-card {
        width: 100%;
        margin-bottom: .75rem;
        overflow: hidden;
        border: 1px solid #ccebdd;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 4px 13px rgba(16, 24, 40, .05);
    }

    /* Linha 1: cabeçalho */
    .orcamento-create-page .financial-summary-header {
        min-height: 36px;
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .75rem;
        color: #fff;
        background: linear-gradient(135deg, #087f5b, #0f9f6e);
        font-size: .82rem;
        font-weight: 700;
    }

    /* Linha 2: três divisões laterais */
    .orcamento-create-page .financial-summary-data {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .orcamento-create-page .financial-data-column {
        min-width: 0;
        min-height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .9rem;
        padding: .4rem .75rem;
        border-left: 1px solid #e3ede8;
        white-space: nowrap;
    }

    .orcamento-create-page .financial-data-column:first-child {
        border-left: 0;
    }

    .orcamento-create-page .financial-data-label {
        color: #667085;
        font-size: .78rem;
        font-weight: 700;
    }

    .orcamento-create-page .financial-data-value {
        color: #172033;
        font-size: 1rem;
        font-weight: 750;
    }

    .orcamento-create-page .financial-data-discount {
        background: #fffafb;
    }

    .orcamento-create-page .financial-data-discount label {
        color: var(--orc-danger);
    }

    .orcamento-create-page .financial-data-discount .form-control {
        flex: 0 0 64px;
        width: 64px;
        min-height: 32px;
        height: 32px;
        padding: .15rem .3rem;
        color: var(--orc-danger);
        border-color: #f2c9cf;
        font-weight: 750;
        text-align: center;
    }

    .orcamento-create-page .financial-data-discount-value {
        color: #667085;
        font-size: .76rem;
    }

    .orcamento-create-page .financial-data-final {
        background: #ecfdf5;
    }

    .orcamento-create-page .financial-data-final .financial-data-label,
    .orcamento-create-page .financial-data-final .financial-data-value {
        color: #087f5b;
    }

    .orcamento-create-page .financial-data-final .financial-data-value {
        font-size: 1.08rem;
    }

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

    /* Compactação visual aproximada de 40% */
    .orcamento-create-page {
        font-size: .875rem;
    }

    .orcamento-create-page .page-heading {
        border-left-width: 4px;
        border-radius: 9px;
        padding: .5rem .75rem;
        box-shadow: 0 4px 13px rgba(16, 24, 40, .045);
    }

    .orcamento-create-page .page-heading.mb-3,
    .orcamento-create-page > .row.mb-3,
    .orcamento-create-page .main-form-card.mb-3 {
        margin-bottom: .6rem !important;
    }

    .orcamento-create-page .page-heading h2 {
        font-size: clamp(1.05rem, 1.5vw, 1.25rem);
        line-height: 1.15;
    }

    .orcamento-create-page .page-heading h2.mb-1 {
        margin-bottom: .15rem !important;
    }

    .orcamento-create-page .page-heading p {
        font-size: .74rem;
        line-height: 1.2;
    }

    .orcamento-create-page .info-card {
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(16, 24, 40, .035) !important;
    }

    .orcamento-create-page .info-card .card-body {
        min-height: 40px;
        padding: .34rem .52rem;
    }

    .orcamento-create-page .info-card .fw-bold {
        font-size: .82rem;
        line-height: 1.1;
    }

    .orcamento-create-page .info-card small {
        font-size: .67rem;
        line-height: 1.1;
    }

    .orcamento-create-page .info-icon {
        width: 24px;
        height: 24px;
        flex-basis: 24px;
        border-radius: 6px;
        font-size: .76rem;
    }

    .orcamento-create-page .info-icon.me-2 {
        margin-right: .42rem !important;
    }

    .orcamento-create-page .main-form-card {
        border-radius: 9px;
        box-shadow: 0 5px 17px rgba(16, 24, 40, .05) !important;
    }

    .orcamento-create-page .main-form-card > .card-header {
        min-height: 31px;
        padding: .32rem .65rem;
        font-size: .82rem;
    }

    .orcamento-create-page .main-form-card > .card-header .badge {
        padding: .25rem .45rem;
        font-size: .66rem;
    }

    .orcamento-create-page .main-form-card > .card-body {
        padding: .5rem;
    }

    .orcamento-create-page .form-section {
        border-radius: 8px !important;
        padding: .54rem !important;
        box-shadow: 0 3px 10px rgba(16, 24, 40, .03);
    }

    .orcamento-create-page .form-section.mb-3 {
        margin-bottom: .6rem !important;
    }

    .orcamento-create-page .form-section .mb-3 {
        margin-bottom: .52rem !important;
    }

    .orcamento-create-page .form-section .mt-3 {
        margin-top: .52rem !important;
    }

    .orcamento-create-page .form-section .g-3 {
        --bs-gutter-x: .7rem;
        --bs-gutter-y: .55rem;
    }

    .orcamento-create-page .form-section h5 {
        gap: .3rem;
        margin-bottom: .45rem !important;
        font-size: .86rem;
        line-height: 1.15;
    }

    .orcamento-create-page .form-label {
        margin-bottom: .18rem;
        font-size: .75rem;
        line-height: 1.15;
    }

    .orcamento-create-page .form-control,
    .orcamento-create-page .form-select {
        min-height: 32px;
        border-radius: 7px;
        padding-top: .28rem;
        padding-bottom: .28rem;
        font-size: .8rem;
        line-height: 1.2;
    }

    .orcamento-create-page textarea.form-control {
        min-height: 46px;
    }

    .orcamento-create-page .badge {
        font-size: .68rem;
    }

    .orcamento-create-page .delivery-address-panel {
        border-radius: 8px;
        padding: .58rem;
    }

    .orcamento-create-page .registered-address {
        border-radius: 7px;
        padding: .5rem .62rem;
        font-size: .76rem;
        line-height: 1.2;
    }

    .orcamento-create-page .coordinate-status {
        font-size: .7rem;
        line-height: 1.2;
    }

    .orcamento-create-page .cep-group .input-group-text {
        padding: .28rem .5rem;
    }

    .orcamento-create-page .cep-status {
        min-height: 14px;
        margin-top: .18rem;
        font-size: .68rem;
    }

    .orcamento-create-page .table-shell {
        border-radius: 8px;
    }

    .orcamento-create-page .table-shell thead th {
        padding: .45rem .5rem;
        font-size: .68rem;
    }

    .orcamento-create-page .table-shell tbody td {
        padding: .38rem .45rem;
        font-size: .76rem;
    }

    .orcamento-create-page .financial-summary-card {
        margin-bottom: .45rem;
        border-radius: 7px;
    }

    .orcamento-create-page .financial-summary-header {
        min-height: 25px;
        padding: .24rem .5rem;
        font-size: .72rem;
    }

    .orcamento-create-page .financial-data-column {
        min-height: 34px;
        gap: .5rem;
        padding: .24rem .5rem;
    }

    .orcamento-create-page .financial-data-label,
    .orcamento-create-page .financial-data-discount-value {
        font-size: .68rem;
    }

    .orcamento-create-page .financial-data-value,
    .orcamento-create-page .financial-data-final .financial-data-value {
        font-size: .82rem;
    }

    .orcamento-create-page .financial-data-discount .form-control {
        min-height: 27px;
        height: 27px;
        font-size: .74rem;
    }

    .orcamento-create-page .action-footer {
        margin: 0 -.5rem -.5rem;
        padding: .55rem;
    }

    .orcamento-create-page .btn {
        min-height: 30px;
        border-radius: 7px;
        padding-top: .28rem;
        padding-bottom: .28rem;
        font-size: .78rem;
        line-height: 1.2;
    }

    .orcamento-create-page .btn.px-3,
    .orcamento-create-page .btn.px-4 {
        padding-left: .75rem !important;
        padding-right: .75rem !important;
    }

    .orcamento-create-page .alert {
        margin-bottom: .6rem;
        padding: .5rem .7rem;
        font-size: .78rem;
    }

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
            grid-template-columns: minmax(0, 1fr);
        }

        .orcamento-create-page .financial-data-column {
            border-top: 1px solid #e3ede8;
            border-left: 0;
        }

        .orcamento-create-page .financial-data-column:first-child {
            border-top: 0;
        }
    }
</style>

<div class="container-fluid px-3 px-xl-4 orcamento-create-page">

    
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

        <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary btn-sm px-3">
            <i class="bi bi-arrow-left-circle me-1"></i>
            Voltar
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger shadow-sm">
            <strong>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Erro!
            </strong>
            Verifique os campos obrigatórios.
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
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

    <form action="<?php echo e(route('orcamentos.store')); ?>" method="POST" id="formOrcamento">
        <?php echo csrf_field(); ?>

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
                                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($cliente->id); ?>"
                                        <?php if((string) old('cliente_id') === (string) $cliente->id): echo 'selected'; endif; ?>
                                        data-endereco="<?php echo e($cliente->endereco ?? ''); ?>"
                                        data-numero="<?php echo e($cliente->numero ?? ''); ?>"
                                        data-complemento="<?php echo e($cliente->complemento ?? ''); ?>"
                                        data-bairro="<?php echo e($cliente->bairro ?? ''); ?>"
                                        data-cidade="<?php echo e($cliente->cidade ?? ''); ?>"
                                        data-estado="<?php echo e($cliente->estado ?? ''); ?>"
                                        data-cep="<?php echo e($cliente->cep ?? ''); ?>"
                                        data-telefone="<?php echo e($cliente->telefone ?? ''); ?>"
                                    >
                                        <?php echo e($cliente->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Data</label>
                            <input type="date" name="data_orcamento" class="form-control"
                                   value="<?php echo e(old('data_orcamento', date('Y-m-d'))); ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control"
                                   value="<?php echo e(old('validade', date('Y-m-d', strtotime('+7 days')))); ?>">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <span class="badge bg-warning text-dark p-2 w-100">
                                <i class="bi bi-clock me-1"></i>
                                7 dias
                            </span>
                        </div>
                    </div>
                </div>

                
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
                                <option value="" disabled <?php if(! old('tipo_entrega')): echo 'selected'; endif; ?>>
                                    Selecione...
                                </option>
                                <option value="retira_loja" <?php if(old('tipo_entrega') === 'retira_loja'): echo 'selected'; endif; ?>>
                                    Retira Loja
                                </option>
                                <option value="entrega" <?php if(old('tipo_entrega') === 'entrega'): echo 'selected'; endif; ?>>
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
                                    <option value="sim" <?php if(old('usar_endereco_cliente', 'sim') === 'sim'): echo 'selected'; endif; ?>>
                                        Sim, usar endereço cadastrado
                                    </option>
                                    <option value="nao" <?php if(old('usar_endereco_cliente') === 'nao'): echo 'selected'; endif; ?>>
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
                                    value="<?php echo e(old('data_prevista_entrega')); ?>"
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
                                    <option value="manha" <?php if(old('periodo_entrega') === 'manha'): echo 'selected'; endif; ?>>
                                        Manhã
                                    </option>
                                    <option value="tarde" <?php if(old('periodo_entrega') === 'tarde'): echo 'selected'; endif; ?>>
                                        Tarde
                                    </option>
                                    <option value="comercial" <?php if(old('periodo_entrega') === 'comercial'): echo 'selected'; endif; ?>>
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
                                            value="<?php echo e(old('cep_entrega')); ?>"
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
                                        value="<?php echo e(old('endereco_entrega')); ?>"
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
                                        value="<?php echo e(old('numero_entrega')); ?>"
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
                                        value="<?php echo e(old('complemento_entrega')); ?>"
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
                                        value="<?php echo e(old('bairro_entrega')); ?>"
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
                                        value="<?php echo e(old('cidade_entrega')); ?>"
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
                                        value="<?php echo e(old('uf_entrega')); ?>"
                                        placeholder="UF"
                                        maxlength="2"
                                        autocomplete="address-level1"
                                    >
                                </div>
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="latitude_entrega"
                            id="latitude_entrega"
                            value="<?php echo e(old('latitude_entrega')); ?>"
                        >

                        <input
                            type="hidden"
                            name="longitude_entrega"
                            id="longitude_entrega"
                            value="<?php echo e(old('longitude_entrega')); ?>"
                        >

                        <input
                            type="hidden"
                            name="coordenada_confirmada"
                            id="coordenada_confirmada"
                            value="<?php echo e(old('coordenada_confirmada', '0')); ?>"
                        >

                        <input
                            type="hidden"
                            name="coordenada_origem"
                            id="coordenada_origem"
                            value="<?php echo e(old('coordenada_origem')); ?>"
                        >

                        <div id="coordenadaEntregaStatus"
                             class="coordinate-status mt-2 <?php echo e($errors->has('coordenada_entrega') ? 'is-error' : ''); ?>"
                             aria-live="polite">
                            <?php echo e($errors->first('coordenada_entrega')
                                ?: 'A localização será preenchida automaticamente pelo endereço.'); ?>

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
                                    value="<?php echo e(old('contato_entrega')); ?>"
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
                                    value="<?php echo e(old('telefone_entrega')); ?>"
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
                                ><?php echo e(old('observacao_entrega')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                            
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

                            
                            <div class="financial-summary-card">
                                
                                <div class="financial-summary-header">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Resumo Financeiro</span>
                                </div>

                                
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

                            
                            <div class="action-footer d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-outline-secondary px-4">
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

        const produtos = <?php echo json_encode($produtos, 15, 512) ?>;
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

<!-- Atendimento, endereço, coordenadas e busca por CEP -->
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

<!-- Geocodificação automática do endereço da entrega -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoEntrega = document.getElementById('tipo_entrega');
        const usarEnderecoCadastrado = document.getElementById('usar_endereco_cadastrado');
        const clienteSelect = document.getElementById('clienteSelect');
        const enderecoCadastradoExibido = document.getElementById('registeredAddressText');
        const enderecoEntrega = document.getElementById('endereco_entrega');
        const numeroEntrega = document.getElementById('numero_entrega');
        const complementoEntrega = document.getElementById('complemento_entrega');
        const bairroEntrega = document.getElementById('bairro_entrega');
        const cidadeEntrega = document.getElementById('cidade_entrega');
        const ufEntrega = document.getElementById('uf_entrega');
        const cepEntrega = document.getElementById('cep_entrega');
        const latitudeEntrega = document.getElementById('latitude_entrega');
        const longitudeEntrega = document.getElementById('longitude_entrega');
        const coordenadaConfirmada = document.getElementById('coordenada_confirmada');
        const coordenadaOrigem = document.getElementById('coordenada_origem');
        const statusCoordenada = document.getElementById('coordenadaEntregaStatus');

        if (
            !latitudeEntrega
            || !longitudeEntrega
            || !coordenadaConfirmada
            || !coordenadaOrigem
            || !statusCoordenada
        ) {
            return;
        }

        const configuracaoGeocodificacao = {
            cepUrl: 'https://cep.awesomeapi.com.br/json/',
            url: <?php echo json_encode((string) config('openstreetmap.geocoding_url'), 15, 512) ?>,
            intervalo: Math.max(
                <?php echo e((int) config('openstreetmap.geocoding_interval_ms')); ?>,
                1000
            ),
        };

        let controladorConsulta = null;
        let sequenciaConsulta = 0;
        let proximaConsultaEm = 0;
        let temporizadorLocalizacao = null;

        function converterCoordenada(valor) {
            const numero = Number(
                String(valor ?? '')
                    .trim()
                    .replace(',', '.')
            );

            return Number.isFinite(numero)
                ? numero
                : null;
        }

        function formatarCoordenada(valor) {
            const numero = converterCoordenada(valor);

            return numero === null
                ? ''
                : numero.toFixed(7);
        }

        function somenteNumeros(valor) {
            return String(valor || '').replace(/\D/g, '');
        }

        function removerCepDaGeolocalizacao(valor) {
            return String(valor || '')
                .replace(
                    /(?:,\s*)?CEP\s*\d{5}-?\d{3}\b/gi,
                    ''
                )
                .replace(
                    /(?:,\s*)?\b\d{5}-\d{3}\b/gi,
                    ''
                )
                .replace(/(?:,\s*){2,}/g, ', ')
                .replace(/^,\s*|,\s*$/g, '')
                .trim();
        }

        function definirStatus(mensagem, tipo) {
            statusCoordenada.textContent = mensagem;
            statusCoordenada.className =
                'coordinate-status mt-2'
                + (tipo ? ' is-' + tipo : '');
        }

        function limparCoordenadas(mensagem = null) {
            latitudeEntrega.value = '';
            longitudeEntrega.value = '';
            coordenadaConfirmada.value = '0';
            coordenadaOrigem.value = '';

            if (mensagem) {
                definirStatus(mensagem, 'pending');
            }
        }

        function aplicarCoordenadas(
            latitude,
            longitude,
            mensagem,
            confirmada = true,
            origem = 'endereco'
        ) {
            const latitudeFormatada =
                formatarCoordenada(latitude);

            const longitudeFormatada =
                formatarCoordenada(longitude);

            if (
                latitudeFormatada === ''
                || longitudeFormatada === ''
            ) {
                limparCoordenadas();
                definirStatus(
                    'As coordenadas retornadas são inválidas.',
                    'error'
                );

                return false;
            }

            latitudeEntrega.value = latitudeFormatada;
            longitudeEntrega.value = longitudeFormatada;
            coordenadaConfirmada.value = confirmada ? '1' : '0';
            coordenadaOrigem.value = origem;

            definirStatus(
                mensagem,
                confirmada ? 'confirmed' : 'pending'
            );

            return true;
        }

        function cancelarConsulta() {
            sequenciaConsulta++;

            if (controladorConsulta) {
                controladorConsulta.abort();
                controladorConsulta = null;
            }
        }

        function montarEnderecoCliente() {
            return removerCepDaGeolocalizacao(
                String(
                    enderecoCadastradoExibido?.textContent
                    || ''
                ).trim()
            );
        }

        function montarEnderecoAlternativo() {
            const cidade = String(
                cidadeEntrega?.value || ''
            ).trim();

            const estado = String(
                ufEntrega?.value || ''
            ).trim().toUpperCase();

            return [
                String(enderecoEntrega?.value || '').trim(),
                String(numeroEntrega?.value || '').trim(),
                String(complementoEntrega?.value || '').trim(),
                String(bairroEntrega?.value || '').trim(),
                cidade && estado
                    ? cidade + ' - ' + estado
                    : cidade || estado,
                'Brasil',
            ]
                .filter(Boolean)
                .join(', ');
        }

        function montarEnderecoAtual() {
            return usarEnderecoCadastrado?.value === 'sim'
                ? montarEnderecoCliente()
                : montarEnderecoAlternativo();
        }

        function obterCepAtual() {
            if (usarEnderecoCadastrado?.value === 'nao') {
                return somenteNumeros(
                    cepEntrega?.value
                );
            }

            const cliente =
                clienteSelect?.options[
                    clienteSelect.selectedIndex
                ] || null;

            return somenteNumeros(
                cliente?.dataset?.cep || ''
            );
        }

        async function localizarCoordenadaPorCep(
            cep,
            sinal
        ) {
            if (cep.length !== 8) {
                return null;
            }

            try {
                const resposta = await fetch(
                    configuracaoGeocodificacao.cepUrl
                        + cep,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                        signal: sinal,
                    }
                );

                if (!resposta.ok) {
                    return null;
                }

                const dados = await resposta.json();
                const coordenadas =
                    dados?.location?.coordinates
                    || null;

                const latitude = converterCoordenada(
                    dados?.lat
                    ?? coordenadas?.latitude
                    ?? (
                        Array.isArray(coordenadas)
                            ? coordenadas[1]
                            : NaN
                    )
                );

                const longitude = converterCoordenada(
                    dados?.lng
                    ?? coordenadas?.longitude
                    ?? (
                        Array.isArray(coordenadas)
                            ? coordenadas[0]
                            : NaN
                    )
                );

                if (
                    !Number.isFinite(latitude)
                    || !Number.isFinite(longitude)
                ) {
                    return null;
                }

                return {
                    latitude: latitude,
                    longitude: longitude,
                };
            } catch (erro) {
                if (erro.name === 'AbortError') {
                    throw erro;
                }

                return null;
            }
        }

        function enderecoAlternativoCompleto() {
            return (
                String(enderecoEntrega?.value || '').trim() !== ''
                && String(numeroEntrega?.value || '').trim() !== ''
                && String(bairroEntrega?.value || '').trim() !== ''
                && String(cidadeEntrega?.value || '').trim() !== ''
                && String(ufEntrega?.value || '').trim().length === 2
                && somenteNumeros(cepEntrega?.value).length === 8
            );
        }

        function montarConsultas(endereco) {
            const consultas = [];

            function adicionar(valor) {
                const consulta = String(valor || '')
                    .replace(/\s+/g, ' ')
                    .replace(/\s*,\s*/g, ', ')
                    .replace(/(?:,\s*){2,}/g, ', ')
                    .replace(/^,\s*|,\s*$/g, '')
                    .trim();

                if (
                    consulta !== ''
                    && !consultas.includes(consulta)
                ) {
                    consultas.push(consulta);
                }
            }

            const original = removerCepDaGeolocalizacao(
                String(endereco || '')
                    .replace(/\bN(?:º|°|o)?\.?\s*(?=\d)/gi, '')
                    .trim()
            );

            const partes = original
                .split(',')
                .map(function (parte) {
                    return parte.trim();
                })
                .filter(Boolean);

            const semComplemento = partes.filter(
                function (parte) {
                    return !/^(?:casa|ap(?:to|artamento)?|bloco|fundos|sala|loja|galp[aã]o)\b/i.test(
                        parte
                    );
                }
            );

            adicionar(original);
            adicionar(semComplemento.join(', '));

            return consultas;
        }

        function aguardar(milissegundos) {
            return new Promise(function (resolve) {
                window.setTimeout(
                    resolve,
                    milissegundos
                );
            });
        }

        async function localizarEnderecoAutomaticamente() {
            if (tipoEntrega?.value !== 'entrega') {
                return false;
            }

            const endereco = montarEnderecoAtual();

            if (endereco === '') {
                limparCoordenadas();
                definirStatus(
                    'Selecione o cliente ou informe o endereço completo.',
                    'pending'
                );
                return false;
            }

            cancelarConsulta();

            const consultaAtual = sequenciaConsulta;
            controladorConsulta = new AbortController();

            limparCoordenadas();
            definirStatus(
                'Localizando automaticamente pelo logradouro, número, bairro, cidade e UF...',
                'loading'
            );

            try {
                /*
                 * REGRA:
                 * 1. Primeiro tenta o endereço completo no geocodificador.
                 * 2. CEP puro nunca é tratado como endereço exato.
                 * 3. O CEP é apenas fallback aproximado e não confirma
                 *    automaticamente o ponto.
                 */
                const consultas = montarConsultas(endereco);

                for (
                    let indice = 0;
                    indice < consultas.length;
                    indice++
                ) {
                    const espera = Math.max(
                        0,
                        proximaConsultaEm - Date.now()
                    );

                    if (espera > 0) {
                        await aguardar(espera);
                    }

                    if (consultaAtual !== sequenciaConsulta) {
                        return false;
                    }

                    proximaConsultaEm =
                        Date.now()
                        + configuracaoGeocodificacao.intervalo;

                    const url = new URL(
                        configuracaoGeocodificacao.url
                    );

                    url.searchParams.set(
                        'q',
                        consultas[indice]
                    );
                    url.searchParams.set(
                        'format',
                        'jsonv2'
                    );
                    url.searchParams.set(
                        'limit',
                        '1'
                    );
                    url.searchParams.set(
                        'countrycodes',
                        'br'
                    );
                    url.searchParams.set(
                        'accept-language',
                        'pt-BR'
                    );

                    const resposta = await fetch(
                        url.toString(),
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                            signal: controladorConsulta.signal,
                        }
                    );

                    if (!resposta.ok) {
                        throw new Error(
                            'Geocodificação HTTP '
                            + resposta.status
                        );
                    }

                    const resultados =
                        await resposta.json();

                    const resultado =
                        resultados[0] || null;

                    if (!resultado) {
                        continue;
                    }

                    const latitude = converterCoordenada(
                        resultado.lat
                    );

                    const longitude = converterCoordenada(
                        resultado.lon
                    );

                    if (
                        !Number.isFinite(latitude)
                        || !Number.isFinite(longitude)
                    ) {
                        continue;
                    }

                    if (consultaAtual !== sequenciaConsulta) {
                        return false;
                    }

                    aplicarCoordenadas(
                        latitude,
                        longitude,
                        'Endereço localizado automaticamente pelo endereço.',
                        true,
                        'endereco'
                    );

                    return true;
                }

                /*
                 * Fallback por CEP:
                 * serve apenas como referência aproximada.
                 * Não pode produzir coordenada_confirmada = 1.
                 */
                const cep = obterCepAtual();

                const coordenadaCep =
                    await localizarCoordenadaPorCep(
                        cep,
                        controladorConsulta.signal
                    );

                if (consultaAtual !== sequenciaConsulta) {
                    return false;
                }

                if (coordenadaCep) {
                    aplicarCoordenadas(
                        coordenadaCep.latitude,
                        coordenadaCep.longitude,
                        'O endereço exato não foi localizado. Foi encontrada apenas uma referência aproximada pelo CEP; revise o endereço antes de salvar.',
                        false,
                        'cep'
                    );

                    return false;
                }

                limparCoordenadas();

                definirStatus(
                    'Endereço não localizado. Confira rua, número, bairro, cidade, UF e CEP.',
                    'error'
                );

                return false;
            } catch (erro) {
                if (erro.name === 'AbortError') {
                    return false;
                }

                limparCoordenadas();

                definirStatus(
                    'Não foi possível localizar o endereço. Tente novamente.',
                    'error'
                );

                return false;
            } finally {
                if (consultaAtual === sequenciaConsulta) {
                    controladorConsulta = null;
                }
            }
        }

        function agendarLocalizacao(espera = 500) {
            window.clearTimeout(
                temporizadorLocalizacao
            );

            temporizadorLocalizacao = window.setTimeout(
                function () {
                    if (
                        tipoEntrega?.value === 'entrega'
                        && (
                            usarEnderecoCadastrado?.value === 'sim'
                            || enderecoAlternativoCompleto()
                        )
                    ) {
                        localizarEnderecoAutomaticamente();
                    }
                },
                espera
            );
        }

        function invalidarEndereco(mensagem) {
            cancelarConsulta();
            limparCoordenadas(mensagem);
        }

        clienteSelect?.addEventListener(
            'change',
            function () {
                invalidarEndereco(
                    'O endereço será localizado automaticamente.'
                );

                if (
                    tipoEntrega?.value === 'entrega'
                    && usarEnderecoCadastrado?.value === 'sim'
                    && this.value
                ) {
                    agendarLocalizacao(100);
                }
            }
        );

        usarEnderecoCadastrado?.addEventListener(
            'change',
            function () {
                invalidarEndereco(
                    'O endereço será localizado automaticamente.'
                );

                if (
                    tipoEntrega?.value === 'entrega'
                    && this.value === 'sim'
                    && clienteSelect?.value
                ) {
                    agendarLocalizacao(100);
                }
            }
        );

        tipoEntrega?.addEventListener(
            'change',
            function () {
                if (this.value !== 'entrega') {
                    cancelarConsulta();
                    limparCoordenadas();
                    return;
                }

                invalidarEndereco(
                    'O endereço será localizado automaticamente.'
                );

                if (
                    usarEnderecoCadastrado?.value === 'sim'
                    && clienteSelect?.value
                ) {
                    agendarLocalizacao(100);
                }
            }
        );

        [
            enderecoEntrega,
            numeroEntrega,
            complementoEntrega,
            bairroEntrega,
            cidadeEntrega,
            ufEntrega,
            cepEntrega,
        ]
            .filter(Boolean)
            .forEach(function (campo) {
                campo.addEventListener(
                    'input',
                    function () {
                        if (
                            tipoEntrega?.value === 'entrega'
                            && usarEnderecoCadastrado?.value === 'nao'
                        ) {
                            invalidarEndereco(
                                'O endereço mudou e será localizado novamente.'
                            );

                            if (enderecoAlternativoCompleto()) {
                                agendarLocalizacao();
                            }
                        }
                    }
                );
            });

        const coordenadaRestaurada = (
            latitudeEntrega.value !== ''
            && longitudeEntrega.value !== ''
        );

        if (coordenadaRestaurada) {
            const confirmada =
                coordenadaConfirmada.value === '1';

            definirStatus(
                confirmada
                    ? 'Endereço localizado automaticamente.'
                    : 'Coordenadas aproximadas disponíveis para a entrega.',
                confirmada ? 'confirmed' : 'pending'
            );
        } else if (
            tipoEntrega?.value === 'entrega'
            && usarEnderecoCadastrado?.value === 'sim'
            && clienteSelect?.value
        ) {
            agendarLocalizacao(100);
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

            const tipoEntrega = document.getElementById(
                'tipo_entrega'
            );

            const latitudeEntrega = document.getElementById(
                'latitude_entrega'
            );

            const longitudeEntrega = document.getElementById(
                'longitude_entrega'
            );

            if (
                tipoEntrega?.value === 'entrega'
                && (
                    !latitudeEntrega?.value
                    || !longitudeEntrega?.value
                )
            ) {
                e.preventDefault();

                alert(
                    'Aguarde a localização automática ou confira o endereço da entrega.'
                );

                document.getElementById(
                    'coordenadaEntregaStatus'
                )?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return false;
            }

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
<script src="<?php echo e(asset('js/orcamento.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/create.blade.php ENDPATH**/ ?>