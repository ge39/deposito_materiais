<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px;">
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

        const formVenda      = document.getElementById('formFinalizarVenda');
        const inputsPagamento = document.querySelectorAll('.pagamento-modal');
        const restanteEl     = document.getElementById('valor-restante');
        const trocoEl        = document.getElementById('valor-troco');
        const totalModalEl   = document.getElementById('total-venda-modal');
        const btnFinalizar   = document.getElementById('btnFinalizar');
        const modalFinalizar = document.getElementById('modalFinalizarVenda');

        let carrinho = window.carrinho || [];

        if (modalFinalizar) {
             if (!window.financeiroCliente) return;

            document.getElementById('saldo-cliente-modal').textContent =
                'R$ ' + Number(window.financeiroCliente.saldo_apos)
                    .toFixed(2)
                    .replace('.', ',');

            aplicarRegraCarteira();
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

        // ===============================
        // CLIENTE
        // ===============================
        if(!window.clienteSelecionado){
            document.getElementById('nome-cliente-modal').textContent = 'VENDA BALCAO';
            document.getElementById('saldo-cliente-modal').textContent = 'R$ 0,00';
            } else {
            document.getElementById('nome-cliente-modal').textContent = clienteSelecionado.nome;
            document.getElementById('saldo-cliente-modal').textContent =
                parseFloat(clienteSelecionado.saldo).toFixed(2).replace('.', ',');
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

                    if (valorAtual <= 0 && restante > 0) {
                        input.value = restante.toFixed(2);
                        restanteEl.textContent = formatMoney(0);
                        trocoEl.textContent = formatMoney(0);
                        return;
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

            if (!window.clienteSelecionado) return;

            let saldo = parseFloat(window.clienteSelecionado.saldo || 0);
            let total = parseMoney(totalModalEl.innerText);

            let inputCarteira = document.querySelector('[data-forma="carteira"]');
            if (!inputCarteira) return;

            if (saldo >= total) {
                inputCarteira.value = total.toFixed(2);
            } else if (saldo > 0) {
                inputCarteira.value = saldo.toFixed(2);
            } else {
                inputCarteira.value = 0;
                inputCarteira.disabled = true;
            }

            calcularPagamentos();
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

            if (!window.cliente) return;

            const saldoEl = document.getElementById('saldo-cliente-finalizar');
            const limiteEl = document.getElementById('limite-cliente-finalizar');

            // Saldo Atual
            if (saldoEl) {
                saldoEl.textContent =
                    `R$ ${Number(window.cliente.saldo_apos || 0).toFixed(2).replace('.', ',')}`;
            }
                // LImite de credito
            if (limiteEl) {
                limiteEl.textContent =
                    `R$ ${Number(window.cliente.limite_credito || 0).toFixed(2).replace('.', ',')}`;
            }
        }

    });
</script>