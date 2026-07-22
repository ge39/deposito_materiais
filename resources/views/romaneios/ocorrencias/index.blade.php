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

    .ocorrencias-page .etapa-titulo {
        border-bottom: 1px solid #d9dee3;
        color: #26323d;
        font-weight: 700;
        margin-bottom: .85rem;
        padding-bottom: .55rem;
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
                <div class="small text-uppercase fw-bold">Fechamento liberado</div>
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
                            Fechamento liberado
                        </span>
                    @else
                        <span class="badge text-bg-danger">
                            Bloqueando fluxo
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body">
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
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                2. Autorização e decisão
                            </div>

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
                                            @disabled($encerrada)
                                        ></textarea>
                                        <button type="submit" class="btn btn-warning w-100" @disabled($encerrada)>
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
                                        <select name="classificacao_final" class="form-select" required @disabled($encerrada)>
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
                                        <select name="destino_estoque" class="form-select" required @disabled($encerrada)>
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
                                            @disabled($encerrada)
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
                                            @disabled($encerrada)
                                        >{{ $ocorrencia->decisao }}</textarea>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary" @disabled($encerrada)>
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Registrar decisão
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                3. Liberação e encerramento
                            </div>

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
                                    Esta ocorrência não impede mais o fechamento logístico.
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

                                    <label class="form-label">Solução aplicada</label>
                                    <textarea
                                        name="solucao"
                                        class="form-control mb-2"
                                        rows="3"
                                        minlength="5"
                                        maxlength="5000"
                                        required
                                    ></textarea>
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        Resolver ocorrência
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
                                    ></textarea>
                                    <div class="form-check border rounded p-3 ps-5 mb-2 bg-white">
                                        <input
                                            class="form-check-input confirmacao-acao"
                                            type="checkbox"
                                            id="cancelar-{{ $ocorrencia->id }}"
                                            data-target="btn-cancelar-{{ $ocorrencia->id }}"
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