<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Backup Automático</h3>
            <p class="text-muted mb-0">
                Configure até cinco execuções diárias no servidor.
            </p>
        </div>
        <a href="<?php echo e(route('backups.index')); ?>"
           class="btn btn-outline-secondary">
            Voltar ao Backup Manual
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <strong>Programação diária</strong>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="<?php echo e(route('backups.automatico.salvar')); ?>">
                <?php echo csrf_field(); ?>

                <input type="hidden" name="ativo" value="0">

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           id="backupAtivo"
                           name="ativo"
                           value="1"
                           <?php if(old('ativo', $configuracao['ativo'])): echo 'checked'; endif; ?>>

                    <label class="form-check-label" for="backupAtivo">
                        Habilitar backup automático
                    </label>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="quantidade" class="form-label">
                            Quantidade de backups por dia
                        </label>

                        <select class="form-select"
                                name="quantidade"
                                id="quantidade">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo e($i); ?>"
                                    <?php if((int) old('quantidade', $configuracao['quantidade']) === $i): echo 'selected'; endif; ?>>
                                    <?php echo e($i); ?> <?php echo e($i === 1 ? 'backup' : 'backups'); ?>

                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <h6 class="mb-3">Horários das execuções</h6>

                <div class="row g-3">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <div class="col-md-4 horario-box"
                             data-indice="<?php echo e($i); ?>">
                            <label class="form-label">
                                Backup <?php echo e($i + 1); ?>

                            </label>

                            <input type="time"
                                   class="form-control horario-input"
                                   name="horarios[]"
                                   value="<?php echo e(old('horarios.'.$i, $horarios[$i] ?? '')); ?>">
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="alert alert-light border mt-4">
                    Cada backup será executado no horário informado.
                    Não é permitido repetir horários.
                </div>

                <button type="submit" class="btn btn-primary">
                    Salvar agendamento
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Horários programados</strong>
        </div>

        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $horarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $horario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <span class="badge bg-primary me-2 mb-2 p-2">
                    <?php echo e($horario); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <span class="text-muted">Nenhum horário configurado.</span>
            <?php endif; ?>

            <div class="text-muted small mt-3">
                O servidor deve executar o Laravel Scheduler.
                Esta página configura os horários, não inicia o agendador.
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantidade = document.getElementById('quantidade');
    const caixas = document.querySelectorAll('.horario-box');

    function atualizar() {
        const limite = Number(quantidade.value);

        caixas.forEach(function (caixa, indice) {
            const ativo = indice < limite;
            const input = caixa.querySelector('input');

            caixa.classList.toggle('d-none', !ativo);
            input.disabled = !ativo;
            input.required = ativo;
        });
    }

    quantidade.addEventListener('change', atualizar);
    atualizar();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\backups-admin\automatico.blade.php ENDPATH**/ ?>