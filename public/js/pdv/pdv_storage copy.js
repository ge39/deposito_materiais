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
     * 🚀 CORREÇÃO CIRÚRGICA: Usa strings explícitas e chama o destruidor seguro por string direta
     */
    limparPdv() {
        try {
            // Executa a limpeza direta por strings seguras
            localStorage.removeItem('pdv_carrinho_atual');
            localStorage.removeItem('pdv_contexto_venda');
            localStorage.removeItem('ultimo_produto_imagem');
            localStorage.removeItem('carrinho');
            localStorage.removeItem('itens');
            console.log("✅ [PdvStorage] Limpeza padrão do PDV executada com sucesso.");
        } catch (error) {
            console.error('Erro ao limpar LocalStorage:', error);
        }
    },

    /**
     * 💣 MÉTODO ADICIONADO: Limpa cirurgicamente todas as chaves por string direta
     * Pode ser chamado via PdvStorage.limparLocalStoragePDV()
     */
    limparLocalStoragePDV() {
        try {
            localStorage.removeItem('pdv_carrinho_atual');
            localStorage.removeItem('pdv_contexto_venda');
            localStorage.removeItem('ultimo_produto_imagem');
            localStorage.removeItem('carrinho');
            localStorage.removeItem('itens');
            
            console.log("✅ [PdvStorage] Chaves do PDV removidas por string direta.");
            return true;
        } catch (error) {
            console.error("❌ [PdvStorage] Erro ao executar a remoção direta:", error);
            return false;
        }
    }
};

// 🎯 Mantém a função registrada globalmente na janela (Retrocompatibilidade)
window.limparLocalStoragePDV = PdvStorage.limparLocalStoragePDV;

// Torna o serviço disponível globalmente na janela do navegador
window.PdvStorage = PdvStorage;

// =======================================================================
// 📡 MÓDULO INTEGRADO: INJEÇÃO AUTOMÁTICA E RESTAURAÇÃO DE CAMPOS
// =======================================================================

// Função que roda ao clicar no botão físico de teste operacional
window.forcarLimpezaEletrochoque = function() {
    window.limparLocalStoragePDV(); // 1. Deleta os dados do navegador
    window.forcarInjecaoClienteBalcao(); // 2. Limpa a tela e restaura o cliente balcão
    alert("💥 LocalStorage e campos limpos na marra! Verifique a aba Application.");
};

// Aplica os dados do banco de dados diretamente nas propriedades .value dos inputs
window.forcarInjecaoClienteBalcao = function() {
    if (!window.CLIENTE_BALCAO) {
        console.warn("Aviso: window.CLIENTE_BALCAO não foi renderizado na memória do navegador.");
        return;
    }

    console.log("⚡ Executando injeção obrigatória pós-venda: Restaurando Cliente Balcão do Banco...");

    // Captura os inputs ocultos e visíveis utilizando os IDs e Names exatos do seu HTML
    const inputId       = document.getElementById('cliente_id');
    const inputNome     = document.querySelector('input[name="nome"]') || document.querySelector('input[name*="nome"]');
    const inputPessoa   = document.querySelector('input[name="pessoa"]');
    const inputTelefone = document.querySelector('input[name="telefone"]');
    const inputEndereco = document.getElementById('endereco');

    // Popular os inputs fisicamente com as strings extraídas do banco
    if (inputId)       inputId.value       = window.CLIENTE_BALCAO.id;       
    if (inputNome)     inputNome.value     = window.CLIENTE_BALCAO.nome;     
    if (inputPessoa)   inputPessoa.value   = window.CLIENTE_BALCAO.tipo;     
    if (inputTelefone) inputTelefone.value = window.CLIENTE_BALCAO.telefone; 
    if (inputEndereco) inputEndereco.value = window.CLIENTE_BALCAO.endereco; 

    // Limpezas complementares da tela (Carrinho e Totais)
    const tbody = document.getElementById('lista-itens') 
               || document.getElementById('lista-produtos') 
               || document.querySelector('#tabelaItensPDV tbody');
    if (tbody) tbody.innerHTML = '';

    const totalGeral = document.getElementById('total_geral') || document.getElementById('totalGeral') || document.getElementById('inputTotalGeral');
    if (totalGeral) {
        if (totalGeral.tagName === 'INPUT') totalGeral.value = 'R$ 0,00';
        else totalGeral.textContent = 'R$ 0,00';
    }

    // 🖼️ LIMPEZA ADICIONADA: Reseta os componentes de imagem (Principal e Blur) ao padrão
    const imgPrincipal = document.getElementById('produto-imagem');
    const imgFundoBlur = document.getElementById('produto-imagem-bg');
    const imagemPadrao = "/images/produto-sem-imagem.png";

    if (imgPrincipal) imgPrincipal.src = imagemPadrao;
    if (imgFundoBlur) {
        imgFundoBlur.src = imagemPadrao;
        imgFundoBlur.style.display = 'none'; // Desativa o fundo borrado para o ícone padrão
    }

    // Devolve o foco imediatamente para o leitor de código de barras
    setTimeout(() => {
        document.getElementById('codigo_barras')?.focus();
    }, 100);
};

// Sobrescreve a assinatura global para interceptar qualquer chamada do modal_finalizar
window.limparPDV = function() {
    window.forcarInjecaoClienteBalcao();
};

// =======================================================================
// 📡 MONITOR DE REQUISIÇÕES AUTOMÁTICO (FETCH SPY)
// =======================================================================
(function() {
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        const response = await originalFetch.apply(this, args);
        const url = args[0];

        // Se a rota chamada foi a de faturar orçamento ou finalizar venda
        if (typeof url === 'string' && (url.includes('/pdv/faturar') || url.includes('/vendas/finalizar') || url.includes('/vendas'))) {
            
            // Se o servidor retornou sucesso, executa a limpeza violenta por string direta
            if (response.ok) {
                window.limparLocalStoragePDV();
            }

            // Aguarda o encerramento do processo e restaura os inputs e o foco da tela
            setTimeout(() => {
                window.forcarInjecaoClienteBalcao();
            }, 150);
        }
        return response;
    };
})();
