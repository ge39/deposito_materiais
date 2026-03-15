<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-xl">

        <div class="modal-content" style="max-height:400px; overflow-y:auto;">

            <div class="modal-header">
                <h5 class="modal-title">Selecionar Cliente (F2)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- BUSCA -->
                <input type="text"
                       id="buscaClientePDV"
                       class="form-control mb-2"
                       autocomplete="off"
                       placeholder="Digite nome, CPF ou telefone">

                <!-- CABEÇALHO -->
                <div class="cliente-header">
                    <div>ID</div>
                    <div>Nome</div>
                    <div>Pessoa</div>
                    <div>CPF/CNPJ</div>
                    <div>Telefone</div>
                    <div>Endereço</div>
                    <div>Nº</div>
                    <div>CEP</div>
                    <div>Bairro</div>
                    <div>Cidade</div>
                    <div>UF</div>
                    <div>Ação</div>
                </div>

                <!-- RESULTADO -->
                <div id="resultadoClientePDV" class="cliente-body">
                    <div class="cliente-empty">
                        Digite para buscar clientes
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>

.cliente-header,
.cliente-row{
    display:grid;
    grid-template-columns:
        60px
        1.5fr
        90px
        150px
        130px
        1.5fr
        70px
        120px
        1fr
        1fr
        60px
        90px;
    align-items:center;
}

.cliente-header{
    background:#212529;
    color:white;
    font-weight:bold;
    padding:6px;
    font-size:13px;
}

.cliente-row{
    border-bottom:1px solid #ddd;
    padding:4px 6px;
    font-size:13px;
}

.cliente-row:hover{
    background:#f2f2f2;
}

.cliente-empty{
    text-align:center;
    padding:20px;
    color:#777;
}

</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const modalCliente = document.getElementById('modalCliente');
    const inputBusca   = document.getElementById('buscaClientePDV');
    const resultado    = document.getElementById('resultadoClientePDV');

    let delayBusca;
    let controller;

    modalCliente.addEventListener('shown.bs.modal', function () {

        // NÃO limpar input aqui
        inputBusca.focus();

        resultado.innerHTML =
            '<div class="cliente-empty">Digite para buscar clientes</div>';
    });


    inputBusca.addEventListener('input', function(){

        clearTimeout(delayBusca);

        const query = this.value.trim();

        if(query.length < 2){
            resultado.innerHTML = '';
            return;
        }

        delayBusca = setTimeout(() => {

            if(controller){
                controller.abort();
            }

            controller = new AbortController();

            fetch(`{{ route('pdv.buscarCliente') }}?query=` + encodeURIComponent(query),{
                signal: controller.signal
            })
            .then(res => res.json())
            .then(clientes => {

                if(!clientes.length){
                    resultado.innerHTML =
                        '<div class="cliente-empty">Nenhum cliente encontrado</div>';
                    return;
                }

                let html = '';

                clientes.forEach(c => {

                    html += `
                    <div class="cliente-row">

                        <div>${c.id}</div>
                        <div>${c.nome}</div>
                        <div>${c.pessoa}</div>
                        <div>${c.cpf_cnpj ?? ''}</div>
                        <div>${c.telefone ?? ''}</div>
                        <div>${c.endereco ?? ''}</div>
                        <div>${c.numero ?? ''}</div>
                        <div>${c.cep ?? ''}</div>
                        <div>${c.bairro ?? ''}</div>
                        <div>${c.cidade ?? ''}</div>
                        <div>${c.estado ?? ''}</div>

                        <div>
                            <button class="btn btn-sm btn-primary"
                                onclick="selecionarClientePDV(
                                    ${c.id},
                                    '${c.nome}',
                                    '${c.pessoa}',
                                    '${c.telefone ?? ''}',
                                    '${c.endereco ?? ''}',
                                    '${c.numero ?? ''}',
                                    '${c.cep ?? ''}',
                                    '${c.bairro ?? ''}',
                                    '${c.cidade ?? ''}',
                                    '${c.estado ?? ''}'
                                )">
                                Selecionar
                            </button>
                        </div>

                    </div>`;
                });

                resultado.innerHTML = html;

            })
            .catch(err=>{
                if(err.name !== "AbortError"){
                    console.error(err);
                }
            });

        },300);

    });

});

</script>