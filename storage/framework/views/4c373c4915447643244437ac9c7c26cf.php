

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-3">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Dashboard de Movimentações</h3>
    </div>

    
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">

            <div class="col-md-2">
                <label>Data início</label>
                <input type="date" name="inicio" class="form-control"
                    value="<?php echo e(request('inicio', $inicio)); ?>">
            </div>

            <div class="col-md-2">
                <label>Data fim</label>
                <input type="date" name="fim" class="form-control"
                    value="<?php echo e(request('fim', $fim)); ?>">
            </div>

            <div class="col-md-2">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="aprovado" <?php if(request('tipo')=='aprovado'): echo 'selected'; endif; ?>>Aprovado</option>
                    <option value="cancelamento" <?php if(request('tipo')=='cancelamento'): echo 'selected'; endif; ?>>Cancelamento</option>
                    <option value="aguardando_estoque" <?php if(request('tipo')=='aguardando_estoque'): echo 'selected'; endif; ?>>Aguardando Estoque</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Orçamento</label>
                <select name="orcamento_id" class="form-control">
                    <option value="">Todos</option>

                    <?php $__currentLoopData = $listaOrcamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($orc->id); ?>"
                            <?php if(request('orcamento_id', $orcamentoId) == $orc->id): echo 'selected'; endif; ?>>
                            #<?php echo e($orc->id); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100">Filtrar</button>
                <a href="<?php echo e(route('dashboard.movimentacoes')); ?>" class="btn btn-secondary w-100">Limpar</a>
            </div>

        </div>
    </form>

    
    <div class="row g-3 mb-3">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total Orçamentos</h6>
                    <h3><?php echo e($totalOrcamentos); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Aprovados</h6>
                    <h3 class="text-success"><?php echo e($orcamentosAprovados); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Cancelados</h6>
                    <h3 class="text-danger"><?php echo e($orcamentosCancelados); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Taxa Cancelamento</h6>
                    <h3 class="text-warning"><?php echo e($taxaCancelamento); ?>%</h3>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Movimentações por Tipo</div>
                <div class="card-body">
                    <canvas id="chartTipo"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Movimentações por Dia</div>
                <div class="card-body">
                    <canvas id="chartDia"></canvas>
                </div>
            </div>
        </div>

    </div>

    
    <div class="card mb-4">
        <div class="card-header">Top Usuários</div>
        <div class="card-body table-responsive">

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__currentLoopData = $topUsuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($item['user']->name ?? 'N/A'); ?></td>
                            <td><?php echo e($item['total']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

            </table>

        </div>
    </div>

    
    <div class="card">
        <div class="card-header">Orçamentos e Itens</div>

        <div class="card-body table-responsive">

            <table class="table table-sm align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Antes</th>
                        <th>Depois</th>
                        <th>Usuário</th>
                        <th>Data</th>
                    </tr>
                </thead>

                <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $orcamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        
                        <tr class="table-primary">
                            <td colspan="7">
                                <strong>Orçamento #<?php echo e($orc->id); ?></strong>

                                <span class="ms-2 text-muted">
                                    (<?php echo e($orc->movimentacoes->count()); ?> itens)
                                </span>

                                
                                <?php
                                    $tipos = $orc->movimentacoes->pluck('tipo');
                                ?>

                                <?php if($tipos->contains('cancelamento')): ?>
                                    <span class="badge bg-danger ms-2">Cancelado</span>
                                <?php elseif($tipos->contains('aprovado')): ?>
                                    <span class="badge bg-success ms-2">Aprovado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark ms-2">Pendente</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        
                        <?php $__currentLoopData = $orc->movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <?php
                                $produto = $mov->item->produto ?? null;
                            ?>

                            <tr>
                                <td><?php echo e($mov->id); ?></td>

                                <td>
                                    <?php echo e($produto->nome ?? $mov->descricao ?? '-'); ?>

                                </td>

                                <td>
                                    <?php if($mov->tipo == 'aprovado'): ?>
                                        <span class="badge bg-success">Aprovado</span>
                                    <?php elseif($mov->tipo == 'cancelamento'): ?>
                                        <span class="badge bg-danger">Cancelamento</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?php echo e($mov->tipo); ?></span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo e($mov->quantidade_antes); ?></td>
                                <td><?php echo e($mov->quantidade_depois); ?></td>

                                <td><?php echo e($mov->user->name ?? '-'); ?></td>

                                <td><?php echo e($mov->created_at->format('d/m H:i')); ?></td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                Nenhum orçamento encontrado
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const porTipo = <?php echo json_encode($porTipo, 15, 512) ?>;
    const porDia = <?php echo json_encode($porDia, 15, 512) ?>;

    new Chart(document.getElementById('chartTipo'), {
        type: 'pie',
        data: {
            labels: Object.keys(porTipo),
            datasets: [{
                data: Object.values(porTipo)
            }]
        }
    });

    new Chart(document.getElementById('chartDia'), {
        type: 'line',
        data: {
            labels: Object.keys(porDia),
            datasets: [{
                label: 'Movimentações',
                data: Object.values(porDia),
                fill: true
            }]
        }
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/dashboard/movimentacoes.blade.php ENDPATH**/ ?>