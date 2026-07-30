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
</style>

@php
    $ocorrencias = $romaneio->ocorrencias;
    $quantidadeAbertas = $ocorrencias
        ->whereNotIn('status', ['Resolvida', 'Cancelada'])
        ->count();
    $quantidadeLiberadas = $ocorrencias
        ->where('permite_fechamento_logistico', true)
        ->count();
@endphp

<div class="container-fluid ocorrencias-page py-3 px-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-exclamation-diamond me-1"></i>
                Tratativa de ocorrências
            </h4>
            <div class="text-muted">
                Registre evidências, análise e decisão administrativa do retorno.
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge text-bg-dark">
                {{ $romaneio->codigo_romaneio ?? ('ROM-' . $romaneio->id) }}
            </span>
            <span class="badge text-bg-warning">
                {{ str_replace('_', ' ', $romaneio->status) }}
            </span>
            <a
                href="{{ route('romaneios.show', $romaneio) }}"
                class="btn btn-outline-secondary btn-sm"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Voltar ao romaneio
            </a>
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

    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-truck me-1"></i>
            Dados do retorno
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Romaneio</div>
                    <div class="resumo-valor">
                        {{ $romaneio->codigo_romaneio ?? ('#' . $romaneio->id) }}
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Entrega</div>
                    <div class="resumo-valor">
                        {{ $romaneio->entrega?->codigo_entrega ?? 'Não informada' }}
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Cliente</div>
                    <div class="resumo-valor">
                        {{ $romaneio->entrega?->cliente?->nome
                            ?? $romaneio->entrega?->venda?->cliente?->nome
                            ?? $romaneio->entrega?->orcamento?->cliente?->nome
                            ?? 'Não informado' }}
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Motorista</div>
                    <div class="resumo-valor">
                        {{ $equipe?->motorista?->nome ?? $romaneio->motorista?->nome ?? 'Não informado' }}
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Ajudante</div>
                    <div class="resumo-valor">
                        {{ $equipe?->ajudante?->nome ?? 'Não informado' }}
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Veículo</div>
                    <div class="resumo-valor">
                        {{ $equipe?->veiculo?->placa ?? $romaneio->veiculo?->placa ?? 'Não informado' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="alert alert-secondary mb-0 h-100">
                <div class="small text-uppercase fw-bold">Ocorrências registradas</div>
                <div class="fs-3 fw-bold">{{ $ocorrencias->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning mb-0 h-100">
                <div class="small text-uppercase fw-bold">Aguardando conclusão</div>
                <div class="fs-3 fw-bold">{{ $quantidadeAbertas }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success mb-0 h-100">
                <div class="small text-uppercase fw-bold">Fechamento logístico liberado</div>
                <div class="fs-3 fw-bold">{{ $quantidadeLiberadas }}</div>
            </div>
        </div>
    </div>

    @forelse ($ocorrencias as $ocorrencia)
        @php
            $entregaItem = $ocorrencia->romaneioItem?->entregaItem;
            $produto = $entregaItem?->vendaItem?->produto
                ?? $entregaItem?->itemOrcamento?->produto;
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
            $destinosComDevolucao = ['Quarentena', 'Reintegracao', 'Perda', 'Reposicao'];
            $exigeDevolucao = in_array($ocorrencia->destino_estoque, $destinosComDevolucao, true);
            $devolucao = $ocorrencia->devolucao;
            $devolucaoConcluida = ! $exigeDevolucao
                || ($devolucao && $devolucao->estaConcluida());

            $faseResponsavelEvidenciasConcluida = $possuiResponsavel && $possuiEvidencia;
            $faseAutorizacaoDecisaoConcluida = $autorizacaoConcluida && $decisaoConcluida;

            $podeExecutarFase2 = ! $encerrada
                && $faseResponsavelEvidenciasConcluida;
            $podeExecutarFase3 = ! $encerrada
                && $faseResponsavelEvidenciasConcluida
                && $faseAutorizacaoDecisaoConcluida;
            $podeExecutarFase4 = ! $encerrada
                && $faseResponsavelEvidenciasConcluida
                && $faseAutorizacaoDecisaoConcluida
                && $devolucaoConcluida;

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
                    'titulo' => '2. Autorização e decisão',
                    'concluido' => $faseAutorizacaoDecisaoConcluida,
                    'estado' => $faseAutorizacaoDecisaoConcluida
                        ? 'concluido'
                        : ($faseResponsavelEvidenciasConcluida ? 'atual' : 'bloqueado'),
                    'pendencia' => $faseResponsavelEvidenciasConcluida
                        ? $pendenciaAutorizacaoDecisao
                        : 'Aguardando a conclusão da etapa 1.',
                    'conclusao' => 'Autorização e decisão administrativa registradas.',
                ],
                [
                    'titulo' => '3. Tratamento da devolução',
                    'concluido' => $devolucaoConcluida,
                    'estado' => $devolucaoConcluida
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
                    'titulo' => '4. Liberação e encerramento',
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
                <div>
                    Ocorrência #{{ $ocorrencia->id }} — {{ $ocorrencia->tipo }}
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

                    <div class="requisitos-lista">
                        @foreach ($requisitos as $requisito)
                            @php
                                $iconeRequisito = match ($requisito['estado']) {
                                    'concluido' => 'bi-check-circle-fill',
                                    'atual' => 'bi-arrow-right-circle-fill',
                                    default => 'bi-lock-fill',
                                };
                            @endphp
                            <div class="requisito-item {{ $requisito['estado'] }}">
                                <i class="requisito-icone bi {{ $iconeRequisito }}"></i>
                                <div>
                                    <div class="fw-bold">{{ $requisito['titulo'] }}</div>
                                    <small>
                                        {{ $requisito['concluido'] ? $requisito['conclusao'] : $requisito['pendencia'] }}
                                    </small>
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

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="resumo-label">Produto</div>
                        <div class="resumo-valor">
                            {{ $produto?->nome ?? ('Item #' . ($ocorrencia->entrega_item_id ?? '—')) }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Quantidade</div>
                        <div class="resumo-valor">
                            {{ number_format((float) $ocorrencia->quantidade_envolvida, 3, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Classificação inicial</div>
                        <div class="resumo-valor">
                            {{ $ocorrencia->classificacao_inicial ?? 'Não informada' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Classificação final</div>
                        <div class="resumo-valor">
                            {{ $ocorrencia->classificacao_final ?? 'Pendente' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Destino</div>
                        <div class="resumo-valor">
                            {{ str_replace('_', ' ', $ocorrencia->destino_estoque) }}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="resumo-label">Descrição</div>
                        <div>{{ $ocorrencia->descricao }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4">
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                1. Responsável e evidências
                            </div>

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
                                        @disabled($encerrada)
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
                                        @disabled($encerrada)
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
                                        <select name="tipo" class="form-select" required @disabled($encerrada)>
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
                                            @disabled($encerrada)
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
                                            @disabled($encerrada)
                                        >
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-outline-success" @disabled($encerrada)>
                                            <i class="bi bi-paperclip me-1"></i>
                                            Anexar evidência
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="d-grid gap-2">
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

                                        @if (! $encerrada)
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

                    <div class="col-xl-4">
                        <div class="etapa-box {{ ! $podeExecutarFase2 && ! $faseAutorizacaoDecisaoConcluida ? 'bloqueada' : '' }}">
                            <div class="etapa-titulo">
                                2. Autorização e decisão
                            </div>

                            @if (! $podeExecutarFase2 && ! $faseAutorizacaoDecisaoConcluida)
                                <div class="alert alert-secondary py-2">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Conclua a etapa 1 para liberar a autorização e a decisão.
                                </div>
                            @endif

                            @if ($ocorrencia->exige_autorizacao)
                                @if ($ocorrencia->autorizada_por)
                                    <div class="alert alert-success py-2">
                                        <strong>Autorizada.</strong>
                                        {{ $ocorrencia->justificativa_autorizacao }}
                                    </div>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('romaneios.ocorrencias.autorizar', [$romaneio, $ocorrencia]) }}"
                                        class="mb-3"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <label class="form-label">Justificativa da autorização</label>
                                        <textarea
                                            name="justificativa_autorizacao"
                                            class="form-control mb-2"
                                            rows="3"
                                            minlength="5"
                                            maxlength="2000"
                                            required
                                            @disabled(! $podeExecutarFase2)
                                        ></textarea>
                                        <button type="submit" class="btn btn-warning w-100" @disabled(! $podeExecutarFase2)>
                                            <i class="bi bi-shield-check me-1"></i>
                                            Autorizar ocorrência
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <form
                                method="POST"
                                action="{{ route('romaneios.ocorrencias.decisao', [$romaneio, $ocorrencia]) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Classificação final</label>
                                        <select name="classificacao_final" class="form-select" required @disabled(! $podeExecutarFase2)>
                                            <option value="">Selecione</option>
                                            @foreach (['Extravio', 'Avaria', 'Recusa', 'Devolucao', 'Perda', 'Divergencia', 'Improcedente', 'Outro'] as $classificacao)
                                                <option value="{{ $classificacao }}" @selected($ocorrencia->classificacao_final === $classificacao)>
                                                    {{ $classificacao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Destino do estoque</label>
                                        <select name="destino_estoque" class="form-select" required @disabled(! $podeExecutarFase2)>
                                            @foreach (['Sem_movimentacao', 'Quarentena', 'Reintegracao', 'Perda', 'Reposicao'] as $destino)
                                                <option value="{{ $destino }}" @selected($ocorrencia->destino_estoque === $destino)>
                                                    {{ str_replace('_', ' ', $destino) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Orçamento de reposição</label>
                                        <input
                                            type="number"
                                            name="orcamento_reposicao_id"
                                            class="form-control"
                                            min="1"
                                            value="{{ $ocorrencia->orcamento_reposicao_id }}"
                                            placeholder="ID do orçamento, quando aplicável"
                                            @disabled(! $podeExecutarFase2)
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Decisão administrativa</label>
                                        <textarea
                                            name="decisao"
                                            class="form-control"
                                            rows="4"
                                            minlength="5"
                                            maxlength="5000"
                                            required
                                            @disabled(! $podeExecutarFase2)
                                        >{{ $ocorrencia->decisao }}</textarea>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary" @disabled(! $podeExecutarFase2)>
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Registrar decisão
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-xl-4 etapas-coluna">
                        <div class="etapa-box {{ ! $podeExecutarFase3 && ! $devolucaoConcluida ? 'bloqueada' : '' }}">
                            <div class="etapa-titulo">
                                3. Tratamento da devolução
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
                                >
                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                    Abrir devolução #{{ $devolucao->id }}
                                </a>
                            @endif
                        </div>

                        <div class="etapa-box {{ ! $podeExecutarFase4 && ! $encerrada ? 'bloqueada' : '' }}">
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

                                <form
                                    method="POST"
                                    action="{{ route('romaneios.ocorrencias.cancelar', [$romaneio, $ocorrencia]) }}"
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

                @if ($ocorrencia->historicos->isNotEmpty())
                    <div class="mt-3">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="collapse"
                            data-bs-target="#historico-{{ $ocorrencia->id }}"
                        >
                            <i class="bi bi-clock-history me-1"></i>
                            Exibir histórico
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
    });
</script>
@endsection