

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php
        $entrega = $romaneio->entrega ?? null;
        $orcamento = $entrega->orcamento ?? null;
        $venda = $entrega->venda ?? null;
        $itens = $romaneio->itens ?? collect();

        $codigo = $romaneio->codigo_romaneio ?? $romaneio->codigo ?? '#' . $romaneio->id;
        $status = $romaneio->status ?? 'Gerado';

        $badgeStatus = match($status) {
            'Gerado' => 'primary',
            'Pendente' => 'warning' ,
            'Em separação', 'Separando' => 'info',
            'Carregando' => 'info',
            'Carregado' => 'success',
            'Finalizado', 'Concluido', 'Concluído' => 'success',
            'Cancelado' => 'danger',
            default => 'secondary',
        };

        $percentual = (float) ($romaneio->percentual_carregado ?? 0);
        $totalItens = $itens->count();
        $itensCarregados = $itens->whereIn('status', ['Carregado', 'Conferido'])->count();
    ?>

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clipboard-check me-2"></i>Romaneio <?php echo e($codigo); ?>

            </h3>
            <small class="text-muted">
                Acompanhamento operacional do romaneio, separação, carregamento e vínculo com entrega.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('romaneios.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>

            <?php if(Route::has('romaneios.print')): ?>
                <a href="<?php echo e(route('romaneios.print', $romaneio->id)); ?>" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </a>
            <?php endif; ?>

            <?php if(Route::has('expedicao.show')): ?>
                <a href="<?php echo e(route('expedicao.show', $romaneio->id)); ?>" class="btn btn-success">
                    <i class="bi bi-box-arrow-right me-1"></i> Expedição
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Status</small>
                        <h5 class="fw-bold mb-0">
                            <span class="badge bg-<?php echo e($badgeStatus); ?>"><?php echo e($status); ?></span>
                        </h5>
                    </div>
                    <i class="bi bi-flag fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Itens</small>
                        <h3 class="fw-bold mb-0"><?php echo e($totalItens); ?></h3>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Carregados</small>
                        <h3 class="fw-bold mb-0"><?php echo e($itensCarregados); ?></h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">Progresso</small>
                        <h3 class="fw-bold mb-0"><?php echo e(number_format($percentual, 0, ',', '.')); ?>%</h3>
                    </div>
                    <i class="bi bi-graph-up-arrow fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Dados Operacionais</strong>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted">Romaneio</small>
                            <div class="fw-bold"><?php echo e($codigo); ?></div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Entrega</small>
                            <div class="fw-bold">
                                <?php echo e($entrega ? '#' . $entrega->id : 'Não vinculada'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Orçamento</small>
                            <div class="fw-bold">
                                <?php echo e($orcamento ? '#' . $orcamento->id : ($entrega->orcamento_id ?? '—')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Venda</small>
                            <div class="fw-bold">
                                <?php echo e($venda ? '#' . $venda->id : ($entrega->venda_id ?? '—')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Emissão</small>
                            <div class="fw-bold">
                                <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y H:i')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Última atualização</small>
                            <div class="fw-bold">
                                <?php echo e(optional($romaneio->updated_at)->format('d/m/Y H:i')); ?>

                            </div>
                        </div>

                        <div class="col-12">
                            <small class="text-muted">Observação</small>
                            <div class="fw-semibold">
                                <?php echo e($romaneio->observacao ?? 'Nenhuma observação registrada.'); ?>

                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted fw-semibold">Percentual carregado</small>
                            <small class="fw-bold"><?php echo e(number_format($percentual, 2, ',', '.')); ?>%</small>
                        </div>
                        <div class="progress" style="height: 18px;">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: <?php echo e(min($percentual, 100)); ?>%;">
                                <?php echo e(number_format($percentual, 0, ',', '.')); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-person-vcard me-2"></i>
                    <strong>Cliente e Entrega</strong>
                </div>

                <div class="card-body">
                    <small class="text-muted">Cliente</small>
                    <div class="fw-bold mb-3">
                        <?php echo e($entrega->cliente_nome ?? $entrega->nome_cliente ?? $orcamento->cliente->nome ?? 'Cliente não informado'); ?>

                    </div>

                    <small class="text-muted">Endereço de entrega</small>
                    <div class="fw-semibold mb-3">
                        <?php echo e($entrega->endereco_entrega ?? $entrega->endereco_entrega_concatenado ?? 'Endereço não informado'); ?>

                    </div>

                    <small class="text-muted">Previsão</small>
                    <div class="fw-semibold mb-3">
                        <?php echo e(optional($entrega->data_prevista_entrega ?? null)->format('d/m/Y') ?? ($entrega->data_prevista_entrega ?? 'Não informada')); ?>

                        <?php if(!empty($entrega->periodo_entrega)): ?>
                            — <?php echo e($entrega->periodo_entrega); ?>

                        <?php endif; ?>
                    </div>

                    <small class="text-muted">Status da entrega</small>
                    <div>
                        <span class="badge bg-secondary">
                            <?php echo e($entrega->status ?? 'Não informado'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-check me-2"></i>
                <strong>Itens do Romaneio</strong>
            </div>
            <span class="badge bg-light text-dark">
                <?php echo e($totalItens); ?> item(ns)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Lote</th>
                            <th class="text-end">Qtd. Prevista</th>
                            <th class="text-end">Qtd. Carregada</th>
                            <th>Status</th>
                            <th>Observação</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $entregaItem = $item->entregaItem ?? null;
                                $itemVenda = $entregaItem->vendaItem ?? null;
                                $itemOrcamento = $entregaItem->itemOrcamento ?? null;

                                $produto = $itemVenda->produto
                                    ?? $itemOrcamento->produto
                                    ?? null;

                                $statusItem = $item->status ?? 'Pendente';

                                $badgeItem = match($statusItem) {
                                    'Pendente' => 'warning',
                                    'Carregando' => 'info',
                                    'Carregado', 'Conferido' => 'success',
                                    'Parcial' => 'primary',
                                    'Devolvido' => 'secondary',
                                    'Cancelado' => 'danger',
                                    default => 'secondary',
                                };

                                $loteNome = '—';

                                if ($itemOrcamento && isset($itemOrcamento->lotes) && $itemOrcamento->lotes->count()) {
                                    $loteNome = $itemOrcamento->lotes->pluck('codigo_lote')->filter()->implode(', ');
                                }
                            ?>

                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <?php echo e($produto->nome ?? $produto->descricao ?? 'Produto não identificado'); ?>

                                    </div>
                                    <small class="text-muted">
                                        Código: <?php echo e($produto->codigo ?? $produto->id ?? '—'); ?>

                                    </small>
                                </td>

                                <td><?php echo e($loteNome ?: '—'); ?></td>

                                <td class="text-end">
                                    <?php echo e(number_format((float) ($item->quantidade_prevista ?? 0), 2, ',', '.')); ?>

                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format((float) ($item->quantidade_carregada ?? 0), 2, ',', '.')); ?>

                                </td>

                                <td>
                                    <span class="badge bg-<?php echo e($badgeItem); ?>">
                                        <?php echo e($statusItem); ?>

                                    </span>
                                </td>

                                <td>
                                    <?php echo e($item->observacao ?? '—'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Nenhum item encontrado para este romaneio.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/show.blade.php ENDPATH**/ ?>