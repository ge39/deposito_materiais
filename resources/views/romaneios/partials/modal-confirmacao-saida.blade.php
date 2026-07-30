@php
    $romaneiosLiberadosSaida = collect(
        $romaneiosLiberadosSaida ?? []
    )->values();

    $romaneiosPendentesSaida = collect(
        $romaneiosPendentesSaida ?? []
    )->values();

    $romaneiosMotoristaDivergente = collect(
        $romaneiosMotoristaDivergente ?? []
    )->values();

    $possuiRomaneiosLiberados =
        $romaneiosLiberadosSaida->isNotEmpty();

    $possuiRomaneiosPendentes =
        $romaneiosPendentesSaida->isNotEmpty();

    $possuiMotoristaDivergente =
        $romaneiosMotoristaDivergente->isNotEmpty();

    $romaneiosBloqueadores =
        $romaneiosPendentesSaida
            ->concat($romaneiosMotoristaDivergente)
            ->unique('id')
            ->values();

    $possuiBloqueioSaida =
        $romaneiosBloqueadores->isNotEmpty();

    $quantidadeBloqueios =
        $romaneiosBloqueadores->count();

    $podeRegistrarSaida =
        $possuiRomaneiosLiberados
        && ! $possuiBloqueioSaida;

    $motoristaSaida =
        $romaneioAtivo?->motorista?->nome
        ?? 'Motorista não identificado';

    $veiculoSaida =
        $romaneioAtivo?->veiculo?->placa
        ?? 'Veículo não identificado';
@endphp

<style>
    #modalConfirmacaoSaida {
        --confirmacao-pendente-bg: #fff8e1;
        --confirmacao-pendente-border: #f0ad00;
        --confirmacao-concluida-bg: #eaf7ef;
        --confirmacao-concluida-border: #198754;
        --bloqueio-bg: #fff5f5;
        --bloqueio-border: #dc3545;
    }

    #modalConfirmacaoSaida .modal-header {
        padding: 14px 18px;
    }

    #modalConfirmacaoSaida .modal-body {
        padding: 16px;
    }

    #modalConfirmacaoSaida .resumo-viagem {
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 8px;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin-bottom: 16px;
        padding: 10px 14px;
    }

    #modalConfirmacaoSaida .resumo-viagem-item {
        flex: 1;
        min-width: 0;
    }

    #modalConfirmacaoSaida .resumo-viagem-label {
        color: #6c757d;
        display: block;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    #modalConfirmacaoSaida .resumo-viagem-valor {
        display: block;
        font-size: .92rem;
        font-weight: 750;
    }

    #modalConfirmacaoSaida .bloqueio-principal {
        background: var(--bloqueio-bg);
        border: 1px solid #f1aeb5;
        border-left: 5px solid var(--bloqueio-border);
        border-radius: 8px;
        margin-bottom: 14px;
        padding: 12px 14px;
    }

    #modalConfirmacaoSaida .bloqueio-principal-titulo {
        color: #842029;
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    #modalConfirmacaoSaida .bloqueio-principal-texto {
        color: #58151c;
        font-size: .84rem;
        line-height: 1.4;
        margin: 0;
    }

    #modalConfirmacaoSaida .bloqueios-lista {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    #modalConfirmacaoSaida .bloqueio-item {
        background: #fff;
        border: 1px solid #dee2e6;
        border-left: 4px solid #dc3545;
        border-radius: 8px;
        padding: 12px;
    }

    #modalConfirmacaoSaida .bloqueio-item-grid {
        align-items: flex-start;
        display: grid;
        gap: 12px;
        grid-template-columns: 1.1fr 1fr 1.5fr auto;
    }

    #modalConfirmacaoSaida .bloqueio-label {
        color: #6c757d;
        display: block;
        font-size: .67rem;
        font-weight: 800;
        letter-spacing: .03em;
        margin-bottom: 3px;
        text-transform: uppercase;
    }

    #modalConfirmacaoSaida .bloqueio-valor {
        display: block;
        font-size: .84rem;
        font-weight: 700;
        line-height: 1.3;
    }

    #modalConfirmacaoSaida .bloqueio-motivos {
        font-size: .79rem;
        margin: 5px 0 0;
        padding-left: 18px;
    }

    #modalConfirmacaoSaida .bloqueio-motivos li {
        margin-bottom: 3px;
    }

    #modalConfirmacaoSaida .bloqueio-acoes {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 145px;
    }

    #modalConfirmacaoSaida .link-romaneio {
        color: #0d6efd;
        font-weight: 800;
        text-decoration: none;
    }

    #modalConfirmacaoSaida .link-romaneio:hover {
        text-decoration: underline;
    }

    #modalConfirmacaoSaida .coluna-confirmacao {
        background: #fff8e1;
        min-width: 132px;
        text-align: center;
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box {
        align-items: center;
        background: var(--confirmacao-pendente-bg);
        border: 2px solid var(--confirmacao-pendente-border);
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: center;
        min-height: 76px;
        padding: 8px;
        transition:
            background-color .2s,
            border-color .2s,
            box-shadow .2s;
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box:hover {
        box-shadow:
            0 0 0 3px rgba(240, 173, 0, .18);
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box.confirmado {
        background: var(--confirmacao-concluida-bg);
        border-color: var(--confirmacao-concluida-border);
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box.confirmado:hover {
        box-shadow:
            0 0 0 3px rgba(25, 135, 84, .16);
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box .form-check-input,
    #modalConfirmacaoSaida .confirmacao-geral-box .form-check-input {
        cursor: pointer;
        flex: 0 0 auto;
        height: 24px;
        margin: 0;
        width: 24px;
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-texto {
        color: #7a5700;
        font-size: .73rem;
        font-weight: 800;
        line-height: 1.15;
        text-transform: uppercase;
    }

    #modalConfirmacaoSaida
    .confirmacao-romaneio-box.confirmado
    .confirmacao-romaneio-texto {
        color: #146c43;
    }

    #modalConfirmacaoSaida .confirmacao-geral-box {
        align-items: flex-start;
        background: var(--confirmacao-pendente-bg);
        border: 2px solid var(--confirmacao-pendente-border);
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        gap: 12px;
        padding: 12px 14px;
        transition:
            background-color .2s,
            border-color .2s,
            box-shadow .2s;
    }

    #modalConfirmacaoSaida .confirmacao-geral-box:hover {
        box-shadow:
            0 0 0 3px rgba(240, 173, 0, .18);
    }

    #modalConfirmacaoSaida .confirmacao-geral-box.confirmado {
        background: var(--confirmacao-concluida-bg);
        border-color: var(--confirmacao-concluida-border);
    }

    #modalConfirmacaoSaida .confirmacao-geral-conteudo {
        flex: 1;
    }

    #modalConfirmacaoSaida .confirmacao-obrigatoria {
        display: inline-block;
        font-size: .68rem;
        letter-spacing: .03em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    #modalConfirmacaoSaida .confirmacao-geral-texto {
        color: #5f4700;
        display: block;
        font-size: .84rem;
        font-weight: 750;
        line-height: 1.35;
    }

    #modalConfirmacaoSaida
    .confirmacao-geral-box.confirmado
    .confirmacao-geral-texto {
        color: #146c43;
    }

    #modalConfirmacaoSaida .confirmacao-status {
        font-size: .75rem;
        margin-top: 5px;
    }

    #modalConfirmacaoSaida #btnConfirmarSaida:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    @media (max-width: 991.98px) {
        #modalConfirmacaoSaida .bloqueio-item-grid {
            grid-template-columns: 1fr 1fr;
        }

        #modalConfirmacaoSaida .bloqueio-acoes {
            grid-column: 1 / -1;
            min-width: 0;
        }
    }

    @media (max-width: 767.98px) {
        #modalConfirmacaoSaida .resumo-viagem {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        #modalConfirmacaoSaida .bloqueio-item-grid {
            grid-template-columns: 1fr;
        }

        #modalConfirmacaoSaida .bloqueio-acoes {
            grid-column: auto;
        }
    }
</style>

<div
    class="modal fade"
    id="modalConfirmacaoSaida"
    tabindex="-1"
    aria-labelledby="modalConfirmacaoSaidaLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <div>
                    <h5
                        class="modal-title"
                        id="modalConfirmacaoSaidaLabel"
                    >
                        @if($possuiBloqueioSaida)
                            <i class="bi bi-exclamation-octagon me-2"></i>
                            Saída bloqueada
                        @else
                            <i class="bi bi-truck me-2"></i>
                            Conferência documental da saída
                        @endif
                    </h5>

                    <div class="small text-white-50 mt-1">
                        @if($possuiBloqueioSaida)
                            Corrija os bloqueios abaixo antes de liberar
                            a viagem.
                        @else
                            Confirme os documentos entregues ao motorista
                            antes de registrar a saída física.
                        @endif
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>

            <div class="modal-body">

                <div class="resumo-viagem">
                    <div class="resumo-viagem-item">
                        <span class="resumo-viagem-label">
                            Motorista
                        </span>

                        <span class="resumo-viagem-valor">
                            <i class="bi bi-person me-1"></i>
                            {{ $motoristaSaida }}
                        </span>
                    </div>

                    <div class="resumo-viagem-item">
                        <span class="resumo-viagem-label">
                            Veículo
                        </span>

                        <span class="resumo-viagem-valor">
                            <i class="bi bi-truck-front me-1"></i>
                            {{ $veiculoSaida }}
                        </span>
                    </div>
                </div>

                @if($possuiBloqueioSaida)
                    <div class="bloqueio-principal">
                        <div class="bloqueio-principal-titulo">
                            <i class="bi bi-slash-circle me-1"></i>

                            A viagem não pode ser liberada
                        </div>

                        <p class="bloqueio-principal-texto">
                            {{
                                $quantidadeBloqueios === 1
                                    ? 'Existe 1 romaneio impedindo a saída deste caminhão.'
                                    : "Existem {$quantidadeBloqueios} romaneios impedindo a saída deste caminhão."
                            }}

                            Abra o romaneio indicado e conclua a ação
                            necessária.
                        </p>
                    </div>

                    <div class="bloqueios-lista">
                        @foreach(
                            $romaneiosBloqueadores
                            as $romaneioBloqueador
                        )
                            @php
                                $entregaBloqueada =
                                    $romaneioBloqueador->entrega;

                                $clienteBloqueado =
                                    $entregaBloqueada?->cliente
                                    ?? $entregaBloqueada?->venda?->cliente
                                    ?? $entregaBloqueada?->orcamento?->cliente;

                                $nomeClienteBloqueado =
                                    $clienteBloqueado?->nome
                                    ?? $clienteBloqueado?->razao_social
                                    ?? 'Cliente não identificado';

                                $statusBloqueadorOriginal =
                                    (string) $romaneioBloqueador->status;

                                $statusBloqueador =
                                    str_replace(
                                        '_',
                                        ' ',
                                        $statusBloqueadorOriginal
                                    );

                                $statusBloqueadorNormalizado =
                                    strtolower(
                                        trim(
                                            str_replace(
                                                ' ',
                                                '_',
                                                $statusBloqueadorOriginal
                                            )
                                        )
                                    );

                                $aguardandoOcorrencia =
                                    str_contains(
                                        $statusBloqueadorNormalizado,
                                        'ocorrencia'
                                    );

                                $bloqueadoPorOperacao =
                                    $romaneiosPendentesSaida->contains(
                                        fn ($item) =>
                                            (int) $item->id
                                            === (int) $romaneioBloqueador->id
                                    );

                                $bloqueadoPorMotorista =
                                    $romaneiosMotoristaDivergente->contains(
                                        fn ($item) =>
                                            (int) $item->id
                                            === (int) $romaneioBloqueador->id
                                    );
                            @endphp

                            <div class="bloqueio-item">
                                <div class="bloqueio-item-grid">

                                    <div>
                                        <span class="bloqueio-label">
                                            Romaneio
                                        </span>

                                        <a
                                            href="{{
                                                route(
                                                    'romaneios.show',
                                                    $romaneioBloqueador
                                                )
                                            }}"
                                            class="link-romaneio"
                                            target="_blank"
                                            rel="noopener"
                                            title="Abrir detalhes do romaneio"
                                        >
                                            {{
                                                $romaneioBloqueador
                                                    ->codigo_romaneio
                                            }}
                                        </a>

                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark">
                                                {{ $statusBloqueador }}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="bloqueio-label">
                                            Entrega e cliente
                                        </span>

                                        <span class="bloqueio-valor">
                                            {{
                                                $entregaBloqueada
                                                    ?->codigo_entrega
                                                ?? '#'
                                                    . $romaneioBloqueador
                                                        ->entrega_id
                                            }}
                                        </span>

                                        <span class="small text-muted">
                                            {{ $nomeClienteBloqueado }}
                                        </span>
                                    </div>

                                    <div>
                                        <span class="bloqueio-label">
                                            Motivo e ação necessária
                                        </span>

                                        <ul class="bloqueio-motivos">
                                            @if(
                                                $bloqueadoPorOperacao
                                                && $aguardandoOcorrencia
                                            )
                                                <li>
                                                    Existe uma ocorrência
                                                    aguardando tratativa.
                                                </li>

                                                <li>
                                                    Resolva a ocorrência para
                                                    continuar a liberação.
                                                </li>
                                            @elseif($bloqueadoPorOperacao)
                                                <li>
                                                    O romaneio ainda não está
                                                    liberado para a viagem.
                                                </li>

                                                <li>
                                                    Conclua as etapas
                                                    operacionais pendentes.
                                                </li>
                                            @endif

                                            @if($bloqueadoPorMotorista)
                                                <li>
                                                    O motorista vinculado é
                                                    diferente do motorista
                                                    desta viagem.
                                                </li>

                                                <li>
                                                    Corrija a vinculação antes
                                                    da saída.
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <div class="bloqueio-acoes">
                                        <a
                                            href="{{
                                                route(
                                                    'romaneios.show',
                                                    $romaneioBloqueador
                                                )
                                            }}"
                                            class="btn btn-outline-dark btn-sm"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <i class="bi bi-box-arrow-up-right me-1"></i>
                                            Abrir romaneio
                                        </a>

                                        @if($aguardandoOcorrencia)
                                            <a
                                                href="{{
                                                    route(
                                                        'romaneios.ocorrencias.index',
                                                        $romaneioBloqueador
                                                    )
                                                }}"
                                                class="btn btn-danger btn-sm"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Abrir ocorrência
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card border-success">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Romaneios e documentos da viagem
                        </div>

                        @if($possuiRomaneiosLiberados)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th
                                                class="coluna-confirmacao"
                                                style="width: 12%;"
                                            >
                                                Confirmação
                                            </th>

                                            <th>Romaneio</th>
                                            <th>Entrega</th>
                                            <th>Cliente</th>
                                            <th>Motorista</th>
                                            <th>Veículo</th>

                                            <th class="text-center">
                                                Impressão
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach(
                                            $romaneiosLiberadosSaida
                                            as $romaneioLiberado
                                        )
                                            @php
                                                $entregaLiberada =
                                                    $romaneioLiberado->entrega;

                                                $clienteLiberado =
                                                    $entregaLiberada?->cliente
                                                    ?? $entregaLiberada
                                                        ?->venda
                                                        ?->cliente
                                                    ?? $entregaLiberada
                                                        ?->orcamento
                                                        ?->cliente;

                                                $nomeClienteLiberado =
                                                    $clienteLiberado?->nome
                                                    ?? $clienteLiberado
                                                        ?->razao_social
                                                    ?? 'Cliente não identificado';

                                                $documentoImpresso =
                                                    ! empty(
                                                        $romaneioLiberado
                                                            ->impresso_em
                                                    );

                                                $motoristaCompativel =
                                                    (int)
                                                        $romaneioLiberado
                                                            ->motorista_id
                                                    === (int)
                                                        $romaneioAtivo
                                                            ?->motorista_id;
                                            @endphp

                                            <tr
                                                class="js-linha-romaneio {{
                                                    ! $documentoImpresso
                                                    || ! $motoristaCompativel
                                                        ? 'table-danger'
                                                        : ''
                                                }}"
                                            >
                                                <td class="coluna-confirmacao">
                                                    <label class="confirmacao-romaneio-box">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input js-romaneio-confirmado"
                                                            name="romaneios_confirmados[]"
                                                            value="{{
                                                                $romaneioLiberado
                                                                    ->id
                                                            }}"
                                                            form="formRomaneio"
                                                            @checked(
                                                                in_array(
                                                                    (int)
                                                                        $romaneioLiberado
                                                                            ->id,
                                                                    array_map(
                                                                        'intval',
                                                                        old(
                                                                            'romaneios_confirmados',
                                                                            []
                                                                        )
                                                                    ),
                                                                    true
                                                                )
                                                            )
                                                            @disabled(
                                                                ! $documentoImpresso
                                                                || ! $motoristaCompativel
                                                            )
                                                        >

                                                        <span class="confirmacao-romaneio-texto">
                                                            Confirmar documento
                                                        </span>

                                                        <span class="badge bg-warning text-dark confirmacao-romaneio-badge">
                                                            Obrigatório
                                                        </span>
                                                    </label>
                                                </td>

                                                <td class="fw-bold">
                                                    <a
                                                        href="{{
                                                            route(
                                                                'romaneios.show',
                                                                $romaneioLiberado
                                                            )
                                                        }}"
                                                        class="link-romaneio"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="Abrir detalhes do romaneio"
                                                    >
                                                        {{
                                                            $romaneioLiberado
                                                                ->codigo_romaneio
                                                        }}
                                                    </a>
                                                </td>

                                                <td>
                                                    {{
                                                        $entregaLiberada
                                                            ?->codigo_entrega
                                                        ?? '#'
                                                            . $romaneioLiberado
                                                                ->entrega_id
                                                    }}
                                                </td>

                                                <td>
                                                    {{ $nomeClienteLiberado }}
                                                </td>

                                                <td>
                                                    {{
                                                        $romaneioLiberado
                                                            ->motorista
                                                            ?->nome
                                                        ?? 'Não identificado'
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        $romaneioLiberado
                                                            ->veiculo
                                                            ?->placa
                                                        ?? 'Não identificado'
                                                    }}
                                                </td>

                                                <td class="text-center">
                                                    <div class="d-flex flex-column gap-1 align-items-stretch">
                                                        @if($documentoImpresso)
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>
                                                                Romaneio impresso
                                                            </span>

                                                            <a
                                                                href="{{
                                                                    route(
                                                                        'romaneios.imprimir',
                                                                        $romaneioLiberado
                                                                    )
                                                                }}"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                target="_blank"
                                                                rel="noopener"
                                                            >
                                                                <i class="bi bi-printer me-1"></i>
                                                                Reimprimir romaneio
                                                            </a>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle me-1"></i>
                                                                Romaneio não impresso
                                                            </span>

                                                            <form
                                                                method="POST"
                                                                action="{{
                                                                    route(
                                                                        'romaneios.registrar-impressao',
                                                                        $romaneioLiberado
                                                                    )
                                                                }}"
                                                                target="_blank"
                                                                class="m-0"
                                                                onsubmit="setTimeout(() => window.location.reload(), 1200)"
                                                            >
                                                                @csrf

                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-outline-dark btn-sm w-100"
                                                                >
                                                                    <i class="bi bi-printer me-1"></i>
                                                                    Imprimir romaneio
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <a
                                                            href="{{
                                                                route(
                                                                    'romaneios.nota-entrega',
                                                                    $romaneioLiberado
                                                                )
                                                            }}"
                                                            class="btn btn-outline-success btn-sm"
                                                            target="_blank"
                                                            rel="noopener"
                                                        >
                                                            <i class="bi bi-file-earmark-check me-1"></i>
                                                            Imprimir nota de entrega
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-body border-top">
                                <label
                                    class="confirmacao-geral-box"
                                    for="confirmacaoDocumentosMotorista"
                                >
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="confirmacaoDocumentosMotorista"
                                        form="formRomaneio"
                                        @disabled(! $podeRegistrarSaida)
                                    >

                                    <span class="confirmacao-geral-conteudo">
                                        <span class="badge bg-warning text-dark confirmacao-obrigatoria">
                                            Confirmação obrigatória
                                        </span>

                                        <span class="confirmacao-geral-texto">
                                            Confirmo que os documentos
                                            marcados foram entregues ao
                                            motorista e pertencem às entregas
                                            carregadas neste caminhão.
                                        </span>

                                        <span class="confirmacao-status text-warning-emphasis">
                                            Marque esta opção para liberar o
                                            registro da saída.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @else
                            <div class="card-body text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                Nenhum romaneio liberado foi encontrado para
                                este caminhão.
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Fechar
                </button>

                @if($possuiBloqueioSaida)
                    <button
                        type="button"
                        class="btn btn-danger"
                        disabled
                    >
                        <i class="bi bi-slash-circle me-1"></i>
                        Saída bloqueada
                    </button>
                @elseif($podeRegistrarSaida)
                    <button
                        type="submit"
                        name="acao"
                        value="registrar_saida"
                        class="btn btn-dark"
                        id="btnConfirmarSaida"
                        form="formRomaneio"
                        disabled
                    >
                        <i class="bi bi-truck me-1"></i>
                        Confirmar documentos e registrar saída
                    </button>
                @else
                    <button
                        type="button"
                        class="btn btn-secondary"
                        disabled
                    >
                        <i class="bi bi-inbox me-1"></i>
                        Nenhum romaneio liberado
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const modal =
            document.getElementById(
                'modalConfirmacaoSaida'
            );

        if (! modal) {
            return;
        }

        const confirmacaoGeral =
            document.getElementById(
                'confirmacaoDocumentosMotorista'
            );

        const botaoConfirmar =
            document.getElementById(
                'btnConfirmarSaida'
            );

        const checkboxes = Array.from(
            modal.querySelectorAll(
                '.js-romaneio-confirmado:not(:disabled)'
            )
        );

        const atualizarBotao = function () {
            const todosMarcados =
                checkboxes.length > 0
                && checkboxes.every(
                    checkbox =>
                        checkbox.checked
                );

            const confirmacaoAceita =
                confirmacaoGeral
                && confirmacaoGeral.checked;

            checkboxes.forEach(function (checkbox) {
                const caixa =
                    checkbox.closest(
                        '.confirmacao-romaneio-box'
                    );

                const badge =
                    caixa?.querySelector(
                        '.confirmacao-romaneio-badge'
                    );

                caixa?.classList.toggle(
                    'confirmado',
                    checkbox.checked
                );

                if (badge) {
                    badge.className =
                        checkbox.checked
                            ? 'badge bg-success confirmacao-romaneio-badge'
                            : 'badge bg-warning text-dark confirmacao-romaneio-badge';

                    badge.textContent =
                        checkbox.checked
                            ? 'Documento confirmado'
                            : 'Obrigatório';
                }
            });

            const confirmacaoGeralBox =
                confirmacaoGeral?.closest(
                    '.confirmacao-geral-box'
                );

            const confirmacaoGeralBadge =
                confirmacaoGeralBox?.querySelector(
                    '.confirmacao-obrigatoria'
                );

            const confirmacaoStatus =
                confirmacaoGeralBox?.querySelector(
                    '.confirmacao-status'
                );

            confirmacaoGeralBox?.classList.toggle(
                'confirmado',
                Boolean(confirmacaoAceita)
            );

            if (confirmacaoGeralBadge) {
                confirmacaoGeralBadge.className =
                    confirmacaoAceita
                        ? 'badge bg-success confirmacao-obrigatoria'
                        : 'badge bg-warning text-dark confirmacao-obrigatoria';

                confirmacaoGeralBadge.textContent =
                    confirmacaoAceita
                        ? 'Confirmação concluída'
                        : 'Confirmação obrigatória';
            }

            if (confirmacaoStatus) {
                confirmacaoStatus.className =
                    confirmacaoAceita
                        ? 'confirmacao-status text-success'
                        : 'confirmacao-status text-warning-emphasis';

                confirmacaoStatus.textContent =
                    confirmacaoAceita
                        ? 'Documentos confirmados para o motorista.'
                        : 'Marque esta opção para liberar o registro da saída.';
            }

            if (! botaoConfirmar) {
                return;
            }

            botaoConfirmar.disabled =
                ! todosMarcados
                || ! confirmacaoAceita;
        };

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener(
                'change',
                atualizarBotao
            );
        });

        confirmacaoGeral?.addEventListener(
            'change',
            atualizarBotao
        );

        modal.addEventListener(
            'shown.bs.modal',
            atualizarBotao
        );

        atualizarBotao();
    }
);
</script>