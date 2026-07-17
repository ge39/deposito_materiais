

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

    $criandoRomaneio = ! $romaneioAtivo;

    $statusOriginal = strtolower(
        trim(
            str_replace(
                ' ',
                '_',
                (string) (
                    $romaneioAtivo?->status
                    ?? $entregaPrincipal?->status
                    ?? 'montagem'
                )
            )
        )
    );

    $etapaAtual = match ($statusOriginal) {
        'montagem',
        'aguardando_separacao',
        'rascunho',
        'pendente' =>
            'montagem',

        'gerado',
        'em_separacao',
        'separando' =>
            'separacao',

        'separado',
        'na_doca',
        'carregando',
        'aguardando_carregamento' =>
            'carregamento',

        'carregado',
        'aguardando_conferencia',
        'conferindo' =>
            'conferencia',

        'conferido',
        'aguardando_liberacao',
        'liberado',
        'saiu_para_entrega',
        'em_rota',
        'entregue',
        'parcial',
        'devolvido' =>
            'liberacao',

        default =>
            $criandoRomaneio
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

    $progressoWorkflow = (
        ($ordemAtual - 1)
        / (count($etapas) - 1)
    ) * 100;

    $codigoRomaneio =
        $romaneioAtivo?->codigo_romaneio
        ?? $romaneioAtivo?->codigo
        ?? $romaneioAtivo?->numero
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

    $formAction = $criandoRomaneio
        ? route('romaneios.store')
        : route(
            'romaneios.operacao.update',
            $romaneioAtivo
        );

    $formMethod = $criandoRomaneio
        ? 'POST'
        : 'PUT';

    $statusClasses = [
        'montagem' =>
            'bg-secondary',

        'separacao' =>
            'bg-warning text-dark',

        'carregamento' =>
            'bg-primary',

        'conferencia' =>
            'bg-info text-dark',

        'liberacao' =>
            'bg-success',
    ];

    $formatarData = function ($data, bool $comHora = false) {
        if (empty($data)) {
            return 'Não registrado';
        }

        return \Carbon\Carbon::parse($data)->format(
            $comHora
                ? 'd/m/Y H:i'
                : 'd/m/Y'
        );
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

    $resolverItemRomaneio = function ($item) use (
        $romaneioAtivo
    ) {
        if (! $romaneioAtivo) {
            return null;
        }

        return collect($romaneioAtivo->itens ?? [])
            ->first(function ($itemRomaneio) use ($item) {
                return (int) $itemRomaneio->entrega_item_id
                    === (int) $item->id;
            });
    };

    $totalEntregas = $entregas->count();

    $totalItens = $entregas->sum(function ($entrega) {
        return collect(
            $entrega?->itens ?? []
        )->count();
    });

    $statusRomaneio = $romaneioAtivo?->status;

    $podeSalvarAndamento = in_array(
        $statusOriginal,
        [
            'gerado',
            'em_separacao',
            'carregando',
        ],
        true
    );

    $podeVoltarEtapa = in_array(
        $statusOriginal,
        [
            'separado',
            'na_doca',
            'carregando',
            'carregado',
            'conferido',
            'liberado',
        ],
        true
    );

    $operacaoFinalizada = in_array(
        $statusOriginal,
        [
            'saiu_para_entrega',
            'em_rota',
            'entregue',
            'parcial',
            'devolvido',
            'cancelado',
        ],
        true
    );
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
        gap: 1rem;
        justify-content: space-between;
        padding: .85rem 1rem;
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

    .operation-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .65rem;
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
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .items-table td {
        font-size: .79rem;
        vertical-align: middle;
    }

    .product-name {
        font-size: .82rem;
        font-weight: 750;
    }

    .product-code {
        color: #6c757d;
        font-size: .7rem;
    }

    .quantity-input {
        min-width: 85px;
        text-align: right;
    }

    .balance-value.pending {
        color: #dc3545;
        font-weight: 800;
    }

    .balance-value.complete {
        color: #198754;
        font-weight: 800;
    }

    .summary-card {
        position: sticky;
        top: 1rem;
    }

    .summary-row {
        align-items: center;
        border-bottom: 1px solid #edf0f2;
        display: flex;
        justify-content: space-between;
        padding: .55rem 0;
    }

    .summary-label {
        color: #6c757d;
        font-size: .72rem;
        font-weight: 700;
    }

    .summary-value {
        font-size: .84rem;
        font-weight: 800;
    }

    .summary-progress {
        height: 8px;
    }

    .next-step {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: .7rem;
    }

    .next-step.ready {
        background: var(--erp-success-soft);
        border-color: #75b798;
    }

    .control-panel {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 7px;
        margin-top: .8rem;
        padding: .75rem;
    }

    .control-grid {
        display: grid;
        gap: .5rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .control-item {
        background: #fff;
        border: 1px solid #e2e6e9;
        border-radius: 6px;
        padding: .55rem;
    }

    .control-item-label {
        color: #6c757d;
        display: block;
        font-size: .65rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .control-item-value {
        display: block;
        font-size: .76rem;
        font-weight: 750;
        margin-top: .15rem;
    }

    .footer-actions {
        align-items: center;
        display: flex;
        gap: .8rem;
        justify-content: space-between;
        padding: .8rem;
    }

    @media (max-width: 991.98px) {
        .workflow {
            gap: .5rem;
            grid-template-columns: repeat(5, minmax(95px, 1fr));
            overflow-x: auto;
        }

        .summary-card {
            position: static;
        }
    }
</style>

<div class="container-fluid romaneio-page py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

        <div>
            <h1 class="romaneio-title">
                <i class="bi bi-truck me-2"></i>
                Operação de Romaneio
            </h1>

            <div class="romaneio-subtitle">
                Montagem, separação, carregamento, conferência e liberação.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">

            <?php if($codigoRomaneio): ?>
                <span class="badge bg-dark fs-6">
                    <?php echo e($codigoRomaneio); ?>

                </span>
            <?php endif; ?>

            <span class="badge <?php echo e($statusClasses[$etapaAtual]); ?>">
                <?php echo e($romaneioAtivo?->status
                    ?? $etapas[$etapaAtual]['label']); ?>

            </span>

            <a href="<?php echo e(route('romaneios.index')); ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Não foi possível concluir a operação.</strong>

            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if($entregas->isEmpty()): ?>

        <div class="alert alert-warning">
            Nenhuma entrega disponível para operação.
        </div>

    <?php else: ?>

        <form id="formRomaneio"
              method="POST"
              action="<?php echo e($formAction); ?>">

            <?php echo csrf_field(); ?>

            <?php if($formMethod === 'PUT'): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <input type="hidden"
                   name="etapa_atual"
                   value="<?php echo e($etapaAtual); ?>">

            <?php if($entregaPrincipal): ?>
                <input type="hidden"
                       name="entrega_id"
                       value="<?php echo e($entregaPrincipal->id); ?>">
            <?php endif; ?>

            <div class="section-card workflow-card mb-3">

                <div class="workflow">

                    <div class="workflow-progress"
                         style="width: <?php echo e($progressoWorkflow); ?>%;">
                    </div>

                    <?php $__currentLoopData = $etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chave => $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php
                            $classeEtapa = '';

                            if ($etapa['ordem'] < $ordemAtual) {
                                $classeEtapa = 'completed';
                            } elseif ($chave === $etapaAtual) {
                                $classeEtapa = 'active';
                            }
                        ?>

                        <div class="workflow-step <?php echo e($classeEtapa); ?>">

                            <div class="workflow-circle">
                                <i class="bi <?php echo e($etapa['icone']); ?>"></i>
                            </div>

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

                        <?php if($criandoRomaneio): ?>
                            Monte o romaneio e confirme as quantidades.

                        <?php elseif($statusOriginal === 'gerado'): ?>
                            O romaneio foi criado e aguarda o início da separação.

                        <?php elseif($statusOriginal === 'em_separacao'): ?>
                            A separação física está em andamento.

                        <?php elseif($statusOriginal === 'separado'): ?>
                            A separação terminou. Encaminhe o romaneio para a doca.

                        <?php elseif($statusOriginal === 'na_doca'): ?>
                            O romaneio está na doca e aguarda o início do carregamento.

                        <?php elseif($statusOriginal === 'carregando'): ?>
                            Registre os produtos colocados no veículo.

                        <?php elseif($statusOriginal === 'carregado'): ?>
                            O carregamento terminou e aguarda conferência.

                        <?php elseif($statusOriginal === 'conferido'): ?>
                            Imprima o romaneio e libere o veículo.

                        <?php elseif($statusOriginal === 'liberado'): ?>
                            O romaneio foi liberado. Registre a saída física.

                        <?php elseif(in_array($statusOriginal, ['saiu_para_entrega', 'em_rota'], true)): ?>
                            O veículo saiu para entrega.

                        <?php elseif($statusOriginal === 'entregue'): ?>
                            A entrega foi concluída.

                        <?php else: ?>
                            Acompanhe a operação do romaneio.
                        <?php endif; ?>

                    </div>

                    <div class="operation-description">

                        <?php if($criandoRomaneio): ?>
                            A criação gera o romaneio, mas não inicia automaticamente a separação.

                        <?php elseif($statusOriginal === 'gerado'): ?>
                            O início registrará o responsável e a data de início da separação.

                        <?php elseif($statusOriginal === 'em_separacao'): ?>
                            Todos os itens precisam ser separados antes da finalização.

                        <?php elseif($statusOriginal === 'separado'): ?>
                            O envio para doca registra a movimentação operacional antes do carregamento.

                        <?php elseif($statusOriginal === 'na_doca'): ?>
                            O início do carregamento registrará o responsável e o horário.

                        <?php elseif($statusOriginal === 'carregando'): ?>
                            O percentual carregado será atualizado conforme as quantidades informadas.

                        <?php elseif($statusOriginal === 'carregado'): ?>
                            Valide todos os itens antes da conclusão da conferência.

                        <?php elseif($statusOriginal === 'conferido'): ?>
                            A liberação somente será permitida após o registro da impressão.

                        <?php elseif($statusOriginal === 'liberado'): ?>
                            A liberação não coloca a entrega em rota. A saída é um evento separado.

                        <?php elseif(in_array($statusOriginal, ['saiu_para_entrega', 'em_rota'], true)): ?>
                            A data de saída e a situação em rota foram registradas.

                        <?php endif; ?>

                    </div>

                    <?php if($romaneioAtivo): ?>

                        <div class="operation-meta">

                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-person me-1"></i>
                                Criado por:
                                <?php echo e($romaneioAtivo?->criador?->nome
                                    ?? $romaneioAtivo?->criado_por
                                    ?? 'Não registrado'); ?>

                            </span>

                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-calendar-check me-1"></i>
                                Emissão:
                                <?php echo e($formatarData(
                                    $romaneioAtivo?->data_emissao,
                                    true
                                )); ?>

                            </span>

                            <?php if($romaneioAtivo?->data_inicio_separacao): ?>
                                <span class="badge bg-warning text-dark">
                                    Separação iniciada:
                                    <?php echo e($formatarData(
                                        $romaneioAtivo->data_inicio_separacao,
                                        true
                                    )); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($romaneioAtivo?->data_inicio_carregamento): ?>
                                <span class="badge bg-primary">
                                    Carga iniciada:
                                    <?php echo e($formatarData(
                                        $romaneioAtivo->data_inicio_carregamento,
                                        true
                                    )); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($romaneioAtivo?->impresso_em): ?>
                                <span class="badge bg-success">
                                    Impresso:
                                    <?php echo e($formatarData(
                                        $romaneioAtivo->impresso_em,
                                        true
                                    )); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($romaneioAtivo?->data_saida): ?>
                                <span class="badge bg-dark">
                                    Saída:
                                    <?php echo e($formatarData(
                                        $romaneioAtivo->data_saida,
                                        true
                                    )); ?>

                                </span>
                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                    <?php if(
                        ! $criandoRomaneio &&
                        $statusOriginal === 'conferido'
                    ): ?>
                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">

                            <button type="submit"
                                    form="formImprimirRomaneio"
                                    class="btn btn-outline-dark btn-sm" formtarget="_blank">

                                <i class="bi bi-printer me-1"></i>

                                <?php echo e($romaneioAtivo?->impresso_em
                                    ? 'Reimprimir Romaneio'
                                    : 'Imprimir Romaneio'); ?>


                            </button>

                            <?php if($romaneioAtivo?->impresso_em): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Impressão registrada
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Impressão pendente
                                </span>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                </div>

                <i class="bi <?php echo e($etapas[$etapaAtual]['icone']); ?> fs-2"></i>

            </div>

            <div class="section-card mb-3">

                <?php
                    $podeEditarEquipe = $criandoRomaneio
                        || in_array(
                            $statusOriginal,
                            [
                                'gerado',
                                'em_separacao',
                            ],
                            true
                        );
                ?>

                <div class="section-header">

                    <span>
                        <i class="bi bi-person-badge me-2"></i>
                        Equipe e Veículo
                    </span>

                    <?php if($podeEditarEquipe): ?>
                        <span class="badge bg-warning text-dark">
                            Definição operacional
                        </span>
                    <?php else: ?>
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

                                <?php if($podeEditarEquipe): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>

                            </label>

                            <select id="motorista_id"
                                    name="motorista_id"
                                    class="form-select form-select-sm
                                        <?php $__errorArgs = ['motorista_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    <?php echo e($podeEditarEquipe ? 'required' : 'disabled'); ?>>

                                <option value="">
                                    Selecione o motorista
                                </option>

                                <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <option value="<?php echo e($motorista->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'motorista_id',
                                                $motoristaSelecionado
                                            ) === (string) $motorista->id
                                        ): ?>
                                            selected
                                        <?php endif; ?>>

                                        <?php echo e($motorista->nome); ?>


                                    </option>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['motorista_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <?php if(! $podeEditarEquipe): ?>
                                <input type="hidden"
                                    name="motorista_id"
                                    value="<?php echo e($motoristaSelecionado); ?>">
                            <?php endif; ?>

                        </div>

                        <div class="col-lg-4">

                            <label for="veiculo_id"
                                class="form-label">

                                Veículo

                                <?php if($podeEditarEquipe): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>

                            </label>

                            <select id="veiculo_id"
                                    name="veiculo_id"
                                    class="form-select form-select-sm
                                        <?php $__errorArgs = ['veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    <?php echo e($podeEditarEquipe ? 'required' : 'disabled'); ?>>

                                <option value="">
                                    Selecione o veículo
                                </option>

                                <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <option value="<?php echo e($veiculo->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'veiculo_id',
                                                $veiculoSelecionado
                                            ) === (string) $veiculo->id
                                        ): ?>
                                            selected
                                        <?php endif; ?>>

                                        <?php echo e($veiculo->descricao
                                            ?? $veiculo->nome
                                            ?? $veiculo->modelo
                                            ?? 'Veículo #' . $veiculo->id); ?>


                                        <?php if(! empty($veiculo->placa)): ?>
                                            - <?php echo e($veiculo->placa); ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <?php if(! $podeEditarEquipe): ?>
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
                                class="form-control form-control-sm
                                    <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old(
                                    'observacao',
                                    $observacaoRomaneio
                                )); ?>"
                                maxlength="1000"
                                <?php echo e($operacaoFinalizada ? 'readonly' : ''); ?>

                                placeholder="Orientação de carga, acesso, prioridade ou rota...">

                            <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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

                                    $clienteNome =
                                        $cliente?->nome
                                        ?? $cliente?->razao_social
                                        ?? 'Cliente não informado';

                                    $telefoneCliente =
                                        $cliente?->telefone
                                        ?? $cliente?->celular
                                        ?? 'Não informado';

                                    $enderecoEntrega =
                                        $entrega?->endereco_entrega
                                        ?? $entrega?->endereco_completo
                                        ?? 'Endereço não informado';

                                    $dataPrevista = $formatarData(
                                        $entrega?->data_prevista_entrega
                                        ?? $entrega?->data_prevista
                                    );

                                    $periodoEntrega =
                                        $entrega?->periodo_entrega
                                        ?? $entrega?->periodo
                                        ?? 'Não informado';

                                    $itensEntrega = collect(
                                        $entrega?->itens ?? []
                                    );

                                    $codigoEntrega =
                                        $entrega?->codigo_entrega
                                        ?? 'ENT-' . $entrega->id;
                                ?>

                                <div class="delivery-item">

                                    <h2 class="accordion-header"
                                        id="headingEntrega<?php echo e($entrega->id); ?>">

                                        <button class="accordion-button delivery-button <?php echo e($indiceEntrega > 0 ? 'collapsed' : ''); ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseEntrega<?php echo e($entrega->id); ?>">

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
                                                        <?php echo e($itensEntrega->count()); ?>

                                                        item(ns)
                                                    </span>

                                                </div>

                                            </div>

                                        </button>
                                    </h2>

                                    <div id="collapseEntrega<?php echo e($entrega->id); ?>"
                                         class="accordion-collapse collapse <?php echo e($indiceEntrega === 0 ? 'show' : ''); ?>"
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

                                                </div>
                                            </div>

                                            <div class="table-responsive">

                                                <table class="table table-bordered items-table mb-0">

                                                    <thead>
                                                        <tr>
                                                            <th class="text-start">
                                                                Produto
                                                            </th>

                                                            <th style="width: 100px;">
                                                                Local
                                                            </th>

                                                            <th style="width: 95px;">
                                                                Prevista
                                                            </th>

                                                            <th style="width: 110px;">
                                                                Romaneio
                                                            </th>

                                                            <?php if(! $criandoRomaneio): ?>
                                                                <th style="width: 110px;">
                                                                    Separada
                                                                </th>

                                                                <th style="width: 110px;">
                                                                    Carregada
                                                                </th>
                                                            <?php endif; ?>

                                                            <th style="width: 95px;">
                                                                Saldo
                                                            </th>

                                                            <th style="width: 130px;">
                                                                Situação
                                                            </th>

                                                            <th style="width: 65px;">
                                                                Unid.
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>

                                                        <?php $__empty_1 = true; $__currentLoopData = $itensEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                                            <?php
                                                                $produto = $resolverProduto($item);

                                                                $itemRomaneio = $resolverItemRomaneio($item);

                                                                $quantidadePrevista =
                                                                    $resolverQuantidadePrevista($item);

                                                                $quantidadeRomaneio = (float) old(
                                                                    "itens.{$item->id}.quantidade",
                                                                    $itemRomaneio?->quantidade_prevista
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

                                                                $statusItem = strtolower(
                                                                    (string) old(
                                                                        "itens.{$item->id}.status",
                                                                        $itemRomaneio?->status
                                                                            ?? 'pendente'
                                                                    )
                                                                );

                                                                $unidade =
                                                                    $produto?->unidade_medida?->sigla
                                                                    ?? $produto?->unidade
                                                                    ?? 'UN';

                                                                $localizacao =
                                                                    $produto?->localizacao_estoque
                                                                    ?? $produto?->localizacao
                                                                    ?? '—';
                                                            ?>

                                                            <tr class="item-row"
                                                                data-prevista="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>"
                                                                data-romaneio="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>">

                                                                <td>

                                                                    <input type="hidden"
                                                                           name="itens[<?php echo e($item->id); ?>][entrega_item_id]"
                                                                           value="<?php echo e($item->id); ?>">

                                                                    <?php if($itemRomaneio): ?>
                                                                        <input type="hidden"
                                                                               name="itens[<?php echo e($item->id); ?>][romaneio_item_id]"
                                                                               value="<?php echo e($itemRomaneio->id); ?>">
                                                                    <?php endif; ?>

                                                                    <div class="product-name">
                                                                        <?php echo e($produto?->nome
                                                                            ?? $produto?->descricao
                                                                            ?? 'Produto não identificado'); ?>

                                                                    </div>

                                                                    <div class="product-code">
                                                                        Código:
                                                                        <?php echo e($produto?->codigo
                                                                            ?? $produto?->id
                                                                            ?? '-'); ?>

                                                                    </div>

                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($localizacao); ?>

                                                                </td>

                                                                <td class="text-end">
                                                                    <?php echo e(number_format(
                                                                        $quantidadePrevista,
                                                                        2,
                                                                        ',',
                                                                        '.'
                                                                    )); ?>

                                                                </td>

                                                                <td class="text-center">

                                                                    <?php if($criandoRomaneio): ?>

                                                                        <input type="number"
                                                                               name="itens[<?php echo e($item->id); ?>][quantidade]"
                                                                               value="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>"
                                                                               min="0.01"
                                                                               max="<?php echo e(number_format($quantidadePrevista, 2, '.', '')); ?>"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-quantity-romaneio>

                                                                    <?php else: ?>

                                                                        <strong>
                                                                            <?php echo e(number_format(
                                                                                $quantidadeRomaneio,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            )); ?>

                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[<?php echo e($item->id); ?>][quantidade]"
                                                                               value="<?php echo e(number_format($quantidadeRomaneio, 2, '.', '')); ?>">

                                                                    <?php endif; ?>

                                                                </td>

                                                                <?php if(! $criandoRomaneio): ?>

                                                                    <td class="text-center">

                                                                        <?php if($statusOriginal === 'em_separacao'): ?>

                                                                            <input type="number"
                                                                                name="itens[<?php echo e($item->id); ?>][quantidade_separada]"
                                                                                value="<?php echo e(number_format(
                                                                                    $quantidadeSeparada,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>"
                                                                                min="0"
                                                                                max="<?php echo e(number_format(
                                                                                    $quantidadeRomaneio,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>"
                                                                                step="1"
                                                                                class="form-control form-control-sm quantity-input
                                                                                    <?php $__errorArgs = ["itens.{$item->id}.quantidade_separada"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                                data-quantity-separated>

                                                                            <?php $__errorArgs = ["itens.{$item->id}.quantidade_separada"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                                <div class="invalid-feedback">
                                                                                    <?php echo e($message); ?>

                                                                                </div>
                                                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                                                        <?php else: ?>

                                                                            <strong>
                                                                                <?php echo e(number_format(
                                                                                    $quantidadeSeparada,
                                                                                    2,
                                                                                    ',',
                                                                                    '.'
                                                                                )); ?>

                                                                            </strong>

                                                                            <input type="hidden"
                                                                                name="itens[<?php echo e($item->id); ?>][quantidade_separada]"
                                                                                value="<?php echo e(number_format(
                                                                                    $quantidadeSeparada,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>">

                                                                        <?php endif; ?>

                                                                    </td>

                                                                    <td class="text-center">

                                                                        <?php if($statusOriginal === 'carregando'): ?>

                                                                            <input type="number"
                                                                                name="itens[<?php echo e($item->id); ?>][quantidade_carregada]"
                                                                                value="<?php echo e(number_format(
                                                                                    $quantidadeCarregada,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>"
                                                                                min="0"
                                                                                max="<?php echo e(number_format(
                                                                                    $quantidadeSeparada,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>"
                                                                                step="1"
                                                                                class="form-control form-control-sm quantity-input
                                                                                    <?php $__errorArgs = ["itens.{$item->id}.quantidade_carregada"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                                data-quantity-loaded>

                                                                            <?php $__errorArgs = ["itens.{$item->id}.quantidade_carregada"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                                <div class="invalid-feedback">
                                                                                    <?php echo e($message); ?>

                                                                                </div>
                                                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                                                        <?php else: ?>

                                                                            <strong>
                                                                                <?php echo e(number_format(
                                                                                    $quantidadeCarregada,
                                                                                    2,
                                                                                    ',',
                                                                                    '.'
                                                                                )); ?>

                                                                            </strong>

                                                                            <input type="hidden"
                                                                                name="itens[<?php echo e($item->id); ?>][quantidade_carregada]"
                                                                                value="<?php echo e(number_format(
                                                                                    $quantidadeCarregada,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                )); ?>">

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

                                                                    <?php if($statusOriginal === 'carregado'): ?>

                                                                        <select name="itens[<?php echo e($item->id); ?>][status]"
                                                                                class="form-select form-select-sm"
                                                                                data-status-item>

                                                                            <option value="conferido"
                                                                                <?php if($statusItem === 'conferido'): echo 'selected'; endif; ?>>
                                                                                Conferido
                                                                            </option>

                                                                            <option value="divergente"
                                                                                <?php if(in_array(
                                                                                    $statusItem,
                                                                                    ['divergente', 'parcial'],
                                                                                    true
                                                                                )): echo 'selected'; endif; ?>>
                                                                                Divergente
                                                                            </option>

                                                                        </select>

                                                                    <?php else: ?>

                                                                        <?php
                                                                            $classeStatus = match ($statusItem) {
                                                                                'separado',
                                                                                'carregado',
                                                                                'conferido' =>
                                                                                    'bg-success',

                                                                                'parcial',
                                                                                'divergente' =>
                                                                                    'bg-warning text-dark',

                                                                                'cancelado' =>
                                                                                    'bg-danger',

                                                                                default =>
                                                                                    'bg-secondary',
                                                                            };
                                                                        ?>

                                                                        <span class="badge <?php echo e($classeStatus); ?>">
                                                                            <?php echo e(ucfirst(
                                                                                str_replace(
                                                                                    '_',
                                                                                    ' ',
                                                                                    $statusItem
                                                                                )
                                                                            )); ?>

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
                                                                <td colspan="9"
                                                                    class="text-center text-muted py-4">
                                                                    Nenhum item encontrado.
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

                                <span class="summary-value">
                                    <?php echo e($totalEntregas); ?>

                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Itens
                                </span>

                                <span class="summary-value">
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
                                    Quantidade realizada
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

                                <div class="d-flex justify-content-between mb-1">

                                    <span class="summary-label">
                                        Progresso
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
                                         style="width: 0%;">
                                    </div>

                                </div>
                            </div>

                            <div class="next-step mt-3"
                                 id="nextStep">

                                <div class="fw-bold small mb-1"
                                     id="nextStepTitle">
                                    Verificando operação
                                </div>

                                <div class="text-muted small"
                                     id="nextStepText">
                                    Aguarde o cálculo dos itens.
                                </div>

                            </div>

                            <?php if($romaneioAtivo): ?>

                                <div class="control-panel">

                                    <div class="fw-bold small mb-2">
                                        Controles registrados
                                    </div>

                                    <div class="control-grid">

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Início separação
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e($formatarData(
                                                    $romaneioAtivo?->data_inicio_separacao,
                                                    true
                                                )); ?>

                                            </span>
                                        </div>

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Fim separação
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e($formatarData(
                                                    $romaneioAtivo?->data_fim_separacao,
                                                    true
                                                )); ?>

                                            </span>
                                        </div>

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Início carga
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e($formatarData(
                                                    $romaneioAtivo?->data_inicio_carregamento,
                                                    true
                                                )); ?>

                                            </span>
                                        </div>

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Fim carga
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e($formatarData(
                                                    $romaneioAtivo?->data_fim_carregamento,
                                                    true
                                                )); ?>

                                            </span>
                                        </div>

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Percentual
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e(number_format(
                                                    (float) $romaneioAtivo?->percentual_carregado,
                                                    2,
                                                    ',',
                                                    '.'
                                                )); ?>%
                                            </span>
                                        </div>

                                        <div class="control-item">
                                            <span class="control-item-label">
                                                Saída
                                            </span>

                                            <span class="control-item-value">
                                                <?php echo e($formatarData(
                                                    $romaneioAtivo?->data_saida,
                                                    true
                                                )); ?>

                                            </span>
                                        </div>

                                    </div>
                                </div>

                            <?php endif; ?>

                            <hr>

                            <div class="small text-muted">

                                <div class="mb-2">
                                    <i class="bi bi-person-badge me-1"></i>
                                    Motorista:

                                    <strong>
                                        <?php echo e($romaneioAtivo?->motorista?->nome
                                            ?? 'A definir'); ?>

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

                        <?php if($criandoRomaneio): ?>
                            A criação prepara o romaneio para o início da separação.

                        <?php elseif($statusOriginal === 'gerado'): ?>
                            Inicie a separação para registrar operador e horário.

                        <?php elseif($statusOriginal === 'em_separacao'): ?>
                            Salve o andamento ou finalize a separação.

                        <?php elseif($statusOriginal === 'separado'): ?>
                            Encaminhe o romaneio para a doca.

                        <?php elseif($statusOriginal === 'na_doca'): ?>
                            Inicie o carregamento do veículo.

                        <?php elseif($statusOriginal === 'carregando'): ?>
                            Salve o andamento ou finalize o carregamento.

                        <?php elseif($statusOriginal === 'carregado'): ?>
                            Confira todos os itens da carga.

                        <?php elseif($statusOriginal === 'conferido'): ?>
                            Imprima e libere o romaneio.

                        <?php elseif($statusOriginal === 'liberado'): ?>
                            Registre a saída física do veículo.

                        <?php elseif(in_array($statusOriginal, ['saiu_para_entrega', 'em_rota'], true)): ?>
                            Veículo em rota de entrega.

                        <?php endif; ?>

                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        <a href="<?php echo e(route('entregas.index')); ?>"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Fechar
                        </a>

                        <?php if(
                            ! $criandoRomaneio &&
                            $podeVoltarEtapa
                        ): ?>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalVoltarEtapa">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Voltar Etapa

                            </button>
                        <?php endif; ?>

                        <?php if(
                            ! $criandoRomaneio &&
                            $podeSalvarAndamento
                        ): ?>
                            <button type="submit"
                                    name="acao"
                                    value="salvar_andamento"
                                    class="btn btn-outline-primary btn-sm">

                                <i class="bi bi-floppy me-1"></i>
                                Salvar Andamento

                            </button>
                        <?php endif; ?>

                        <?php if($criandoRomaneio): ?>

                            <button type="submit"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-check-circle me-1"></i>
                                Criar Romaneio

                            </button>

                        <?php elseif($statusOriginal === 'gerado'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="iniciar_separacao"
                                    class="btn btn-warning btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-play-circle me-1"></i>
                                Iniciar Separação

                            </button>

                        <?php elseif($statusOriginal === 'em_separacao'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="finalizar_separacao"
                                    class="btn btn-warning btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-box-seam me-1"></i>
                                Finalizar Separação

                            </button>

                        <?php elseif($statusOriginal === 'separado'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="enviar_para_doca"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-arrow-right-circle me-1"></i>
                                Enviar para Doca

                            </button>

                        <?php elseif($statusOriginal === 'na_doca'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="iniciar_carregamento"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-play-circle me-1"></i>
                                Iniciar Carregamento

                            </button>

                        <?php elseif($statusOriginal === 'carregando'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="finalizar_carregamento"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-truck-front me-1"></i>
                                Finalizar Carregamento

                            </button>

                        <?php elseif($statusOriginal === 'carregado'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="concluir_conferencia"
                                    class="btn btn-info btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-clipboard-check me-1"></i>
                                Concluir Conferência

                            </button>

                        <?php elseif($statusOriginal === 'conferido'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="liberar_veiculo"
                                    class="btn btn-success btn-sm"
                                    id="btnPrincipal"
                                    <?php if(
                                        empty(
                                            $romaneioAtivo?->impresso_em
                                        )
                                    ): echo 'disabled'; endif; ?>>

                                <i class="bi bi-shield-check me-1"></i>
                                Liberar Romaneio

                            </button>

                        <?php elseif($statusOriginal === 'liberado'): ?>

                            <button type="submit"
                                    name="acao"
                                    value="registrar_saida"
                                    class="btn btn-dark btn-sm"
                                    id="btnPrincipal">

                                <i class="bi bi-truck me-1"></i>
                                Registrar Saída

                            </button>

                        <?php elseif(in_array(
                            $statusOriginal,
                            ['saiu_para_entrega', 'em_rota'],
                            true
                        )): ?>

                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    disabled>

                                <i class="bi bi-check-circle me-1"></i>
                                Veículo em Rota

                            </button>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </form>

        <?php if(
            ! $criandoRomaneio &&
            $statusOriginal === 'conferido'
        ): ?>
            <form id="formImprimirRomaneio"
                  method="POST"
                  action="<?php echo e(route(
                      'romaneios.registrar-impressao',
                      $romaneioAtivo
                  )); ?>"
                  class="d-none">
                <?php echo csrf_field(); ?>
            </form>
        <?php endif; ?>

        <?php if(
            ! $criandoRomaneio &&
            $podeVoltarEtapa
        ): ?>
            <div class="modal fade"
                 id="modalVoltarEtapa"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title">
                                Retornar etapa
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">

                            <label for="motivo_retorno"
                                   class="form-label">
                                Motivo do retorno
                            </label>

                            <textarea id="motivo_retorno"
                                      class="form-control"
                                      rows="4"
                                      maxlength="500"
                                      placeholder="Informe por que o romaneio precisa retornar de etapa."></textarea>

                        </div>

                        <div class="modal-footer">

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="button"
                                    class="btn btn-danger"
                                    id="btnConfirmarRetorno">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Confirmar Retorno

                            </button>

                        </div>

                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formRomaneio');

    if (!form) {
        return;
    }

    const etapaAtual =
        document.querySelector('[name="etapa_atual"]')?.value
        ?? 'montagem';

    const statusOriginal = <?php echo json_encode($statusOriginal, 15, 512) ?>;

    const rows = [
        ...document.querySelectorAll('.item-row')
    ];

    const summaryExpected =
        document.getElementById('summaryExpected');

    const summaryCompleted =
        document.getElementById('summaryCompleted');

    const summaryPending =
        document.getElementById('summaryPending');

    const summaryPercent =
        document.getElementById('summaryPercent');

    const summaryProgress =
        document.getElementById('summaryProgress');

    const nextStep =
        document.getElementById('nextStep');

    const nextStepTitle =
        document.getElementById('nextStepTitle');

    const nextStepText =
        document.getElementById('nextStepText');

    const btnPrincipal =
        document.getElementById('btnPrincipal');

    function numero(valor) {
        if (
            valor === null ||
            valor === undefined ||
            valor === ''
        ) {
            return 0;
        }

        return parseFloat(
            String(valor).replace(',', '.')
        ) || 0;
    }

    function formatarNumero(valor) {
        return Number(valor).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
    }

    function quantidadeRomaneio(row) {
        const input = row.querySelector(
            '[data-quantity-romaneio]'
        );

        if (input) {
            return numero(input.value);
        }

        return numero(row.dataset.romaneio);
    }

    function quantidadeSeparada(row) {
        const input = row.querySelector(
            '[data-quantity-separated]'
        );

        if (input) {
            return numero(input.value);
        }

        const hidden = row.querySelector(
            'input[name$="[quantidade_separada]"]'
        );

        return hidden
            ? numero(hidden.value)
            : 0;
    }

    function quantidadeCarregada(row) {
        const input = row.querySelector(
            '[data-quantity-loaded]'
        );

        if (input) {
            return numero(input.value);
        }

        const hidden = row.querySelector(
            'input[name$="[quantidade_carregada]"]'
        );

        return hidden
            ? numero(hidden.value)
            : 0;
    }

    function quantidadeEtapa(row) {
        if (etapaAtual === 'montagem') {
            return quantidadeRomaneio(row);
        }

        if (statusOriginal === 'gerado') {
            return 0;
        }

        if (
            etapaAtual === 'separacao' ||
            statusOriginal === 'separado' ||
            statusOriginal === 'na_doca'
        ) {
            return quantidadeSeparada(row);
        }

        return quantidadeCarregada(row);
    }

    function atualizarLinha(row) {
        const prevista =
            numero(row.dataset.prevista);

        const realizada =
            quantidadeEtapa(row);

        const saldo = Math.max(
            0,
            prevista - realizada
        );

        const saldoSpan = row.querySelector(
            '[data-balance]'
        );

        if (saldoSpan) {
            saldoSpan.textContent =
                formatarNumero(saldo);

            saldoSpan.classList.toggle(
                'pending',
                saldo > 0.0001
            );

            saldoSpan.classList.toggle(
                'complete',
                saldo <= 0.0001
            );
        }

        return {
            prevista,
            realizada,
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
            ? Math.min(
                100,
                (realizada / prevista) * 100
            )
            : 0;

        if (summaryExpected) {
            summaryExpected.textContent =
                formatarNumero(prevista);
        }

        if (summaryCompleted) {
            summaryCompleted.textContent =
                formatarNumero(realizada);
        }

        if (summaryPending) {
            summaryPending.textContent =
                String(pendentes);
        }

        if (summaryPercent) {
            summaryPercent.textContent =
                Math.round(percentual) + '%';
        }

        if (summaryProgress) {
            summaryProgress.style.width =
                percentual + '%';
        }

        if (nextStep) {
            nextStep.classList.toggle(
                'ready',
                pendentes === 0
            );
        }

        if (
            !nextStepTitle ||
            !nextStepText
        ) {
            return;
        }

        if (statusOriginal === 'gerado') {
            nextStepTitle.textContent =
                'Aguardando início da separação';

            nextStepText.textContent =
                'Clique em Iniciar Separação para registrar a operação.';

            return;
        }

        if (statusOriginal === 'separado') {
            nextStepTitle.textContent =
                'Separação finalizada';

            nextStepText.textContent =
                'O próximo evento é o envio para a doca.';

            return;
        }

        if (statusOriginal === 'na_doca') {
            nextStepTitle.textContent =
                'Romaneio na doca';

            nextStepText.textContent =
                'O veículo já pode iniciar o carregamento.';

            return;
        }

        if (statusOriginal === 'carregado') {
            nextStepTitle.textContent =
                'Carga concluída';

            nextStepText.textContent =
                'Confira a situação de todos os itens.';

            return;
        }

        if (statusOriginal === 'conferido') {
            nextStepTitle.textContent =
                'Conferência concluída';

            nextStepText.textContent =
                'Imprima o romaneio antes de liberar.';

            return;
        }

        if (statusOriginal === 'liberado') {
            nextStepTitle.textContent =
                'Romaneio liberado';

            nextStepText.textContent =
                'Registre a saída física do veículo.';

            return;
        }

        if (
            statusOriginal === 'saiu_para_entrega' ||
            statusOriginal === 'em_rota'
        ) {
            nextStepTitle.textContent =
                'Veículo em rota';

            nextStepText.textContent =
                'A saída foi registrada com sucesso.';

            return;
        }

        nextStepTitle.textContent =
            pendentes === 0
                ? 'Etapa concluída'
                : 'Etapa em andamento';

        nextStepText.textContent =
            pendentes === 0
                ? 'A operação pode avançar.'
                : 'Ainda existem itens pendentes.';
    }

    document
        .querySelectorAll(
            '[data-quantity-romaneio], ' +
            '[data-quantity-separated], ' +
            '[data-quantity-loaded], ' +
            '[data-status-item]'
        )
        .forEach(elemento => {
            elemento.addEventListener(
                'input',
                atualizarResumo
            );

            elemento.addEventListener(
                'change',
                atualizarResumo
            );
        });

    form.addEventListener('submit', event => {
        const botaoSubmit = event.submitter;

        const acao = botaoSubmit?.value ?? '';

        if (
            acao === 'finalizar_separacao' ||
            acao === 'finalizar_carregamento'
        ) {
            const possuiPendencia = rows.some(
                row => atualizarLinha(row).saldo > 0.0001
            );

            if (possuiPendencia) {
                event.preventDefault();

                window.alert(
                    'Existem itens pendentes. ' +
                    'Conclua todas as quantidades antes de avançar.'
                );

                return;
            }
        }

        if (acao === 'concluir_conferencia') {
            const possuiDivergencia = rows.some(row => {
                const select = row.querySelector(
                    '[data-status-item]'
                );

                return (
                    select &&
                    select.value !== 'conferido'
                );
            });

            if (possuiDivergencia) {
                event.preventDefault();

                window.alert(
                    'Existem itens divergentes. ' +
                    'Todos precisam estar conferidos.'
                );

                return;
            }
        }

        if (acao) {
            const inputAcaoExistente = form.querySelector(
                'input[type="hidden"][name="acao"]'
            );

            if (inputAcaoExistente) {
                inputAcaoExistente.value = acao;
            } else {
                const inputAcao = document.createElement('input');

                inputAcao.type = 'hidden';
                inputAcao.name = 'acao';
                inputAcao.value = acao;

                form.appendChild(inputAcao);
            }
        }

        if (btnPrincipal) {
            btnPrincipal.disabled = true;
        }
    });

    const btnConfirmarRetorno =
        document.getElementById(
            'btnConfirmarRetorno'
        );

    btnConfirmarRetorno?.addEventListener(
        'click',
        () => {
            const motivo = document
                .getElementById('motivo_retorno')
                ?.value
                ?.trim();

            if (!motivo || motivo.length < 5) {
                window.alert(
                    'Informe um motivo com pelo menos 5 caracteres.'
                );

                return;
            }

            const inputAcao =
                document.createElement('input');

            inputAcao.type = 'hidden';
            inputAcao.name = 'acao';
            inputAcao.value = 'voltar_etapa';

            const inputMotivo =
                document.createElement('input');

            inputMotivo.type = 'hidden';
            inputMotivo.name = 'motivo_retorno';
            inputMotivo.value = motivo;

            form.appendChild(inputAcao);
            form.appendChild(inputMotivo);

            form.submit();
        }
    );

    atualizarResumo();
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/create.blade.php ENDPATH**/ ?>