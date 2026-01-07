<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1" aria-labelledby="modalFinalizarVendaLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalFinalizarVendaLabel">Finalizar Venda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        
        <div class="alert alert-secondary fs-4 text-center">
            <span >Total a pagar:</span></br> <strong id="total-venda-modal"> 0,00</strong>
        </div>
        
        <div class="alert alert-light text-center mb-3">
            <div class="fw-semibold">
                Restante a pagar:
                <span id="valor-restante" class="text-danger fw-bold">
                    R$ 0,00
                </span>
            </div>

            <div class="fw-bold fs-5 mt-1">
                Troco:
                <span id="valor-troco" class="text-success">
                    R$ 0,00
                </span>
            </div>
        </div>

        
        <div class="card shadow-sm">
          <div class="card-body">
            <?php
                $formas = [
                    'dinheiro' => ' DD - Dinheiro',
                    'cartao_credito' => 'CC - Cartão de Crédito',
                    'cartao_debito' => 'CD - Cartão de Débito',
                    'pix' => ' Pi - PIX',
                    'carteira' => 'CA - Carteira'
                ];
            ?>

            <?php $__currentLoopData = $formas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row mb-2 align-items-center">
                <div class="col-5">
                    <label class="form-label fw-semibold"><?php echo e($label); ?></label>
                </div>
                <div class="col-7">
                    <input  type="number" step="0.01"  class="form-control pagamento-modal" data-forma="<?php echo e($key); ?>" placeholder="0,00" min="0" autofocus style="max-width: 150px; font-weight:bold">
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
          </div>
        </div>

      </div>
      <div class="modal-footer d-grid gap-2 md-2">
        <button type="button" class="btn btn-success btn-lg" id="btnFinalizar">Finalizar Venda</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_finalizar.blade.php ENDPATH**/ ?>