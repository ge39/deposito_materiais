<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Finalizar Venda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        
        <div class="alert alert-secondary fs-4 text-center">
            Total a pagar:<br>
            <strong id="total-venda-modal">0,00</strong>
        </div>

        
        <div class="alert alert-light text-center mb-3">
            <div class="fw-semibold">
                Restante:
                <span id="valor-restante" class="text-danger fw-bold">R$ 0,00</span>
            </div>

            <div class="fw-bold fs-5 mt-1">
                Troco:
                <span id="valor-troco" class="text-success">R$ 0,00</span>
            </div>
        </div>

        
        <div class="card shadow-sm">
          <div class="card-body">

            <?php
                $formas = [
                    'dinheiro' => 'DD - Dinheiro',
                    'cartao_credito' => 'CC - Crédito',
                    'cartao_debito' => 'CD - Débito',
                    'pix' => 'PI - PIX',
                    'carteira' => 'CA - Carteira'
                ];
            ?>

            <?php $__currentLoopData = $formas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row mb-2 align-items-center">
                <div class="col-5">
                    <label class="form-label fw-semibold"><?php echo e($label); ?></label>
                </div>
                <div class="col-7">
                    <input  
                        type="number" 
                        step="0.01"  
                        class="form-control pagamento-modal" 
                        data-forma="<?php echo e($key); ?>"
                        placeholder="0,00" 
                        min="0"
                        style="max-width:150px;font-weight:bold"
                        <?php if($loop->first): ?> autofocus <?php endif; ?>
                    >
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

          </div>
        </div>

      </div>

      <div class="modal-footer d-grid gap-2">
        <button type="button" class="btn btn-success btn-lg" id="btnFinalizar">
            Finalizar Venda
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
        </button>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const formVenda      = document.getElementById('formFinalizarVenda');
    const inputsPagamento = document.querySelectorAll('.pagamento-modal');
    const restanteEl     = document.getElementById('valor-restante');
    const trocoEl        = document.getElementById('valor-troco');
    const totalModalEl   = document.getElementById('total-venda-modal');
    const btnFinalizar   = document.getElementById('btnFinalizar');

    let carrinho = window.carrinho || [];

    // ===============================
    // FUNÇÃO AUXILIAR
    // ===============================
    function parseMoney(texto) {
        return parseFloat(
            texto.replace('R$', '').replace(/\./g, '').replace(',', '.')
        ) || 0;
    }

    function formatMoney(valor) {
        return 'R$ ' + valor.toFixed(2).replace('.', ',');
    }

    // ===============================
    // ENTER INTELIGENTE
    // ===============================
    inputsPagamento.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                let restante = parseMoney(restanteEl.textContent);
                let valorAtual = parseFloat(input.value) || 0;

                // Se vazio → preenche com restante
                if (valorAtual <= 0 && restante > 0) {
                    input.value = restante.toFixed(2);
                    restanteEl.textContent = formatMoney(0);
                    trocoEl.textContent = formatMoney(0);
                    return;
                }

                // Se já tem valor → FINALIZA
                btnFinalizar.click();
            }
        });
    });

    // ===============================
    // FINALIZAR VENDA
    // ===============================
    btnFinalizar.addEventListener('click', function() {

        let totalPagamento = 0;
        let pagamentoData = {};

        inputsPagamento.forEach(input => {
            let val = parseFloat(input.value) || 0;
            input.value = val.toFixed(2);
            totalPagamento += val;
            pagamentoData[input.dataset.forma] = val.toFixed(2);
        });

        if (totalPagamento <= 0) {
            alert('Informe a forma de pagamento');
            return;
        }

        // console.log('=====================');
        // console.log('Itens do Carrinho:', carrinho);
        // console.log('Pagamento:', pagamentoData);
        // console.log('Total:', totalModalEl.textContent);
        // console.log('Restante:', restanteEl.textContent);
        // console.log('Troco:', trocoEl.textContent);
        // console.log('=====================');

        formVenda?.submit();
    });

});
</script><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_finalizar.blade.php ENDPATH**/ ?>