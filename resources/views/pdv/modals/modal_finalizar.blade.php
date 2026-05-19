<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
    <form id="formFinalizarVenda" method="POST" action="">
      @csrf
      <div class="modal-content">
        
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">Finalizar Venda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          {{-- Total --}}
          <div class="alert alert-secondary fs-5 text-center">
              Total a pagar:<br>
              <strong id="total-venda-modal">0,00</strong>
          </div>

          {{-- Dados cliente carteira --}}
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

          {{-- Resumo --}}
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

          {{-- Pagamentos --}}
          <div class="card shadow-sm">
            <div class="card-body">
              @php
                  $formas = [
                      'dinheiro' => 'DD - Dinheiro',
                      'cartao_credito' => 'CC - Crédito',
                      'cartao_debito' => 'CD - Débito',
                      'pix' => 'PI - PIX',
                      'carteira' => 'CA - Carteira'
                  ];
              @endphp

              @foreach($formas as $key => $label)
              <div class="row mb-2 align-items-center">
                  <div class="col-5">
                      <label class="form-label fw-semibold">{{ $label }}</label>
                  </div>
                  <div class="col-7">
                      <input  
                          type="number" 
                          step="0.01"  
                          class="form-control pagamento-modal" 
                          name="pagamentos[{{ $key }}]"
                          data-forma="{{ $key }}"
                          placeholder="0,00" 
                          min="0"
                          style="max-width:150px;font-weight:bold"
                          @if($loop->first) autofocus @endif
                      >
                  </div>
              </div>
              @endforeach
            </div>
          </div>

        </div>

        <div class="modal-footer d-grid gap-1">
          <button type="button" class="btn btn-success btn-custom btn-SM" id="btnFinalizar">
              Finalizar Venda
          </button>
          <button type="button" class="btn btn-SM btn-outline-secondary" data-bs-dismiss="modal">
              Cancelar
          </button>
        </div>

      </div>
    </form>
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
            });
        });

        // ===============================
        // ATALHOS TECLADO (DD, CC, CD, PI, CA)
        // ===============================
        document.addEventListener('keydown', function (e) {
            if (!modalFinalizar || !modalFinalizar.classList.contains('show')) return;
            if (e.ctrlKey || e.altKey || e.metaKey) return;

            const tecla = e.key.toLowerCase();
            
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

            e.preventDefault();

            let valorRestante = parseMoney(restanteEl.textContent);
            let valorAtualDoCampo = parseFloat(input.value) || 0;
            let valorParaPreencher = valorRestante + valorAtualDoCampo;

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
            calcularPagamentos();
            
            input.focus();
            input.select();

            window.__pdvBufferForma = '';
        });

        // ========================================================
        // ENTER NOS INPUTS DOS ATALHOS: SE RESTANTE FOR 0, FOCO NO BOTÃO
        // ========================================================
        inputsPagamento.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Impede o envio precoce ou comportamento indesejado do navegador

                    let restante = parseMoney(restanteEl.textContent);
                    let valorAtual = parseFloat(input.value) || 0;

                    // Se o operador der Enter com o campo zerado, preenche com o saldo restante
                    if (valorAtual <= 0 && restante > 0) {
                        input.value = restante.toFixed(2);
                        calcularPagamentos();
                    }

                    // Pega o restante final após a digitação/preenchimento
                    let restanteFinal = parseMoney(restanteEl.textContent);
                    
                    // Condição estrita solicitada: Se Restante for igual a R$ 0,00 -> Foco no botão
                    if (restanteFinal <= 0) {
                        setTimeout(() => {
                            if (btnFinalizar) {
                                btnFinalizar.focus();
                            }
                        }, 10);
                    } else {
                        // Se ainda restar valor, mantém o operador no campo atual para edição
                        input.focus();
                        input.select();
                    }
                }
            });
        });

        // Se o operador apertar Enter com o botão já focado, finaliza a venda
        btnFinalizar?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnFinalizar.click();
            }
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
        }

        // ========================================================
        // AÇÃO DO BOTÃO FINALIZAR (CLIQUE / SUBMIT)
        // ========================================================
        btnFinalizar?.addEventListener('click', function() {
            let totalPagamento = 0;

            inputsPagamento.forEach(input => {
                let val = parseFloat(input.value) || 0;
                input.value = val.toFixed(2);
                totalPagamento += val;
            });

            if (totalPagamento <= 0) {
                alert('Informe ao menos uma forma de pagamento.');
                return;
            }

            window.carrinho = [];
            
            if (typeof atualizarTabelaCarrinho === 'function') {
                atualizarTabelaCarrinho();
            }

            setTimeout(() => {
                const inputBarras = document.getElementById('codigo_barras');
                if (inputBarras) {
                    inputBarras.value = ''; 
                    inputBarras.focus();    
                }
            }, 100);

            formVenda?.submit();
        });

        function atualizarResumoClienteFinalizar() {
            // Mantida intacta para evitar quebra de chamadas externas de escopo
        }
    });
</script>
