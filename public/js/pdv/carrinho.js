// window.Carrinho = (function() {
//     let itens = [];

//     function init() { itens = []; }

//     function adicionar(item) {
//         itens.push(item);
//         // console.log('Item adicionado:', item);
//         // console.log('Carrinho atual:', itens);
//     }

//     function listar() { return itens; }
//     function remover(index) { itens.splice(index, 1); }
//     function limpar() { itens = []; }

//     return { init, adicionar, listar, remover, limpar };
// })();

window.Carrinho = (function() {

    let itens = [];

    function init() { itens = []; }

    function adicionar(item) {  itens.push(item);     }

    function listar() {  return itens;  }

    function remover(index) { itens.splice(index, 1); }

    function limpar() { itens = []; }

    function total() {
        return itens.reduce((acc, item) => {
            return acc + (item.quantidade * item.preco_unitario);
        }, 0);
    }

    return {
        init,
        adicionar,
        listar,
        remover,
        limpar,
        total
    };

})();
