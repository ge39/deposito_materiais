window.Carrinho = (function() {

    let itens = [];

    function init() { 
        try {
            const salvo = localStorage.getItem('pdv_carrinho_atual');
            itens = salvo ? JSON.parse(salvo) : [];
        } catch (e) {
            itens = [];
        }
        window.carrinho = itens;
    }

    function adicionar(item) {  
        itens.push(item);     
        
        // 🎯 EXIBIÇÃO CIRÚRGICA NO F12
        console.log("➡️ ITEM ADICIONADO:", item);
        console.log("🛒 CARRINHO COMPLETO ATUAL:", itens);
        
        // Gravação direta e simples
        localStorage.setItem('pdv_carrinho_atual', JSON.stringify(itens));
        window.carrinho = itens;
    }

    function listar() { return itens; }

    function remover(index) { 
        itens.splice(index, 1); 
        
        console.log("❌ ITEM REMOVIDO NO INDEX:", index);
        console.log("🛒 CARRINHO ATUALIZADO:", itens);
        
        localStorage.setItem('pdv_carrinho_atual', JSON.stringify(itens));
        window.carrinho = itens;
    }

    function limpar() { 
        itens = []; 
        localStorage.removeItem('pdv_carrinho_atual');
        window.carrinho = [];
        console.log("🧹 CARRINHO LIMPO COMPLETA MENTE");
    }

    function total() {
        return itens.reduce((acc, item) => acc + (item.quantidade * item.preco_unitario), 0);
    }

    init();

    return { init, adicionar, listar, remover, limpar, total };
})();
