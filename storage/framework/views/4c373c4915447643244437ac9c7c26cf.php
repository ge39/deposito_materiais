

<?php $__env->startSection('content'); ?>
<div class="container">

<h2 class="mb-4">📊 Dashboard de Movimentações</h2>


<form method="GET" class="row mb-4">
    <div class="col-md-3">
        <input type="date" name="inicio" value="<?php echo e($inicio); ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" name="fim" value="<?php echo e($fim); ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <select name="tipo" class="form-control">
            <option value="">Todos</option>
            <option value="reserva" <?php if($tipo=='reserva'): echo 'selected'; endif; ?>>Reserva</option>
            <option value="cancelamento" <?php if($tipo=='cancelamento'): echo 'selected'; endif; ?>>Cancelamento</option>
            <option value="edicao" <?php if($tipo=='edicao'): echo 'selected'; endif; ?>>Edição</option>
        </select>
    </div>

    <div class="col-md-3">
        <button class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>


<div class="row mb-4">

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Total</small>
            <h3><?php echo e($total); ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Reservas</small>
            <h3><?php echo e($reservas); ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Cancelamentos</small>
            <h3><?php echo e($cancelamentos); ?></h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small>Taxa Cancelamento</small>
            <h3><?php echo e(number_format($taxaCancelamento, 1)); ?>%</h3>
        </div>
    </div>

</div>


<?php if($taxaCancelamento > 30): ?>
    <div class="alert alert-danger">
        ⚠️ Alta taxa de cancelamento — verifique estoque ou preços
    </div>
<?php endif; ?>


<div class="card mb-4 p-3 shadow-sm">
    <canvas id="grafico"></canvas>
</div>


<div class="card mb-4 p-3 shadow-sm">
    <h5>👤 Top usuários</h5>

    <?php $__currentLoopData = $topUsuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="d-flex justify-content-between">
            <span><?php echo e($u->user->name ?? 'Sistema'); ?></span>
            <strong><?php echo e($u->total); ?></strong>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Antes</th>
                    <th>Depois</th>
                    <th>Usuário</th>
                    <th>Data</th>
                </tr>
            </thead>

            <tbody>
                <?php $__currentLoopData = $ultimas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($mov->id); ?></td>

                        <td>
                            <span class="badge
                                <?php if($mov->tipo=='reserva'): ?> bg-primary
                                <?php elseif($mov->tipo=='cancelamento'): ?> bg-danger
                                <?php else: ?> bg-secondary
                                <?php endif; ?>">
                                <?php echo e($mov->tipo); ?>

                            </span>
                        </td>

                        <td><?php echo e($mov->quantidade_antes); ?></td>
                        <td><?php echo e($mov->quantidade_depois); ?></td>
                        <td><?php echo e($mov->user->name ?? '-'); ?></td>
                        <td><?php echo e($mov->created_at->format('d/m H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>

        </table>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('grafico'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($porDia->pluck('data')); ?>,
        datasets: [{
            label: 'Movimentações',
            data: <?php echo json_encode($porDia->pluck('total')); ?>,
            tension: 0.4
        }]
    }
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/dashboard/movimentacoes.blade.php ENDPATH**/ ?>