// =================================================================
// PDV STORAGE SERVICE - GERENCIAMENTO INTELIGENTE DO LOCALSTORAGE
// =================================================================

const PdvStorage = {
    // Chaves únicas para isolar os dados no navegador
    KEY_CARRINHO: 'pdv_carrinho_atual',
    KEY_CONTEXTO: 'pdv_contexto_venda',

    /**
     * Salva o array de produtos atual na memória do navegador
     * @param {Array} itens 
     */
    salvarCarrinho(itens) {
        try {
            if (!Array.isArray(itens)) return;
            localStorage.setItem(this.KEY_CARRINHO, JSON.stringify(itens));
        } catch (error) {
            console.error('Erro ao gravar LocalStorage:', error);
        }
    },

    /**
     * Recupera os produtos salvos. Retorna um array vazio se não houver nada.
     * @returns {Array}
     */
    obterCarrinho() {
        try {
            const dados = localStorage.getItem(this.KEY_CARRINHO);
            return dados ? JSON.parse(dados) : [];
        } catch (error) {
            console.error('Erro ao ler LocalStorage:', error);
            return [];
        }
    },

    /**
     * Salva metadados da venda (ex: id do cliente selecionado, id do caixa, etc)
     * @param {Object} objetoContexto 
     */
    salvarContexto(objetoContexto) {
        try {
            localStorage.setItem(this.KEY_CONTEXTO, JSON.stringify(objetoContexto));
        } catch (error) {
            console.error('Erro ao salvar contexto:', error);
        }
    },

    /**
     * Recupera os metadados salvos da venda
     * @returns {Object|null}
     */
    obterContexto() {
        try {
            const dados = localStorage.getItem(this.KEY_CONTEXTO);
            return dados ? JSON.parse(dados) : null;
        } catch (error) {
            return null;
        }
    },

    /**
     * Limpa os dados temporários após o sucesso da venda
     */
    limparPdv() {
        try {
            localStorage.removeItem(this.KEY_CARRINHO);
            localStorage.removeItem(this.KEY_CONTEXTO);
        } catch (error) {
            console.error('Erro ao limpar LocalStorage:', error);
        }
    }
};

// Torna o serviço disponível globalmente na janela do navegador
window.PdvStorage = PdvStorage;
