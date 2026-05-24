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
    window.carrinho = window.carrinho || [];

    document.addEventListener('DOMContentLoaded', function () {

        const inputsPagamento = document.querySelectorAll('.pagamento-modal');
        const restanteEl      = document.getElementById('valor-restante');
        const trocoEl         = document.getElementById('valor-troco');
        const totalModalEl    = document.getElementById('total-venda-modal');
        const btnFinalizar    = document.getElementById('btnFinalizar');
        const modalFinalizar  = document.getElementById('modalFinalizarVenda');

        let carrinho = window.carrinho || [];
        
        window.__pdvUltimaFormaFocada = 'dinheiro';


        if (modalFinalizar) {
            modalFinalizar.addEventListener('shown.bs.modal', function () {
                const totalGeralPDV = document.getElementById('totalGeral');
                if (totalGeralPDV) {
                    totalModalEl.innerText = totalGeralPDV.innerText || totalGeralPDV.textContent;
                }

                if (window.financeiroCliente) {
                    document.getElementById('saldo-cliente-modal').textContent =
                        'R$ ' + Number(window.financeiroCliente.saldo_apos)
                            .toFixed(2)
                            .replace('.', ',');
                }
                 calcularPagamentos();
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


        function obtenerTotalVenda() {
            const totalGeralEl = document.getElementById('totalGeral');
            if (!totalGeralEl) return 0;
            return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
        }


        function obtenerSaldoCarteira() {
            return window.cliente?.saldo || window.clienteSelecionado?.saldo || 0;
        }


        inputsPagamento.forEach(input => {
            input.addEventListener('focus', function() {
                window.__pdvUltimaFormaFocada = this.dataset.forma;
            });
        });


        // ===============================
        // ATALHOS TECLADO RÁPIDOS
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
                let saldoDisponivel = obtenerSaldoCarteira();
                let statusCredito = window.cliente?.status || window.clienteSelecionado?.status;

                if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
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


        // OUVINTE DE INPUT EM TEMPO REAL
        inputsPagamento.forEach(input => {
            input.addEventListener('input', function() {
                calcularPagamentos(this);
            });
        });


        // ===============================
        // ENTER INTELIGENTE NO BOTÃO
        // ===============================
        inputsPagamento.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    let restante = parseMoney(restanteEl.textContent);
                    let valorAtual = parseFloat(input.value) || 0;

                    if (valorAtual <= 0 && restante > 0) {
                        input.value = restante.toFixed(2);
                        calcularPagamentos(this);
                    }

                    let restanteFinal = parseMoney(restanteEl.textContent);
                    
                    if (restanteFinal <= 0) {
                        setTimeout(() => {
                            if (btnFinalizar) btnFinalizar.focus();
                        }, 10);
                    } else {
                        input.focus();
                        input.select();
                    }
                }
            });
        });


        btnFinalizar?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnFinalizar.click();
            }
        });


        // ========================================================
        // REGRA DO TROCO INTELIGENTE REFORMULADA POR COMPLETO
        // ========================================================
        function calcularPagamentos(inputModificado = null) {
            
            let totalVenda = parseMoney(totalModalEl.innerText);
            
            let somaOutrasFormas = 0;
            inputsPagamento.forEach(input => {
                if (input.dataset.forma !== 'dinheiro') {
                    somaOutrasFormas += parseFloat(input.value) || 0;
                }
            });

            let restanteParaDinheiro = totalVenda - somaOutrasFormas;
            if (restanteParaDinheiro < 0) restanteParaDinheiro = 0;


            if (inputModificado && inputModificado.dataset.forma !== 'dinheiro') {
                let valorDigitado = parseFloat(inputModificado.value) || 0;
                let somaSemOAtual = 0;
                
                inputsPagamento.forEach(input => {
                    if (input !== inputModificado && input.dataset.forma !== 'dinheiro') {
                        somaSemOAtual += parseFloat(input.value) || 0;
                    }
                });

                if (somaSemOAtual + valorDigitado > totalVenda) {
                    valorDigitado = totalVenda - somaSemOAtual;
                    if (valorDigitado < 0) valorDigitado = 0;
                    inputModificado.value = valorDigitado > 0 ? valorDigitado.toFixed(2) : '';
                }
            }


            const inputDinheiro = document.querySelector('.pagamento-modal[data-forma="dinheiro"]');
            let valorDinheiro = inputDinheiro ? (parseFloat(inputDinheiro.value) || 0) : 0;


            let somaTotalInformada = 0;
            inputsPagamento.forEach(input => {
                somaTotalInformada += parseFloat(input.value) || 0;
            });


            let restanteFinal = totalVenda - somaTotalInformada;
            let trocoFinal = 0;


            if (restanteFinal < 0) {
                if (valorDinheiro > restanteParaDinheiro) {
                    trocoFinal = valorDinheiro - restanteParaDinheiro;
                } else {
                    trocoFinal = Math.abs(restanteFinal);
                }
                restanteFinal = 0;
            }


            restanteEl.innerText = formatMoney(restanteFinal);
            trocoEl.innerText = formatMoney(trocoFinal);
        }


        // ===============================
        // REGRA CARTEIRA ORIGINAL
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


        // ========================================================
        // ENVIO AJAX COMPATÍVEL VIA FETCH (FLUXO CONTINUO)
        // ========================================================
               // ========================================================
        // ENVIO AJAX COMPATÍVEL VIA FETCH (CORRIGIDO E ALINHADO)
        // ========================================================
        btnFinalizar?.addEventListener('click', function() {
            let totalPagamento = 0;

            inputsPagamento.forEach(input => {
                totalPagamento += parseFloat(input.value) || 0;
            });

            if (totalPagamento <= 0) {
                alert('Informe a forma de pagamento');
                return;
            }

            // 👤 RESOLUÇÃO DINÂMICA DO CLIENTE_ID (Lê o campo real onde seu modal injeta os dados)
                       // 👤 RESOLUÇÃO DINÂMICA DO CLIENTE_ID
            let idClienteFinal = null;
            
            // Tenta ler o input que muda quando você usa o modal de clientes
            const inputClienteHidden = document.getElementById('cliente_id') || document.querySelector('input[name="cliente_id"]');
            
            // Só envia o ID se ele for um número de cliente selecionado e não for o texto "VENDA BALCAO"
            if (inputClienteHidden && inputClienteHidden.value && inputClienteHidden.value.trim() !== '' && inputClienteHidden.value !== 'VENDA BALCAO') {
                idClienteFinal = parseInt(inputClienteHidden.value, 10);
            }


            // 🧑‍💼 ID do Operador Logado
            let idFuncionarioFinal = parseInt(window.auth_user_id, 10) || 
                                     parseInt(document.getElementById('funcionario_id')?.value, 10) || 1;

            // 🏪 CORREÇÃO CAIXA: Inverte a ordem para ler primeiro o input hidden do Blade (que tem o 310) 
            const inputCaixaHidden = document.querySelector('input[name="caixa_id"]') || document.getElementById('caixa_id');
            let idCaixaFinal = parseInt(inputCaixaHidden?.value, 10) || parseInt(window.caixa_id, 10) || 1;
            
            let formData = new FormData();
            formData.append('cliente_id', idClienteFinal || '');
            formData.append('funcionario_id', idFuncionarioFinal);
            formData.append('caixa_id', idCaixaFinal);
            formData.append('dataVenda', new Date().toISOString().slice(0, 19).replace('T', ' '));

            // Processamento dos Itens do Carrinho
            if (window.carrinho && window.carrinho.length > 0) {
                window.carrinho.forEach((item, index) => {
                    formData.append(`itens[${index}][produto_id]`, item.produto_id || item.id || "");
                    formData.append(`itens[${index}][quantidade]`, item.quantidade || item.qtd || 1);
                    formData.append(`itens[${index}][valor_unitario]`, item.valor_unitario || item.preco || item.preco_venda || 0);
                    formData.append(`itens[${index}][desconto]`, item.desconto || 0);
                    if (item.lote_id) {
                        formData.append(`itens[${index}][lote_id]`, item.lote_id);
                    }
                });
            }

            inputsPagamento.forEach(input => {
                let valorForma = parseFloat(input.value) || 0;
                formData.append(`pagamentos[${input.dataset.forma}]`, valorForma);
                formData.append(input.dataset.forma, valorForma);
            });

            // Extrai o troco calculado na interface para repassar à URL do cupom
            let valorTroco = 0;
            if (trocoEl) {
                valorTroco = parseFloat(trocoEl.innerText.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
            }

            // Altera o estado do botão para processando
            const textoOriginalBtn = btnFinalizar.innerHTML;
            btnFinalizar.disabled = true;
            btnFinalizar.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...`;

            fetch('/vendas/finalizar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(texto => { throw new Error(texto) });
                }
                return response.json();
            })
            .then(data => {
                let idDaVendaGerada = data.venda_id || data.id || (data.venda && data.venda.id);

                if (data.success && idDaVendaGerada) {
                    // alert('Venda finalizada com sucesso!');
                    
                    // Impressão via Iframe oculto
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = `/venda/${idDaVendaGerada}/cupom?troco=${valorTroco}`;
                    document.body.appendChild(iframe);
                    
                    iframe.onload = function() {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                        
                        // Limpeza nativa sem dar reload na página
                        if (typeof executarLimpezaEResetPDV === 'function') {
                            executarLimpezaEResetPDV();
                        } else {
                            window.carrinho = [];
                            inputsPagamento.forEach(input => input.value = '');
                            if (typeof calcularPagamentos === 'function') calcularPagamentos();
                        }
                        
                        setTimeout(() => {
                            if (iframe.parentNode) document.body.removeChild(iframe);
                        }, 1000);
                    };

                    // Fecha o modal de forma nativa
                    if (typeof bootstrap !== 'undefined' && modalFinalizar) {
                        bootstrap.Modal.getInstance(modalFinalizar)?.hide();
                    }

                } else {
                    alert('Atenção: O sistema recusou a operação.\nMotivo: ' + (data.message || data.erro || 'Erro desconhecido.'));
                }
            })
            .catch(error => {
                console.error(error);
                alert('Erro de comunicação: ' + error.message);
            })
            .finally(() => {
                btnFinalizar.disabled = false;
                btnFinalizar.innerHTML = textoOriginalBtn;
            });
        });


        function verificarNovoCaixa() {
            // Gatilho interno original mantido em escopo
        }


        function executarLimpezaEResetPDV() {
            window.carrinho = [];
            
            if (typeof atualizarTabelaCarrinho === 'function') {
                atualizarTabelaCarrinho();
            }
            if (typeof renderizarCarrinho === 'function') {
                renderizarCarrinho();
            }

            if (typeof bootstrap !== 'undefined' && modalFinalizar) {
                let modalInstance = bootstrap.Modal.getInstance(modalFinalizar);
                modalInstance?.hide();
            }

            inputsPagamento.forEach(input => input.value = '');

            const totalGeralPDV = document.getElementById('totalGeral');
            if (totalGeralPDV) {
                totalGeralPDV.textContent = 'R$ 0,00';
            }

            setTimeout(() => {
                const inputBarras = document.getElementById('codigo_barras');
                if (inputBarras) {
                    inputBarras.value = '';
                    inputBarras.focus();
                }
            }, 150);
        }


        function atualizarResumoClienteFinalizar() {
            // Mantida íntegra para interceptações de escopo externo
        }

    });
</script>
