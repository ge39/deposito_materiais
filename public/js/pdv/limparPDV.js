// public/js/pdv/limparPDV.js

window.limparPDV = function() {
    // 🔍 1️⃣ INÍCIO DO GRUPO DE RASTREAMENTO NO CONSOLE
    console.group("🚀 DIAGNÓSTICO DE LIMPEZA: window.limparPDV()");
    
    // Captura o estado das variáveis globais antes de alterá-las
    console.log("📍 Memória Global - CLIENTE_BALCAO:", window.CLIENTE_BALCAO);
    console.log("📍 Memória Global - orcamentoAtualId ATUAL:", window.orcamentoAtualId);

    const inputsPagamento = document.querySelectorAll('.input-pagamento');
    const totalGeralEl = document.getElementById('total_geral');
    
    const modalEl = document.getElementById('modalPagamento');
    const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
    
    // Zera a memória de escopo global do caixa
    window.carrinho = [];
    window.orcamentoAtualId = null;

    // Limpa as formas de pagamento
    inputsPagamento.forEach(input => {
        input.value = '';
        input.disabled = false;
    });

    if (typeof atualizarResumo === 'function') {
        atualizarResumo();
    }

    // Campos nativos do produto selecionado
    const campos = [
        'descricao',
        'codigo_barras',
        'preco_venda',
        'quantidade',
        'qtd_disponivel',
        'total_geral',
        'unidade'
    ];

    campos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
            campo.value = '';
        }
    });

    // Mapeia os elementos do cabeçalho superior
    const inputId    = document.getElementById('cliente-id') || document.getElementById('input-cliente-id') || document.querySelector('[name="cliente_id"]');
    const inputNome  = document.getElementById('cliente-nome');
    const inputTipo  = document.getElementById('cliente-tipo');
    const inputFone  = document.getElementById('cliente-telefone');
    const inputEnd   = document.getElementById('cliente-endereco');

    // 📋 2️⃣ TABELA DE DIAGNÓSTICO DOS ELEMENTOS DO CLIENTE ANTES DO RESET
    console.log("🔎 Estado dos elementos capturados no DOM antes do reset:");
    console.table([
        { "Elemento": "ID Oculto (input-id)", "Capturado": inputId ? "Sim" : "Não", "Valor Lido": inputId ? inputId.value : "N/A" },
        { "Elemento": "Nome (cliente-nome)", "Capturado": inputNome ? "Sim" : "Não", "Valor Lido": inputNome ? inputNome.value : "N/A" },
        { "Elemento": "Tipo (cliente-tipo)", "Capturado": inputTipo ? "Sim" : "Não", "Valor Lido": inputTipo ? inputTipo.value : "N/A" },
        { "Elemento": "Fone (cliente-telefone)", "Capturado": inputFone ? "Sim" : "Não", "Valor Lido": inputFone ? inputFone.value : "N/A" },
        { "Elemento": "Endereço (cliente-endereco)", "Capturado": inputEnd ? "Sim" : "Não", "Valor Lido": inputEnd ? inputEnd.value : "N/A" }
    ]);

    // Executa a restauração para o padrão "VENDA BALCÃO"
    console.log("⚡ Executando alteração física dos valores...");
    if (inputId && window.CLIENTE_BALCAO) {
        inputId.value = window.CLIENTE_BALCAO.id;
    }
    if (inputNome) {
        inputNome.value = window.CLIENTE_BALCAO ? window.CLIENTE_BALCAO.nome : 'VENDA BALCAO';
    }
    if (inputTipo) inputTipo.value = 'Física';
    if (inputFone) inputFone.value = '';
    if (inputEnd)  inputEnd.value  = '';

    if (totalGeralEl) {
        totalGeralEl.textContent = 'R$ 0,00';
    }

    modal?.hide();

    setTimeout(() => {
        document.getElementById('codigo_barras')?.focus();
        console.log("🎯 Foco devolvido para o input de código de barras.");
        console.groupEnd(); // FIM DO BLOCO DE LOGS
    }, 150);
};
