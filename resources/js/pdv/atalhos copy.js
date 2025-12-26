document.addEventListener('DOMContentLoaded', function () {

    console.log('Atalhos do PDV carregados');

    /**
     * CONTROLE GLOBAL DO ESTADO DO CAIXA
     * true  = caixa bloqueado
     * false = caixa liberado
     */
    window.caixaBloqueado = true;

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
