window.PDV = (function () {

    function init() {
        if (!window.Produto) {
            console.error('Produto não carregado');
            return;
        }

        Produto.init();
        Carrinho.init();
        Orcamento.init();
        UI.init();
        Atalhos.init();
        regras.init();
        console.log('PDV iniciado');
    }

    return { init };

})();
