@extends('layouts.app')

@section('content')
<style>
    .ocorrencias-page {
        font-size: .92rem;
    }

    .ocorrencias-page .card {
        border-color: #d7dde2;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
    }

    .ocorrencias-page .card-header {
        background: #737e87;
        color: #fff;
        font-weight: 700;
    }

    .ocorrencias-page .resumo-label {
        color: #64717c;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ocorrencias-page .resumo-valor {
        color: #17212b;
        font-weight: 700;
    }

    .ocorrencias-page .item-em-tratamento {
        background: linear-gradient(135deg, #ffffff 0%, #f4f8ff 100%);
        border: 1px solid #86b7fe;
        border-left: 6px solid #0d6efd;
        border-radius: .55rem;
        box-shadow: 0 .3rem .75rem rgba(13, 110, 253, .10);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .ocorrencias-page .item-em-tratamento-cabecalho {
        align-items: center;
        background: #e7f1ff;
        border-bottom: 1px solid #b6d4fe;
        color: #084298;
        display: flex;
        font-weight: 800;
        justify-content: space-between;
        padding: .7rem 1rem;
    }

    .ocorrencias-page .item-em-tratamento-corpo {
        padding: 1rem;
    }

    .ocorrencias-page .item-produto-nome {
        color: #17212b;
        font-size: 1.12rem;
        font-weight: 800;
    }

    .ocorrencias-page .ocorrencia-identificador {
        align-items: center;
        border: 2px solid rgba(255, 255, 255, .9);
        border-radius: .45rem;
        box-shadow: 0 .2rem .45rem rgba(0, 0, 0, .18);
        display: inline-flex;
        font-size: .92rem;
        font-weight: 800;
        gap: .35rem;
        letter-spacing: .02em;
        padding: .42rem .7rem;
        white-space: nowrap;
    }

    .ocorrencias-page .ocorrencia-identificador-principal {
        font-size: 1rem;
        padding: .5rem .8rem;
    }

    .ocorrencias-page .item-dado {
        background: rgba(255, 255, 255, .82);
        border: 1px solid #d9e7fb;
        border-radius: .4rem;
        height: 100%;
        padding: .65rem .75rem;
    }

    .ocorrencias-page .item-descricao {
        background: #fff;
        border-top: 1px solid #d9e7fb;
        color: #35404a;
        margin-top: .85rem;
        padding: .8rem .85rem 0;
    }

    .ocorrencias-page .ocorrencia-card {
        border-left-width: 5px;
    }

    .ocorrencias-page .ocorrencia-critica {
        border-left-color: #dc3545;
    }

    .ocorrencias-page .ocorrencia-atencao {
        border-left-color: #ffc107;
    }

    .ocorrencias-page .ocorrencia-informativa {
        border-left-color: #0dcaf0;
    }

    .ocorrencias-page .etapa-box {
        border: 1px solid #d9dee3;
        border-radius: .45rem;
        background: #f8f9fa;
        padding: 1rem;
        height: 100%;
    }

    .ocorrencias-page .etapa-box.bloqueada {
        background: #f1f3f5;
        border-color: #ced4da;
    }

    .ocorrencias-page .etapa-box.bloqueada .etapa-titulo {
        color: #6c757d;
    }

    .ocorrencias-page .etapa-titulo {
        border-bottom: 1px solid #d9dee3;
        color: #26323d;
        font-weight: 700;
        margin-bottom: .85rem;
        padding-bottom: .55rem;
    }

    .ocorrencias-page .etapas-coluna {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ocorrencias-page .etapas-coluna .etapa-box {
        height: auto;
    }

    .ocorrencias-page .etapas-coluna .etapa-box:last-child {
        flex: 1 1 auto;
    }

    .ocorrencias-page .evidencia-thumb {
        align-items: center;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: .35rem;
        display: flex;
        gap: .65rem;
        min-height: 62px;
        padding: .5rem;
    }

    .ocorrencias-page .evidencia-item {
        position: relative;
    }

    .ocorrencias-page .evidencia-item .btn-remover-evidencia {
        align-items: center;
        display: flex;
        height: 30px;
        justify-content: center;
        padding: 0;
        position: absolute;
        right: .4rem;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        z-index: 2;
    }

    .ocorrencias-page .evidencia-item .evidencia-thumb {
        padding-right: 2.75rem;
    }

    .ocorrencias-page .evidencia-thumb img {
        border-radius: .25rem;
        height: 48px;
        object-fit: cover;
        width: 58px;
    }

    .ocorrencias-page .historico-item {
        border-left: 3px solid #198754;
        margin-left: .35rem;
        padding: 0 0 .8rem .85rem;
    }

    .ocorrencias-page .form-label {
        font-weight: 600;
    }

    .ocorrencias-page .orientacao-operador {
        background: #f8fbff;
        border: 1px solid #9ec5fe;
        border-left: 5px solid #0d6efd;
        border-radius: .5rem;
        margin-bottom: 1rem;
        padding: 1rem;
    }

    .ocorrencias-page .orientacao-operador.concluida {
        background: #f1fff6;
        border-color: #75b798;
        border-left-color: #198754;
    }

    .ocorrencias-page .orientacao-titulo {
        color: #17212b;
        font-size: 1rem;
        font-weight: 800;
    }

    .ocorrencias-page .orientacao-texto {
        color: #52606d;
        margin-top: .2rem;
    }

    .ocorrencias-page .requisitos-lista {
        display: grid;
        gap: .45rem;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        margin-top: 1rem;
    }

    .ocorrencias-page .requisito-item {
        align-items: flex-start;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: .4rem;
        display: flex;
        gap: .55rem;
        padding: .65rem .75rem;
    }

    .ocorrencias-page .requisito-item.concluido {
        border-color: #a3cfbb;
    }

    .ocorrencias-page .requisito-item.pendente {
        background: #fff8e1;
        border-color: #ffda6a;
    }

    .ocorrencias-page .requisito-item.atual {
        background: #eef6ff;
        border-color: #6ea8fe;
        box-shadow: inset 4px 0 0 #0d6efd;
    }

    .ocorrencias-page .requisito-item.bloqueado {
        background: #f1f3f5;
        border-color: #ced4da;
        color: #6c757d;
    }

    .ocorrencias-page .requisito-item.atual .requisito-icone {
        color: #0d6efd;
    }

    .ocorrencias-page .requisito-item.bloqueado .requisito-icone {
        color: #6c757d;
    }

    .ocorrencias-page .requisito-icone {
        flex: 0 0 auto;
        font-size: 1.05rem;
        line-height: 1.2;
    }

    .ocorrencias-page .requisito-item.concluido .requisito-icone {
        color: #198754;
    }

    .ocorrencias-page .requisito-item.pendente .requisito-icone {
        color: #b58105;
    }

    .ocorrencias-page .bloqueio-resolucao {
        background: #fff8e1;
        border: 1px solid #ffda6a;
        border-radius: .4rem;
        color: #664d03;
        margin-bottom: .75rem;
        padding: .75rem;
    }

    .ocorrencias-page .btn-resolucao-bloqueado {
        cursor: not-allowed;
        opacity: .6;
    }

    .ocorrencias-page .fluxo-etapas {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin: 1.25rem 0;
    }

    .ocorrencias-page .fluxo-etapa {
        align-items: center;
        color: #6c757d;
        display: flex;
        gap: .75rem;
        min-width: 0;
        position: relative;
    }

    .ocorrencias-page .fluxo-etapa:not(:last-child)::after {
        background: #ced4da;
        content: "";
        height: 2px;
        left: calc(100% - .4rem);
        position: absolute;
        top: 1.35rem;
        width: 1.8rem;
    }

    .ocorrencias-page .fluxo-etapa-numero {
        align-items: center;
        border: 2px solid #adb5bd;
        border-radius: 50%;
        display: inline-flex;
        flex: 0 0 2.75rem;
        font-size: 1.05rem;
        font-weight: 800;
        height: 2.75rem;
        justify-content: center;
    }

    .ocorrencias-page .fluxo-etapa.concluida {
        color: #146c43;
    }

    .ocorrencias-page .fluxo-etapa.concluida .fluxo-etapa-numero {
        background: #eaf7ef;
        border-color: #198754;
        color: #198754;
    }

    .ocorrencias-page .fluxo-etapa.atual {
        color: #0a58ca;
    }

    .ocorrencias-page .fluxo-etapa.atual .fluxo-etapa-numero {
        background: #eef6ff;
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .ocorrencias-page .fluxo-etapa-titulo {
        font-weight: 800;
    }

    .ocorrencias-page .fluxo-etapa-status {
        font-size: .78rem;
    }

    .ocorrencias-page .etapa-concluida-resumo {
        align-items: center;
        background: #f3fbf6;
        border: 1px solid #75b798;
        border-radius: .5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding: .8rem 1rem;
    }

    .ocorrencias-page .etapa-box.fase-concluida {
        background: #f3fbf6;
        border-color: #75b798;
        height: auto;
    }

    .ocorrencias-page .etapa-box.fase-concluida > form {
        display: none;
    }

    .ocorrencias-page .etapa-autorizacao-decisao.fase-concluida > .ordem-operacao,
    .ocorrencias-page .etapa-autorizacao-decisao.fase-concluida > .fluxo-gatilho,
    .ocorrencias-page .etapa-autorizacao-decisao.fase-concluida > .subetapa {
        display: none;
    }

    .ocorrencias-page .etapa-autorizacao-decisao .etapa-concluida-resumo {
        grid-column: 1 / -1;
    }

    .ocorrencias-page .etapa-ativa {
        background: #fff;
        border: 2px solid #0d6efd;
        border-radius: .55rem;
        box-shadow: 0 .35rem .85rem rgba(13, 110, 253, .08);
        margin-bottom: 1rem;
        padding: 1rem;
    }

    .ocorrencias-page .ordem-operacao {
        background: #eef6ff;
        border: 1px solid #9ec5fe;
        border-radius: .45rem;
        color: #084298;
        margin-bottom: 1rem;
        padding: .75rem 1rem;
    }

    .ocorrencias-page .etapa-autorizacao-decisao {
        display: grid;
        gap: 0;
        grid-template-columns: 1fr 1.35fr;
    }

    .ocorrencias-page .etapa-autorizacao-decisao > .etapa-titulo,
    .ocorrencias-page .etapa-autorizacao-decisao > .ordem-operacao,
    .ocorrencias-page .etapa-autorizacao-decisao > .alert-secondary {
        grid-column: 1 / -1;
    }

    .ocorrencias-page .subetapa {
        min-width: 0;
        padding: .25rem 1rem;
    }

    .ocorrencias-page .subetapa + .subetapa {
        border-left: 1px solid #d7dde2;
    }

    .ocorrencias-page .subetapa.bloqueada {
        color: #8b949e;
        opacity: .68;
    }

    .ocorrencias-page .subetapa-titulo {
        align-items: center;
        display: flex;
        font-size: 1rem;
        font-weight: 800;
        gap: .55rem;
        margin-bottom: .8rem;
    }

    .ocorrencias-page .subetapa-numero {
        align-items: center;
        background: #0d6efd;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        height: 2rem;
        justify-content: center;
        width: 2rem;
    }

    .ocorrencias-page .subetapa.bloqueada .subetapa-numero {
        background: #adb5bd;
    }

    .ocorrencias-page .etapa-futura {
        display: none;
    }

    @media (max-width: 991.98px) {
        .ocorrencias-page .fluxo-etapas {
            grid-template-columns: 1fr 1fr;
        }

        .ocorrencias-page .fluxo-etapa::after {
            display: none;
        }

        .ocorrencias-page .etapa-autorizacao-decisao {
            grid-template-columns: 1fr;
        }

        .ocorrencias-page .subetapa + .subetapa {
            border-left: 0;
            border-top: 1px solid #d7dde2;
            margin-top: 1rem;
            padding-top: 1rem;
        }
    }

    @media (max-width: 575.98px) {
        .ocorrencias-page .fluxo-etapas {
            grid-template-columns: 1fr;
        }
    }

    /* Layout homologado da tratativa progressiva */
    .ocorrencias-page {
        color: #1f2937;
        font-size: .9rem;
        max-width: 100%;
    }

    .tratativa-cabecalho {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .tratativa-cabecalho-principal {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .tratativa-titulo {
        color: #1f2937;
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1.15;
        margin: 0 0 .25rem;
    }

    .tratativa-subtitulo {
        color: #718096;
    }

    .tratativa-badges,
    .tratativa-acoes {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
    }

    .badge-operacional {
        align-items: center;
        border: 1px solid;
        border-radius: .38rem;
        display: inline-flex;
        font-size: .78rem;
        font-weight: 700;
        gap: .4rem;
        padding: .5rem .7rem;
    }

    .badge-operacional-atencao {
        background: #fff8e1;
        border-color: #f1cb70;
        color: #946200;
    }

    .badge-operacional-critico {
        background: #fff0f1;
        border-color: #f2b6bc;
        color: #c1122f;
    }

    .badge-operacional-status {
        background: #f3f5f7;
        border-color: #cfd6dd;
        color: #53606c;
    }

    .ocorrencias-page .ocorrencia-card {
        background: transparent;
        border: 0;
        box-shadow: none;
        margin-bottom: 1rem !important;
    }

    .ocorrencias-page .ocorrencia-card > .card-header {
        display: none;
    }

    .ocorrencias-page .ocorrencia-card > .card-body {
        padding: 0;
    }

    .ocorrencias-page .orientacao-operador,
    .ocorrencias-page .orientacao-operador.concluida {
        background: transparent;
        border: 0;
        margin: 0 0 1rem;
        padding: 0 1.2rem;
    }

    .ocorrencias-page .orientacao-operador > .d-flex:first-child {
        display: none !important;
    }

    .ocorrencias-page .fluxo-etapas {
        gap: 2.5rem;
        margin: .75rem 0 1.25rem;
    }

    .ocorrencias-page .fluxo-etapa:not(:last-child)::after {
        left: calc(100% - .65rem);
        top: 1.4rem;
        width: 3.8rem;
    }

    .ocorrencias-page .item-em-tratamento {
        background: #fff;
        border: 1px solid #d8dee5;
        border-left-width: 1px;
        border-radius: .4rem;
        box-shadow: none;
        margin-bottom: 1rem;
    }

    .ocorrencias-page .item-em-tratamento-cabecalho {
        background: #fff;
        border-bottom: 1px solid #d8dee5;
        color: #273444;
        font-weight: 800;
        padding: .75rem 1rem;
    }

    .ocorrencias-page .item-em-tratamento-corpo {
        padding: .85rem 1.5rem 1rem;
    }

    .ocorrencias-page .item-dado {
        background: transparent;
        border: 0;
        padding: .35rem .15rem;
    }

    .ocorrencias-page .item-produto-nome,
    .ocorrencias-page .resumo-valor {
        font-size: .92rem !important;
    }

    .ocorrencias-page .item-descricao {
        background: transparent;
        border: 0;
        margin-top: .45rem;
        padding: .35rem .15rem 0;
    }

    .ocorrencias-page .etapa-box {
        background: #fff;
        border-color: #d8dee5;
        border-radius: .4rem;
    }

    .ocorrencias-page .etapa-box.fase-concluida {
        background: #fbfffc;
        border-color: #48a875;
        padding: 0;
    }

    .ocorrencias-page .etapa-box.fase-concluida > .etapa-titulo {
        display: none;
    }

    .ocorrencias-page .etapa-concluida-resumo {
        background: transparent;
        border: 0;
        border-radius: .4rem;
        min-height: 64px;
        padding: .65rem 1rem;
    }

    .evidencias-miniaturas {
        display: inline-flex;
        gap: .45rem;
        margin-left: .55rem;
        vertical-align: middle;
    }

    .evidencias-miniaturas img,
    .evidencia-miniatura-arquivo {
        align-items: center;
        border: 1px solid #d8dee5;
        border-radius: .32rem;
        display: inline-flex;
        height: 46px;
        justify-content: center;
        object-fit: cover;
        width: 66px;
    }

    .etapa-concluida-acoes {
        align-items: center;
        display: flex;
        gap: .7rem;
    }

    .ocorrencias-page .etapa-autorizacao-decisao {
        background: #f7f9fc;
        border: 1px solid #cfd8e3;
        border-radius: .45rem;
        display: grid;
        gap: 3rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: auto 1fr;
        padding: 0 1rem 1rem;
        position: relative;
    }

    .ocorrencias-page .etapa-autorizacao-decisao > .etapa-titulo {
        background: #eef4ff;
        border-bottom: 1px solid #cbd9f2;
        color: #173f78;
        font-size: 1.08rem;
        grid-column: 1 / -1;
        margin: 0 -1rem;
        padding: .85rem 1rem;
    }

    .ocorrencias-page .fluxo-gatilho {
        align-items: center;
        background: #fff;
        border: 1px solid #b9c8dc;
        border-radius: 999px;
        color: #4b6380;
        display: flex;
        font-size: .68rem;
        font-weight: 800;
        gap: .3rem;
        grid-column: 1 / -1;
        justify-content: center;
        left: 50%;
        letter-spacing: .04em;
        padding: .35rem .55rem;
        position: absolute;
        text-transform: uppercase;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 3;
    }

    .ocorrencias-page .subetapa {
        background: #fff;
        border: 1px solid #d7dfe8;
        border-radius: .45rem;
        margin-top: 1rem;
        min-width: 0;
        padding: 1rem;
    }

    .ocorrencias-page .subetapa-autorizacao {
        grid-column: 1;
        grid-row: 2;
    }

    .ocorrencias-page .subetapa-decisao {
        grid-column: 2;
        grid-row: 2;
    }

    .ocorrencias-page .subetapa-titulo {
        color: #243b53;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .ocorrencias-page .subetapa-decisao.bloqueada {
        background: #f2f4f7;
        border-color: #d8dde3;
        color: #7b8794;
        opacity: 1;
        position: relative;
    }

    .ocorrencias-page .subetapa-decisao.bloqueada .row {
        opacity: .55;
    }

    .subetapa-identidade {
        align-items: center;
        display: flex;
        gap: .55rem;
    }

    .subetapa-status {
        border: 1px solid;
        border-radius: 999px;
        font-size: .67rem;
        font-weight: 800;
        padding: .25rem .48rem;
        text-transform: uppercase;
    }

    .subetapa-status-acao {
        background: #fff3cd;
        border-color: #f1cf67;
        color: #805b00;
    }

    .subetapa-status-bloqueado {
        background: #eef1f4;
        border-color: #cbd2d9;
        color: #68737e;
    }

    .subetapa-status-liberado {
        background: #dff5e8;
        border-color: #8ac6a2;
        color: #146c43;
    }

    .subetapa-descricao {
        color: #68737e;
        font-size: .8rem;
        margin: -.45rem 0 .85rem;
    }

    .aviso-autorizacao {
        align-items: flex-start;
        background: #fff9e8;
        border: 1px solid #efc35f;
        border-radius: .38rem;
        color: #8a5700;
        display: flex;
        gap: .7rem;
        margin-bottom: .75rem;
        padding: .8rem;
    }

    .aviso-autorizacao > i {
        font-size: 1.2rem;
    }

    .aviso-autorizacao strong,
    .aviso-autorizacao small {
        display: block;
    }

    .contador-campo {
        color: #7b8794;
        font-size: .75rem;
        margin: .2rem 0 .55rem;
        text-align: right;
    }

    .ocorrencias-page .historico-container {
        border: 1px solid #d8dee5;
        border-radius: .4rem;
        margin-top: 1rem;
        padding: .75rem 1rem;
    }

    @media (max-width: 991.98px) {
        .tratativa-cabecalho {
            align-items: flex-start;
        }

        .ocorrencias-page .etapa-autorizacao-decisao {
            display: block;
        }

        .ocorrencias-page .fluxo-gatilho {
            display: none;
        }

        .ocorrencias-page .subetapa-decisao {
            border-left: 0;
            border-top: 1px solid #d8dee5;
        }
    }
</style>

@php
    $ocorrencias = $romaneio->ocorrencias;
    $ocorrenciaCabecalho = $ocorrencias->first();
    $quantidadeAbertas = $ocorrencias
        ->whereNotIn('status', ['Resolvida', 'Cancelada'])
        ->count();
    $quantidadeLiberadas = $ocorrencias
        ->where('permite_fechamento_logistico', true)
        ->count();
@endphp

<div class="container-fluid ocorrencias-page py-3 px-3">
    <div class="tratativa-cabecalho">
        <div class="tratativa-cabecalho-principal">
            <div>
                <h2 class="tratativa-titulo">Tratativa de ocorrências</h2>
                <div class="tratativa-subtitulo">
                    Romaneio {{ $romaneio->codigo_romaneio ?? ('ROM-' . $romaneio->id) }}
                    • Entrega {{ $romaneio->entrega?->codigo_entrega ?? ('#' . ($romaneio->entrega_id ?? '—')) }}
                </div>
            </div>

            @if ($ocorrenciaCabecalho)
                <div class="tratativa-badges">
                    <span class="badge-operacional badge-operacional-atencao">
                        <i class="bi bi-shield-exclamation"></i>
                        {{ str_replace('_', ' ', $ocorrenciaCabecalho->tipo) }}
                    </span>
                    <span class="badge-operacional badge-operacional-critico">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $ocorrenciaCabecalho->criticidade }}
                    </span>
                    <span class="badge-operacional badge-operacional-status">
                        <i class="bi bi-clock"></i>
                        {{ str_replace('_', ' ', $ocorrenciaCabecalho->status) }}
                    </span>
                </div>
            @endif
        </div>

        <div class="tratativa-acoes">
            <a
                href="{{ route('entregas.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Voltar às entregas
            </a>
            <button
                type="button"
                class="btn btn-outline-secondary"
                aria-label="Mais opções"
            >
                <i class="bi bi-three-dots-vertical"></i>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Revise os dados informados:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($ocorrencias as $ocorrencia)
        @php
            $entregaItem = $ocorrencia->entregaItem
                ?? $ocorrencia->romaneioItem?->entregaItem;
            $avaliacoesTriagem = $ocorrencia->avaliacoes
                ->sortBy('ordem')
                ->values();
            $avaliacaoComLote = $avaliacoesTriagem->first(
                fn ($avaliacao) => $avaliacao->lote !== null
            );
            $loteOriginal = $entregaItem?->vendaItem?->lote
                ?? $avaliacaoComLote?->lote;
            $produto = $entregaItem?->vendaItem?->produto
                ?? $entregaItem?->itemOrcamento?->produto;
            $detalhesAvaria = $avaliacoesTriagem
                ->map(function ($avaliacao) {
                    $detalhes = collect();

                    if (
                        ! empty($avaliacao->embalagem)
                        && $avaliacao->embalagem !== 'Intacta'
                    ) {
                        $detalhes->push(
                            'Embalagem: '
                            . str_replace('_', ' ', $avaliacao->embalagem)
                        );
                    }

                    if (
                        ! empty($avaliacao->conteudo)
                        && $avaliacao->conteudo !== 'Preservado'
                    ) {
                        $detalhes->push(
                            'Conteúdo: '
                            . str_replace('_', ' ', $avaliacao->conteudo)
                        );
                    }

                    if (
                        ! empty($avaliacao->integridade)
                        && $avaliacao->integridade !== 'Integro'
                    ) {
                        $detalhes->push(
                            'Integridade: '
                            . str_replace('_', ' ', $avaliacao->integridade)
                        );
                    }

                    if (
                        ! empty($avaliacao->validade_status)
                        && ! in_array(
                            $avaliacao->validade_status,
                            [
                                'Dentro_validade',
                                'Proximo_vencimento',
                                'Nao_se_aplica',
                            ],
                            true
                        )
                    ) {
                        $detalhes->push(
                            'Validade: '
                            . str_replace('_', ' ', $avaliacao->validade_status)
                        );
                    }

                    $observacaoAvaliacao = trim(
                        (string) ($avaliacao->observacao ?? '')
                    );

                    if ($observacaoAvaliacao !== '') {
                        $detalhes->push($observacaoAvaliacao);
                    }

                    return $detalhes->unique()->implode('; ');
                })
                ->filter()
                ->values();
            $descricaoDefeito = $detalhesAvaria->isNotEmpty()
                ? $detalhesAvaria->implode(' | ')
                : (
                    trim((string) ($ocorrencia->justificativa_triagem ?? ''))
                    ?: 'Nenhuma avaria foi detalhada na triagem.'
                );
            $encerrada = in_array(
                $ocorrencia->status,
                ['Resolvida', 'Cancelada'],
                true
            );
            $classeCriticidade = match ($ocorrencia->criticidade) {
                'Critico' => 'ocorrencia-critica',
                'Atencao' => 'ocorrencia-atencao',
                default => 'ocorrencia-informativa',
            };
            $badgeCriticidade = match ($ocorrencia->criticidade) {
                'Critico' => 'text-bg-danger',
                'Atencao' => 'text-bg-warning',
                default => 'text-bg-info',
            };

            $possuiResponsavel = ! empty($ocorrencia->responsavel_analise_id);
            $possuiEvidencia = $ocorrencia->anexos->isNotEmpty();
            $autorizacaoConcluida = ! $ocorrencia->exige_autorizacao
                || ! empty($ocorrencia->autorizada_por);
            $decisaoConcluida = ! empty($ocorrencia->decidida_por)
                && ! empty($ocorrencia->classificacao_final)
                && ! empty($ocorrencia->decisao);
            $fechamentoLogisticoLiberado = (bool) $ocorrencia->permite_fechamento_logistico;
            $destinosComDevolucao = [
                'Quarentena',
                'Reintegracao',
                'Perda',
                'Reposicao',
                'Tratamento_individual',
            ];
            $exigeDevolucao = in_array($ocorrencia->destino_estoque, $destinosComDevolucao, true);
            $devolucao = $ocorrencia->devolucao;
            $devolucaoConcluida = ! $exigeDevolucao
                || ($devolucao && $devolucao->estaConcluida());

            $triagemConcluida = $ocorrencia->triagem_status === 'Concluida';
            $destinosTriagem = $ocorrencia->avaliacoes
                ->pluck('destino_sugerido')
                ->filter()
                ->unique()
                ->values();
            $possuiDestinoDetalhado = $triagemConcluida
                && $destinosTriagem->isNotEmpty();
            $destinoCabecalhoTriagem = match (true) {
                $destinosTriagem->count() === 1 => $destinosTriagem->first(),
                $destinosTriagem->count() > 1 => 'Tratamento_individual',
                default => null,
            };

            $faseResponsavelEvidenciasConcluida = $possuiResponsavel && $possuiEvidencia;
            $faseAutorizacaoDecisaoConcluida = $autorizacaoConcluida && $decisaoConcluida;
            $faseExecucaoSolucaoConcluida = $faseAutorizacaoDecisaoConcluida
                && $devolucaoConcluida;

            $podeExecutarFase1 = ! $encerrada
                && $triagemConcluida;
            $podeExecutarFase2 = ! $encerrada
                && $triagemConcluida
                && $faseResponsavelEvidenciasConcluida;
            $podeExecutarFase3 = ! $encerrada
                && $triagemConcluida
                && $faseResponsavelEvidenciasConcluida
                && $faseAutorizacaoDecisaoConcluida;
            $podeExecutarFase4 = ! $encerrada
                && $triagemConcluida
                && $faseResponsavelEvidenciasConcluida
                && $faseAutorizacaoDecisaoConcluida
                && $devolucaoConcluida;
            $podeAutorizar = $podeExecutarFase2
                && $ocorrencia->exige_autorizacao
                && empty($ocorrencia->autorizada_por);
            $podeRegistrarDecisao = $podeExecutarFase2
                && $autorizacaoConcluida
                && ! $decisaoConcluida;

            $pendenciaResponsavelEvidencias = ! $possuiResponsavel
                ? 'Selecione quem ficará responsável pela análise.'
                : 'Anexe pelo menos uma foto ou documento.';

            $pendenciaAutorizacaoDecisao = ! $autorizacaoConcluida
                ? 'Informe a justificativa e autorize a ocorrência.'
                : 'Defina a classificação, o destino e a decisão administrativa.';

            $pendenciaDevolucao = ! $exigeDevolucao
                ? 'Esta ocorrência não exige tratamento de devolução.'
                : (! $devolucao
                    ? 'A devolução do material ainda precisa ser iniciada.'
                    : 'A devolução #' . $devolucao->id . ' está em '
                        . str_replace('_', ' ', $devolucao->status)
                        . '. Conclua esse fluxo para continuar.');

            $pendenciaEncerramento = ! $fechamentoLogisticoLiberado
                ? 'Confira o responsável e as evidências e libere o fechamento logístico.'
                : 'Informe a solução aplicada e resolva a ocorrência.';

            $requisitos = [
                [
                    'titulo' => '1. Responsável e evidências',
                    'concluido' => $faseResponsavelEvidenciasConcluida,
                    'estado' => $faseResponsavelEvidenciasConcluida
                        ? 'concluido'
                        : 'atual',
                    'pendencia' => $pendenciaResponsavelEvidencias,
                    'conclusao' => 'Responsável definido e evidência registrada.',
                ],
                [
                    'titulo' => '2. Validação e tratativa',
                    'concluido' => $faseAutorizacaoDecisaoConcluida,
                    'estado' => $faseAutorizacaoDecisaoConcluida
                        ? 'concluido'
                        : ($faseResponsavelEvidenciasConcluida ? 'atual' : 'bloqueado'),
                    'pendencia' => $faseResponsavelEvidenciasConcluida
                        ? $pendenciaAutorizacaoDecisao
                        : 'Aguardando a conclusão da etapa 1.',
                    'conclusao' => 'Validação e tratativa administrativa registradas.',
                ],
                [
                    'titulo' => '3. Execução da solução',
                    'concluido' => $faseExecucaoSolucaoConcluida,
                    'estado' => $faseExecucaoSolucaoConcluida
                        ? 'concluido'
                        : ($faseResponsavelEvidenciasConcluida && $faseAutorizacaoDecisaoConcluida
                            ? 'atual'
                            : 'bloqueado'),
                    'pendencia' => $faseResponsavelEvidenciasConcluida && $faseAutorizacaoDecisaoConcluida
                        ? $pendenciaDevolucao
                        : 'Aguardando a conclusão das etapas anteriores.',
                    'conclusao' => $exigeDevolucao
                        ? 'Tratamento da devolução concluído.'
                        : 'Não aplicável para esta ocorrência.',
                ],
                [
                    'titulo' => '4. Encerramento',
                    'concluido' => $encerrada,
                    'estado' => $encerrada
                        ? 'concluido'
                        : ($faseResponsavelEvidenciasConcluida
                            && $faseAutorizacaoDecisaoConcluida
                            && $devolucaoConcluida
                                ? 'atual'
                                : 'bloqueado'),
                    'pendencia' => $faseResponsavelEvidenciasConcluida
                        && $faseAutorizacaoDecisaoConcluida
                        && $devolucaoConcluida
                            ? $pendenciaEncerramento
                            : 'Aguardando a conclusão da etapa 3.',
                    'conclusao' => 'Ocorrência encerrada.',
                ],
            ];

            $primeiraPendencia = collect($requisitos)->firstWhere('concluido', false);
            $podeResolver = ! $encerrada
                && $faseResponsavelEvidenciasConcluida
                && $faseAutorizacaoDecisaoConcluida
                && $devolucaoConcluida
                && $fechamentoLogisticoLiberado;

            if ($encerrada) {
                $orientacaoTitulo = 'Tratativa concluída';
                $orientacaoTexto = 'Esta ocorrência já foi encerrada. Consulte os dados e o histórico abaixo.';
            } elseif (! $triagemConcluida) {
                $orientacaoTitulo = 'Triagem visual pendente';
                $orientacaoTexto = 'Avalie as evidências e indique o destino sugerido antes de iniciar as tratativas.';
            } elseif ($primeiraPendencia) {
                $orientacaoTitulo = 'Próxima ação: ' . $primeiraPendencia['titulo'];
                $orientacaoTexto = $primeiraPendencia['pendencia'];
            } else {
                $orientacaoTitulo = 'Ocorrência pronta para conclusão';
                $orientacaoTexto = 'Todos os requisitos foram atendidos. Informe a solução aplicada e resolva a ocorrência.';
            }
        @endphp

        <div class="card ocorrencia-card {{ $classeCriticidade }} mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge text-bg-primary ocorrencia-identificador ocorrencia-identificador-principal">
                        <i class="bi bi-exclamation-diamond-fill"></i>
                        OCORRÊNCIA #{{ $ocorrencia->id }}
                    </span>
                    <span>{{ $ocorrencia->tipo }}</span>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge {{ $badgeCriticidade }}">
                        {{ $ocorrencia->criticidade }}
                    </span>
                    <span class="badge text-bg-light">
                        {{ str_replace('_', ' ', $ocorrencia->status) }}
                    </span>
                    @if ($ocorrencia->permite_fechamento_logistico)
                        <span class="badge text-bg-success">
                            Fechamento logístico liberado
                        </span>
                    @else
                        <span class="badge text-bg-danger">
                            Bloqueando fluxo
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="orientacao-operador {{ $podeResolver || $encerrada ? 'concluida' : '' }}">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi {{ $podeResolver || $encerrada ? 'bi-check-circle-fill text-success' : 'bi-signpost-split-fill text-primary' }} fs-4"></i>
                        <div>
                            <div class="orientacao-titulo">{{ $orientacaoTitulo }}</div>
                            <div class="orientacao-texto">{{ $orientacaoTexto }}</div>
                        </div>
                    </div>

                    @if (! $triagemConcluida && ! $encerrada)
                        <a
                            href="{{ route('romaneios.ocorrencias.triagem', $romaneio) }}"
                            class="btn btn-primary btn-sm mt-3"
                        >
                            <i class="bi bi-grid-3x3-gap me-1"></i>
                            Realizar triagem visual
                        </a>
                    @endif

                    <div class="fluxo-etapas">
                        @foreach ($requisitos as $requisito)
                            @php
                                $iconeRequisito = match ($requisito['estado']) {
                                    'concluido' => 'bi-check-lg',
                                    'atual' => null,
                                    default => 'bi-lock-fill',
                                };
                            @endphp
                            <div class="fluxo-etapa {{ $requisito['estado'] }}">
                                <span class="fluxo-etapa-numero">
                                    @if ($iconeRequisito)
                                        <i class="bi {{ $iconeRequisito }}"></i>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <div>
                                    <div class="fluxo-etapa-titulo">{{ $requisito['titulo'] }}</div>
                                    <div class="fluxo-etapa-status">
                                        {{ $requisito['concluido']
                                            ? 'Concluída'
                                            : ($requisito['estado'] === 'atual' ? 'Em andamento' : 'Pendente') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($fechamentoLogisticoLiberado && ! $encerrada)
                        <div class="alert alert-success mt-3 mb-0">
                            <strong>Fechamento logístico liberado.</strong>
                            A entrega pode continuar, mas a ocorrência administrativa permanece aberta até a conclusão de todas as tratativas.
                        </div>
                    @endif
                </div>

                <div class="item-em-tratamento">
                    <div class="item-em-tratamento-cabecalho">
                        <span>
                            <i class="bi bi-box-seam me-2"></i>
                            Material em tratamento
                        </span>
                    </div>

                    <div class="item-em-tratamento-corpo">
                        <div class="row g-2">
                            <div class="col-lg-3 col-md-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Produto</div>
                                    <div class="item-produto-nome">
                                        {{ $produto?->nome ?? ('Item #' . ($ocorrencia->entrega_item_id ?? '—')) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Lote original</div>
                                    <div class="resumo-valor">
                                        {{ $loteOriginal?->numero_lote ?? 'Não identificado' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Validade</div>
                                    <div class="resumo-valor">
                                        {{ $loteOriginal?->validade_lote
                                            ? \Carbon\Carbon::parse($loteOriginal->validade_lote)->format('d/m/Y')
                                            : 'Não aplicável' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Quantidade afetada</div>
                                    <div class="resumo-valor fs-6">
                                        {{ number_format((float) $ocorrencia->quantidade_envolvida, 3, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-1 col-md-3 col-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Triagem</div>
                                    <div class="resumo-valor">
                                        <span class="badge text-bg-warning">
                                            {{ $ocorrencia->classificacao_inicial ?? 'Não informada' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="item-dado">
                                    <div class="resumo-label">Destino sugerido</div>
                                    <div class="resumo-valor">
                                        <span class="badge text-bg-warning">
                                            {{ str_replace('_', ' ', $destinoCabecalhoTriagem ?: 'Não informado') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item-descricao">
                            <div class="resumo-label mb-1">Observação do defeito</div>
                            <div class="badge text-danger fs-6">{{ $descricaoDefeito }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="etapa-box {{ $faseResponsavelEvidenciasConcluida ? 'fase-concluida' : (! $podeExecutarFase1 ? 'bloqueada' : '') }}">
                            <div class="etapa-titulo">
                                1. Responsável e evidências
                            </div>

                            @if ($faseResponsavelEvidenciasConcluida)
                                <div class="etapa-concluida-resumo mb-0">
                                    <div>
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Etapa 1 concluída</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Responsável:</span>
                                        <strong>{{ $ocorrencia->responsavelAnalise?->name ?? 'Definido' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Evidências:</span>
                                        <span class="evidencias-miniaturas">
                                            @foreach ($ocorrencia->anexos->take(2) as $anexoResumo)
                                                @php
                                                    $caminhoResumo = (string) $anexoResumo->caminho;
                                                    $arquivoResumoPublico = str_starts_with($caminhoResumo, 'image/')
                                                        || str_starts_with($caminhoResumo, 'uploads/');
                                                    $urlResumo = $arquivoResumoPublico
                                                        ? asset($caminhoResumo)
                                                        : asset('storage/' . $caminhoResumo);
                                                @endphp

                                                @if (str_starts_with((string) $anexoResumo->mime_type, 'image/'))
                                                    <img
                                                        src="{{ $urlResumo }}"
                                                        alt="Miniatura da evidência"
                                                    >
                                                @else
                                                    <span class="evidencia-miniatura-arquivo">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </span>
                                    </div>
                                    <div class="etapa-concluida-acoes">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-link text-decoration-none"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#evidencias-{{ $ocorrencia->id }}"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            Ver evidências
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-link text-decoration-none"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#editar-etapa1-{{ $ocorrencia->id }}"
                                        >
                                            <i class="bi bi-people me-1"></i>
                                            Reatribuir
                                        </button>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </div>
                                </div>
                            @endif

                            @if (! $podeExecutarFase1 && ! $faseResponsavelEvidenciasConcluida)
                                <div class="alert alert-secondary py-2">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Conclua a triagem visual para liberar esta etapa.
                                </div>
                            @endif

                            <div
                                id="editar-etapa1-{{ $ocorrencia->id }}"
                                class="{{ $faseResponsavelEvidenciasConcluida ? 'collapse mt-3' : '' }}"
                            >
                            <form
                                method="POST"
                                action="{{ route('romaneios.ocorrencias.responsavel', [$romaneio, $ocorrencia]) }}"
                                class="mb-3"
                            >
                                @csrf
                                @method('PATCH')

                                <label class="form-label">Responsável pela análise</label>
                                <div class="input-group">
                                    <select
                                        name="responsavel_analise_id"
                                        class="form-select"
                                        required
                                        @disabled(! $podeExecutarFase1)
                                    >
                                        <option value="">Selecione</option>
                                        @foreach ($responsaveis as $responsavel)
                                            <option
                                                value="{{ $responsavel->id }}"
                                                @selected((int) $ocorrencia->responsavel_analise_id === (int) $responsavel->id)
                                            >
                                                {{ $responsavel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="submit"
                                        class="btn btn-outline-primary"
                                        @disabled(! $podeExecutarFase1)
                                    >
                                        Salvar
                                    </button>
                                </div>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('romaneios.ocorrencias.evidencias.store', [$romaneio, $ocorrencia]) }}"
                                enctype="multipart/form-data"
                                class="mb-3"
                            >
                                @csrf

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo</label>
                                        <select name="tipo" class="form-select" required @disabled(! $podeExecutarFase1)>
                                            <option value="Foto">Foto</option>
                                            <option value="Documento">Documento</option>
                                            <option value="Comprovante">Comprovante</option>
                                            <option value="Assinatura">Assinatura</option>
                                            <option value="Outro">Outro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Arquivo</label>
                                        <input
                                            type="file"
                                            name="arquivo"
                                            class="form-control"
                                            accept="image/jpeg,image/png,image/webp,application/pdf"
                                            required
                                            @disabled(! $podeExecutarFase1)
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Descrição</label>
                                        <input
                                            type="text"
                                            name="descricao"
                                            class="form-control"
                                            maxlength="500"
                                            placeholder="Identifique o conteúdo da evidência"
                                            @disabled(! $podeExecutarFase1)
                                        >
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-outline-success" @disabled(! $podeExecutarFase1)>
                                            <i class="bi bi-paperclip me-1"></i>
                                            Anexar evidência
                                        </button>
                                    </div>
                                </div>
                            </form>

                            </div>

                            <div
                                class="d-grid gap-2 {{ $faseResponsavelEvidenciasConcluida ? 'collapse' : '' }}"
                                id="evidencias-{{ $ocorrencia->id }}"
                            >
                                @forelse ($ocorrencia->anexos as $anexo)
                                    @php
                                        $caminhoAnexo = (string) $anexo->caminho;
                                        $arquivoPublico = str_starts_with($caminhoAnexo, 'image/')
                                            || str_starts_with($caminhoAnexo, 'uploads/');
                                        $urlAnexo = $arquivoPublico
                                            ? asset($anexo->caminho)
                                            : asset('storage/' . $anexo->caminho);
                                    @endphp
                                    <div class="evidencia-item">
                                        <a
                                            href="{{ $urlAnexo }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="evidencia-thumb text-decoration-none"
                                        >
                                            @if (str_starts_with((string) $anexo->mime_type, 'image/'))
                                                <img
                                                    src="{{ $urlAnexo }}"
                                                    alt="Evidência da ocorrência"
                                                >
                                            @else
                                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                            @endif
                                            <span>
                                                <strong class="d-block">{{ $anexo->nome_original }}</strong>
                                                <small class="text-muted">
                                                    {{ $anexo->descricao ?? $anexo->tipo }}
                                                </small>
                                            </span>
                                        </a>

                                        @if ($podeExecutarFase1)
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm btn-remover-evidencia"
                                                title="Remover evidência"
                                                aria-label="Remover evidência"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalRemoverEvidencia"
                                                data-action="{{ route('romaneios.ocorrencias.evidencias.destroy', [$romaneio, $ocorrencia, $anexo]) }}"
                                                data-nome="{{ $anexo->nome_original }}"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @empty
                                    <div class="alert alert-warning py-2 mb-0">
                                        Nenhuma evidência anexada.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="etapa-box etapa-autorizacao-decisao {{ $faseAutorizacaoDecisaoConcluida ? 'fase-concluida' : (! $podeExecutarFase2 ? 'etapa-futura' : '') }}">
                            <div class="etapa-titulo">
                                <i class="bi bi-diagram-3 me-1"></i>
                                2. Validação e definição da tratativa
                            </div>

                            @if ($faseAutorizacaoDecisaoConcluida)
                                <div class="etapa-concluida-resumo mb-0">
                                    <div>
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Etapa 2 concluída</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Classificação:</span>
                                        <strong>{{ $ocorrencia->classificacao_final }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted">Destino:</span>
                                        <strong>{{ str_replace('_', ' ', $ocorrencia->destino_estoque) }}</strong>
                                    </div>
                                </div>
                            @endif

                            <div class="fluxo-gatilho">
                                Validar
                                <i class="bi bi-arrow-right"></i>
                                Liberar tratativa
                            </div>

                            @if (! $podeExecutarFase2 && ! $faseAutorizacaoDecisaoConcluida)
                                <div class="alert alert-secondary py-2">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Conclua a etapa 1 para liberar a autorização e a decisão.
                                </div>
                            @endif

                            @if ($ocorrencia->exige_autorizacao)
                                @if ($ocorrencia->autorizada_por)
                                    <div class="subetapa subetapa-autorizacao">
                                        <div class="subetapa-titulo">
                                            <div class="subetapa-identidade">
                                                <span class="subetapa-numero"><i class="bi bi-check-lg"></i></span>
                                                <span>1. Validação concluída</span>
                                            </div>
                                            <span class="subetapa-status subetapa-status-liberado">
                                                Concluída
                                            </span>
                                        </div>
                                        <div class="alert alert-success py-2 mb-0">
                                        <strong>Autorizada.</strong>
                                        {{ $ocorrencia->justificativa_autorizacao }}
                                        </div>
                                    </div>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('romaneios.ocorrencias.autorizar', [$romaneio, $ocorrencia]) }}"
                                        class="subetapa subetapa-autorizacao"
                                        data-form-autorizacao
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="hidden"
                                            name="justificativa"
                                            value=""
                                            data-justificativa-negativa
                                        >

                                        <div class="subetapa-titulo">
                                            <div class="subetapa-identidade">
                                                <span class="subetapa-numero">1</span>
                                                <span>Validar ocorrência</span>
                                            </div>
                                            <span class="subetapa-status subetapa-status-acao">
                                                Ação necessária
                                            </span>
                                        </div>
                                        <div class="subetapa-descricao">
                                            Confirme se a ocorrência possui elementos suficientes para seguir à definição da tratativa.
                                        </div>
                                        <div class="aviso-autorizacao">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <div>
                                                <strong>Esta ocorrência exige autorização</strong>
                                                <small>A decisão será registrada e aplicada à ocorrência atual.</small>
                                            </div>
                                        </div>
                                        <label class="form-label">Motivo da autorização</label>
                                        <textarea
                                            name="justificativa_autorizacao"
                                            class="form-control mb-2"
                                            rows="3"
                                            minlength="5"
                                            maxlength="500"
                                            required
                                            @disabled(! $podeAutorizar)
                                        ></textarea>
                                        <div class="contador-campo">0 / 500</div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <button type="submit" class="btn btn-primary w-100" @disabled(! $podeAutorizar)>
                                                    <i class="bi bi-check-lg me-1"></i>
                                                    Autorizar e liberar decisão
                                                </button>
                                            </div>
                                            <div class="col-sm-6">
                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-secondary w-100"
                                                    formaction="{{ route('romaneios.ocorrencias.cancelar', [$romaneio, $ocorrencia]) }}"
                                                    data-nao-autorizar
                                                    @disabled(! $podeAutorizar)
                                                >
                                                    <i class="bi bi-x-circle me-1"></i>
                                                    Não autorizar e encerrar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-text mt-2">
                                            Ao autorizar, você permitirá a definição do destino definitivo e o registro da decisão.
                                        </div>
                                    </form>
                                @endif
                            @else
                                <div class="subetapa subetapa-autorizacao">
                                    <div class="subetapa-titulo">
                                        <div class="subetapa-identidade">
                                            <span class="subetapa-numero"><i class="bi bi-check-lg"></i></span>
                                            <span>1. Validação dispensada</span>
                                        </div>
                                        <span class="subetapa-status subetapa-status-liberado">
                                            Liberada
                                        </span>
                                    </div>
                                    <div class="alert alert-success py-2 mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        A decisão já está liberada.
                                    </div>
                                </div>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('romaneios.ocorrencias.decisao', [$romaneio, $ocorrencia]) }}"
                                class="subetapa subetapa-decisao {{ ! $podeRegistrarDecisao && ! $decisaoConcluida ? 'bloqueada' : '' }}"
                            >
                                @csrf
                                @method('PATCH')

                                <div class="subetapa-titulo">
                                    <div class="subetapa-identidade">
                                        <span class="subetapa-numero">2</span>
                                        <span>Definir tratativa</span>
                                    </div>
                                    @if ($autorizacaoConcluida)
                                        <span class="subetapa-status subetapa-status-liberado">
                                            Liberada
                                        </span>
                                    @else
                                        <span class="subetapa-status subetapa-status-bloqueado">
                                            <i class="bi bi-lock-fill me-1"></i>
                                            Aguardando validação
                                        </span>
                                    @endif
                                </div>
                                <div class="subetapa-descricao">
                                    Defina a classificação definitiva, o destino do material e a decisão administrativa.
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label">Classificação final</label>
                                        <select name="classificacao_final" class="form-select" required @disabled(! $podeRegistrarDecisao)>
                                            <option value="">Selecione</option>
                                            @foreach (['Extravio', 'Avaria', 'Recusa', 'Devolucao', 'Perda', 'Divergencia', 'Improcedente', 'Outro'] as $classificacao)
                                                <option value="{{ $classificacao }}" @selected($ocorrencia->classificacao_final === $classificacao)>
                                                    {{ $classificacao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Destino do estoque</label>
                                        @if ($possuiDestinoDetalhado)
                                            <input
                                                type="hidden"
                                                name="destino_estoque"
                                                value="{{ $destinoCabecalhoTriagem }}"
                                            >
                                            <input
                                                type="text"
                                                class="form-control bg-light"
                                                value="{{ str_replace('_', ' ', $destinoCabecalhoTriagem) }}"
                                                readonly
                                            >
                                            <div class="form-text">
                                                Definido automaticamente pelas linhas da triagem.
                                            </div>
                                        @else
                                            <select
                                                name="destino_estoque"
                                                class="form-select"
                                                data-destino-estoque
                                                required
                                                @disabled(! $podeRegistrarDecisao)
                                            >
                                                @foreach (['Sem_movimentacao', 'Quarentena', 'Reintegracao', 'Perda', 'Reposicao'] as $destino)
                                                    <option
                                                        value="{{ $destino }}"
                                                        @selected($ocorrencia->destino_estoque === $destino)
                                                    >
                                                        {{ str_replace('_', ' ', $destino) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div
                                        class="col-12 {{ $ocorrencia->destino_estoque === 'Reposicao' ? '' : 'd-none' }}"
                                        data-reposicao-wrapper
                                    >
                                        <label class="form-label">Orçamento de reposição</label>
                                        <input
                                            type="number"
                                            name="orcamento_reposicao_id"
                                            class="form-control"
                                            min="1"
                                            value="{{ $ocorrencia->orcamento_reposicao_id }}"
                                            placeholder="ID do orçamento, quando aplicável"
                                            @disabled(! $podeRegistrarDecisao)
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Decisão administrativa</label>
                                        <textarea
                                            name="decisao"
                                            class="form-control"
                                            rows="4"
                                            minlength="5"
                                            maxlength="500"
                                            required
                                            @disabled(! $podeRegistrarDecisao)
                                        >{{ $ocorrencia->decisao }}</textarea>
                                        <div class="contador-campo">
                                            {{ mb_strlen((string) $ocorrencia->decisao) }} / 500
                                        </div>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary" @disabled(! $podeRegistrarDecisao)>
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Registrar decisão e avançar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 etapas-coluna">
                        <div class="etapa-box {{ ! $podeExecutarFase3 && ! $faseAutorizacaoDecisaoConcluida ? 'etapa-futura' : '' }}">
                            <div class="etapa-titulo">
                                3. Execução da solução
                            </div>

                            @if (! $exigeDevolucao)
                                <div class="alert alert-secondary mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Esta ocorrência não exige tratamento de devolução.
                                </div>
                            @elseif (! $devolucao)
                                <div class="alert alert-warning mb-0">
                                    <div class="fw-bold mb-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        Devolução ainda não iniciada
                                    </div>
                                    <div>
                                        O material precisa ser registrado no fluxo de devolução antes que a ocorrência possa ser encerrada.
                                    </div>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('devolucoes.ocorrencias.iniciar', $ocorrencia) }}"
                                    class="mt-3"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn {{ $podeExecutarFase3 ? 'btn-warning' : 'btn-secondary' }} w-100 fw-semibold"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ $podeExecutarFase3
                                            ? 'Criar a devolução e importar as linhas concluídas da triagem.'
                                            : 'Conclua a autorização e a decisão administrativa antes de iniciar.' }}"
                                        @disabled(! $podeExecutarFase3)
                                    >
                                        <i class="bi {{ $podeExecutarFase3 ? 'bi-arrow-repeat' : 'bi-lock-fill' }} me-1"></i>
                                        {{ $podeExecutarFase3 ? 'Iniciar tratamento da devolução' : 'Aguardando conclusão da etapa 2' }}
                                    </button>
                                </form>
                            @elseif ($devolucao->estaConcluida())
                                <div class="alert alert-success mb-0">
                                    <div class="fw-bold mb-1">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Devolução #{{ $devolucao->id }} concluída
                                    </div>
                                    <div>
                                        O tratamento do material foi concluído e esta etapa está liberada.
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <div class="fw-bold mb-1">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Devolução #{{ $devolucao->id }} criada — aguardando conclusão
                                    </div>
                                    <div>
                                        Status atual: {{ str_replace('_', ' ', $devolucao->status) }}. Conclua a devolução para liberar o encerramento da ocorrência.
                                    </div>
                                </div>

                                <a
                                    href="{{ route('devolucoes.pendentes', ['devolucao_id' => $devolucao->id]) }}"
                                    class="btn btn-outline-primary w-100 mt-3 fw-semibold"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Abrir a devolução para conferir e processar individualmente os destinos definidos na triagem."
                                >
                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                    Abrir devolução #{{ $devolucao->id }}
                                </a>
                            @endif
                        </div>

                        <div class="etapa-box {{ ! $podeExecutarFase4 && ! $encerrada ? 'etapa-futura' : '' }}">
                            <div class="etapa-titulo">
                                4. Liberação e encerramento
                            </div>

                            @if (! $podeExecutarFase4 && ! $encerrada)
                                <div class="alert alert-secondary py-2">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Conclua a etapa 3 para liberar o fechamento e o encerramento.
                                </div>
                            @endif

                            @if (! $ocorrencia->permite_fechamento_logistico && ! $encerrada)
                                <form
                                    method="POST"
                                    action="{{ route('romaneios.ocorrencias.liberar-fechamento', [$romaneio, $ocorrencia]) }}"
                                    class="mb-3"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-check border rounded p-3 ps-5 mb-2 bg-white">
                                        <input
                                            class="form-check-input confirmacao-acao"
                                            type="checkbox"
                                            id="liberar-{{ $ocorrencia->id }}"
                                            data-target="btn-liberar-{{ $ocorrencia->id }}"
                                            @disabled(! $podeExecutarFase4)
                                        >
                                        <label class="form-check-label" for="liberar-{{ $ocorrencia->id }}">
                                            Confirmo que o responsável e as evidências foram conferidos.
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        id="btn-liberar-{{ $ocorrencia->id }}"
                                        class="btn btn-success w-100"
                                        disabled
                                    >
                                        <i class="bi bi-unlock me-1"></i>
                                        Liberar fechamento logístico
                                    </button>
                                </form>
                            @elseif ($ocorrencia->permite_fechamento_logistico)
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <strong>Fechamento logístico liberado.</strong>
                                    A entrega pode continuar, mas a ocorrência administrativa ainda precisa ser concluída.
                                </div>
                            @endif

                            @if (! $encerrada)
                                <form
                                    method="POST"
                                    action="{{ route('romaneios.ocorrencias.resolver', [$romaneio, $ocorrencia]) }}"
                                    class="mb-3"
                                >
                                    @csrf
                                    @method('PATCH')

                                    @if (! $podeResolver)
                                        <div class="bloqueio-resolucao">
                                            <div class="fw-bold mb-1">
                                                <i class="bi bi-lock-fill me-1"></i>
                                                Resolução ainda indisponível
                                            </div>
                                            <div>
                                                {{ $primeiraPendencia['pendencia'] ?? 'Conclua as etapas obrigatórias para continuar.' }}
                                            </div>
                                        </div>
                                    @endif

                                    <label class="form-label">Solução aplicada</label>
                                    <textarea
                                        name="solucao"
                                        class="form-control mb-2"
                                        rows="3"
                                        minlength="5"
                                        maxlength="5000"
                                        placeholder="{{ $podeResolver ? 'Descreva como a ocorrência foi solucionada.' : 'Disponível após a conclusão das pendências.' }}"
                                        required
                                        @disabled(! $podeResolver)
                                    >{{ old('solucao') }}</textarea>
                                    <button
                                        type="submit"
                                        class="btn btn-outline-success w-100 {{ ! $podeResolver ? 'btn-resolucao-bloqueado' : '' }}"
                                        @disabled(! $podeResolver)
                                    >
                                        <i class="bi {{ $podeResolver ? 'bi-check-circle' : 'bi-lock' }} me-1"></i>
                                        {{ $podeResolver ? 'Resolver ocorrência' : 'Aguardando conclusão das pendências' }}
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-link text-danger text-decoration-none px-0"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#cancelamento-{{ $ocorrencia->id }}"
                                    @disabled(! $podeExecutarFase4)
                                >
                                    <i class="bi bi-three-dots me-1"></i>
                                    Opções administrativas
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('romaneios.ocorrencias.cancelar', [$romaneio, $ocorrencia]) }}"
                                    class="collapse mt-2"
                                    id="cancelamento-{{ $ocorrencia->id }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label class="form-label">Justificativa do cancelamento</label>
                                    <textarea
                                        name="justificativa"
                                        class="form-control mb-2"
                                        rows="2"
                                        minlength="5"
                                        maxlength="5000"
                                        required
                                        @disabled(! $podeExecutarFase4)
                                    ></textarea>
                                    <div class="form-check border rounded p-3 ps-5 mb-2 bg-white">
                                        <input
                                            class="form-check-input confirmacao-acao"
                                            type="checkbox"
                                            id="cancelar-{{ $ocorrencia->id }}"
                                            data-target="btn-cancelar-{{ $ocorrencia->id }}"
                                            @disabled(! $podeExecutarFase4)
                                        >
                                        <label class="form-check-label" for="cancelar-{{ $ocorrencia->id }}">
                                            Confirmo que a ocorrência deve ser cancelada como improcedente.
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        id="btn-cancelar-{{ $ocorrencia->id }}"
                                        class="btn btn-outline-danger w-100"
                                        disabled
                                    >
                                        Cancelar ocorrência
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-secondary">
                                    <strong>{{ $ocorrencia->status }}:</strong>
                                    {{ $ocorrencia->solucao ?? $ocorrencia->decisao }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if (
                    $devolucao
                    && $devolucao->lotes->isNotEmpty()
                )
                    <div class="card mt-3 border-primary">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <i class="bi bi-ui-checks-grid me-1"></i>
                                Tratamento individual dos materiais
                            </div>
                            <span class="badge text-bg-light">
                                {{ $devolucao->lotes->count() }}
                                {{ $devolucao->lotes->count() === 1 ? 'linha' : 'linhas' }}
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">Linha</th>
                                        <th class="text-end">Quantidade</th>
                                        <th>Embalagem</th>
                                        <th>Conteúdo</th>
                                        <th>Integridade</th>
                                        <th>Reaproveitamento</th>
                                        <th>Destino</th>
                                        <th>Lote</th>
                                        <th>Processamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($devolucao->lotes as $detalhe)
                                        @php
                                            $avaliacao = $detalhe->avaliacao;
                                            $classeProcessamento = match ($detalhe->status_processamento) {
                                                'Processado' => 'text-bg-success',
                                                'Cancelado' => 'text-bg-secondary',
                                                default => 'text-bg-warning',
                                            };
                                        @endphp

                                        <tr>
                                            <td class="text-center fw-semibold">
                                                {{ $avaliacao?->ordem ?? $loop->iteration }}
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ number_format((float) $detalhe->quantidade, 3, ',', '.') }}
                                            </td>
                                            <td>
                                                {{ str_replace('_', ' ', $avaliacao?->embalagem ?? 'Não informada') }}
                                            </td>
                                            <td>
                                                {{ str_replace('_', ' ', $avaliacao?->conteudo ?? 'Não informado') }}
                                            </td>
                                            <td>
                                                {{ str_replace('_', ' ', $avaliacao?->integridade ?? 'Não informada') }}
                                            </td>
                                            <td>
                                                {{ str_replace('_', ' ', $avaliacao?->reaproveitamento ?? 'Não informado') }}
                                            </td>
                                            <td>
                                                <span class="badge text-bg-primary">
                                                    {{ str_replace('_', ' ', $detalhe->destino_estoque) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $detalhe->lote?->numero_lote ?? 'Não identificado' }}
                                            </td>
                                            <td>
                                                <span class="badge {{ $classeProcessamento }}">
                                                    {{ $detalhe->status_processamento }}
                                                </span>

                                                @if ($detalhe->processado_em)
                                                    <div class="small text-muted mt-1">
                                                        {{ $detalhe->processado_em->format('d/m/Y H:i') }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th class="text-end">
                                            {{ number_format(
                                                (float) $devolucao->lotes->sum('quantidade'),
                                                3,
                                                ',',
                                                '.'
                                            ) }}
                                        </th>
                                        <th colspan="7">
                                            @if ($devolucao->movimentacaoEstoqueConcluida())
                                                <span class="badge text-bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Todos os destinos processados
                                                </span>
                                            @else
                                                <span class="badge text-bg-warning">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Existem destinos aguardando processamento
                                                </span>
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($ocorrencia->historicos->isNotEmpty())
                    <div class="historico-container">
                        <button
                            type="button"
                            class="btn btn-sm btn-link text-dark text-decoration-none w-100 d-flex align-items-center justify-content-between p-0"
                            data-bs-toggle="collapse"
                            data-bs-target="#historico-{{ $ocorrencia->id }}"
                        >
                            <span class="fw-bold">
                                <i class="bi bi-clock-history me-2"></i>
                                Histórico da ocorrência
                            </span>
                            <span class="text-primary">
                                Ver histórico
                                <i class="bi bi-chevron-down ms-3 text-dark"></i>
                            </span>
                        </button>

                        <div class="collapse mt-3" id="historico-{{ $ocorrencia->id }}">
                            @foreach ($ocorrencia->historicos->sortByDesc('id') as $historico)
                                <div class="historico-item">
                                    <strong>{{ $historico->evento }}</strong>
                                    <div class="small text-muted">
                                        {{ optional($historico->registrado_em)->format('d/m/Y H:i') }}
                                        — {{ $historico->status_anterior ?? 'Início' }}
                                        → {{ $historico->status_novo }}
                                    </div>
                                    @if ($historico->descricao)
                                        <div>{{ $historico->descricao }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            Nenhuma ocorrência foi registrada para este romaneio.
        </div>
    @endforelse
</div>

<div
    class="modal fade"
    id="modalRemoverEvidencia"
    tabindex="-1"
    aria-labelledby="modalRemoverEvidenciaTitulo"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalRemoverEvidenciaTitulo">
                    Remover evidência
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Confirme a remoção da evidência:
                </p>
                <div class="fw-bold" id="nomeEvidenciaRemocao"></div>
                <div class="alert alert-warning mt-3 mb-0">
                    O registro e o arquivo físico serão removidos.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form id="formRemoverEvidencia" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Remover
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document
            .querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(function (elemento) {
                bootstrap.Tooltip.getOrCreateInstance(elemento);
            });

        document.querySelectorAll('.confirmacao-acao').forEach(function (checkbox) {
            const botao = document.getElementById(checkbox.dataset.target);

            if (!botao) {
                return;
            }

            checkbox.addEventListener('change', function () {
                botao.disabled = !checkbox.checked;
            });
        });

        const modalRemoverEvidencia = document.getElementById('modalRemoverEvidencia');

        if (modalRemoverEvidencia) {
            modalRemoverEvidencia.addEventListener('show.bs.modal', function (event) {
                const botao = event.relatedTarget;
                const formulario = document.getElementById('formRemoverEvidencia');
                const nome = document.getElementById('nomeEvidenciaRemocao');

                formulario.action = botao.dataset.action;
                nome.textContent = botao.dataset.nome;
            });
        }

        document.querySelectorAll('[data-destino-estoque]').forEach(function (campo) {
            const formulario = campo.closest('form');
            const reposicao = formulario
                ? formulario.querySelector('[data-reposicao-wrapper]')
                : null;

            if (!reposicao) {
                return;
            }

            const atualizarReposicao = function () {
                reposicao.classList.toggle('d-none', campo.value !== 'Reposicao');
            };

            campo.addEventListener('change', atualizarReposicao);
            atualizarReposicao();
        });

        document.querySelectorAll('[data-form-autorizacao]').forEach(function (formulario) {
            const justificativaAutorizacao = formulario.querySelector(
                '[name="justificativa_autorizacao"]'
            );
            const justificativaNegativa = formulario.querySelector(
                '[data-justificativa-negativa]'
            );
            const botaoNaoAutorizar = formulario.querySelector(
                '[data-nao-autorizar]'
            );

            if (!justificativaAutorizacao || !justificativaNegativa) {
                return;
            }

            formulario.addEventListener('submit', function (event) {
                justificativaNegativa.value = justificativaAutorizacao.value;

                if (
                    event.submitter === botaoNaoAutorizar
                    && !window.confirm(
                        'Confirma que esta ocorrência não deve ser autorizada? Ela será encerrada como improcedente.'
                    )
                ) {
                    event.preventDefault();
                }
            });
        });

        document.querySelectorAll('textarea[maxlength="500"]').forEach(function (campo) {
            const contador = campo.nextElementSibling;

            if (!contador || !contador.classList.contains('contador-campo')) {
                return;
            }

            const atualizarContador = function () {
                contador.textContent = campo.value.length + ' / 500';
            };

            campo.addEventListener('input', atualizarContador);
            atualizarContador();
        });
    });
</script>
@endsection