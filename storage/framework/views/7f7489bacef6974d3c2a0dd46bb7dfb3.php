

<?php $__env->startSection('content'); ?>

<?php
    $formatarQuantidade = static function ($valor): string {
        return number_format(
            (float) $valor,
            2,
            ',',
            '.'
        );
    };

    $classeStatus = static function (?string $status): string {
        return match ($status) {
            'Entregue' => 'status-success',
            'Entregue_finalizada_com_ocorrencia' => 'status-warning',
            'Entregue_parcial' => 'status-warning',
            'Em_rota', 'No_destino' => 'status-primary',
            'Liberada', 'Carregada' => 'status-info',
            'Nao_entregue', 'Recusada', 'Devolvida' => 'status-danger',
            default => 'status-secondary',
        };
    };

    $percentualKpi = static function ($parte, $total): string {
        if ((float) $total <= 0) {
            return '0%';
        }

        return number_format(
            ((float) $parte / (float) $total) * 100,
            0,
            ',',
            '.'
        ) . '%';
    };

    $limitarTexto = static function ($texto, int $limite = 48): string {
        $texto = trim((string) $texto);

        $comprimento = function_exists('mb_strlen')
            ? mb_strlen($texto)
            : strlen($texto);

        if ($texto === '' || $comprimento <= $limite) {
            return $texto;
        }

        $tamanhoRecorte = max(1, $limite - 3);
        $recorte = function_exists('mb_substr')
            ? mb_substr($texto, 0, $tamanhoRecorte)
            : substr($texto, 0, $tamanhoRecorte);

        return rtrim($recorte) . '...';
    };

    $pontosMapa = $entregasDetalhadas
        ->filter(
            fn (array $entrega): bool =>
                (bool) ($entrega['coordenada_confirmada'] ?? false)
                && ($entrega['latitude_entrega'] ?? null) !== null
                && ($entrega['longitude_entrega'] ?? null) !== null
        )
        ->map(
            fn (array $entrega): array => [
                'id' => (int) $entrega['id'],
                'codigo' => (string) $entrega['codigo'],
                'cliente' => (string) $entrega['cliente'],
                'endereco' => (string) $entrega['endereco'],
                'endereco_chave' => (string) $entrega['endereco_chave'],
                'latitude_entrega' =>
                    $entrega['latitude_entrega'],
                'longitude_entrega' =>
                    $entrega['longitude_entrega'],
                'coordenada_confirmada' =>
                    (bool) $entrega['coordenada_confirmada'],
                'data' => (string) $entrega['data_formatada'],
                'periodo' => (string) $entrega['periodo_rotulo'],
                'status' => (string) $entrega['status_rotulo'],
                'atrasada' => (bool) $entrega['atrasada'],
                'encerrada' => (bool) $entrega['encerrada'],
                'url' => route(
                    'entregas.show',
                    $entrega['id']
                ),
            ]
        )
        ->values();
?>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">

<style>
    .smart-dashboard {
        --dash-navy: #072b62;
        --dash-navy-dark: #041d43;
        --dash-blue: #2267bd;
        --dash-blue-soft: #e8f1fb;
        --dash-green: #249654;
        --dash-orange: #f48120;
        --dash-red: #dc3e3e;
        --dash-cyan: #15aabf;
        --dash-gray: #6c757d;
        --dash-border: #d5dce4;
        --dash-canvas: #eef1f5;
        background: var(--dash-canvas);
        color: #111827;
        min-height: calc(100vh - 56px);
        padding: .85rem;
    }

    .smart-dashboard .dashboard-shell {
        margin: 0 auto;
        max-width: 1880px;
    }

    .smart-dashboard .dashboard-header {
        align-items: center;
        background: linear-gradient(
            120deg,
            var(--dash-navy-dark),
            var(--dash-navy)
        );
        border-radius: .55rem;
        box-shadow: 0 .22rem .55rem rgba(0, 0, 0, .17);
        color: #fff;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: .85rem;
        padding: .85rem 1.1rem;
    }

    .smart-dashboard .dashboard-title {
        font-size: clamp(1.15rem, 2vw, 1.7rem);
        font-weight: 800;
        letter-spacing: .02em;
        margin: 0;
        text-transform: uppercase;
    }

    .smart-dashboard .dashboard-subtitle {
        color: rgba(255, 255, 255, .76);
        font-size: .78rem;
        margin-top: .18rem;
    }

    .smart-dashboard .dashboard-filter {
        align-items: end;
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        justify-content: flex-end;
    }

    .smart-dashboard .filter-field label {
        color: rgba(255, 255, 255, .82);
        display: block;
        font-size: .66rem;
        font-weight: 700;
        margin-bottom: .18rem;
        text-transform: uppercase;
    }

    .smart-dashboard .filter-field .form-control,
    .smart-dashboard .filter-field .form-select {
        font-size: .78rem;
        min-width: 132px;
    }

    .smart-dashboard .filter-readonly {
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: .32rem;
        color: #fff;
        font-size: .76rem;
        min-width: 118px;
        padding: .4rem .55rem;
        text-align: center;
    }

    .smart-dashboard .kpi-grid {
        display: grid;
        gap: .7rem;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        margin-bottom: .85rem;
    }

    .smart-dashboard .kpi-card {
        align-items: center;
        background: #fff;
        border: 1px solid var(--dash-border);
        border-radius: .5rem;
        box-shadow: 0 .18rem .4rem rgba(16, 24, 40, .1);
        display: flex;
        gap: .72rem;
        min-height: 96px;
        padding: .75rem .85rem;
    }

    .smart-dashboard .kpi-icon {
        align-items: center;
        border-radius: 50%;
        color: #fff;
        display: flex;
        flex: 0 0 48px;
        font-size: 1.35rem;
        height: 48px;
        justify-content: center;
        width: 48px;
    }

    .smart-dashboard .kpi-blue .kpi-icon {
        background: var(--dash-blue);
    }

    .smart-dashboard .kpi-green .kpi-icon {
        background: var(--dash-green);
    }

    .smart-dashboard .kpi-orange .kpi-icon {
        background: var(--dash-orange);
    }

    .smart-dashboard .kpi-red .kpi-icon {
        background: var(--dash-red);
    }

    .smart-dashboard .kpi-cyan .kpi-icon {
        background: var(--dash-cyan);
    }

    .smart-dashboard .kpi-label {
        color: #27364a;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    .smart-dashboard .kpi-value {
        color: #07111e;
        font-size: 1.75rem;
        font-weight: 850;
        line-height: 1;
        margin: .18rem 0;
    }

    .smart-dashboard .kpi-note {
        color: #6b7280;
        font-size: .68rem;
    }

    .smart-dashboard .dashboard-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: 1fr 1fr 1fr;
        margin-bottom: .75rem;
    }

    .smart-dashboard .dashboard-grid-lower {
        display: grid;
        gap: .75rem;
        grid-template-columns: 1.08fr .92fr;
        margin-bottom: .75rem;
    }

    .smart-dashboard .panel {
        background: #fff;
        border: 1px solid var(--dash-border);
        border-radius: .42rem;
        box-shadow: 0 .16rem .36rem rgba(16, 24, 40, .09);
        min-width: 0;
        overflow: hidden;
    }

    .smart-dashboard .panel-header {
        align-items: center;
        background: var(--dash-navy);
        color: #fff;
        display: flex;
        font-size: .74rem;
        font-weight: 800;
        gap: .4rem;
        justify-content: space-between;
        letter-spacing: .015em;
        min-height: 33px;
        padding: .48rem .65rem;
        text-transform: uppercase;
    }

    .smart-dashboard .panel-body {
        padding: .72rem;
    }

    .smart-dashboard .ranking-row {
        align-items: center;
        display: grid;
        gap: .45rem;
        grid-template-columns: minmax(105px, 34%) 1fr 28px;
        margin-bottom: .57rem;
    }

    .smart-dashboard .ranking-row:last-child {
        margin-bottom: 0;
    }

    .smart-dashboard .ranking-name {
        font-size: .72rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .smart-dashboard .ranking-track,
    .smart-dashboard .status-track {
        background: #e7ebf0;
        border-radius: .15rem;
        height: 21px;
        overflow: hidden;
        position: relative;
    }

    .smart-dashboard .ranking-fill {
        background: linear-gradient(90deg, #184d9a, #3b76cc);
        height: 100%;
        min-width: 2px;
    }

    .smart-dashboard .ranking-total {
        font-size: .72rem;
        font-weight: 800;
        text-align: right;
    }

    .smart-dashboard .period-grid {
        display: grid;
        gap: .55rem;
    }

    .smart-dashboard .period-card {
        border: 1px solid #d8dee7;
        border-left: .3rem solid var(--dash-blue);
        border-radius: .32rem;
        padding: .48rem .55rem;
    }

    .smart-dashboard .period-head {
        align-items: center;
        display: flex;
        font-size: .72rem;
        justify-content: space-between;
        margin-bottom: .35rem;
    }

    .smart-dashboard .period-total {
        background: var(--dash-navy);
        border-radius: 1rem;
        color: #fff;
        font-size: .64rem;
        font-weight: 800;
        padding: .12rem .42rem;
    }

    .smart-dashboard .period-metrics {
        display: grid;
        gap: .3rem;
        grid-template-columns: repeat(3, 1fr);
    }

    .smart-dashboard .period-metric {
        background: #f3f5f8;
        border-radius: .25rem;
        font-size: .62rem;
        padding: .3rem;
        text-align: center;
    }

    .smart-dashboard .period-metric strong {
        display: block;
        font-size: .78rem;
    }

    .smart-dashboard .delivery-map-wrap {
        background: #e9eef4;
        border: 1px solid var(--dash-border);
        border-radius: .32rem;
        height: 300px;
        overflow: hidden;
        position: relative;
    }

    body.delivery-map-fullscreen {
        overflow: hidden !important;
    }

    .smart-dashboard .delivery-map-wrap.is-fullscreen {
        border: 0;
        border-radius: 0;
        height: 100vh !important;
        inset: 0;
        position: fixed;
        width: 100vw;
        z-index: 2000;
    }

    .smart-dashboard .delivery-map-actions {
        align-items: center;
        display: flex;
        gap: .35rem;
        position: absolute;
        right: .55rem;
        top: .55rem;
        z-index: 1000;
    }

    .smart-dashboard .delivery-map-action {
        align-items: center;
        background: #fff;
        border: 1px solid #bfc8d2;
        border-radius: .3rem;
        box-shadow: 0 1px 5px rgba(0, 0, 0, .22);
        color: var(--dash-navy);
        display: inline-flex;
        font-size: .65rem;
        font-weight: 800;
        gap: .3rem;
        min-height: 32px;
        padding: .35rem .55rem;
    }

    .smart-dashboard .delivery-map-action:hover,
    .smart-dashboard .delivery-map-action:focus {
        background: var(--dash-navy);
        color: #fff;
    }

    .smart-dashboard .delivery-map-wrap.is-fullscreen
    .delivery-map-actions {
        right: 1rem;
        top: 1rem;
    }

    .smart-dashboard #mapa-entregas-inteligentes {
        height: 100%;
        width: 100%;
        z-index: 1;
    }

    .smart-dashboard .delivery-map-div-icon {
        background: transparent;
        border: 0;
    }

    .smart-dashboard .delivery-map-pin {
        align-items: center;
        border: 2px solid #fff;
        border-radius: 50% 50% 50% 0;
        box-shadow: 0 2px 7px rgba(0, 0, 0, .35);
        color: #fff;
        display: flex;
        font-size: .72rem;
        font-weight: 800;
        height: 30px;
        justify-content: center;
        transform: rotate(-45deg);
        width: 30px;
    }

    .smart-dashboard .delivery-map-pin span {
        transform: rotate(45deg);
    }

    .smart-dashboard .delivery-map-overlap {
        align-items: center;
        background: var(--dash-navy);
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .4);
        color: #fff;
        display: flex;
        flex-direction: column;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        line-height: 1;
        width: 42px;
    }

    .smart-dashboard .delivery-map-overlap strong {
        font-size: .8rem;
    }

    .smart-dashboard .delivery-map-overlap small {
        font-size: .48rem;
        margin-top: .1rem;
        text-transform: uppercase;
    }

    .smart-dashboard .leaflet-popup-content-wrapper,
    .smart-dashboard .leaflet-popup-tip {
        color: #172033;
    }

    .smart-dashboard .leaflet-popup-content {
        margin: .7rem .8rem;
    }

    .smart-dashboard .delivery-map-message {
        align-items: center;
        display: flex;
        flex-direction: column;
        font-size: .72rem;
        gap: .35rem;
        height: 100%;
        justify-content: center;
        padding: 1rem;
        text-align: center;
    }

    .smart-dashboard .delivery-map-footer {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .45rem .8rem;
        justify-content: space-between;
        margin-top: .45rem;
    }

    .smart-dashboard .delivery-map-legend {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        font-size: .65rem;
        gap: .65rem;
    }

    .smart-dashboard .delivery-map-legend span {
        align-items: center;
        display: inline-flex;
        gap: .25rem;
    }

    .smart-dashboard .delivery-map-legend i {
        border-radius: 50%;
        display: inline-block;
        height: .58rem;
        width: .58rem;
    }

    .smart-dashboard .map-info-window {
        color: #172033;
        font-size: .75rem;
        line-height: 1.35;
        max-width: 320px;
        min-width: 230px;
        padding: .15rem;
    }

    .smart-dashboard .map-info-address {
        border-bottom: 1px solid #e0e4e9;
        display: block;
        margin-bottom: .38rem;
        padding-bottom: .32rem;
    }

    .smart-dashboard .map-info-delivery {
        border-bottom: 1px dashed #e0e4e9;
        padding: .35rem 0;
    }

    .smart-dashboard .map-info-delivery:last-child {
        border-bottom: 0;
    }

    .smart-dashboard .map-info-code {
        color: var(--dash-blue);
        font-weight: 800;
        text-decoration: none;
    }

    .smart-dashboard .map-info-status {
        color: #657184;
        display: block;
        font-size: .68rem;
        margin-top: .1rem;
    }

    .smart-dashboard .chart-wrap {
        height: 286px;
        min-height: 286px;
        position: relative;
    }

    .smart-dashboard #grafico-entregas-dia {
        height: 100%;
        width: 100%;
    }

    .smart-dashboard .chart-legend {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        font-size: .65rem;
        gap: .7rem;
        margin-bottom: .35rem;
    }

    .smart-dashboard .legend-mark {
        border-radius: .08rem;
        display: inline-block;
        height: 9px;
        margin-right: .22rem;
        width: 9px;
    }

    .smart-dashboard .status-row {
        align-items: center;
        display: grid;
        gap: .5rem;
        grid-template-columns: minmax(128px, 30%) 1fr 38px 48px;
        margin-bottom: .7rem;
    }

    .smart-dashboard .status-row:last-child {
        margin-bottom: 0;
    }

    .smart-dashboard .status-name {
        font-size: .7rem;
    }

    .smart-dashboard .status-fill {
        height: 100%;
        min-width: 0;
    }

    .smart-dashboard .status-number,
    .smart-dashboard .status-percent {
        font-size: .7rem;
        font-weight: 750;
        text-align: right;
    }

    .smart-dashboard .alerts-grid {
        display: grid;
        gap: .55rem;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        padding: .65rem;
    }

    .smart-dashboard .alert-card {
        align-items: center;
        border: 1px solid #dde2e8;
        border-radius: .38rem;
        display: flex;
        gap: .55rem;
        min-height: 68px;
        padding: .5rem;
    }

    .smart-dashboard .alert-icon {
        align-items: center;
        border-radius: 50%;
        color: #fff;
        display: flex;
        flex: 0 0 35px;
        font-size: .95rem;
        height: 35px;
        justify-content: center;
        width: 35px;
    }

    .smart-dashboard .alert-danger .alert-icon { background: var(--dash-red); }
    .smart-dashboard .alert-warning .alert-icon { background: var(--dash-orange); }
    .smart-dashboard .alert-info .alert-icon { background: var(--dash-cyan); }
    .smart-dashboard .alert-primary .alert-icon { background: var(--dash-blue); }

    .smart-dashboard .alert-title {
        font-size: .68rem;
        font-weight: 800;
        line-height: 1.15;
        text-transform: uppercase;
    }

    .smart-dashboard .alert-description {
        color: #697586;
        font-size: .61rem;
        line-height: 1.2;
        margin-top: .16rem;
    }

    .smart-dashboard .opportunity-grid {
        display: grid;
        gap: .55rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: .65rem;
    }

    .smart-dashboard .opportunity-card {
        background: #fff8e7;
        border: 1px solid #f0cb71;
        border-left: .35rem solid #f0a500;
        border-radius: .35rem;
        padding: .62rem;
    }

    .smart-dashboard .opportunity-code {
        font-size: .76rem;
        font-weight: 850;
    }

    .smart-dashboard .opportunity-detail {
        color: #586474;
        font-size: .68rem;
        margin-top: .22rem;
    }

    .smart-dashboard .dashboard-table {
        margin: 0;
        min-width: 1320px;
    }

    .smart-dashboard .dashboard-table thead th {
        background: var(--dash-navy);
        border-color: #31527e;
        color: #fff;
        font-size: .67rem;
        padding: .46rem .48rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .smart-dashboard .dashboard-table tbody td {
        border-color: #dce2e8;
        font-size: .7rem;
        padding: .43rem .48rem;
        vertical-align: middle;
    }

    .smart-dashboard .dashboard-table tbody tr:hover td {
        background: #eef5ff;
    }

    .smart-dashboard .row-overdue td {
        background: #fff1f1;
    }

    .smart-dashboard .status-pill {
        border: 1px solid transparent;
        border-radius: 1rem;
        display: inline-block;
        font-size: .62rem;
        font-weight: 800;
        padding: .18rem .48rem;
        white-space: nowrap;
    }

    .smart-dashboard .status-success {
        background: #e5f5eb;
        border-color: #89cba3;
        color: #13743d;
    }

    .smart-dashboard .status-warning {
        background: #fff3cd;
        border-color: #e7c65f;
        color: #775b00;
    }

    .smart-dashboard .status-primary {
        background: #e4efff;
        border-color: #8fb5e8;
        color: #174f97;
    }

    .smart-dashboard .status-info {
        background: #def6fa;
        border-color: #7fcbd7;
        color: #086273;
    }

    .smart-dashboard .status-danger {
        background: #fbe5e7;
        border-color: #e69ba3;
        color: #9c2330;
    }

    .smart-dashboard .status-secondary {
        background: #eceff2;
        border-color: #b8c0c8;
        color: #47515b;
    }

    .smart-dashboard .empty-panel {
        align-items: center;
        color: #748091;
        display: flex;
        font-size: .72rem;
        justify-content: center;
        min-height: 150px;
        padding: 1rem;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .smart-dashboard .kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .smart-dashboard .dashboard-grid {
            grid-template-columns: 1fr 1fr;
        }

        .smart-dashboard .panel-concentration {
            grid-column: 1 / -1;
        }

        .smart-dashboard .alerts-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .smart-dashboard {
            padding: .5rem;
        }

        .smart-dashboard .dashboard-header {
            align-items: stretch;
            flex-direction: column;
        }

        .smart-dashboard .dashboard-filter {
            justify-content: stretch;
        }

        .smart-dashboard .filter-field,
        .smart-dashboard .filter-field .form-control,
        .smart-dashboard .filter-readonly,
        .smart-dashboard .dashboard-filter .btn {
            width: 100%;
        }

        .smart-dashboard .kpi-grid,
        .smart-dashboard .dashboard-grid,
        .smart-dashboard .dashboard-grid-lower,
        .smart-dashboard .alerts-grid,
        .smart-dashboard .opportunity-grid {
            grid-template-columns: 1fr;
        }

        .smart-dashboard .panel-concentration {
            grid-column: auto;
        }
    }
</style>

<div class="smart-dashboard">
    <div class="dashboard-shell">
        <header class="dashboard-header">
            <div>
                <h1 class="dashboard-title">
                    <i class="bi bi-truck-front me-1"></i>
                    Painel de Gestão Logística
                </h1>

                <div class="dashboard-subtitle">
                    Entrega Inteligente · análise operacional sem alteração automática do fluxo
                </div>
            </div>

            <form method="GET"
                  action="<?php echo e(route('entregas-inteligentes.index')); ?>"
                  class="dashboard-filter">
                <div class="filter-field">
                    <label for="data_referencia">Data de referência</label>
                    <input type="date"
                           id="data_referencia"
                           name="data_referencia"
                           class="form-control form-control-sm"
                           value="<?php echo e($dataReferencia->toDateString()); ?>">
                </div>

                <div class="filter-field">
                    <label>Início da janela</label>
                    <div class="filter-readonly">
                        <?php echo e($inicioJanela->format('d/m/Y')); ?>

                    </div>
                </div>

                <div class="filter-field">
                    <label>Fim da janela</label>
                    <div class="filter-readonly">
                        <?php echo e($fimJanela->format('d/m/Y')); ?>

                    </div>
                </div>

                <button type="submit"
                        class="btn btn-info btn-sm fw-bold">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Atualizar
                </button>
            </form>
        </header>

        <section class="kpi-grid" aria-label="Indicadores principais">
            <article class="kpi-card kpi-blue">
                <div class="kpi-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="kpi-label">Total programado</div>
                    <div class="kpi-value"><?php echo e($indicadores['total']); ?></div>
                    <div class="kpi-note">
                        Qtd. <?php echo e($formatarQuantidade($indicadores['quantidade_prevista'])); ?>

                    </div>
                </div>
            </article>

            <article class="kpi-card kpi-green">
                <div class="kpi-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="kpi-label">Concluídas</div>
                    <div class="kpi-value"><?php echo e($indicadores['concluidas']); ?></div>
                    <div class="kpi-note">
                        <?php echo e($percentualKpi($indicadores['concluidas'], $indicadores['total'])); ?> do total
                    </div>
                </div>
            </article>

            <article class="kpi-card kpi-orange">
                <div class="kpi-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="kpi-label">Em andamento</div>
                    <div class="kpi-value"><?php echo e($indicadores['em_andamento']); ?></div>
                    <div class="kpi-note">
                        <?php echo e($percentualKpi($indicadores['em_andamento'], $indicadores['total'])); ?> do total
                    </div>
                </div>
            </article>

            <article class="kpi-card kpi-red">
                <div class="kpi-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="kpi-label">Atrasadas</div>
                    <div class="kpi-value"><?php echo e($indicadores['atrasadas']); ?></div>
                    <div class="kpi-note">
                        <?php echo e($percentualKpi($indicadores['atrasadas'], $indicadores['total'])); ?> requer atenção
                    </div>
                </div>
            </article>

            <article class="kpi-card kpi-cyan">
                <div class="kpi-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div>
                    <div class="kpi-label">Eficiência</div>
                    <div class="kpi-value">
                        <?php echo e(number_format($indicadores['eficiencia'], 0, ',', '.')); ?>%
                    </div>
                    <div class="kpi-note">Entregas efetivamente concluídas</div>
                </div>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-people me-1"></i>
                        Entregas por cliente
                    </span>
                    <span><?php echo e($rankingClientes->count()); ?> grupos</span>
                </h2>

                <div class="panel-body">
                    <?php $__empty_1 = true; $__currentLoopData = $rankingClientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="ranking-row">
                            <div class="ranking-name"
                                 title="<?php echo e($cliente['cliente']); ?>">
                                <?php echo e($cliente['cliente']); ?>

                            </div>
                            <div class="ranking-track"
                                 title="<?php echo e($cliente['total']); ?> entrega(s)">
                                <div class="ranking-fill"
                                     style="width: <?php echo e($cliente['percentual_barra']); ?>%;"></div>
                            </div>
                            <div class="ranking-total">
                                <?php echo e($cliente['total']); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty-panel">
                            Nenhuma entrega encontrada na janela selecionada.
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-diagram-3 me-1"></i>
                        Distribuição por período e status
                    </span>
                </h2>

                <div class="panel-body">
                    <div class="period-grid">
                        <?php $__empty_1 = true; $__currentLoopData = $distribuicaoPeriodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $periodo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="period-card">
                                <div class="period-head">
                                    <strong><?php echo e($periodo['rotulo']); ?></strong>
                                    <span class="period-total">
                                        <?php echo e($periodo['total']); ?> entrega(s)
                                    </span>
                                </div>

                                <div class="period-metrics">
                                    <div class="period-metric text-success">
                                        <strong><?php echo e($periodo['concluidas']); ?></strong>
                                        Concluídas
                                    </div>
                                    <div class="period-metric text-warning">
                                        <strong><?php echo e($periodo['em_andamento']); ?></strong>
                                        Em andamento
                                    </div>
                                    <div class="period-metric text-danger">
                                        <strong><?php echo e($periodo['atrasadas']); ?></strong>
                                        Atrasadas
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="empty-panel">
                                Não existem períodos para consolidar.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <article class="panel panel-concentration">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-geo-alt me-1"></i>
                        Mapa operacional
                    </span>
                    <span>
                        <?php echo e($pontosMapa->count()); ?> entrega(s) · OpenStreetMap
                    </span>
                </h2>

                <div class="panel-body">
                    <div class="delivery-map-wrap">
                        <div id="mapa-entregas-inteligentes"
                             role="region"
                             aria-label="Mapa operacional das entregas"></div>

                        <div class="delivery-map-actions"
                             aria-label="Controles adicionais do mapa">
                            <button type="button"
                                    id="mapa-entregas-centralizar"
                                    class="delivery-map-action"
                                    title="Centralizar todos os pontos">
                                <i class="bi bi-crosshair"></i>
                                <span>Centralizar</span>
                            </button>

                            <button type="button"
                                    id="mapa-entregas-tela-cheia"
                                    class="delivery-map-action"
                                    aria-pressed="false"
                                    title="Expandir mapa">
                                <i class="bi bi-arrows-fullscreen"></i>
                                <span>Expandir</span>
                            </button>
                        </div>
                    </div>

                    <div class="delivery-map-footer">
                        <div class="delivery-map-legend"
                             aria-label="Legenda dos pontos do mapa">
                            <span>
                                <i style="background:#249654"></i>
                                Concluída
                            </span>
                            <span>
                                <i style="background:#f48120"></i>
                                Programada
                            </span>
                            <span>
                                <i style="background:#dc3e3e"></i>
                                Atrasada
                            </span>
                        </div>

                        <div id="mapa-entregas-mensagem"
                             class="small text-muted">
                            Preparando os pontos das entregas no mapa.
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="dashboard-grid-lower">
            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-bar-chart-line me-1"></i>
                        Entregas por dia — D-7 a D+7
                    </span>
                    <span><?php echo e($inicioJanela->format('d/m')); ?> a <?php echo e($fimJanela->format('d/m')); ?></span>
                </h2>

                <div class="panel-body">
                    <div class="chart-legend">
                        <span>
                            <i class="legend-mark" style="background:#2267bd;"></i>
                            Programadas
                        </span>
                        <span>
                            <i class="legend-mark" style="background:#249654;"></i>
                            Concluídas
                        </span>
                        <span>
                            <i class="legend-mark" style="background:#f48120;"></i>
                            Eficiência
                        </span>
                    </div>

                    <div class="chart-wrap">
                        <canvas id="grafico-entregas-dia"
                                aria-label="Gráfico de entregas por dia"></canvas>
                    </div>
                </div>
            </article>

            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-speedometer2 me-1"></i>
                        Status operacional
                    </span>
                    <span>% do total</span>
                </h2>

                <div class="panel-body">
                    <?php $__currentLoopData = $distribuicaoStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="status-row">
                            <div class="status-name">
                                <?php echo e($status['rotulo']); ?>

                            </div>
                            <div class="status-track">
                                <div class="status-fill"
                                     style="width: <?php echo e($status['percentual']); ?>%; background: <?php echo e($status['cor']); ?>;"></div>
                            </div>
                            <div class="status-number">
                                <?php echo e($status['quantidade']); ?>

                            </div>
                            <div class="status-percent">
                                <?php echo e(number_format($status['percentual'], 0, ',', '.')); ?>%
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </article>
        </section>

        <section class="panel mb-3">
            <h2 class="panel-header">
                <span>
                    <i class="bi bi-exclamation-diamond me-1"></i>
                    Alertas e decisões prioritárias
                </span>
            </h2>

            <div class="alerts-grid">
                <?php $__currentLoopData = $alertasOperacionais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="alert-card alert-<?php echo e($alerta['tipo']); ?>">
                        <div class="alert-icon">
                            <i class="bi <?php echo e($alerta['icone']); ?>"></i>
                        </div>
                        <div>
                            <div class="alert-title">
                                <?php echo e($alerta['quantidade']); ?> · <?php echo e($alerta['titulo']); ?>

                            </div>
                            <div class="alert-description">
                                <?php echo e($alerta['descricao']); ?>

                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <?php if($oportunidades->isNotEmpty()): ?>
            <section class="panel mb-3">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-lightbulb me-1"></i>
                        Sugestões de antecipação D+1 / D+2
                    </span>
                    <span>Exige confirmação do cliente</span>
                </h2>

                <div class="opportunity-grid">
                    <?php $__currentLoopData = $oportunidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oportunidade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $futura = $oportunidade['entrega_futura'];
                        ?>

                        <article class="opportunity-card">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="opportunity-code">
                                        <?php echo e($futura['codigo']); ?> · <?php echo e($futura['cliente']); ?>

                                    </div>
                                    <div class="opportunity-detail">
                                        Programada para <?php echo e($futura['data_formatada']); ?>

                                        · <?php echo e($futura['periodo_rotulo']); ?>

                                    </div>
                                </div>

                                <span class="badge bg-warning text-dark align-self-start">
                                    D+<?php echo e($oportunidade['dias_antecipacao']); ?>

                                </span>
                            </div>

                            <div class="opportunity-detail">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                <?php echo e($oportunidade['endereco']); ?>

                            </div>

                            <div class="opportunity-detail">
                                Hoje no mesmo endereço:
                                <?php $__currentLoopData = $oportunidade['entregas_hoje']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entregaHoje): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <strong class="ms-1"><?php echo e($entregaHoje['codigo']); ?></strong>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel">
            <h2 class="panel-header">
                <span>
                    <i class="bi bi-table me-1"></i>
                    Detalhamento das entregas
                </span>
                <span><?php echo e($entregasDetalhadas->count()); ?> registro(s)</span>
            </h2>

            <?php if($entregasDetalhadas->isEmpty()): ?>
                <div class="empty-panel">
                    Não existem entregas na janela selecionada.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dashboard-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Entrega</th>
                                <th>Data / janela</th>
                                <th>Cliente / contato</th>
                                <th>Bairro / cidade</th>
                                <th>Produtos</th>
                                <th>Qtd. prevista</th>
                                <th>Veículo / motorista</th>
                                <th>Romaneio</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $entregasDetalhadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e($entrega['atrasada'] ? 'row-overdue' : ''); ?>">
                                    <td class="text-center"><?php echo e($indice + 1); ?></td>
                                    <td class="fw-bold"><?php echo e($entrega['codigo']); ?></td>
                                    <td>
                                        <strong><?php echo e($entrega['data_formatada']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo e($entrega['dia_semana']); ?> · <?php echo e($entrega['periodo_rotulo']); ?>

                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($entrega['cliente']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo e($entrega['telefone']); ?>

                                        </small>
                                    </td>
                                    <td>
                                        <?php echo e($entrega['bairro']); ?>

                                        <small class="d-block text-muted">
                                            <?php echo e($entrega['cidade']); ?>

                                        </small>
                                    </td>
                                    <td title="<?php echo e($entrega['produtos']); ?>">
                                        <?php echo e($limitarTexto($entrega['produtos'], 48)); ?>

                                    </td>
                                    <td class="text-end fw-bold">
                                        <?php echo e($formatarQuantidade($entrega['quantidade_prevista'])); ?>

                                    </td>
                                    <td>
                                        <strong><?php echo e($entrega['veiculo']); ?></strong>
                                        <small class="d-block text-muted">
                                            <?php echo e($entrega['motorista']); ?>

                                        </small>
                                    </td>
                                    <td>
                                        <?php if($entrega['romaneio_codigo']): ?>
                                            <strong><?php echo e($entrega['romaneio_codigo']); ?></strong>
                                            <small class="d-block text-muted">
                                                <?php echo e(str_replace('_', ' ', $entrega['romaneio_status'] ?? '')); ?>

                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Não criado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-pill <?php echo e($classeStatus($entrega['status'])); ?>">
                                            <?php echo e($entrega['status_rotulo']); ?>

                                        </span>

                                        <?php if($entrega['atrasada']): ?>
                                            <small class="d-block text-danger fw-bold mt-1">
                                                Atrasada
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('entregas.show', $entrega['id'])); ?>"
                                           class="btn btn-outline-primary btn-sm"
                                           title="Consultar entrega">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
    (function () {
        const configuracaoMapa = {
            tileUrl: <?php echo json_encode((string) config('openstreetmap.tile_url'), 15, 512) ?>,
            attribution: <?php echo json_encode((string) config('openstreetmap.attribution'), 15, 512) ?>,
            geocodingUrl: <?php echo json_encode((string) config('openstreetmap.geocoding_url'), 15, 512) ?>,
            center: [
                <?php echo e((float) config('openstreetmap.center.lat')); ?>,
                <?php echo e((float) config('openstreetmap.center.lng')); ?>,
            ],
            zoom: <?php echo e((int) config('openstreetmap.zoom')); ?>,
            maxZoom: <?php echo e((int) config('openstreetmap.max_zoom')); ?>,
            intervaloConsulta: <?php echo e((int) config('openstreetmap.geocoding_interval_ms')); ?>,
            diasCache: <?php echo e((int) config('openstreetmap.geocoding_cache_days')); ?>,
        };

        const entregasMapa = <?php echo json_encode($pontosMapa, 15, 512) ?>;
        const chaveCache = 'entregas-inteligentes-geocodificacao-v2';

        function aguardar(milissegundos) {
            return new Promise(function (resolver) {
                window.setTimeout(
                    resolver,
                    milissegundos
                );
            });
        }

        function carregarCache() {
            try {
                return JSON.parse(
                    window.localStorage.getItem(chaveCache)
                    || '{}'
                );
            } catch (erro) {
                console.warn(
                    'Cache de geocodificação inválido.',
                    erro
                );

                return {};
            }
        }

        function salvarCache(cache) {
            try {
                window.localStorage.setItem(
                    chaveCache,
                    JSON.stringify(cache)
                );
            } catch (erro) {
                console.warn(
                    'Não foi possível salvar o cache de geocodificação.',
                    erro
                );
            }
        }

        function normalizarCoordenadaMapa(
            valor,
            minimo,
            maximo
        ) {
            const coordenada = Number(
                String(valor ?? '')
                    .trim()
                    .replace(',', '.')
            );

            if (
                ! Number.isFinite(coordenada)
                || coordenada < minimo
                || coordenada > maximo
            ) {
                return null;
            }

            return coordenada;
        }

        function agruparEntregasPorCoordenada(entregas) {
            const grupos = new Map();

            entregas.forEach(function (entrega) {
                const latitude = normalizarCoordenadaMapa(
                    entrega.latitude_entrega,
                    -90,
                    90
                );

                const longitude = normalizarCoordenadaMapa(
                    entrega.longitude_entrega,
                    -180,
                    180
                );

                if (
                    entrega.coordenada_confirmada !== true
                    || latitude === null
                    || longitude === null
                ) {
                    return;
                }

                const chave = 'coordenada:'
                    + latitude.toFixed(7)
                    + ':'
                    + longitude.toFixed(7);

                if (! grupos.has(chave)) {
                    grupos.set(chave, {
                        chave: chave,
                        endereco: entrega.endereco,
                        entregas: [],
                        latitude: latitude,
                        longitude: longitude,
                        coordenadaConfirmada: true,
                    });
                }

                const grupo = grupos.get(chave);

                grupo.entregas.push(entrega);
            });

            return Array.from(
                grupos.values()
            );
        }

        function montarConsultasEndereco(endereco) {
            const consultas = [];

            function adicionarConsulta(valor) {
                const consulta = String(valor || '')
                    .replace(/\s+/g, ' ')
                    .replace(/\s*,\s*/g, ', ')
                    .replace(/(?:,\s*){2,}/g, ', ')
                    .replace(/^,\s*|,\s*$/g, '')
                    .trim();

                if (
                    consulta !== ''
                    && ! consultas.includes(consulta)
                ) {
                    consultas.push(consulta);
                }
            }

            const original = String(endereco || '')
                .replace(/\s*\/\s*/g, ', ')
                .replace(/\bN(?:º|°|o)?\.?\s*(?=\d)/gi, '')
                .trim();

            const partes = original
                .split(',')
                .map(parte => parte.trim())
                .filter(Boolean);

            const semComplemento = partes
                .filter(
                    parte => ! /^(?:casa|ap(?:to|artamento)?|bloco|fundos|sala|loja|galp[aã]o)\b/i.test(parte)
                );

            const semNumero = semComplemento
                .filter(
                    parte => ! /^\d+[a-z-]*$/i.test(parte)
                );

            const cepEncontrado = original.match(
                /\b\d{5}-?\d{3}\b/
            );

            adicionarConsulta(
                original + ', Brasil'
            );

            adicionarConsulta(
                semComplemento.join(', ')
                    + ', Brasil'
            );

            adicionarConsulta(
                semNumero.join(', ')
                    + ', Brasil'
            );

            if (cepEncontrado) {
                adicionarConsulta(
                    cepEncontrado[0] + ', Brasil'
                );
            }

            return consultas;
        }

        function definirCorMarcador(entregasDoEndereco) {
            if (
                entregasDoEndereco.some(
                    entrega => entrega.atrasada
                )
            ) {
                return '#dc3e3e';
            }

            if (
                entregasDoEndereco.every(
                    entrega => entrega.encerrada
                )
            ) {
                return '#249654';
            }

            return '#f48120';
        }

        function criarIconeMarcador(numero, cor) {
            return L.divIcon({
                className: 'delivery-map-div-icon',
                html: '<div class="delivery-map-pin" style="background:'
                    + cor
                    + '"><span>'
                    + numero
                    + '</span></div>',
                iconAnchor: [
                    15,
                    30,
                ],
                iconSize: [
                    30,
                    30,
                ],
                popupAnchor: [
                    0,
                    -27,
                ],
            });
        }

        function criarIconeSobreposicao(total) {
            return L.divIcon({
                className: 'delivery-map-div-icon',
                html: '<div class="delivery-map-overlap"><strong>'
                    + total
                    + '</strong><small>pontos</small></div>',
                iconAnchor: [
                    21,
                    21,
                ],
                iconSize: [
                    42,
                    42,
                ],
            });
        }

        function criarConteudoInformacao(grupo) {
            const conteudo = document.createElement('div');
            conteudo.className = 'map-info-window';

            const endereco = document.createElement('strong');
            endereco.className = 'map-info-address';
            endereco.textContent = grupo.endereco;
            conteudo.append(endereco);

            grupo.entregas.forEach(function (entrega) {
                const linha = document.createElement('div');
                linha.className = 'map-info-delivery';

                const codigo = document.createElement('a');
                codigo.className = 'map-info-code';
                codigo.href = entrega.url;
                codigo.textContent = entrega.codigo;
                linha.append(codigo);

                const cliente = document.createElement('div');
                cliente.textContent = entrega.cliente;
                linha.append(cliente);

                const status = document.createElement('span');
                status.className = 'map-info-status';
                status.textContent = entrega.data
                    + ' · '
                    + entrega.periodo
                    + ' · '
                    + entrega.status;
                linha.append(status);

                conteudo.append(linha);
            });

            return conteudo;
        }

        function criarMarcadorIndividual(ponto, posicao) {
            const marcador = L.marker(
                posicao,
                {
                    icon: criarIconeMarcador(
                        ponto.numero,
                        ponto.cor
                    ),
                    title: ponto.grupo.endereco,
                    zIndexOffset: ponto.atrasada
                        ? 1000
                        : 0,
                }
            );

            marcador.bindPopup(
                criarConteudoInformacao(
                    ponto.grupo
                ),
                {
                    maxWidth: 340,
                    minWidth: 230,
                }
            );

            return marcador;
        }

        function adicionarMarcadoresSobrepostos(
            mapa,
            pontos
        ) {
            const centro = pontos[0].posicao;
            const camadaExpandida = L.layerGroup();
            let expandido = false;

            const marcadorAgrupado = L.marker(
                centro,
                {
                    icon: criarIconeSobreposicao(
                        pontos.length
                    ),
                    title: pontos.length
                        + ' pontos coincidentes. Clique para expandir.',
                    zIndexOffset: 2000,
                }
            ).addTo(mapa);

            function recolher() {
                camadaExpandida.clearLayers();
                expandido = false;
            }

            function expandir() {
                camadaExpandida.clearLayers();

                const centroPixel = mapa.latLngToLayerPoint(
                    centro
                );

                const raio = Math.max(
                    42,
                    pontos.length * 18
                );

                pontos.forEach(function (ponto, indice) {
                    const angulo = (
                        (Math.PI * 2 * indice)
                        / pontos.length
                    ) - (Math.PI / 2);

                    const pontoPixel = L.point(
                        centroPixel.x
                            + Math.cos(angulo) * raio,
                        centroPixel.y
                            + Math.sin(angulo) * raio
                    );

                    const posicaoExpandida = mapa.layerPointToLatLng(
                        pontoPixel
                    );

                    L.polyline(
                        [
                            centro,
                            posicaoExpandida,
                        ],
                        {
                            color: '#072b62',
                            dashArray: '4,4',
                            interactive: false,
                            opacity: .7,
                            weight: 1.5,
                        }
                    ).addTo(camadaExpandida);

                    criarMarcadorIndividual(
                        ponto,
                        posicaoExpandida
                    ).addTo(camadaExpandida);
                });

                camadaExpandida.addTo(mapa);
                expandido = true;
            }

            marcadorAgrupado.on(
                'click',
                function () {
                    if (expandido) {
                        recolher();
                        return;
                    }

                    expandir();
                }
            );

            mapa.on(
                'zoomstart',
                recolher
            );
        }

        async function inicializarMapaEntregasInteligentes() {
            const elemento = document.getElementById(
                'mapa-entregas-inteligentes'
            );

            const mensagem = document.getElementById(
                'mapa-entregas-mensagem'
            );

            if (! elemento) {
                return;
            }

            if (typeof window.L === 'undefined') {
                elemento.innerHTML = '<div class="delivery-map-message text-danger"><i class="bi bi-exclamation-triangle fs-4"></i><strong>Não foi possível carregar o Leaflet.</strong><span>Verifique a conexão com a internet.</span></div>';

                if (mensagem) {
                    mensagem.textContent = 'Falha ao carregar o mapa.';
                }

                return;
            }

            const mapa = L.map(
                elemento,
                {
                    zoomControl: true,
                }
            ).setView(
                configuracaoMapa.center,
                configuracaoMapa.zoom
            );

            L.tileLayer(
                configuracaoMapa.tileUrl,
                {
                    attribution: configuracaoMapa.attribution,
                    maxZoom: configuracaoMapa.maxZoom,
                }
            ).addTo(mapa);

            const grupos = agruparEntregasPorCoordenada(
                entregasMapa
            );

            if (grupos.length === 0) {
                if (mensagem) {
                    mensagem.textContent = 'Nenhuma entrega possui coordenadas confirmadas.';
                }

                return;
            }

            const cache = carregarCache();
            const validadeCache = configuracaoMapa.diasCache
                * 24
                * 60
                * 60
                * 1000;

            const limites = L.latLngBounds([]);
            let proximaConsultaEm = 0;
            let localizados = 0;
            let naoLocalizados = 0;
            let falhasComunicacao = 0;
            const pontosLocalizados = [];

            const envoltorioMapa = elemento.closest(
                '.delivery-map-wrap'
            );

            const botaoCentralizar = document.getElementById(
                'mapa-entregas-centralizar'
            );

            const botaoTelaCheia = document.getElementById(
                'mapa-entregas-tela-cheia'
            );

            function centralizarMapa() {
                mapa.invalidateSize({
                    pan: false,
                });

                if (localizados === 1) {
                    mapa.setView(
                        limites.getCenter(),
                        15
                    );

                    return;
                }

                if (localizados > 1) {
                    mapa.fitBounds(
                        limites,
                        {
                            maxZoom: 15,
                            padding: [
                                42,
                                42,
                            ],
                        }
                    );

                    return;
                }

                mapa.setView(
                    configuracaoMapa.center,
                    configuracaoMapa.zoom
                );
            }

            function definirMapaTelaCheia(expandido) {
                if (
                    ! envoltorioMapa
                    || ! botaoTelaCheia
                ) {
                    return;
                }

                envoltorioMapa.classList.toggle(
                    'is-fullscreen',
                    expandido
                );

                document.body.classList.toggle(
                    'delivery-map-fullscreen',
                    expandido
                );

                botaoTelaCheia.setAttribute(
                    'aria-pressed',
                    expandido ? 'true' : 'false'
                );

                botaoTelaCheia.title = expandido
                    ? 'Minimizar mapa'
                    : 'Expandir mapa';

                const icone = botaoTelaCheia.querySelector(
                    'i'
                );

                const rotulo = botaoTelaCheia.querySelector(
                    'span'
                );

                if (icone) {
                    icone.className = expandido
                        ? 'bi bi-arrows-angle-contract'
                        : 'bi bi-arrows-fullscreen';
                }

                if (rotulo) {
                    rotulo.textContent = expandido
                        ? 'Minimizar'
                        : 'Expandir';
                }

                window.setTimeout(
                    function () {
                        mapa.invalidateSize({
                            pan: false,
                        });
                    },
                    80
                );
            }

            if (botaoCentralizar) {
                botaoCentralizar.addEventListener(
                    'click',
                    centralizarMapa
                );
            }

            if (botaoTelaCheia) {
                botaoTelaCheia.addEventListener(
                    'click',
                    function () {
                        const expandido = envoltorioMapa
                            && envoltorioMapa.classList.contains(
                                'is-fullscreen'
                            );

                        definirMapaTelaCheia(
                            ! expandido
                        );
                    }
                );
            }

            document.addEventListener(
                'keydown',
                function (evento) {
                    if (
                        evento.key === 'Escape'
                        && envoltorioMapa
                        && envoltorioMapa.classList.contains(
                            'is-fullscreen'
                        )
                    ) {
                        definirMapaTelaCheia(false);
                    }
                }
            );

            window.addEventListener(
                'resize',
                function () {
                    mapa.invalidateSize({
                        pan: false,
                    });
                }
            );

            async function geocodificar(grupo) {
                if (
                    grupo.coordenadaConfirmada === true
                    && Number.isFinite(grupo.latitude)
                    && Number.isFinite(grupo.longitude)
                ) {
                    return {
                        encontrado: true,
                        lat: grupo.latitude,
                        lng: grupo.longitude,
                        persistido: true,
                    };
                }

                const registroCache = cache[grupo.chave];

                if (
                    registroCache
                    && Number(registroCache.consultadoEm) > 0
                    && (
                        Date.now()
                        - Number(registroCache.consultadoEm)
                    ) < validadeCache
                ) {
                    return registroCache;
                }

                const consultas = montarConsultasEndereco(
                    grupo.endereco
                );

                for (
                    let indiceConsulta = 0;
                    indiceConsulta < consultas.length;
                    indiceConsulta++
                ) {
                    const espera = Math.max(
                        0,
                        proximaConsultaEm - Date.now()
                    );

                    if (espera > 0) {
                        await aguardar(espera);
                    }

                    proximaConsultaEm = Date.now()
                        + configuracaoMapa.intervaloConsulta;

                    const url = new URL(
                        configuracaoMapa.geocodingUrl
                    );

                    url.searchParams.set(
                        'q',
                        consultas[indiceConsulta]
                    );
                    url.searchParams.set(
                        'format',
                        'jsonv2'
                    );
                    url.searchParams.set(
                        'limit',
                        '1'
                    );
                    url.searchParams.set(
                        'countrycodes',
                        'br'
                    );
                    url.searchParams.set(
                        'accept-language',
                        'pt-BR'
                    );

                    const resposta = await fetch(
                        url.toString(),
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                    if (! resposta.ok) {
                        throw new Error(
                            'Geocodificação HTTP '
                            + resposta.status
                        );
                    }

                    const resultados = await resposta.json();
                    const resultado = resultados[0] || null;

                    if (! resultado) {
                        continue;
                    }

                    const registro = {
                        encontrado: true,
                        lat: Number(resultado.lat),
                        lng: Number(resultado.lon),
                        consultadoEm: Date.now(),
                    };

                    cache[grupo.chave] = registro;
                    salvarCache(cache);

                    return registro;
                }

                const registroNaoLocalizado = {
                    encontrado: false,
                    consultadoEm: Date.now(),
                };

                cache[grupo.chave] = registroNaoLocalizado;
                salvarCache(cache);

                return registroNaoLocalizado;
            }

            for (
                let indice = 0;
                indice < grupos.length;
                indice++
            ) {
                const grupo = grupos[indice];

                if (mensagem) {
                    mensagem.textContent = 'Carregando ponto '
                        + (indice + 1)
                        + ' de '
                        + grupos.length
                        + '...';
                }

                try {
                    const localizacao = await geocodificar(
                        grupo
                    );

                    if (
                        ! localizacao.encontrado
                        || ! Number.isFinite(localizacao.lat)
                        || ! Number.isFinite(localizacao.lng)
                    ) {
                        naoLocalizados++;
                        continue;
                    }

                    const posicao = L.latLng(
                        localizacao.lat,
                        localizacao.lng
                    );

                    pontosLocalizados.push({
                        atrasada: grupo.entregas.some(
                            entrega => entrega.atrasada
                        ),
                        cor: definirCorMarcador(
                            grupo.entregas
                        ),
                        grupo: grupo,
                        numero: indice + 1,
                        posicao: posicao,
                    });

                    limites.extend(posicao);
                    localizados++;
                } catch (erro) {
                    console.warn(
                        'Endereço não localizado:',
                        grupo.endereco,
                        erro
                    );

                    falhasComunicacao++;
                    naoLocalizados++;
                }
            }

            const pontosPorCoordenada = new Map();

            pontosLocalizados.forEach(function (ponto) {
                const chaveCoordenada = ponto.posicao.lat
                    .toFixed(6)
                    + ','
                    + ponto.posicao.lng.toFixed(6);

                if (! pontosPorCoordenada.has(chaveCoordenada)) {
                    pontosPorCoordenada.set(
                        chaveCoordenada,
                        []
                    );
                }

                pontosPorCoordenada
                    .get(chaveCoordenada)
                    .push(ponto);
            });

            let agrupamentosSobrepostos = 0;

            pontosPorCoordenada.forEach(function (pontos) {
                if (pontos.length === 1) {
                    criarMarcadorIndividual(
                        pontos[0],
                        pontos[0].posicao
                    ).addTo(mapa);

                    return;
                }

                agrupamentosSobrepostos++;

                adicionarMarcadoresSobrepostos(
                    mapa,
                    pontos
                );
            });

            centralizarMapa();

            if (mensagem) {
                mensagem.textContent = localizados
                    + ' ponto(s) carregado(s) pelas coordenadas'
                    + (
                        naoLocalizados > 0
                            ? ' · '
                                + naoLocalizados
                                + ' não localizado(s)'
                            : ''
                    )
                    + (
                        falhasComunicacao > 0
                            ? ' · '
                                + falhasComunicacao
                                + ' falha(s) de comunicação'
                            : ''
                    )
                    + (
                        agrupamentosSobrepostos > 0
                            ? ' · '
                                + agrupamentosSobrepostos
                                + ' grupo(s) sobreposto(s): clique para expandir'
                            : ''
                    );
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener(
                'DOMContentLoaded',
                inicializarMapaEntregasInteligentes
            );
        } else {
            inicializarMapaEntregasInteligentes();
        }
    })();
</script>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const canvas = document.getElementById(
                'grafico-entregas-dia'
            );

            if (! canvas) {
                return;
            }

            const serie = <?php echo json_encode($graficoDiario->values(), 15, 512) ?>;
            const contexto = canvas.getContext('2d');

            function desenharGrafico() {
                const largura = Math.max(
                    320,
                    canvas.parentElement.clientWidth
                );
                const altura = 270;
                const escala = window.devicePixelRatio || 1;

                canvas.width = largura * escala;
                canvas.height = altura * escala;
                canvas.style.width = largura + 'px';
                canvas.style.height = altura + 'px';

                contexto.setTransform(
                    escala,
                    0,
                    0,
                    escala,
                    0,
                    0
                );

                contexto.clearRect(0, 0, largura, altura);

                const margem = {
                    topo: 18,
                    direita: 44,
                    inferior: 35,
                    esquerda: 34,
                };

                const areaLargura = largura
                    - margem.esquerda
                    - margem.direita;

                const areaAltura = altura
                    - margem.topo
                    - margem.inferior;

                const maiorTotal = Math.max(
                    1,
                    ...serie.map(
                        item => Number(item.total) || 0
                    )
                );

                contexto.font = '10px Arial';
                contexto.textAlign = 'right';
                contexto.fillStyle = '#637083';
                contexto.strokeStyle = '#dce2e8';
                contexto.lineWidth = 1;

                for (let indice = 0; indice <= 4; indice++) {
                    const y = margem.topo
                        + (areaAltura / 4) * indice;

                    contexto.beginPath();
                    contexto.moveTo(margem.esquerda, y);
                    contexto.lineTo(
                        largura - margem.direita,
                        y
                    );
                    contexto.stroke();

                    const valor = Math.round(
                        maiorTotal * (1 - indice / 4)
                    );

                    contexto.fillText(
                        String(valor),
                        margem.esquerda - 6,
                        y + 3
                    );
                }

                const larguraGrupo = areaLargura
                    / Math.max(1, serie.length);

                const larguraBarra = Math.max(
                    4,
                    Math.min(18, larguraGrupo * .26)
                );

                const pontosEficiencia = [];

                serie.forEach(function (item, indice) {
                    const centroX = margem.esquerda
                        + larguraGrupo * indice
                        + larguraGrupo / 2;

                    const total = Number(item.total) || 0;
                    const concluidas = Number(item.concluidas) || 0;
                    const eficiencia = Number(item.eficiencia) || 0;

                    const alturaTotal = total
                        / maiorTotal
                        * areaAltura;

                    const alturaConcluida = concluidas
                        / maiorTotal
                        * areaAltura;

                    contexto.fillStyle = '#2267bd';
                    contexto.fillRect(
                        centroX - larguraBarra - 1,
                        margem.topo + areaAltura - alturaTotal,
                        larguraBarra,
                        alturaTotal
                    );

                    contexto.fillStyle = '#249654';
                    contexto.fillRect(
                        centroX + 1,
                        margem.topo + areaAltura - alturaConcluida,
                        larguraBarra,
                        alturaConcluida
                    );

                    pontosEficiencia.push({
                        x: centroX,
                        y: margem.topo
                            + areaAltura
                            - eficiencia / 100 * areaAltura,
                    });

                    contexto.fillStyle = item.rotulo === 'D+0'
                        ? '#072b62'
                        : '#5f6b7a';
                    contexto.font = item.rotulo === 'D+0'
                        ? 'bold 9px Arial'
                        : '9px Arial';
                    contexto.textAlign = 'center';
                    contexto.fillText(
                        item.rotulo,
                        centroX,
                        altura - 17
                    );
                });

                contexto.strokeStyle = '#f48120';
                contexto.lineWidth = 2;
                contexto.beginPath();

                pontosEficiencia.forEach(function (ponto, indice) {
                    if (indice === 0) {
                        contexto.moveTo(ponto.x, ponto.y);
                    } else {
                        contexto.lineTo(ponto.x, ponto.y);
                    }
                });

                contexto.stroke();

                pontosEficiencia.forEach(function (ponto) {
                    contexto.fillStyle = '#f48120';
                    contexto.beginPath();
                    contexto.arc(
                        ponto.x,
                        ponto.y,
                        3,
                        0,
                        Math.PI * 2
                    );
                    contexto.fill();
                });

                contexto.fillStyle = '#637083';
                contexto.font = '9px Arial';
                contexto.textAlign = 'left';
                contexto.fillText(
                    '100%',
                    largura - margem.direita + 6,
                    margem.topo + 3
                );
                contexto.fillText(
                    '0%',
                    largura - margem.direita + 6,
                    margem.topo + areaAltura
                );
            }

            desenharGrafico();

            let temporizador = null;

            window.addEventListener(
                'resize',
                function () {
                    window.clearTimeout(temporizador);
                    temporizador = window.setTimeout(
                        desenharGrafico,
                        120
                    );
                }
            );
        }
    );
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas_inteligentes/index.blade.php ENDPATH**/ ?>