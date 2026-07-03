

<?php $__env->startSection('content'); ?>

<?php
    $statusAtual = strtolower($entrega->status ?? '');

    $statusLabels = [
        'pendente_pagamento'   => 'Pendente pagamento',
        'aguardando_separacao' => 'Aguardando separação',
        'separando'            => 'Separando',
        'carregado'            => 'Carregado',
        'em_rota'              => 'Em rota',
        'entregue'             => 'Entregue',
        'parcial'              => 'Parcial',
        'devolvido'            => 'Devolvido',
        'cancelado'            => 'Cancelado',
    ];

    $statusClasses = [
        'pendente_pagamento'   => 'bg-secondary',
        'aguardando_separacao' => 'bg-warning text-dark',
        'separando'            => 'bg-primary',
        'carregado'            => 'bg-info text-dark',
        'em_rota'              => 'bg-dark',
        'entregue'             => 'bg-success',
        'parcial'              => 'bg-warning text-dark',
        'devolvido'            => 'bg-danger',
        'cancelado'            => 'bg-danger',
    ];

    $progressoStatus = [
        'pendente_pagamento'   => 10,
        'aguardando_separacao' => 20,
        'separando'            => 40,
        'carregado'            => 60,
        'em_rota'              => 80,
        'parcial'              => 85,
        'entregue'             => 100,
        'devolvido'            => 100,
        'cancelado'            => 100,
    ];

    $percentual = $progressoStatus[$statusAtual] ?? 0;

    $dataPrevista = $entrega->data_prevista_entrega
        ? \Carbon\Carbon::parse($entrega->data_prevista_entrega)
        : ($entrega->data_prevista ? \Carbon\Carbon::parse($entrega->data_prevista) : null);

    $dataRealizada = $entrega->data_realizada
        ? \Carbon\Carbon::parse($entrega->data_realizada)
        : null;

    $periodoEntrega = $entrega->periodo_entrega ?? null;

    $observacaoEntrega = $entrega->observacao_entrega
        ?? $entrega->observacao
        ?? null;

    $totalItens = $entrega->itens ? $entrega->itens->count() : 0;

    $itensEntregues = $entrega->itens
        ? $entrega->itens->where('status', 'entregue')->count()
        : 0;

    $mapsUrl = $entrega->endereco_entrega
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($entrega->endereco_entrega)
        : null;

    $etapas = [
        'Entrega criada'       => ['pendente_pagamento', 'aguardando_separacao', 'separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Venda faturada'       => ['aguardando_separacao', 'separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Separação iniciada'   => ['separando', 'carregado', 'em_rota', 'parcial', 'entregue'],
        'Carga preparada'      => ['carregado', 'em_rota', 'parcial', 'entregue'],
        'Saiu para entrega'    => ['em_rota', 'parcial', 'entregue'],
        'Entrega concluída'    => ['entregue'],
    ];
?>

<style>
    .kpi-card {
        border-radius: 6px;
    }

    .kpi-card .card-body {
        padding: 10px 12px;
    }

    .kpi-card small {
        font-size: .72rem;
        color: #6c757d;
    }

    .kpi-card h5 {
        margin: 0;
        font-weight: 700;
    }

    .timeline-entrega {
        position: relative;
        padding-left: 28px;
    }

    .timeline-entrega::before {
        content: "";
        position: absolute;
        left: 9px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 18px;
    }

    .timeline-icon {
        position: absolute;
        left: -28px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
    }

    .table-itens th,
    .table-itens td {
        vertical-align: middle;
        white-space: nowrap;
    }
</style>

<div class="container-fluid px-2">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                Fluxo da Entrega #<?php echo e($entrega->codigo_entrega ?? $entrega->id); ?>

            </h4>
            <small class="text-muted">
                Acompanhe todas as etapas da entrega, desde a geração até a conclusão.
            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>

            <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>Imprimir
            </button>

            <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Atualizar
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-2">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-2">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-3">

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>STATUS</small>
                    <h5>
                        <span class="badge <?php echo e($statusClasses[$statusAtual] ?? 'bg-secondary'); ?>">
                            <?php echo e($statusLabels[$statusAtual] ?? ucfirst(str_replace('_', ' ', $entrega->status))); ?>

                        </span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PREVISÃO</small>
                    <h5><?php echo e($dataPrevista ? $dataPrevista->format('d/m/Y') : '-'); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PERÍODO</small>
                    <h5><?php echo e($periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-'); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>ITENS</small>
                    <h5><?php echo e($itensEntregues); ?>/<?php echo e($totalItens); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>PROGRESSO</small>
                    <h5><?php echo e($percentual); ?>%</h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm kpi-card h-100">
                <div class="card-body">
                    <small>TIPO</small>
                    <h5>
                        <?php if($entrega->tipo_entrega === 'retira_loja'): ?>
                            <span class="badge bg-secondary">Retira loja</span>
                        <?php else: ?>
                            <span class="badge bg-info text-dark">Entrega</span>
                        <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>

    </div>

    
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
                <strong>Andamento da entrega</strong>
                <span><?php echo e($percentual); ?>%</span>
            </div>

            <div class="progress" style="height: 14px;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: <?php echo e($percentual); ?>%;">
                    <?php echo e($percentual); ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        
        <div class="col-md-8">

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-info-circle me-2"></i>Dados da Entrega</strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Código</small>
                            <div class="fw-semibold"><?php echo e($entrega->codigo_entrega ?? '-'); ?></div>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Venda</small>
                            <div class="fw-semibold"><?php echo e($entrega->venda_id ?? '-'); ?></div>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Orçamento</small>
                            <div class="fw-semibold"><?php echo e($entrega->orcamento_id ?? '-'); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Data prevista</small>
                            <div class="fw-semibold">
                                <?php echo e($dataPrevista ? $dataPrevista->format('d/m/Y') : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Período da entrega</small>
                            <div class="fw-semibold">
                                <?php echo e($periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Data realizada</small>
                            <div>
                                <?php echo e($dataRealizada ? $dataRealizada->format('d/m/Y') : '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Responsável</small>
                            <div class="fw-semibold"><?php echo e($entrega->responsavel_recebimento ?? '-'); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Telefone</small>
                            <div><?php echo e($entrega->telefone_recebimento ?? '-'); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Motorista</small>
                            <div><?php echo e($entrega->motorista->name ?? $entrega->motorista->nome ?? '-'); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Veículo</small>
                            <div><?php echo e($entrega->veiculo->placa ?? '-'); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Frete</small>
                            <div>
                                <?php if($entrega->cobrar_frete): ?>
                                    R$ <?php echo e(number_format($entrega->valor_frete ?? 0, 2, ',', '.')); ?>

                                <?php else: ?>
                                    Sem cobrança
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <small class="text-muted">Observação da entrega</small>
                            <div><?php echo e($observacaoEntrega ?? '-'); ?></div>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-person-vcard me-2"></i>Dados do Cliente</strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                <?php echo e($entrega->venda->cliente->nome 
                                    ?? $entrega->orcamento->cliente->nome 
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Telefone</small>
                            <div>
                                <?php echo e($entrega->venda->cliente->telefone 
                                    ?? $entrega->orcamento->cliente->telefone 
                                    ?? '-'); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Documento</small>
                            <div>
                                <?php echo e($entrega->venda->cliente->cpf_cnpj 
                                    ?? $entrega->orcamento->cliente->cpf_cnpj 
                                    ?? '-'); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-geo-alt me-2"></i>Endereço de Entrega</strong>

                    <?php if($mapsUrl): ?>
                        <a href="<?php echo e($mapsUrl); ?>" target="_blank" class="btn btn-light btn-sm">
                            <i class="bi bi-map me-1"></i>Abrir no Maps
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="fw-semibold">
                        <?php echo e($entrega->endereco_entrega ?? 'Endereço não informado'); ?>

                    </div>

                    <small class="text-muted">
                        Usar endereço do cliente:
                        <?php echo e($entrega->usar_endereco_cliente ? 'Sim' : 'Não'); ?>

                    </small>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-box-seam me-2"></i>Itens da Entrega</strong>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0 table-itens">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Produto</th>
                                    <th>Origem</th>
                                    <th>Qtd. Venda/Orçamento</th>
                                    <th>Previsto</th>
                                    <th>Entregue</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                    $itensBase = collect();

                                    if ($entrega->venda_id && $entrega->venda && $entrega->venda->itens) {
                                        $itensBase = $entrega->venda->itens;
                                        $origemItens = 'Venda';
                                    } elseif ($entrega->orcamento_id && $entrega->orcamento && $entrega->orcamento->itens) {
                                        $itensBase = $entrega->orcamento->itens;
                                        $origemItens = 'Orçamento';
                                    } else {
                                        $origemItens = '-';
                                    }

                                    $itensOperacionais = $entrega->itens ?? collect();

                                    $statusItemClasses = [
                                        'pendente'  => 'bg-secondary',
                                        'separado'  => 'bg-primary',
                                        'carregado' => 'bg-info text-dark',
                                        'entregue'  => 'bg-success',
                                        'parcial'   => 'bg-warning text-dark',
                                        'devolvido' => 'bg-danger',
                                        'cancelado' => 'bg-danger',
                                    ];
                                ?>

                                <?php $__empty_1 = true; $__currentLoopData = $itensBase; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemBase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $entregaItem = null;

                                        if ($origemItens === 'Venda') {
                                            $entregaItem = $itensOperacionais
                                                ->where('venda_item_id', $itemBase->id)
                                                ->first();
                                        }

                                        if (!$entregaItem && $origemItens === 'Orçamento') {
                                            $entregaItem = $itensOperacionais
                                                ->where('item_orcamento_id', $itemBase->id)
                                                ->first();
                                        }

                                        $produtoNome =
                                            $itemBase->produto->nome
                                            ?? $itemBase->produto_nome
                                            ?? $itemBase->descricao
                                            ?? $itemBase->nome_produto
                                            ?? 'Produto não identificado';

                                        $quantidadeBase =
                                            $itemBase->quantidade
                                            ?? $itemBase->qtd
                                            ?? $itemBase->quantidade_vendida
                                            ?? $itemBase->quantidade_orcada
                                            ?? 0;

                                        $quantidadePrevista = $entregaItem->quantidade_prevista ?? $quantidadeBase;
                                        $quantidadeEntregue = $entregaItem->quantidade_entregue ?? 0;
                                        $saldo = max($quantidadePrevista - $quantidadeEntregue, 0);

                                        $statusItem = strtolower($entregaItem->status ?? 'pendente');
                                    ?>

                                    <tr>
                                        <td class="text-center"><?php echo e($loop->iteration); ?></td>

                                        <td class="fw-semibold">
                                            <?php echo e($produtoNome); ?>

                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($origemItens === 'Venda' ? 'bg-success' : 'bg-secondary'); ?>">
                                                <?php echo e($origemItens); ?>

                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format($quantidadeBase, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format($quantidadePrevista, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format($quantidadeEntregue, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-center">
                                            <?php echo e(number_format($saldo, 2, ',', '.')); ?>

                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?php echo e($statusItemClasses[$statusItem] ?? 'bg-secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $entregaItem->status ?? 'pendente'))); ?>

                                            </span>
                                        </td>

                                        <td>
                                            <?php echo e($entregaItem->observacao ?? '-'); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Nenhum item encontrado na venda ou no orçamento desta entrega.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="col-md-4">

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-calendar-check me-2"></i>Resumo Operacional</strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Data prevista</small>
                        <div class="fw-semibold">
                            <?php echo e($dataPrevista ? $dataPrevista->format('d/m/Y') : '-'); ?>

                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Período</small>
                        <div class="fw-semibold">
                            <?php echo e($periodoEntrega ? ucfirst(str_replace('_', ' ', $periodoEntrega)) : '-'); ?>

                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Observação</small>
                        <div><?php echo e($observacaoEntrega ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-diagram-3 me-2"></i>Fluxo da Entrega</strong>
                </div>

                <div class="card-body">
                    <div class="timeline-entrega">
                        <?php $__currentLoopData = $etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa => $statusValidos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $concluida = in_array($statusAtual, $statusValidos);
                                $classeIcone = $concluida ? 'bg-success text-white' : 'bg-light border text-muted';
                                $icone = $concluida ? 'bi-check' : 'bi-circle';
                            ?>

                            <div class="timeline-item">
                                <div class="timeline-icon <?php echo e($classeIcone); ?>">
                                    <i class="bi <?php echo e($icone); ?>"></i>
                                </div>

                                <div class="<?php echo e($concluida ? 'fw-semibold' : 'text-muted'); ?>">
                                    <?php echo e($etapa); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <strong><i class="bi bi-clock-history me-2"></i>Histórico</strong>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">
                            <?php echo e($entrega->created_at ? $entrega->created_at->format('d/m/Y H:i') : '-'); ?>

                        </small>
                        <div class="fw-semibold">Entrega criada</div>
                    </div>

                    <?php if($entrega->orcamento_id): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                Orçamento #<?php echo e($entrega->orcamento_id); ?>

                            </small>
                            <div>Entrega vinculada ao orçamento.</div>
                        </div>
                    <?php endif; ?>

                    <?php if($entrega->venda_id): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                Venda #<?php echo e($entrega->venda_id); ?>

                            </small>
                            <div>Entrega vinculada à venda.</div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <small class="text-muted">
                            <?php echo e($entrega->updated_at ? $entrega->updated_at->format('d/m/Y H:i') : '-'); ?>

                        </small>
                        <div>Status atual: <?php echo e($statusLabels[$statusAtual] ?? $entrega->status); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i>
                Esta tela é apenas de acompanhamento. As ações operacionais ficam no painel de entregas.
            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas/show.blade.php ENDPATH**/ ?>