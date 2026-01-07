<div class="modal fade" id="modalCliente" tabindex="-1" >
    <div class="modal-dialog modal-bg"> <!-- corrigido para XL -->
        <div class="modal-content " style="width: 1400px; max-height: 400px;margin-left:-150px; overflow-y: auto;">

            <div class="modal-header" >
                <h5 class="modal-title">Selecionar Cliente (F2)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" >

                <input type="text" id="buscaClientePDV" class="form-control mb-2"
                    placeholder="Digite nome, CPF ou telefone">

                <div class="table-responsive">
                    <table class="table table-hover table-bordered" >
                        <thead class="table-dark" style="width: 1000px; max-height: 400px; overflow-y: auto;">
                            <tr>
                                <th style="width: 30px;">ID</th>
                                <th style="width: 150px;">Nome</th>
                                <th style="width: 100px;">Pessoa</th>
                                <th style="width: 130px;">CPF/CNPJ</th>
                                <th style="width: 150px;">Telefone</th>
                                <th style="width: 100px;">Endereço</th>
                                <th style="width: 100px;">N.º</th>
                                <th style="width: 100px;">Cep</th>
                                <th style="width: 150px;">Bairro</th>
                                <th style="width: 150px;">Cidade</th>
                                <th style="width: 50px;">Estado</th>
                                <th style="width: 100px;">Ação</th>
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
                    <tr class="pointer" style="width: 1000px; max-height: 400px; overflow-y: auto;">
                        <td style="font-size: 18px;">${c.id}</td>
                        <td style="font-size: 18px;">${c.nome}</td>
                        <td style="font-size: 18px;">${c.pessoa}</td>
                        <td style="font-size: 18px;">${c.cpf_cnpj ?? ''}</td>
                        <td style="font-size: 18px;">${c.telefone ?? ''}</td>
                        <td style="font-size: 18px;">${c.endereco ?? ''}</td>
                        <td style="font-size: 18px;">${c.numero ?? ''}</td>
                        <td style="font-size: 18px;">${c.cep ?? ''}</td>
                        <td style="font-size: 18px;">${c.bairro ?? ''}</td>
                        <td style="font-size: 18px;">${c.cidade ?? ''}</td>
                        <td style="font-size: 18px;">${c.estado ?? ''}</td>
                        <td style="font-size: 18px;">
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
    document.addEventListener('DOMContentLoaded', function () {

        var modalCliente = document.getElementById('modalCliente');

        // foco no input (já existente)
        modalCliente.addEventListener('shown.bs.modal', function () {
            document.getElementById('buscaClientePDV').focus();
        });

        // LIMPEZA DO BACKDROP AO FECHAR
        modalCliente.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop')
                .forEach(el => el.remove());
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

    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) {
        modal = new bootstrap.Modal(modalElement);
    }

    modal.hide();

    
    }
</script>


<style>
    #modalCliente .table {
        font-size: 14px !important;
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

