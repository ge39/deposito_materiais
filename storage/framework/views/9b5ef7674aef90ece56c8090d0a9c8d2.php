



<?php $__env->startSection('title', 'Rastreamento de Veículos'); ?>

<?php $__env->startSection('content'); ?>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">

<style>
    .vehicle-tracking-page {
        --track-navy: #072b62;
        --track-border: #d5dce4;
        --track-muted: #667085;
        --track-bg: #f6f8fb;
    }

    .vehicle-tracking-page {
        padding: 1rem;
    }

    .vehicle-tracking-header {
        align-items: center;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        margin-bottom: .8rem;
    }

    .vehicle-tracking-header h1 {
        color: var(--track-navy);
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
    }

    .vehicle-tracking-header p {
        color: var(--track-muted);
        font-size: .78rem;
        margin: .18rem 0 0;
    }

    .vehicle-tracking-layout {
        display: grid;
        gap: .8rem;
        grid-template-columns: minmax(0, 1fr) 310px;
    }

    .vehicle-tracking-panel {
        background: #fff;
        border: 1px solid var(--track-border);
        border-radius: .35rem;
        overflow: hidden;
    }

    .vehicle-tracking-panel-header {
        align-items: center;
        background: var(--track-navy);
        color: #fff;
        display: flex;
        font-size: .75rem;
        font-weight: 800;
        justify-content: space-between;
        min-height: 40px;
        padding: .55rem .7rem;
        text-transform: uppercase;
    }

    /*
     * MESMA ESTRUTURA DO MAPA QUE JÁ FUNCIONA
     * EM /entregas-inteligentes.
     */
    .vehicle-tracking-map-wrap {
        background: #e9eef4;
        border: 1px solid var(--track-border);
        height: 520px;
        overflow: hidden;
        position: relative;
        width: 100%;
    }

    #mapa-rastreamento-veiculos {
        height: 100%;
        width: 100%;
        z-index: 1;
    }

    /*
     * Proteção contra CSS global do ERP interferindo nos tiles.
     * Leaflet precisa que cada tile permaneça 256 x 256 px.
     */
    #mapa-rastreamento-veiculos img.leaflet-tile {
        height: 256px !important;
        max-height: none !important;
        max-width: none !important;
        width: 256px !important;
    }

    #mapa-rastreamento-veiculos .leaflet-map-pane,
    #mapa-rastreamento-veiculos .leaflet-tile-pane,
    #mapa-rastreamento-veiculos .leaflet-overlay-pane,
    #mapa-rastreamento-veiculos .leaflet-shadow-pane,
    #mapa-rastreamento-veiculos .leaflet-marker-pane,
    #mapa-rastreamento-veiculos .leaflet-tooltip-pane,
    #mapa-rastreamento-veiculos .leaflet-popup-pane {
        left: 0;
        position: absolute;
        top: 0;
    }

    #mapa-rastreamento-veiculos .leaflet-tile {
        left: 0;
        position: absolute;
        top: 0;
    }

    .vehicle-list {
        height: 520px;
        overflow-y: auto;
        padding: .55rem;
    }

    .vehicle-card {
        background: #fff;
        border: 1px solid #d7dee7;
        border-radius: .4rem;
        cursor: pointer;
        margin-bottom: .5rem;
        padding: .65rem;
    }

    .vehicle-card:last-child {
        margin-bottom: 0;
    }

    .vehicle-card-top {
        align-items: flex-start;
        display: flex;
        gap: .5rem;
        justify-content: space-between;
    }

    .vehicle-plate {
        color: #16243a;
        font-size: .82rem;
        font-weight: 900;
    }

    .vehicle-model {
        color: var(--track-muted);
        font-size: .7rem;
        margin-top: .08rem;
    }

    .vehicle-status {
        border-radius: 1rem;
        font-size: .58rem;
        font-weight: 900;
        padding: .18rem .4rem;
    }

    .vehicle-status.online {
        background: #dff3e8;
        color: #116b39;
    }

    .vehicle-status.offline {
        background: #fde7e9;
        color: #a91f2d;
    }

    .vehicle-status.unknown {
        background: #fff3cd;
        color: #765900;
    }

    .vehicle-meta {
        color: #475467;
        display: grid;
        font-size: .66rem;
        gap: .24rem .45rem;
        grid-template-columns: 1fr 1fr;
        margin-top: .55rem;
    }

    .vehicle-empty,
    .vehicle-error {
        color: var(--track-muted);
        font-size: .75rem;
        padding: 1rem;
        text-align: center;
    }

    .vehicle-error {
        color: #a91f2d;
    }

    .vehicle-map-div-icon {
        background: transparent;
        border: 0;
    }

    .vehicle-map-marker-wrap {
        height: 34px;
        position: relative;
        width: 34px;
    }

    .vehicle-map-pin {
        align-items: center;
        background: #072b62;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 7px rgba(0, 0, 0, .35);
        color: #fff;
        display: flex;
        font-size: .9rem;
        height: 34px;
        justify-content: center;
        width: 34px;
    }

    .vehicle-map-pin.offline {
        background: #6c757d;
    }

    .vehicle-map-pin.unknown {
        background: #f0ad00;
        color: #212529;
    }

    .vehicle-popup-title {
        color: #072b62;
        font-size: .86rem;
        font-weight: 900;
        margin-bottom: .35rem;
    }

    .vehicle-popup-row {
        font-size: .72rem;
        margin-bottom: .15rem;
    }

    .vehicle-tracking-footer {
        align-items: center;
        color: var(--track-muted);
        display: flex;
        font-size: .66rem;
        justify-content: space-between;
        min-height: 32px;
        padding: .45rem .6rem;
    }

    @media (max-width: 980px) {
        .vehicle-tracking-layout {
            grid-template-columns: 1fr;
        }

        .vehicle-list {
            height: auto;
            max-height: 360px;
        }
    }
</style>

<div class="vehicle-tracking-page">
    <div class="vehicle-tracking-header">
        <div>
            <h1>
                <i class="bi bi-geo-alt me-1"></i>
                Rastreamento de veículos
            </h1>
            <p>
                Exibição dos veículos ativos vinculados ao Traccar.
                Não cria rota e não depende de entrega ou viagem.
            </p>
        </div>

        <button type="button"
                id="btn-atualizar-rastreamento"
                class="btn btn-sm btn-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Atualizar
        </button>
    </div>

    <div class="vehicle-tracking-layout">
        <section class="vehicle-tracking-panel">
            <div class="vehicle-tracking-panel-header">
                <span>
                    <i class="bi bi-map me-1"></i>
                    Mapa da frota
                </span>

                <span id="rastreamento-atualizado-em">
                    Aguardando consulta
                </span>
            </div>

            <div class="vehicle-tracking-map-wrap">
                <div id="mapa-rastreamento-veiculos"
                     role="region"
                     aria-label="Mapa dos veículos rastreados"></div>
            </div>

            <div class="vehicle-tracking-footer">
                <span id="rastreamento-resumo">
                    Preparando mapa...
                </span>

                <span>OpenStreetMap</span>
            </div>
        </section>

        <aside class="vehicle-tracking-panel">
            <div class="vehicle-tracking-panel-header">
                <span>
                    <i class="bi bi-truck me-1"></i>
                    Veículos
                </span>

                <span id="rastreamento-total">0</span>
            </div>

            <div id="lista-rastreamento-veiculos"
                 class="vehicle-list">
                <div class="vehicle-empty">
                    Carregando veículos...
                </div>
            </div>
        </aside>
    </div>
</div>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin="">
</script>

<script>
(function () {
    'use strict';

    const urlPosicoesVeiculos = <?php echo json_encode(
        route('entregas-inteligentes.posicoes-veiculos')
    , 15, 512) ?>;

    const mapa = L.map(
        document.getElementById('mapa-rastreamento-veiculos'),
        {
            zoomControl: true,
        }
    ).setView(
        [-23.5505, -46.6333],
        10
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }
    ).addTo(mapa);

    const camadaVeiculos = L.layerGroup().addTo(mapa);
    const marcadoresPorVeiculo = new Map();

    function escaparHtml(valor) {
        return String(valor ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function normalizarStatus(valor) {
        const status = String(valor || '').toLowerCase();

        if (status === 'online') {
            return 'online';
        }

        if (status === 'offline') {
            return 'offline';
        }

        return 'unknown';
    }

    function rotuloStatus(valor) {
        const status = normalizarStatus(valor);

        if (status === 'online') {
            return 'ONLINE';
        }

        if (status === 'offline') {
            return 'OFFLINE';
        }

        return 'DESCONHECIDO';
    }

    function formatarDataHora(valor) {
        if (! valor) {
            return '—';
        }

        const data = new Date(valor);

        if (Number.isNaN(data.getTime())) {
            return '—';
        }

        return new Intl.DateTimeFormat(
            'pt-BR',
            {
                dateStyle: 'short',
                timeStyle: 'medium',
            }
        ).format(data);
    }

    function criarIconeVeiculo(veiculo) {
        const status = normalizarStatus(
            veiculo.status
        );

        return L.divIcon({
            className: 'vehicle-map-div-icon',
            html: [
                '<div class="vehicle-map-marker-wrap">',
                    '<div class="vehicle-map-pin ',
                        status,
                    '">',
                        '<i class="bi bi-truck-front-fill"></i>',
                    '</div>',
                '</div>',
            ].join(''),
            iconSize: [34, 34],
            iconAnchor: [17, 17],
            popupAnchor: [0, -18],
        });
    }

    function criarPopupVeiculo(veiculo) {
        return [
            '<div class="vehicle-popup-title">',
                escaparHtml(veiculo.placa || 'Sem placa'),
                ' · ',
                escaparHtml(veiculo.modelo || 'Veículo'),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Status:</strong> ',
                escaparHtml(rotuloStatus(veiculo.status)),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>ERP:</strong> ',
                escaparHtml(veiculo.veiculo_id ?? '—'),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Identificador Traccar:</strong> ',
                escaparHtml(
                    veiculo.traccar_unique_id ?? '—'
                ),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Dispositivo:</strong> ',
                escaparHtml(veiculo.device_name || '—'),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Última posição:</strong> ',
                escaparHtml(
                    formatarDataHora(veiculo.fix_time)
                ),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Última comunicação:</strong> ',
                escaparHtml(
                    formatarDataHora(veiculo.last_update)
                ),
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Velocidade:</strong> ',
                Number.isFinite(
                    Number(veiculo.speed_kmh)
                )
                    ? escaparHtml(
                        Number(veiculo.speed_kmh)
                            .toFixed(1)
                        + ' km/h'
                    )
                    : '—',
            '</div>',

            '<div class="vehicle-popup-row">',
                '<strong>Bateria:</strong> ',
                Number.isFinite(
                    Number(veiculo.battery_level)
                )
                    ? escaparHtml(
                        Number(veiculo.battery_level)
                        + '%'
                    )
                    : '—',
            '</div>',
        ].join('');
    }

    function montarLista(veiculos) {
        const lista = document.getElementById(
            'lista-rastreamento-veiculos'
        );

        const total = document.getElementById(
            'rastreamento-total'
        );

        total.textContent = String(
            veiculos.length
        );

        if (veiculos.length === 0) {
            lista.innerHTML =
                '<div class="vehicle-empty">'
                + 'Nenhum veículo rastreável retornado.'
                + '</div>';

            return;
        }

        lista.innerHTML = '';

        veiculos.forEach(function (veiculo) {
            const status = normalizarStatus(
                veiculo.status
            );

            const latitude = Number(
                veiculo.latitude
            );

            const longitude = Number(
                veiculo.longitude
            );

            const temPosicao =
                veiculo.posicao_disponivel === true
                && veiculo.valid === true
                && Number.isFinite(latitude)
                && Number.isFinite(longitude);

            const item = document.createElement(
                'div'
            );

            item.className = 'vehicle-card';

            item.innerHTML = [
                '<div class="vehicle-card-top">',
                    '<div>',
                        '<div class="vehicle-plate">',
                            escaparHtml(
                                veiculo.placa || 'Sem placa'
                            ),
                        '</div>',
                        '<div class="vehicle-model">',
                            escaparHtml(
                                veiculo.modelo || 'Veículo'
                            ),
                        '</div>',
                    '</div>',

                    '<span class="vehicle-status ',
                        status,
                    '">',
                        escaparHtml(
                            rotuloStatus(veiculo.status)
                        ),
                    '</span>',
                '</div>',

                '<div class="vehicle-meta">',
                    '<span><strong>ERP:</strong> ',
                        escaparHtml(
                            veiculo.veiculo_id ?? '—'
                        ),
                    '</span>',

                    '<span><strong>Identificador:</strong> ',
                        escaparHtml(
                            veiculo.traccar_unique_id ?? '—'
                        ),
                    '</span>',

                    '<span><strong>GPS:</strong> ',
                        temPosicao ? 'OK' : 'SEM POSIÇÃO',
                    '</span>',

                    '<span><strong>Bateria:</strong> ',
                        Number.isFinite(
                            Number(veiculo.battery_level)
                        )
                            ? escaparHtml(
                                Number(
                                    veiculo.battery_level
                                )
                                + '%'
                            )
                            : '—',
                    '</span>',

                    '<span style="grid-column:1/-1;">',
                        '<strong>Fix:</strong> ',
                        escaparHtml(
                            formatarDataHora(
                                veiculo.fix_time
                            )
                        ),
                    '</span>',
                '</div>',
            ].join('');

            if (temPosicao) {
                item.addEventListener(
                    'click',
                    function () {
                        const marcador =
                            marcadoresPorVeiculo.get(
                                Number(
                                    veiculo.veiculo_id
                                )
                            );

                        if (! marcador) {
                            return;
                        }

                        mapa.setView(
                            marcador.getLatLng(),
                            17
                        );

                        marcador.openPopup();
                    }
                );
            }

            lista.appendChild(item);
        });
    }

    function desenharVeiculos(veiculos) {
        camadaVeiculos.clearLayers();
        marcadoresPorVeiculo.clear();

        const limites = L.latLngBounds([]);
        let totalComPosicao = 0;

        veiculos.forEach(function (veiculo) {
            const latitude = Number(
                veiculo.latitude
            );

            const longitude = Number(
                veiculo.longitude
            );

            const temPosicao =
                veiculo.posicao_disponivel === true
                && veiculo.valid === true
                && Number.isFinite(latitude)
                && Number.isFinite(longitude);

            if (! temPosicao) {
                return;
            }

            const posicao = L.latLng(
                latitude,
                longitude
            );

            const marcador = L.marker(
                posicao,
                {
                    icon: criarIconeVeiculo(
                        veiculo
                    ),
                    riseOnHover: true,
                    zIndexOffset: 1000,
                }
            );

            marcador.bindPopup(
                criarPopupVeiculo(veiculo),
                {
                    maxWidth: 320,
                }
            );

            marcador.addTo(
                camadaVeiculos
            );

            marcadoresPorVeiculo.set(
                Number(veiculo.veiculo_id),
                marcador
            );

            limites.extend(posicao);
            totalComPosicao++;
        });

        /*
         * Mesma filosofia da Entrega Inteligente:
         * primeiro o tamanho real do container, depois centraliza.
         */
        mapa.invalidateSize({
            pan: false,
        });

        if (limites.isValid()) {
            if (totalComPosicao === 1) {
                mapa.setView(
                    limites.getCenter(),
                    17
                );
            } else {
                mapa.fitBounds(
                    limites,
                    {
                        padding: [35, 35],
                        maxZoom: 16,
                    }
                );
            }
        }

        return totalComPosicao;
    }

    async function carregarVeiculos() {
        const botao = document.getElementById(
            'btn-atualizar-rastreamento'
        );

        const atualizado = document.getElementById(
            'rastreamento-atualizado-em'
        );

        const resumo = document.getElementById(
            'rastreamento-resumo'
        );

        const lista = document.getElementById(
            'lista-rastreamento-veiculos'
        );

        botao.disabled = true;
        atualizado.textContent =
            'Consultando Traccar...';

        try {
            const resposta = await fetch(
                urlPosicoesVeiculos,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                    cache: 'no-store',
                }
            );

            const dados = await resposta.json();

            if (
                ! resposta.ok
                || dados.ok !== true
            ) {
                throw new Error(
                    dados.message
                    || 'Falha ao consultar veículos.'
                );
            }

            const veiculos = Array.isArray(
                dados.veiculos
            )
                ? dados.veiculos
                : [];

            montarLista(veiculos);

            const comPosicao =
                desenharVeiculos(veiculos);

            resumo.textContent =
                veiculos.length
                + ' veículo(s) retornado(s) · '
                + comPosicao
                + ' com posição GPS';

            atualizado.textContent =
                'Atualizado em '
                + new Intl.DateTimeFormat(
                    'pt-BR',
                    {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    }
                ).format(new Date());

        } catch (erro) {
            console.error(
                'Erro ao consultar veículos.',
                erro
            );

            lista.innerHTML =
                '<div class="vehicle-error">'
                + 'Falha ao consultar os veículos.<br>'
                + escaparHtml(erro.message)
                + '</div>';

            atualizado.textContent =
                'Falha na consulta';

            resumo.textContent =
                'Rastreamento indisponível.';
        } finally {
            botao.disabled = false;
        }
    }

    document.getElementById(
        'btn-atualizar-rastreamento'
    ).addEventListener(
        'click',
        carregarVeiculos
    );

    /*
     * Não há lógica experimental de ResizeObserver,
     * redraw ou temporizadores aqui.
     * O mapa usa o mesmo padrão simples que já funciona
     * na Entrega Inteligente.
     */
    carregarVeiculos();
})();
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/rastreamento.blade.php ENDPATH**/ ?>