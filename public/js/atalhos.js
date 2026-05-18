// ==========================================
// 🛡️ ESCOPO DE MEMÓRIA GLOBAL DO PDV (COMPARTILHADO EM REDE)
// ==========================================
window.carrinho = window.carrinho || [];
window.cliente = window.cliente || null;
window.caixaBloqueado = false;
window.CAIXA_ID = window.CAIXA_ID || null;

document.addEventListener('DOMContentLoaded', function () {
    
    // ==========================================
    // 🔍 CAPTURADOR AUTOMÁTICO DE CAIXA_ID DO DOM
    // ==========================================
    const inputCaixa = document.querySelector('input[name="caixa_id"]') || 
                       document.getElementById('input-caixa-id') || 
                       document.getElementById('caixa_id');
    
    if (inputCaixa && inputCaixa.value) {
        window.CAIXA_ID = inputCaixa.value;
    }

    // =========================
    // ABRIR MODAL FINALIZAR
    // =========================
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

    // ==========================================
    // 🎹 CONTROLE DE ATALHOS GLOBAIS (CAPTURE: TRUE)
    // ==========================================
    document.addEventListener('keydown', function (e) {
        if (window.caixaBloqueado) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (e.repeat) return;

        // F2 - CLIENTE
        if (e.key === 'F2') {
            e.preventDefault();
            const modal = document.getElementById('modalCliente');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F3 - PRODUTO
        if (e.key === 'F3') {
            e.preventDefault();
            const modal = document.getElementById('modalProduto');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F4 - ORÇAMENTO
        if (e.key === 'F4') {
            e.preventDefault();
            const modal = document.getElementById('modalOrcamento');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F6 - FINALIZAR VENDA (Blindado contra erro de DOM se o modal não existir)
        if (e.code === 'F6') {
            if (document.querySelector('.modal.show')) return;
            e.preventDefault();
            const btnF6 = document.getElementById('btnF6');
            if (btnF6 || document.getElementById('modalFinalizarVenda')) {
                window.abrirModalFinalizar();
            } else {
                console.warn("Aviso do Sistema: Elementos do modal de finalização não estão presentes nesta view.");
            }
        }
    }, true);

    // ==========================================
    // 🛒 LEITOR DE CÓDIGO DE BARRAS / EVENTO ENTER
    // ==========================================
    const inputCodigoBarras = document.getElementById('codigo_barras') || document.querySelector('input[name="codigo_barras"]');
    if (inputCodigoBarras) {
        inputCodigoBarras.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault(); 
                
                const valorBipado = this.value.trim();
                if (valorBipado === "") return;

                if (typeof window.adicionarProdutoAoCarrinho === 'function') {
                    window.adicionarProdutoAoCarrinho(valorBipado);
                } else if (typeof adicionarProduto === 'function') {
                    adicionarProduto(valorBipado);
                }
            }
        });
    }

    // =========================
    // BOTÃO ABRIR CAIXA
    // =========================
    const btnAbrirCaixa = document.querySelector('.btn-abrir-caixa');
    if (btnAbrirCaixa) {
        btnAbrirCaixa.addEventListener('click', function () {
            window.caixaBloqueado = false;
            document.body.classList.remove('caixa-bloqueado');
            const overlay = document.getElementById('overlay-caixa-bloqueado');
            if (overlay) overlay.style.display = 'none';
        });
    }

    // ==========================================
    // FECHAMENTO CAIXA (F10)
    // ==========================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F10') {
            e.preventDefault();

            if (!window.CAIXA_ID) {
                const inputCaixaReconferencia = document.querySelector('input[name="caixa_id"]') || document.getElementById('input-caixa-id');
                if (inputCaixaReconferencia && inputCaixaReconferencia.value) {
                    window.CAIXA_ID = inputCaixaReconferencia.value;
                }
            }

            if (!window.CAIXA_ID || window.CAIXA_ID === "") {
                alert('Nenhum caixa aberto ou localizado no HTML deste PDV.');
                return;
            }

            const itensNoCarrinho = window.carrinho || [];
            if (itensNoCarrinho.length > 0) {
                const prosseguir = confirm('Atenção: Há itens pendentes no carrinho! Deseja realmente descartar esta venda para prosseguir ao fechamento do caixa?');
                if (!prosseguir) return;
            }

            window.location.href = `/fechamento_caixa/fechamento/${window.CAIXA_ID}`;
        }
    });
});
