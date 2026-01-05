// resources/js/pdv/modal_finalizar.js

// Função que abre o modal de finalizar venda
function abrirModalFinalizar() {
    // Pega o label que mostra o total na tela do PDV
    const labelTotal = document.getElementById('totalGeral');
    let total = 0;

    if(labelTotal){
        // Remove tudo que não seja número ou vírgula
        const texto = labelTotal.textContent.replace(/[^\d,]/g,'');
        // Converte para float
        total = parseFloat(texto.replace(',', '.')) || 0;
    }

    console.log('💰 Total convertido para JS no modal_finalizar:', total);

    // Atualiza o input escondido do modal
    const inputTotalGeral = document.getElementById('inputTotalGeral');
    if(inputTotalGeral){
        // Converte para string com vírgula
        inputTotalGeral.value = total.toFixed(2).replace('.', ',');
    }

    // Abre o modal usando Bootstrap 5
    const modalEl = document.getElementById('modalFinalizarVenda'); // certifique-se que esse é o id do modal
    if(modalEl && typeof bootstrap !== 'undefined'){
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        console.warn('⚠️ Modal de finalizar venda não encontrado ou Bootstrap não carregado.');
    }
}

// Event listener para a tecla F6
document.addEventListener('keydown', function(e){
    if(e.code === 'F6'){
        e.preventDefault();
        abrirModalFinalizar();
        console.log('F6 pressionado - modal de finalizar chamado.');
    }
    
});
// window.abrirModalFinalizar = abrirModalFinalizar;

// Exporta a função caso queira chamar de outro arquivo
export { abrirModalFinalizar };
