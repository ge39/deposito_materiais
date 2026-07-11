<!-- REMOVIDO o aria-hidden="true" estático e o autofocus interno do input -->
<div class="modal fade" id="modalOrcamento" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modalOrcamentoTitle">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalOrcamentoTitle">Buscar Orçamento (F4)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <label class="form-label" for="inputCodigoOrcamento">Código do Orçamento</label>
                <input type="text"
                       id="inputCodigoOrcamento" value="2026070116425332"
                       class="form-control form-control-lg"
                       autocomplete="off">
            </div>

            <div class="modal-footer">
                <button type="button" class="g-2 m-1.5 btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="g-2 m-1.5 btn btn-primary"
                        onclick="confirmarOrcamentoFront()">
                    Confirmar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    const meuModalOrcamento = document.getElementById('modalOrcamento');

    // 1. GERENCIAMENTO DE ABERTURA: Foca no input somente após o modal terminar a animação de abertura
    meuModalOrcamento.addEventListener('shown.bs.modal', function () {
        const input = document.getElementById('inputCodigoOrcamento');
        input.focus();
        input.select(); // Opcional: Seleciona o texto interno para facilitar a digitação do operador
    });

    // 2. SEGURANÇA ANTITRAVAMENTO: Remove o foco interno antes do modal sumir da tela (evita o erro do Chrome)
    meuModalOrcamento.addEventListener('hide.bs.modal', function () {
        if (document.activeElement) {
            document.activeElement.blur(); // Remove o foco do input para liberar a tela de trás
        }
    });

    // 3. LIMPEZA PADRÃO: Devolve o foco para o campo principal do PDV ao fechar
    meuModalOrcamento.addEventListener('hidden.bs.modal', function () {
        // Substitua pelo ID do seu campo de bipe de produtos (ex: 'inputCodigoBarras')
        const campoPrincipalPDV = document.getElementById('inputCodigoBarras'); 
        if (campoPrincipalPDV) {
            campoPrincipalPDV.focus();
        }
    });
</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_orcamento.blade.php ENDPATH**/ ?>