<!-- Modal Finalizar Venda -->
<div class="modal fade" id="modalFinalizarVenda" tabindex="-1" aria-labelledby="modalFinalizarVendaLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalFinalizarVendaLabel">Finalizar Venda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        {{-- Total --}}
        <div class="alert alert-secondary fs-4 text-center">
            <span>Total a pagar:</span></br> 
            <strong id="total-venda-modal">0,00</strong>
        </div>

        {{-- Resumo de valores --}}
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

        {{-- Pagamentos --}}
        <div class="card shadow-sm">
          <div class="card-body">
            @php
                $formas = [
                    'dinheiro' => ' DD - Dinheiro',
                    'cartao_credito' => 'CC - Cartão de Crédito',
                    'cartao_debito' => 'CD - Cartão de Débito',
                    'pix' => ' Pi - PIX',
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
                        name="{{ $key }}" 
                        data-forma="{{ $key }}" 
                        placeholder="0,00" 
                        min="0" 
                        style="max-width: 150px; font-weight:bold"
                        @if($loop->first) autofocus @endif
                    >
                </div>
            </div>
            @endforeach
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

<!-- JS para o Modal Finalizar Venda -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const formVenda = document.getElementById('formFinalizarVenda');
        if (!formVenda) return;

        const inputsPagamento = formVenda.querySelectorAll('.pagamento-modal');
        const restanteEl = document.getElementById('valor-restante');
        const trocoEl = document.getElementById('valor-troco');
        const totalModalEl = document.getElementById('total-venda-modal');
        const btnFinalizar = document.getElementById('btnFinalizar');

        // Exemplo: carrinho no JS (substitua pela sua estrutura real)
        // Cada item: {id, descricao, quantidade, preco, subtotal}
        let carrinho = window.carrinho || []; // garantir compatibilidade com seu PDV

        // ENTER preenche o restante
        inputsPagamento.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if(e.key === 'Enter') {
                    let restante = parseFloat(restanteEl.textContent.replace('R$', '').replace(',', '.')) || 0;
                    if (parseFloat(input.value) <= 0 && restante > 0) {
                        input.value = restante.toFixed(2);
                        restanteEl.textContent = 'R$ 0,00';
                        trocoEl.textContent = 'R$ 0,00';
                    }
                }
            });
        });

        // Finalizar venda
        btnFinalizar.addEventListener('click', function() {

            let totalPagamento = 0;
            let pagamentoData = {};

            inputsPagamento.forEach(input => {
                let val = parseFloat(input.value) || 0;
                input.value = val.toFixed(2);
                totalPagamento += val;
                pagamentoData[input.dataset.forma] = val.toFixed(2);
            });

            if(totalPagamento <= 0) {
                alert('Informe a forma de pagamento');
                return;
            }

            // ======== CONSOLE LOG ========
            console.log('=====================');
            console.log('Itens do Carrinho:');
            carrinho.forEach((item, index) => {
                console.log(`${index + 1}. ${item.descricao} | Qtd: ${item.quantidade} | Preço: ${item.preco} | Subtotal: ${item.subtotal}`);
            });

            console.log('---------------------');
            console.log('Dados do Pagamento:', pagamentoData);
            console.log('Resumo da Venda:');
            console.log('Total:', totalModalEl.textContent);
            console.log('Restante:', restanteEl.textContent);
            console.log('Troco:', trocoEl.textContent);
            console.log('=====================');

            // Aqui você envia o form ao backend
            formVenda.submit();

        });

    });
</script>
