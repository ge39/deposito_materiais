@php
    $romaneiosLiberadosSaida = collect(
        $romaneiosLiberadosSaida
        ?? []
    )->values();

    $romaneiosPendentesSaida = collect(
        $romaneiosPendentesSaida
        ?? []
    )->values();

    $romaneiosMotoristaDivergente = collect(
        $romaneiosMotoristaDivergente
        ?? []
    )->values();

    $possuiRomaneiosPendentes =
        $romaneiosPendentesSaida
            ->isNotEmpty();

    $possuiMotoristaDivergente =
        $romaneiosMotoristaDivergente
            ->isNotEmpty();

    $possuiRomaneiosLiberados =
        $romaneiosLiberadosSaida
            ->isNotEmpty();

    $podeRegistrarSaida =
        $possuiRomaneiosLiberados
        && ! $possuiRomaneiosPendentes
        && ! $possuiMotoristaDivergente;

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
        transition: background-color .2s, border-color .2s, box-shadow .2s;
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box:hover {
        box-shadow: 0 0 0 3px rgba(240, 173, 0, .18);
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box.confirmado {
        background: var(--confirmacao-concluida-bg);
        border-color: var(--confirmacao-concluida-border);
    }

    #modalConfirmacaoSaida .confirmacao-romaneio-box.confirmado:hover {
        box-shadow: 0 0 0 3px rgba(25, 135, 84, .16);
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

    #modalConfirmacaoSaida .confirmacao-romaneio-box.confirmado
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
        transition: background-color .2s, border-color .2s, box-shadow .2s;
    }

    #modalConfirmacaoSaida .confirmacao-geral-box:hover {
        box-shadow: 0 0 0 3px rgba(240, 173, 0, .18);
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

    #modalConfirmacaoSaida .confirmacao-geral-box.confirmado
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
                        <i class="bi bi-truck me-2"></i>
                        Conferência documental da saída
                    </h5>

                    <div class="small text-white-50 mt-1">
                        Confirme os romaneios entregues ao motorista
                        antes de registrar a saída física.
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

                <div class="card border-secondary mb-3">
                    <div class="card-header bg-secondary text-white fw-bold">
                        <i class="bi bi-person-vcard me-1"></i>
                        Equipe responsável pela viagem
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <span class="d-block small text-muted fw-bold text-uppercase">
                                    Motorista
                                </span>

                                <div class="fs-6 fw-bold">
                                    <i class="bi bi-person me-1"></i>
                                    {{ $motoristaSaida }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <span class="d-block small text-muted fw-bold text-uppercase">
                                    Veículo
                                </span>

                                <div class="fs-6 fw-bold">
                                    <i class="bi bi-truck-front me-1"></i>
                                    {{ $veiculoSaida }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                @if($possuiRomaneiosPendentes)
                    <div class="alert alert-warning">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-hourglass-split me-1"></i>
                            O caminhão possui cargas ainda não liberadas
                        </div>

                        <div class="small">
                            A saída não poderá ser registrada enquanto
                            existirem romaneios em preparação vinculados
                            a este veículo.
                        </div>
                    </div>

                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning text-dark fw-bold">
                            <i class="bi bi-clock-history me-1"></i>
                            Romaneios que o caminhão deverá aguardar
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Romaneio</th>
                                        <th>Entrega</th>
                                        <th>Cliente</th>
                                        <th>Status atual</th>
                                        <th class="text-center">
                                            Situação
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(
                                        $romaneiosPendentesSaida
                                        as $romaneioPendente
                                    )
                                        @php
                                            $entregaPendente =
                                                $romaneioPendente
                                                    ->entrega;

                                            $clientePendente =
                                                $entregaPendente?->cliente
                                                ?? $entregaPendente
                                                    ?->venda
                                                    ?->cliente
                                                ?? $entregaPendente
                                                    ?->orcamento
                                                    ?->cliente;

                                            $nomeClientePendente =
                                                $clientePendente?->nome
                                                ?? $clientePendente
                                                    ?->razao_social
                                                ?? 'Cliente não identificado';

                                            $statusPendente =
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    (string)
                                                        $romaneioPendente
                                                            ->status
                                                );
                                        @endphp

                                        <tr>
                                            <td class="fw-bold">
                                                {{
                                                    $romaneioPendente
                                                        ->codigo_romaneio
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $entregaPendente
                                                        ?->codigo_entrega
                                                    ?? '#'
                                                        . $romaneioPendente
                                                            ->entrega_id
                                                }}
                                            </td>

                                            <td>
                                                {{ $nomeClientePendente }}
                                            </td>

                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $statusPendente }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-secondary">
                                                    Aguardar
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($possuiMotoristaDivergente)
                    <div class="alert alert-danger">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-person-exclamation me-1"></i>
                            Existem romaneios vinculados a outro motorista
                        </div>

                        <div class="small">
                            Todos os romaneios do caminhão precisam estar
                            vinculados ao mesmo motorista antes da saída.
                        </div>
                    </div>

                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white fw-bold">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Divergências de motorista
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Romaneio</th>
                                        <th>Entrega</th>
                                        <th>Motorista vinculado</th>
                                        <th>Veículo</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(
                                        $romaneiosMotoristaDivergente
                                        as $romaneioDivergente
                                    )
                                        <tr>
                                            <td class="fw-bold">
                                                {{
                                                    $romaneioDivergente
                                                        ->codigo_romaneio
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $romaneioDivergente
                                                        ->entrega
                                                        ?->codigo_entrega
                                                    ?? '#'
                                                        . $romaneioDivergente
                                                            ->entrega_id
                                                }}
                                            </td>

                                            <td class="text-danger fw-bold">
                                                {{
                                                    $romaneioDivergente
                                                        ->motorista
                                                        ?->nome
                                                    ?? 'Não identificado'
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    $romaneioDivergente
                                                        ->veiculo
                                                        ?->placa
                                                    ?? 'Não identificado'
                                                }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="card border-success">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-file-earmark-check me-1"></i>
                        Romaneios liberados e documentos da viagem
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
                                                $romaneioLiberado
                                                    ->entrega;

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

                                        <tr class="js-linha-romaneio {{
                                            ! $documentoImpresso
                                            || ! $motoristaCompativel
                                                ? 'table-danger'
                                                : ''
                                        }}">
                                            <td class="coluna-confirmacao">
                                                <label class="confirmacao-romaneio-box">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input js-romaneio-confirmado"
                                                        name="romaneios_confirmados[]"
                                                        value="{{
                                                            $romaneioLiberado->id
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
                                                            || $possuiRomaneiosPendentes
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
                                                {{
                                                    $romaneioLiberado
                                                        ->codigo_romaneio
                                                }}
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
                                        Confirmo que os documentos marcados foram
                                        entregues ao motorista e pertencem às
                                        entregas carregadas neste caminhão.
                                    </span>

                                    <span class="confirmacao-status text-warning-emphasis">
                                        Marque esta opção para liberar o registro da saída.
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

                @if($possuiRomaneiosPendentes)
                    <button
                        type="button"
                        class="btn btn-warning"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-hourglass-split me-1"></i>
                        Aguardar carregamentos
                    </button>
                @else
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
                const caixa = checkbox.closest(
                    '.confirmacao-romaneio-box'
                );

                const badge = caixa?.querySelector(
                    '.confirmacao-romaneio-badge'
                );

                caixa?.classList.toggle(
                    'confirmado',
                    checkbox.checked
                );

                if (badge) {
                    badge.className = checkbox.checked
                        ? 'badge bg-success confirmacao-romaneio-badge'
                        : 'badge bg-warning text-dark confirmacao-romaneio-badge';

                    badge.textContent = checkbox.checked
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
                confirmacaoGeralBadge.className = confirmacaoAceita
                    ? 'badge bg-success confirmacao-obrigatoria'
                    : 'badge bg-warning text-dark confirmacao-obrigatoria';

                confirmacaoGeralBadge.textContent = confirmacaoAceita
                    ? 'Confirmação concluída'
                    : 'Confirmação obrigatória';
            }

            if (confirmacaoStatus) {
                confirmacaoStatus.className = confirmacaoAceita
                    ? 'confirmacao-status text-success'
                    : 'confirmacao-status text-warning-emphasis';

                confirmacaoStatus.textContent = confirmacaoAceita
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