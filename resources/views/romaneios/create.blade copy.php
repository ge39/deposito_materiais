@extends('layouts.app')

@section('content')

@php
    $entregas = collect($entregasDisponiveis ?? [])->filter()->values();
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
                    ?? 'montagem'
                )
            )
        )
    );

    $etapaAtual = match ($statusOriginal) {
        'montagem' =>
            'montagem',

        'aguardando_separacao',
        'em_separacao' =>
            'separacao',

        'aguardando_conferencia_separacao',
        'em_conferencia_separacao',
        'separacao_conferida' =>
            'conferencia_separacao',

        'aguardando_carregamento',
        'carregando' =>
            'carregamento',

        'aguardando_conferencia_saida',
        'em_conferencia_saida' =>
            'conferencia_saida',

        'aguardando_liberacao',
        'liberado' =>
            'liberacao',

        'em_rota',
        'retornando',
        'aguardando_conferencia_retorno',
        'em_conferencia_retorno',
        'aguardando_prestacao_contas',
        'em_prestacao_contas',
        'aguardando_fechamento',
        'fechado' =>
            'em_rota',

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

        'conferencia_separacao' => [
            'label' => 'Conf. Separação',
            'icone' => 'bi-clipboard2-check',
            'ordem' => 3,
        ],

        'carregamento' => [
            'label' => 'Carregamento',
            'icone' => 'bi-truck-front',
            'ordem' => 4,
        ],

        'conferencia_saida' => [
            'label' => 'Conf. Saída',
            'icone' => 'bi-clipboard-data',
            'ordem' => 5,
        ],

        'liberacao' => [
            'label' => 'Liberação',
            'icone' => 'bi-shield-check',
            'ordem' => 6,
        ],

        'em_rota' => [
            'label' => 'Em Rota',
            'icone' => 'bi-sign-turn-right',
            'ordem' => 7,
        ],
    ];

    $ordemAtual = $etapas[$etapaAtual]['ordem'];

    $progressoWorkflow = count($etapas) > 1
        ? (($ordemAtual - 1) / (count($etapas) - 1)) * 100
        : 0;

    $codigoRomaneio =
        $romaneioAtivo?->codigo_romaneio
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
        'montagem' => 'bg-secondary',
        'separacao' => 'bg-warning text-dark',
        'conferencia_separacao' => 'bg-info text-dark',
        'carregamento' => 'bg-primary',
        'conferencia_saida' => 'bg-info text-dark',
        'liberacao' => 'bg-success',
        'em_rota' => 'bg-dark',
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

    $resolverItemRomaneio = function ($item) use ($romaneioAtivo) {
        if (! $romaneioAtivo) {
            return null;
        }

        return collect($romaneioAtivo->itens ?? [])
            ->first(
                fn ($itemRomaneio) =>
                    (int) $itemRomaneio->entrega_item_id
                    === (int) $item->id
            );
    };

    $funcionariosOperacionais = collect(
        $funcionariosOperacionais ?? []
    );

    $podeSalvarAndamento = in_array(
        $statusOriginal,
        [
            'em_separacao',
            'em_conferencia_separacao',
            'carregando',
            'em_conferencia_saida',
        ],
        true
    );

    $podeVoltarEtapa = in_array(
        $statusOriginal,
        [
            'aguardando_conferencia_separacao',
            'em_conferencia_separacao',
            'aguardando_carregamento',
            'carregando',
            'aguardando_conferencia_saida',
            'em_conferencia_saida',
            'aguardando_liberacao',
            'liberado',
        ],
        true
    );

    $operacaoInternaFinalizada = in_array(
        $statusOriginal,
        [
            'em_rota',
            'retornando',
            'aguardando_conferencia_retorno',
            'em_conferencia_retorno',
            'aguardando_prestacao_contas',
            'em_prestacao_contas',
            'aguardando_fechamento',
            'fechado',
            'cancelado',
        ],
        true
    );

    $podeEditarEquipe = $criandoRomaneio || ! in_array(
        $statusOriginal,
        [
            'liberado',
            'em_rota',
            'retornando',
            'aguardando_conferencia_retorno',
            'em_conferencia_retorno',
            'aguardando_prestacao_contas',
            'em_prestacao_contas',
            'aguardando_fechamento',
            'fechado',
            'cancelado',
        ],
        true
    );

    $campoQuantidadeAtiva = match ($statusOriginal) {
        'em_separacao' =>
            'quantidade_separada',

        'em_conferencia_separacao' =>
            'quantidade_conferida_separacao',

        'carregando' =>
            'quantidade_carregada',

        'em_conferencia_saida' =>
            'quantidade_conferida_saida',

        default =>
            null,
    };

    $descricaoEtapa = match ($statusOriginal) {
        'montagem' =>
            'Monte o romaneio, defina motorista, veículo e quantidades.',

        'aguardando_separacao' =>
            'O romaneio está pronto para iniciar a separação física.',

        'em_separacao' =>
            'Registre as quantidades separadas de cada item.',

        'aguardando_conferencia_separacao' =>
            'A separação terminou e aguarda conferência antes do carregamento.',

        'em_conferencia_separacao' =>
            'Confira fisicamente cada quantidade separada.',

        'separacao_conferida',
        'aguardando_carregamento' =>
            'A separação foi conferida e o carregamento pode começar.',

        'carregando' =>
            'Registre as quantidades efetivamente colocadas no veículo.',

        'aguardando_conferencia_saida' =>
            'O carregamento terminou e aguarda a conferência final de saída.',

        'em_conferencia_saida' =>
            'Confira a carga no veículo antes da liberação.',

        'aguardando_liberacao' =>
            'A carga está conferida. Imprima o romaneio e libere o veículo.',

        'liberado' =>
            'O veículo está liberado e aguarda o registro da saída física.',

        'em_rota' =>
            'O veículo está em rota. O retorno será tratado na tela de Entregas.',

        default =>
            'Acompanhe a operação do romaneio.',
    };
@endphp

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
        overflow-x: auto;
        padding: 1rem;
    }

    .workflow {
        display: grid;
        grid-template-columns: repeat(7, minmax(110px, 1fr));
        min-width: 820px;
        position: relative;
    }

    .workflow::before {
        background: #d9dee3;
        content: "";
        height: 4px;
        left: 7%;
        position: absolute;
        right: 7%;
        top: 19px;
        z-index: 0;
    }

    .workflow-progress {
        background: #198754;
        height: 4px;
        left: 7%;
        max-width: 86%;
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
        font-size: .72rem;
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

    .operation-banner.conferencia_separacao,
    .operation-banner.conferencia_saida {
        background: var(--erp-info-soft);
        border-color: #9eeaf9;
    }

    .operation-banner.carregamento {
        background: var(--erp-primary-soft);
        border-color: #9ec5fe;
    }

    .operation-banner.liberacao {
        background: var(--erp-success-soft);
        border-color: #75b798;
    }

    .operation-banner.em_rota {
        background: #e9ecef;
        border-color: #adb5bd;
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
        border: 1px solid #dfe3e7;
        border-radius: 7px;
        margin-bottom: .7rem;
        overflow: hidden;
    }

    .delivery-item:last-child {
        margin-bottom: 0;
    }

    .delivery-button {
        background: #f8f9fa;
        font-size: .82rem;
        padding: .75rem;
    }

    .delivery-code {
        font-size: .78rem;
        font-weight: 800;
    }

    .delivery-client {
        color: #6c757d;
        font-size: .72rem;
    }

    .items-table th {
        background: var(--erp-dark);
        color: #fff;
        font-size: .67rem;
        font-weight: 800;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .items-table td {
        font-size: .77rem;
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
        min-width: 88px;
        text-align: right;
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

    .footer-actions {
        align-items: center;
        display: flex;
        gap: .8rem;
        justify-content: space-between;
        padding: .8rem;
    }

    @media (max-width: 991.98px) {
        .summary-card {
            position: static;
        }

        .footer-actions {
            align-items: stretch;
            flex-direction: column;
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
                Montagem, separação, conferência, carregamento, liberação e saída.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($codigoRomaneio)
                <span class="badge bg-dark fs-6">
                    {{ $codigoRomaneio }}
                </span>
            @endif

            <span class="badge {{ $statusClasses[$etapaAtual] }}">
                {{ $romaneioAtivo?->status
                    ?? $etapas[$etapaAtual]['label'] }}
            </span>

            <a href="{{ route('romaneios.index') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível concluir a operação.</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($entregas->isEmpty())
        <div class="alert alert-warning">
            Nenhuma entrega disponível para operação.
        </div>
    @else
        <form id="formRomaneio"
              method="POST"
              action="{{ $formAction }}">

            @csrf

            @if($formMethod === 'PUT')
                @method('PUT')
            @endif

            <input type="hidden"
                   name="etapa_atual"
                   value="{{ $etapaAtual }}">

            @if($entregaPrincipal)
                <input type="hidden"
                       name="entrega_id"
                       value="{{ $entregaPrincipal->id }}">
            @endif

            <div class="section-card workflow-card mb-3">
                <div class="workflow">
                    <div class="workflow-progress"
                         style="width: {{ $progressoWorkflow }}%;">
                    </div>

                    @foreach($etapas as $chave => $etapa)
                        @php
                            $classeEtapa = '';

                            if ($etapa['ordem'] < $ordemAtual) {
                                $classeEtapa = 'completed';
                            } elseif ($chave === $etapaAtual) {
                                $classeEtapa = 'active';
                            }
                        @endphp

                        <div class="workflow-step {{ $classeEtapa }}">
                            <div class="workflow-circle">
                                <i class="bi {{ $etapa['icone'] }}"></i>
                            </div>

                            <span class="workflow-label">
                                {{ $etapa['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="operation-banner {{ $etapaAtual }} mb-3">
                <div>
                    <div class="operation-title">
                        {{ $descricaoEtapa }}
                    </div>

                    <div class="operation-description">
                       @if($criandoRomaneio)
    <button type="submit"
            class="btn btn-primary btn-sm"
            id="btnPrincipal">
        <i class="bi bi-check-circle me-1"></i>
        Criar Romaneio
    </button>

        @elseif($statusOriginal === 'montagem')
            <button type="submit"
                    name="acao"
                    value="concluir_montagem"
                    class="btn btn-primary btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-check-circle me-1"></i>
                Concluir Montagem
            </button>

        @elseif($statusOriginal === 'aguardando_separacao')
            <button type="submit"
                    name="acao"
                    value="iniciar_separacao"
                    class="btn btn-warning btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-play-circle me-1"></i>
                Iniciar Separação
            </button>

        @elseif($statusOriginal === 'em_separacao')
            <button type="submit"
                    name="acao"
                    value="finalizar_separacao"
                    class="btn btn-warning btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-box-seam me-1"></i>
                Finalizar Separação
            </button>

        @elseif($statusOriginal === 'aguardando_conferencia_separacao')
            <button type="submit"
                    name="acao"
                    value="iniciar_conferencia_separacao"
                    class="btn btn-info btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-clipboard2-check me-1"></i>
                Iniciar Conferência
            </button>

        @elseif($statusOriginal === 'em_conferencia_separacao')
            <button type="submit"
                    name="acao"
                    value="finalizar_conferencia_separacao"
                    class="btn btn-info btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-check2-all me-1"></i>
                Finalizar Conferência
            </button>

        @elseif(in_array(
            $statusOriginal,
            [
                'separacao_conferida',
                'aguardando_carregamento',
            ],
            true
        ))
            <button type="submit"
                    name="acao"
                    value="iniciar_carregamento"
                    class="btn btn-primary btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-play-circle me-1"></i>
                Iniciar Carregamento
            </button>

        @elseif($statusOriginal === 'carregando')
            <button type="submit"
                    name="acao"
                    value="finalizar_carregamento"
                    class="btn btn-primary btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-truck-front me-1"></i>
                Finalizar Carregamento
            </button>

        @elseif($statusOriginal === 'aguardando_conferencia_saida')
            <button type="submit"
                    name="acao"
                    value="iniciar_conferencia_saida"
                    class="btn btn-info btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-clipboard-data me-1"></i>
                Iniciar Conf. Saída
            </button>

        @elseif($statusOriginal === 'em_conferencia_saida')
            <button type="submit"
                    name="acao"
                    value="finalizar_conferencia_saida"
                    class="btn btn-info btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-check2-all me-1"></i>
                Finalizar Conf. Saída
            </button>

        @elseif($statusOriginal === 'aguardando_liberacao')
            <button type="submit"
                    form="formImprimirRomaneio"
                    class="btn btn-outline-dark btn-sm" formTarget="_blank">
                <i class="bi bi-printer me-1"></i>
                Imprimir
            </button>

            <button type="submit"
                    name="acao"
                    value="liberar_veiculo"
                    class="btn btn-success btn-sm"
                    id="btnPrincipal"
                    @disabled(
                        empty(
                            $romaneioAtivo?->impresso_em
                        )
                    )>
                <i class="bi bi-shield-check me-1"></i>
                Liberar Veículo
            </button>

        @elseif($statusOriginal === 'liberado')
            <button type="submit"
                    name="acao"
                    value="registrar_saida"
                    class="btn btn-dark btn-sm"
                    id="btnPrincipal">
                <i class="bi bi-truck me-1"></i>
                Registrar Saída
            </button>

        @elseif($statusOriginal === 'em_rota')
            <button type="button"
                    class="btn btn-dark btn-sm"
                    disabled>
                <i class="bi bi-sign-turn-right me-1"></i>
                Veículo em Rota
            </button>
        @endif
                    </div>

                    @if($romaneioAtivo)
                        <div class="operation-meta">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-person me-1"></i>
                                Criado por:
                                {{ $romaneioAtivo?->criador?->name
                                    ?? $romaneioAtivo?->criador?->nome
                                    ?? $romaneioAtivo?->criado_por
                                    ?? 'Não registrado' }}
                            </span>

                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-calendar-check me-1"></i>
                                Emissão:
                                {{ $formatarData(
                                    $romaneioAtivo?->data_emissao,
                                    true
                                ) }}
                            </span>

                            @if($romaneioAtivo?->data_inicio_separacao)
                                <span class="badge bg-warning text-dark">
                                    Separação:
                                    {{ $formatarData(
                                        $romaneioAtivo->data_inicio_separacao,
                                        true
                                    ) }}
                                </span>
                            @endif

                            @if($romaneioAtivo?->data_inicio_conferencia_separacao)
                                <span class="badge bg-info text-dark">
                                    Conf. separação:
                                    {{ $formatarData(
                                        $romaneioAtivo->data_inicio_conferencia_separacao,
                                        true
                                    ) }}
                                </span>
                            @endif

                            @if($romaneioAtivo?->data_inicio_carregamento)
                                <span class="badge bg-primary">
                                    Carga:
                                    {{ $formatarData(
                                        $romaneioAtivo->data_inicio_carregamento,
                                        true
                                    ) }}
                                </span>
                            @endif

                            @if($romaneioAtivo?->data_inicio_conferencia_saida)
                                <span class="badge bg-info text-dark">
                                    Conf. saída:
                                    {{ $formatarData(
                                        $romaneioAtivo->data_inicio_conferencia_saida,
                                        true
                                    ) }}
                                </span>
                            @endif

                            @if($romaneioAtivo?->data_saida)
                                <span class="badge bg-dark">
                                    Saída:
                                    {{ $formatarData(
                                        $romaneioAtivo->data_saida,
                                        true
                                    ) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="section-card mb-3">
                <div class="section-header">
                    <span>
                        <i class="bi bi-person-badge me-1"></i>
                        Equipe e orientações
                    </span>
                </div>

                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label for="motorista_id"
                                   class="form-label">
                                Motorista
                            </label>

                            <select id="motorista_id"
                                    name="motorista_id"
                                    class="form-select form-select-sm"
                                    {{ $podeEditarEquipe ? '' : 'disabled' }}>
                                <option value="">Selecione...</option>

                                @foreach($motoristas as $motorista)
                                    <option value="{{ $motorista->id }}"
                                        @selected(
                                            (int) $motoristaSelecionado
                                            === (int) $motorista->id
                                        )>
                                        {{ $motorista->nome }}
                                    </option>
                                @endforeach
                            </select>

                            @if(! $podeEditarEquipe)
                                <input type="hidden"
                                       name="motorista_id"
                                       value="{{ $motoristaSelecionado }}">
                            @endif
                        </div>

                        <div class="col-lg-3">
                            <label for="veiculo_id"
                                   class="form-label">
                                Veículo
                            </label>

                            <select id="veiculo_id"
                                    name="veiculo_id"
                                    class="form-select form-select-sm"
                                    {{ $podeEditarEquipe ? '' : 'disabled' }}>
                                <option value="">Selecione...</option>

                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->id }}"
                                        @selected(
                                            (int) $veiculoSelecionado
                                            === (int) $veiculo->id
                                        )>
                                        {{ $veiculo->placa
                                            ?? $veiculo->descricao
                                            ?? $veiculo->observacao
                                            ?? 'Veículo #' . $veiculo->id }}
                                    </option>
                                @endforeach
                            </select>

                            @if(! $podeEditarEquipe)
                                <input type="hidden"
                                       name="veiculo_id"
                                       value="{{ $veiculoSelecionado }}">
                            @endif
                        </div>

                        @if($statusOriginal === 'em_separacao')
                            <div class="col-lg-3">
                                <label for="separado_por"
                                       class="form-label">
                                    Separador
                                </label>

                                <select id="separado_por"
                                        name="separado_por"
                                        class="form-select form-select-sm">
                                    <option value="">Selecione...</option>

                                    @foreach($funcionariosOperacionais as $funcionario)
                                        <option value="{{ $funcionario->id }}"
                                            @selected(
                                                (int) old('separado_por')
                                                === (int) $funcionario->id
                                            )>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($statusOriginal === 'aguardando_conferencia_separacao')
                            <div class="col-lg-3">
                                <label for="conferencia_separacao_por"
                                       class="form-label">
                                    Conferente da separação
                                </label>

                                <select id="conferencia_separacao_por"
                                        name="conferencia_separacao_por"
                                        class="form-select form-select-sm">
                                    <option value="">Selecione...</option>

                                    @foreach($funcionariosOperacionais as $funcionario)
                                        <option value="{{ $funcionario->id }}"
                                            @selected(
                                                (int) old('conferencia_separacao_por')
                                                === (int) $funcionario->id
                                            )>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($statusOriginal === 'aguardando_carregamento')
                            <div class="col-lg-3">
                                <label for="carregado_por"
                                       class="form-label">
                                    Responsável pelo carregamento
                                </label>

                                <select id="carregado_por"
                                        name="carregado_por"
                                        class="form-select form-select-sm">
                                    <option value="">Selecione...</option>

                                    @foreach($funcionariosOperacionais as $funcionario)
                                        <option value="{{ $funcionario->id }}"
                                            @selected(
                                                (int) old('carregado_por')
                                                === (int) $funcionario->id
                                            )>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($statusOriginal === 'aguardando_conferencia_saida')
                            <div class="col-lg-3">
                                <label for="conferencia_saida_por"
                                       class="form-label">
                                    Conferente de saída
                                </label>

                                <select id="conferencia_saida_por"
                                        name="conferencia_saida_por"
                                        class="form-select form-select-sm">
                                    <option value="">Selecione...</option>

                                    @foreach($funcionariosOperacionais as $funcionario)
                                        <option value="{{ $funcionario->id }}"
                                            @selected(
                                                (int) old('conferencia_saida_por')
                                                === (int) $funcionario->id
                                            )>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-lg-3">
                            <label for="observacao"
                                   class="form-label">
                                Observação
                            </label>

                            <input type="text"
                                   id="observacao"
                                   name="observacao"
                                   class="form-control form-control-sm"
                                   maxlength="1000"
                                   value="{{ $observacaoRomaneio }}"
                                   {{ $operacaoInternaFinalizada ? 'readonly' : '' }}
                                   placeholder="Orientação de carga, acesso ou prioridade...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-9">
                    <div class="delivery-list section-card">
                        <div class="accordion"
                             id="accordionEntregas">

                            @foreach($entregas as $indiceEntrega => $entrega)
                                @php
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
                                        ?? 'Endereço não informado';

                                    $dataPrevista = $formatarData(
                                        $entrega?->data_prevista_entrega
                                        ?? $entrega?->data_prevista
                                    );

                                    $itensEntrega = collect(
                                        $entrega?->itens ?? []
                                    );

                                    $codigoEntrega =
                                        $entrega?->codigo_entrega
                                        ?? 'ENT-' . $entrega->id;
                                @endphp

                                <div class="delivery-item">
                                    <h2 class="accordion-header"
                                        id="headingEntrega{{ $entrega->id }}">

                                        <button class="accordion-button delivery-button {{ $indiceEntrega > 0 ? 'collapsed' : '' }}"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseEntrega{{ $entrega->id }}">

                                            <div class="d-flex justify-content-between align-items-center gap-3 w-100 me-2">
                                                <div>
                                                    <div class="delivery-code">
                                                        {{ $codigoEntrega }}
                                                    </div>

                                                    <div class="delivery-client">
                                                        {{ $clienteNome }}
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        {{ $dataPrevista }}
                                                    </span>

                                                    <span class="badge bg-primary">
                                                        {{ $itensEntrega->count() }}
                                                        item(ns)
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>

                                    <div id="collapseEntrega{{ $entrega->id }}"
                                         class="accordion-collapse collapse {{ $indiceEntrega === 0 ? 'show' : '' }}"
                                         data-bs-parent="#accordionEntregas">

                                        <div class="accordion-body p-0">
                                            <div class="p-3 border-bottom">
                                                <div class="row g-3">
                                                    <div class="col-lg-3">
                                                        <span class="info-label">
                                                            Cliente
                                                        </span>

                                                        <div class="info-value">
                                                            {{ $clienteNome }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <span class="info-label">
                                                            Telefone
                                                        </span>

                                                        <div class="info-value">
                                                            {{ $telefoneCliente }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-5">
                                                        <span class="info-label">
                                                            Endereço
                                                        </span>

                                                        <div class="info-value">
                                                            {{ $enderecoEntrega }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <span class="info-label">
                                                            Previsão
                                                        </span>

                                                        <div class="info-value">
                                                            {{ $dataPrevista }}
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

                                                            <th>Local</th>
                                                            <th>Prevista</th>
                                                            <th>Romaneio</th>
                                                            <th>Separada</th>
                                                            <th>Conf. Separação</th>
                                                            <th>Carregada</th>
                                                            <th>Conf. Saída</th>
                                                            <th>Situação</th>
                                                            <th>Unid.</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @forelse($itensEntrega as $item)
                                                            @php
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

                                                                $quantidadeConferidaSeparacao = (float) old(
                                                                    "itens.{$item->id}.quantidade_conferida_separacao",
                                                                    $itemRomaneio?->quantidade_conferida_separacao
                                                                        ?? 0
                                                                );

                                                                $quantidadeCarregada = (float) old(
                                                                    "itens.{$item->id}.quantidade_carregada",
                                                                    $itemRomaneio?->quantidade_carregada
                                                                        ?? 0
                                                                );

                                                                $quantidadeConferidaSaida = (float) old(
                                                                    "itens.{$item->id}.quantidade_conferida_saida",
                                                                    $itemRomaneio?->quantidade_conferida_saida
                                                                        ?? 0
                                                                );

                                                                $unidade =
                                                                    $produto?->unidade_medida?->sigla
                                                                    ?? $produto?->unidade
                                                                    ?? 'UN';

                                                                $localizacao =
                                                                    $produto?->localizacao_estoque
                                                                    ?? $produto?->localizacao
                                                                    ?? '—';
                                                            @endphp

                                                            <tr class="item-row"
                                                                data-prevista="{{ number_format($quantidadeRomaneio, 2, '.', '') }}">

                                                                <td>
                                                                    <input type="hidden"
                                                                           name="itens[{{ $item->id }}][entrega_item_id]"
                                                                           value="{{ $item->id }}">

                                                                    @if($itemRomaneio)
                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][romaneio_item_id]"
                                                                               value="{{ $itemRomaneio->id }}">
                                                                    @endif

                                                                    <div class="product-name">
                                                                        {{ $produto?->nome
                                                                            ?? $produto?->descricao
                                                                            ?? 'Produto não identificado' }}
                                                                    </div>

                                                                    <div class="product-code">
                                                                        Código:
                                                                        {{ $produto?->codigo
                                                                            ?? $produto?->id
                                                                            ?? '-' }}
                                                                    </div>
                                                                </td>

                                                                <td class="text-center">
                                                                    {{ $localizacao }}
                                                                </td>

                                                                <td class="text-end">
                                                                    {{ number_format(
                                                                        $quantidadePrevista,
                                                                        2,
                                                                        ',',
                                                                        '.'
                                                                    ) }}
                                                                </td>

                                                                <td class="text-center">
                                                                    @if($criandoRomaneio)
                                                                        <input type="number"
                                                                               name="itens[{{ $item->id }}][quantidade]"
                                                                               value="{{ number_format($quantidadeRomaneio, 2, '.', '') }}"
                                                                               min="1"
                                                                               max="{{ number_format($quantidadePrevista, 2, '.', '') }}"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-active-quantity>
                                                                    @else
                                                                        <strong>
                                                                            {{ number_format(
                                                                                $quantidadeRomaneio,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            ) }}
                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][quantidade]"
                                                                               value="{{ number_format($quantidadeRomaneio, 2, '.', '') }}">
                                                                    @endif
                                                                </td>

                                                                <td class="text-center">
                                                                    @if($campoQuantidadeAtiva === 'quantidade_separada')
                                                                        <input type="number"
                                                                               name="itens[{{ $item->id }}][quantidade_separada]"
                                                                               value="{{ number_format($quantidadeSeparada, 2, '.', '') }}"
                                                                               min="0"
                                                                               max="{{ number_format($quantidadeRomaneio, 2, '.', '') }}"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-active-quantity>
                                                                    @else
                                                                        <strong>
                                                                            {{ number_format(
                                                                                $quantidadeSeparada,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            ) }}
                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][quantidade_separada]"
                                                                               value="{{ number_format($quantidadeSeparada, 2, '.', '') }}">
                                                                    @endif
                                                                </td>

                                                                <td class="text-center">
                                                                    @if($campoQuantidadeAtiva === 'quantidade_conferida_separacao')
                                                                        <input type="number"
                                                                               name="itens[{{ $item->id }}][quantidade_conferida_separacao]"
                                                                               value="{{ number_format($quantidadeConferidaSeparacao, 2, '.', '') }}"
                                                                               min="0"
                                                                               max="{{ number_format($quantidadeSeparada, 2, '.', '') }}"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-active-quantity>
                                                                    @else
                                                                        <strong>
                                                                            {{ number_format(
                                                                                $quantidadeConferidaSeparacao,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            ) }}
                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][quantidade_conferida_separacao]"
                                                                               value="{{ number_format($quantidadeConferidaSeparacao, 2, '.', '') }}">
                                                                    @endif
                                                                </td>

                                                                <td class="text-center">
                                                                    @if($campoQuantidadeAtiva === 'quantidade_carregada')
                                                                        <input type="number"
                                                                               name="itens[{{ $item->id }}][quantidade_carregada]"
                                                                               value="{{ number_format($quantidadeCarregada, 2, '.', '') }}"
                                                                               min="0"
                                                                               max="{{ number_format($quantidadeConferidaSeparacao, 2, '.', '') }}"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-active-quantity>
                                                                    @else
                                                                        <strong>
                                                                            {{ number_format(
                                                                                $quantidadeCarregada,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            ) }}
                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][quantidade_carregada]"
                                                                               value="{{ number_format($quantidadeCarregada, 2, '.', '') }}">
                                                                    @endif
                                                                </td>

                                                                <td class="text-center">
                                                                    @if($campoQuantidadeAtiva === 'quantidade_conferida_saida')
                                                                        <input type="number"
                                                                               name="itens[{{ $item->id }}][quantidade_conferida_saida]"
                                                                               value="{{ number_format($quantidadeConferidaSaida, 2, '.', '') }}"
                                                                               min="0"
                                                                               max="{{ number_format($quantidadeCarregada, 2, '.', '') }}"
                                                                               step="1"
                                                                               class="form-control form-control-sm quantity-input"
                                                                               data-active-quantity>
                                                                    @else
                                                                        <strong>
                                                                            {{ number_format(
                                                                                $quantidadeConferidaSaida,
                                                                                2,
                                                                                ',',
                                                                                '.'
                                                                            ) }}
                                                                        </strong>

                                                                        <input type="hidden"
                                                                               name="itens[{{ $item->id }}][quantidade_conferida_saida]"
                                                                               value="{{ number_format($quantidadeConferidaSaida, 2, '.', '') }}">
                                                                    @endif
                                                                </td>

                                                                <td class="text-center">
                                                                    <span class="badge bg-light text-dark border">
                                                                        {{ $itemRomaneio?->status
                                                                            ?? 'Pendente' }}
                                                                    </span>
                                                                </td>

                                                                <td class="text-center">
                                                                    {{ $unidade }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="10"
                                                                    class="text-center text-muted py-4">
                                                                    Nenhum item encontrado.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="section-card summary-card">
                        <div class="section-header">
                            <span>
                                <i class="bi bi-graph-up me-1"></i>
                                Resumo
                            </span>
                        </div>

                        <div class="p-3">
                            <div class="summary-row">
                                <span class="summary-label">
                                    Total previsto
                                </span>

                                <span class="summary-value"
                                      id="summaryExpected">
                                    0,00
                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Total informado
                                </span>

                                <span class="summary-value"
                                      id="summaryCompleted">
                                    0,00
                                </span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">
                                    Pendente
                                </span>

                                <span class="summary-value"
                                      id="summaryPending">
                                    0,00
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
                                    <div id="summaryProgress"
                                         class="progress-bar"
                                         style="width: 0%;">
                                    </div>
                                </div>
                            </div>

                            <div id="nextStep"
                                 class="next-step mt-3">
                                <div class="fw-bold small"
                                     id="nextStepTitle">
                                    Preencha as quantidades.
                                </div>

                                <div class="small text-muted mt-1">
                                    A etapa só poderá ser finalizada quando todas as quantidades estiverem conciliadas.
                                </div>
                            </div>

                            @if($romaneioAtivo)
                                <hr>

                                <div class="small text-muted">
                                    <div class="mb-2">
                                        <i class="bi bi-person-badge me-1"></i>
                                        Motorista:

                                        <strong>
                                            {{ $romaneioAtivo?->motorista?->nome
                                                ?? 'A definir' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <i class="bi bi-truck me-1"></i>
                                        Veículo:

                                        <strong>
                                            {{ $romaneioAtivo?->veiculo?->placa
                                                ?? $romaneioAtivo?->veiculo?->descricao
                                                ?? 'A definir' }}
                                        </strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card mt-3">
                <div class="footer-actions">
                    <div class="small text-muted">
                        {{ $descricaoEtapa }}
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('entregas.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Fechar
                        </a>

                        @if(! $criandoRomaneio && $podeVoltarEtapa)
                            <button type="button"
                                class="btn btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalNavegarEtapa">

                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Alterar Etapa
                        </button>
                        @endif

                        @if(! $criandoRomaneio && $podeSalvarAndamento)
                            <button type="submit"
                                    name="acao"
                                    value="salvar_andamento"
                                    class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-floppy me-1"></i>
                                Salvar Andamento
                            </button>
                        @endif

                        @if($criandoRomaneio)
                            <button type="submit"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-check-circle me-1"></i>
                                Criar Romaneio
                            </button>

                        @elseif($statusOriginal === 'aguardando_separacao')
                            <button type="submit"
                                    name="acao"
                                    value="iniciar_separacao"
                                    class="btn btn-warning btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-play-circle me-1"></i>
                                Iniciar Separação
                            </button>

                        @elseif($statusOriginal === 'em_separacao')
                            <button type="submit"
                                    name="acao"
                                    value="finalizar_separacao"
                                    class="btn btn-warning btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-box-seam me-1"></i>
                                Finalizar Separação
                            </button>

                        @elseif($statusOriginal === 'aguardando_conferencia_separacao')
                            <button type="submit"
                                    name="acao"
                                    value="iniciar_conferencia_separacao"
                                    class="btn btn-info btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-clipboard2-check me-1"></i>
                                Iniciar Conferência
                            </button>

                        @elseif($statusOriginal === 'em_conferencia_separacao')
                            <button type="submit"
                                    name="acao"
                                    value="finalizar_conferencia_separacao"
                                    class="btn btn-info btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-check2-all me-1"></i>
                                Finalizar Conferência
                            </button>

                        @elseif(in_array(
                            $statusOriginal,
                            [
                                'separacao_conferida',
                                'aguardando_carregamento',
                            ],
                            true
                        ))
                            <button type="submit"
                                    name="acao"
                                    value="iniciar_carregamento"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-play-circle me-1"></i>
                                Iniciar Carregamento
                            </button>

                        @elseif($statusOriginal === 'carregando')
                            <button type="submit"
                                    name="acao"
                                    value="finalizar_carregamento"
                                    class="btn btn-primary btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-truck-front me-1"></i>
                                Finalizar Carregamento
                            </button>

                        @elseif($statusOriginal === 'aguardando_conferencia_saida')
                            <button type="submit"
                                    name="acao"
                                    value="iniciar_conferencia_saida"
                                    class="btn btn-info btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-clipboard-data me-1"></i>
                                Iniciar Conf. Saída
                            </button>

                        @elseif($statusOriginal === 'em_conferencia_saida')
                            <button type="submit"
                                    name="acao"
                                    value="finalizar_conferencia_saida"
                                    class="btn btn-info btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-check2-all me-1"></i>
                                Finalizar Conf. Saída
                            </button>

                        @elseif($statusOriginal === 'aguardando_liberacao')
                            <button type="submit"
                                    form="formImprimirRomaneio"
                                    class="btn btn-outline-dark btn-sm">
                                <i class="bi bi-printer me-1"></i>
                                Imprimir
                            </button>

                            <button type="submit"
                                    name="acao"
                                    value="liberar_veiculo"
                                    class="btn btn-success btn-sm"
                                    id="btnPrincipal"
                                    @disabled(
                                        empty(
                                            $romaneioAtivo?->impresso_em
                                        )
                                    )>
                                <i class="bi bi-shield-check me-1"></i>
                                Liberar Veículo
                            </button>

                        @elseif($statusOriginal === 'liberado')
                            <button type="submit"
                                    name="acao"
                                    value="registrar_saida"
                                    class="btn btn-dark btn-sm"
                                    id="btnPrincipal">
                                <i class="bi bi-truck me-1"></i>
                                Registrar Saída
                            </button>

                        @elseif($statusOriginal === 'em_rota')
                            <button type="button"
                                    class="btn btn-dark btn-sm"
                                    disabled>
                                <i class="bi bi-sign-turn-right me-1"></i>
                                Veículo em Rota
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if(
            ! $criandoRomaneio
            && $statusOriginal === 'aguardando_liberacao'
        )
            <form id="formImprimirRomaneio"
                  method="POST"
                  action="{{ route(
                      'romaneios.registrar-impressao',
                      $romaneioAtivo
                  ) }}"
                  class="d-none">
                @csrf
            </form>
        @endif

       @if(! $criandoRomaneio && $podeVoltarEtapa)
    <div class="modal fade"
         id="modalNavegarEtapa"
         tabindex="-1"
         aria-labelledby="modalNavegarEtapaLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"
                        id="modalNavegarEtapaLabel">

                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        Alterar etapa operacional
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        A alteração será registrada no histórico auditável do romaneio.
                    </div>

                    <div class="mb-3">
                        <label for="etapa_destino_modal"
                               class="form-label fw-semibold">
                            Etapa de destino
                        </label>

                        <select id="etapa_destino_modal"
                                class="form-select">

                            <option value="">
                                Selecione a etapa
                            </option>

                            @foreach($etapas as $chaveEtapa => $configuracaoEtapa)
                                @if(
                                    $configuracaoEtapa['ordem'] < $ordemAtual
                                    && $chaveEtapa !== 'em_rota'
                                )
                                    <option value="{{ $chaveEtapa }}">
                                        {{ $configuracaoEtapa['label'] }}
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        <div class="invalid-feedback">
                            Selecione a etapa de destino.
                        </div>
                    </div>

                    <div>
                        <label for="motivo_movimentacao_modal"
                               class="form-label fw-semibold">
                            Motivo da alteração
                        </label>

                        <textarea id="motivo_movimentacao_modal"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Informe por que o romaneio precisa retornar para outra etapa."></textarea>

                        <div class="invalid-feedback">
                            Informe um motivo com pelo menos 5 caracteres.
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button"
                            class="btn btn-danger"
                            id="btnConfirmarNavegacao">

                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Confirmar alteração
                    </button>
                </div>

            </div>
        </div>
    </div>
@endif
    @endif
</div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formRomaneio');

        if (!form) {
            return;
        }

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

        const btnPrincipal =
            document.getElementById('btnPrincipal');

        const parseNumber = value => {
            const parsed = Number.parseFloat(value);

            return Number.isFinite(parsed)
                ? parsed
                : 0;
        };

        const formatNumber = value => {
            return value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        const atualizarResumo = () => {
            let previsto = 0;
            let informado = 0;

            rows.forEach(row => {
                previsto += parseNumber(
                    row.dataset.prevista
                );

                const inputAtivo =
                    row.querySelector(
                        '[data-active-quantity]'
                    );

                if (inputAtivo) {
                    informado += parseNumber(
                        inputAtivo.value
                    );
                } else {
                    informado += parseNumber(
                        row.dataset.prevista
                    );
                }
            });

            const pendente = Math.max(
                previsto - informado,
                0
            );

            const percentual = previsto > 0
                ? Math.min(
                    (informado / previsto) * 100,
                    100
                )
                : 0;

            if (summaryExpected) {
                summaryExpected.textContent =
                    formatNumber(previsto);
            }

            if (summaryCompleted) {
                summaryCompleted.textContent =
                    formatNumber(informado);
            }

            if (summaryPending) {
                summaryPending.textContent =
                    formatNumber(pendente);
            }

            if (summaryPercent) {
                summaryPercent.textContent =
                    `${percentual.toFixed(0)}%`;
            }

            if (summaryProgress) {
                summaryProgress.style.width =
                    `${percentual}%`;
            }

            const concluido =
                Math.abs(previsto - informado) < 0.001;

            if (nextStep) {
                nextStep.classList.toggle(
                    'ready',
                    concluido
                );
            }

            if (nextStepTitle) {
                nextStepTitle.textContent = concluido
                    ? 'Etapa pronta para conclusão.'
                    : 'Existem quantidades pendentes.';
            }

            if (btnPrincipal) {
                const acao = btnPrincipal.value ?? '';

                const exigeQuantidadeCompleta = [
                    'finalizar_separacao',
                    'finalizar_conferencia_separacao',
                    'finalizar_carregamento',
                    'finalizar_conferencia_saida',
                ].includes(acao);

                if (exigeQuantidadeCompleta) {
                    btnPrincipal.disabled = ! concluido;
                }
            }
        };

        document
            .querySelectorAll('[data-active-quantity]')
            .forEach(input => {
                input.addEventListener(
                    'input',
                    atualizarResumo
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Navegação auditável entre etapas
        |--------------------------------------------------------------------------
        */

        const modalNavegarElemento =
            document.getElementById(
                'modalNavegarEtapa'
            );

       const etapaDestino =
            document.getElementById('etapa_destino_modal');

        const motivoMovimentacao =
            document.getElementById('motivo_movimentacao_modal');

        const btnConfirmarNavegacao =

    document.getElementById('btnConfirmarNavegacao');

        const motivoMovimentacao =
            document.getElementById(
                'motivoMovimentacao'
            );

        const btnConfirmarNavegacao =
            document.getElementById(
                'btnConfirmarNavegacao'
            );

        let etapaDestinoSelecionada = null;

        const removerCamposNavegacaoAnteriores = () => {
            form
                .querySelectorAll(
                    '[data-campo-navegacao="true"]'
                )
                .forEach(input => input.remove());
        };

        document
            .querySelectorAll(
                '.workflow-navigation-button'
            )
            .forEach(button => {
                button.addEventListener(
                    'click',
                    () => {
                        etapaDestinoSelecionada =
                            button.dataset.etapaDestino;

                        if (etapaDestinoLabel) {
                            etapaDestinoLabel.value =
                                button.dataset.etapaLabel
                                ?? '';
                        }

                        if (motivoMovimentacao) {
                            motivoMovimentacao.value = '';

                            motivoMovimentacao
                                .classList
                                .remove('is-invalid');
                        }

                        if (!modalNavegarElemento) {
                            return;
                        }

                        bootstrap.Modal
                            .getOrCreateInstance(
                                modalNavegarElemento
                            )
                            .show();
                    }
                );
            });

        btnConfirmarNavegacao
            ?.addEventListener(
                'click',
                () => {
                    const motivo =
                        motivoMovimentacao
                            ?.value
                            .trim()
                        ?? '';

                    if (
                        !etapaDestinoSelecionada
                        || motivo.length < 5
                    ) {
                        motivoMovimentacao
                            ?.classList
                            .add('is-invalid');

                        motivoMovimentacao?.focus();

                        return;
                    }

                    motivoMovimentacao
                        ?.classList
                        .remove('is-invalid');

                    removerCamposNavegacaoAnteriores();

                    const campos = {
                        acao: 'navegar_etapa',
                        etapa_destino:
                            etapaDestinoSelecionada,
                        motivo_movimentacao:
                            motivo,
                    };

                    Object.entries(campos)
                        .forEach(
                            ([nome, valor]) => {
                                const input =
                                    document.createElement(
                                        'input'
                                    );

                                input.type = 'hidden';
                                input.name = nome;
                                input.value = valor;

                                input.dataset
                                    .campoNavegacao =
                                    'true';

                                form.appendChild(input);
                            }
                        );

                    btnConfirmarNavegacao.disabled =
                        true;

                    form.submit();
                }
            );

        atualizarResumo();
    });
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form =
            document.getElementById('formRomaneio');

        if (!form) {
            return;
        }

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

        const btnPrincipal =
            document.getElementById('btnPrincipal');

        const modalNavegarElemento =
            document.getElementById('modalNavegarEtapa');

        const etapaDestino =
            document.getElementById('etapa_destino_modal');

        const motivoMovimentacao =
            document.getElementById(
                'motivo_movimentacao_modal'
            );

        const btnConfirmarNavegacao =
            document.getElementById(
                'btnConfirmarNavegacao'
            );

        const parseNumber = value => {
            const parsed =
                Number.parseFloat(value);

            return Number.isFinite(parsed)
                ? parsed
                : 0;
        };

        const formatNumber = value => {
            return value.toLocaleString(
                'pt-BR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            );
        };

        const atualizarResumo = () => {
            let previsto = 0;
            let informado = 0;

            rows.forEach(row => {
                previsto += parseNumber(
                    row.dataset.prevista
                );

                const inputAtivo =
                    row.querySelector(
                        '[data-active-quantity]'
                    );

                if (inputAtivo) {
                    informado += parseNumber(
                        inputAtivo.value
                    );
                } else {
                    informado += parseNumber(
                        row.dataset.prevista
                    );
                }
            });

            const pendente = Math.max(
                previsto - informado,
                0
            );

            const percentual = previsto > 0
                ? Math.min(
                    (informado / previsto) * 100,
                    100
                )
                : 0;

            if (summaryExpected) {
                summaryExpected.textContent =
                    formatNumber(previsto);
            }

            if (summaryCompleted) {
                summaryCompleted.textContent =
                    formatNumber(informado);
            }

            if (summaryPending) {
                summaryPending.textContent =
                    formatNumber(pendente);
            }

            if (summaryPercent) {
                summaryPercent.textContent =
                    `${percentual.toFixed(0)}%`;
            }

            if (summaryProgress) {
                summaryProgress.style.width =
                    `${percentual}%`;
            }

            const concluido =
                Math.abs(
                    previsto - informado
                ) < 0.001;

            if (nextStep) {
                nextStep.classList.toggle(
                    'ready',
                    concluido
                );
            }

            if (nextStepTitle) {
                nextStepTitle.textContent =
                    concluido
                        ? 'Etapa pronta para conclusão.'
                        : 'Existem quantidades pendentes.';
            }

            if (btnPrincipal) {
                const acao =
                    btnPrincipal.value ?? '';

                const exigeQuantidadeCompleta = [
                    'finalizar_separacao',
                    'finalizar_conferencia_separacao',
                    'finalizar_carregamento',
                    'finalizar_conferencia_saida',
                ].includes(acao);

                if (exigeQuantidadeCompleta) {
                    btnPrincipal.disabled =
                        ! concluido;
                }
            }
        };

        document
            .querySelectorAll(
                '[data-active-quantity]'
            )
            .forEach(input => {
                input.addEventListener(
                    'input',
                    atualizarResumo
                );
            });

        const removerCamposNavegacao = () => {
            form
                .querySelectorAll(
                    '[data-campo-navegacao="true"]'
                )
                .forEach(input => {
                    input.remove();
                });
        };

        const criarCampoNavegacao = (
            nome,
            valor
        ) => {
            const input =
                document.createElement('input');

            input.type = 'hidden';
            input.name = nome;
            input.value = valor;

            input.dataset.campoNavegacao =
                'true';

            form.appendChild(input);
        };

        btnConfirmarNavegacao
            ?.addEventListener(
                'click',
                () => {
                    const destino =
                        etapaDestino?.value ?? '';

                    const motivo =
                        motivoMovimentacao
                            ?.value
                            .trim()
                        ?? '';

                    let valido = true;

                    etapaDestino
                        ?.classList
                        .remove('is-invalid');

                    motivoMovimentacao
                        ?.classList
                        .remove('is-invalid');

                    if (!destino) {
                        etapaDestino
                            ?.classList
                            .add('is-invalid');

                        valido = false;
                    }

                    if (motivo.length < 5) {
                        motivoMovimentacao
                            ?.classList
                            .add('is-invalid');

                        valido = false;
                    }

                    if (!valido) {
                        return;
                    }

                    removerCamposNavegacao();

                    criarCampoNavegacao(
                        'acao',
                        'navegar_etapa'
                    );

                    criarCampoNavegacao(
                        'etapa_destino',
                        destino
                    );

                    criarCampoNavegacao(
                        'motivo_movimentacao',
                        motivo
                    );

                    btnConfirmarNavegacao.disabled =
                        true;

                    form.submit();
                }
            );

        modalNavegarElemento
            ?.addEventListener(
                'hidden.bs.modal',
                () => {
                    if (etapaDestino) {
                        etapaDestino.value = '';

                        etapaDestino
                            .classList
                            .remove('is-invalid');
                    }

                    if (motivoMovimentacao) {
                        motivoMovimentacao.value = '';

                        motivoMovimentacao
                            .classList
                            .remove('is-invalid');
                    }

                    if (btnConfirmarNavegacao) {
                        btnConfirmarNavegacao.disabled =
                            false;
                    }
                }
            );

        atualizarResumo();
    });
</script>

@endsection