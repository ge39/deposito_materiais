

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Cliente: <?php echo e($cliente->nome); ?></h2>
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">&laquo; Voltar</a>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">💰 Limite de Crédito</h5>
                    <p class="display-6">R$ <?php echo e(number_format($cliente->limite_credito,2,',','.')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php
                $saldoPerc = $cliente->limite_credito > 0 ? ($saldo / $cliente->limite_credito) * 100 : 0;
                $saldoClass = $saldoPerc >= 80 ? 'bg-danger text-white animate__animated animate__pulse' : '';
            ?>
            <div class="card shadow-sm h-100 <?php echo e($saldoClass); ?>">
                <div class="card-body">
                    <h5 class="card-title <?php echo e($saldoPerc >= 80 ? 'text-white' : 'text-danger'); ?>">
                        <?php echo e($saldoPerc >= 80 ? '⚠️ Saldo Crítico' : '🔴 Saldo Atual'); ?>

                    </h5>
                    <p class="display-6 <?php echo e($saldoPerc >= 80 ? 'text-white' : ($saldo > 0 ? 'text-danger' : 'text-success')); ?>">
                        R$ <?php echo e(number_format($saldo,2,',','.')); ?>

                    </p>
                    <?php if($saldoPerc >= 80): ?>
                        <small>Atenção: saldo próximo do limite!</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">🟢 Disponível</h5>
                    <p class="display-6">
                        R$ <?php echo e(number_format(max($cliente->limite_credito - $saldo, 0),2,',','.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>

    
    <ul class="nav nav-tabs mb-3" id="clienteTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="dados-tab" data-bs-toggle="tab" href="#dados" role="tab">Dados do Cliente</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="conta-tab" data-bs-toggle="tab" href="#conta" role="tab">Conta Corrente</a>
        </li>
    </ul>

    <div class="tab-content" id="clienteTabsContent">

        
        <div class="tab-pane fade show active" id="dados" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">Informações Básicas</div>
                        <div class="card-body">
                            <p><strong>Nome:</strong> <?php echo e($cliente->nome); ?></p>
                            <p><strong>Tipo:</strong> <span class="badge bg-info">👤 <?php echo e(ucfirst($cliente->tipo)); ?></span></p>
                            <p><strong>CPF/CNPJ:</strong> <?php echo e($cliente->cpf_cnpj); ?></p>
                            <p><strong>Telefone:</strong> <?php echo e($cliente->telefone); ?></p>
                            <p><strong>Email:</strong> <?php echo e($cliente->email); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">Crédito e Observações</div>
                        <div class="card-body">
                            <p><strong>Limite de Crédito:</strong> 💰 R$ <?php echo e(number_format($cliente->limite_credito,2,',','.')); ?></p>
                            <p><strong>Saldo Atual:</strong> 
                                <span class="<?php echo e($saldo > 0 ? 'text-danger fw-bold' : 'text-success fw-bold'); ?>">
                                    <?php echo e($saldoPerc >= 80 ? '⚠️ ' : ''); ?>R$ <?php echo e(number_format($saldo,2,',','.')); ?>

                                </span>
                            </p>
                            <p><strong>Disponível:</strong> 🟢 R$ <?php echo e(number_format(max($cliente->limite_credito - $saldo,0),2,',','.')); ?></p>
                            <p><strong>Observações:</strong> <?php echo e($cliente->observacoes ?? '—'); ?></p>
                            <p><strong>Criado em:</strong> <?php echo e($cliente->created_at->format('d/m/Y H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="tab-pane fade" id="conta" role="tabpanel">
            <div class="row g-3 mb-3">

                
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">Evolução do Saldo</div>
                        <div class="card-body">
                            <canvas id="saldoChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body bg-light">
                            <h5 class="card-title">Extrato da Conta Corrente</h5>
                            <?php if($movimentacoes->count()): ?>
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Origem</th>
                                            <th>Valor</th>
                                            <th>Saldo Após</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="<?php echo e($mov->tipo === 'debito' ? 'table-danger' : 'table-success'); ?>">
                                                <td><?php echo e($mov->created_at->format('d/m/Y H:i')); ?></td>
                                                <td>
                                                    <span class="badge <?php echo e($mov->tipo === 'debito' ? 'bg-danger' : 'bg-success'); ?>">
                                                        <?php echo e($mov->tipo === 'debito' ? '🔴 Débito' : '🟢 Crédito'); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e(ucfirst($mov->origem)); ?></td>
                                                <td>R$ <?php echo e(number_format($mov->valor,2,',','.')); ?></td>
                                                <td>R$ <?php echo e(number_format($mov->saldo_apos,2,',','.')); ?></td>
                                                <td><?php echo e($mov->descricao); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                                <?php echo e($movimentacoes->links()); ?>

                            <?php else: ?>
                                <p class="text-muted">Nenhuma movimentação encontrada.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('saldoChart').getContext('2d');

    const saldoData = {
        labels: [
            <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                '<?php echo e($mov->created_at->format("d/m H:i")); ?>',
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ],
        datasets: [{
            label: 'Saldo',
            data: [
                <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($mov->saldo_apos); ?>,
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ],
            fill: true,
            borderColor: 'rgba(40, 167, 69, 1)',
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            tension: 0.3,
            pointRadius: 4,
            pointBackgroundColor: 'rgba(220,53,69,1)'
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: saldoData,
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { display: true, title: { display: true, text: 'Data' } },
                y: { display: true, title: { display: true, text: 'Saldo (R$)' } }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/show.blade.php ENDPATH**/ ?>