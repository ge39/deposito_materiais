

<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">
                Atribuir motorista e veículo
            </h1>
            <small class="text-muted">
                Entrega <?php echo e($entrega->codigo_entrega ?? '#' . $entrega->id); ?>

            </small>
        </div>

        <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Corrija os campos abaixo.</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Equipe da entrega</strong>
        </div>

        <div class="card-body">
            <form method="POST" action="<?php echo e(route('entregas.salvar-equipe', $entrega->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="motorista_id" class="form-label">Motorista</label>
                        <select name="motorista_id" id="motorista_id" class="form-select" required>
                            <option value="">Selecione o motorista</option>

                            <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($motorista->id); ?>"
                                    <?php if(old('motorista_id', $entrega->motorista_id) == $motorista->id): echo 'selected'; endif; ?>>
                                    <?php echo e($motorista->nome); ?> — <?php echo e($motorista->telefone ?? 'sem telefone'); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="veiculo_id" class="form-label">Veículo</label>
                        <select name="veiculo_id" id="veiculo_id" class="form-select" required>
                            <option value="">Selecione o veículo</option>

                            <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($veiculo->id); ?>"
                                    <?php if(old('veiculo_id', $entrega->veiculo_id) == $veiculo->id): echo 'selected'; endif; ?>>
                                    <?php echo e($veiculo->placa); ?>

                                    <?php echo e($veiculo->modelo ? ' — ' . $veiculo->modelo : ''); ?>

                                    <?php echo e($veiculo->capacidade_kg ? ' — ' . number_format($veiculo->capacidade_kg, 2, ',', '.') . ' kg' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>" class="btn btn-outline-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Salvar equipe
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/entregas/atribuir-equipe.blade.php ENDPATH**/ ?>