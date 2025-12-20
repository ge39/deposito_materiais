window.addItemCarrinho = function (dados) {}
window.removerLinha = function (tr) {}
window.recalcularTotal = function () {}

window.Carrinho = (function () {

    function init() {
        console.log('Carrinho iniciado');
    }

    function limpar() {
        console.log('Carrinho limpo');
    }

    return {
        init,
        limpar
    };

})();
