document.addEventListener('DOMContentLoaded', function () {

    // =========================
    // ESTADO GLOBAL DO CAIXA
    // =========================
    window.caixaBloqueado = false;

    // =========================
    // CLIENTE GLOBAL (CACHE)
    // =========================
    window.cliente = window.cliente || null;

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

    // =========================
    // ATALHOS GLOBAIS (CAPTURE)
    // =========================
    document.addEventListener('keydown', function (e) {

        // 🔒 trava total do sistema
        if (window.caixaBloqueado) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (e.repeat) return;

        // =========================
        // F2 - CLIENTE
        // =========================
        if (e.key === 'F2') {
            e.preventDefault();
            const modal = document.getElementById('modalCliente');
            if (modal) new bootstrap.Modal(modal).show();
        }

        // =========================
        // F3 - PRODUTO
        // =========================
        if (e.key === 'F3') {
            e.preventDefault();
            const modal = document.getElementById('modalProduto');
            if (modal) new bootstrap.Modal(modal).show();
        }

        // =========================
        // F4 - ORÇAMENTO
        // =========================
        if (e.key === 'F4') {
            e.preventDefault();
            const modal = document.getElementById('modalOrcamento');
            if (modal) new bootstrap.Modal(modal).show();
        }

        // =========================
        // F6 - FINALIZAR VENDA
        // =========================
        if (e.code === 'F6') {

            if (document.querySelector('.modal.show')) return;

            e.preventDefault();
            window.abrirModalFinalizar();
        }

    }, true);

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

    // =========================
    // FECHAMENTO CAIXA (F10)
    // =========================
    document.addEventListener('keydown', function (e) {

        if (e.key === 'F10') {
            e.preventDefault();

            if (!window.CAIXA_ID) {
                alert('Nenhum caixa aberto.');
                return;
            }

            window.location.href = `/fechamento_caixa/fechamento/${window.CAIXA_ID}`;
        }

    });

});