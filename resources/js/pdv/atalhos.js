document.addEventListener('DOMContentLoaded', function () {

    // console.log('Atalhos do PDV carregados');

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

    /**
     * Verifica se o PDV está bloqueado
     * Regra: body.caixa-bloqueado = teclado bloqueado
     */
    function pdvEstaBloqueado() {
        return document.body.classList.contains('caixa-bloqueado');
    }

    document.addEventListener('keydown', function (e) {

        // Evita repetição contínua
        if (e.repeat) return;

        /**
         * 🔒 BLOQUEIO GLOBAL DO TECLADO
         * Se o PDV estiver bloqueado, nenhuma tecla funciona
         */
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

            // Exemplo futuro:
            // case 'F5':
            //     e.preventDefault();
            //     finalizarVenda();
            //     break;
        }
    });
});
