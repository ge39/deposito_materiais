window.orcamentoAtivo = false;

window.podeEditarLinha = function (tr) {
    if (!orcamentoAtivo) return true;
    return tr.dataset.origem !== 'orcamento';
}
