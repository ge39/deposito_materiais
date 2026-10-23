



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
                'telefone' => (string) ($entrega['telefone'] ?? 'Não informado'),
                'responsavel_recebimento' => (string) ($entrega['responsavel_recebimento'] ?? 'Não informado'),
                'observacao_entrega' => $entrega['observacao_entrega'] ?? null,
                'endereco' => (string) $entrega['endereco'],
                'endereco_chave' => (string) $entrega['endereco_chave'],
                'bairro' => trim((string) ($entrega['bairro'] ?? '')),
                'latitude_entrega' =>
                    $entrega['latitude_entrega'],
                'longitude_entrega' =>
                    $entrega['longitude_entrega'],
                'coordenada_confirmada' =>
                    (bool) $entrega['coordenada_confirmada'],
                'data_chave' => (string) $entrega['data_chave'],
                'data' => (string) $entrega['data_formatada'],
                'periodo' => (string) $entrega['periodo_rotulo'],
                'status' => (string) $entrega['status_rotulo'],
                'atrasada' => (bool) $entrega['atrasada'],
                'dias_atraso' => (int) ($entrega['dias_atraso'] ?? 0),
                'encerrada' => (bool) $entrega['encerrada'],
                'status_chave' => (string) ($entrega['status'] ?? ''),
                'veiculo_id' => $entrega['veiculo_id'] ?? null,
                'veiculo' => (string) ($entrega['veiculo'] ?? 'Não definido'),
                'veiculo_modelo' => (string) ($entrega['veiculo_modelo'] ?? ''),
                'veiculo_tipo' => (string) ($entrega['veiculo_tipo'] ?? ''),
                'veiculo_carroceria' => (string) ($entrega['veiculo_carroceria'] ?? ''),
                'veiculo_possui_munck' => (bool) ($entrega['veiculo_possui_munck'] ?? false),
                'veiculo_carroceria_aberta' => (bool) ($entrega['veiculo_carroceria_aberta'] ?? false),
                'veiculo_carroceria_fechada' => (bool) ($entrega['veiculo_carroceria_fechada'] ?? false),
                'motorista_id' => $entrega['motorista_id'] ?? null,
                'motorista' => (string) ($entrega['motorista'] ?? 'Não definido'),
                'romaneio_id' => $entrega['romaneio_id'] ?? null,
                'romaneio_codigo' => (string) ($entrega['romaneio_codigo'] ?? ''),
                'romaneio_status' => (string) ($entrega['romaneio_status'] ?? ''),
                'ordem_rota' => $entrega['ordem_rota'] ?? null,
                'ordem_inteligente' => $entrega['ordem_inteligente'] ?? null,
                'ordem_mapa' => $entrega['ordem_mapa'] ?? null,
                'distancia_mapa_km' => $entrega['distancia_mapa_km'] ?? null,
                'distancia_anterior_km' => $entrega['distancia_anterior_km'] ?? null,
                'tempo_anterior_segundos' => $entrega['tempo_anterior_segundos'] ?? null,
                'tempo_anterior_minutos' => $entrega['tempo_anterior_minutos'] ?? null,
                'tempo_mapa_segundos' => $entrega['tempo_mapa_segundos'] ?? null,
                'tempo_mapa_minutos' => $entrega['tempo_mapa_minutos'] ?? null,
                'fonte_distancia' => (string) ($entrega['fonte_distancia'] ?? 'geografica'),
                'fonte_distancia_mapa' => (string) ($entrega['fonte_distancia_mapa'] ?? 'geografica'),
                'agrupamento_rota' => $entrega['agrupamento_rota'] ?? null,
                'concentracao_regional' => (int) ($entrega['concentracao_regional'] ?? 1),
                'concentracao_bairro' => (int) ($entrega['concentracao_bairro'] ?? 1),
                'concentracao_cep' => (int) ($entrega['concentracao_cep'] ?? 1),
                'concentracao_100m' => (int) ($entrega['concentracao_100m'] ?? 1),
                'liberado_em' => $entrega['liberado_em'] ?? null,
                'saida_em' => $entrega['saida_em'] ?? null,
                'restricoes_rota' => $entrega['restricoes_rota'] ?? [],
                'url' => route(
                    'entregas.show',
                    $entrega['id']
                ),
            ]
        )
        ->values();

    $cargasPorCaminhao = $entregasDetalhadas
        ->groupBy(function (array $entrega): string {
            $veiculo = trim((string) ($entrega['veiculo'] ?? ''));
            $motorista = trim((string) ($entrega['motorista'] ?? ''));
            $romaneio = trim((string) ($entrega['romaneio_codigo'] ?? ''));

            if (
                $veiculo === ''
                || $veiculo === 'Não definido'
            ) {
                return 'sem-veiculo';
            }

            return implode('|', [
                $veiculo,
                $motorista,
                $romaneio,
            ]);
        })
        ->map(function ($entregas, string $chave): array {
            $primeira = $entregas->first();
            $semVeiculo = $chave === 'sem-veiculo';

            $datasEntrega = $entregas
                ->groupBy('data_chave')
                ->map(function ($entregasData, string $dataChave): array {
                    $primeiraData = $entregasData->first();

                    return [
                        'chave' => $dataChave,
                        'formatada' => \Carbon\CarbonImmutable::parse(
                            $dataChave
                        )->format('d/m'),
                        'formatada_completa' => (string) (
                            $primeiraData['data_formatada']
                            ?? \Carbon\CarbonImmutable::parse(
                                $dataChave
                            )->format('d/m/Y')
                        ),
                        'atrasada' => $entregasData->contains(
                            fn (array $entrega): bool =>
                                (bool) ($entrega['atrasada'] ?? false)
                        ),
                    ];
                })
                ->sortBy('chave')
                ->values();

            return [
                'chave' => $chave,
                'sem_veiculo' => $semVeiculo,
                'veiculo' => $semVeiculo
                    ? 'Veículo não definido'
                    : ($primeira['veiculo'] ?? 'Não definido'),
                'veiculo_modelo' => $primeira['veiculo_modelo'] ?? null,
                'motorista' => $semVeiculo
                    ? 'Motorista não definido'
                    : ($primeira['motorista'] ?? 'Não definido'),
                'romaneio_codigo' => $primeira['romaneio_codigo'] ?? null,
                'romaneio_status' => $primeira['romaneio_status'] ?? null,
                'total_entregas' => $entregas->count(),
                'quantidade_prevista' => $entregas->sum('quantidade_prevista'),
                'atrasadas' => $entregas->where('atrasada', true)->count(),
                'datas_entrega' => $datasEntrega,
                'entregas' => $entregas->values(),
            ];
        })
        ->sortBy([
            ['sem_veiculo', 'asc'],
            ['veiculo', 'asc'],
            ['motorista', 'asc'],
        ])
        ->values();

    $hojeOperacional = \Carbon\CarbonImmutable::today(
        (string) config('app.timezone')
    );

    $entregasAtrasadasCard = $entregasDetalhadas
        ->where('atrasada', true)
        ->map(function (array $entrega) use (
            $hojeOperacional
        ): array {
            $dataPrevista = \Carbon\CarbonImmutable::parse(
                $entrega['data_chave']
            )->startOfDay();

            $entrega['dias_atraso_card'] = (int) $dataPrevista
                ->diffInDays(
                    $hojeOperacional,
                    true
                );

            return $entrega;
        })
        ->sortByDesc('dias_atraso_card')
        ->values();

    $motoristasEntregasCard = $entregasDetalhadas
        ->groupBy(function (array $entrega): string {
            $motorista = trim(
                (string) ($entrega['motorista'] ?? '')
            );

            return $motorista !== ''
                && $motorista !== 'Não definido'
                    ? $motorista
                    : 'Motorista não definido';
        })
        ->map(function ($entregas, string $motorista): array {
            $primeira = $entregas->first();

            return [
                'motorista' => $motorista,
                'veiculo' => $primeira['veiculo']
                    ?? 'Não definido',
                'total' => $entregas->count(),
                'atrasadas' => $entregas
                    ->where('atrasada', true)
                    ->count(),
                'entregas' => $entregas
                    ->sortBy([
                        ['data_chave', 'asc'],
                        ['ordem_rota', 'asc'],
                        ['codigo', 'asc'],
                    ])
                    ->values(),
            ];
        })
        ->sortBy('motorista')
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

    .smart-dashboard .routing-engine-status {
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .32);
        border-radius: 1rem;
        display: inline-flex;
        font-size: .65rem;
        font-weight: 800;
        gap: .3rem;
        margin-top: .42rem;
        padding: .2rem .5rem;
        text-transform: uppercase;
    }

    .smart-dashboard .routing-engine-status.is-online {
        background: rgba(36, 150, 84, .24);
        color: #d8ffe7;
    }

    .smart-dashboard .routing-engine-status.is-fallback {
        background: rgba(244, 129, 32, .25);
        color: #fff0dc;
    }

    .smart-dashboard .route-source-badge {
        border: 1px solid transparent;
        border-radius: .8rem;
        display: inline-flex;
        font-size: .55rem;
        font-weight: 900;
        line-height: 1;
        margin-top: .2rem;
        padding: .2rem .38rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .smart-dashboard .route-source-badge.is-valhalla {
        background: #e6f0ff;
        border-color: #92b9ec;
        color: #174f97;
    }

    .smart-dashboard .route-source-badge.is-geographic {
        background: #fff3cd;
        border-color: #e5c45f;
        color: #765900;
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

    .smart-dashboard .operation-cards-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: .75rem;
    }

    .smart-dashboard .operation-card-list {
        display: grid;
        gap: .5rem;
        max-height: 340px;
        overflow-y: auto;
        padding: .7rem;
    }

    .smart-dashboard .operation-card-row {
        align-items: center;
        background: #fff;
        border: 1px solid #dce2e8;
        border-left: .28rem solid var(--dash-blue);
        border-radius: .35rem;
        display: grid;
        gap: .55rem;
        grid-template-columns: minmax(155px, .85fr) minmax(210px, 1.25fr) auto;
        padding: .52rem .58rem;
    }

    .smart-dashboard .operation-card-row.is-overdue {
        background: #fff4f4;
        border-left-color: var(--dash-red);
    }

    .smart-dashboard .operation-card-title {
        color: #10233e;
        display: block;
        font-size: .72rem;
        font-weight: 850;
        line-height: 1.25;
        text-decoration: none;
    }

    .smart-dashboard .operation-card-detail {
        color: #667386;
        display: block;
        font-size: .64rem;
        line-height: 1.35;
        margin-top: .12rem;
    }

    .smart-dashboard .operation-card-deliveries {
        color: #526174;
        font-size: .64rem;
        line-height: 1.45;
        min-width: 0;
    }

    .smart-dashboard .operation-card-badge {
        background: #fbe5e7;
        border: 1px solid #e69ba3;
        border-radius: 1rem;
        color: #9c2330;
        font-size: .61rem;
        font-weight: 850;
        padding: .22rem .5rem;
        text-align: center;
        white-space: nowrap;
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

    .smart-dashboard .delivery-route-panel {
        background: rgba(255, 255, 255, .98);
        border: 1px solid #cbd5e1;
        border-radius: .42rem;
        box-shadow: 0 3px 14px rgba(15, 23, 42, .24);
        display: none;
        left: .65rem;
        max-height: calc(100% - 1.3rem);
        overflow: hidden;
        position: absolute;
        top: .65rem;
        width: min(380px, calc(100vw - 1.3rem));
        z-index: 1000;
    }

    .smart-dashboard .delivery-map-wrap.is-fullscreen
    .delivery-route-panel {
        display: block;
    }

    .smart-dashboard .delivery-route-panel.is-collapsed {
        width: min(380px, calc(100vw - 1.3rem));
    }

    .smart-dashboard .delivery-route-panel-header {
        align-items: center;
        background: var(--dash-navy);
        color: #fff;
        display: flex;
        gap: .45rem;
        justify-content: space-between;
        min-height: 38px;
        padding: .42rem .55rem;
    }

    .smart-dashboard .delivery-route-panel-header strong {
        font-size: .72rem;
        text-transform: uppercase;
    }

    .smart-dashboard .delivery-route-panel-toggle {
        align-items: center;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .42);
        border-radius: .25rem;
        color: #fff;
        display: inline-flex;
        height: 26px;
        justify-content: center;
        width: 28px;
    }

    .smart-dashboard .delivery-route-panel.is-collapsed
    .delivery-route-panel-body {
        display: none;
    }

    .smart-dashboard .delivery-route-panel-body {
        max-height: 238px;
        overflow-y: auto;
        padding: .42rem;
    }

    .smart-dashboard .delivery-map-wrap.is-fullscreen
    .delivery-route-panel-body {
        max-height: calc(100vh - 58px);
    }

    .smart-dashboard .delivery-route-accordion {
        border: 1px solid #d7dee7;
        border-radius: .34rem;
        margin-bottom: .38rem;
        overflow: hidden;
    }

    .smart-dashboard .delivery-route-accordion:last-child {
        margin-bottom: 0;
    }

    .smart-dashboard .delivery-route-accordion summary {
        align-items: center;
        background: #eef4fb;
        color: var(--dash-navy);
        cursor: pointer;
        display: flex;
        font-size: .66rem;
        font-weight: 850;
        gap: .35rem;
        justify-content: space-between;
        list-style: none;
        padding: .42rem .48rem;
    }

    .smart-dashboard .delivery-route-accordion summary::-webkit-details-marker {
        display: none;
    }

    .smart-dashboard .delivery-route-summary-identity {
        display: grid;
        gap: .12rem;
        min-width: 0;
    }

    .smart-dashboard .delivery-route-summary-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .smart-dashboard .delivery-route-summary-dates {
        align-items: center;
        color: #5f6b7a;
        display: flex;
        flex-wrap: wrap;
        font-size: .56rem;
        font-weight: 700;
        gap: .13rem;
        line-height: 1.2;
    }

    .smart-dashboard .delivery-route-summary-neighborhoods {
        color: #5f6b7a;
        font-size: .56rem;
        font-weight: 700;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .smart-dashboard .delivery-route-summary-neighborhoods strong {
        color: #34445a;
        font-weight: 900;
    }

    .smart-dashboard .delivery-route-summary-date {
        color: #34445a;
        font-weight: 900;
    }

    .smart-dashboard .delivery-route-summary-date.is-overdue {
        color: var(--dash-red);
    }

    .smart-dashboard .delivery-route-summary-quantity {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .smart-dashboard .delivery-route-accordion-content {
        padding: .42rem;
    }

    .smart-dashboard .delivery-route-legend {
        background: #f7f9fc;
        border: 1px solid #d7dee7;
        border-radius: .34rem;
        display: grid;
        gap: .28rem;
        margin-bottom: .42rem;
        padding: .42rem;
    }

    .smart-dashboard .delivery-route-legend-line {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
    }

    .smart-dashboard .delivery-route-legend-label {
        color: #5f6b7a;
        font-size: .55rem;
        font-weight: 850;
        margin-right: .1rem;
        text-transform: uppercase;
    }

    .smart-dashboard .delivery-route-rule {
        color: #4f5d70;
        font-size: .52rem;
        line-height: 1.35;
    }

    .smart-dashboard .delivery-route-stop {
        background: #fff;
        border-bottom: 1px solid #e1e7ee;
        cursor: pointer;
        display: grid;
        gap: .12rem;
        grid-template-columns: 28px 1fr;
        padding: .4rem .15rem;
    }

    .smart-dashboard .delivery-route-stop:last-of-type {
        border-bottom: 0;
    }

    .smart-dashboard .delivery-route-stop:hover {
        background: #f5f9ff;
    }

    .smart-dashboard .delivery-route-stop.is-overdue {
        border-left: 3px solid #dc3545;
    }

    .smart-dashboard .delivery-route-stop.is-today {
        border-left: 3px solid #198754;
    }

    .smart-dashboard .delivery-route-stop.is-normal {
        border-left: 3px solid #0d6efd;
    }

    .smart-dashboard .delivery-route-order {
        align-items: center;
        background: var(--dash-navy);
        border-radius: 50%;
        color: #fff;
        display: flex;
        font-size: .63rem;
        font-weight: 900;
        height: 25px;
        justify-content: center;
        width: 25px;
    }

    .smart-dashboard .delivery-route-stop strong,
    .smart-dashboard .delivery-route-stop span {
        display: block;
    }

    .smart-dashboard .delivery-route-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .22rem;
        margin: .18rem 0;
    }

    .smart-dashboard .delivery-route-badge {
        border: 1px solid transparent;
        border-radius: .7rem;
        display: inline-flex !important;
        font-size: .51rem;
        font-weight: 900;
        line-height: 1;
        padding: .18rem .34rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .smart-dashboard .delivery-route-badge.is-overdue {
        background: #fde7e9;
        border-color: #ef9ba4;
        color: #a91f2d;
    }

    .smart-dashboard .delivery-route-badge.is-today,
    .smart-dashboard .delivery-route-badge.is-near {
        background: #e4f5eb;
        border-color: #8ec9a5;
        color: #116b39;
    }

    .smart-dashboard .delivery-route-badge.is-normal {
        background: #e6f0ff;
        border-color: #92b9ec;
        color: #174f97;
    }

    .smart-dashboard .delivery-route-badge.is-medium {
        background: #fff3cd;
        border-color: #e5c45f;
        color: #765900;
    }

    .smart-dashboard .delivery-route-badge.is-far {
        background: #f4e7fb;
        border-color: #c9a2df;
        color: #70408b;
    }

    .smart-dashboard .delivery-route-stop strong {
        color: #17365f;
        font-size: .65rem;
    }

    .smart-dashboard .delivery-route-stop span {
        color: #596779;
        font-size: .59rem;
        line-height: 1.25;
    }

    .smart-dashboard .delivery-route-stop
    > span.delivery-route-order {
        color: #fff;
        display: flex;
        font-size: .7rem;
        line-height: 1;
    }

    .smart-dashboard .delivery-route-empty {
        color: #667085;
        font-size: .65rem;
        padding: .75rem .35rem;
        text-align: center;
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

    .smart-dashboard .delivery-map-marker-wrap {
        height: 30px;
        position: relative;
        width: 30px;
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

    .smart-dashboard .delivery-map-status-label {
        background: rgba(255, 255, 255, .98);
        border: 2px solid currentColor;
        border-radius: 999px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, .2);
        font-size: .45rem;
        font-weight: 900;
        left: 50%;
        line-height: 1;
        padding: .14rem .3rem;
        position: absolute;
        text-transform: uppercase;
        top: 31px;
        transform: translateX(-50%);
        white-space: nowrap;
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

    .smart-dashboard .map-info-indicator {
        border-radius: 999px;
        display: inline-block;
        font-size: .62rem;
        font-weight: 900;
        margin: .22rem 0 .12rem;
        padding: .18rem .42rem;
        text-transform: uppercase;
    }

    .smart-dashboard .map-info-indicator.is-overdue {
        background: #fee2e2;
        color: #b42318;
    }

    .smart-dashboard .map-info-indicator.is-today {
        background: #e4f5eb;
        color: #116b39;
    }

    .smart-dashboard .map-info-indicator.is-normal {
        background: #e5efff;
        color: #124f9e;
    }

    .smart-dashboard .delivery-map-depot-icon {
        background: transparent;
        border: 0;
    }

    .smart-dashboard .delivery-map-depot-pin {
        align-items: center;
        background: #072b62;
        border: 4px solid #fff;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .46);
        color: #fff;
        display: flex;
        flex-direction: column;
        height: 78px;
        justify-content: center;
        position: relative;
        width: 78px;
    }

    .smart-dashboard .delivery-map-depot-pin i {
        font-size: 1.65rem;
        line-height: 1;
    }

    .smart-dashboard .delivery-map-depot-pin .delivery-map-depot-name {
        display: block;
        font-size: .48rem;
        font-weight: 900;
        letter-spacing: .025em;
        line-height: 1.05;
        margin-top: .18rem;
        max-width: 64px;
        overflow: hidden;
        text-align: center;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .smart-dashboard .delivery-map-depot-count {
        align-items: center;
        background: #f48120;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        font-size: .62rem;
        font-weight: 900;
        height: 24px;
        justify-content: center;
        position: absolute;
        right: -8px;
        top: -7px;
        width: 24px;
    }

    .smart-dashboard .delivery-map-yard-line {
        stroke-dasharray: 4 5;
    }

    .smart-dashboard .delivery-map-vehicle-icon {
        background: transparent;
        border: 0;
    }

    .smart-dashboard .delivery-map-vehicle-pin {
        align-items: center;
        background: #0b6bcb;
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 3px 11px rgba(0, 0, 0, .42);
        color: #fff;
        display: flex;
        font-size: 1.25rem;
        height: 48px;
        justify-content: center;
        position: relative;
        width: 48px;
    }

    .smart-dashboard .delivery-map-vehicle-pin::after {
        background: inherit;
        bottom: -5px;
        content: '';
        height: 12px;
        left: 15px;
        position: absolute;
        transform: rotate(45deg);
        width: 12px;
        z-index: -1;
    }

    .smart-dashboard .delivery-map-vehicle-kind {
        background: rgba(255, 255, 255, .94);
        border-radius: .35rem;
        bottom: 2px;
        color: #082b60;
        font-size: .34rem;
        font-weight: 950;
        left: 50%;
        line-height: 1;
        padding: 1px 3px;
        position: absolute;
        text-transform: uppercase;
        transform: translateX(-50%);
        white-space: nowrap;
    }

    .smart-dashboard .delivery-map-vehicle-pin > i {
        transform: translateY(-4px);
    }

    .smart-dashboard .delivery-map-vehicle-order {
        align-items: center;
        background: #fff;
        border: 2px solid currentColor;
        border-radius: 50%;
        color: var(--dash-navy);
        display: flex;
        font-size: .62rem;
        font-weight: 950;
        height: 23px;
        justify-content: center;
        left: -9px;
        position: absolute;
        top: -8px;
        width: 23px;
        z-index: 3;
    }

    @media (max-width: 767.98px) {
        .smart-dashboard .delivery-route-panel {
            max-width: calc(100% - 1.3rem);
            width: 290px;
        }
    }

    .smart-dashboard .delivery-map-vehicle-label {
        align-items: center;
        background: rgba(255, 255, 255, .97);
        border: 2px solid var(--dash-navy);
        border-radius: .34rem;
        bottom: -29px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
        color: var(--dash-navy);
        display: flex;
        flex-direction: column;
        left: 50%;
        min-width: 74px;
        padding: .12rem .3rem;
        position: absolute;
        transform: translateX(-50%);
        white-space: nowrap;
    }

    .smart-dashboard .delivery-map-vehicle-plate {
        font-size: .53rem;
        font-weight: 900;
        line-height: 1.05;
    }

    .smart-dashboard .delivery-map-vehicle-status {
        border-radius: 999px;
        display: inline-block;
        font-size: .46rem;
        font-weight: 900;
        line-height: 1;
        margin-top: .1rem;
        padding: .1rem .28rem;
        text-transform: uppercase;
    }

    .smart-dashboard .vehicle-status-carregada {
        background: #e9ecef;
        color: #495057;
    }

    .smart-dashboard .vehicle-status-liberada {
        background: #fff0d6;
        color: #9a5700;
    }

    .smart-dashboard .vehicle-status-em-rota {
        background: #e5efff;
        color: #124f9e;
    }

    .smart-dashboard .vehicle-status-no-destino {
        background: #e1f4e8;
        color: #146c3a;
    }

    .smart-dashboard .vehicle-status-atrasada {
        background: #fee2e2;
        color: #b42318;
    }

    .smart-dashboard .map-vehicle-popup {
        font-size: .75rem;
        line-height: 1.4;
        min-width: 245px;
    }

    .smart-dashboard .map-vehicle-popup-title {
        border-bottom: 1px solid #dde3ea;
        color: var(--dash-navy);
        display: block;
        font-size: .86rem;
        margin-bottom: .42rem;
        padding-bottom: .3rem;
    }

    .smart-dashboard .map-vehicle-popup-row {
        display: grid;
        gap: .35rem;
        grid-template-columns: 78px 1fr;
        margin-bottom: .2rem;
    }

    .smart-dashboard .map-vehicle-popup-row span {
        color: #667085;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .smart-dashboard .map-popup-value-danger {
        color: #b42318;
    }

    .smart-dashboard .map-popup-value-success {
        color: #146c3a;
    }

    .smart-dashboard .map-popup-value-normal {
        color: #174f97;
    }

    .smart-dashboard .map-popup-value-medium {
        color: #8a6500;
    }

    .smart-dashboard .map-popup-value-far {
        color: #70408b;
    }

    .smart-dashboard .map-route-qr {
        align-items: center;
        border-top: 1px solid #dde3ea;
        display: flex;
        flex-direction: column;
        gap: .35rem;
        margin-top: .5rem;
        padding-top: .55rem;
        text-align: center;
    }

    .smart-dashboard .map-route-qr-code {
        background: #fff;
        border: 1px solid #d7dee7;
        border-radius: .35rem;
        padding: .35rem;
    }

    .smart-dashboard .map-route-qr-code img,
    .smart-dashboard .map-route-qr-code canvas {
        display: block;
        height: 160px !important;
        width: 160px !important;
    }

    .smart-dashboard .map-route-qr a {
        color: var(--dash-blue);
        font-size: .68rem;
        font-weight: 800;
        text-decoration: none;
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

    .smart-dashboard .truck-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: .75rem;
    }

    .smart-dashboard .truck-card {
        background: #fff;
        border: 1px solid #d5dce4;
        border-radius: .48rem;
        box-shadow: 0 .14rem .35rem rgba(16, 24, 40, .08);
        min-width: 0;
        overflow: hidden;
    }

    .smart-dashboard .truck-card.is-unassigned {
        border-color: #e5b85d;
    }

    .smart-dashboard .truck-card-head {
        align-items: flex-start;
        background: #f5f7fa;
        border-bottom: 1px solid #dce2e8;
        display: flex;
        gap: .7rem;
        justify-content: space-between;
        padding: .72rem .78rem;
    }

    .smart-dashboard .truck-card.is-unassigned .truck-card-head {
        background: #fff8e7;
    }

    .smart-dashboard .truck-identity {
        align-items: center;
        display: flex;
        gap: .62rem;
        min-width: 0;
    }

    .smart-dashboard .truck-icon {
        align-items: center;
        background: var(--dash-navy);
        border-radius: 50%;
        color: #fff;
        display: flex;
        flex: 0 0 40px;
        font-size: 1rem;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .smart-dashboard .truck-card.is-unassigned .truck-icon {
        background: var(--dash-orange);
    }

    .smart-dashboard .truck-name {
        color: #10233e;
        font-size: .82rem;
        font-weight: 850;
        line-height: 1.2;
    }

    .smart-dashboard .truck-model,
    .smart-dashboard .truck-driver,
    .smart-dashboard .truck-manifest,
    .smart-dashboard .truck-delivery-dates {
        color: #667386;
        font-size: .66rem;
        line-height: 1.3;
        margin-top: .12rem;
    }

    .smart-dashboard .truck-delivery-date {
        color: #34445a;
        font-weight: 800;
    }

    .smart-dashboard .truck-delivery-date.is-overdue {
        color: var(--dash-red);
    }

    .smart-dashboard .truck-delivery-date-separator {
        color: #8a96a6;
        padding: 0 .16rem;
    }

    .smart-dashboard .truck-summary {
        display: grid;
        flex: 0 0 auto;
        gap: .28rem;
        grid-template-columns: repeat(2, minmax(62px, 1fr));
    }

    .smart-dashboard .truck-summary-item {
        background: #fff;
        border: 1px solid #dce2e8;
        border-radius: .3rem;
        color: #657184;
        font-size: .58rem;
        padding: .28rem .38rem;
        text-align: center;
        text-transform: uppercase;
    }

    .smart-dashboard .truck-summary-item strong {
        color: #12243d;
        display: block;
        font-size: .76rem;
    }

    .smart-dashboard .truck-deliveries {
        display: grid;
        gap: .42rem;
        padding: .62rem .72rem .72rem;
    }

    .smart-dashboard .truck-delivery {
        align-items: center;
        border: 1px solid #e0e5eb;
        border-left: .24rem solid var(--dash-blue);
        border-radius: .3rem;
        display: grid;
        gap: .5rem;
        grid-template-columns: minmax(190px, .95fr) minmax(280px, 1.45fr) minmax(130px, .65fr) auto;
        padding: .48rem .55rem;
    }

    .smart-dashboard .truck-delivery.is-overdue {
        background: #fff4f4;
        border-left-color: var(--dash-red);
    }

    .smart-dashboard .truck-delivery-code {
        color: var(--dash-blue);
        font-size: .72rem;
        font-weight: 850;
        text-decoration: none;
    }

    .smart-dashboard .truck-customer-name {
        color: #17233a;
        display: block;
        font-size: .7rem;
        margin-top: .12rem;
    }

    .smart-dashboard .truck-delivery-note {
        color: #8a5a00 !important;
        margin-top: .14rem;
    }

    .smart-dashboard .truck-delivery-main,
    .smart-dashboard .truck-delivery-location,
    .smart-dashboard .truck-delivery-window {
        font-size: .67rem;
        line-height: 1.3;
        min-width: 0;
    }

    .smart-dashboard .truck-delivery-main small,
    .smart-dashboard .truck-delivery-location small,
    .smart-dashboard .truck-delivery-window small {
        color: #748091;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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

        .smart-dashboard .truck-grid {
            grid-template-columns: 1fr;
        }

        .smart-dashboard .truck-delivery {
            grid-template-columns: minmax(140px, .75fr) minmax(190px, 1.2fr) minmax(115px, .65fr) auto;
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
        .smart-dashboard .operation-cards-grid,
        .smart-dashboard .alerts-grid,
        .smart-dashboard .opportunity-grid {
            grid-template-columns: 1fr;
        }

        .smart-dashboard .operation-card-row {
            align-items: start;
            grid-template-columns: 1fr auto;
        }

        .smart-dashboard .operation-card-deliveries {
            grid-column: 1 / -1;
        }

        .smart-dashboard .panel-concentration {
            grid-column: auto;
        }

        .smart-dashboard .truck-card-head {
            flex-direction: column;
        }

        .smart-dashboard .truck-summary {
            width: 100%;
        }

        .smart-dashboard .truck-delivery {
            align-items: start;
            grid-template-columns: 1fr auto;
        }

        .smart-dashboard .truck-delivery-main,
        .smart-dashboard .truck-delivery-location,
        .smart-dashboard .truck-delivery-window {
            grid-column: 1 / -1;
        }
    }
</style>

<div class="smart-dashboard">
    <div class="dashboard-shell">
        <?php echo $__env->make('entregas.partials.alertas_sla', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <header class="dashboard-header">
            <div>
                <h1 class="dashboard-title">
                    <i class="bi bi-truck-front me-1"></i>
                    Painel de Gestão Logística
                </h1>

                <div class="dashboard-subtitle">
                    Entrega Inteligente · acompanhamento exclusivo das entregas em rota
                </div>

                <?php if((bool) ($roteirizador['disponivel'] ?? false)): ?>
                    <div class="routing-engine-status is-online"
                         title="Distâncias e tempos calculados pela malha rodoviária do Valhalla">
                        <i class="bi bi-sign-turn-right-fill"></i>
                        Valhalla ativo · <?php echo e((int) ($roteirizador['pontos_matriz'] ?? 0)); ?> pontos
                    </div>
                <?php else: ?>
                    <div class="routing-engine-status is-fallback"
                         title="<?php echo e($roteirizador['erro'] ?? 'Roteirizador rodoviário indisponível'); ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Cálculo geográfico de contingência
                    </div>
                <?php endif; ?>
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

        <section class="kpi-grid" aria-label="Indicadores da operação em rota">
            <article class="kpi-card kpi-blue">
                <div class="kpi-icon">
                    <i class="bi bi-sign-turn-right"></i>
                </div>
                <div>
                    <div class="kpi-label">Entregas ativas</div>
                    <div class="kpi-value"><?php echo e($indicadores['total'] ?? $entregasDetalhadas->count()); ?></div>
                    <div class="kpi-note">
                        Qtd. <?php echo e($formatarQuantidade($indicadores['quantidade_prevista'] ?? $entregasDetalhadas->sum('quantidade_prevista'))); ?>

                    </div>
                </div>
            </article>

            <article class="kpi-card kpi-orange">
                <div class="kpi-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="kpi-label">Em rota</div>
                    <div class="kpi-value"><?php echo e($indicadores['em_rota'] ?? $entregasDetalhadas->where('status', 'Em_rota')->count()); ?></div>
                    <div class="kpi-note">Deslocamento ou sequência de entregas</div>
                </div>
            </article>

            <article class="kpi-card kpi-cyan">
                <div class="kpi-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <div class="kpi-label">No destino</div>
                    <div class="kpi-value"><?php echo e($indicadores['no_destino'] ?? $entregasDetalhadas->where('status', 'No_destino')->count()); ?></div>
                    <div class="kpi-note">Atendimento no endereço do cliente</div>
                </div>
            </article>

            <article class="kpi-card kpi-red">
                <div class="kpi-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="kpi-label">Fora da janela</div>
                    <div class="kpi-value"><?php echo e($indicadores['atrasadas'] ?? $entregasDetalhadas->where('atrasada', true)->count()); ?></div>
                    <div class="kpi-note">Somente entregas ainda em operação</div>
                </div>
            </article>

            <article class="kpi-card kpi-green">
                <div class="kpi-icon">
                    <i class="bi bi-truck-front-fill"></i>
                </div>
                <div>
                    <div class="kpi-label">Veículos ativos</div>
                    <div class="kpi-value"><?php echo e($indicadores['veiculos_ativos'] ?? $entregasDetalhadas->where('veiculo', '!=', 'Não definido')->pluck('veiculo')->unique()->count()); ?></div>
                    <div class="kpi-note">Frota identificada nesta operação</div>
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
                                        <strong><?php echo e($periodo['em_rota']); ?></strong>
                                        Em rota
                                    </div>
                                    <div class="period-metric text-warning">
                                        <strong><?php echo e($periodo['no_destino']); ?></strong>
                                        No destino
                                    </div>
                                    <div class="period-metric text-danger">
                                        <strong><?php echo e($periodo['atrasadas']); ?></strong>
                                        Fora da janela
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
                        Mapa das entregas em rota
                    </span>
                    <span>
                        <?php echo e($pontosMapa->count()); ?> entrega(s) · OpenStreetMap
                    </span>
                </h2>

                <div class="panel-body">
                    <div class="delivery-map-wrap">
                        <div id="mapa-entregas-inteligentes"
                             role="region"
                             aria-label="Mapa das entregas em rota das entregas"></div>

                        <aside id="mapa-rotas-painel"
                               class="delivery-route-panel is-collapsed"
                               aria-label="Ordens das entregas em rota">
                            <div class="delivery-route-panel-header">
                                <strong class="delivery-route-panel-title">
                                    <i class="bi bi-list-ol me-1"></i>
                                    Ordens de entrega
                                </strong>
                                <button type="button"
                                        id="mapa-rotas-painel-toggle"
                                        class="delivery-route-panel-toggle"
                                        aria-expanded="false"
                                        title="Abrir ordens de entrega">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                            <div id="mapa-rotas-accordion"
                                 class="delivery-route-panel-body"></div>
                        </aside>

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
                                <i style="background:#6c757d"></i>
                                Carregada
                            </span>
                            <span>
                                <i style="background:#f48120"></i>
                                Liberada
                            </span>
                            <span>
                                <i style="background:#072b62"></i>
                                Em rota
                            </span>
                            <span>
                                <i style="background:#249654"></i>
                                No destino / concluída
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

        <section class="operation-cards-grid"
                 aria-label="Entregas atrasadas e motoristas">
            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-clock-history me-1"></i>
                        Entregas atrasadas
                    </span>
                    <span>
                        <?php echo e($entregasAtrasadasCard->count()); ?> entrega(s)
                    </span>
                </h2>

                <?php if($entregasAtrasadasCard->isEmpty()): ?>
                    <div class="empty-panel">
                        Nenhuma entrega atrasada na operação atual.
                    </div>
                <?php else: ?>
                    <div class="operation-card-list">
                        <?php $__currentLoopData = $entregasAtrasadasCard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entregaAtrasada): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="operation-card-row is-overdue">
                                <div>
                                    <a href="<?php echo e(route('entregas.show', $entregaAtrasada['id'])); ?>"
                                       class="operation-card-title">
                                        <?php echo e($entregaAtrasada['codigo']); ?>

                                    </a>
                                    <span class="operation-card-detail">
                                        <?php echo e($entregaAtrasada['cliente']); ?>

                                    </span>
                                    <span class="operation-card-detail">
                                        <i class="bi bi-telephone me-1"></i>
                                        <?php echo e($entregaAtrasada['telefone']); ?>

                                    </span>
                                </div>

                                <div>
                                    <strong class="operation-card-title">
                                        <?php echo e($entregaAtrasada['data_formatada']); ?> ·
                                        <?php echo e($entregaAtrasada['periodo_rotulo']); ?>

                                    </strong>
                                    <span class="operation-card-detail">
                                        <i class="bi bi-person-badge me-1"></i>
                                        <?php echo e($entregaAtrasada['motorista']); ?> ·
                                        <?php echo e($entregaAtrasada['veiculo']); ?>

                                    </span>
                                    <span class="operation-card-detail">
                                        <?php echo e($entregaAtrasada['status_rotulo']); ?>

                                    </span>
                                </div>

                                <span class="operation-card-badge">
                                    <?php echo e($entregaAtrasada['dias_atraso_card']); ?>

                                    <?php echo e($entregaAtrasada['dias_atraso_card'] === 1 ? 'dia' : 'dias'); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="panel">
                <h2 class="panel-header">
                    <span>
                        <i class="bi bi-person-badge me-1"></i>
                        Motoristas e suas entregas
                    </span>
                    <span>
                        <?php echo e($motoristasEntregasCard->count()); ?> motorista(s)
                    </span>
                </h2>

                <?php if($motoristasEntregasCard->isEmpty()): ?>
                    <div class="empty-panel">
                        Nenhum motorista vinculado à operação atual.
                    </div>
                <?php else: ?>
                    <div class="operation-card-list">
                        <?php $__currentLoopData = $motoristasEntregasCard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motoristaCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="operation-card-row <?php echo e($motoristaCard['atrasadas'] > 0 ? 'is-overdue' : ''); ?>">
                                <div>
                                    <strong class="operation-card-title">
                                        <?php echo e($motoristaCard['motorista']); ?>

                                    </strong>
                                    <span class="operation-card-detail">
                                        <i class="bi bi-truck me-1"></i>
                                        <?php echo e($motoristaCard['veiculo']); ?>

                                    </span>
                                    <span class="operation-card-detail">
                                        <?php echo e($motoristaCard['total']); ?> entrega(s)
                                    </span>
                                </div>

                                <div class="operation-card-deliveries">
                                    <?php $__currentLoopData = $motoristaCard['entregas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entregaMotorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div>
                                            <strong><?php echo e($entregaMotorista['codigo']); ?></strong>
                                            · <?php echo e($entregaMotorista['cliente']); ?>

                                            · <?php echo e($entregaMotorista['status_rotulo']); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <span class="status-pill <?php echo e($motoristaCard['atrasadas'] > 0 ? 'status-danger' : 'status-success'); ?>">
                                    <?php if($motoristaCard['atrasadas'] > 0): ?>
                                        <?php echo e($motoristaCard['atrasadas']); ?> atrasada(s)
                                    <?php else: ?>
                                        No prazo
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
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
                            Em rota
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



        <section class="panel mb-3">
            <h2 class="panel-header">
                <span>
                    <i class="bi bi-truck-front-fill me-1"></i>
                    Operação por caminhão
                </span>
                <span><?php echo e($cargasPorCaminhao->count()); ?> caminhão(ões) em operação</span>
            </h2>

            <?php if($cargasPorCaminhao->isEmpty()): ?>
                <div class="empty-panel">
                    Não existem entregas com status Em rota ou No destino na data selecionada.
                </div>
            <?php else: ?>
                <div class="truck-grid">
                    <?php $__currentLoopData = $cargasPorCaminhao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $carga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="truck-card <?php echo e($carga['sem_veiculo'] ? 'is-unassigned' : ''); ?>">
                            <header class="truck-card-head">
                                <div class="truck-identity">
                                    <div class="truck-icon">
                                        <i class="bi <?php echo e($carga['sem_veiculo'] ? 'bi-exclamation-triangle' : 'bi-truck-front-fill'); ?>"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="truck-name">
                                            <?php echo e($carga['veiculo']); ?>

                                        </div>

                                        <?php if($carga['veiculo_modelo']): ?>
                                            <div class="truck-model">
                                                <i class="bi bi-card-text me-1"></i>
                                                <?php echo e($carga['veiculo_modelo']); ?>

                                            </div>
                                        <?php endif; ?>

                                        <div class="truck-driver">
                                            <i class="bi bi-person-badge me-1"></i>
                                            Motorista: <strong><?php echo e($carga['motorista']); ?></strong>
                                        </div>

                                        <div class="truck-manifest">
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Romaneio:
                                            <strong><?php echo e($carga['romaneio_codigo'] ?: 'Não criado'); ?></strong>
                                            <?php if($carga['romaneio_status']): ?>
                                                · <?php echo e(str_replace('_', ' ', $carga['romaneio_status'])); ?>

                                            <?php endif; ?>
                                        </div>

                                        <?php if($carga['datas_entrega']->isNotEmpty()): ?>
                                            <div class="truck-delivery-dates">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                Datas das entregas:
                                                <?php $__currentLoopData = $carga['datas_entrega']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dataEntrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(! $loop->first): ?>
                                                        <span class="truck-delivery-date-separator">-</span>
                                                    <?php endif; ?>
                                                    <span class="truck-delivery-date <?php echo e($dataEntrega['atrasada'] ? 'is-overdue' : ''); ?>"
                                                          title="<?php echo e($dataEntrega['formatada_completa']); ?>">
                                                        <?php echo e($dataEntrega['formatada']); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="truck-summary">
                                    <div class="truck-summary-item">
                                        <strong><?php echo e($carga['total_entregas']); ?></strong>
                                        Entregas
                                    </div>
                                    <div class="truck-summary-item">
                                        <strong><?php echo e($formatarQuantidade($carga['quantidade_prevista'])); ?></strong>
                                        Quantidade
                                    </div>
                                    <?php if($carga['atrasadas'] > 0): ?>
                                        <div class="truck-summary-item text-danger">
                                            <strong class="text-danger"><?php echo e($carga['atrasadas']); ?></strong>
                                            Fora da janela
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <div class="truck-deliveries">
                                <?php $__currentLoopData = $carga['entregas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entregaCaminhao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="truck-delivery <?php echo e($entregaCaminhao['atrasada'] ? 'is-overdue' : ''); ?>">
                                        <div class="truck-delivery-main">
                                            <a href="<?php echo e(route('entregas.show', $entregaCaminhao['id'])); ?>"
                                               class="truck-delivery-code">
                                                <?php echo e($entregaCaminhao['codigo']); ?>

                                            </a>
                                            <strong class="truck-customer-name">
                                                <?php echo e($entregaCaminhao['cliente']); ?>

                                            </strong>
                                            <small>
                                                <i class="bi bi-telephone me-1"></i>
                                                <?php echo e($entregaCaminhao['telefone']); ?>

                                            </small>
                                            <small>
                                                <i class="bi bi-person-check me-1"></i>
                                                Recebedor: <?php echo e($entregaCaminhao['responsavel_recebimento']); ?>

                                            </small>
                                        </div>

                                        <div class="truck-delivery-location">
                                            <strong><?php echo e($entregaCaminhao['bairro']); ?> · <?php echo e($entregaCaminhao['cidade']); ?></strong>
                                            <small title="<?php echo e($entregaCaminhao['endereco']); ?>">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?php echo e($entregaCaminhao['endereco']); ?>

                                            </small>
                                            <?php if($entregaCaminhao['observacao_entrega']): ?>
                                                <small class="truck-delivery-note"
                                                       title="<?php echo e($entregaCaminhao['observacao_entrega']); ?>">
                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                    <?php echo e($limitarTexto($entregaCaminhao['observacao_entrega'], 72)); ?>

                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="truck-delivery-window">
                                            <strong><?php echo e($entregaCaminhao['data_formatada']); ?></strong>
                                            <small>
                                                <?php echo e($entregaCaminhao['periodo_rotulo']); ?> ·
                                                <?php echo e($formatarQuantidade($entregaCaminhao['quantidade_prevista'])); ?> un.
                                            </small>
                                            <?php if($entregaCaminhao['atrasada']): ?>
                                                <small class="text-danger fw-bold">
                                                    Fora da janela prevista
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="text-end">
                                            <span class="status-pill <?php echo e($classeStatus($entregaCaminhao['status'])); ?>">
                                                <?php echo e($entregaCaminhao['status_rotulo']); ?>

                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2 class="panel-header">
                <span>
                    <i class="bi bi-table me-1"></i>
                    Entregas da operação ativa
                </span>
                <span><?php echo e($entregasDetalhadas->count()); ?> registro(s)</span>
            </h2>

            <?php if($entregasDetalhadas->isEmpty()): ?>
                <div class="empty-panel">
                    Não existem entregas em rota para a data selecionada.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dashboard-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Entrega</th>
                                <th>Data / janela</th>
                                <th>Rota calculada</th>
                                <th>Cliente / contato</th>
                                <th>Bairro / cidade</th>
                                <th>Produtos</th>
                                <th>Qtd. Itens</th>
                                <th>Veículo / motorista</th>
                                <th>Romaneio</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $entregasDetalhadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $distanciaTrecho = $entrega['distancia_anterior_km']
                                        ?? $entrega['distancia_mapa_km']
                                        ?? null;
                                    $tempoTrechoMinutos = $entrega['tempo_anterior_minutos']
                                        ?? $entrega['tempo_mapa_minutos']
                                        ?? null;
                                    $fonteTrecho = $entrega['fonte_distancia']
                                        ?? $entrega['fonte_distancia_mapa']
                                        ?? 'geografica';
                                    $trechoValhalla = $fonteTrecho === 'valhalla';
                                ?>
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
                                        <?php if($distanciaTrecho !== null): ?>
                                            <strong>
                                                <?php echo e(number_format((float) $distanciaTrecho, 2, ',', '.')); ?> km
                                            </strong>

                                            <?php if($tempoTrechoMinutos !== null): ?>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?php echo e(number_format((float) $tempoTrechoMinutos, 1, ',', '.')); ?> min
                                                </small>
                                            <?php endif; ?>

                                            <span class="route-source-badge <?php echo e($trechoValhalla ? 'is-valhalla' : 'is-geographic'); ?>">
                                                <?php echo e($trechoValhalla ? 'Valhalla' : 'Estimativa geográfica'); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Não calculada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($entrega['cliente']); ?></strong>
                                        <small class="d-block text-muted">
                                            <i class="bi bi-telephone me-1"></i><?php echo e($entrega['telefone']); ?>

                                        </small>
                                        <small class="d-block text-muted">
                                            <i class="bi bi-person-check me-1"></i>
                                            <?php echo e($entrega['responsavel_recebimento']); ?>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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

        const depositoMapa = {
            nome: <?php echo json_encode((string) (
                $empresaAtiva['nome']
                ?? config('app.name', 'Empresa')
            ), 512) ?>,
            endereco: <?php echo json_encode((string) (
                $empresaAtiva['endereco']
                ?? config(
                    'logistica.deposito.endereco', 'Endereço não configurado'
                )
            ), 512) ?>,
            telefone: <?php echo json_encode((string) (
                $empresaAtiva['telefone']
                ?? ''
            ), 15, 512) ?>,
            email: <?php echo json_encode((string) (
                $empresaAtiva['email']
                ?? ''
            ), 15, 512) ?>,
            latitude: Number(<?php echo json_encode(
                $empresaAtiva['latitude']
                ?? config(
                    'logistica.deposito.latitude', config('openstreetmap.center.lat')
                ), 512) ?>),
            longitude: Number(<?php echo json_encode(
                $empresaAtiva['longitude']
                ?? config(
                    'logistica.deposito.longitude', config('openstreetmap.center.lng')
                ), 512) ?>),
            zoom: Number(<?php echo json_encode(
                $empresaAtiva['zoom']
                ?? config('logistica.deposito.zoom', 16), 512) ?>),
            raioPatioMetros: Number(<?php echo json_encode(
                $empresaAtiva['raio_patio_metros']
                ?? config(
                    'logistica.deposito.raio_patio_metros', 18
                ), 512) ?>),
        };

        const entregasMapa = <?php echo json_encode($pontosMapa, 15, 512) ?>;
        const urlPosicoesVeiculos = <?php echo json_encode(
            url('/entregas-inteligentes/posicoes-veiculos')
        , 15, 512) ?>;
        const raioAgrupamentoMetros = 100;
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

        function extrairCep(endereco) {
            const resultado = String(endereco || '').match(
                /\b(\d{5})-?(\d{3})\b/
            );

            return resultado
                ? resultado[1] + resultado[2]
                : '';
        }

        function agruparEntregasPorEndereco(entregas) {
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

                const enderecoChave = String(
                    entrega.endereco_chave || ''
                ).trim();

                const chave = enderecoChave !== ''
                    ? enderecoChave
                    : 'entrega:' + String(entrega.id);

                if (! grupos.has(chave)) {
                    grupos.set(chave, {
                        chave: chave,
                        endereco: entrega.endereco,
                        cep: extrairCep(entrega.endereco),
                        entregas: [],
                        latitude: latitude,
                        longitude: longitude,
                        coordenadaConfirmada: true,
                    });
                }

                const grupo = grupos.get(chave);

                const quantidadeAtual =
                    grupo.entregas.length;

                if (quantidadeAtual > 0) {
                    grupo.latitude = (
                        grupo.latitude * quantidadeAtual
                        + latitude
                    ) / (quantidadeAtual + 1);

                    grupo.longitude = (
                        grupo.longitude * quantidadeAtual
                        + longitude
                    ) / (quantidadeAtual + 1);
                }

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
            const classificacoes = entregasDoEndereco.map(
                entrega => classificarSituacaoEntrega(entrega)
            );

            if (
                classificacoes.some(
                    classificacao => classificacao.chave === 'overdue'
                )
            ) {
                return '#dc3e3e';
            }

            if (
                classificacoes.some(
                    classificacao => classificacao.chave === 'today'
                )
            ) {
                return '#249654';
            }

            return '#0d6efd';
        }

        function rotuloIndicadorEntregas(entregasDoEndereco) {
            const classificacoes = entregasDoEndereco.map(
                entrega => classificarSituacaoEntrega(entrega)
            );

            if (
                classificacoes.some(
                    classificacao => classificacao.chave === 'overdue'
                )
            ) {
                return 'Atrasada';
            }

            if (
                classificacoes.some(
                    classificacao => classificacao.chave === 'today'
                )
            ) {
                return 'Em dia';
            }

            return 'Normal';
        }

        function criarIconeMarcador(numero, cor, rotulo) {
            return L.divIcon({
                className: 'delivery-map-div-icon',
                html: '<div class="delivery-map-marker-wrap">'
                    + '<div class="delivery-map-pin" style="background:'
                    + cor
                    + ';border-radius:8px;transform:none;">'
                    + '<i class="bi bi-house-door-fill" '
                    + 'style="font-size:1rem;"></i>'
                    + '<span style="'
                    + 'position:absolute;'
                    + 'right:-7px;'
                    + 'top:-8px;'
                    + 'width:20px;'
                    + 'height:20px;'
                    + 'border-radius:50%;'
                    + 'background:#fff;'
                    + 'border:2px solid '
                    + cor
                    + ';'
                    + 'color:'
                    + cor
                    + ';'
                    + 'display:flex;'
                    + 'align-items:center;'
                    + 'justify-content:center;'
                    + 'font-size:.58rem;'
                    + 'font-weight:900;'
                    + 'transform:none;'
                    + '">'
                    + numero
                    + '</span>'
                    + '</div>'
                    + '<span class="delivery-map-status-label" style="color:'
                    + cor
                    + '">'
                    + escaparHtml(rotulo)
                    + '</span></div>',
                iconAnchor: [
                    15,
                    30,
                ],
                iconSize: [
                    30,
                    48,
                ],
                popupAnchor: [
                    0,
                    -27,
                ],
            });
        }

        function escaparHtml(valor) {
            return String(valor ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function resumirTextoMapa(valor, limite) {
            const texto = String(valor || '').trim();

            if (texto.length <= limite) {
                return texto;
            }

            return texto.slice(0, Math.max(1, limite - 3)).trim()
                + '...';
        }

        function rotuloStatusVeiculo(status) {
            return {
                Carregada: 'Carregada',
                Liberada: 'Liberada',
                Em_rota: 'Em rota',
                No_destino: 'No destino',
            }[status] || String(status || 'Sem status')
                .replaceAll('_', ' ');
        }

        function calcularDiasAtraso(entrega) {
            if (! entrega.atrasada) {
                return 0;
            }

            const partes = String(
                entrega.data_chave || ''
            ).match(/^(\d{4})-(\d{2})-(\d{2})$/);

            if (partes) {
                const agora = new Date();
                const hojeUtc = Date.UTC(
                    agora.getFullYear(),
                    agora.getMonth(),
                    agora.getDate()
                );

                const previsaoUtc = Date.UTC(
                    Number(partes[1]),
                    Number(partes[2]) - 1,
                    Number(partes[3])
                );

                return Math.max(
                    0,
                    Math.floor(
                        (hojeUtc - previsaoUtc)
                        / 86400000
                    )
                );
            }

            return Math.max(
                0,
                Math.trunc(
                    Number(entrega.dias_atraso) || 0
                )
            );
        }

        function classificarSituacaoEntrega(entrega) {
            if (Boolean(entrega.atrasada)) {
                return {
                    chave: 'overdue',
                    rotulo: 'Atrasada',
                    classe: 'is-overdue',
                };
            }

            const agora = new Date();
            const hoje = [
                agora.getFullYear(),
                String(agora.getMonth() + 1).padStart(2, '0'),
                String(agora.getDate()).padStart(2, '0'),
            ].join('-');

            if (String(entrega.data_chave || '') === hoje) {
                return {
                    chave: 'today',
                    rotulo: 'Em dia',
                    classe: 'is-today',
                };
            }

            return {
                chave: 'normal',
                rotulo: 'Normal',
                classe: 'is-normal',
            };
        }

        function classificarDistanciaEntrega(entrega) {
            const distanciaDisponivel = [
                entrega.distancia_anterior_km,
                entrega.distancia_mapa_km,
            ].find(function (valor) {
                return valor !== null
                    && valor !== undefined
                    && String(valor).trim() !== ''
                    && Number.isFinite(Number(valor));
            });

            const distancia = distanciaDisponivel === undefined
                ? null
                : Number(distanciaDisponivel);

            if (
                distancia === null
                || ! Number.isFinite(distancia)
                || distancia < 0
            ) {
                return {
                    valor: null,
                    rotulo: 'Distância não calculada',
                    classe: 'is-normal',
                };
            }

            if (distancia <= 5) {
                return {
                    valor: distancia,
                    rotulo: 'Próxima',
                    classe: 'is-near',
                };
            }

            if (distancia <= 15) {
                return {
                    valor: distancia,
                    rotulo: 'Média distância',
                    classe: 'is-medium',
                };
            }

            return {
                valor: distancia,
                rotulo: 'Distante',
                classe: 'is-far',
            };
        }

        function rotuloDistanciaEntrega(entrega) {
            const distancia = classificarDistanciaEntrega(
                entrega
            );

            if (distancia.valor === null) {
                return distancia.rotulo;
            }

            const origem = obterOrdemEntrega(entrega) === 1
                ? 'da sede'
                : 'da entrega anterior';

            const tempo = rotuloTempoEntrega(entrega);
            const fonte = rotuloFonteDistanciaEntrega(entrega);

            return distancia.valor.toLocaleString(
                'pt-BR',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            ) + ' km ' + origem
                + (tempo ? ' · ' + tempo : '')
                + ' · ' + fonte
                + ' · ' + distancia.rotulo;
        }

        function fonteDistanciaEntrega(entrega) {
            const fonte = String(
                entrega.fonte_distancia
                    || entrega.fonte_distancia_mapa
                    || 'geografica'
            ).toLowerCase();

            return fonte === 'valhalla'
                ? 'valhalla'
                : 'geografica';
        }

        function rotuloFonteDistanciaEntrega(entrega) {
            return fonteDistanciaEntrega(entrega) === 'valhalla'
                ? 'Valhalla'
                : 'Estimativa geográfica';
        }

        function minutosEstimadosEntrega(entrega) {
            const minutosDisponiveis = [
                entrega.tempo_anterior_minutos,
                entrega.tempo_mapa_minutos,
            ].find(function (valor) {
                return valor !== null
                    && valor !== undefined
                    && String(valor).trim() !== ''
                    && Number.isFinite(Number(valor));
            });

            if (minutosDisponiveis !== undefined) {
                return Math.max(0, Number(minutosDisponiveis));
            }

            const segundosDisponiveis = [
                entrega.tempo_anterior_segundos,
                entrega.tempo_mapa_segundos,
            ].find(function (valor) {
                return valor !== null
                    && valor !== undefined
                    && String(valor).trim() !== ''
                    && Number.isFinite(Number(valor));
            });

            return segundosDisponiveis === undefined
                ? null
                : Math.max(0, Number(segundosDisponiveis) / 60);
        }

        function rotuloTempoEntrega(entrega) {
            const minutos = minutosEstimadosEntrega(entrega);

            if (minutos === null) {
                return '';
            }

            if (minutos < 60) {
                return minutos.toLocaleString('pt-BR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1,
                }) + ' min';
            }

            const horas = Math.floor(minutos / 60);
            const minutosRestantes = Math.round(minutos % 60);

            return horas + 'h'
                + String(minutosRestantes).padStart(2, '0');
        }

        function rotuloOrdemInteligente(entrega) {
            const ordem = obterOrdemEntrega(entrega);

            if (! ordem) {
                return 'Não definida';
            }

            let rotulo = ordem
                + 'ª entrega no planejamento inteligente';

            if (Boolean(entrega.atrasada)) {
                rotulo += ' · PRIORIDADE POR ATRASO';
            }

            return rotulo;
        }

        function classeStatusVeiculo(status) {
            return {
                Carregada: 'vehicle-status-carregada',
                Liberada: 'vehicle-status-liberada',
                Em_rota: 'vehicle-status-em-rota',
                No_destino: 'vehicle-status-no-destino',
            }[status] || 'vehicle-status-carregada';
        }

        function tipoVisualVeiculo(entrega) {
            const descricaoOriginal = [
                entrega.veiculo_tipo,
                entrega.veiculo_carroceria,
                entrega.veiculo_modelo,
            ]
                .filter(Boolean)
                .join(' · ');

            const descricao = descricaoOriginal
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            if (
                entrega.veiculo_possui_munck
                || descricao.includes('munck')
                || descricao.includes('guindauto')
            ) {
                return {
                    chave: 'munck',
                    icone: 'bi-truck',
                    rotulo: 'Munck',
                    descricao: descricaoOriginal || 'Caminhão Munck',
                };
            }

            if (
                entrega.veiculo_carroceria_fechada
                || descricao.includes('bau')
                || descricao.includes('fechada')
            ) {
                return {
                    chave: 'bau',
                    icone: 'bi-truck',
                    rotulo: 'Baú',
                    descricao: descricaoOriginal || 'Caminhão baú',
                };
            }

            if (
                entrega.veiculo_carroceria_aberta
                || descricao.includes('aberta')
                || descricao.includes('carga seca')
                || descricao.includes('grade baixa')
                || descricao.includes('grade alta')
            ) {
                return {
                    chave: 'carroceria',
                    icone: 'bi-truck',
                    rotulo: 'Aberta',
                    descricao: descricaoOriginal || 'Carroceria aberta',
                };
            }

            if (
                descricao.includes('van')
                || descricao.includes('furgao')
                || descricao.includes('ducato')
                || descricao.includes('sprinter')
                || descricao.includes('master')
                || descricao.includes('boxer')
                || descricao.includes('jumper')
            ) {
                return {
                    chave: 'van',
                    icone: 'bi-truck-front',
                    rotulo: 'Van',
                    descricao: descricaoOriginal || 'Van',
                };
            }

            if (
                descricao.includes('utilitario')
                || descricao.includes('pickup')
                || descricao.includes('picape')
                || descricao.includes('saveiro')
                || descricao.includes('strada')
                || descricao.includes('montana')
            ) {
                return {
                    chave: 'utilitario',
                    icone: 'bi-truck-front',
                    rotulo: 'Util.',
                    descricao: descricaoOriginal || 'Utilitário',
                };
            }

            if (
                descricao.includes('moto')
                || descricao.includes('motocicleta')
            ) {
                return {
                    chave: 'moto',
                    icone: 'bi-bicycle',
                    rotulo: 'Moto',
                    descricao: descricaoOriginal || 'Motocicleta',
                };
            }

            if (
                descricao.includes('passeio')
                || descricao.includes('sedan')
                || descricao.includes('hatch')
                || descricao.includes('automovel')
            ) {
                return {
                    chave: 'carro',
                    icone: 'bi-car-front',
                    rotulo: 'Carro',
                    descricao: descricaoOriginal || 'Automóvel',
                };
            }

            return {
                chave: 'caminhao',
                icone: 'bi-truck',
                rotulo: 'Cam.',
                descricao: descricaoOriginal || 'Caminhão',
            };
        }

        function criarIconeDeposito(totalVeiculosPatio) {
            const nomeEmpresa = resumirTextoMapa(
                depositoMapa.nome,
                18
            );

            return L.divIcon({
                className: 'delivery-map-depot-icon',
                html: '<div class="delivery-map-depot-pin" title="'
                    + escaparHtml(depositoMapa.nome)
                    + '">'
                    + '<i class="bi bi-buildings-fill"></i>'
                    + '<span class="delivery-map-depot-name">'
                    + escaparHtml(nomeEmpresa)
                    + '</span>'
                    + '</div>',
                iconAnchor: [
                    39,
                    39,
                ],
                iconSize: [
                    78,
                    78,
                ],
                popupAnchor: [
                    0,
                    -42,
                ],
            });
        }

        function criarConteudoDeposito(totalVeiculosPatio) {
            const conteudo = document.createElement('div');
            conteudo.className = 'map-vehicle-popup';

            const titulo = document.createElement('strong');
            titulo.className = 'map-vehicle-popup-title';
            titulo.textContent = depositoMapa.nome;
            conteudo.append(titulo);

            const dados = [
                ['Endereço', depositoMapa.endereco],
            ];

            if (depositoMapa.telefone) {
                dados.push([
                    'Telefone',
                    depositoMapa.telefone,
                ]);
            }

            if (depositoMapa.email) {
                dados.push([
                    'E-mail',
                    depositoMapa.email,
                ]);
            }

            dados.forEach(function (dado) {
                const linha = document.createElement('div');
                linha.className = 'map-vehicle-popup-row';

                const rotulo = document.createElement('span');
                rotulo.textContent = dado[0];

                const valor = document.createElement('strong');
                valor.textContent = dado[1];

                linha.append(rotulo, valor);
                conteudo.append(linha);
            });

            return conteudo;
        }

        function obterOrdemEntrega(entrega) {
            const ordem = Number(
                entrega.ordem_mapa
                    ?? entrega.ordem_inteligente
                    ?? entrega.ordem_rota
            );

            return Number.isFinite(ordem) && ordem > 0
                ? Math.trunc(ordem)
                : null;
        }

        function rotuloOrdemCurta(entrega) {
            const ordem = obterOrdemEntrega(entrega);

            if (! ordem) {
                return '-';
            }

            return String(ordem);
        }

        function criarIconeVeiculo(entrega, totalEntregas) {
            const tipo = tipoVisualVeiculo(entrega);

            const cores = {
                Carregada: '#6c757d',
                Liberada: '#f48120',
                Em_rota: '#072b62',
                No_destino: '#198754',
            };

            const status = String(
                entrega.status_chave || ''
            );

            const atrasada = Boolean(entrega.atrasada);

            const cor = atrasada
                ? '#dc3e3e'
                : (cores[status] || '#072b62');

            const placa = String(
                entrega.veiculo || 'Veículo'
            );

            const classificacao = classificarSituacaoEntrega(
                entrega
            );

            const statusRotulo = rotuloStatusVeiculo(status)
                + ' · '
                + classificacao.rotulo;

            const statusClasse = atrasada
                ? 'vehicle-status-atrasada'
                : classeStatusVeiculo(status);

            const ordem = obterOrdemEntrega(entrega);

            const indicadorOrdem = [
                'Em_rota',
                'No_destino',
            ].includes(status)
                && Number.isFinite(ordem)
                && ordem > 0
                    ? '<span class="delivery-map-vehicle-order" title="Ordem da entrega">'
                        + rotuloOrdemCurta(entrega)
                        + '</span>'
                    : '';

            return L.divIcon({
                className: 'delivery-map-vehicle-icon',
                html: '<div class="delivery-map-vehicle-pin" style="background:'
                    + cor
                    + '">'
                    + indicadorOrdem
                    + '<i class="bi '
                    + tipo.icone
                    + '"></i><span class="delivery-map-vehicle-kind">'
                    + escaparHtml(tipo.rotulo)
                    + '</span><span class="delivery-map-vehicle-label">'
                    + '<strong class="delivery-map-vehicle-plate">'
                    + escaparHtml(placa)
                    + '</strong>'
                    + '<span class="delivery-map-vehicle-status '
                    + statusClasse
                    + '">'
                    + escaparHtml(statusRotulo)
                    + '</span>'
                    + '</span></div>',
                iconAnchor: [
                    24,
                    54,
                ],
                iconSize: [
                    82,
                    78,
                ],
                popupAnchor: [
                    0,
                    -50,
                ],
            });
        }

        function descricaoPosicaoVeiculo(status) {
            return {
                Carregada: 'Carregado no pátio',
                Liberada: 'Liberado no pátio',
                Em_rota: 'Próxima entrega da rota',
                No_destino: 'No endereço do cliente',
            }[status] || 'Posição operacional';
        }

        function montarUrlEnderecoEntrega(entrega) {
            const latitude = Number(
                entrega.latitude_entrega
            );
            const longitude = Number(
                entrega.longitude_entrega
            );

            if (
                entrega.coordenada_confirmada !== true
                || ! Number.isFinite(latitude)
                || latitude < -90
                || latitude > 90
                || ! Number.isFinite(longitude)
                || longitude < -180
                || longitude > 180
            ) {
                return null;
            }

            const parametros = new URLSearchParams({
                api: '1',
                query: latitude.toFixed(7)
                    + ','
                    + longitude.toFixed(7),
            });

            return 'https://www.google.com/maps/search/?'
                + parametros.toString();
        }

        function adicionarQrEnderecoEntrega(conteudo, entrega) {
            const urlEndereco = montarUrlEnderecoEntrega(
                entrega
            );

            if (! urlEndereco) {
                return;
            }

            const areaQr = document.createElement('div');
            areaQr.className = 'map-route-qr';

            const codigoQr = document.createElement('div');
            codigoQr.className = 'map-route-qr-code';

            const link = document.createElement('a');
            link.href = urlEndereco;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Abrir endereço da entrega no Google Maps';

            areaQr.append(codigoQr, link);
            conteudo.append(areaQr);

            if (typeof QRCode === 'function') {
                new QRCode(codigoQr, {
                    text: urlEndereco,
                    width: 160,
                    height: 160,
                    colorDark: '#0b3268',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.L,
                });
            }
        }

        function criarIconeVeiculoRastreado(
            entrega,
            totalEntregas
        ) {
            const base = criarIconeVeiculo(
                entrega,
                totalEntregas
            );

            const html = String(
                base.options.html || ''
            )
                .replace(
                    /style="background:[^"]+"/,
                    'style="background:#7c3aed"'
                )
                .replace(
                    'delivery-map-vehicle-pin',
                    'delivery-map-vehicle-pin delivery-map-vehicle-pin-gps'
                );

            return L.divIcon({
                ...base.options,
                className:
                    'delivery-map-vehicle-icon '
                    + 'delivery-map-vehicle-icon-gps',
                html: html,

                /*
                 * Mantém a coordenada GPS real.
                 * O deslocamento é apenas visual para o ícone não
                 * ficar encoberto pelo marcador da empresa/pátio.
                 */
                iconAnchor: [
                    -34,
                    54,
                ],

                popupAnchor: [
                    -58,
                    -50,
                ],
            });
        }


        function criarConteudoVeiculo(
            entrega,
            totalEntregas
        ) {
            const conteudo = document.createElement('div');
            conteudo.className = 'map-vehicle-popup';

            const titulo = document.createElement('strong');
            titulo.className = 'map-vehicle-popup-title';
            titulo.textContent = entrega.veiculo
                + (
                    entrega.veiculo_modelo
                        ? ' · ' + entrega.veiculo_modelo
                        : ''
                );
            conteudo.append(titulo);

            const diasAtraso = calcularDiasAtraso(
                entrega
            );

            const classificacao = classificarSituacaoEntrega(
                entrega
            );
            const distancia = classificarDistanciaEntrega(
                entrega
            );

            const situacao = entrega.atrasada
                ? 'ATRASADA HÁ '
                    + diasAtraso
                    + (diasAtraso === 1 ? ' DIA' : ' DIAS')
                : classificacao.rotulo.toUpperCase();

            const tipoVeiculo = tipoVisualVeiculo(entrega);

            const dados = [
                ['Status', rotuloStatusVeiculo(
                    entrega.status_chave
                )],
                ['Tipo do veículo', tipoVeiculo.descricao],
                ['Situação', situacao,
                    classificacao.chave === 'overdue'
                        ? 'map-popup-value-danger'
                        : classificacao.chave === 'today'
                            ? 'map-popup-value-success'
                            : 'map-popup-value-normal'],
                ['Distância do trecho', rotuloDistanciaEntrega(entrega),
                    distancia.classe === 'is-near'
                        ? 'map-popup-value-success'
                        : distancia.classe === 'is-medium'
                            ? 'map-popup-value-medium'
                            : distancia.classe === 'is-far'
                                ? 'map-popup-value-far'
                                : 'map-popup-value-normal'],
                ['Tempo estimado', rotuloTempoEntrega(entrega)
                    || 'Não calculado'],
                ['Motor de cálculo', rotuloFonteDistanciaEntrega(
                    entrega
                )],
                ['Agrupamento', entrega.agrupamento_rota
                    || 'Distância sequencial'],
                ['Previsão', entrega.data + ' · ' + entrega.periodo],
                ['Motorista', entrega.motorista],
                ['Romaneio', entrega.romaneio_codigo || 'Não informado'],
                ['Entregas', String(totalEntregas)],
                ['Ordem inteligente', rotuloOrdemInteligente(entrega),
                    entrega.atrasada
                        ? 'map-popup-value-danger'
                        : 'map-popup-value-normal'],
                ['Liberada em', entrega.liberado_em || 'Ainda não liberada'],
                ['Posição', descricaoPosicaoVeiculo(
                    entrega.status_chave
                )],
                ['Entrega referência', entrega.codigo],
                ['Cliente de referência', entrega.cliente],
                ['Telefone do cliente', entrega.telefone || 'Não informado'],
                ['Endereço', entrega.endereco],
            ];

            dados.forEach(function (dado) {
                const linha = document.createElement('div');
                linha.className = 'map-vehicle-popup-row';

                const rotulo = document.createElement('span');
                rotulo.textContent = dado[0];

                const valor = document.createElement('strong');
                valor.textContent = dado[1] || 'Não informado';

                if (dado[2]) {
                    valor.classList.add(dado[2]);
                }

                linha.append(rotulo, valor);
                conteudo.append(linha);
            });

            adicionarQrEnderecoEntrega(
                conteudo,
                entrega
            );

            return conteudo;
        }

        function calcularPosicaoPatio(
            indice,
            total,
            raioPersonalizado = null,
            deslocamentoAngular = 0
        ) {
            const latitude = depositoMapa.latitude;
            const longitude = depositoMapa.longitude;

            const raioBase = Number.isFinite(
                Number(raioPersonalizado)
            )
                ? Number(raioPersonalizado)
                : Math.min(
                    70,
                    Math.max(
                        60,
                        Number(depositoMapa.raioPatioMetros)
                            || 60
                    )
                );

            const quantidade = Math.max(total, 1);
            const itensPorAnel = 8;
            const anel = Math.floor(indice / itensPorAnel);
            const posicaoNoAnel = indice % itensPorAnel;
            const quantidadeNoAnel = Math.min(
                itensPorAnel,
                quantidade - (anel * itensPorAnel)
            );
            const raio = raioBase + (anel * 9);
            const angulo = (
                (Math.PI * 2 * posicaoNoAnel)
                / Math.max(quantidadeNoAnel, 1)
            ) - (Math.PI / 2) + deslocamentoAngular;

            const deltaLatitude =
                (raio * Math.cos(angulo)) / 111320;

            const fatorLongitude = Math.max(
                .2,
                Math.cos(latitude * Math.PI / 180)
            );

            const deltaLongitude =
                (raio * Math.sin(angulo))
                / (111320 * fatorLongitude);

            return L.latLng(
                latitude + deltaLatitude,
                longitude + deltaLongitude
            );
        }

        async function buscarPosicoesVeiculosRastreados() {
            try {
                const resposta = await fetch(
                    urlPosicoesVeiculos,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                        credentials: 'same-origin',
                    }
                );

                if (! resposta.ok) {
                    throw new Error(
                        'Rastreamento HTTP ' + resposta.status
                    );
                }

                const dados = await resposta.json();

                if (
                    ! dados
                    || dados.ok !== true
                    || ! Array.isArray(dados.veiculos)
                ) {
                    return [];
                }

                return dados.veiculos;
            } catch (erro) {
                console.warn(
                    'Não foi possível carregar as posições dos veículos.',
                    erro
                );

                return [];
            }
        }

        function criarConteudoVeiculoRastreado(
            entrega,
            rastreamento,
            totalEntregas
        ) {
            const conteudo = criarConteudoVeiculo(
                entrega,
                totalEntregas
            );

            const titulo = conteudo.querySelector(
                '.map-vehicle-popup-title'
            );

            const dadosGps = [
                [
                    'Rastreamento',
                    'Posição GPS do Traccar',
                    'map-popup-value-success',
                ],
                [
                    'Identificador',
                    rastreamento.traccar_unique_id || 'Não informado',
                ],
                [
                    'Dispositivo',
                    rastreamento.device_name || 'Não informado',
                ],
                [
                    'Status Traccar',
                    rastreamento.status || 'unknown',
                ],
                [
                    'Último fix',
                    rastreamento.fix_time || 'Não informado',
                ],
                [
                    'Velocidade',
                    rastreamento.speed_kmh !== null
                    && rastreamento.speed_kmh !== undefined
                        ? String(rastreamento.speed_kmh) + ' km/h'
                        : 'Não informada',
                ],
                [
                    'Bateria',
                    rastreamento.battery_level !== null
                    && rastreamento.battery_level !== undefined
                        ? String(rastreamento.battery_level) + '%'
                        : 'Não informada',
                ],
            ];

            const fragmento = document.createDocumentFragment();

            dadosGps.forEach(function (dado) {
                const linha = document.createElement('div');
                linha.className = 'map-vehicle-popup-row';

                const rotulo = document.createElement('span');
                rotulo.textContent = dado[0];

                const valor = document.createElement('strong');
                valor.textContent = dado[1];

                if (dado[2]) {
                    valor.classList.add(dado[2]);
                }

                linha.append(rotulo, valor);
                fragmento.append(linha);
            });

            if (titulo && titulo.nextSibling) {
                conteudo.insertBefore(
                    fragmento,
                    titulo.nextSibling
                );
            } else {
                conteudo.append(fragmento);
            }

            return conteudo;
        }

        async function adicionarMarcadoresVeiculosRastreados(
            mapa,
            limites,
            marcadoresOperacionaisPorVeiculo = new Map()
        ) {
            const idsVeiculosDaOperacao = new Set(
                entregasMapa
                    .map(
                        entrega => Number(
                            entrega.veiculo_id
                        )
                    )
                    .filter(
                        id => Number.isFinite(id) && id > 0
                    )
            );

            if (idsVeiculosDaOperacao.size === 0) {
                return {
                    total: 0,
                    veiculoIds: new Set(),
                };
            }

            const posicoes = await buscarPosicoesVeiculosRastreados();
            const idsComPosicaoGps = new Set();
            let total = 0;

            posicoes.forEach(function (rastreamento) {
                const veiculoId = Number(
                    rastreamento.veiculo_id
                );

                if (
                    ! idsVeiculosDaOperacao.has(veiculoId)
                    || rastreamento.posicao_disponivel !== true
                ) {
                    return;
                }

                const latitude = Number(
                    rastreamento.latitude
                );
                const longitude = Number(
                    rastreamento.longitude
                );

                if (
                    ! Number.isFinite(latitude)
                    || latitude < -90
                    || latitude > 90
                    || ! Number.isFinite(longitude)
                    || longitude < -180
                    || longitude > 180
                ) {
                    return;
                }

                const entregasDoVeiculo = entregasMapa.filter(
                    entrega =>
                        Number(entrega.veiculo_id) === veiculoId
                );

                if (entregasDoVeiculo.length === 0) {
                    return;
                }

                const referencia = entregasDoVeiculo
                    .slice()
                    .sort(function (a, b) {
                        const prioridade = {
                            No_destino: 0,
                            Em_rota: 1,
                            Liberada: 2,
                            Carregada: 3,
                        };

                        return (
                            prioridade[a.status_chave] ?? 9
                        ) - (
                            prioridade[b.status_chave] ?? 9
                        );
                    })[0];

                const posicao = L.latLng(
                    latitude,
                    longitude
                );

                const marcadorExistente =
                    marcadoresOperacionaisPorVeiculo.get(
                        veiculoId
                    );

                const iconeGps =
                    criarIconeVeiculoRastreado(
                        referencia,
                        entregasDoVeiculo.length
                    );

                const popupGps =
                    criarConteudoVeiculoRastreado(
                        referencia,
                        rastreamento,
                        entregasDoVeiculo.length
                    );

                if (marcadorExistente) {
                    marcadorExistente.setLatLng(
                        posicao
                    );

                    marcadorExistente.setIcon(
                        iconeGps
                    );

                    marcadorExistente.unbindPopup();

                    marcadorExistente.bindPopup(
                        popupGps,
                        {
                            maxWidth: 380,
                            minWidth: 255,
                        }
                    );

                    marcadorExistente.setZIndexOffset(
                        6500
                    );
                } else {
                    const marcadorGps = L.marker(
                        posicao,
                        {
                            icon: iconeGps,
                            title: String(
                                referencia.veiculo
                                || rastreamento.placa
                                || 'Veículo'
                            ) + ' · GPS',
                            zIndexOffset: 6500,
                        }
                    )
                        .bindPopup(
                            popupGps,
                            {
                                maxWidth: 380,
                                minWidth: 255,
                            }
                        )
                        .addTo(mapa);

                    marcadoresOperacionaisPorVeiculo.set(
                        veiculoId,
                        marcadorGps
                    );
                }

                limites.extend(posicao);
                idsComPosicaoGps.add(veiculoId);
                total++;
            });

            return {
                total: total,
                veiculoIds: idsComPosicaoGps,
            };
        }

        function adicionarMarcadoresVeiculos(
            mapa,
            pontos,
            limites
        ) {
            const posicoesPorEntrega = new Map();

            pontos.forEach(function (ponto) {
                ponto.grupo.entregas.forEach(function (entrega) {
                    posicoesPorEntrega.set(
                        Number(entrega.id),
                        ponto.posicao
                    );
                });
            });

            const veiculos = new Map();
            const marcadoresPorVeiculo = new Map();

            entregasMapa.forEach(function (entrega) {
                const veiculo = String(
                    entrega.veiculo || ''
                ).trim();

                const veiculoId = Number(
                    entrega.veiculo_id
                );

                if (
                    veiculo === ''
                    || veiculo === 'Não definido'
                    || ! [
                        'Carregada',
                        'Liberada',
                    ].includes(entrega.status_chave)
                ) {
                    return;
                }

                const chave = [
                    veiculo,
                    entrega.motorista || '',
                ].join('|');

                if (! veiculos.has(chave)) {
                    veiculos.set(chave, []);
                }

                veiculos.get(chave).push(entrega);
            });

            const cargasPatio = [];
            const cargasRua = [];

            veiculos.forEach(function (entregas) {
                const noPatio = entregas.every(
                    entrega => [
                        'Carregada',
                        'Liberada',
                    ].includes(entrega.status_chave)
                );

                const carga = {
                    entregas: entregas,
                    referencia: null,
                };

                if (noPatio) {
                    carga.referencia = entregas.slice().sort(
                        function (a, b) {
                            const diferencaAtraso = Number(b.atrasada)
                                - Number(a.atrasada);

                            if (diferencaAtraso !== 0) {
                                return diferencaAtraso;
                            }

                            const prioridade = {
                                Liberada: 0,
                                Carregada: 1,
                            };

                            return (
                                prioridade[a.status_chave] ?? 9
                            ) - (
                                prioridade[b.status_chave] ?? 9
                            );
                        }
                    )[0];

                    cargasPatio.push(carga);
                    return;
                }

                const candidatas = entregas
                    .filter(function (entrega) {
                        return posicoesPorEntrega.has(
                            Number(entrega.id)
                        );
                    })
                    .sort(function (a, b) {
                        const diferencaAtraso = Number(b.atrasada)
                            - Number(a.atrasada);

                        if (diferencaAtraso !== 0) {
                            return diferencaAtraso;
                        }

                        const prioridade = {
                            No_destino: 0,
                            Em_rota: 1,
                        };

                        const diferencaStatus = (
                            prioridade[a.status_chave] ?? 9
                        ) - (
                            prioridade[b.status_chave] ?? 9
                        );

                        if (diferencaStatus !== 0) {
                            return diferencaStatus;
                        }

                        return Number(a.ordem_rota ?? 999999)
                            - Number(b.ordem_rota ?? 999999);
                    });

                carga.referencia = candidatas[0] || entregas[0];
                cargasRua.push(carga);
            });

            const depositoPosicao = L.latLng(
                depositoMapa.latitude,
                depositoMapa.longitude
            );

            L.marker(
                depositoPosicao,
                {
                    icon: criarIconeDeposito(
                        cargasPatio.length
                    ),
                    title: depositoMapa.nome,
                    zIndexOffset: 5000,
                }
            )
                .bindPopup(
                    criarConteudoDeposito(
                        cargasPatio.length
                    ),
                    {
                        maxWidth: 360,
                        minWidth: 250,
                    }
                )
                .addTo(mapa);

            limites.extend(depositoPosicao);

            cargasPatio.forEach(function (carga, indice) {
                const posicao = calcularPosicaoPatio(
                    indice,
                    cargasPatio.length
                );

                L.polyline(
                    [
                        depositoPosicao,
                        posicao,
                    ],
                    {
                        className: 'delivery-map-yard-line',
                        color: '#6c757d',
                        interactive: false,
                        opacity: .55,
                        weight: 1.2,
                    }
                ).addTo(mapa);

                const marcadorVeiculo = L.marker(
                    posicao,
                    {
                        icon: criarIconeVeiculo(
                            carga.referencia,
                            carga.entregas.length
                        ),
                        title: carga.referencia.veiculo
                            + ' · '
                            + carga.referencia.motorista,
                        zIndexOffset: 4200,
                    }
                )
                    .bindPopup(
                        criarConteudoVeiculo(
                            carga.referencia,
                            carga.entregas.length
                        ),
                        {
                            maxWidth: 360,
                            minWidth: 245,
                        }
                    )
                    .addTo(mapa);

                const veiculoId = Number(
                    carga.referencia.veiculo_id
                );

                if (
                    Number.isFinite(veiculoId)
                    && veiculoId > 0
                ) {
                    marcadoresPorVeiculo.set(
                        veiculoId,
                        marcadorVeiculo
                    );
                }

                limites.extend(posicao);
            });

            cargasRua.forEach(function (carga) {
                const posicao = posicoesPorEntrega.get(
                    Number(carga.referencia.id)
                );

                if (! posicao) {
                    return;
                }

                const marcadorVeiculo = L.marker(
                    posicao,
                    {
                        icon: criarIconeVeiculo(
                            carga.referencia,
                            carga.entregas.length
                        ),
                        title: carga.referencia.veiculo
                            + ' · '
                            + carga.referencia.motorista,
                        zIndexOffset: 4000,
                    }
                )
                    .bindPopup(
                        criarConteudoVeiculo(
                            carga.referencia,
                            carga.entregas.length
                        ),
                        {
                            maxWidth: 360,
                            minWidth: 245,
                        }
                    )
                    .addTo(mapa);

                const veiculoId = Number(
                    carga.referencia.veiculo_id
                );

                if (
                    Number.isFinite(veiculoId)
                    && veiculoId > 0
                ) {
                    marcadoresPorVeiculo.set(
                        veiculoId,
                        marcadorVeiculo
                    );
                }

                limites.extend(posicao);
            });

            return {
                patio: cargasPatio.length,
                rua: cargasRua.length,
                marcadoresPorVeiculo:
                    marcadoresPorVeiculo,
            };
        }

        function prepararPosicoesEntregasEmRota(
            mapa,
            entregas,
            posicoesPorEntrega
        ) {
            const grupos = [];

            entregas.forEach(function (entrega) {
                const posicaoReal = posicoesPorEntrega.get(
                    Number(entrega.id)
                );

                if (! posicaoReal) {
                    return;
                }

                let grupo = grupos.find(function (item) {
                    return mapa.distance(
                        item.referencia,
                        posicaoReal
                    ) <= 18;
                });

                if (! grupo) {
                    grupo = {
                        referencia: posicaoReal,
                        itens: [],
                    };
                    grupos.push(grupo);
                }

                grupo.itens.push({
                    entrega: entrega,
                    posicaoReal: posicaoReal,
                });
            });

            const posicoesPreparadas = new Map();

            grupos.forEach(function (grupo) {
                grupo.itens.sort(function (a, b) {
                    const diferencaOrdem = (
                        obterOrdemEntrega(a.entrega) ?? 999999
                    ) - (
                        obterOrdemEntrega(b.entrega) ?? 999999
                    );

                    if (diferencaOrdem !== 0) {
                        return diferencaOrdem;
                    }

                    return Number(a.entrega.id)
                        - Number(b.entrega.id);
                });

                if (grupo.itens.length === 1) {
                    const item = grupo.itens[0];
                    posicoesPreparadas.set(
                        Number(item.entrega.id),
                        {
                            posicaoReal: item.posicaoReal,
                            posicaoExibicao: item.posicaoReal,
                            sobreposta: false,
                        }
                    );

                    return;
                }

                const centroLatitude = grupo.itens.reduce(
                    function (total, item) {
                        return total + item.posicaoReal.lat;
                    },
                    0
                ) / grupo.itens.length;

                const centroLongitude = grupo.itens.reduce(
                    function (total, item) {
                        return total + item.posicaoReal.lng;
                    },
                    0
                ) / grupo.itens.length;

                const raioMetros = Math.max(
                    34,
                    grupo.itens.length * 11
                );

                grupo.itens.forEach(function (item, indice) {
                    const angulo = (
                        (2 * Math.PI * indice)
                        / grupo.itens.length
                    ) - (Math.PI / 2);

                    const deltaLatitude = (
                        raioMetros * Math.cos(angulo)
                    ) / 111320;

                    const fatorLongitude = Math.max(
                        .2,
                        Math.cos(
                            centroLatitude * Math.PI / 180
                        )
                    );

                    const deltaLongitude = (
                        raioMetros * Math.sin(angulo)
                    ) / (111320 * fatorLongitude);

                    posicoesPreparadas.set(
                        Number(item.entrega.id),
                        {
                            posicaoReal: item.posicaoReal,
                            posicaoExibicao: L.latLng(
                                centroLatitude + deltaLatitude,
                                centroLongitude + deltaLongitude
                            ),
                            sobreposta: true,
                        }
                    );
                });
            });

            return posicoesPreparadas;
        }

        function adicionarEntregasEmRota(
            mapa,
            pontos,
            limites
        ) {
            const posicoesPorEntrega = new Map();
            const marcadores = new Map();

            pontos.forEach(function (ponto) {
                ponto.grupo.entregas.forEach(function (entrega) {
                    posicoesPorEntrega.set(
                        Number(entrega.id),
                        ponto.posicao
                    );
                });
            });

            const entregasEmRota = entregasMapa
                .filter(function (entrega) {
                    return [
                        'Em_rota',
                        'No_destino',
                    ].includes(entrega.status_chave)
                        && posicoesPorEntrega.has(
                            Number(entrega.id)
                        );
                });

            const posicoesPreparadas =
                prepararPosicoesEntregasEmRota(
                    mapa,
                    entregasEmRota,
                    posicoesPorEntrega
                );

            let total = 0;

            entregasEmRota.forEach(function (entrega) {
                    const entregasRota = entregasDaRotaCorrespondente(
                        entrega
                    );
                    const posicoes = posicoesPreparadas.get(
                        Number(entrega.id)
                    );

                    if (! posicoes) {
                        return;
                    }

                    if (posicoes.sobreposta) {
                        L.polyline(
                            [
                                posicoes.posicaoReal,
                                posicoes.posicaoExibicao,
                            ],
                            {
                                color: '#64748b',
                                dashArray: '3 4',
                                interactive: false,
                                opacity: .72,
                                weight: 1.3,
                            }
                        ).addTo(mapa);
                    }

                    const ordem = obterOrdemEntrega(entrega);

                    const marcador = L.marker(
                        posicoes.posicaoExibicao,
                        {
                            icon: criarIconeMarcador(
                                ordem || '•',
                                definirCorMarcador([entrega]),
                                rotuloIndicadorEntregas([entrega])
                            ),
                            title: entrega.codigo
                                + ' · '
                                + entrega.veiculo
                                + ' · '
                                + entrega.motorista
                                + (
                                    ordem
                                        ? ' · Ordem ' + ordem
                                        : ''
                                )
                                + ' · '
                                + classificarSituacaoEntrega(
                                    entrega
                                ).rotulo,
                            zIndexOffset: 4000
                                + Math.max(
                                    0,
                                    100 - (ordem ?? 100)
                                ),
                        }
                    )
                        .bindPopup(
                            criarConteudoVeiculo(
                                entrega,
                                entregasRota.length
                            ),
                            {
                                maxWidth: 360,
                                minWidth: 245,
                            }
                        )
                        .addTo(mapa);

                    marcadores.set(
                        Number(entrega.id),
                        marcador
                    );

                    limites.extend(posicoes.posicaoReal);
                    limites.extend(posicoes.posicaoExibicao);
                    total++;
                });

            return {
                total: total,
                marcadores: marcadores,
            };
        }

        function criarIconeSobreposicao(total, possuiAtraso) {
            const cor = possuiAtraso
                ? '#dc3e3e'
                : '#072b62';

            return L.divIcon({
                className: 'delivery-map-div-icon',
                html: '<div class="delivery-map-overlap" style="background:'
                    + cor
                    + '"><strong>'
                    + total
                    + '</strong><small>'
                    + (possuiAtraso ? 'atraso' : 'pontos')
                    + '</small></div>',
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

        function entregasDaRotaCorrespondente(entregaReferencia) {
            const veiculo = String(
                entregaReferencia.veiculo || ''
            ).trim();
            const motorista = String(
                entregaReferencia.motorista || ''
            ).trim();

            if (
                veiculo === ''
                || veiculo === 'Não definido'
            ) {
                return [entregaReferencia];
            }

            const rota = entregasMapa.filter(function (entrega) {
                return [
                    'Em_rota',
                    'No_destino',
                ].includes(entrega.status_chave)
                    && String(entrega.veiculo || '').trim() === veiculo
                    && String(entrega.motorista || '').trim() === motorista;
            });

            return rota.length > 0
                ? rota
                : [entregaReferencia];
        }

        function montarPainelOrdensEntrega(
            mapa,
            marcadores
        ) {
            const accordion = document.getElementById(
                'mapa-rotas-accordion'
            );

            if (! accordion) {
                return;
            }

            accordion.innerHTML = '';

            const entregasEmRota = entregasMapa
                .filter(function (entrega) {
                    return [
                        'Em_rota',
                        'No_destino',
                    ].includes(entrega.status_chave);
                })
                .sort(function (a, b) {
                    const prioridadeStatus = {
                        No_destino: 0,
                        Em_rota: 1,
                    };
                    const diferencaStatus = (
                        prioridadeStatus[a.status_chave] ?? 9
                    ) - (
                        prioridadeStatus[b.status_chave] ?? 9
                    );

                    if (diferencaStatus !== 0) {
                        return diferencaStatus;
                    }

                    return (
                        obterOrdemEntrega(a) ?? 999999
                    ) - (
                        obterOrdemEntrega(b) ?? 999999
                    );
                });

            if (entregasEmRota.length === 0) {
                const vazio = document.createElement('div');
                vazio.className = 'delivery-route-empty';
                vazio.textContent = 'Nenhuma entrega em rota.';
                accordion.append(vazio);

                return;
            }

            const legenda = document.createElement('div');
            legenda.className = 'delivery-route-legend';
            legenda.innerHTML = '<div class="delivery-route-legend-line">'
                + '<span class="delivery-route-legend-label">Situação</span>'
                + '<span class="delivery-route-badge is-overdue">Atrasada</span>'
                + '<span class="delivery-route-badge is-today">Em dia</span>'
                + '<span class="delivery-route-badge is-normal">Normal</span>'
                + '</div><div class="delivery-route-legend-line">'
                + '<span class="delivery-route-legend-label">Distância</span>'
                + '<span class="delivery-route-badge is-near">Próxima ≤ 5 km</span>'
                + '<span class="delivery-route-badge is-medium">Média ≤ 15 km</span>'
                + '<span class="delivery-route-badge is-far">Distante &gt; 15 km</span>'
                + '</div><div class="delivery-route-legend-line">'
                + '<span class="delivery-route-legend-label">Cálculo</span>'
                + '<span class="delivery-route-badge is-normal">Valhalla rodoviário</span>'
                + '<span class="delivery-route-badge is-medium">Contingência geográfica</span>'
                + '</div><div class="delivery-route-rule">'
                + '<strong>Ordem inteligente:</strong> atraso como exceção prioritária, '
                + 'data prevista, período, coordenadas até 100 m, mesmo CEP, mesmo bairro, '
                + 'concentração regional, distância rodoviária e menor percurso. '
                + 'Cidade não participa do agrupamento. '
                + 'A rota iniciada e as validações operacionais permanecem preservadas.'
                + '</div>';
            accordion.append(legenda);

            const rotas = new Map();

            entregasEmRota.forEach(function (entrega) {
                const chave = [
                    entrega.veiculo || 'Veículo não definido',
                    entrega.motorista || 'Motorista não definido',
                ].join('|');

                if (! rotas.has(chave)) {
                    rotas.set(chave, []);
                }

                rotas.get(chave).push(entrega);
            });

            rotas.forEach(function (entregasRota) {
                const referencia = entregasRota[0];
                const detalhes = document.createElement('details');
                detalhes.className = 'delivery-route-accordion';
                detalhes.open = false;

                const resumo = document.createElement('summary');

                const blocoIdentificacao = document.createElement('div');
                blocoIdentificacao.className =
                    'delivery-route-summary-identity';

                const identificacao = document.createElement('span');
                identificacao.className =
                    'delivery-route-summary-name';
                identificacao.textContent = referencia.veiculo
                    + ' · '
                    + referencia.motorista;

                const datasPorChave = new Map();

                entregasRota.forEach(function (entrega) {
                    const chaveData = String(
                        entrega.data_chave
                        || entrega.data
                        || ''
                    ).trim();

                    if (chaveData === '') {
                        return;
                    }

                    const dataExistente = datasPorChave.get(
                        chaveData
                    );

                    if (dataExistente) {
                        dataExistente.atrasada =
                            dataExistente.atrasada
                            || Boolean(entrega.atrasada);
                        return;
                    }

                    const partesData = chaveData.split('-');
                    const dataCompleta = String(
                        entrega.data || chaveData
                    );

                    datasPorChave.set(
                        chaveData,
                        {
                            chave: chaveData,
                            completa: dataCompleta,
                            reduzida: partesData.length === 3
                                ? partesData[2] + '/' + partesData[1]
                                : dataCompleta.substring(0, 5),
                            atrasada: Boolean(entrega.atrasada),
                        }
                    );
                });

                const datasEntrega = Array.from(
                    datasPorChave.values()
                ).sort(function (a, b) {
                    return a.chave.localeCompare(b.chave);
                });

                blocoIdentificacao.append(identificacao);

                if (datasEntrega.length > 0) {
                    const blocoDatas = document.createElement('div');
                    blocoDatas.className =
                        'delivery-route-summary-dates';

                    const rotuloDatas = document.createElement('span');
                    rotuloDatas.textContent = datasEntrega.length === 1
                        ? 'Data:'
                        : 'Datas:';
                    blocoDatas.append(rotuloDatas);

                    datasEntrega.forEach(function (dataEntrega, indice) {
                        if (indice > 0) {
                            blocoDatas.append(
                                document.createTextNode(' - ')
                            );
                        }

                        const data = document.createElement('span');
                        data.className =
                            'delivery-route-summary-date'
                            + (dataEntrega.atrasada
                                ? ' is-overdue'
                                : '');
                        data.textContent = datasEntrega.length === 1
                            ? dataEntrega.completa
                            : dataEntrega.reduzida;
                        data.title = dataEntrega.completa;
                        blocoDatas.append(data);
                    });

                    blocoIdentificacao.append(blocoDatas);
                }

                const periodosAtendidos = Array.from(
                    new Set(
                        entregasRota
                            .map(function (entrega) {
                                return String(entrega.periodo || '').trim();
                            })
                            .filter(Boolean)
                    )
                );

                if (periodosAtendidos.length > 0) {
                    const blocoPeriodos = document.createElement('div');
                    blocoPeriodos.className =
                        'delivery-route-summary-neighborhoods';
                    blocoPeriodos.textContent = 'Período(s): ';

                    const periodos = document.createElement('strong');
                    periodos.textContent = periodosAtendidos.join(' • ');
                    blocoPeriodos.append(periodos);
                    blocoIdentificacao.append(blocoPeriodos);
                }

                const bairrosAtendidos = Array.from(
                    new Set(
                        entregasRota
                            .map(function (entrega) {
                                return String(
                                    entrega.bairro || ''
                                ).trim();
                            })
                            .filter(function (bairro) {
                                return bairro !== '';
                            })
                    )
                ).sort(function (a, b) {
                    return a.localeCompare(
                        b,
                        'pt-BR',
                        {
                            sensitivity: 'base',
                        }
                    );
                });

                if (bairrosAtendidos.length > 0) {
                    const blocoBairros = document.createElement('div');
                    blocoBairros.className =
                        'delivery-route-summary-neighborhoods';

                    const rotuloBairros = document.createElement('span');
                    rotuloBairros.textContent = bairrosAtendidos.length === 1
                        ? 'Bairro atendido: '
                        : 'Bairros atendidos: ';

                    const bairros = document.createElement('strong');
                    bairros.textContent = bairrosAtendidos.join(' • ');
                    bairros.title = bairrosAtendidos.join(' • ');

                    blocoBairros.append(rotuloBairros, bairros);
                    blocoIdentificacao.append(blocoBairros);
                }

                const quantidade = document.createElement('span');
                quantidade.className =
                    'delivery-route-summary-quantity';
                quantidade.textContent = entregasRota.length
                    + (entregasRota.length === 1
                        ? ' entrega'
                        : ' entregas');

                resumo.append(blocoIdentificacao, quantidade);
                detalhes.append(resumo);

                const conteudo = document.createElement('div');
                conteudo.className = 'delivery-route-accordion-content';

                entregasRota.forEach(function (entrega) {
                    const classificacao =
                        classificarSituacaoEntrega(entrega);
                    const distancia =
                        classificarDistanciaEntrega(entrega);
                    const parada = document.createElement('div');
                    parada.className = 'delivery-route-stop '
                        + classificacao.classe;
                    parada.tabIndex = 0;
                    parada.setAttribute('role', 'button');
                    parada.setAttribute(
                        'aria-label',
                        'Localizar entrega ' + entrega.codigo
                    );

                    const ordem = document.createElement('span');
                    ordem.className = 'delivery-route-order';
                    ordem.textContent = rotuloOrdemCurta(entrega);
                    ordem.title = rotuloOrdemInteligente(entrega);

                    const dados = document.createElement('div');

                    const documento = document.createElement('strong');
                    documento.textContent = entrega.codigo;

                    const badges = document.createElement('div');
                    badges.className = 'delivery-route-badges';

                    const badgeSituacao = document.createElement('span');
                    badgeSituacao.className = 'delivery-route-badge '
                        + classificacao.classe;
                    badgeSituacao.textContent = classificacao.rotulo;

                    const badgeDistancia = document.createElement('span');
                    badgeDistancia.className = 'delivery-route-badge '
                        + distancia.classe;
                    badgeDistancia.textContent = distancia.rotulo;
                    badgeDistancia.title = rotuloDistanciaEntrega(
                        entrega
                    );

                    badges.append(
                        badgeSituacao,
                        badgeDistancia
                    );

                    const badgeFonte = document.createElement('span');
                    badgeFonte.className = 'delivery-route-badge '
                        + (
                            fonteDistanciaEntrega(entrega) === 'valhalla'
                                ? 'is-normal'
                                : 'is-medium'
                        );
                    badgeFonte.textContent = rotuloFonteDistanciaEntrega(
                        entrega
                    );
                    badgeFonte.title = fonteDistanciaEntrega(entrega)
                        === 'valhalla'
                            ? 'Distância rodoviária calculada pelo Valhalla.'
                            : 'Estimativa geográfica usada como contingência.';
                    badges.append(badgeFonte);

                    const tempoEstimado = rotuloTempoEntrega(entrega);

                    if (tempoEstimado) {
                        const badgeTempo = document.createElement('span');
                        badgeTempo.className =
                            'delivery-route-badge is-normal';
                        badgeTempo.textContent = tempoEstimado;
                        badgeTempo.title = 'Tempo estimado do trecho';
                        badges.append(badgeTempo);
                    }

                    if (Boolean(entrega.atrasada)) {
                        const badgePrioridade = document.createElement(
                            'span'
                        );
                        badgePrioridade.className =
                            'delivery-route-badge is-overdue';
                        badgePrioridade.textContent =
                            'Prioridade por atraso';
                        badgePrioridade.title =
                            'Esta entrega ocupa a primeira posição da sua rota por estar atrasada.';
                        badges.append(badgePrioridade);
                    }

                    const equipe = document.createElement('span');
                    equipe.textContent = 'Placa: '
                        + entrega.veiculo
                        + ' · Motorista: '
                        + entrega.motorista;

                    const cliente = document.createElement('span');
                    cliente.textContent = 'Cliente: '
                        + entrega.cliente;

                    const telefone = document.createElement('span');
                    telefone.textContent = 'Telefone: '
                        + (entrega.telefone || 'Não informado');

                    const endereco = document.createElement('span');
                    endereco.textContent = 'Endereço: '
                        + entrega.endereco;

                    const previsao = document.createElement('span');
                    previsao.textContent = 'Previsão: '
                        + entrega.data
                        + ' · '
                        + entrega.periodo;

                    const trecho = document.createElement('span');
                    trecho.textContent = 'Trecho: '
                        + rotuloDistanciaEntrega(entrega);

                    const agrupamento = document.createElement('span');
                    agrupamento.textContent = 'Agrupamento: '
                        + (entrega.agrupamento_rota
                            || 'Distância sequencial');

                    dados.append(
                        documento,
                        badges,
                        equipe,
                        cliente,
                        telefone,
                        previsao,
                        trecho,
                        agrupamento,
                        endereco
                    );
                    parada.append(ordem, dados);

                    const localizarEntrega = function () {
                        const marcador = marcadores.get(
                            Number(entrega.id)
                        );

                        if (! marcador) {
                            return;
                        }

                        mapa.flyTo(
                            marcador.getLatLng(),
                            Math.max(mapa.getZoom(), 16),
                            {
                                duration: .55,
                            }
                        );
                        marcador.openPopup();
                    };

                    parada.addEventListener(
                        'click',
                        localizarEntrega
                    );
                    parada.addEventListener(
                        'keydown',
                        function (evento) {
                            if (
                                evento.key === 'Enter'
                                || evento.key === ' '
                            ) {
                                evento.preventDefault();
                                localizarEntrega();
                            }
                        }
                    );

                    conteudo.append(parada);
                });

                detalhes.append(conteudo);
                accordion.append(detalhes);
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

                const indicador = document.createElement('span');
                const classificacao =
                    classificarSituacaoEntrega(entrega);
                indicador.className = 'map-info-indicator '
                    + classificacao.classe;

                if (entrega.atrasada) {
                    const diasAtraso = calcularDiasAtraso(
                        entrega
                    );

                    indicador.textContent = 'Atrasada há '
                        + diasAtraso
                        + (diasAtraso === 1 ? ' dia' : ' dias');
                } else {
                    indicador.textContent = classificacao.rotulo;
                }

                linha.append(indicador);

                const cliente = document.createElement('div');
                cliente.textContent = entrega.cliente;
                linha.append(cliente);

                const previsao = document.createElement('span');
                previsao.className = 'map-info-status';
                previsao.textContent = 'Previsão: '
                    + entrega.data
                    + ' · '
                    + entrega.periodo;
                linha.append(previsao);

                const status = document.createElement('span');
                status.className = 'map-info-status';
                status.textContent = 'Status: ' + entrega.status;
                linha.append(status);

                const rota = document.createElement('span');
                rota.className = 'map-info-status';
                rota.textContent = 'Ordem: '
                    + rotuloOrdemInteligente(entrega)
                    + ' · Distância: '
                    + rotuloDistanciaEntrega(entrega)
                    + ' · Agrupamento: '
                    + (entrega.agrupamento_rota
                        || 'Distância sequencial');
                linha.append(rota);

                const contato = document.createElement('span');
                contato.className = 'map-info-status';
                contato.textContent = 'Contato: '
                    + entrega.telefone
                    + ' · Recebedor: '
                    + entrega.responsavel_recebimento;
                linha.append(contato);

                if (entrega.observacao_entrega) {
                    const observacao = document.createElement('span');
                    observacao.className = 'map-info-status';
                    observacao.textContent = 'Observação: '
                        + entrega.observacao_entrega;
                    linha.append(observacao);
                }

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
                        ponto.cor,
                        rotuloIndicadorEntregas(
                            ponto.grupo.entregas
                        )
                    ),
                    title: ponto.grupo.endereco
                        + ' · '
                        + rotuloIndicadorEntregas(
                            ponto.grupo.entregas
                        ),
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
                        pontos.length,
                        pontos.some(
                            ponto => ponto.atrasada
                        )
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

        function agruparPontosPorCepOuProximidade(
            mapa,
            pontos
        ) {
            const agrupamentos = [];

            pontos.forEach(function (ponto) {
                const cep = String(
                    ponto.grupo.cep || ''
                );

                const agrupamentoEncontrado =
                    agrupamentos.find(
                        function (agrupamento) {
                            const mesmoCep = cep !== ''
                                && agrupamento.ceps.has(cep);

                            const pontoProximo =
                                agrupamento.pontos.some(
                                    function (pontoExistente) {
                                        return mapa.distance(
                                            pontoExistente.posicao,
                                            ponto.posicao
                                        ) <= raioAgrupamentoMetros;
                                    }
                                );

                            return mesmoCep || pontoProximo;
                        }
                    );

                if (agrupamentoEncontrado) {
                    agrupamentoEncontrado.pontos.push(ponto);

                    if (cep !== '') {
                        agrupamentoEncontrado.ceps.add(cep);
                    }

                    return;
                }

                agrupamentos.push({
                    ceps: new Set(
                        cep !== '' ? [cep] : []
                    ),
                    pontos: [ponto],
                });
            });

            return agrupamentos.map(
                agrupamento => agrupamento.pontos
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

            const grupos = agruparEntregasPorEndereco(
                entregasMapa
            );

            const depositoValido =
                Number.isFinite(depositoMapa.latitude)
                && Number.isFinite(depositoMapa.longitude);

            if (! depositoValido) {
                if (mensagem) {
                    mensagem.textContent = 'As coordenadas do depósito não estão configuradas.';
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

            const painelRotas = document.getElementById(
                'mapa-rotas-painel'
            );

            const botaoPainelRotas = document.getElementById(
                'mapa-rotas-painel-toggle'
            );

            function centralizarMapa() {
                mapa.invalidateSize({
                    pan: false,
                });

                if (limites.isValid()) {
                    const painelAberto = painelRotas
                        && ! painelRotas.classList.contains(
                            'is-collapsed'
                        )
                        && envoltorioMapa
                        && envoltorioMapa.classList.contains(
                            'is-fullscreen'
                        )
                        && window.innerWidth >= 768;

                    mapa.fitBounds(
                        limites,
                        {
                            maxZoom: depositoMapa.zoom,
                            paddingTopLeft: [
                                painelAberto ? 360 : 52,
                                52,
                            ],
                            paddingBottomRight: [52, 52],
                        }
                    );

                    return;
                }

                mapa.setView(
                    [
                        depositoMapa.latitude,
                        depositoMapa.longitude,
                    ],
                    depositoMapa.zoom
                );
            }

            function centralizarMapaAposLayout() {
                window.requestAnimationFrame(
                    function () {
                        window.requestAnimationFrame(
                            centralizarMapa
                        );
                    }
                );

                window.setTimeout(
                    centralizarMapa,
                    120
                );

                window.setTimeout(
                    centralizarMapa,
                    400
                );
            }

            function fecharPainelRotas() {
                if (! painelRotas || ! botaoPainelRotas) {
                    return;
                }

                painelRotas.classList.add('is-collapsed');
                botaoPainelRotas.setAttribute(
                    'aria-expanded',
                    'false'
                );
                botaoPainelRotas.title = 'Abrir ordens de entrega';

                const icone = botaoPainelRotas.querySelector('i');

                if (icone) {
                    icone.className = 'bi bi-chevron-down';
                }

                painelRotas.querySelectorAll('details[open]')
                    .forEach(function (detalhes) {
                        detalhes.open = false;
                    });
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

                fecharPainelRotas();

                if (! expandido) {
                    mapa.closePopup();
                }

                centralizarMapaAposLayout();
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

            if (painelRotas && botaoPainelRotas) {
                botaoPainelRotas.addEventListener(
                    'click',
                    function () {
                        const recolhido = painelRotas.classList.toggle(
                            'is-collapsed'
                        );
                        const icone = botaoPainelRotas.querySelector('i');

                        botaoPainelRotas.setAttribute(
                            'aria-expanded',
                            recolhido ? 'false' : 'true'
                        );
                        botaoPainelRotas.title = recolhido
                            ? 'Abrir ordens de entrega'
                            : 'Fechar ordens de entrega';

                        if (icone) {
                            icone.className = recolhido
                                ? 'bi bi-chevron-down'
                                : 'bi bi-chevron-up';
                        }

                        if (recolhido) {
                            painelRotas.querySelectorAll('details[open]')
                                .forEach(function (detalhes) {
                                    detalhes.open = false;
                                });
                        }

                        centralizarMapaAposLayout();
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

            const gruposPatio = grupos.filter(
                function (grupo) {
                    return grupo.entregas.every(
                        function (entrega) {
                            return [
                                'Carregada',
                                'Liberada',
                            ].includes(
                                entrega.status_chave
                            );
                        }
                    );
                }
            );

            let indiceGrupoPatio = 0;

            for (
                let indice = 0;
                indice < grupos.length;
                indice++
            ) {
                const grupo = grupos[indice];

                const grupoNoPatio = grupo.entregas.every(
                    function (entrega) {
                        return [
                            'Carregada',
                            'Liberada',
                        ].includes(
                            entrega.status_chave
                        );
                    }
                );

                if (mensagem) {
                    mensagem.textContent = 'Carregando ponto '
                        + (indice + 1)
                        + ' de '
                        + grupos.length
                        + '...';
                }

                if (
                    ! grupoNoPatio
                    && (
                    ! Number.isFinite(grupo.latitude)
                    || ! Number.isFinite(grupo.longitude)
                    )
                ) {
                    naoLocalizados++;
                    continue;
                }

                const posicao = grupoNoPatio
                    ? calcularPosicaoPatio(
                        indiceGrupoPatio++,
                        gruposPatio.length,
                        95,
                        Math.PI / 6
                    )
                    : L.latLng(
                        grupo.latitude,
                        grupo.longitude
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
            }

            const pontosDemaisStatus = pontosLocalizados
                .map(function (ponto) {
                    const entregas = ponto.grupo.entregas.filter(
                        function (entrega) {
                            return entrega.status_chave !== 'Em_rota';
                        }
                    );

                    if (entregas.length === 0) {
                        return null;
                    }

                    return {
                        ...ponto,
                        atrasada: entregas.some(
                            entrega => entrega.atrasada
                        ),
                        cor: definirCorMarcador(entregas),
                        grupo: {
                            ...ponto.grupo,
                            entregas: entregas,
                        },
                    };
                })
                .filter(Boolean);

            const agrupamentosMapa =
                agruparPontosPorCepOuProximidade(
                    mapa,
                    pontosDemaisStatus
                );

            let agrupamentosSobrepostos = 0;

            agrupamentosMapa.forEach(function (pontos) {
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

            const resumoVeiculos = adicionarMarcadoresVeiculos(
                mapa,
                pontosLocalizados,
                limites
            );

            const resumoEntregasEmRota = adicionarEntregasEmRota(
                mapa,
                pontosLocalizados,
                limites
            );

            montarPainelOrdensEntrega(
                mapa,
                resumoEntregasEmRota.marcadores
            );

            centralizarMapaAposLayout();

            /*
             * O mapa operacional já está visível neste ponto.
             * A posição GPS é carregada em segundo plano para
             * não atrasar a renderização inicial.
             */
            const marcadoresRastreadosPorVeiculo =
                resumoVeiculos.marcadoresPorVeiculo;

            let atualizacaoGpsEmAndamento = false;

            async function atualizarVeiculosRastreados() {
                if (
                    atualizacaoGpsEmAndamento
                    || document.visibilityState !== 'visible'
                ) {
                    return;
                }

                atualizacaoGpsEmAndamento = true;

                try {
                    await adicionarMarcadoresVeiculosRastreados(
                        mapa,
                        limites,
                        marcadoresRastreadosPorVeiculo
                    );
                } finally {
                    atualizacaoGpsEmAndamento = false;
                }
            }

            atualizarVeiculosRastreados();

            window.setInterval(
                atualizarVeiculosRastreados,
                5000
            );

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
                    + ' · '
                    + resumoVeiculos.patio
                    + ' veículo(s) no pátio'
                    + ' · '
                    + resumoEntregasEmRota.total
                    + ' entrega(s) em rota'
                    + (
                        agrupamentosSobrepostos > 0
                            ? ' · '
                                + agrupamentosSobrepostos
                                + ' grupo(s) por CEP/proximidade: clique para expandir'
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas_inteligentes\index.blade.php ENDPATH**/ ?>