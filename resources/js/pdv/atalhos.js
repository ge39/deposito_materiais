document.addEventListener('DOMContentLoaded', function () {
    
    // FUNÇÃO PARA ABRIR MODAL DE FINALIZAR VENDA
   window.abrirModalFinalizar = function () {
    const modal = document.getElementById('modalFinalizarVenda');
    if (!modal) return;

    const instancia = bootstrap.Modal.getOrCreateInstance(modal);
    instancia.show();
};
    /**
     * CONTROLE GLOBAL DO ESTADO DO CAIXA
     * true  = caixa bloqueado
     * false = caixa liberado
     */
    window.caixaBloqueado = false;

    /**
     * LISTENER GLOBAL DE TECLADO (CAPTURE)
     * BLOQUEIA QUALQUER TECLA QUANDO CAIXA FECHADO
     */
    document.addEventListener('keydown', function (e) {

        if (window.caixaBloqueado) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (e.repeat) return;

        // ===== ATALHOS LIBERADOS =====

        if (e.key === 'F2') {
            e.preventDefault();
            const modal = document.getElementById('modalCliente');
            if (modal) new bootstrap.Modal(modal).show();
        }

        if (e.key === 'F3') {
            e.preventDefault();
            const modal = document.getElementById('modalProduto');
            if (modal) new bootstrap.Modal(modal).show();
        }

        if (e.key === 'F4') {
            e.preventDefault();
            const modal = document.getElementById('modalOrcamento');
            if (modal) new bootstrap.Modal(modal).show();
        }
         
        if (e.code === 'F6') {

            // Se já existe um modal aberto, NÃO faz nada
            if (document.querySelector('.modal.show')) {
                return;
            }

            e.preventDefault();
            abrirModalFinalizar();
        }
    
    }, true); // capture=true (fundamental)

    /**
     * BOTÃO ABRIR CAIXA (ÚNICO ELEMENTO ATIVO)
     */
    const btnAbrirCaixa = document.querySelector('.btn-abrir-caixa');

    if (btnAbrirCaixa) {
        btnAbrirCaixa.addEventListener('click', function () {

            console.log('Caixa aberto');

            window.caixaBloqueado = false;

            document.body.classList.remove('caixa-bloqueado');

            const overlay = document.getElementById('overlay-caixa-bloqueado');
            if (overlay) overlay.style.display = 'none';
        });
    }

});