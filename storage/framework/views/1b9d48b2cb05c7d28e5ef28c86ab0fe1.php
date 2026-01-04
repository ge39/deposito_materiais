

<?php $__env->startSection('title', 'Lançamento Manual de Valores - Caixa #'.$caixa->id); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Fechamento de Caixa  - Caixa #<?php echo e($caixa->id); ?></h2>
   <div class="card mb-4 shadow-sm">
    <div class="card-header fs-5 bg-primary text-white fw-bold">
        Informações do Caixa
    </div>
    <div class="card-body" style="font-size:14px;">
        <div class="row mb-3">
            <div class="col-md-2 fw-semibold text-muted">Operador</div>
            <div class="col-md-3"><?php echo e($caixa->usuario->name); ?></div>

            <div class="col-md-2 fw-semibold text-muted">Terminal</div>
            <div class="col-md-3"><?php echo e($caixa->terminal_id ?? 'N/A'); ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2 fw-semibold text-muted">Data Abertura</div>
            <div class="col-md-3"><?php echo e($caixa->data_abertura->format('d/m/Y H:i')); ?></div>

            <div class="col-md-2 fw-semibold text-muted">Status</div>
            <div class="col-md-3">
                <span class="badge <?php echo e($caixa->status === 'aberto' ? 'bg-success' : ($caixa->status === 'fechado' ? 'bg-secondary' : 'bg-warning')); ?>">
                    <?php echo e(ucfirst($caixa->status)); ?>

                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 fw-semibold text-muted">Fundo de Troco</div>
            <div class="col-md-3">R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?></div>
        </div>
    </div>
</div>


    
    <form action="<?php echo e(route('fechamento.fechar', $caixa->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <!-- =============================== -->
    <!-- PAGAMENTOS + BANDEIRAS -->
    <!-- =============================== -->
    <div class="row mt-4">
        <!-- Valores por Forma de Pagamento -->
        <div class="col-md-6">
            <div class="card-header bg-primary fs-5 text-white fw-bold p-2">Valores por Forma de Pagamento</div>
            <div class="card mb-3">
                <div class="card-body">

                    <?php $__currentLoopData = [
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'Pix',
                        'carteira' => 'Carteira',
                        'cartao_debito' => 'Cartão Débito',
                        'cartao_credito' => 'Cartão Crédito'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-semibold" style="font-size:14px;">
                                <?php echo e($label); ?>

                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="<?php echo e($name); ?>"
                                    class="form-control form-control-sm"
                                    style="font-size:14px;"
                                    value="<?php echo e(old($name, 0)); ?>"
                                    required>
                            </div>
                        </div>
                        
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>
        </div>

        <!-- Bandeiras de Cartão -->
        <div class="col-md-6">
            <div class="card-header fs-5 bg-primary text-white fw-bold p-2">Bandeiras de Cartão</div>
            <div class="card mb-3">
                <div class="card-body">

                    <?php $__currentLoopData = [
                        'bandeira_visa' => 'Visa',
                        'bandeira_mastercard' => 'Mastercard',
                        'bandeira_elo' => 'Elo',
                        'bandeira_amex' => 'Amex',
                        'bandeira_hipercard' => 'Hipercard'                        
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-semibold" style="font-size:14px;">
                                <?php echo e($label); ?>

                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="<?php echo e($name); ?>"
                                    class="form-control form-control-sm"
                                    style="font-size:14px;"
                                    value="<?php echo e(old($name, 0)); ?>">
                            </div>
                        </div>
                       
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                </div>
            </div>
        </div>

    </div>

    <!-- =============================== -->
    <!-- ENTRADAS + SAÍDAS DE CAIXA -->
    <!-- =============================== -->
    <div class="row mt-4">

        <!-- Entradas -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fs-5 bg-primary text-white fw-bold">
                    Entradas de Caixa
                </div>
                <div class="card-body">

                    <?php $__currentLoopData = [
                        'entrada_suprimento' => 'Suprimento',
                        'entrada_ajuste' => 'Ajuste Positivo',
                        'entrada_devolucao' => 'Devolução em Dinheiro',
                        'entrada_outros' => 'Outras Entradas'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-bold" style="font-size:14px;">
                                <?php echo e($label); ?>

                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="<?php echo e($name); ?>"
                                    class="form-control form-control-sm text-end"
                                    value="<?php echo e(old($name, 0)); ?>">
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>
        </div>

        <!-- Saídas -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fs-5 bg-primary text-white fw-bold">
                    Saídas de Caixa
                </div>
                <div class="card-body">

                    <?php $__currentLoopData = [
                        'saida_sangria' => 'Sangria',
                        'saida_despesa' => 'Despesas',
                        'saida_ajuste' => 'Ajuste Negativo',
                        'saida_outros' => 'Outras Saídas'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-bold" style="font-size:14px;">
                                <?php echo e($label); ?>

                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="<?php echo e($name); ?>"
                                    class="form-control form-control-sm text-end"
                                    value="<?php echo e(old($name, 0)); ?>">
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>
        </div>

    </div>

    <!-- =============================== -->
    <!-- BOTÕES -->
    <!-- =============================== -->
    <div class="row mt-4">
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-success">
                Fechar Caixa
            </button>
            <a href="<?php echo e(route('fechamento.lista')); ?>" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </div>

</form>

        

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/lancar_valores.blade.php ENDPATH**/ ?>