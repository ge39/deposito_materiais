// public/js/pdv/ui.js

/**
 * Força a restauração dos inputs do cliente para o padrão VENDA BALCÃO
 */
window.restaurarClientePadraoBalcao = function() {
    if (!window.CLIENTE_BALCAO) {
        console.warn("Aviso: window.CLIENTE_BALCAO não está definido no Blade.");
        return;
    }

    // 🎯 Captura os inputs exatamente pelos atributos [name="..."] que o orcamento.js usa para preencher
    const inputId = document.querySelector('[name="cliente_id"]');
    const inputNome = document.querySelector('[name="cliente_nome"]') || document.querySelector('[name="nome"]');
    const inputPessoa = document.querySelector('[name="pessoa"]');
    const inputTelefone = document.querySelector('[name="telefone"]');
    const inputEndereco = document.querySelector('[name="endereco"]');

    // 🔄 Injeta os dados do Venda Balcão salvos na memória global
    if (inputId) inputId.value = window.CLIENTE_BALCAO.id ?? '';
    if (inputNome) inputNome.value = window.CLIENTE_BALCAO.nome ?? 'VENDA BALCAO';
    if (inputPessoa) inputPessoa.value = window.CLIENTE_BALCAO.tipo === 'fisica' ? 'Física' : 'Jurídica';
    
    // Limpa os dados de contato do cliente do orçamento antigo
    if (inputTelefone) inputTelefone.value = '';
    if (inputEndereco) inputEndereco.value = '';

    console.log("🔄 Campos do cliente resetados para VENDA BALCÃO via ui.js");
};
