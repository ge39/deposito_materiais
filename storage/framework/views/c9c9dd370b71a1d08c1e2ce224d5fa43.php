<?php $__env->startSection('content'); ?>

<style>
.bi-logistica-card {
    height: 142px;
    min-height: 142px;
    border-width: 2px;
}

.bi-logistica-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    padding: .55rem 1rem .60rem;
}

.bi-logistica-icon {
    font-size: 1.30rem;
    line-height: 1;
}

.bi-logistica-label {
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.1;
    text-transform: uppercase;
}

.bi-logistica-value {
    font-size: 1.28rem;
    font-weight: 700;
    line-height: 1.05;
}

.bi-logistica-detail {
    font-size: .78rem;
    text-decoration: none;
}

.bi-logistica-detail:hover {
    text-decoration: underline;
}

.modal-bi .modal-dialog {
    max-width: 1200px;
}

.modal-bi .modal-body {
    max-height: 72vh;
    overflow-y: auto;
}

.modal-bi table th {
    white-space: nowrap;
}
</style>


<div class="container py-4">

    

    <div class="d-flex justify-content-between align-items-start mb-4">

        <div>
            <h2 class="mb-1">
                <i class="bi bi-truck me-2"></i>
                Entregas &amp; Log&iacute;stica
            </h2>

            <div class="text-muted">
                Entregas realizadas, atrasos, ocorr&ecirc;ncias,
                romaneios, ve&iacute;culos e motoristas.
            </div>
        </div>

        <a
            href="<?php echo e(route('bi.index')); ?>"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>

    </div>


    

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET" action="<?php echo e(route('bi.logistica.index')); ?>">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data inicial</label>

                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="<?php echo e($inicio->format('Y-m-d')); ?>"
                        >
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Data final</label>

                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="<?php echo e($fim->format('Y-m-d')); ?>"
                        >
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">

                            <option value="">Todos</option>

                            <?php $__currentLoopData = $statusDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($opcao); ?>"
                                    <?php if($status === $opcao): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($opcao); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Per&iacute;odo</label>

                        <select name="periodo" class="form-select">

                            <option value="">Todos</option>

                            <option value="manha" <?php if($periodo === 'manha'): echo 'selected'; endif; ?>>
                                Manh&atilde;
                            </option>

                            <option value="tarde" <?php if($periodo === 'tarde'): echo 'selected'; endif; ?>>
                                Tarde
                            </option>

                            <option value="comercial" <?php if($periodo === 'comercial'): echo 'selected'; endif; ?>>
                                Comercial
                            </option>

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Motorista</label>

                        <select name="motorista_id" class="form-select">

                            <option value="">Todos</option>

                            <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($motorista->id); ?>"
                                    <?php if($motoristaId === (int) $motorista->id): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($motorista->nome); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                    </div>


                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Ve&iacute;culo</label>

                        <select name="veiculo_id" class="form-select">

                            <option value="">Todos</option>

                            <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($veiculo->id); ?>"
                                    <?php if($veiculoId === (int) $veiculo->id): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($veiculo->placa); ?>

                                    <?php if($veiculo->modelo): ?>
                                        - <?php echo e($veiculo->modelo); ?>

                                    <?php endif; ?>
                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                    </div>


                    <div class="col-12">

                        <div class="d-flex gap-2 justify-content-end">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Aplicar
                            </button>

                            <a
                                href="<?php echo e(route('bi.logistica.index')); ?>"
                                class="btn btn-outline-secondary"
                            >
                                Limpar
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    

    <div class="row g-4">

        <?php
            $cards = [
                [
                    'titulo' => 'Entregas Previstas',
                    'valor' => $totalEntregas,
                    'icone' => 'bi-calendar-check',
                    'cor' => 'primary',
                    'modal' => 'modalEntregas',
                ],
                [
                    'titulo' => 'Entregues',
                    'valor' => $entregues,
                    'icone' => 'bi-check-circle',
                    'cor' => 'success',
                    'modal' => 'modalEntregues',
                ],
                [
                    'titulo' => 'Entregues c/ Ocorrência',
                    'valor' => $entreguesComOcorrencia,
                    'icone' => 'bi-exclamation-circle',
                    'cor' => 'warning',
                    'modal' => 'modalEntreguesOcorrencia',
                ],
                [
                    'titulo' => 'Entregas Parciais',
                    'valor' => $entregasParciais,
                    'icone' => 'bi-circle-half',
                    'cor' => 'warning',
                    'modal' => 'modalParciais',
                ],
                [
                    'titulo' => 'Não Entregues',
                    'valor' => $naoEntregues,
                    'icone' => 'bi-x-circle',
                    'cor' => 'danger',
                    'modal' => 'modalNaoEntregues',
                ],
                [
                    'titulo' => 'Em Rota',
                    'valor' => $emRota,
                    'icone' => 'bi-truck',
                    'cor' => 'primary',
                    'modal' => 'modalEmRota',
                ],
                [
                    'titulo' => 'Atrasadas',
                    'valor' => $atrasadas,
                    'icone' => 'bi-clock-history',
                    'cor' => 'danger',
                    'modal' => 'modalAtrasadas',
                ],
                [
                    'titulo' => 'Ocorrências',
                    'valor' => $totalOcorrencias,
                    'icone' => 'bi-exclamation-triangle',
                    'cor' => 'warning',
                    'modal' => 'modalOcorrencias',
                ],
                [
                    'titulo' => 'Romaneios',
                    'valor' => $totalRomaneios,
                    'icone' => 'bi-clipboard-check',
                    'cor' => 'secondary',
                    'modal' => 'modalRomaneios',
                ],
                [
                    'titulo' => 'Saídas Registradas',
                    'valor' => $saidasRegistradas,
                    'icone' => 'bi-box-arrow-right',
                    'cor' => 'info',
                    'modal' => 'modalSaidas',
                ],
                [
                    'titulo' => 'Motoristas Utilizados',
                    'valor' => $motoristasUtilizados,
                    'icone' => 'bi-person-badge',
                    'cor' => 'info',
                    'modal' => 'modalMotoristas',
                ],
                [
                    'titulo' => 'Veículos Utilizados',
                    'valor' => $veiculosUtilizados,
                    'icone' => 'bi-truck-front',
                    'cor' => 'info',
                    'modal' => 'modalVeiculos',
                ],
            ];
        ?>


        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="col-lg-4 col-md-6">

                <div class="card bi-logistica-card border-<?php echo e($card['cor']); ?> shadow-sm">

                    <div class="card-body">

                        <i class="bi <?php echo e($card['icone']); ?> bi-logistica-icon text-<?php echo e($card['cor']); ?>"></i>

                        <div class="bi-logistica-label">
                            <?php echo e($card['titulo']); ?>

                        </div>

                        <div class="bi-logistica-value">
                            <?php echo e($card['valor']); ?>

                        </div>

                        <a
                            href="#"
                            class="bi-logistica-detail text-<?php echo e($card['cor']); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#<?php echo e($card['modal']); ?>"
                        >
                            Ver detalhes
                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>

                    </div>

                </div>

            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

</div>




<?php
    $modaisEntrega = [
        [
            'id' => 'modalEntregas',
            'titulo' => 'Entregas Previstas',
            'icone' => 'bi-calendar-check',
            'cor' => 'primary',
            'dados' => $entregasPrevistasDetalhes,
        ],
        [
            'id' => 'modalEntregues',
            'titulo' => 'Entregas Realizadas',
            'icone' => 'bi-check-circle',
            'cor' => 'success',
            'dados' => $entreguesDetalhes,
        ],
        [
            'id' => 'modalEntreguesOcorrencia',
            'titulo' => 'Entregues com Ocorrência',
            'icone' => 'bi-exclamation-circle',
            'cor' => 'warning',
            'dados' => $entreguesComOcorrenciaDetalhes,
        ],
        [
            'id' => 'modalParciais',
            'titulo' => 'Entregas Parciais',
            'icone' => 'bi-circle-half',
            'cor' => 'warning',
            'dados' => $parciaisDetalhes,
        ],
        [
            'id' => 'modalNaoEntregues',
            'titulo' => 'Não Entregues',
            'icone' => 'bi-x-circle',
            'cor' => 'danger',
            'dados' => $naoEntreguesDetalhes,
        ],
        [
            'id' => 'modalEmRota',
            'titulo' => 'Entregas em Rota',
            'icone' => 'bi-truck',
            'cor' => 'primary',
            'dados' => $emRotaDetalhes,
        ],
    ];
?>


<?php $__currentLoopData = $modaisEntrega; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<div class="modal fade modal-bi" id="<?php echo e($modal['id']); ?>" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi <?php echo e($modal['icone']); ?> text-<?php echo e($modal['cor']); ?> me-2"></i>

                    <?php echo e($modal['titulo']); ?>


                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-<?php echo e($modal['cor']); ?>">

                    Total:
                    <strong>
                        <?php echo e($modal['dados']->count()); ?>

                    </strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th>Realizada</th>
                                <th>Per&iacute;odo</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Endere&ccedil;o</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $modal['dados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($linha->codigo_entrega ?: ('#' . $linha->id)); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->data_prevista
                                        ? \Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y')
                                        : '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->data_realizada
                                        ? \Carbon\Carbon::parse($linha->data_realizada)->format('d/m/Y')
                                        : '—'); ?>

                                </td>

                                <td>
                                    <?php echo e(ucfirst($linha->periodo_entrega ?: '—')); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->status); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->motorista_nome ?: '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->placa ?: '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->endereco_entrega ?: '—'); ?>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted"
                                >
                                    Nenhum registro encontrado.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>




<div class="modal fade modal-bi" id="modalAtrasadas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-clock-history text-danger me-2"></i>

                    Entregas Atrasadas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-danger">

                    Total:
                    <strong><?php echo e($atrasadas); ?></strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th class="text-end">Dias atraso</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Endere&ccedil;o</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $atrasadasDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($linha->codigo_entrega ?: ('#' . $linha->id)); ?>

                                </td>

                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y')); ?>

                                </td>

                                <td class="text-end fw-bold text-danger">
                                    <?php echo e($linha->dias_atraso); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->status); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->motorista_nome ?: '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->placa ?: '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->endereco_entrega ?: '—'); ?>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma entrega atrasada.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalOcorrencias" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>

                    Ocorr&ecirc;ncias

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-warning">

                    Total:
                    <strong><?php echo e($totalOcorrencias); ?></strong>

                </div>


                <h6>Resumo por categoria</h6>

                <div class="table-responsive mb-4">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Categoria</th>
                                <th class="text-end">Quantidade</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $ocorrenciasPorCategoria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>
                                <td><?php echo e($linha->categoria ?: 'N&atilde;o informada'); ?></td>
                                <td class="text-end"><?php echo e($linha->total); ?></td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="2" class="text-center text-muted">
                                    Nenhuma ocorr&ecirc;ncia encontrada.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <h6>Detalhamento</h6>

                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Entrega</th>
                                <th>Romaneio</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Classifica&ccedil;&atilde;o</th>
                                <th>Criticidade</th>
                                <th>Status</th>
                                <th class="text-end">Quantidade</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $ocorrenciasDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td><?php echo e($linha->codigo_entrega ?: '—'); ?></td>

                                <td>#<?php echo e($linha->romaneio_id); ?></td>

                                <td><?php echo e($linha->categoria ?: '—'); ?></td>

                                <td><?php echo e($linha->tipo ?: '—'); ?></td>

                                <td><?php echo e($linha->classificacao_inicial ?: '—'); ?></td>

                                <td><?php echo e($linha->criticidade ?: '—'); ?></td>

                                <td><?php echo e($linha->status ?: '—'); ?></td>

                                <td class="text-end">
                                    <?php echo e(number_format((float) ($linha->quantidade_envolvida ?? 0), 2, ',', '.')); ?>

                                </td>

                                <td><?php echo e($linha->motorista_nome ?: '—'); ?></td>

                                <td><?php echo e($linha->placa ?: '—'); ?></td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td colspan="10" class="text-center text-muted">
                                    Nenhuma ocorr&ecirc;ncia encontrada.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalRomaneios" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-clipboard-check text-secondary me-2"></i>

                    Romaneios

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-secondary">

                    Total:
                    <strong><?php echo e($totalRomaneios); ?></strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Romaneio</th>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Sa&iacute;da</th>
                                <th>Retorno</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $romaneiosDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>#<?php echo e($linha->id); ?></td>

                                <td><?php echo e($linha->codigo_entrega ?: ('#' . $linha->entrega_id)); ?></td>

                                <td>
                                    <?php echo e($linha->data_prevista
                                        ? \Carbon\Carbon::parse($linha->data_prevista)->format('d/m/Y')
                                        : '—'); ?>

                                </td>

                                <td><?php echo e($linha->status); ?></td>

                                <td><?php echo e($linha->motorista_nome ?: '—'); ?></td>

                                <td><?php echo e($linha->placa ?: '—'); ?></td>

                                <td>
                                    <?php echo e($linha->data_saida
                                        ? \Carbon\Carbon::parse($linha->data_saida)->format('d/m/Y H:i')
                                        : '—'); ?>

                                </td>

                                <td>
                                    <?php echo e($linha->data_retorno
                                        ? \Carbon\Carbon::parse($linha->data_retorno)->format('d/m/Y H:i')
                                        : '—'); ?>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Nenhum romaneio encontrado.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalSaidas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-box-arrow-right text-info me-2"></i>

                    Sa&iacute;das Registradas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Total:
                    <strong><?php echo e($saidasRegistradas); ?></strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Data / Hora</th>
                                <th>Romaneio</th>
                                <th>Entrega</th>
                                <th>Motorista</th>
                                <th>Ve&iacute;culo</th>
                                <th>Status anterior</th>
                                <th>Status novo</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $saidasDetalhes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($linha->ocorrido_em)->format('d/m/Y H:i')); ?>

                                </td>

                                <td>#<?php echo e($linha->romaneio_id); ?></td>

                                <td><?php echo e($linha->codigo_entrega ?: '—'); ?></td>

                                <td><?php echo e($linha->motorista_nome ?: '—'); ?></td>

                                <td>
                                    <?php echo e($linha->placa ?: '—'); ?>

                                    <?php if($linha->modelo): ?>
                                        - <?php echo e($linha->modelo); ?>

                                    <?php endif; ?>
                                </td>

                                <td><?php echo e($linha->status_anterior ?: '—'); ?></td>

                                <td><?php echo e($linha->status_novo ?: '—'); ?></td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Nenhuma sa&iacute;da registrada.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalMotoristas" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-person-badge text-info me-2"></i>

                    Ranking de Motoristas

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Motoristas utilizados:
                    <strong><?php echo e($motoristasUtilizados); ?></strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Motorista</th>
                                <th class="text-end">Romaneios</th>
                                <th class="text-end">Entregues</th>
                                <th class="text-end">C/ ocorr.</th>
                                <th class="text-end">Parciais</th>
                                <th class="text-end">N&atilde;o entregues</th>
                                <th class="text-end">Em rota</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $rankingMotoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td class="fw-semibold">
                                    <?php echo e($linha->nome ?: ('Motorista #' . $linha->motorista_id)); ?>

                                </td>

                                <td class="text-end"><?php echo e($linha->romaneios); ?></td>

                                <td class="text-end"><?php echo e($linha->entregues); ?></td>

                                <td class="text-end"><?php echo e($linha->entregues_com_ocorrencia); ?></td>

                                <td class="text-end"><?php echo e($linha->parciais); ?></td>

                                <td class="text-end"><?php echo e($linha->nao_entregues); ?></td>

                                <td class="text-end"><?php echo e($linha->em_rota); ?></td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td colspan="7" class="text-center text-muted">
                                    Nenhum motorista encontrado.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>




<div class="modal fade modal-bi" id="modalVeiculos" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-truck-front text-info me-2"></i>

                    Ranking de Ve&iacute;culos

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-info">

                    Ve&iacute;culos utilizados:
                    <strong><?php echo e($veiculosUtilizados); ?></strong>

                </div>


                <div class="table-responsive">

                    <table class="table table-sm table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th class="text-end">Romaneios</th>
                                <th class="text-end">Entregues</th>
                                <th class="text-end">C/ ocorr.</th>
                                <th class="text-end">Parciais</th>
                                <th class="text-end">N&atilde;o entregues</th>
                                <th class="text-end">Em rota</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $rankingVeiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td class="fw-semibold">
                                    <?php echo e($linha->placa ?: ('Veículo #' . $linha->veiculo_id)); ?>

                                </td>

                                <td><?php echo e($linha->modelo ?: '—'); ?></td>

                                <td class="text-end"><?php echo e($linha->romaneios); ?></td>

                                <td class="text-end"><?php echo e($linha->entregues); ?></td>

                                <td class="text-end"><?php echo e($linha->entregues_com_ocorrencia); ?></td>

                                <td class="text-end"><?php echo e($linha->parciais); ?></td>

                                <td class="text-end"><?php echo e($linha->nao_entregues); ?></td>

                                <td class="text-end"><?php echo e($linha->em_rota); ?></td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td colspan="8" class="text-center text-muted">
                                    Nenhum ve&iacute;culo encontrado.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\bi\logistica\index.blade.php ENDPATH**/ ?>