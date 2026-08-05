

<?php $__env->startSection('content'); ?>

<div class="container my-5">

    
    <?php if(isset($auditoria)): ?>
        <?php if(abs((float)$auditoria->diferenca) <= 0.01): ?>
            <div class="alert alert-success fw-bold text-center fs-4 shadow-sm mb-4 border-2 border-success">
                🎉 CAIXA HOMOLOGADO E CONSISTENTE (Divergência: R$ 0,00)
            </div>
        <?php else: ?>
            <div class="alert alert-warning fw-bold text-center fs-4 shadow-sm mb-4 border-2 border-warning text-dark">
                ⚠️ ATENÇÃO: CONFERÊNCIA FISCAL CONCLUÍDA COM AJUSTES 
                (Diferença residual: R$ <?php echo e(number_format($auditoria->diferenca, 2, ',', '.')); ?>)
            </div>
        <?php endif; ?>
    <?php endif; ?>

    
    <div class="text-center mb-4">
        <h3 class="fw-bold text-success">Conclusão da Auditoria do Caixa #<?php echo e($caixa->id); ?></h3>
        <p class="text-muted fs-5">Os lançamentos e conciliações contábeis do turno foram encerrados e homologados pela gerência.</p>
    </div>

    
    <div class="card shadow-sm border-success">
        <div class="card-header bg-success text-light fw-bold fs-5">
            Resumo e Balanço de Encerramento do Turno
        </div>

        <div class="card-body fs-5">

            
            <?php if(session('auditoria_sucesso')): ?>
                <div class="alert alert-success d-flex align-items-center py-2 px-3 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <?php echo e(session('auditoria_sucesso')); ?>

                    </div>
                </div>
            <?php endif; ?>

            
            <div class="row mb-4 border-bottom border-light-subtle pb-3">
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">ID do Caixa</span>
                    <strong>#<?php echo e($caixa->id); ?></strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Operador do Turno</span>
                    <strong><?php echo e($caixa->usuario->name ?? 'Não identificado'); ?></strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Terminal de Vendas</span>
                    <strong>Caixa #<?php echo e($caixa->terminal_id ?? $caixa->id); ?></strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Data/Hora de Encerramento</span>
                    <strong><?php echo e($caixa->data_fechamento ? \Carbon\Carbon::parse($caixa->data_fechamento)->format('d/m/Y H:i') : '-'); ?></strong>
                </div>
            </div>

            
            <div class="row mb-4">
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Status Contábil</span>
                    <span class="badge <?php echo e($caixa->status === 'fechado' ? 'bg-success' : 'bg-danger'); ?> fs-6 px-2 py-1">
                        <?php echo e($caixa->status === 'fechado' ? 'FECHADO / CONSISTENTE' : ucfirst($caixa->status)); ?>

                    </span>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Fundo de Troco (Inicial)</span>
                    <strong class="text-secondary">R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?></strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Faturamento Esperado (Sistema)</span>
                    <strong class="text-primary">R$ <?php echo e(number_format($auditoria->total_sistema ?? $caixa->valor_fechamento, 2, ',', '.')); ?></strong>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="text-muted small d-block">Valor de Fechamento (Físico)</span>
                    <strong class="text-success">R$ <?php echo e(number_format($caixa->valor_fechamento, 2, ',', '.')); ?></strong>
                </div>
            </div>

            
            <?php if(isset($movimentacoes) && $movimentacoes->count() > 0): ?>
                <div class="mt-4 pt-3 border-top">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text me-1"></i> Lançamentos Corretivos Efetuados pela Auditoria</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover border align-middle fs-6">
                            <thead class="table-light">
                                <tr>
                                    <th>Forma Corrigida</th>
                                    <th class="text-end">Valor Esperado</th>
                                    <th class="text-end">Valor Homologado</th>
                                    <th class="text-end">Diferença Ajustada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $diffForma = (float)$mov->valor_auditado - (float)$mov->valor;
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-secondary"><?php echo e(ucfirst(str_replace('_', ' ', $mov->forma_pagamento))); ?></td>
                                        <td class="text-end text-muted">R$ <?php echo e(number_format($mov->valor, 2, ',', '.')); ?></td>
                                        <td class="text-end text-dark fw-bold">R$ <?php echo e(number_format($mov->valor_auditado, 2, ',', '.')); ?></td>
                                        <td class="text-end fw-bold <?php echo e($diffForma < 0 ? 'text-danger' : ($diffForma > 0 ? 'text-success' : 'text-muted')); ?>">
                                            <?php echo e($diffForma > 0 ? '+' : ''); ?>R$ <?php echo e(number_format($diffForma, 2, ',', '.')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="mt-4 pt-3 border-top text-end">
                <a href="<?php echo e(route('fechamento.lista')); ?>" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left-short"></i> Voltar ao Painel Geral
                </a>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary me-2">
                    Ir para o Dashboard
                </a>
                <a href="<?php echo e(route('caixa.abrir')); ?>" class="btn btn-success px-4 fw-bold shadow-sm">
                    <i class="bi bi-plus-lg"></i> Abrir Novo Caixa
                </a>
            </div>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\fechamento_caixa\confirmacao_auditoria.blade.php ENDPATH**/ ?>