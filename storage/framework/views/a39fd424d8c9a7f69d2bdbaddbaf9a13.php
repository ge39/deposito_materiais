

<?php $__env->startSection('content'); ?>
<style>
    .clientes-index-page {
        --cli-primary: #2563eb;
        --cli-primary-dark: #1d4ed8;
        --cli-success: #15966b;
        --cli-danger: #dc3545;
        --cli-warning: #f59e0b;
        --cli-text: #172033;
        --cli-muted: #667085;
        --cli-border: #dfe5ef;
        --cli-header: #202a3a;
        --cli-soft: #f6f8fb;

        width: calc(100vw - 24px);
        max-width: none;
        margin-left: calc(50% - 50vw + 12px);
        padding-left: clamp(.75rem, 1.4vw, 1.5rem);
        padding-right: clamp(.75rem, 1.4vw, 1.5rem);
        color: var(--cli-text);
    }

    .clientes-index-page .page-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 1rem;
        margin-bottom: 1rem;
        background:
            linear-gradient(
                135deg,
                rgba(37, 99, 235, .10),
                rgba(14, 165, 233, .03)
            ),
            #ffffff;

        border: 1px solid var(--cli-border);
        border-left: 5px solid var(--cli-primary);
        border-radius: 13px;
        box-shadow: 0 7px 22px rgba(16, 24, 40, .055);
    }

    .clientes-index-page .page-heading-title {
        display: flex;
        align-items: center;
        gap: .8rem;
    }

    .clientes-index-page .page-heading-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        color: var(--cli-primary);
        background: #eaf1ff;
        border-radius: 11px;
        font-size: 1.25rem;
    }

    .clientes-index-page .page-heading h2 {
        margin: 0;
        color: #101828;
        font-size: clamp(1.35rem, 2vw, 1.7rem);
        font-weight: 750;
        letter-spacing: -.02em;
    }

    .clientes-index-page .page-heading p {
        margin: .15rem 0 0;
        color: var(--cli-muted);
        font-size: .84rem;
    }

    .clientes-index-page .filter-card,
    .clientes-index-page .table-card {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid var(--cli-border);
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(16, 24, 40, .045);
    }

    .clientes-index-page .filter-card {
        margin-bottom: 1rem;
    }

    .clientes-index-page .section-header {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .6rem .9rem;
        color: #ffffff;
        background: #737d86;
        font-size: .92rem;
        font-weight: 700;
    }

    .clientes-index-page .section-header-title {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .clientes-index-page .filter-card-body {
        padding: .85rem;
        background: var(--cli-soft);
    }

    .clientes-index-page .form-label {
        margin-bottom: .35rem;
        color: #344054;
        font-size: .8rem;
        font-weight: 700;
    }

    .clientes-index-page .form-control,
    .clientes-index-page .form-select {
        min-height: 40px;
        border-color: #d3dae6;
        border-radius: 8px;
        box-shadow: none;
    }

    .clientes-index-page .form-control:focus,
    .clientes-index-page .form-select:focus {
        border-color: #80a8ff;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .12);
    }

    .clientes-index-page .btn {
        border-radius: 8px;
        font-weight: 650;
    }

    .clientes-index-page .btn-primary {
        background: var(--cli-primary);
        border-color: var(--cli-primary);
    }

    .clientes-index-page .btn-primary:hover {
        background: var(--cli-primary-dark);
        border-color: var(--cli-primary-dark);
    }

    .clientes-index-page .summary-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .6rem;
        color: #344054;
        background: #ffffff;
        border-radius: 7px;
        font-size: .75rem;
        font-weight: 700;
    }

    .clientes-index-page .table-responsive {
        margin: 0;
    }

    .clientes-index-page .table {
        min-width: 1050px;
        margin: 0;
        vertical-align: middle;
    }

    .clientes-index-page .table thead th {
        padding: .75rem .65rem;
        color: #ffffff;
        background: var(--cli-header);
        border: 0;
        font-size: .74rem;
        font-weight: 750;
        letter-spacing: .025em;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .clientes-index-page .table tbody td {
        padding: .7rem .65rem;
        color: #344054;
        border-color: #e9edf3;
        font-size: .84rem;
    }

    .clientes-index-page .table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    .clientes-index-page .table tbody tr:hover td {
        background: #eef5ff;
    }

    .clientes-index-page .cliente-nome {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 190px;
    }

    .clientes-index-page .cliente-avatar {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        color: var(--cli-primary);
        background: #eaf1ff;
        border-radius: 50%;
        font-size: .95rem;
        font-weight: 750;
        text-transform: uppercase;
    }

    .clientes-index-page .cliente-nome-principal {
        color: #172033;
        font-weight: 750;
        line-height: 1.15;
    }

    .clientes-index-page .cliente-id {
        margin-top: .15rem;
        color: var(--cli-muted);
        font-size: .7rem;
    }

    .clientes-index-page .badge-tipo,
    .clientes-index-page .badge-plano,
    .clientes-index-page .badge-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .3rem .5rem;
        border-radius: 7px;
        font-size: .7rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .clientes-index-page .badge-fisica {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .clientes-index-page .badge-juridica {
        color: #7c3aed;
        background: #ede9fe;
    }

    .clientes-index-page .badge-plano {
        color: #92400e;
        background: #fef3c7;
    }

    .clientes-index-page .badge-status {
        color: #087f5b;
        background: #d1fae5;
    }

    .clientes-index-page .contact-line {
        display: flex;
        align-items: center;
        gap: .35rem;
        margin-bottom: .2rem;
        color: #475467;
        font-size: .77rem;
    }

    .clientes-index-page .contact-line:last-child {
        margin-bottom: 0;
    }

    .clientes-index-page .contact-line i {
        color: #667085;
    }

    .clientes-index-page .contact-line a {
        max-width: 220px;
        overflow: hidden;
        color: inherit;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clientes-index-page .contact-line a:hover {
        color: var(--cli-primary);
    }

    .clientes-index-page .credit-value {
        color: #087f5b;
        font-weight: 750;
        white-space: nowrap;
    }

    .clientes-index-page .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        white-space: nowrap;
    }

    .clientes-index-page .action-buttons .btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .clientes-index-page .empty-state {
        padding: 3rem 1rem;
        color: var(--cli-muted);
        text-align: center;
    }

    .clientes-index-page .empty-state-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .75rem;
        color: var(--cli-primary);
        background: #eaf1ff;
        border-radius: 50%;
        font-size: 1.5rem;
    }

    .clientes-index-page .pagination-wrapper {
        padding: .8rem 1rem;
        background: #ffffff;
        border-top: 1px solid var(--cli-border);
    }

    @media (max-width: 767.98px) {
        .clientes-index-page {
            width: 100%;
            margin-left: 0;
            padding-left: .75rem;
            padding-right: .75rem;
        }

        .clientes-index-page .page-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .clientes-index-page .page-heading-actions {
            width: 100%;
        }

        .clientes-index-page .page-heading-actions .btn {
            width: 100%;
        }

        .clientes-index-page .filter-actions {
            width: 100%;
        }

        .clientes-index-page .filter-actions .btn {
            flex: 1 1 0;
        }
    }
</style>

<div class="clientes-index-page">

    <!-- Cabeçalho -->
    <div class="page-heading">
        <div class="page-heading-title">
            <div class="page-heading-icon">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <h2>Clientes</h2>

                <p>
                    Consulta, acompanhamento e manutenção dos clientes ativos.
                </p>
            </div>
        </div>

        <div class="page-heading-actions d-flex gap-2">
            <a
                href="<?php echo e(route('clientes.inativos')); ?>"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-person-x me-1"></i>
                Inativos
            </a>

            <a
                href="<?php echo e(route('clientes.create')); ?>"
                class="btn btn-success"
            >
                <i class="bi bi-person-plus me-1"></i>
                Novo Cliente
            </a>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if(session('success')): ?>
        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <i class="bi bi-exclamation-triangle me-1"></i>

            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filter-card">
        <div class="section-header">
            <div class="section-header-title">
                <i class="bi bi-funnel"></i>
                <span>Filtros de Consulta</span>
            </div>
        </div>

        <div class="filter-card-body">
            <form
                action="<?php echo e(route('clientes.buscar')); ?>"
                method="GET"
            >
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6 col-md-6">
                        <label for="busca" class="form-label">
                            Nome ou CPF/CNPJ
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search text-primary"></i>
                            </span>

                            <input
                                type="text"
                                name="busca"
                                id="busca"
                                value="<?php echo e(request('busca')); ?>"
                                class="form-control"
                                maxlength="255"
                                placeholder="Digite o nome ou CPF/CNPJ"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <label for="tipo" class="form-label">
                            Tipo de Pessoa
                        </label>

                        <select
                            name="tipo"
                            id="tipo"
                            class="form-select"
                        >
                            <option value="">Todos</option>

                            <option
                                value="fisica"
                                <?php echo e(request('tipo') === 'fisica' ? 'selected' : ''); ?>

                            >
                                Pessoa Física
                            </option>

                            <option
                                value="juridica"
                                <?php echo e(request('tipo') === 'juridica' ? 'selected' : ''); ?>

                            >
                                Pessoa Jurídica
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <div class="filter-actions d-flex gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i class="bi bi-search me-1"></i>
                                Buscar
                            </button>

                            <a
                                href="<?php echo e(route('clientes.index')); ?>"
                                class="btn btn-outline-secondary flex-grow-1"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="table-card">
        <div class="section-header">
            <div class="section-header-title">
                <i class="bi bi-list-ul"></i>
                <span>Clientes Ativos</span>
            </div>

            <span class="summary-badge">
                <i class="bi bi-people"></i>
                Total: <?php echo e($clientes->total()); ?>

            </span>
        </div>

        <?php if($clientes->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">Cliente</th>
                            <th>Tipo / Plano</th>
                            <th>CPF/CNPJ</th>
                            <th class="text-start">Contato</th>
                            <th>Limite de Crédito</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $nomeCliente = trim(
                                    (string) $cliente->nome
                                );

                                $iniciais = collect(
                                    preg_split(
                                        '/\s+/',
                                        $nomeCliente
                                    )
                                )
                                    ->filter()
                                    ->take(2)
                                    ->map(
                                        fn ($parte) =>
                                            mb_strtoupper(
                                                mb_substr(
                                                    $parte,
                                                    0,
                                                    1
                                                )
                                            )
                                    )
                                    ->implode('');

                                $planoCliente = match (
                                    $cliente->tipo_cliente
                                ) {
                                    'markup_1' =>
                                        'Varejo',

                                    'markup_2' =>
                                        'Empresa / Empreiteiro',

                                    'markup_3' =>
                                        'Atacado',

                                    default =>
                                        'Não definido',
                                };
                            ?>

                            <tr>
                                <td>
                                    <div class="cliente-nome">
                                        <div class="cliente-avatar">
                                            <?php echo e($iniciais ?: '?'); ?>

                                        </div>

                                        <div>
                                            <div class="cliente-nome-principal">
                                                <?php echo e($nomeCliente !== ''
                                                        ? $nomeCliente
                                                        : 'Cadastro sem nome'); ?>

                                            </div>

                                            <div class="cliente-id">
                                                ID #<?php echo e($cliente->id); ?>

                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="mb-1">
                                        <span
                                            class="
                                                badge-tipo
                                                <?php echo e($cliente->tipo === 'juridica'
                                                        ? 'badge-juridica'
                                                        : 'badge-fisica'); ?>

                                            "
                                        >
                                            <?php echo e($cliente->tipo === 'juridica'
                                                    ? 'Pessoa Jurídica'
                                                    : 'Pessoa Física'); ?>

                                        </span>
                                    </div>

                                    <span class="badge-plano">
                                        <?php echo e($planoCliente); ?>

                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="fw-semibold">
                                        <?php echo e($cliente->cpf_cnpj ?: '-'); ?>

                                    </span>
                                </td>

                                <td>
                                    <div class="contact-line">
                                        <i class="bi bi-telephone"></i>

                                        <?php if($cliente->telefone): ?>
                                            <a
                                                href="tel:<?php echo e(preg_replace('/\D/', '', $cliente->telefone)); ?>"
                                            >
                                                <?php echo e($cliente->telefone); ?>

                                            </a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="contact-line">
                                        <i class="bi bi-envelope"></i>

                                        <?php if($cliente->email): ?>
                                            <a
                                                href="mailto:<?php echo e($cliente->email); ?>"
                                                title="<?php echo e($cliente->email); ?>"
                                            >
                                                <?php echo e($cliente->email); ?>

                                            </a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="credit-value">
                                        R$
                                        <?php echo e(number_format(
                                                (float) (
                                                    $cliente
                                                        ->credito
                                                        ?->limite_credito
                                                    ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            )); ?>

                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge-status">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Ativo
                                    </span>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a
                                            href="<?php echo e(route('clientes.show', $cliente->id)); ?>"
                                            class="btn btn-outline-info"
                                            title="Conta corrente"
                                            aria-label="Conta corrente"
                                        >
                                            <i class="bi bi-wallet2"></i>
                                        </a>

                                        <a
                                            href="<?php echo e(route('clientes.edit', $cliente->id)); ?>"
                                            class="btn btn-outline-warning"
                                            title="Editar cliente"
                                            aria-label="Editar cliente"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form
                                            action="<?php echo e(route('clientes.desativar', $cliente->id)); ?>"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="
                                                return confirm(
                                                    'Tem certeza que deseja desativar este cliente?'
                                                );
                                            "
                                        >
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>

                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger"
                                                title="Desativar cliente"
                                                aria-label="Desativar cliente"
                                            >
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div
                    class="
                        d-flex
                        flex-column
                        flex-md-row
                        align-items-center
                        justify-content-between
                        gap-2
                    "
                >
                    <div class="small text-muted">
                        Exibindo
                        <?php echo e($clientes->firstItem()); ?>

                        até
                        <?php echo e($clientes->lastItem()); ?>

                        de
                        <?php echo e($clientes->total()); ?>

                        clientes
                    </div>

                    <div>
                        <?php echo e($clientes
                                ->appends(request()->input())
                                ->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-person-search"></i>
                </div>

                <h5 class="fw-bold">
                    Nenhum cliente encontrado
                </h5>

                <p class="mb-3">
                    Ajuste os filtros ou cadastre um novo cliente.
                </p>

                <a
                    href="<?php echo e(route('clientes.create')); ?>"
                    class="btn btn-success"
                >
                    <i class="bi bi-person-plus me-1"></i>
                    Novo Cliente
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/index.blade.php ENDPATH**/ ?>