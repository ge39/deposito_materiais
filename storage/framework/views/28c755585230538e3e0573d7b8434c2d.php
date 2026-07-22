

<?php $__env->startSection('content'); ?>

<?php
    $statusRomaneio = (string) $romaneio->status;

    $registrandoRetorno = in_array(
        $statusRomaneio,
        [
            'Em_rota',
            'Retornando',
        ],
        true
    );

    $aguardandoConferencia =
        $statusRomaneio === 'Aguardando_conferencia_retorno';

    $conferindoRetorno =
        $statusRomaneio === 'Em_conferencia_retorno';

    $conferenciaFinalizada = in_array(
        $statusRomaneio,
        [
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
            'Fechado',
        ],
        true
    );

    $podeEditarResultados =
        $registrandoRetorno
        || $conferindoRetorno;

    $conferenteSelecionado = old(
        'retorno_conferido_por',
        $romaneio->itens
            ->pluck('retorno_conferido_por')
            ->filter()
            ->first()
    );

    $rotaFormulario = match (true) {
        $registrandoRetorno => route(
            'entregas.registrar-retorno',
            $entrega->id
        ),

        $aguardandoConferencia => route(
            'entregas.iniciar-conferencia-retorno',
            $entrega->id
        ),

        $conferindoRetorno => route(
            'entregas.finalizar-conferencia-retorno',
            $entrega->id
        ),

        default => null,
    };

    $cliente =
        $entrega->cliente
        ?? $entrega->venda?->cliente
        ?? $entrega->orcamento?->cliente;

    $nomeCliente =
        $cliente?->nome
        ?? $cliente?->razao_social
        ?? 'Cliente não identificado';

    $motorista =
        $romaneio->motorista?->nome
        ?? $entrega->motorista?->nome
        ?? 'Não informado';

    $veiculo =
        $romaneio->veiculo?->placa
        ?? $entrega->veiculo?->placa
        ?? 'Não informado';

    $totalProdutos =
        $romaneio->itens->count();

    $totalVolumes =
        $romaneio->itens->sum(
            fn ($item) =>
                (float) $item->quantidade_conferida_saida
        );

    $statusLabels = [
        'Em_rota' =>
            'Em rota',

        'Retornando' =>
            'Aguardando retorno',

        'Aguardando_conferencia_retorno' =>
            'Aguardando conferência',

        'Em_conferencia_retorno' =>
            'Conferência em andamento',

        'Aguardando_prestacao_contas' =>
            'Aguardando prestação de contas',

        'Em_prestacao_contas' =>
            'Em prestação de contas',

        'Aguardando_fechamento' =>
            'Aguardando fechamento',

        'Fechado' =>
            'Fechado',
    ];

    $abrirOcorrencias =
        old('tipo_retorno') === 'ocorrencia'
        || $errors->has('observacao_retorno')
        || $errors->has('itens')
        || collect($errors->keys())
            ->contains(
                fn ($chave) =>
                    str_starts_with(
                        $chave,
                        'itens.'
                    )
            );
?>

<style>
    .retorno-page {
        --erp-border: #d8dde3;
        --erp-muted: #6c757d;
        --erp-dark: #343a40;
        --erp-success: #198754;
        --erp-success-soft: #eaf7ef;
        --erp-warning-soft: #fff5df;
        --erp-danger-soft: #fcebec;
        --erp-info-soft: #e8f7fa;
    }

    .retorno-title {
        font-size: 1.3rem;
        font-weight: 800;
        margin: 0;
    }

    .retorno-subtitle {
        color: var(--erp-muted);
        font-size: .8rem;
    }

    .retorno-card {
        background: #fff;
        border: 1px solid var(--erp-border);
        border-radius: 8px;
        box-shadow: 0 .15rem .45rem rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .retorno-card-header {
        align-items: center;
        background: #6c757d;
        color: #fff;
        display: flex;
        font-size: .88rem;
        font-weight: 800;
        justify-content: space-between;
        padding: .7rem .9rem;
    }

    .retorno-info-label {
        color: #687078;
        display: block;
        font-size: .67rem;
        font-weight: 800;
        letter-spacing: .035em;
        margin-bottom: .18rem;
        text-transform: uppercase;
    }

    .retorno-info-value {
        color: #212529;
        font-size: .86rem;
        font-weight: 700;
    }

    .retorno-decisao {
        border: 1px solid #75b798;
        border-radius: 9px;
        padding: 1.5rem;
        text-align: center;
    }

    .retorno-decisao-icone {
        align-items: center;
        background: var(--erp-success);
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        font-size: 2rem;
        height: 66px;
        justify-content: center;
        margin-bottom: .8rem;
        width: 66px;
    }

    .retorno-decisao-titulo {
        color: #157347;
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .retorno-decisao-texto {
        color: #6c757d;
        font-size: .86rem;
        margin-bottom: 1.25rem;
    }

    .retorno-resumo-operacional {
        border: 1px solid var(--erp-border);
        border-radius: 7px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        margin: 0 auto 1.25rem;
        max-width: 950px;
    }

    .retorno-resumo-item {
        align-items: center;
        display: flex;
        font-size: .82rem;
        font-weight: 700;
        gap: .55rem;
        justify-content: center;
        min-height: 64px;
        padding: .75rem;
    }

    .retorno-resumo-item + .retorno-resumo-item {
        border-left: 1px solid var(--erp-border);
    }

    .retorno-resumo-item i {
        color: #495057;
        font-size: 1.25rem;
    }

    .retorno-botoes-decisao {
        display: flex;
        flex-wrap: wrap;
        gap: .9rem;
        justify-content: center;
    }

    .retorno-botoes-decisao .btn {
        font-size: .92rem;
        font-weight: 700;
        min-height: 52px;
        min-width: 330px;
    }

    .retorno-ajuda {
        color: #6c757d;
        font-size: .75rem;
        margin-top: .85rem;
    }

    .retorno-banner {
        border: 1px solid #9eeaf9;
        border-radius: 8px;
        padding: .85rem 1rem;
    }

    .retorno-banner.aguardando {
        background: var(--erp-warning-soft);
        border-color: #ffda6a;
    }

    .retorno-banner.conferindo {
        background: #eaf2ff;
        border-color: #9ec5fe;
    }

    .retorno-banner.finalizado {
        background: var(--erp-success-soft);
        border-color: #75b798;
    }

    .retorno-table {
        margin-bottom: 0;
        min-width: 1150px;
    }

    .retorno-table th {
        background: var(--erp-dark);
        color: #fff;
        font-size: .65rem;
        font-weight: 800;
        padding: .6rem .45rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .retorno-table td {
        font-size: .75rem;
        padding: .45rem;
        vertical-align: middle;
    }

    .retorno-table .col-saida {
        background: #e7f1ff;
    }

    .retorno-table th.col-saida {
        background: #0d6efd;
    }

    .retorno-table .col-entregue {
        background: var(--erp-success-soft);
    }

    .retorno-table th.col-entregue {
        background: #198754;
    }

    .retorno-table .col-devolvida {
        background: var(--erp-warning-soft);
    }

    .retorno-table th.col-devolvida {
        background: #ffc107;
        color: #212529;
    }

    .retorno-table .col-ocorrencia {
        background: var(--erp-danger-soft);
    }

    .retorno-table th.col-ocorrencia {
        background: #dc3545;
    }

    .produto-nome {
        font-size: .8rem;
        font-weight: 750;
    }

    .produto-codigo {
        color: #6c757d;
        font-size: .68rem;
    }

    .resultado-input {
        min-width: 82px;
        text-align: right;
    }

    .resultado-input:disabled {
        background: transparent;
        border-color: transparent;
        color: #212529;
        opacity: 1;
    }

    .resultado-total {
        font-size: .82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .resultado-total.valido {
        color: #198754;
    }

    .resultado-total.invalido {
        color: #dc3545;
    }

    .linha-retorno.invalida
    .resultado-input:not(:disabled) {
        border-color: #dc3545;
    }

    .linha-retorno.valida
    .resultado-input:not(:disabled) {
        border-color: #75b798;
    }

    .mensagem-item {
        color: #dc3545;
        display: none;
        font-size: .67rem;
        margin-top: .2rem;
    }

    .linha-retorno.invalida .mensagem-item {
        display: block;
    }

    .retorno-resumo {
        position: sticky;
        top: 1rem;
    }

    .resumo-linha {
        align-items: center;
        border-bottom: 1px solid #edf0f2;
        display: flex;
        justify-content: space-between;
        padding: .55rem 0;
    }

    .resumo-label {
        color: #6c757d;
        font-size: .72rem;
        font-weight: 700;
    }

    .resumo-valor {
        font-size: .84rem;
        font-weight: 800;
    }

    .retorno-actions {
        align-items: center;
        display: flex;
        gap: .7rem;
        justify-content: space-between;
        padding: .8rem;
    }

    .ocorrencias-section {
        display: none;
    }

    .ocorrencias-section.aberta {
        display: block;
    }

    @media (max-width: 991.98px) {
        .retorno-resumo-operacional {
            grid-template-columns: repeat(2, 1fr);
        }

        .retorno-resumo-item:nth-child(3) {
            border-left: 0;
            border-top: 1px solid var(--erp-border);
        }

        .retorno-resumo-item:nth-child(4) {
            border-top: 1px solid var(--erp-border);
        }

        .retorno-resumo {
            position: static;
        }

        .retorno-actions {
            align-items: stretch;
            flex-direction: column;
        }
    }

    @media (max-width: 575.98px) {
        .retorno-resumo-operacional {
            grid-template-columns: 1fr;
        }

        .retorno-resumo-item + .retorno-resumo-item {
            border-left: 0;
            border-top: 1px solid var(--erp-border);
        }

        .retorno-botoes-decisao .btn {
            min-width: 100%;
        }
    }
</style>

<div class="container-fluid retorno-page py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="retorno-title">
                <i class="bi bi-truck me-1"></i>
                Retorno da entrega
            </h1>

            <div class="retorno-subtitle">
                Confirme o resultado da entrega e finalize o retorno do veículo.
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark">
                <?php echo e($romaneio->codigo_romaneio); ?>

            </span>

            <span class="badge <?php echo e($conferenciaFinalizada
                    ? 'bg-success'
                    : (
                        $aguardandoConferencia
                            ? 'bg-warning text-dark'
                            : 'bg-primary'
                    )); ?>">
                <?php echo e($statusLabels[$statusRomaneio]
                    ?? str_replace(
                        '_',
                        ' ',
                        $statusRomaneio
                    )); ?>

            </span>

            <a
                href="<?php echo e(route('entregas.index')); ?>"
                class="btn btn-outline-secondary btn-sm"
            >
                <i class="bi bi-arrow-left"></i>
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
            <div class="fw-bold mb-1">
                Não foi possível concluir a operação.
            </div>

            <ul class="mb-0 ps-3">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="retorno-card mb-3">
        <div class="retorno-card-header">
            <span>
                <i class="bi bi-info-square me-1"></i>
                Dados da entrega
            </span>
        </div>

        <div class="p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <span class="retorno-info-label">
                        Entrega
                    </span>

                    <div class="retorno-info-value">
                        <?php echo e($entrega->codigo_entrega
                            ?? "#{$entrega->id}"); ?>

                    </div>
                </div>

                <div class="col-md-3">
                    <span class="retorno-info-label">
                        Cliente
                    </span>

                    <div class="retorno-info-value">
                        <?php echo e($nomeCliente); ?>

                    </div>
                </div>

                <div class="col-md-3">
                    <span class="retorno-info-label">
                        Motorista
                    </span>

                    <div class="retorno-info-value">
                        <?php echo e($motorista); ?>

                    </div>
                </div>

                <div class="col-md-3">
                    <span class="retorno-info-label">
                        Veículo
                    </span>

                    <div class="retorno-info-value">
                        <?php echo e($veiculo); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($rotaFormulario): ?>
        <form
            method="POST"
            action="<?php echo e($rotaFormulario); ?>"
            id="form-retorno"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>
    <?php endif; ?>

    <?php if($registrandoRetorno): ?>
        <input
            type="hidden"
            name="tipo_retorno"
            id="tipo_retorno"
            value="<?php echo e(old('tipo_retorno', 'normal')); ?>"
        >

        <div
            class="retorno-decisao mb-3"
            id="painel-decisao"
        >
            <div class="retorno-decisao-icone">
                <i class="bi bi-check-lg"></i>
            </div>

            <div class="retorno-decisao-titulo">
                A entrega ocorreu normalmente?
            </div>

            <div class="retorno-decisao-texto">
                Confirme que todos os materiais foram entregues e que não houve
                ocorrências no trajeto ou no cliente.
            </div>

            <div class="retorno-resumo-operacional">
                <div class="retorno-resumo-item">
                    <i class="bi bi-box-seam"></i>
                    <span>
                        <?php echo e($totalProdutos); ?>

                        produto(s)
                    </span>
                </div>

                <div class="retorno-resumo-item">
                    <i class="bi bi-box"></i>
                    <span>
                        <?php echo e(number_format(
                                $totalVolumes,
                                2,
                                ',',
                                '.'
                            )); ?>

                        volume(s)
                    </span>
                </div>

                <div class="retorno-resumo-item">
                    <i class="bi bi-clock"></i>
                    <span>
                        Saída:
                        <?php echo e($romaneio->data_saida
                                ? $romaneio->data_saida->format('d/m/Y H:i')
                                : 'Não registrada'); ?>

                    </span>
                </div>

                <div class="retorno-resumo-item">
                    <i class="bi bi-arrow-return-left"></i>
                    <span>
                        Retorno:
                        <?php echo e(now()->format('d/m/Y H:i')); ?>

                    </span>
                </div>
            </div>

            <div class="retorno-botoes-decisao">
                <button
                    type="button"
                    class="btn btn-success"
                    id="btn-retorno-normal"
                >
                    <i class="bi bi-check-circle me-1"></i>
                    Sim, entrega realizada normalmente
                </button>

                <button
                    type="button"
                    class="btn btn-outline-danger"
                    id="btn-abrir-ocorrencias"
                >
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Houve alguma falha na entrega?
                </button>
            </div>

            <div class="retorno-ajuda">
                Ao informar uma falha, será aberta a conferência detalhada
                dos produtos e das ocorrências do trajeto.
            </div>
        </div>
    <?php elseif($aguardandoConferencia): ?>
        <div class="retorno-banner aguardando mb-3">
            <div class="fw-bold">
                <i class="bi bi-person-check me-1"></i>
                Aguardando início da conferência física
            </div>

            <div class="small text-muted mt-1">
                Escolha o funcionário responsável e inicie a conferência.
            </div>
        </div>
    <?php elseif($conferindoRetorno): ?>
        <div class="retorno-banner conferindo mb-3">
            <div class="fw-bold">
                <i class="bi bi-clipboard-check me-1"></i>
                Conferência física em andamento
            </div>

            <div class="small text-muted mt-1">
                Confira e corrija as quantidades antes de finalizar.
            </div>
        </div>
    <?php else: ?>
        <div class="retorno-banner finalizado mb-3">
            <div class="fw-bold text-success">
                <i class="bi bi-check-circle me-1"></i>
                Conferência do retorno finalizada
            </div>

            <div class="small text-muted mt-1">
                O romaneio foi encaminhado para a prestação de contas.
            </div>

            <div class="mt-3">
                <a
                    href="<?php echo e(route(
                        'entregas.retorno.imprimir',
                        $entrega->id
                    )); ?>"
                    class="btn btn-outline-dark btn-sm"
                    target="_blank"
                >
                    <i class="bi bi-printer me-1"></i>
                    Imprimir relatório do retorno
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if($aguardandoConferencia || $conferindoRetorno): ?>
        <div class="retorno-card mb-3">
            <div class="retorno-card-header">
                <span>
                    <i class="bi bi-person-check me-1"></i>
                    Responsável pela conferência
                </span>
            </div>

            <div class="p-3">
                <div class="row">
                    <div class="col-lg-5">
                        <label
                            for="retorno_conferido_por"
                            class="form-label fw-bold"
                        >
                            Conferente do retorno
                        </label>

                        <?php if($conferindoRetorno): ?>
                            <input
                                type="hidden"
                                name="retorno_conferido_por"
                                value="<?php echo e($conferenteSelecionado); ?>"
                            >
                        <?php endif; ?>

                        <select
                            name="<?php echo e($conferindoRetorno
                                    ? 'retorno_conferido_por_visual'
                                    : 'retorno_conferido_por'); ?>"
                            id="retorno_conferido_por"
                            class="form-select"
                            required
                            <?php echo e($conferindoRetorno ? 'disabled' : ''); ?>

                        >
                            <option value="">
                                Selecione...
                            </option>

                            <?php $__currentLoopData = $funcionariosOperacionais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $funcionario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($funcionario->id); ?>"
                                    <?php if(
                                        (int) $conferenteSelecionado
                                        === (int) $funcionario->id
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($funcionario->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
        $mostrarDetalhamento =
            ! $registrandoRetorno
            || $abrirOcorrencias;
    ?>

    <?php if(
        $registrandoRetorno
        || $conferindoRetorno
        || $conferenciaFinalizada
    ): ?>
        <div
            class="ocorrencias-section <?php echo e($mostrarDetalhamento
                    ? 'aberta'
                    : ''); ?>"
            id="secao-ocorrencias"
        >
            <div class="row g-3">
                <div class="col-xl-10">
                    <div class="retorno-card">
                        <div class="retorno-card-header">
                            <span>
                                <i class="bi bi-box-seam me-1"></i>
                                Resultado por produto
                            </span>

                            <span>
                                <?php echo e($totalProdutos); ?>

                                produto(s)
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered retorno-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th class="col-saida">
                                            Conf. saída
                                        </th>
                                        <th class="col-entregue">
                                            Entregue
                                        </th>
                                        <th class="col-devolvida">
                                            Devolvida
                                        </th>
                                        <th class="col-ocorrencia">
                                            Recusada
                                        </th>
                                        <th class="col-ocorrencia">
                                            Avariada
                                        </th>
                                        <th class="col-ocorrencia">
                                            Perdida
                                        </th>
                                        <th>Resultado</th>
                                        <th>Observação</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens
                                            ->sortBy('ordem')
                                            ->values(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $romaneioItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $entregaItem =
                                                $romaneioItem->entregaItem;

                                            $produto =
                                                $entregaItem?->produto
                                                ?? $entregaItem?->vendaItem?->produto
                                                ?? $entregaItem?->itemOrcamento?->produto;

                                            $nomeProduto =
                                                $produto?->nome
                                                ?? $produto?->descricao
                                                ?? "Produto #{$romaneioItem->entrega_item_id}";

                                            $codigoProduto =
                                                $produto?->codigo
                                                ?? $produto?->id
                                                ?? '-';

                                            $quantidadeSaida = (float)
                                                $romaneioItem
                                                    ->quantidade_conferida_saida;

                                            $quantidadeEntregue = old(
                                                "itens.{$indice}.quantidade_entregue",
                                                $registrandoRetorno
                                                    ? $quantidadeSaida
                                                    : (
                                                        $romaneioItem
                                                            ->quantidade_entregue
                                                        ?? 0
                                                    )
                                            );

                                            $quantidadeDevolvida = old(
                                                "itens.{$indice}.quantidade_devolvida",
                                                $romaneioItem
                                                    ->quantidade_devolvida
                                                ?? 0
                                            );

                                            $quantidadeRecusada = old(
                                                "itens.{$indice}.quantidade_recusada",
                                                $romaneioItem
                                                    ->quantidade_recusada
                                                ?? 0
                                            );

                                            $quantidadeAvariada = old(
                                                "itens.{$indice}.quantidade_avariada",
                                                $romaneioItem
                                                    ->quantidade_avariada
                                                ?? 0
                                            );

                                            $quantidadePerdida = old(
                                                "itens.{$indice}.quantidade_perdida",
                                                $romaneioItem
                                                    ->quantidade_perdida
                                                ?? 0
                                            );

                                            $observacaoItem = old(
                                                "itens.{$indice}.observacao",
                                                $romaneioItem
                                                    ->observacao
                                                ?? ''
                                            );
                                        ?>

                                        <tr
                                            class="linha-retorno"
                                            data-quantidade-saida="<?php echo e(number_format(
                                                    $quantidadeSaida,
                                                    2,
                                                    '.',
                                                    ''
                                                )); ?>"
                                        >
                                            <td>
                                                <?php if($rotaFormulario): ?>
                                                    <input
                                                        type="hidden"
                                                        name="itens[<?php echo e($indice); ?>][romaneio_item_id]"
                                                        value="<?php echo e($romaneioItem->id); ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="itens[<?php echo e($indice); ?>][entrega_item_id]"
                                                        value="<?php echo e($romaneioItem->entrega_item_id); ?>"
                                                    >
                                                <?php endif; ?>

                                                <div class="produto-nome">
                                                    <?php echo e($nomeProduto); ?>

                                                </div>

                                                <div class="produto-codigo">
                                                    Código:
                                                    <?php echo e($codigoProduto); ?>

                                                </div>
                                            </td>

                                            <td class="col-saida text-end fw-bold">
                                                <?php echo e(number_format(
                                                        $quantidadeSaida,
                                                        2,
                                                        ',',
                                                        '.'
                                                    )); ?>

                                            </td>

                                            <?php $__currentLoopData = [
                                                'quantidade_entregue' =>
                                                    $quantidadeEntregue,

                                                'quantidade_devolvida' =>
                                                    $quantidadeDevolvida,

                                                'quantidade_recusada' =>
                                                    $quantidadeRecusada,

                                                'quantidade_avariada' =>
                                                    $quantidadeAvariada,

                                                'quantidade_perdida' =>
                                                    $quantidadePerdida,
                                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $valor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $classeColuna = match (
                                                        $campo
                                                    ) {
                                                        'quantidade_entregue' =>
                                                            'col-entregue',

                                                        'quantidade_devolvida' =>
                                                            'col-devolvida',

                                                        default =>
                                                            'col-ocorrencia',
                                                    };
                                                ?>

                                                <td class="<?php echo e($classeColuna); ?>">
                                                    <input
                                                        type="number"
                                                        name="itens[<?php echo e($indice); ?>][<?php echo e($campo); ?>]"
                                                        value="<?php echo e($valor); ?>"
                                                        class="form-control form-control-sm resultado-input"
                                                        min="0"
                                                        max="<?php echo e($quantidadeSaida); ?>"
                                                        step="0.01"
                                                        <?php echo e($podeEditarResultados
                                                                ? ''
                                                                : 'disabled'); ?>

                                                    >
                                                </td>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <td class="text-center">
                                                <div class="resultado-total">
                                                    0,00 /
                                                    <?php echo e(number_format(
                                                            $quantidadeSaida,
                                                            2,
                                                            ',',
                                                            '.'
                                                        )); ?>

                                                </div>

                                                <div class="mensagem-item">
                                                    A soma deve ser igual à saída.
                                                </div>
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="itens[<?php echo e($indice); ?>][observacao]"
                                                    value="<?php echo e($observacaoItem); ?>"
                                                    class="form-control form-control-sm"
                                                    maxlength="500"
                                                    placeholder="Ocorrência do produto"
                                                    <?php echo e($podeEditarResultados
                                                            ? ''
                                                            : 'disabled'); ?>

                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td
                                                colspan="9"
                                                class="text-center text-muted py-4"
                                            >
                                                Nenhum produto encontrado
                                                neste romaneio.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if($registrandoRetorno || $conferindoRetorno): ?>
                        <div class="retorno-card mt-3">
                            <div class="retorno-card-header">
                                <span>
                                    <i class="bi bi-signpost me-1"></i>
                                    Ocorrência no trajeto ou no cliente
                                </span>
                            </div>

                            <div class="p-3">
                                <label
                                    for="observacao_retorno"
                                    class="form-label fw-bold"
                                >
                                    Descrição da ocorrência
                                </label>

                                <textarea
                                    name="<?php echo e($registrandoRetorno
                                            ? 'observacao_retorno'
                                            : 'observacao'); ?>"
                                    id="observacao_retorno"
                                    class="form-control"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Descreva atrasos, problemas no trajeto, dificuldade de acesso, ocorrências no cliente ou outras informações relevantes..."
                                ><?php echo e(old(
                                    $registrandoRetorno
                                        ? 'observacao_retorno'
                                        : 'observacao',
                                    $conferindoRetorno
                                        ? ($romaneio->observacao ?? '')
                                        : ''
                                )); ?></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-xl-2">
                    <div class="retorno-card retorno-resumo">
                        <div class="retorno-card-header">
                            <span>
                                <i class="bi bi-bar-chart me-1"></i>
                                Resumo
                            </span>
                        </div>

                        <div class="p-3">
                            <div class="resumo-linha">
                                <span class="resumo-label">
                                    Saída
                                </span>

                                <span
                                    class="resumo-valor"
                                    id="resumo-saida"
                                >
                                    0,00
                                </span>
                            </div>

                            <div class="resumo-linha">
                                <span class="resumo-label">
                                    Entregue
                                </span>

                                <span
                                    class="resumo-valor text-success"
                                    id="resumo-entregue"
                                >
                                    0,00
                                </span>
                            </div>

                            <div class="resumo-linha">
                                <span class="resumo-label">
                                    Ocorrências
                                </span>

                                <span
                                    class="resumo-valor text-danger"
                                    id="resumo-ocorrencias"
                                >
                                    0,00
                                </span>
                            </div>

                            <div class="resumo-linha">
                                <span class="resumo-label">
                                    Diferença
                                </span>

                                <span
                                    class="resumo-valor"
                                    id="resumo-diferenca"
                                >
                                    0,00
                                </span>
                            </div>

                            <div
                                class="alert alert-warning small mt-3 mb-0"
                                id="alerta-validacao"
                            >
                                Existem produtos com resultado incompleto.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($registrandoRetorno): ?>
                <div class="retorno-card mt-3">
                    <div class="retorno-actions">
                        <span class="small text-muted">
                            Registre as ocorrências encontradas no retorno.
                        </span>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="btn-cancelar-ocorrencias"
                            >
                                <i class="bi bi-arrow-left me-1"></i>
                                Voltar
                            </button>

                            <button
                                type="submit"
                                class="btn btn-danger"
                                id="btn-registrar-ocorrencias"
                            >
                                <i class="bi bi-journal-check me-1"></i>
                                Registrar retorno com ocorrência
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($aguardandoConferencia): ?>
        <div class="retorno-card mt-3">
            <div class="retorno-actions">
                <span class="small text-muted">
                    Escolha o conferente para prosseguir.
                </span>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(route('entregas.index')); ?>"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar para entregas
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-play-circle me-1"></i>
                        Iniciar conferência
                    </button>
                </div>
            </div>
        </div>
    <?php elseif($conferindoRetorno): ?>
        <div class="retorno-card mt-3">
            <div class="retorno-actions">
                <span class="small text-muted">
                    Confira fisicamente cada quantidade.
                </span>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(route('entregas.index')); ?>"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar para entregas
                    </a>

                    <button
                        type="submit"
                        class="btn btn-info"
                        id="btn-validar-resultados"
                        disabled
                    >
                        <i class="bi bi-check-circle me-1"></i>
                        Finalizar conferência
                    </button>
                </div>
            </div>
        </div>
    <?php elseif($conferenciaFinalizada): ?>
        <div class="retorno-card mt-3">
            <div class="retorno-actions">
                <span class="small text-muted">
                    Esta etapa já foi concluída.
                </span>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(route('entregas.index')); ?>"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar para entregas
                    </a>

                    <a
                        href="<?php echo e(route(
                            'entregas.retorno.imprimir',
                            $entrega->id
                        )); ?>"
                        class="btn btn-dark"
                        target="_blank"
                    >
                        <i class="bi bi-printer me-1"></i>
                        Imprimir retorno
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($rotaFormulario): ?>
        </form>
    <?php endif; ?>
</div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-retorno');
        const tipoRetorno = document.getElementById('tipo_retorno');
        const painelDecisao = document.getElementById('painel-decisao');
        const secaoOcorrencias = document.getElementById('secao-ocorrencias');

        const botaoRetornoNormal = document.getElementById(
            'btn-retorno-normal'
        );

        const botaoAbrirOcorrencias = document.getElementById(
            'btn-abrir-ocorrencias'
        );

        const botaoCancelarOcorrencias = document.getElementById(
            'btn-cancelar-ocorrencias'
        );

        const botaoRegistrarOcorrencias = document.getElementById(
            'btn-registrar-ocorrencias'
        );

        const botaoValidarResultados = document.getElementById(
            'btn-validar-resultados'
        );

        const linhas = Array.from(
            document.querySelectorAll('.linha-retorno')
        );

        const formatarQuantidade = function (valor) {
            return Number(valor || 0).toLocaleString(
                'pt-BR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
        };

        const obterValor = function (input) {
            const valor = Number.parseFloat(
                input?.value ?? '0'
            );

            return Number.isFinite(valor)
                ? valor
                : 0;
        };

        const atualizarValidacao = function () {
            let formularioValido =
                linhas.length > 0;

            let totalSaida = 0;
            let totalEntregue = 0;
            let totalOcorrencias = 0;

            linhas.forEach(function (linha) {
                const quantidadeSaida = Number.parseFloat(
                    linha.dataset.quantidadeSaida
                    || '0'
                );

                const inputs = Array.from(
                    linha.querySelectorAll(
                        '.resultado-input'
                    )
                );

                const valores = inputs.map(
                    obterValor
                );

                const quantidadeEntregue =
                    valores[0] || 0;

                const ocorrencias =
                    (valores[1] || 0)
                    + (valores[2] || 0)
                    + (valores[3] || 0)
                    + (valores[4] || 0);

                const totalResultado =
                    quantidadeEntregue
                    + ocorrencias;

                const linhaValida =
                    Math.abs(
                        totalResultado
                        - quantidadeSaida
                    ) < 0.005;

                linha.classList.toggle(
                    'valida',
                    linhaValida
                );

                linha.classList.toggle(
                    'invalida',
                    ! linhaValida
                );

                const elementoTotal =
                    linha.querySelector(
                        '.resultado-total'
                    );

                if (elementoTotal) {
                    elementoTotal.textContent =
                        formatarQuantidade(
                            totalResultado
                        )
                        + ' / '
                        + formatarQuantidade(
                            quantidadeSaida
                        );

                    elementoTotal.classList.toggle(
                        'valido',
                        linhaValida
                    );

                    elementoTotal.classList.toggle(
                        'invalido',
                        ! linhaValida
                    );
                }

                formularioValido =
                    formularioValido
                    && linhaValida;

                totalSaida += quantidadeSaida;
                totalEntregue += quantidadeEntregue;
                totalOcorrencias += ocorrencias;
            });

            const diferencaGeral =
                totalSaida
                - totalEntregue
                - totalOcorrencias;

            const resumoSaida =
                document.getElementById(
                    'resumo-saida'
                );

            const resumoEntregue =
                document.getElementById(
                    'resumo-entregue'
                );

            const resumoOcorrencias =
                document.getElementById(
                    'resumo-ocorrencias'
                );

            const resumoDiferenca =
                document.getElementById(
                    'resumo-diferenca'
                );

            if (resumoSaida) {
                resumoSaida.textContent =
                    formatarQuantidade(
                        totalSaida
                    );
            }

            if (resumoEntregue) {
                resumoEntregue.textContent =
                    formatarQuantidade(
                        totalEntregue
                    );
            }

            if (resumoOcorrencias) {
                resumoOcorrencias.textContent =
                    formatarQuantidade(
                        totalOcorrencias
                    );
            }

            if (resumoDiferenca) {
                resumoDiferenca.textContent =
                    formatarQuantidade(
                        diferencaGeral
                    );

                resumoDiferenca.classList.toggle(
                    'text-success',
                    formularioValido
                );

                resumoDiferenca.classList.toggle(
                    'text-danger',
                    ! formularioValido
                );
            }

            const alerta =
                document.getElementById(
                    'alerta-validacao'
                );

            if (alerta) {
                alerta.classList.toggle(
                    'alert-success',
                    formularioValido
                );

                alerta.classList.toggle(
                    'alert-warning',
                    ! formularioValido
                );

                alerta.textContent =
                    formularioValido
                        ? 'Todos os produtos possuem resultado completo.'
                        : 'Existem produtos com resultado incompleto.';
            }

            if (botaoValidarResultados) {
                botaoValidarResultados.disabled =
                    ! formularioValido;
            }

            if (botaoRegistrarOcorrencias) {
                botaoRegistrarOcorrencias.disabled =
                    ! formularioValido;
            }

            return formularioValido;
        };

        botaoRetornoNormal?.addEventListener(
            'click',
            function () {
                const confirmado = window.confirm(
                    'Confirma que todos os materiais foram entregues e que não houve ocorrências no trajeto ou no cliente?'
                );

                if (! confirmado) {
                    return;
                }

                tipoRetorno.value = 'normal';
                form.submit();
            }
        );

        botaoAbrirOcorrencias?.addEventListener(
            'click',
            function () {
                tipoRetorno.value = 'ocorrencia';

                painelDecisao.style.display =
                    'none';

                secaoOcorrencias.classList.add(
                    'aberta'
                );

                atualizarValidacao();

                window.scrollTo({
                    top:
                        secaoOcorrencias
                            .getBoundingClientRect()
                            .top
                        + window.scrollY
                        - 20,

                    behavior: 'smooth'
                });
            }
        );

        botaoCancelarOcorrencias?.addEventListener(
            'click',
            function () {
                tipoRetorno.value = 'normal';

                secaoOcorrencias.classList.remove(
                    'aberta'
                );

                painelDecisao.style.display =
                    '';

                window.scrollTo({
                    top:
                        painelDecisao
                            .getBoundingClientRect()
                            .top
                        + window.scrollY
                        - 20,

                    behavior: 'smooth'
                });
            }
        );

        linhas.forEach(function (linha) {
            linha
                .querySelectorAll(
                    '.resultado-input'
                )
                .forEach(function (input) {
                    input.addEventListener(
                        'input',
                        atualizarValidacao
                    );
                });
        });

        form?.addEventListener(
            'submit',
            function (event) {
                const exigeValidacaoDetalhada =
                    ! tipoRetorno
                    || tipoRetorno.value
                        === 'ocorrencia';

                if (
                    exigeValidacaoDetalhada
                    && ! atualizarValidacao()
                ) {
                    event.preventDefault();

                    window.alert(
                        'Revise os produtos. A soma dos resultados deve ser igual à quantidade conferida na saída.'
                    );
                }
            }
        );

        atualizarValidacao();

        <?php if($abrirOcorrencias): ?>
            if (
                painelDecisao
                && secaoOcorrencias
            ) {
                painelDecisao.style.display =
                    'none';

                secaoOcorrencias.classList.add(
                    'aberta'
                );
            }
        <?php endif; ?>
    });
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const registrandoRetorno =
            <?php echo json_encode($registrandoRetorno, 15, 512) ?>;

        const conferindoRetorno =
            <?php echo json_encode($conferindoRetorno, 15, 512) ?>;

        const form =
            document.getElementById('form-retorno');

        const tipoRetorno =
            document.getElementById('tipo_retorno');

        const painelDecisao =
            document.getElementById('painel-decisao');

        const secaoOcorrencias =
            document.getElementById('secao-ocorrencias');

        const botaoRetornoNormal =
            document.getElementById(
                'btn-retorno-normal'
            );

        const botaoAbrirOcorrencias =
            document.getElementById(
                'btn-abrir-ocorrencias'
            );

        const botaoCancelarOcorrencias =
            document.getElementById(
                'btn-cancelar-ocorrencias'
            );

        const botaoRegistrarOcorrencias =
            document.getElementById(
                'btn-registrar-ocorrencias'
            );

        const botaoValidarResultados =
            document.getElementById(
                'btn-validar-resultados'
            );

        const linhas = Array.from(
            document.querySelectorAll(
                '.linha-retorno'
            )
        );

        const formatarQuantidade = function (valor) {
            return Number(valor || 0).toLocaleString(
                'pt-BR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
        };

        const obterValor = function (input) {
            const valor = Number.parseFloat(
                input?.value ?? '0'
            );

            return Number.isFinite(valor)
                ? valor
                : 0;
        };

        const atualizarValidacao = function () {
            let formularioValido =
                linhas.length > 0;

            let totalSaida = 0;
            let totalEntregue = 0;
            let totalOcorrencias = 0;

            linhas.forEach(function (linha) {
                const quantidadeSaida =
                    Number.parseFloat(
                        linha.dataset.quantidadeSaida
                        || '0'
                    );

                const inputs = Array.from(
                    linha.querySelectorAll(
                        '.resultado-input'
                    )
                );

                const valores =
                    inputs.map(obterValor);

                const quantidadeEntregue =
                    valores[0] || 0;

                const ocorrencias =
                    (valores[1] || 0)
                    + (valores[2] || 0)
                    + (valores[3] || 0)
                    + (valores[4] || 0);

                const totalResultado =
                    quantidadeEntregue
                    + ocorrencias;

                const linhaValida =
                    Math.abs(
                        totalResultado
                        - quantidadeSaida
                    ) < 0.005;

                linha.classList.toggle(
                    'valida',
                    linhaValida
                );

                linha.classList.toggle(
                    'invalida',
                    ! linhaValida
                );

                const elementoTotal =
                    linha.querySelector(
                        '.resultado-total'
                    );

                if (elementoTotal) {
                    elementoTotal.textContent =
                        formatarQuantidade(
                            totalResultado
                        )
                        + ' / '
                        + formatarQuantidade(
                            quantidadeSaida
                        );

                    elementoTotal.classList.toggle(
                        'valido',
                        linhaValida
                    );

                    elementoTotal.classList.toggle(
                        'invalido',
                        ! linhaValida
                    );
                }

                formularioValido =
                    formularioValido
                    && linhaValida;

                totalSaida +=
                    quantidadeSaida;

                totalEntregue +=
                    quantidadeEntregue;

                totalOcorrencias +=
                    ocorrencias;
            });

            const diferencaGeral =
                totalSaida
                - totalEntregue
                - totalOcorrencias;

            const resumoSaida =
                document.getElementById(
                    'resumo-saida'
                );

            const resumoEntregue =
                document.getElementById(
                    'resumo-entregue'
                );

            const resumoOcorrencias =
                document.getElementById(
                    'resumo-ocorrencias'
                );

            const resumoDiferenca =
                document.getElementById(
                    'resumo-diferenca'
                );

            if (resumoSaida) {
                resumoSaida.textContent =
                    formatarQuantidade(
                        totalSaida
                    );
            }

            if (resumoEntregue) {
                resumoEntregue.textContent =
                    formatarQuantidade(
                        totalEntregue
                    );
            }

            if (resumoOcorrencias) {
                resumoOcorrencias.textContent =
                    formatarQuantidade(
                        totalOcorrencias
                    );
            }

            if (resumoDiferenca) {
                resumoDiferenca.textContent =
                    formatarQuantidade(
                        diferencaGeral
                    );

                resumoDiferenca.classList.toggle(
                    'text-success',
                    formularioValido
                );

                resumoDiferenca.classList.toggle(
                    'text-danger',
                    ! formularioValido
                );
            }

            const alerta =
                document.getElementById(
                    'alerta-validacao'
                );

            if (alerta) {
                alerta.classList.toggle(
                    'alert-success',
                    formularioValido
                );

                alerta.classList.toggle(
                    'alert-warning',
                    ! formularioValido
                );

                alerta.textContent =
                    formularioValido
                        ? 'Todos os produtos possuem resultado completo.'
                        : 'Revise os produtos. A soma dos resultados deve ser igual à quantidade conferida na saída.';
            }

            if (botaoValidarResultados) {
                botaoValidarResultados.disabled =
                    ! formularioValido;
            }

            if (botaoRegistrarOcorrencias) {
                botaoRegistrarOcorrencias.disabled =
                    ! formularioValido;
            }

            return formularioValido;
        };

        botaoRetornoNormal?.addEventListener(
            'click',
            function () {
                if (
                    ! form
                    || ! tipoRetorno
                ) {
                    return;
                }

                tipoRetorno.value =
                    'normal';

                botaoRetornoNormal.disabled =
                    true;

                botaoRetornoNormal.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"'
                    + ' role="status" aria-hidden="true"></span>'
                    + ' Finalizando entrega...';

                if (botaoAbrirOcorrencias) {
                    botaoAbrirOcorrencias.disabled =
                        true;
                }

                form.submit();
            }
        );

        botaoAbrirOcorrencias?.addEventListener(
            'click',
            function () {
                if (
                    ! tipoRetorno
                    || ! painelDecisao
                    || ! secaoOcorrencias
                ) {
                    return;
                }

                tipoRetorno.value =
                    'ocorrencia';

                painelDecisao.style.display =
                    'none';

                secaoOcorrencias.classList.add(
                    'aberta'
                );

                atualizarValidacao();

                window.scrollTo({
                    top:
                        secaoOcorrencias
                            .getBoundingClientRect()
                            .top
                        + window.scrollY
                        - 20,

                    behavior: 'smooth'
                });
            }
        );

        botaoCancelarOcorrencias?.addEventListener(
            'click',
            function () {
                if (
                    ! tipoRetorno
                    || ! painelDecisao
                    || ! secaoOcorrencias
                ) {
                    return;
                }

                tipoRetorno.value =
                    'normal';

                secaoOcorrencias.classList.remove(
                    'aberta'
                );

                painelDecisao.style.display =
                    '';

                window.scrollTo({
                    top:
                        painelDecisao
                            .getBoundingClientRect()
                            .top
                        + window.scrollY
                        - 20,

                    behavior: 'smooth'
                });
            }
        );

        linhas.forEach(function (linha) {
            linha
                .querySelectorAll(
                    '.resultado-input'
                )
                .forEach(function (input) {
                    input.addEventListener(
                        'input',
                        atualizarValidacao
                    );
                });
        });

        form?.addEventListener(
            'submit',
            function (event) {
                const registrandoOcorrencia =
                    registrandoRetorno
                    && tipoRetorno
                    && tipoRetorno.value
                        === 'ocorrencia';

                const exigeValidacaoDetalhada =
                    registrandoOcorrencia
                    || conferindoRetorno;

                if (
                    exigeValidacaoDetalhada
                    && ! atualizarValidacao()
                ) {
                    event.preventDefault();

                    const alerta =
                        document.getElementById(
                            'alerta-validacao'
                        );

                    if (alerta) {
                        alerta.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            }
        );

        if (linhas.length > 0) {
            atualizarValidacao();
        }

        <?php if($abrirOcorrencias): ?>
            if (
                painelDecisao
                && secaoOcorrencias
            ) {
                painelDecisao.style.display =
                    'none';

                secaoOcorrencias.classList.add(
                    'aberta'
                );
            }
        <?php endif; ?>
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas\retorno.blade.php ENDPATH**/ ?>