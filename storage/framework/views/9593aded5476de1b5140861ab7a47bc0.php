<?php $__env->startSection('content'); ?>

<?php
    $comparacao = $indicadores['comparacao'];

    $formatarVariacao = function ($item) {
        if ($item['percentual'] === null) {
            return 'Sem base anterior';
        }

        $sinal =
            $item['percentual'] > 0
                ? '+'
                : '';

        return
            $sinal
            . number_format(
                $item['percentual'],
                2,
                ',',
                '.'
            )
            . '%';
    };

    $classeVariacao = function ($item) {
        if ($item['direcao'] === 'alta') {
            return 'text-success';
        }

        if ($item['direcao'] === 'queda') {
            return 'text-danger';
        }

        return 'text-muted';
    };

    $iconeVariacao = function ($item) {
        if ($item['direcao'] === 'alta') {
            return 'bi-arrow-up-right';
        }

        if ($item['direcao'] === 'queda') {
            return 'bi-arrow-down-right';
        }

        return 'bi-dash';
    };
?>

<div class="container-fluid">

    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-speedometer2 me-2"></i>
                Visão Executiva

            </h1>

            <p class="text-muted mb-0">

                Indicadores consolidados do ERP.

            </p>

        </div>

        <a
            href="<?php echo e(route('bi.index')); ?>"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Central BI

        </a>

    </div>


    
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?php echo e(route('bi.executivo.index')); ?>"
                class="row g-3 align-items-end">

                <div class="col-md-4">

                    <label
                        for="data_inicio"
                        class="form-label">

                        Data inicial

                    </label>

                    <input
                        type="date"
                        class="form-control
                               <?php $__errorArgs = ['data_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                   is-invalid
                               <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        id="data_inicio"
                        name="data_inicio"
                        value="<?php echo e(old(
                            'data_inicio',
                            $inicio->format('Y-m-d')
                        )); ?>">

                    <?php $__errorArgs = ['data_inicio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>


                <div class="col-md-4">

                    <label
                        for="data_fim"
                        class="form-label">

                        Data final

                    </label>

                    <input
                        type="date"
                        class="form-control
                               <?php $__errorArgs = ['data_fim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                   is-invalid
                               <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        id="data_fim"
                        name="data_fim"
                        value="<?php echo e(old(
                            'data_fim',
                            $fim->format('Y-m-d')
                        )); ?>">

                    <?php $__errorArgs = ['data_fim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>


                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-primary w-100">

                        <i class="bi bi-funnel me-1"></i>
                        Aplicar período

                    </button>

                </div>

            </form>

        </div>

    </div>


    
    <div class="row g-3 mb-4">

        <div class="col-lg-6">

            <div class="alert alert-light border shadow-sm mb-0">

                <i class="bi bi-calendar3 me-2"></i>

                Período atual:

                <strong>
                    <?php echo e($inicio->format('d/m/Y')); ?>

                </strong>

                até

                <strong>
                    <?php echo e($fim->format('d/m/Y')); ?>

                </strong>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="alert alert-light border shadow-sm mb-0">

                <i class="bi bi-arrow-left-right me-2"></i>

                Comparação:

                <strong>
                    <?php echo e($comparacao[
                            'inicio_anterior'
                        ]->format('d/m/Y')); ?>

                </strong>

                até

                <strong>
                    <?php echo e($comparacao[
                            'fim_anterior'
                        ]->format('d/m/Y')); ?>

                </strong>

            </div>

        </div>

    </div>


    
    <div class="row g-4">


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Faturamento
                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        <?php echo e(number_format(
                            $indicadores['faturamento'],
                            2,
                            ',',
                            '.'
                        )); ?>


                    </div>

                    <div class="small mt-2 <?php echo e($classeVariacao(
                        $comparacao['faturamento']
                    )); ?>">

                        <i class="bi <?php echo e($iconeVariacao(
                                $comparacao['faturamento']
                            )); ?>"></i>

                        <?php echo e($formatarVariacao(
                                $comparacao['faturamento']
                            )); ?>


                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Vendas finalizadas
                    </div>

                    <div class="fs-3 fw-bold">

                        <?php echo e(number_format(
                                $indicadores[
                                    'vendas_finalizadas'
                                ],
                                0,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small mt-2 <?php echo e($classeVariacao(
                        $comparacao[
                            'vendas_finalizadas'
                        ]
                    )); ?>">

                        <i class="bi <?php echo e($iconeVariacao(
                                $comparacao[
                                    'vendas_finalizadas'
                                ]
                            )); ?>"></i>

                        <?php echo e($formatarVariacao(
                                $comparacao[
                                    'vendas_finalizadas'
                                ]
                            )); ?>


                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">
                        Ticket médio
                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        <?php echo e(number_format(
                                $indicadores[
                                    'ticket_medio'
                                ],
                                2,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small mt-2 <?php echo e($classeVariacao(
                        $comparacao[
                            'ticket_medio'
                        ]
                    )); ?>">

                        <i class="bi <?php echo e($iconeVariacao(
                                $comparacao[
                                    'ticket_medio'
                                ]
                            )); ?>"></i>

                        <?php echo e($formatarVariacao(
                                $comparacao[
                                    'ticket_medio'
                                ]
                            )); ?>


                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Estoque a custo atual

                    </div>

                    <div class="fs-3 fw-bold">

                        R$
                        <?php echo e(number_format(
                                $indicadores[
                                    'valor_estoque_atual'
                                ],
                                2,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small text-muted mt-2">

                        Estoque atual × custo real de entrada

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Compras recebidas

                    </div>

                    <div class="fs-4 fw-bold">

                        R$
                        <?php echo e(number_format(
                                $indicadores[
                                    'compras_recebidas_valor'
                                ],
                                2,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small mt-2 <?php echo e($classeVariacao(
                        $comparacao[
                            'compras_recebidas_valor'
                        ]
                    )); ?>">

                        <i class="bi <?php echo e($iconeVariacao(
                                $comparacao[
                                    'compras_recebidas_valor'
                                ]
                            )); ?>"></i>

                        <?php echo e($formatarVariacao(
                                $comparacao[
                                    'compras_recebidas_valor'
                                ]
                            )); ?>


                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Orçamentos faturados

                    </div>

                    <div class="fs-3 fw-bold">

                        <?php echo e(number_format(
                                $indicadores[
                                    'orcamentos_faturados_quantidade'
                                ],
                                0,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small text-muted mt-2">

                        R$
                        <?php echo e(number_format(
                                $indicadores[
                                    'orcamentos_faturados_valor'
                                ],
                                2,
                                ',',
                                '.'
                            )); ?>


                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Entregas concluídas

                    </div>

                    <div class="fs-3 fw-bold">

                        <?php echo e(number_format(
                                $indicadores[
                                    'entregas_concluidas'
                                ],
                                0,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small mt-2 <?php echo e($classeVariacao(
                        $comparacao[
                            'entregas_concluidas'
                        ]
                    )); ?>">

                        <i class="bi <?php echo e($iconeVariacao(
                                $comparacao[
                                    'entregas_concluidas'
                                ]
                            )); ?>"></i>

                        <?php echo e($formatarVariacao(
                                $comparacao[
                                    'entregas_concluidas'
                                ]
                            )); ?>


                        vs período anterior

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Entregas em aberto

                    </div>

                    <div class="fs-3 fw-bold">

                        <?php echo e(number_format(
                                $indicadores[
                                    'entregas_em_aberto'
                                ],
                                0,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small text-muted mt-2">

                        Situação operacional atual

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Movimentações de caixa

                    </div>

                    <div class="fs-3 fw-bold">

                        <?php echo e(number_format(
                                $indicadores[
                                    'movimentacoes_caixa'
                                ],
                                0,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small text-muted mt-2">

                        Registros no período

                    </div>

                </div>

            </div>

        </div>


        
        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <div class="text-muted small mb-1">

                        Sangrias

                    </div>

                    <div class="fs-4 fw-bold">

                        R$
                        <?php echo e(number_format(
                                $indicadores[
                                    'valor_sangrias'
                                ],
                                2,
                                ',',
                                '.'
                            )); ?>


                    </div>

                    <div class="small text-muted mt-2">

                        Valor registrado no período

                    </div>

                </div>

            </div>

        </div>

    </div>


    
    <div class="row g-4 mt-1">

        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-graph-up me-2"></i>
                        Faturamento por dia

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoFaturamento"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-bar-chart me-2"></i>
                        Vendas por dia

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoVendas"></canvas>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-xl-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h5 class="card-title">

                        <i class="bi bi-truck me-2"></i>
                        Entregas por status

                    </h5>

                    <div style="height: 320px;">

                        <canvas id="graficoEntregas"></canvas>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="alert alert-secondary mt-4 mb-0">

        <i class="bi bi-info-circle me-2"></i>

        Lucro e margem permanecem fora desta versão.
        O custo histórico da venda será homologado antes
        desses indicadores serem disponibilizados.

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js">
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const faturamento = <?php echo json_encode(
        $indicadores['series']['faturamento_diario']
    , 15, 512) ?>;

    const vendas = <?php echo json_encode(
        $indicadores['series']['vendas_diarias']
    , 15, 512) ?>;

    const entregas = <?php echo json_encode(
        $indicadores['series']['entregas_por_status']
    , 15, 512) ?>;


    new Chart(
        document.getElementById('graficoFaturamento'),
        {
            type: 'line',

            data: {
                labels: faturamento.labels,

                datasets: [
                    {
                        label: 'Faturamento (R$)',
                        data: faturamento.valores,
                        tension: 0.25,
                        fill: false
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        ticks: {
                            callback: function (value) {
                                return 'R$ ' +
                                    Number(value).toLocaleString(
                                        'pt-BR',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }
                                    );
                            }
                        }
                    }
                },

                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'R$ ' +
                                    Number(
                                        context.raw
                                    ).toLocaleString(
                                        'pt-BR',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }
                                    );
                            }
                        }
                    }
                }
            }
        }
    );


    new Chart(
        document.getElementById('graficoVendas'),
        {
            type: 'bar',

            data: {
                labels: vendas.labels,

                datasets: [
                    {
                        label: 'Vendas finalizadas',
                        data: vendas.valores
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );


    new Chart(
        document.getElementById('graficoEntregas'),
        {
            type: 'bar',

            data: {
                labels: entregas.labels,

                datasets: [
                    {
                        label: 'Entregas',
                        data: entregas.valores
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                indexAxis: 'y',

                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\bi\executivo\index.blade.php ENDPATH**/ ?>