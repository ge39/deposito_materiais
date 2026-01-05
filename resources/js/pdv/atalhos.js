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

    const modalCaixasEsquecidosEl = document.getElementById('listaCaixasEsquecidos');
    const modalCaixasEsquecidos = bootstrap.Modal.getOrCreateInstance(
        modalCaixasEsquecidosEl
    );
    document.addEventListener('keydown', function(e) {
        if (e.code === 'F6') {
            e.preventDefault();

            // Pega o total exibido
            const total = document.getElementById('totalGeral')?.textContent || '0';
            console.log('💰 Total exibido antes de F6:', total);
            window.carrinhoTotal = total;

            // Abre modal finalizador
            const modalEl = document.getElementById('modalFinalizarVenda');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
                console.log('F6 OK - modal finalizador aberto');
            } else {
                console.error('❌ Modal #modalFinalizar não encontrado no DOM');
            }
        }

    });

    /**
     * 🔒 CONTROLE DE BLOQUEIO DO PDV
     */
    function bloquearPDV() {
        document.body.classList.add('caixa-bloqueado');
    }

    function desbloquearPDV() {
        document.body.classList.remove('caixa-bloqueado');
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

        // 🔒 BLOQUEIO TOTAL
        if (pdvEstaBloqueado()) {
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
        }
            
    });

   
});
