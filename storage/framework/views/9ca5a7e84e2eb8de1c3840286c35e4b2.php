<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1" aria-labelledby="modalFinalizarVendaLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalFinalizarVendaLabel">Finalizar Venda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        
        <div class="alert alert-secondary fs-5 text-center">
            Total a pagar: <strong id="total-venda-modal">R$ 0,00</strong>
        </div>

        
        <div class="card shadow-sm">
          <div class="card-body">
            <?php
                $formas = [
                    'dinheiro' => 'Dinheiro',
                    'cartao_credito' => 'Cartão de Crédito',
                    'cartao_debito' => 'Cartão de Débito',
                    'pix' => 'PIX',
                    'carteira' => 'Carteira'
                ];
            ?>

            <?php $__currentLoopData = $formas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row mb-2 align-items-center">
                <div class="col-5">
                    <label class="form-label fw-semibold"><?php echo e($label); ?></label>
                </div>
                <div class="col-7">
                    <input type="number" step="0.01" class="form-control pagamento-modal" data-forma="<?php echo e($key); ?>" placeholder="0,00">
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

          </div>
        </div>

      </div>
      <div class="modal-footer d-grid gap-2">
        <button type="button" class="btn btn-success btn-lg" id="btnFinalizarVendaModal">F6 - Finalizar Venda</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const labelTotalPDV = document.getElementById('totalGeral');
    const totalModalEl = document.getElementById('total-venda-modal');

    function abrirModalFinalizar() {
        let total = 0;
        if(labelTotalPDV) {
            const texto = labelTotalPDV.textContent.replace(/[^\d,]/g,'');
            total = parseFloat(texto.replace(',', '.')) || 0;
        }

        if(totalModalEl) {
            totalModalEl.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        const modalEl = document.getElementById('modalFinalizarVenda');
        if(modalEl && typeof bootstrap !== 'undefined'){
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            console.warn('⚠️ Modal de finalizar venda não encontrado ou Bootstrap não carregado.');
        }

        console.log('💰 Total exibido no modal:', total);
    }

    // Tecla F6
    document.addEventListener('keydown', function(e){
        if(e.code === 'F6'){
            e.preventDefault();
            abrirModalFinalizar();
            console.log('F6 OK - modal finalizador aberto');
        }
    });

    window.abrirModalFinalizar = abrirModalFinalizar;

});
</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_finalizar.blade.php ENDPATH**/ ?>