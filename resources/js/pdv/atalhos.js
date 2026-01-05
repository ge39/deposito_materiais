document.addEventListener('DOMContentLoaded', function () {

    // Instâncias únicas dos modais
    const modalCliente = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalCliente')
    );

    const modalProduto = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalProduto')
    );

    const modalOrcamento = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalOrcamento')
    );

    const modalFinalizar = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalFinalizarVenda')
);

    const modalCaixasEsquecidosEl = document.getElementById('listaCaixasEsquecidos');
    const modalCaixasEsquecidos = bootstrap.Modal.getOrCreateInstance(
        modalCaixasEsquecidosEl
    );
   
    /**
     * 🔒 CONTROLE DE BLOQUEIO DO PDV
     */
    function bloquearPDV() {
        document.body.classList.add('caixa-bloqueado');
    }

    function desbloquearPDV() {
        document.body.classList.remove('caixa-bloqueado');
    }

    // Verifica se o PDV deve iniciar bloqueado
    function caixaAbertoHaMaisDe12Horas() {
        if (!window.PDV_STATE?.caixa_aberto_em) return false;

        const abertoEm = new Date(window.PDV_STATE.caixa_aberto_em);
        const agora = new Date();

        const diffHoras = (agora - abertoEm) / (1000 * 60 * 60);

        return diffHoras >= 12;
    }

    function pdvEstaBloqueadoPorRegra() {
        const status = window.PDV_STATE?.caixa_status;

        // 🔒 Status inválido
        if (status === 'bloqueado' || status === 'fechado') {
            return true;
        }

        // ⏰ Aberto há mais de 12h
        if (status === 'aberto' && caixaAbertoHaMaisDe12Horas()) {
            return true;
        }

        return false;
    }

    /**
     * Quando o modal de caixas esquecidos abrir → BLOQUEIA PDV
     */
    modalCaixasEsquecidosEl.addEventListener('shown.bs.modal', function () {
        bloquearPDV();
    });

    /**
     * Quando o modal fechar → DESBLOQUEIA PDV
     */
    modalCaixasEsquecidosEl.addEventListener('hidden.bs.modal', function () {
        desbloquearPDV();
    });

    /**
     * Verifica se o PDV está bloqueado
     */
    function pdvEstaBloqueado() {
        return document.body.classList.contains('caixa-bloqueado');
    }

    /**
     * 🔑 ATALHOS DO TECLADO
     */
    document.addEventListener('keydown', function (e) {

        if (e.repeat) return;

        // 🔒 BLOQUEIO TOTAL (STATUS, 12H OU UI)
        if (pdvEstaBloqueado() || pdvEstaBloqueadoPorRegra()) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }


        switch (e.key) {

            case 'F2':
                e.preventDefault();
                modalCliente.show();
                break;

            case 'F3':
                e.preventDefault();
                modalProduto.show();
                break;

            case 'F4':
                e.preventDefault();
                modalOrcamento.show();
                break;  

            case 'F6':
                 e.preventDefault();
                 modalFinalizar.show();
                break;             
        }
            
    });
       
});
