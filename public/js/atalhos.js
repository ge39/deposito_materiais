// =========================================================================
// ESTADO GLOBAL DO PDV (COMPATÍVEL COM LOCAL E REDE)
// =========================================================================
window.carrinho = window.carrinho || [];
window.cliente = window.cliente || null;
window.caixaBloqueado = false;
window.CAIXA_ID = window.CAIXA_ID || null;

document.addEventListener('DOMContentLoaded', function () {
    
    // =========================================================================
    // 🔍 CAPTURADOR AUTOMÁTICO DE CAIXA_ID DO DOM
    // =========================================================================
    const inputCaixa = document.querySelector('input[name="caixa_id"]') || 
                       document.getElementById('input-caixa-id') || 
                       document.getElementById('caixa_id');
    
    if (inputCaixa && inputCaixa.value) {
        window.CAIXA_ID = inputCaixa.value;
    } else {
        console.warn('Aviso: Elemento HTML com o caixa_id não foi localizado na página atual do PDV.');
    }

    // =========================================================================
    // 🔲 INTEGRAÇÃO E ISOLAMENTO DO CAMPO DE CÓDIGO DE BARRAS
    // =========================================================================
    const inputCodigo = document.getElementById('codigo_barras') || 
                        document.querySelector('input[name="codigo_barras"]');

    if (inputCodigo) {
        // Evita que o Enter recarregue a página caso o input esteja dentro de uma tag <form>
        const formPai = inputCodigo.closest('form');
        if (formPai) {
            formPai.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        // Intercepta a digitação manual e impede que o Enter acione outros atalhos do PDV
        inputCodigo.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation(); // Trava o evento aqui para não disparar atalhos globais

                const codigoDigitado = this.value.trim();
                if (codigoDigitado === '') return;

                // Aciona a função global de inserção de produto do seu PDV
                if (typeof window.adicionarProdutoCarrinho === 'function') {
                    window.adicionarProdutoCarrinho(codigoDigitado);
                } else if (typeof adicionarProduto === 'function') {
                    adicionarProduto(codigoDigitado);
                }

                this.value = ''; // Limpa o campo após processar
            }
        });
    }

    // =========================================================================
    // 🪟 FUNÇÕES DE MODAL (FINALIZAR VENDA)
    // =========================================================================
    window.abrirModalFinalizar = function () {
        const modal = document.getElementById('modalFinalizarVenda');
        if (!modal) return;
        const instancia = bootstrap.Modal.getOrCreateInstance(modal);
        instancia.show();
        atualizarModalFinalizarCliente();
    };

    function atualizarModalFinalizarCliente() {
        if (!window.cliente) return;
        const saldoEl = document.getElementById('saldo-cliente-finalizar');
        const limiteEl = document.getElementById('limite-cliente-finalizar');
        const saldo = Number(window.cliente.saldo_apos || 0);
        const limite = Number(window.cliente.limite_credito || 0);
        
        if (saldoEl) {
            saldoEl.textContent = `Saldo: R$ ${saldo.toFixed(2).replace('.', ',')}`;
        }
        if (limiteEl) {
            limiteEl.textContent = `Limite: R$ ${limite.toFixed(2).replace('.', ',')}`;
        }
    }

    // =========================================================================
    // 🎛️ CENTRAL ÚNICA DE ATALHOS GLOBAIS (MODO CAPTURE)
    // =========================================================================
    document.addEventListener('keydown', function (e) {
        
        // 🔒 Trava total do sistema caso o caixa esteja sob bloqueio de sangria
        if (window.caixaBloqueado) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (e.repeat) return;

        // F2 - CLIENTE
        if (e.key === 'F2') {
            e.preventDefault();
            e.stopPropagation();
            const modal = document.getElementById('modalCliente');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F3 - PRODUTO
        if (e.key === 'F3') {
            e.preventDefault();
            e.stopPropagation();
            const modal = document.getElementById('modalProduto');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F4 - ORÇAMENTO (Protegido contra interceptação do Chrome na rede)
        if (e.key === 'F4') {
            e.preventDefault();
            e.stopPropagation();
            const modal = document.getElementById('modalOrcamento');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F6 - FINALIZAR VENDA
        if (e.key === 'F6' || e.code === 'F6') {
            if (document.querySelector('.modal.show')) return;
            e.preventDefault();
            e.stopPropagation();
            window.abrirModalFinalizar();
        }

        // F10 - FECHAMENTO CAIXA (Unificado aqui para não sofrer concorrência do navegador)
        if (e.key === 'F10') {
            e.preventDefault();
            e.stopPropagation();

            // Segunda tentativa de captura do ID do caixa em tempo de execução
            if (!window.CAIXA_ID) {
                const inputCaixaReconferencia = document.querySelector('input[name="caixa_id"]') || 
                                               document.getElementById('input-caixa-id') || 
                                               document.getElementById('caixa_id');
                if (inputCaixaReconferencia && inputCaixaReconferencia.value) {
                    window.CAIXA_ID = inputCaixaReconferencia.value;
                }
            }

            if (!window.CAIXA_ID || window.CAIXA_ID === "") {
                alert('Nenhum caixa aberto ou localizado no HTML deste PDV.');
                return;
            }

            // Cláusula de barreira preventiva contra abandono de vendas
            const itensNoCarrinho = window.carrinho || [];
            if (itensNoCarrinho.length > 0) {
                const prosseguir = confirm('Atenção: Há itens pendentes no carrinho! Deseja realmente descartar esta venda para prosseguir ao fechamento do caixa?');
                if (!prosseguir) return;
            }

            window.location.href = `/fechamento_caixa/fechamento/${window.CAIXA_ID}`;
        }
        
    }, true); // O 'true' ativa o modo de captura priorizando seu código antes das regras do navegador

    // =========================================================================
    // 🔓 DESBLOQUEIO MANUAL DO CAIXA
    // =========================================================================
    const btnAbrirCaixa = document.querySelector('.btn-abrir-caixa');
    if (btnAbrirCaixa) {
        btnAbrirCaixa.addEventListener('click', function () {
            window.caixaBloqueado = false;
            document.body.classList.remove('caixa-bloqueado');
            const overlay = document.getElementById('overlay-caixa-bloqueado');
            if (overlay) overlay.style.display = 'none';
        });
    }
});
