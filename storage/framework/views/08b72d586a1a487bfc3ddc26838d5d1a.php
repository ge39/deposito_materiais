<style>
    .entrega-sla-summary {
        width: 100%;
        margin: 0 auto 1rem;
        border: 1px solid #efc55e;
        border-left: 5px solid #f0ad00;
        border-radius: 8px;
        background: #fffaf0;
        box-shadow: 0 2px 8px rgba(33, 37, 41, .08);
    }

    .entrega-sla-summary[hidden],
    .entrega-sla-overlay[hidden] {
        display: none !important;
    }

    .entrega-sla-summary-row {
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: .65rem;
        padding: .55rem .8rem;
        text-align: center;
    }

    .entrega-sla-summary-title {
        color: #6d5000;
        font-size: .82rem;
        font-weight: 800;
    }

    .entrega-sla-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        color: #fff;
        font-size: .69rem;
        font-weight: 800;
        line-height: 1;
        padding: .34rem .58rem;
    }

    .entrega-sla-badge.is-warning {
        background: #b58100;
    }

    .entrega-sla-badge.is-critical {
        background: #dc3545;
    }

    .entrega-sla-summary-button {
        border: 1px solid #8a6500;
        border-radius: 6px;
        background: #fff;
        color: #6d5000;
        font-size: .72rem;
        font-weight: 800;
        padding: .3rem .65rem;
    }

    .entrega-sla-summary-button:hover {
        background: #6d5000;
        color: #fff;
    }

    .entrega-sla-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: grid;
        place-items: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .48);
    }

    .entrega-sla-dialog {
        width: min(540px, 100%);
        max-height: min(520px, calc(100vh - 2rem));
        display: flex;
        flex-direction: column;
        border: 1px solid #dc3545;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .3);
        overflow: hidden;
    }

    .entrega-sla-dialog-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        background: #dc3545;
        color: #fff;
        padding: .72rem .9rem;
    }

    .entrega-sla-dialog-title {
        margin: 0;
        font-size: .9rem;
        font-weight: 800;
    }

    .entrega-sla-close {
        width: 28px;
        height: 28px;
        display: inline-grid;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, .16);
        color: #fff;
        font-size: 1.1rem;
    }

    .entrega-sla-dialog-intro {
        margin: 0;
        border-bottom: 1px solid #e9ecef;
        color: #475467;
        font-size: .79rem;
        padding: .72rem .9rem;
    }

    .entrega-sla-list {
        display: grid;
        gap: .42rem;
        overflow-y: auto;
        padding: .7rem;
    }

    .entrega-sla-item {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) minmax(130px, .8fr) auto auto;
        align-items: center;
        gap: .55rem;
        border: 1px solid #e6e8ec;
        border-left: 4px solid #f0ad00;
        border-radius: 7px;
        background: #fff;
        padding: .48rem .6rem;
    }

    .entrega-sla-item.is-critical {
        border-color: #f1aeb5;
        border-left-color: #dc3545;
        background: #fff7f7;
    }

    .entrega-sla-code,
    .entrega-sla-time {
        font-size: .73rem;
        font-weight: 800;
    }

    .entrega-sla-status {
        color: #667085;
        font-size: .72rem;
    }

    .entrega-sla-time {
        color: #9a6700;
        white-space: nowrap;
    }

    .entrega-sla-item.is-critical .entrega-sla-time {
        color: #b42318;
    }

    tr.entrega-sla-warning > td {
        box-shadow: inset 4px 0 #f0ad00;
    }

    tr.entrega-sla-critical > td {
        background-color: #fff3f3 !important;
        box-shadow: none;
    }

    body.entrega-sla-modal-open {
        overflow: hidden;
    }

    @media (max-width: 700px) {
        .entrega-sla-item {
            grid-template-columns: 1fr auto;
        }

        .entrega-sla-status {
            grid-column: 1 / -1;
        }
    }
</style>

<section id="entrega-sla-summary"
         class="entrega-sla-summary"
         aria-live="polite"
         hidden>
    <div class="entrega-sla-summary-row">
        <span class="entrega-sla-summary-title">
            <i class="bi bi-alarm me-1"></i>
            Permanência no status
        </span>

        <span id="entrega-sla-warning-count"
              class="entrega-sla-badge is-warning"></span>

        <span id="entrega-sla-critical-count"
              class="entrega-sla-badge is-critical"></span>

        <button type="button"
                id="entrega-sla-open"
                class="entrega-sla-summary-button">
            <i class="bi bi-eye me-1"></i>Ver alertas
        </button>
    </div>
</section>

<div id="entrega-sla-overlay"
     class="entrega-sla-overlay"
     role="dialog"
     aria-modal="true"
     aria-labelledby="entrega-sla-dialog-title"
     hidden>
    <div class="entrega-sla-dialog">
        <header class="entrega-sla-dialog-header">
            <h2 id="entrega-sla-dialog-title"
                class="entrega-sla-dialog-title">
                <i class="bi bi-exclamation-octagon me-1"></i>
                Alertas operacionais
            </h2>

            <button type="button"
                    id="entrega-sla-close"
                    class="entrega-sla-close"
                    aria-label="Fechar">&times;</button>
        </header>

        <p id="entrega-sla-dialog-intro"
           class="entrega-sla-dialog-intro"></p>

        <div id="entrega-sla-list"
             class="entrega-sla-list"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resumo = document.getElementById('entrega-sla-summary');
        const avisosTotal = document.getElementById(
            'entrega-sla-warning-count'
        );
        const criticosTotal = document.getElementById(
            'entrega-sla-critical-count'
        );
        const abrir = document.getElementById('entrega-sla-open');
        const fechar = document.getElementById('entrega-sla-close');
        const overlay = document.getElementById('entrega-sla-overlay');
        const introducao = document.getElementById(
            'entrega-sla-dialog-intro'
        );
        const lista = document.getElementById('entrega-sla-list');
        const url = <?php echo json_encode(route('entregas.alertas-sla'), 15, 512) ?>;
        const chaveSessao = 'entrega-sla-criticos-exibidos';
        let alertasAtuais = [];

        if (! resumo || ! overlay || ! lista) {
            return;
        }

        function abrirModal() {
            if (alertasAtuais.length === 0) {
                return;
            }

            overlay.hidden = false;
            document.body.classList.add('entrega-sla-modal-open');
            fechar.focus();
        }

        function fecharModal() {
            overlay.hidden = true;
            document.body.classList.remove('entrega-sla-modal-open');
        }

        function limparDestaques() {
            document.querySelectorAll(
                '[data-entrega-id].entrega-sla-warning, '
                + '[data-entrega-id].entrega-sla-critical'
            ).forEach(function (elemento) {
                elemento.classList.remove(
                    'entrega-sla-warning',
                    'entrega-sla-critical'
                );
            });
        }

        function destacarLinha(alerta) {
            document.querySelectorAll(
                '[data-entrega-id="' + Number(alerta.id) + '"]'
            ).forEach(function (elemento) {
                elemento.classList.add(
                    alerta.nivel === 'critico'
                        ? 'entrega-sla-critical'
                        : 'entrega-sla-warning'
                );
            });
        }

        function montarItem(alerta) {
            const item = document.createElement('article');
            item.className = 'entrega-sla-item'
                + (alerta.nivel === 'critico'
                    ? ' is-critical'
                    : '');

            const codigo = document.createElement('div');
            codigo.className = 'entrega-sla-code';
            codigo.textContent = alerta.codigo;

            const status = document.createElement('div');
            status.className = 'entrega-sla-status';
            status.textContent = alerta.status_rotulo;

            const tempo = document.createElement('div');
            tempo.className = 'entrega-sla-time';
            tempo.textContent = alerta.tempo_parado;

            const link = document.createElement('a');
            link.className = 'btn btn-outline-primary btn-sm';
            link.href = alerta.url;
            link.innerHTML = '<i class="bi bi-eye"></i>';
            link.title = 'Abrir entrega';
            link.setAttribute('aria-label', 'Abrir entrega ' + alerta.codigo);

            item.append(codigo, status, tempo, link);

            return item;
        }

        function renderizar(dados) {
            alertasAtuais = Array.isArray(dados.alertas)
                ? dados.alertas
                : [];
            const avisos = alertasAtuais.filter(
                alerta => alerta.nivel === 'aviso'
            );
            const criticos = alertasAtuais.filter(
                alerta => alerta.nivel === 'critico'
            );

            limparDestaques();
            lista.innerHTML = '';
            resumo.hidden = alertasAtuais.length === 0;
            avisosTotal.hidden = avisos.length === 0;
            criticosTotal.hidden = criticos.length === 0;
            avisosTotal.textContent = avisos.length + ' em atenção';
            criticosTotal.textContent = criticos.length + ' críticos';
            introducao.textContent = alertasAtuais.length
                + (alertasAtuais.length === 1
                    ? ' entrega exige acompanhamento operacional.'
                    : ' entregas exigem acompanhamento operacional.');

            alertasAtuais.forEach(function (alerta) {
                lista.append(montarItem(alerta));
                destacarLinha(alerta);
            });

            if (alertasAtuais.length === 0) {
                fecharModal();
                return;
            }

            const assinatura = criticos
                .map(alerta => String(alerta.id))
                .sort()
                .join(':');
            const assinaturaExibida = window.sessionStorage.getItem(
                chaveSessao
            );

            if (
                assinatura !== ''
                && assinatura !== assinaturaExibida
            ) {
                window.sessionStorage.setItem(chaveSessao, assinatura);
                abrirModal();
            }
        }

        async function sincronizarAlertas() {
            try {
                const resposta = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (! resposta.ok) {
                    throw new Error('Falha ao consultar alertas operacionais.');
                }

                renderizar(await resposta.json());
            } catch (erro) {
                console.warn(erro.message);
            }
        }

        abrir.addEventListener('click', abrirModal);
        fechar.addEventListener('click', fecharModal);
        overlay.addEventListener('click', function (evento) {
            if (evento.target === overlay) {
                fecharModal();
            }
        });
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && ! overlay.hidden) {
                fecharModal();
            }
        });

        sincronizarAlertas();
        window.setInterval(sincronizarAlertas, 60000);
    });
</script><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas\partials\alertas_sla.blade.php ENDPATH**/ ?>