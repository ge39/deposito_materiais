

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-truck me-2"></i>Central da Entrega
                <span class="text-muted">#<?php echo e($entrega->codigo_entrega ?? $entrega->id); ?></span>
            </h4>
            <small class="text-muted">
                Acompanhamento operacional da entrega, itens, responsável e status logístico.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('entregas.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
        $statusClasses = [
            'pendente'  => 'bg-warning text-dark',
            'separando' => 'bg-primary',
            'carregado' => 'bg-info text-dark',
            'em_rota'   => 'bg-dark',
            'entregue'  => 'bg-success',
            'parcial'   => 'bg-secondary',
            'devolvido' => 'bg-danger',
            'cancelado' => 'bg-danger',
        ];

        $statusLabels = [
            'pendente'  => 'Pendente',
            'separando' => 'Separando',
            'carregado' => 'Carregado',
            'em_rota'   => 'Em rota',
            'entregue'  => 'Entregue',
            'parcial'   => 'Parcial',
            'devolvido' => 'Devolvido',
            'cancelado' => 'Cancelado',
        ];

        $statusAtual = $entrega->status;
    ?>

    
    <div class="row mb-3">

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Status Atual</small>
                    <h5 class="mb-0">
                        <span class="badge <?php echo e($statusClasses[$statusAtual] ?? 'bg-secondary'); ?>">
                            <?php echo e($statusLabels[$statusAtual] ?? ucfirst($statusAtual)); ?>

                        </span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-dark border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Data Prevista</small>
                    <h5 class="mb-0">
                        <?php echo e($entrega->data_prevista ? $entrega->data_prevista->format('d/m/Y') : '-'); ?>

                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-success border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Data Realizada</small>
                    <h5 class="mb-0">
                        <?php echo e($entrega->data_realizada ? $entrega->data_realizada->format('d/m/Y') : '-'); ?>

                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-start border-info border-4 h-100">
                <div class="card-body py-2">
                    <small class="text-muted">Itens</small>
                    <h5 class="mb-0">
                        <?php echo e($entrega->itens->count()); ?>

                    </h5>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        
        <div class="col-md-8">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-info-circle me-2"></i>Dados da Entrega
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-3">
                            <small class="text-muted">Código</small>
                            <div class="fw-bold"><?php echo e($entrega->codigo_entrega ?? '-'); ?></div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Venda</small>
                            <div><?php echo e($entrega->venda_id ?? '-'); ?></div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Orçamento</small>
                            <div><?php echo e($entrega->orcamento_id ?? '-'); ?></div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Tipo</small>
                            <div>
                                <?php if($entrega->tipo_entrega === 'retira_loja'): ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-shop me-1"></i>Retira loja
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-truck me-1"></i>Entrega
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Responsável pelo recebimento</small>
                            <div class="fw-bold"><?php echo e($entrega->responsavel_recebimento ?? '-'); ?></div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <small class="text-muted">Telefone</small>
                            <div><?php echo e($entrega->telefone_recebimento ?? '-'); ?></div>
                        </div>

                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-geo-alt me-2"></i>Endereço de Entrega
                    </strong>
                </div>

                <div class="card-body">
                    <p class="mb-1">
                        <?php echo e($entrega->endereco_entrega ?? 'Endereço não informado.'); ?>

                    </p>

                    <small class="text-muted">
                        Usa endereço do cliente:
                        <strong><?php echo e($entrega->usar_endereco_cliente ? 'Sim' : 'Não'); ?></strong>
                    </small>
                </div>
            </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-box-seam me-2"></i>Itens da Entrega
                    </strong>

                    <span class="badge bg-light text-dark">
                        Total: <?php echo e($entrega->itens->count()); ?>

                    </span>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered table-hover table-sm align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Item</th>
                                <th>Venda Item</th>
                                <th>Previsto</th>
                                <th>Entregue</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th>Observação</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $entrega->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-center"><?php echo e($item->id); ?></td>
                                    <td class="text-center"><?php echo e($item->venda_item_id); ?></td>
                                    <td class="text-center"><?php echo e(number_format($item->quantidade_prevista, 2, ',', '.')); ?></td>
                                    <td class="text-center"><?php echo e(number_format($item->quantidade_entregue, 2, ',', '.')); ?></td>
                                    <td class="text-center"><?php echo e(number_format($item->saldo, 2, ',', '.')); ?></td>

                                    <td class="text-center">
                                        <span class="badge <?php echo e($statusClasses[$item->status] ?? 'bg-secondary'); ?>">
                                            <?php echo e($statusLabels[$item->status] ?? ucfirst($item->status)); ?>

                                        </span>
                                    </td>

                                    <td><?php echo e($item->observacao ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Nenhum item vinculado a esta entrega.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

        
        <div class="col-md-4">

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-lightning-charge me-2"></i>Ações Operacionais
                    </strong>
                </div>

               <div class="card-body d-grid gap-2">

                    <?php if($entrega->status === 'aguardando_separacao'): ?>
                        <form method="POST" action="<?php echo e(route('entregas.separar', $entrega->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-box-seam me-1"></i>Iniciar Separação
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($entrega->status === 'separando'): ?>
                        <form method="POST" action="<?php echo e(route('entregas.carregar', $entrega->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="bi bi-truck-flatbed me-1"></i>Marcar como Carregado
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($entrega->status === 'carregado'): ?>
                        <form method="POST" action="<?php echo e(route('entregas.rota', $entrega->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-dark btn-sm w-100">
                                <i class="bi bi-signpost-split me-1"></i>Saiu para Entrega
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if(in_array($entrega->status, ['em_rota', 'parcial'])): ?>
                        <form method="POST" action="<?php echo e(route('entregas.confirmar', $entrega->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-check2-circle me-1"></i>Confirmar Entrega Total
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-secondary btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEntregaParcial">
                            <i class="bi bi-sliders me-1"></i>Registrar Entrega Parcial
                        </button>
                    <?php endif; ?>

                    <?php if(!in_array($entrega->status, ['entregue', 'cancelado', 'devolvido'])): ?>
                        <button type="button"
                                class="btn btn-danger btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCancelarEntrega">
                            <i class="bi bi-x-octagon me-1"></i>Cancelar Entrega
                        </button>
                    <?php endif; ?>

                </div>

            
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <strong>
                        <i class="bi bi-clock-history me-2"></i>Fluxo da Entrega
                    </strong>
                </div>

                <div class="card-body">

                    <?php
                        $fluxo = [
                            'pendente'  => 'Entrega criada',
                            'separando' => 'Separação iniciada',
                            'carregado' => 'Carga preparada',
                            'em_rota'   => 'Saiu para entrega',
                            'entregue'  => 'Entrega concluída',
                        ];

                        $ordem = array_keys($fluxo);
                        $indiceAtual = array_search($statusAtual, $ordem);
                    ?>

                    <?php $__currentLoopData = $fluxo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $indice = array_search($status, $ordem);
                            $feito = $indiceAtual !== false && $indice <= $indiceAtual;
                        ?>

                        <div class="d-flex align-items-start mb-2">
                            <div class="me-2">
                                <?php if($feito): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-circle text-muted"></i>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="<?php echo e($feito ? 'fw-bold' : 'text-muted'); ?>">
                                    <?php echo e($label); ?>

                                </div>
                                <small class="text-muted"><?php echo e($statusLabels[$status] ?? $status); ?></small>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>

        </div>

    </div>

</div>


<div class="modal fade" id="modalEntregaParcial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?php echo e(route('entregas.confirmar', $entrega->id)); ?>" class="modal-content">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-sliders me-2"></i>Registrar Entrega Parcial
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Item</th>
                            <th>Previsto</th>
                            <th>Já Entregue</th>
                            <th>Quantidade Entregue Agora</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $entrega->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center">
                                    #<?php echo e($item->id); ?>

                                    <input type="hidden"
                                           name="itens[<?php echo e($index); ?>][entrega_item_id]"
                                           value="<?php echo e($item->id); ?>">
                                </td>

                                <td class="text-center">
                                    <?php echo e(number_format($item->quantidade_prevista, 2, ',', '.')); ?>

                                </td>

                                <td class="text-center">
                                    <?php echo e(number_format($item->quantidade_entregue, 2, ',', '.')); ?>

                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="<?php echo e($item->quantidade_prevista); ?>"
                                           name="itens[<?php echo e($index); ?>][quantidade_entregue]"
                                           class="form-control form-control-sm"
                                           value="<?php echo e($item->quantidade_entregue); ?>">
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i>Salvar Parcial
                </button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="modalCancelarEntrega" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo e(route('entregas.cancelar', $entrega->id)); ?>" class="modal-content">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-octagon me-2"></i>Cancelar Entrega
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Motivo do cancelamento</label>
                <textarea name="motivo"
                          class="form-control"
                          rows="4"
                          placeholder="Informe o motivo do cancelamento..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas/show.blade.php ENDPATH**/ ?>