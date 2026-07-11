

<?php $__env->startSection('content'); ?>

<?php
    $entregas = collect($entregasDisponiveis ?? [])
        ->filter()
        ->values();

    $entregaPrincipal = $entregas->first();

    $romaneioAtivo = $romaneioAtivo
        ?? $entregaPrincipal?->romaneioAtivo
        ?? $entregaPrincipal?->romaneio
        ?? null;

    $criandoRomaneio = !$romaneioAtivo;

    $statusOriginal = strtolower(
        $romaneioAtivo?->status
        ?? $entregaPrincipal?->status
        ?? 'montagem'
    );

    $etapaAtual = match ($statusOriginal) {
        'aguardando_separacao',
        'montagem',
        'rascunho',
        'pendente' => 'montagem',

        'separando',
        'em_separacao' => 'separacao',

        'separado',
        'aguardando_carregamento',
        'carregando',
        'em_carregamento' => 'carregamento',

        'carregado',
        'aguardando_conferencia',
        'conferindo',
        'em_conferencia' => 'conferencia',

        'conferido',
        'aguardando_liberacao',
        'liberado',
        'em_rota',
        'finalizado' => 'liberacao',

        default => $criandoRomaneio
            ? 'montagem'
            : 'separacao',
    };

    $etapas = [
        'montagem' => [
            'label' => 'Montagem',
            'icone' => 'bi-clipboard-check',
            'ordem' => 1,
        ],
        'separacao' => [
            'label' => 'Separação',
            'icone' => 'bi-box-seam',
            'ordem' => 2,
        ],
        'carregamento' => [
            'label' => 'Carregamento',
            'icone' => 'bi-truck-front',
            'ordem' => 3,
        ],
        'conferencia' => [
            'label' => 'Conferência',
            'icone' => 'bi-clipboard-data',
            'ordem' => 4,
        ],
        'liberacao' => [
            'label' => 'Liberação',
            'icone' => 'bi-sign-turn-right',
            'ordem' => 5,
        ],
    ];

    $ordemAtual = $etapas[$etapaAtual]['ordem'];
    $progresso = (($ordemAtual - 1) / (count($etapas) - 1)) * 100;

    $codigoRomaneio = $romaneioAtivo?->codigo
        ?? $romaneioAtivo?->numero
        ?? $romaneioAtivo?->codigo_romaneio
        ?? null;

    $motoristaSelecionado = old(
        'motorista_id',
        $romaneioAtivo?->motorista_id
    );

    $veiculoSelecionado = old(
        'veiculo_id',
        $romaneioAtivo?->veiculo_id
    );

    $observacaoRomaneio = old(
        'observacao',
        $romaneioAtivo?->observacao
    );

    $somenteLeituraEquipe = !$criandoRomaneio;

    $formAction = $criandoRomaneio
        ? route('romaneios.store')
        : (
            \Illuminate\Support\Facades\Route::has('romaneios.update')
                ? route('romaneios.update', $romaneioAtivo)
                : route('romaneios.store')
        );

    $statusClasses = [
        'montagem' => 'bg-secondary',
        'separacao' => 'bg-warning text-dark',
        'carregamento' => 'bg-primary',
        'conferencia' => 'bg-info text-dark',
        'liberacao' => 'bg-success',
    ];

    $formatarData = function ($data) {
        if (empty($data)) {
            return 'Não informada';
        }

        return \Carbon\Carbon::parse($data)->format('d/m/Y');
    };

    $resolverCliente = function ($entrega) {
        return $entrega?->orcamento?->cliente
            ?? $entrega?->venda?->cliente
            ?? $entrega?->cliente
            ?? null;
    };

    $resolverProduto = function ($item) {
        return $item?->produto
            ?? $item?->vendaItem?->produto
            ?? $item?->itemOrcamento?->produto
            ?? null;
    };

    $resolverQuantidadePrevista = function ($item) {
        return (float) (
            $item?->saldo_disponivel_romaneio
            ?? $item?->quantidade_prevista
            ?? $item?->itemOrcamento?->quantidade_solicitada
            ?? $item?->vendaItem?->quantidade
            ?? 0
        );
    };

    $resolverItemRomaneio = function ($item) use ($romaneioAtivo) {
        if (!$romaneioAtivo) {
            return null;
        }

        return collect($romaneioAtivo->itens ?? [])
            ->first(function ($itemRomaneio) use ($item) {
                return (int) (
                    $itemRomaneio->entrega_item_id
                    ?? $itemRomaneio->pivot?->entrega_item_id
                    ?? 0
                ) === (int) $item->id;
            });
    };

    $totalEntregas = $entregas->count();

    $totalItens = $entregas->sum(function ($entrega) {
        return collect($entrega->itens ?? [])->count();
    });
?>

<style>
    .romaneio-page {
        --erp-border: #d8dde3;
        --erp-muted: #6c757d;
        --erp-dark: #343a40;
        --erp-soft: #f3f5f7;
        --erp-primary-soft: #eaf2ff;
        --erp-success-soft: #eaf7ef;
        --erp-warning-soft: #fff5df;
        --erp-info-soft: #e8f7fa;
    }

    .romaneio-title {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
    }

    .romaneio-subtitle {
        color: var(--erp-muted);
        font-size: .82rem;
    }

    .section-card {
        background: #fff;
        border: 1px solid var(--erp-border);
        border-radius: 8px;
        box-shadow: 0 .15rem .45rem rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .section-header {
        align-items: center;
        background: #6c757d;
        color: #fff;
        display: flex;
        font-size: .9rem;
        font-weight: 800;
        justify-content: space-between;
        padding: .7rem .9rem;
    }

    .workflow-card {
        padding: 1rem;
    }

    .workflow {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        position: relative;
    }

    .workflow::before {
        background: #d9dee3;
        content: "";
        height: 4px;
        left: 10%;
        position: absolute;
        right: 10%;
        top: 19px;
        z-index: 0;
    }

    .workflow-progress {
        background: #198754;
        height: 4px;
        left: 10%;
        max-width: 80%;
        position: absolute;
        top: 19px;
        transition: width .25s ease;
        z-index: 1;
    }

    .workflow-step {
        position: relative;
        text-align: center;
        z-index: 2;
    }

    .workflow-circle {
        align-items: center;
        background: #fff;
        border: 3px solid #ced4da;
        border-radius: 50%;
        color: #6c757d;
        display: inline-flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .workflow-step.completed .workflow-circle {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    .workflow-step.active .workflow-circle {
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .14);
        color: #fff;
    }

    .workflow-label {
        color: #6c757d;
        display: block;
        font-size: .74rem;
        font-weight: 700;
        margin-top: .42rem;
    }

    .workflow-step.completed .workflow-label,
    .workflow-step.active .workflow-label {
        color: #212529;
    }

    .operation-banner {
        align-items: center;
        background: var(--erp-primary-soft);
        border: 1px solid #9ec5fe;
        border-radius: 7px;
        display: flex;
        justify-content: space-between;
        padding: .75rem .9rem;
    }

    .operation-banner.separacao {
        background: var(--erp-warning-soft);
        border-color: #ffda6a;
    }

    .operation-banner.carregamento {
        background: var(--erp-primary-soft);
        border-color: #9ec5fe;
    }

    .operation-banner.conferencia {
        background: var(--erp-info-soft);
        border-color: #9eeaf9;
    }

    .operation-banner.liberacao {
        background: var(--erp-success-soft);
        border-color: #75b798;
    }

    .operation-title {
        font-size: .95rem;
        font-weight: 800;
    }

    .operation-description {
        color: #495057;
        font-size: .78rem;
    }

    .form-label,
    .info-label {
        color: #5f676e;
        display: block;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .035em;
        margin-bottom: .2rem;
        text-transform: uppercase;
    }

    .info-value {
        color: #212529;
        font-size: .88rem;
        font-weight: 650;
        line-height: 1.35;
    }

    .delivery-list {
        background: var(--erp-soft);
        padding: .75rem;
    }

    .delivery-item {
        background: #fff;
        border: 1px solid #ccd2d8;
        border-radius: 7px;
        margin-bottom: .75rem;
        overflow: hidden;
    }

    .delivery-item:last-child {
        margin-bottom: 0;
    }

    .delivery-button {
        background: #e9ecef;
        border: 0;
        box-shadow: none !important;
        color: #212529;
        padding: .8rem .9rem;
    }

    .delivery-button:not(.collapsed) {
        background: #dde2e6;
        color: #212529;
    }

    .delivery-button::after {
        display: none;
    }

    .delivery-code {
        font-size: .93rem;
        font-weight: 850;
    }

    .delivery-client {
        color: #495057;
        font-size: .78rem;
        font-weight: 650;
    }

    .toggle-icon {
        align-items: center;
        border: 1px solid #adb5bd;
        border-radius: 5px;
        display: inline-flex;
        flex: 0 0 auto;
        height: 30px;
        justify-content: center;
        width: 30px;
    }

    .toggle-icon i {
        transition: transform .2s ease;
    }

    .delivery-button:not(.collapsed) .toggle-icon i {
        transform: rotate(180deg);
    }

    .items-table th {
        background: var(--erp-dark);
        color: #fff;
        font-size: .7rem;
        font-weight: 800;
        padding: .62rem .5rem;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .items-table td {
        font-size: .8rem;
        padding: .6rem .5rem;
        vertical-align: middle;
    }

    .items-table tbody tr:nth-child(even) td {
        background: #fafafa;
    }

    .items-table tbody tr.item-disabled td {
        background: #f1f3f5 !important;
        color: #8a9096;
    }

    .quantity-input {
        margin: 0 auto;
        max-width: 110px;
        text-align: right;
    }

    .quantity-input.fragmented {
        border-color: #fd7e14;
        box-shadow: 0 0 0 .15rem rgba(253, 126, 20, .12);
    }

    .status-select {
        min-width: 135px;
    }

    .balance-value {
        font-weight: 850;
    }

    .balance-value.pending {
        color: #dc3545;
    }

    .balance-value.complete {
        color: #198754;
    }

    .summary-card {
        position: sticky;
        top: 1rem;
    }

    .summary-row {
        align-items: center;
        border-bottom: 1px solid #eceff2;
        display: flex;
        justify-content: space-between;
        padding: .62rem 0;
    }

    .summary-row:last-child {
        border-bottom: 0;
    }

    .summary-label {
        color: var(--erp-muted);
        font-size: .76rem;
        font-weight: 650;
    }

    .summary-value {
        color: #212529;
        font-size: .92rem;
        font-weight: 850;
        text-align: right;
    }

    .summary-progress {
        height: 8px;
    }

    .next-step {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: .72rem;
    }

    .next-step.ready {
        background: var(--erp-success-soft);
        border-color: #75b798;
    }

    .history-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .history-list li {
        border-left: 2px solid #ced4da;
        margin-left: .35rem;
        padding: 0 0 .8rem 1rem;
        position: relative;
    }

    .history-list li:last-child {
        padding-bottom: 0;
    }

    .history-list li::before {
        background: #fff;
        border: 2px solid #6c757d;
        border-radius: 50%;
        content: "";
        height: 10px;
        left: -6px;
        position: absolute;
        top: 4px;
        width: 10px;
    }

    .history-time {
        color: #6c757d;
        font-size: .68rem;
        font-weight: 700;
    }

    .history-text {
        font-size: .78rem;
        font-weight: 650;
    }

    .footer-actions {
        align-items: center;
        background: #fff;
        border-top: 1px solid var(--erp-border);
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: space-between;
        padding: .85rem;
    }

    .action-primary {
        min-width: 190px;
    }

    @media (max-width: 991.98px) {
        .workflow {
            grid-template-columns: repeat(5, minmax(78px, 1fr));
            overflow-x: auto;
        }

        .workflow-label {
            font-size: .65rem;
        }

        .summary-card {
            position: static;
        }
    }
</style>

<div class="container-fluid px-2 py-3 romaneio-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="romaneio-title">
                <i class="bi bi-truck-front me-2"></i>
                <?php echo e($criandoRomaneio ? 'Montagem do Romaneio' : 'Operação do Romaneio'); ?>

            </h4>

            <div class="romaneio-subtitle">
                <?php echo e($criandoRomaneio
                    ? 'Defina a equipe, confira as entregas e monte a carga.'
                    : 'Acompanhe o romaneio até a liberação do veículo.'); ?>

            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php if($codigoRomaneio): ?>
                <span class="badge bg-dark px-3 py-2">
                    <?php echo e($codigoRomaneio); ?>

                </span>
            <?php endif; ?>

            <span class="badge <?php echo e($statusClasses[$etapaAtual]); ?> px-3 py-2">
                <?php echo e($etapas[$etapaAtual]['label']); ?>

            </span>

            <a href="<?php echo e(route('entregas.index')); ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success py-2">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Verifique os campos informados:</strong>

            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if($entregas->isEmpty()): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-circle me-1"></i>
            Nenhuma entrega foi informada para o romaneio.
        </div>
    <?php else: ?>
        <form id="formRomaneio"
              action="<?php echo e($formAction); ?>"
              method="POST">

            <?php echo csrf_field(); ?>

            <?php if(!$criandoRomaneio && \Illuminate\Support\Facades\Route::has('romaneios.update')): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <input type="hidden"
                   name="romaneio_id"
                   value="<?php echo e($romaneioAtivo?->id); ?>">

            <input type="hidden"
                   name="etapa_atual"
                   value="<?php echo e($etapaAtual); ?>">

            <?php $__currentLoopData = $entregas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden"
                       name="entregas[]"
                       value="<?php echo e($entrega->id); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="section-card workflow-card mb-3">

                <div class="workflow">

                    <div class="workflow-progress"
                         style="width: <?php echo e($progresso); ?>%;"></div>

                    <?php $__currentLoopData = $etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chave => $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $concluida = $etapa['ordem'] < $ordemAtual;
                            $ativa = $etapa['ordem'] === $ordemAtual;
                        ?>

                        <div class="workflow-step <?php echo e($concluida ? 'completed' : ''); ?> <?php echo e($ativa ? 'active' : ''); ?>">
                            <span class="workflow-circle">
                                <i class="bi <?php echo e($concluida ? 'bi-check-lg' : $etapa['icone']); ?>"></i>
                            </span>

                            <span class="workflow-label">
                                <?php echo e($etapa['label']); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>

            <div class="operation-banner <?php echo e($etapaAtual); ?> mb-3">
                <div>
                    <div class="operation-title">
                        <?php switch($etapaAtual):
                            case ('montagem'): ?>
                                Monte o romaneio e confirme as quantidades.
                                <?php break; ?>

                            <?php case ('separacao'): ?>
                                Registre a separação física dos produtos.
                                <?php break; ?>

                            <?php case ('carregamento'): ?>
                                Confirme os itens colocados no veículo.
                                <?php break; ?>

                            <?php case ('conferencia'): ?>
                                Valide a carga e registre divergências.
                                <?php break; ?>

                            <?php case ('liberacao'): ?>
                                Revise as informações e libere o veículo.
                                <?php break; ?>
                        <?php endswitch; ?>
                    </div>

                    <div class="operation-description">
                        <?php switch($etapaAtual):
                            case ('montagem'): ?>
                                A criação do romaneio inicia o fluxo operacional da expedição.
                                <?php break; ?>

                            <?php case ('separacao'): ?>
                                Todos os itens precisam ser separados ou justificados antes do avanço.
                                <?php break; ?>

                            <?php case ('carregamento'): ?>
                                Confirme a quantidade efetivamente carregada para cada produto.
                                <?php break; ?>

                            <?php case ('conferencia'): ?>
                                Divergências poderão gerar saldo pendente para uma entrega posterior.
                                <?php break; ?>

                            <?php case ('liberacao'): ?>
                                Após a liberação, o romaneio seguirá para rota.
                                <?php break; ?>
                        <?php endswitch; ?>
                    </div>
                </div>

                <i class="bi <?php echo e($etapas[$etapaAtual]['icone']); ?> fs-2"></i>
            </div>

            <div class="section-card mb-3">

                <div class="section-header">
                    <span>
                        <i class="bi bi-person-badge me-2"></i>
                        Equipe e Veículo
                    </span>

                    <?php if($somenteLeituraEquipe): ?>
                        <span class="badge bg-light text-dark">
                            Dados definidos
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-3">
                    <div class="row g-3">

                        <div class="col-lg-4">
                            <label for="motorista_id"
                                   class="form-label">
                                Motorista
                            </label>

                            <select id="motorista_id"
                                    name="motorista_id"
                                    class="form-select form-select-sm <?php $__errorArgs = ['motorista_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    <?php echo e($somenteLeituraEquipe ? 'disabled' : 'required'); ?>>

                                <option value="">
                                    Selecione o motorista
                                </option>

                                <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($motorista->id); ?>"
                                        <?php if((string) $motoristaSelecionado === (string) $motorista->id): echo 'selected'; endif; ?>>
                                        <?php echo e($motorista->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <?php if($somenteLeituraEquipe): ?>
                                <input type="hidden"
                                       name="motorista_id"
                                       value="<?php echo e($motoristaSelecionado); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <label for="veiculo_id"
                                   class="form-label">
                                Veículo
                            </label>

                            <select id="veiculo_id"
                                    name="veiculo_id"
                                    class="form-select form-select-sm <?php $__errorArgs = ['veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    <?php echo e($somenteLeituraEquipe ? 'disabled' : 'required'); ?>>

                                <option value="">
                                    Selecione o veículo
                                </option>

                                <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($veiculo->id); ?>"
                                        <?php if((string) $veiculoSelecionado === (string) $veiculo->id): echo 'selected'; endif; ?>>

                                        <?php echo e($veiculo->descricao
                                            ?? $veiculo->nome
                                            ?? $veiculo->modelo
                                            ?? 'Veículo #' . $veiculo->id); ?>


                                        <?php if(!empty($veiculo->placa)): ?>
                                            - <?php echo e($veiculo->placa); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <?php if($somenteLeituraEquipe): ?>
                                <input type="hidden"
                                       name="veiculo_id"
                                       value="<?php echo e($veiculoSelecionado); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <label for="observacao"
                                   class="form-label">
                                Observação do romaneio
                            </label>

                            <input type="text"
                                   id="observacao"
                                   name="observacao"
                                   class="form-control form-control-sm <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e($observacaoRomaneio); ?>"
                                   maxlength="1000"
                                   <?php echo e($etapaAtual === 'liberacao' ? 'readonly' : ''); ?>

                                   placeholder="Orientação de carga, acesso, prioridade ou rota...">
                        </div>

                    </div>
                </div>
            </div>

            <div class="row g-3">

                <div class="col-xl-9">

                    <div class="delivery-list section-card">

                        <div class="accordion"
                             id="accordionEntregas">

                            <?php $__currentLoopData = $entregas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indiceEntrega => $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php
                                    $cliente = $resolverCliente($entrega);

                                    $clienteNome = $cliente?->nome
                                        ?? $cliente?->razao_social
                                        ?? 'Cliente não informado';

                                    $telefoneCliente = $cliente?->telefone
                                        ?? $cliente?->celular
                                        ?? 'Não informado';

                                    $enderecoEntrega = $entrega?->endereco_entrega
                                        ?? $entrega?->endereco_completo
                                        ?? 'Endereço não informado';

                                    $dataPrevista = $formatarData(
                                        $entrega?->data_prevista_entrega
                                        ?? $entrega?->data_prevista
                                    );

                                    $periodoEntrega = $entrega?->periodo_entrega
                                        ?? $entrega?->periodo
                                        ?? 'Não informado';

                                    $itensEntrega = collect($entrega?->itens ?? []);

                                    $codigoEntrega = $entrega?->codigo_entrega
                                        ?? 'ENT-' . $entrega->id;
                                ?>

                                <div class="delivery-item">

                                    <h2 class="accordion-header"
                                        id="headingEntrega<?php echo e($entrega->id); ?>">

                                        <button class="accordion-button delivery-button <?php echo e($indiceEntrega > 0 ? 'collapsed' : ''); ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseEntrega<?php echo e($entrega->id); ?>"
                                                aria-expanded="<?php echo e($indiceEntrega === 0 ? 'true' : 'false'); ?>"
                                                aria-controls="collapseEntrega<?php echo e($entrega->id); ?>">

                                            <div class="d-flex justify-content-between align-items-center gap-3 w-100 me-2">

                                                <div class="d-flex align-items-center gap-3">

                                                    <span class="toggle-icon">
                                                        <i class="bi bi-chevron-down"></i>
                                                    </span>

                                                    <div>
                                                        <div class="delivery-code">
                                                            <?php echo e($codigoEntrega); ?>

                                                        </div>

                                                        <div class="delivery-client">
                                                            <?php echo e($clienteNome); ?>

                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">

                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?php echo e($dataPrevista); ?>

                                                    </span>

                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo e($periodoEntrega); ?>

                                                    </span>

                                                    <span class="badge bg-primary">
                                                        <?php echo e($itensEntrega->count()); ?> item(ns)
                                                    </span>

                                                </div>

                                            </div>
                                        </button>
                                    </h2>

                                    <div id="collapseEntrega<?php echo e($entrega->id); ?>"
                                         class="accordion-collapse collapse <?php echo e($indiceEntrega === 0 ? 'show' : ''); ?>"
                                         aria-labelledby="headingEntrega<?php echo e($entrega->id); ?>"
                                         data-bs-parent="#accordionEntregas">

                                        <div class="accordion-body p-0">

                                            <div class="p-3 border-bottom">
                                                <div class="row g-3">

                                                    <div class="col-lg-3">
                                                        <span class="info-label">
                                                            Cliente
                                                        </span>

                                                        <div class="info-value">
                                                            <?php echo e($clienteNome); ?>

                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <span class="info-label">
                                                            Telefone
                                                        </span>

                                                        <div class="info-value">
                                                            <?php echo e($telefoneCliente); ?>

                                                        </div>
                                                    </div>

                                                    <div class="col-lg-5">
                                                        <span class="info-label">
                                                            Endereço
                                                        </span>

                                                        <div class="info-value">
                                                            <?php echo e($enderecoEntrega); ?>

                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <span class="info-label">
                                                            Previsão
                                                        </span>

                                                        <div class="info-value">
                                                            <?php echo e($dataPrevista); ?>

                                                        </div>
                                                    </div>

                                                    <?php if(!empty($entrega?->observacao_entrega)): ?>
                                                        <div class="col-12">
                                                            <span class="info-label">
                                                                Observação da entrega
                                                            </span>

                                                            <div class="info-value">
                                                                <?php echo e($entrega->observacao_entrega); ?>

                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                            </div>

                                            <div class="table-responsive">

                                                <table class="table table-bordered items-table mb-0">

                                                    <thead>
                                                        <tr>
                                                            <th class="text-start">
                                                                Produto
                                                            </th>

                                                            <th style="width: 105px;">
                                                                Local
                                                            </th>

                                                            <th style="width: 105px;">
                                                                Prevista
                                                            </th>

                                                            <th style="width: 125px;">
                                                                Romaneio
                                                            </th>

                                                            <?php if(in_array($etapaAtual, ['separacao', 'carregamento', 'conferencia', 'liberacao'])): ?>
                                                                <th style="width: 125px;">
                                                                    Separada
                                                                </th>
                                                            <?php endif; ?>

                                                            <?php if(in_array($etapaAtual, ['carregamento', 'conferencia', 'liberacao'])): ?>
                                                                <th style="width: 125px;">
                                                                    Carregada
                                                                </th>
                                                            <?php endif; ?>

                                                            <th style="width: 100px;">
                                                                Saldo
                                                            </th>

                                                            <th style="width: 135px;">
                                                                Situação
                                                            </th>

                                                            <th style="width: 70px;">
                                                                Unid.
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>

                                                        <?php $__empty_1 = true; $__currentLoopData = $itensEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                                            <?php
                                                                $produto = $resolverProduto($item);
                                                                $itemRomaneio = $resolverItemRomaneio($item);
                                                                $quantidadePrevista = $resolverQuantidadePrevista($item);

                                                                $quantidadeRomaneio = (float) old(
                                                                    "itens.{$item->id}.quantidade",
                                                                    $itemRomaneio?->quantidade_prevista
                                                                    ?? $itemRomaneio?->quantidade
                                                                    ?? $quantidadePrevista
                                                                );

                                                                $quantidadeSeparada = (float) old(
                                                                    "itens.{$item->id}.quantidade_separada",
                                                                    $itemRomaneio?->quantidade_separada
                                                                    ?? 0
                                                                );

                                                                $quantidadeCarregada = (float) old(
                                                                    "itens.{$item->id}.quantidade_carregada",
                                                                    $itemRomaneio?->quantidade_carregada
                                                                    ?? 0
                                                                );

                                                                $statusItem = old(
                                                                    "itens.{$item->id}.status",
                                                                    strtolower(
                                                                        $itemRomaneio?->status
                                                                        ?? 'pendente'
                                                                    )
                                                                );

                                                                $unidade = $produto?->unidade_medida?->sigla
                                                                    ?? $produto?->unidade
                                                                    ?? 'UN';

                                                                $localizacao = $produto?->localizacao_estoque
                                                                    ?? $produto?->localizacao
                                                                    ?? '—';
                                                            ?>

                                                            <tr class="item-row"
                                                                data-etapa="<?php echo e($etapaAtual); ?>"
                                                                data-prevista="<?php echo e(number_format($quantidadePrevista, 2, '.', '')); ?>"
                                                                data-romaneio="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>">

                                                                <td>
                                                                    <div class="fw-semibold">
                                                                        <?php echo e($produto?->nome
                                                                            ?? $produto?->descricao
                                                                            ?? 'Produto não identificado'); ?>

                                                                    </div>

                                                                    <small class="text-muted">
                                                                        Código: <?php echo e($produto?->id ?? '—'); ?>

                                                                    </small>

                                                                    <input type="hidden"
                                                                           name="itens[<?php echo e($item->id); ?>][entrega_item_id]"
                                                                           value="<?php echo e($item->id); ?>">

                                                                    <input type="hidden"
                                                                           name="itens[<?php echo e($item->id); ?>][romaneio_item_id]"
                                                                           value="<?php echo e($itemRomaneio?->id); ?>">
                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($localizacao); ?>

                                                                </td>

                                                                <td class="text-end fw-bold">
                                                                    <?php echo e(number_format($quantidadePrevista, 2, ',', '.')); ?>

                                                                </td>

                                                                <td class="text-center">

                                                                    <?php if($etapaAtual === 'montagem'): ?>
                                                                        <input type="number"
                                                                               name="itens[<?php echo e($item->id); ?>][quantidade]"
                                                                               value="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>"
                                                                               min="0"
                                                                               max="<?php echo e(number_format($quantidadePrevista, 2, '.', '')); ?>"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-quantity-romaneio>
                                                                    <?php else: ?>
                                                                        <span class="fw-bold">
                                                                            <?php echo e(number_format($quantidadeRomaneio, 2, ',', '.')); ?>

                                                                        </span>

                                                                        <input type="hidden"
                                                                               name="itens[<?php echo e($item->id); ?>][quantidade]"
                                                                               value="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>">
                                                                    <?php endif; ?>

                                                                </td>

                                                                <?php if(in_array($etapaAtual, ['separacao', 'carregamento', 'conferencia', 'liberacao'])): ?>
                                                                    <td class="text-center">

                                                                        <?php if($etapaAtual === 'separacao'): ?>
                                                                            <input type="number"
                                                                                   name="itens[<?php echo e($item->id); ?>][quantidade_separada]"
                                                                                   value="<?php echo e(number_format($quantidadeSeparada, 2, '.', '')); ?>"
                                                                                   min="0"
                                                                                   max="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>"
                                                                                   step="1"
                                                                                   class="form-control form-control-sm quantity-input"
                                                                                   data-quantity-separated>
                                                                        <?php else: ?>
                                                                            <span class="fw-bold">
                                                                                <?php echo e(number_format($quantidadeSeparada, 2, ',', '.')); ?>

                                                                            </span>

                                                                            <input type="hidden"
                                                                                   name="itens[<?php echo e($item->id); ?>][quantidade_separada]"
                                                                                   value="<?php echo e(number_format($quantidadeSeparada, 2, '.', '')); ?>">
                                                                        <?php endif; ?>

                                                                    </td>
                                                                <?php endif; ?>

                                                                <?php if(in_array($etapaAtual, ['carregamento', 'conferencia', 'liberacao'])): ?>
                                                                    <td class="text-center">

                                                                        <?php if($etapaAtual === 'carregamento'): ?>
                                                                            <input type="number"
                                                                                   name="itens[<?php echo e($item->id); ?>][quantidade_carregada]"
                                                                                   value="<?php echo e(number_format($quantidadeCarregada, 2, '.', '')); ?>"
                                                                                   min="0"
                                                                                   max="<?php echo e(number_format(max($quantidadeSeparada, $quantidadeRomaneio), 2, '.', '')); ?>"
                                                                                   step="1"
                                                                                   class="form-control form-control-sm quantity-input"
                                                                                   data-quantity-loaded>
                                                                        <?php else: ?>
                                                                            <span class="fw-bold">
                                                                                <?php echo e(number_format($quantidadeCarregada, 2, ',', '.')); ?>

                                                                            </span>

                                                                            <input type="hidden"
                                                                                   name="itens[<?php echo e($item->id); ?>][quantidade_carregada]"
                                                                                   value="<?php echo e(number_format($quantidadeCarregada, 2, '.', '')); ?>">
                                                                        <?php endif; ?>

                                                                    </td>
                                                                <?php endif; ?>

                                                                <td class="text-end">
                                                                    <span class="balance-value"
                                                                          data-balance>
                                                                        0,00
                                                                    </span>
                                                                </td>

                                                                <td class="text-center">

                                                                    <?php if(in_array($etapaAtual, ['separacao', 'carregamento', 'conferencia'])): ?>
                                                                        <select name="itens[<?php echo e($item->id); ?>][status]"
                                                                                class="form-select form-select-sm status-select"
                                                                                data-status-item>

                                                                            <option value="pendente"
                                                                                <?php if($statusItem === 'pendente'): echo 'selected'; endif; ?>>
                                                                                Pendente
                                                                            </option>

                                                                            <option value="em_andamento"
                                                                                <?php if(in_array($statusItem, ['em_andamento', 'separando', 'carregando'])): echo 'selected'; endif; ?>>
                                                                                Em andamento
                                                                            </option>

                                                                            <option value="concluido"
                                                                                <?php if(in_array($statusItem, ['concluido', 'separado', 'carregado', 'conferido'])): echo 'selected'; endif; ?>>
                                                                                Concluído
                                                                            </option>

                                                                            <option value="divergente"
                                                                                <?php if(in_array($statusItem, ['divergente', 'parcial'])): echo 'selected'; endif; ?>>
                                                                                Divergente
                                                                            </option>
                                                                        </select>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary"
                                                                              data-status-badge>
                                                                            <?php echo e(ucfirst(str_replace('_', ' ', $statusItem))); ?>

                                                                        </span>

                                                                        <input type="hidden"
                                                                               name="itens[<?php echo e($item->id); ?>][status]"
                                                                               value="<?php echo e($statusItem); ?>">
                                                                    <?php endif; ?>

                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($unidade); ?>

                                                                </td>

                                                            </tr>

                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                                            <tr>
                                                                <td colspan="10"
                                                                    class="text-center text-muted py-4">

                                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                                    Nenhum item encontrado para esta entrega.
                                                                </td>
                                                            </tr>

                                                        <?php endif; ?>

                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </div>
                    </div>

                    <?php if(!$criandoRomaneio && !empty($romaneioAtivo?->historicos)): ?>
                        <div class="section-card mt-3">

                            <div class="section-header">
                                <span>
                                    <i class="bi bi-clock-history me-2"></i>
                                    Histórico Operacional
                                </span>
                            </div>

                            <div class="card-body p-3">

                                <ul class="history-list">
                                    <?php $__currentLoopData = $romaneioAtivo->historicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li>
                                            <div class="history-time">
                                                <?php echo e(optional($historico->created_at)->format('d/m/Y H:i')); ?>

                                            </div>

                                            <div class="history-text">
                                                <?php echo e($historico->descricao
                                                    ?? $historico->acao
                                                    ?? 'Movimentação registrada'); ?>

                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>

                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-xl-3">

                    <div class="section-card summary-card">

                        <div class="section-header">
                            <span>
                                <i class="bi bi-clipboard-data me-2"></i>
                                Resumo Operacional
                            </span>
                        </div>

                        <div class="card-body p-3">

                            <div class="summary-row">
                                <span class="summary-label">
                                    Romaneio
                                </span>

                                <span class="summary-value">
                                    <?php echo e($codigoRomaneio ?? 'Novo'); ?>

                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Entregas
                                </span>

                                <span class="summary-value"
                                      id="summaryDeliveries">
                                    <?php echo e($totalEntregas); ?>

                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Itens
                                </span>

                                <span class="summary-value"
                                      id="summaryItems">
                                    <?php echo e($totalItens); ?>

                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Quantidade prevista
                                </span>

                                <span class="summary-value"
                                      id="summaryExpected">
                                    0,00
                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Quantidade concluída
                                </span>

                                <span class="summary-value"
                                      id="summaryCompleted">
                                    0,00
                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Pendências
                                </span>

                                <span class="summary-value text-danger"
                                      id="summaryPending">
                                    0
                                </span>
                            </div>

                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="summary-label">
                                        Progresso da etapa
                                    </span>

                                    <span class="summary-value"
                                          id="summaryPercent">
                                        0%
                                    </span>
                                </div>

                                <div class="progress summary-progress">
                                    <div class="progress-bar"
                                         id="summaryProgress"
                                         role="progressbar"
                                         style="width: 0%;"></div>
                                </div>
                            </div>

                            <div class="next-step mt-3"
                                 id="nextStep">

                                <div class="fw-bold small mb-1"
                                     id="nextStepTitle">
                                    Aguardando conferência
                                </div>

                                <div class="text-muted small"
                                     id="nextStepText">
                                    Revise os itens para continuar.
                                </div>
                            </div>

                            <hr>

                            <div class="small text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-person-badge me-1"></i>
                                    Motorista:
                                    <strong>
                                        <?php echo e($romaneioAtivo?->motorista?->nome ?? 'A definir'); ?>

                                    </strong>
                                </div>

                                <div>
                                    <i class="bi bi-truck me-1"></i>
                                    Veículo:
                                    <strong>
                                        <?php echo e($romaneioAtivo?->veiculo?->placa
                                            ?? $romaneioAtivo?->veiculo?->descricao
                                            ?? 'A definir'); ?>

                                    </strong>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="section-card mt-3">

                <div class="footer-actions">

                    <div class="small text-muted">
                        <?php switch($etapaAtual):
                            case ('montagem'): ?>
                                A criação inicia a separação física da carga.
                                <?php break; ?>

                            <?php case ('separacao'): ?>
                                Salve o andamento ou finalize a separação.
                                <?php break; ?>

                            <?php case ('carregamento'): ?>
                                Confirme todos os itens carregados no veículo.
                                <?php break; ?>

                            <?php case ('conferencia'): ?>
                                Verifique divergências antes de concluir.
                                <?php break; ?>

                            <?php case ('liberacao'): ?>
                                A liberação altera o romaneio para em rota.
                                <?php break; ?>
                        <?php endswitch; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        <a href="<?php echo e(route('entregas.index')); ?>"
                           class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </a>

                        <?php if(!$criandoRomaneio && $etapaAtual !== 'liberacao'): ?>
                            <button type="submit"
                                    name="acao"
                                    value="salvar"
                                    class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-floppy me-1"></i>
                                Salvar Andamento
                            </button>
                        <?php endif; ?>

                        <?php switch($etapaAtual):

                            case ('montagem'): ?>
                                <button type="submit"
                                        name="acao"
                                        value="criar_romaneio"
                                        class="btn btn-primary btn-sm action-primary"
                                        id="btnPrincipal">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Criar Romaneio
                                </button>
                                <?php break; ?>

                            <?php case ('separacao'): ?>
                                <button type="submit"
                                        name="acao"
                                        value="finalizar_separacao"
                                        class="btn btn-warning btn-sm action-primary"
                                        id="btnPrincipal">
                                    <i class="bi bi-box-seam me-1"></i>
                                    Finalizar Separação
                                </button>
                                <?php break; ?>

                            <?php case ('carregamento'): ?>
                                <button type="submit"
                                        name="acao"
                                        value="finalizar_carregamento"
                                        class="btn btn-primary btn-sm action-primary"
                                        id="btnPrincipal">
                                    <i class="bi bi-truck-front me-1"></i>
                                    Finalizar Carregamento
                                </button>
                                <?php break; ?>

                            <?php case ('conferencia'): ?>
                                <button type="submit"
                                        name="acao"
                                        value="concluir_conferencia"
                                        class="btn btn-info btn-sm action-primary"
                                        id="btnPrincipal">
                                    <i class="bi bi-clipboard-check me-1"></i>
                                    Concluir Conferência
                                </button>
                                <?php break; ?>

                            <?php case ('liberacao'): ?>
                                <button type="submit"
                                        name="acao"
                                        value="liberar_veiculo"
                                        class="btn btn-success btn-sm action-primary"
                                        id="btnPrincipal">
                                    <i class="bi bi-sign-turn-right me-1"></i>
                                    Liberar Veículo
                                </button>
                                <?php break; ?>

                        <?php endswitch; ?>

                    </div>
                </div>
            </div>

        </form>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const form = document.getElementById('formRomaneio');

        if (!form) {
            return;
        }

        const etapaAtual = document.querySelector('[name="etapa_atual"]')?.value ?? 'montagem';

        const rows = [...document.querySelectorAll('.item-row')];

        const summaryExpected = document.getElementById('summaryExpected');
        const summaryCompleted = document.getElementById('summaryCompleted');
        const summaryPending = document.getElementById('summaryPending');
        const summaryPercent = document.getElementById('summaryPercent');
        const summaryProgress = document.getElementById('summaryProgress');

        const nextStep = document.getElementById('nextStep');
        const nextStepTitle = document.getElementById('nextStepTitle');
        const nextStepText = document.getElementById('nextStepText');

        const btnPrincipal = document.getElementById('btnPrincipal');

        function number(v) {
            if (v === null || v === undefined || v === '') {
                return 0;
            }

            return parseFloat(
                String(v).replace(',', '.')
            ) || 0;
        }

        function money(v) {
            return Number(v).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function quantidadeRomaneio(row) {

            const input = row.querySelector('[data-quantity-romaneio]');

            if (input) {
                return number(input.value);
            }

            return number(row.dataset.romaneio);
        }

        function quantidadeSeparada(row) {

            const input = row.querySelector('[data-quantity-separated]');

            if (!input) {

                const hidden = row.querySelector('input[name$="[quantidade_separada]"]');

                return hidden
                    ? number(hidden.value)
                    : 0;
            }

            return number(input.value);
        }

        function quantidadeCarregada(row) {

            const input = row.querySelector('[data-quantity-loaded]');

            if (!input) {

                const hidden = row.querySelector('input[name$="[quantidade_carregada]"]');

                return hidden
                    ? number(hidden.value)
                    : 0;
            }

            return number(input.value);
        }

        function atualizarLinha(row) {

            const prevista = number(row.dataset.prevista);

            let quantidadeEtapa = 0;

            switch (etapaAtual) {

                case 'montagem':
                    quantidadeEtapa = quantidadeRomaneio(row);
                    break;

                case 'separacao':
                    quantidadeEtapa = quantidadeSeparada(row);
                    break;

                case 'carregamento':
                case 'conferencia':
                case 'liberacao':
                    quantidadeEtapa = quantidadeCarregada(row);
                    break;
            }

            const saldo = Math.max(
                0,
                prevista - quantidadeEtapa
            );

            const saldoSpan = row.querySelector('[data-balance]');

            if (saldoSpan) {

                saldoSpan.textContent = money(saldo);

                saldoSpan.classList.toggle(
                    'pending',
                    saldo > 0
                );

                saldoSpan.classList.toggle(
                    'complete',
                    saldo <= 0
                );
            }

            if (etapaAtual === 'montagem') {

                const input = row.querySelector('[data-quantity-romaneio]');

                if (input) {

                    input.classList.toggle(
                        'fragmented',
                        quantidadeEtapa < prevista &&
                        quantidadeEtapa > 0
                    );
                }
            }

            return {
                prevista,
                realizada: quantidadeEtapa,
                saldo
            };
        }

        function atualizarResumo() {

            let prevista = 0;
            let realizada = 0;
            let pendentes = 0;

            rows.forEach(row => {

                const dados = atualizarLinha(row);

                prevista += dados.prevista;
                realizada += dados.realizada;

                if (dados.saldo > 0.0001) {
                    pendentes++;
                }

            });

            const percentual = prevista > 0
                ? (realizada / prevista) * 100
                : 0;

            if (summaryExpected)
                summaryExpected.textContent = money(prevista);

            if (summaryCompleted)
                summaryCompleted.textContent = money(realizada);

            if (summaryPending)
                summaryPending.textContent = pendentes;

            if (summaryPercent)
                summaryPercent.textContent = Math.round(percentual) + '%';

            if (summaryProgress)
                summaryProgress.style.width = percentual + '%';

            if (nextStep) {

                nextStep.classList.toggle(
                    'ready',
                    pendentes === 0
                );
            }

            switch (etapaAtual) {

                case 'montagem':

                    nextStepTitle.innerText =
                        pendentes === 0
                            ? 'Pronto para criar o romaneio'
                            : 'Montagem em andamento';

                    nextStepText.innerText =
                        pendentes === 0
                            ? 'A próxima etapa será Separação.'
                            : 'Confira as quantidades do romaneio.';

                    break;

                case 'separacao':

                    nextStepTitle.innerText =
                        pendentes === 0
                            ? 'Separação concluída'
                            : 'Separação em andamento';

                    nextStepText.innerText =
                        pendentes === 0
                            ? 'O romaneio poderá seguir para Carregamento.'
                            : 'Existem itens aguardando separação.';

                    break;

                case 'carregamento':

                    nextStepTitle.innerText =
                        pendentes === 0
                            ? 'Carga concluída'
                            : 'Carregamento em andamento';

                    nextStepText.innerText =
                        pendentes === 0
                            ? 'Pronto para Conferência.'
                            : 'Ainda existem itens para carregar.';

                    break;

                case 'conferencia':

                    nextStepTitle.innerText =
                        pendentes === 0
                            ? 'Conferência concluída'
                            : 'Conferência com divergências';

                    nextStepText.innerText =
                        pendentes === 0
                            ? 'Pronto para Liberação.'
                            : 'Existem diferenças para analisar.';

                    break;

                case 'liberacao':

                    nextStepTitle.innerText =
                        'Veículo pronto';

                    nextStepText.innerText =
                        'Toda operação foi concluída.';

                    break;
            }

            if (btnPrincipal) {

                if (
                    etapaAtual !== 'conferencia'
                ) {

                    btnPrincipal.disabled =
                        pendentes > 0;
                }

            }

        }

        rows.forEach(row => {

            row.querySelectorAll(
                'input[type=number],select'
            ).forEach(el => {

                el.addEventListener(
                    'input',
                    atualizarResumo
                );

                el.addEventListener(
                    'change',
                    atualizarResumo
                );

            });

        });

        form.addEventListener('submit', function (e) {

            atualizarResumo();

            if (
                btnPrincipal &&
                btnPrincipal.disabled
            ) {

                e.preventDefault();

                return;
            }

            if (btnPrincipal) {

                btnPrincipal.disabled = true;

                btnPrincipal.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

            }

        });

        atualizarResumo();

    });

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/create.blade.php ENDPATH**/ ?>