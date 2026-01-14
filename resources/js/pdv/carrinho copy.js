 window.Carrinho.adicionar({ id: 1, quantidade: 2, preco: 10 });
 console.log('Resultado:'.window.Carrinho.listar());


window.Carrinho = (function () {
    let itens = [];

    function init() {}
    function adicionar(item) {}
    function listar() { return itens; }

    return { init, adicionar, listar };
})();














