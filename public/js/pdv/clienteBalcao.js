/**
 * Restaura o PDV para o estado padrão de Venda Balcão na interface gráfica e na memória
 */
window.vendaBalcao = function() {
    console.log('Acionando restauração visual do PDV para Venda Balcão...');

    // 1. Limpa IDs de orçamentos antigos da memória global do caixa
    window.orcamentoAtualId = null;

    // 2. Limpa os itens do carrinho e o total acumulado na tela
    window.carrinho = [];
    const tbody = document.getElementById('lista-itens');
    if (tbody) {
        tbody.innerHTML = ''; 
    }
    
    const totalVendaEl = document.getElementById('totalGeral');
    if (totalVendaEl) {
        totalVendaEl.textContent = 'R$ 0,00'; 
    }

    // 3. Busca a div oculta com o JSON do cliente padrão enviado pelo Laravel
    const elCliente = document.getElementById('dados-cliente-balcao');
    if (!elCliente) {
        console.warn('Tag HTML #dados-cliente-balcao não foi localizada na View.');
        return;
    }

    try {
        const clientePadrao = JSON.parse(elCliente.getAttribute('data-cliente'));

        // 4. 🔥 ATUALIZAÇÃO VISUAL BASEADA EXATAMENTE NO SEU HTML
        
        // Campo oculto ID do Cliente (id="cliente_id")
        const inputId = document.getElementById('cliente_id');
        if (inputId) {
            inputId.value = clientePadrao.id;
        }
        
        // Campo de Texto com o Nome do Cliente (name="nome")
        const inputNome = document.querySelector('input[name="nome"]');
        if (inputNome) {
            inputNome.value = clientePadrao.nome;
        }
        
        // Campo do Tipo de Pessoa (name="pessoa")
        const inputPessoa = document.querySelector('input[name="pessoa"]');
        if (inputPessoa) {
            inputPessoa.value = clientePadrao.tipo || '';
        }

        // Campo do Contato Local (name="telefone")
        const inputTelefone = document.querySelector('input[name="telefone"]');
        if (inputTelefone) {
            inputTelefone.value = clientePadrao.telefone || '';
        }

        // Campo do Endereço para Entrega (id="endereco" ou name="endereco")
        const inputEndereco = document.getElementById('endereco') || document.querySelector('input[name="endereco"]');
        if (inputEndereco) {
            inputEndereco.value = clientePadrao.endereco || '';
        }

        console.log('Restauração visual concluída para:', clientePadrao.nome);

    } catch (error) {
        console.error('Erro ao processar a atualização visual da venda balcão:', error);
    }
};
