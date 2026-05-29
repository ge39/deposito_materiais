// public/js/pdv/limparPDV.js

// 🎯 DECLARAÇÃO GLOBAL: Garante visibilidade total para modal.js e orcamento.js
window.limparPDV = function() {
    console.group("🎯 MÓDULO GLOBAL: window.limparPDV()");

    // 1️⃣ Zera a memória de escopo global do caixa
    window.carrinho = [];
    window.orcamentoAtualId = null;

    // 2️⃣ Esvazia visualmente a tabela de itens vendidos na direita
    const tbody = document.getElementById('lista-itens') 
            || document.getElementById('lista-produtos') 
            || document.getElementById('lista-itens-venda') 
            || document.querySelector('#tabelaItensPDV tbody');
    if (tbody) {
        tbody.innerHTML = '';
    }
    
    // Zera o display de preço principal/totalizador
    const totalGeralEl = document.getElementById('total_geral') 
                    || document.getElementById('totalGeral') 
                    || document.getElementById('inputTotalGeral');
    if (totalGeralEl) {
        if (totalGeralEl.tagName === 'INPUT') {
            totalGeralEl.value = 'R$ 0,00';
        } else {
            totalGeralEl.textContent = 'R$ 0,00';
        }
    }

    // Limpa e libera as caixas de texto do modal de pagamentos
    document.querySelectorAll('.input-pagamento').forEach(input => {
        input.value = '';
        input.disabled = false;
    });

    // Atualiza os resumos de valores na tela (se a função existir globalmente)
    if (typeof atualizarResumo === 'function') {
        atualizarResumo();
    }

    // Limpa a memória visual dos inputs do último produto pesquisado/bipado
    const camposProduto = ['descricao', 'codigo_barras', 'preco_venda', 'quantidade', 'qtd_disponivel', 'total_geral', 'unidade'];
    camposProduto.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
            campo.value = '';
        }
    });

    // =======================================================================
    // 🎯 RESET SEGURO DA IMAGEM DO PRODUTO (ID REAL COLETADO DO SEU HTML)
    // =======================================================================
    try {
        const imgProduto = document.getElementById('produto-imagem');
        if (imgProduto) {
            imgProduto.src = ''; // Retorna ao estado original limpo do HTML da Blade sem quebrar o JS
        }
    } catch (errorImg) {
        console.warn("Aviso: Falha ao limpar o elemento de imagem, avançando...", errorImg);
    }

    // =======================================================================
    // ⚡ INJEÇÃO CIRÚRGICA DO CLIENTE VENDA BALCÃO (SELETORES DO SEU HTML)
    // =======================================================================
    if (window.CLIENTE_BALCAO) {
        
        // Captura os inputs exatamente pelas IDs e Names do seu HTML
        const inputId       = document.getElementById('cliente_id');
        const inputNome     = document.querySelector('input[name="nome"]');
        const inputPessoa   = document.querySelector('input[name="persona"]') || document.querySelector('input[name="pessoa"]');
        const inputTelefone = document.querySelector('input[name="telefone"]');
        const inputEndereco = document.getElementById('endereco');

        // Alimenta fisicamente os values na interface sobrepondo o cliente antigo
        if (inputId) {
            inputId.value = window.CLIENTE_BALCAO.id; // ID: 6
        }
        if (inputNome) {
            inputNome.value = window.CLIENTE_BALCAO.nome; // "VENDA BALCAO"
        }
        if (inputPessoa) {
            inputPessoa.value = window.CLIENTE_BALCAO.tipo; // "fisica"
        }
        if (inputTelefone) {
            inputTelefone.value = window.CLIENTE_BALCAO.telefone; // "11111111111"
        }
        if (inputEndereco) {
            inputEndereco.value = window.CLIENTE_BALCAO.endereco; // "Retira Loja"
        }

        console.log(`✅ Sucesso: Frente de caixa restaurada para ${window.CLIENTE_BALCAO.nome}`);
    } else {
        console.warn("⚠️ Alerta: window.CLIENTE_BALCAO não localizado na memória do navegador.");
    }
    // =======================================================================

    // Oculta o modal de fechamento/pagamento do Bootstrap de forma segura
    const modalEl = document.getElementById('modalFinalizar') 
                || document.getElementById('modalPagamento');
    if (modalEl && typeof bootstrap !== 'undefined') {
        try {
            const modal = bootstrap.Modal.getInstance(modalEl) 
                    || bootstrap.Modal.getOrCreateInstance(modalEl);
            modal?.hide();
        } catch (e) {
            console.warn("Aviso ao fechar modal:", e);
        }
    }

    // Devolve imediatamente o foco do teclado para o input amarelo de Código de Barras
    setTimeout(() => {
        const campoBarras = document.getElementById('codigo_barras');
        if (campoBarras) {
            campoBarras.focus();
        }
        console.groupEnd();
    }, 150);
};
