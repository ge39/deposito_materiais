<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Finalizar Venda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        
        <div class="alert alert-secondary fs-5 text-center">
            Total a pagar:<br>
            <strong id="total-venda-modal">0,00</strong>
        </div>

        
        <div class="alert alert-light py-1 px-2 d-flex justify-content-between align-items-center mb-2">

            <div>
                <span class="text-muted">Cliente:</span>
                <span id="nome-cliente-modal" class="fw-semibold text-primary">
                    VENDA BALCAO
                </span>
            </div>

            <div>
                <span class="text-muted">Saldo Atual:</span>
                <span id="saldo-cliente-modal" class="fw-bold text-success">
                    R$ 0,00
                </span>
            </div>

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

      <div class="modal-footer d-grid gap-1">
        <button type="button" class="btn btn-success btn-SM" id="btnFinalizar">
            Finalizar Venda
        </button>
        <button type="button" class="btn btn-SM btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
        </button>
      </div>

    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const formVenda       = document.getElementById('formFinalizarVenda');
        const inputsPagamento = document.querySelectorAll('.pagamento-modal');
        const restanteEl      = document.getElementById('valor-restante');
        const trocoEl         = document.getElementById('valor-troco');
        const totalModalEl    = document.getElementById('total-venda-modal');
        const btnFinalizar    = document.getElementById('btnFinalizar');
        const modalFinalizar  = document.getElementById('modalFinalizarVenda');

        let carrinho = window.carrinho || [];

        // Ao abrir o modal, busca o valor total real da tela principal do seu PDV
        if (modalFinalizar) {
            modalFinalizar.addEventListener('shown.bs.modal', function () {
                const totalGeralPDV = document.getElementById('totalGeral');
                if (totalGeralPDV) {
                    totalModalEl.innerText = totalGeralPDV.innerText || totalGeralPDV.textContent;
                }
                aplicarRegraCarteira();
            });
        }

        // ===============================
        // FUNÇÕES AUXILIARES
        // ===============================
        function formatMoney(valor) {
            const numero = Number(valor);
            if (isNaN(numero)) {
                return 'R$ 0,00';
            }
            return 'R$ ' + numero.toFixed(2).replace('.', ',');
        }

        function parseMoney(valor) {
            if (!valor) return 0;
            let limpo = valor.toString()
                .replace('R$', '')
                .replace(/\s/g, '')
                .replace(/\./g, '')
                .replace(',', '.');
            return parseFloat(limpo) || 0;
        }

        // ===============================
        // CLIENTE
        // ===============================
        if(!window.clienteSelecionado){
            document.getElementById('nome-cliente-modal').textContent = 'VENDA BALCAO';
            document.getElementById('saldo-cliente-modal').textContent = 'R$ 0,00';
        } else {
            document.getElementById('nome-cliente-modal').textContent = window.clienteSelecionado.nome;
            document.getElementById('saldo-cliente-modal').textContent =
                formatMoney(window.clienteSelecionado.saldo);
        }

        // ===============================
        // OUVINTE DE INPUTS
        // ===============================
        inputsPagamento.forEach(input => {
            input.addEventListener('input', function() {
                calcularPagamentos();
                
                // Se o usuário digitar manualmente e zerar o restante, foca no botão
                let restante = parseMoney(restanteEl.textContent);
                if (restante <= 0) {
                    btnFinalizar?.focus();
                }
            });
        });

        // ===============================
        // ATALHOS TECLADO (DD, CC, CD, PI, CA)
        // ===============================
        document.addEventListener('keydown', function (e) {
            // Só executa se o modal de finalização estiver aberto na tela
            if (!modalFinalizar || !modalFinalizar.classList.contains('show')) return;
            
            // Ignora se o operador estiver pressionando as teclas junto com Ctrl, Alt ou Cmd
            if (e.ctrlKey || e.altKey || e.metaKey) return;

            const tecla = e.key.toLowerCase();
            
            // Registra os últimos dois caracteres digitados para formar a combinação
            window.__pdvBufferForma = (window.__pdvBufferForma || '') + tecla;
            window.__pdvBufferForma = window.__pdvBufferForma.slice(-2);

            let forma = null;
            switch (window.__pdvBufferForma) {
                case 'dd': forma = 'dinheiro'; break;
                case 'cc': forma = 'cartao_credito'; break;
                case 'cd': forma = 'cartao_debito'; break;
                case 'pi': forma = 'pix'; break;
                case 'ca': forma = 'carteira'; break;
            }

            if (!forma) return;
            
            const input = document.querySelector(`.pagamento-modal[data-forma="${forma}"]`);
            if (!input) return;

            // Previne a digitação das letras dentro do input
            e.preventDefault();

            // Pega o valor atual pendente (restante) na tela
            let valorRestante = parseMoney(restanteEl.textContent);
            let valorAtualDoCampo = parseFloat(input.value) || 0;
            let valorParaPreencher = valorRestante + valorAtualDoCampo;

            // Restrição específica para a Carteira do Cliente
            if (forma === 'carteira') {
                let saldoDisponivel = parseFloat(window.clienteSelecionado?.saldo || 0);
                if (!window.clienteSelecionado || saldoDisponivel <= 0) {
                    window.__pdvBufferForma = '';
                    return;
                }
                if (valorParaPreencher > saldoDisponivel) {
                    valorParaPreencher = saldoDisponivel;
                }
            }

            input.value = valorParaPreencher.toFixed(2);
            
            // Dispara a atualização dos cálculos na tela
            calcularPagamentos();
            window.__pdvBufferForma = '';

            // 🔥 REGRA SOLICITADA: Se o restante zerou, manda o foco para o botão finalizar. Se não, foca no input.
            let novoRestante = parseMoney(restanteEl.textContent);
            if (novoRestante <= 0) {
                btnFinalizar?.focus();
            } else {
                input.focus();
                input.select();
            }
        });

        // ===============================
        // ENTER INTELIGENTE
        // ===============================
        inputsPagamento.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    let restante = parseMoney(restanteEl.textContent);
                    let valorAtual = parseFloat(input.value) || 0;

                    if (valorAtual <= 0 && restante > 0) {
                        input.value = restante.toFixed(2);
                        calcularPagamentos();
                        
                        // Recalcula o restante após o preenchimento automático do Enter
                        let novoRestante = parseMoney(restanteEl.textContent);
                        if (novoRestante <= 0) {
                            btnFinalizar?.focus();
                            return;
                        }
                    }

                    btnFinalizar?.click();
                }
            });
        });

        // ===============================
        // CÁLCULO
        // ===============================
        function calcularPagamentos() {
            let total = parseMoney(totalModalEl.innerText);
            let soma = 0;

            inputsPagamento.forEach(input => {
                soma += parseFloat(input.value) || 0;
            });

            let restante = total - soma;
            let troco = 0;

            if (restante < 0) {
                troco = Math.abs(restante);
                restante = 0;
            }

            restanteEl.innerText = formatMoney(restante);
            trocoEl.innerText = formatMoney(troco);
        }

        // ===============================
        // REGRA CARTEIRA
        // ===============================
        function aplicarRegraCarteira() {
            let total = parseMoney(totalModalEl.innerText);
            let inputCarteira = document.querySelector('[data-forma="carteira"]');
            if (!inputCarteira) return;

            if (!window.clienteSelecionado) {
                inputCarteira.value = '';
                inputCarteira.disabled = true;
                calcularPagamentos();
                return;
            }

            let saldo = parseFloat(window.clienteSelecionado.saldo || 0);

            if (saldo >= total) {
                inputCarteira.value = total.toFixed(2);
            } else if (saldo > 0) {
                inputCarteira.value = saldo.toFixed(2);
            } else {
                inputCarteira.value = '';
                inputCarteira.disabled = true;
            }

            calcularPagamentos();

            // Ao abrir o modal, se a carteira já cobrir tudo e o restante for zero, move o foco para o botão salvar
            let restante = parseMoney(restanteEl.textContent);
            if (restante <= 0) {
                btnFinalizar?.focus();
            }
        }

        // ===============================
        // FINALIZAR
        // ===============================
        btnFinalizar?.addEventListener('click', function() {
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

            formVenda?.submit();
        });

        function atualizarResumoClienteFinalizar() {
            // Mantida para compatibilidade externa
        }
    });
</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_finalizar.blade.php ENDPATH**/ ?>