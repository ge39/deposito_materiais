

<?php $__env->startSection('content'); ?>
<!-- Modal de Confirmação Visual -->
<div class="modal fade" id="modalConfirmarFechamento" tabindex="-1" aria-labelledby="modalConfirmarFechamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning shadow-lg">
      
      <div class="modal-header bg-warning text-dark d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-2"></i>
        <h5 class="modal-title fw-bold" id="modalConfirmarFechamentoLabel">Atenção: Fechamento de Caixa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      
      <div class="modal-body fs-6">
        <p>
          Você está prestes a fechar o caixa. <strong>Valores incorretos ou duvidosos</strong> lançados podem acarretar o bloqueio do caixa e <strong>passível de Auditoria</strong>.
        </p>
        <p class="text-danger fw-bold mb-0">
          Confirme apenas se os valores estiverem corretos.
        </p>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Não, cancelar</button>
        <button type="button" class="btn btn-warning btn-sm fw-bold" id="confirmarFechamento">
          Sim, fechar caixa
        </button>
      </div>
      
    </div>
  </div>
</div>

<div class="container">

    <h4 class="mb-4 text-primary">
        Fechamento de Caixa #<?php echo e($caixa->id); ?>

    </h4>

    <form method="POST" action="<?php echo e(route('fechamento.fechar', $caixa->id)); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <strong>✅ Dados do Caixa - Fechamento do Caixa #<?php echo e($caixa->id); ?></strong>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>ID (Caixa)</strong><br>
                        <?php echo e($caixa->id); ?>

                    </div>

                    <div class="col-md-4">
                        <strong>Operador</strong><br>
                        <?php echo e($caixa->usuario->name ?? 'Não identificado'); ?>

                    </div>

                    <div class="col-md-4">
                        <strong>Terminal ID</strong><br>
                        <?php echo e($caixa->terminal_id); ?>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <strong>Abertura</strong><br>
                        <?php echo e(\Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y H:i')); ?>

                    </div>

                    <div class="col-md-4">
                        <strong>Status</strong><br>
                        <?php
                            $statusLabel = match($caixa->status) {
                                'aberto' => 'Aberto',
                                'pendente' => 'Pendente',
                                'fechado' => 'Fechado',
                                'fechado_sem_movimento' => 'Fechado sem movimentação',
                                'inconsistente' => 'Inconsistente',
                                default => ucfirst($caixa->status),
                            };
                        ?>

                        <span class="badge bg-success">
                            <?php echo e($statusLabel); ?>

                        </span>
                    </div>

                    <div class="col-md-4">
                        <strong>Fundo de Troco</strong><br>
                        R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?>

                    </div>
                </div>
            </div>
        </div>

        
        <?php if(!$caixa->possuiVendas()): ?>

            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <strong>Fechamento sem Movimentação</strong>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="motivo_fechamento" class="fw-bold">
                            Motivo do fechamento
                        </label>

                        <select
                            name="motivo_fechamento"
                            id="motivo_fechamento"
                            class="form-control"
                            required
                        >
                            <option value="">Selecione o motivo</option>
                            <option value="Caixa aberto sem movimento">
                                Caixa aberto sem movimento
                            </option>
                            <option value="Sistema indisponível">
                                Sistema indisponível
                            </option>
                            <option value="Loja não abriu">
                                Loja não abriu
                            </option>
                            <option value="Erro hardware">
                                Erro hardware
                            </option>
                            <option value="Erro operacional">
                                Erro operacional
                            </option>
                        </select>
                    </div>
                </div>
            </div>

        
        <?php else: ?>

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
                <div class="card-header fs-5 bg-success text-white fw-bold">
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
                <div class="card-header fs-5 bg-danger text-white fw-bold">
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


        <?php endif; ?>

        <div class="text-end">
            <button type="submit" class="btn btn-success btn-sm">
                Confirmar Fechamento
            </button>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary btn-sm">
                Cancelar
            </a>
        </div>

    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/fechamento.blade.php ENDPATH**/ ?>