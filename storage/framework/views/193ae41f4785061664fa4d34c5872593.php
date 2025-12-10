<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- corrigido para XL -->
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Selecionar Cliente (F2)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text" id="buscaClientePDV" class="form-control mb-2"
                    placeholder="Digite nome, CPF ou telefone">

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px;">ID</th>
                                <th style="width: 50px;">Nome</th>
                                <th style="width: 70px;">CPF/CNPJ</th>
                                <th style="width: 50px;">Telefone</th>
                                <th style="width: 100px;">Endereço</th>
                                <th style="width: 25px;">N.º</th>
                                <th style="width: 35px;">Cep</th>
                                <th style="width: 100px;">Bairro</th>
                                <th style="width: 50px;">Cidade</th>
                                <th style="width: 50px;">Estado</th>
                                <th style="width: 80px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoClientePDV">
                            <!-- preenchido via JS -->
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    var modalCliente = document.getElementById('modalCliente');

    modalCliente.addEventListener('shown.bs.modal', function () {
        document.getElementById('buscaClientePDV').focus();
    });
});

    document.getElementById('buscaClientePDV').addEventListener('keyup', function () {

    let query = this.value;

    if (query.length < 2) {
        document.getElementById('resultadoClientePDV').innerHTML = '';
        return;
    }

    fetch(`<?php echo e(route('pdv.buscarCliente')); ?>?query=` + query)
        .then(res => res.json())
        .then(data => {

            let html = ``;

            data.forEach(c => {
                html += `
                    <tr class="pointer">
                        <td>${c.id}</td>
                        <td>${c.nome}</td>
                        <td>${c.cpf_cnpj ?? ''}</td>
                        <td>${c.telefone ?? ''}</td>
                        <td>${c.endereco ?? ''}</td>
                        <td>${c.numero ?? ''}</td>
                        <td>${c.cep ?? ''}</td>
                        <td>${c.bairro ?? ''}</td>
                        <td>${c.cidade ?? ''}</td>
                        <td>${c.estado ?? ''}</td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                    onclick="selecionarClientePDV(${c.id}, '${c.nome}')">
                                Selecionar
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('resultadoClientePDV').innerHTML = html;

        });

});
</script>

<style>
    #modalCliente .table {
        font-size: 12px !important;
    }

    #modalCliente .table th,
    #modalCliente .table td {
        padding: 4px 6px !important; /* compactar células */
    }

    #modalCliente .btn-sm {
        font-size: 10px !important;
        padding: 2px 6px !important;
    }
</style>

<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_cliente_pdv.blade.php ENDPATH**/ ?>