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
                                <th style="width: 50px;">Pessoa</th>
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

    fetch(`{{ route('pdv.buscarCliente') }}?query=` + query)
        .then(res => res.json())
        .then(data => {

            let html = ``;

            data.forEach(c => {
                html += `
                    <tr class="pointer">
                        <td style="font-size: 12px; font-weight: bold;">${c.id}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.nome}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.cpf_cnpj ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.telefone ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.endereco ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.numero ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.cep ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.bairro ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.cidade ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">${c.estado ?? ''}</td>
                        <td style="font-size: 12px; font-weight: bold;">
                            <button class="btn btn-sm btn-primary"
                                    onclick="selecionarClientePDV(${c.id}, '${c.nome}', '${c.tipo}', '${c.telefone}', '${c.endereco}', '${c.numero ?? ''}', '${c.cep ?? ''}', '${c.bairro ?? ''}', '${c.cidade ?? ''}', '${c.estado ?? ''}')">
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

<script>    
    function selecionarClientePDV(  
    id, nome, pessoa, telefone = '', endereco = '', numero = '',
    cep = '', bairro = '', cidade = '', estado = ''
    ){
    // Preenche o campo HIDDEN
    document.querySelector('input[name="cliente_id"]').value = id;


    // Preenche os campos visíveis
    document.querySelector('input[name="nome"]').value = nome;
    document.querySelector('input[name="pessoa"]').value = pessoa;
    document.querySelector('input[name="telefone"]').value = telefone;

    const enderecoCompleto =
        `${endereco} ${numero} - ${bairro}, ${cidade} - ${estado}, CEP: ${cep}`;
    document.querySelector('input[name="endereco"]').value = enderecoCompleto;

    // Fecha o modal
    const modalElement = document.getElementById('modalCliente');
    const modal = bootstrap.Modal.getInstance(modalElement);
    modal.hide();
    }
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

