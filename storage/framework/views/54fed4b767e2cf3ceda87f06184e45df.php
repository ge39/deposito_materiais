<div class="modal fade " id="modalOrcamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Buscar Orçamento (F4)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Código do Orçamento</label>
                <input type="text"
                       id="inputCodigoOrcamento" value="2025121632"
                       class="form-control form-control-lg"
                       autocomplete="off">
            </div>

            <div class="modal-footer">
                <button class="g-2 m-1.5 btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button class="g-2 m-1.5 btn btn-primary"
                        onclick="confirmarOrcamentoFront()">
                    Confirmar
                </button>
            </div>

        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_orcamento.blade.php ENDPATH**/ ?>