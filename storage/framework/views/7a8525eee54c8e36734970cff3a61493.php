
<!-- MODAL CLIENTE PDV -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content modal-cliente-pdv">

            <div class="modal-header">
                <h5 class="modal-title">Selecionar Cliente (F2)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text"
                       id="buscaClientePDV"
                       class="form-control mb-2"
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

                </div>

                <!-- RESULTADO -->
                <div id="resultadoClientePDV"></div>

            </div>
        </div>
    </div>
</div>

<style>

    .modal-cliente-pdv{
    background:#212529;
    max-height:32vh;
    color:white;
    padding:6px;
     border-radius: 30px;
    }

    .cliente-header,
    .cliente-row{
        display:flex;
        align-items:center;
        font-size:14px;
    }

    .cliente-header{
        background:#212529;
        color:white;
        font-weight:bold;
        padding:6px;
        
    }

    .cliente-row{
        padding:4px;
        border-bottom:1px solid #ddd;
    }

    .cliente-row:hover{
        background:#f2f2f2;
    }

    .cliente-row.active{
        background:#0d6efd;
        color:white;
    }

    .cliente-row div,
    .cliente-header div{
        padding:2px 4px;
        
    }

    .cliente-row div:nth-child(1),
    .cliente-header div:nth-child(1){width:40px}

    .cliente-row div:nth-child(2),
    .cliente-header div:nth-child(2){width:150px}

    .cliente-row div:nth-child(3),
    .cliente-header div:nth-child(3){width:90px}

    .cliente-row div:nth-child(4),
    .cliente-header div:nth-child(4){width:130px}

    .cliente-row div:nth-child(5),
    .cliente-header div:nth-child(5){width:150px}

    .cliente-row div:nth-child(6),
    .cliente-header div:nth-child(6){width:100px}

    .cliente-row div:nth-child(7),
    .cliente-header div:nth-child(7){width:60px}

    .cliente-row div:nth-child(8),
    .cliente-header div:nth-child(8){width:100px}

    .cliente-row div:nth-child(9),
    .cliente-header div:nth-child(9){width:140px}

    .cliente-row div:nth-child(10),
    .cliente-header div:nth-child(10){width:140px}

    .cliente-row div:nth-child(11),
    .cliente-header div:nth-child(11){width:50px}

    .cliente-row div:nth-child(12),
    .cliente-header div:nth-child(12){width:90px}

    .cliente-row button{
        font-size:10px;
        padding:2px 6px;
    }

</style>

 <script>

let clienteIndex = -1;
let clientes = [];

let debounceTimer;
let controller;

// ===============================
// MODAL + FOCO CORRETO
// ===============================
document.addEventListener('DOMContentLoaded', function(){

    const modalCliente = document.getElementById('modalCliente');
    const inputBusca = document.getElementById('buscaClientePDV');

    modalCliente.addEventListener('shown.bs.modal', function(){
        setTimeout(() => {
            inputBusca.focus();
            inputBusca.select();
        }, 200); // 🔥 essencial
    });

    modalCliente.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
        clienteIndex = -1;
    });

});

// ===============================
// F2 ABRE MODAL
// ===============================
document.addEventListener('keydown', function(e){

    if(e.key === "F2"){
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('modalCliente'));
        modal.show();
    }

});

// ===============================
// BUSCA CLIENTE (OTIMIZADA)
// ===============================
const inputBusca = document.getElementById('buscaClientePDV');
const resultadoClientePDV = document.getElementById('resultadoClientePDV');

inputBusca.addEventListener('keyup', function(e){

    if(e.key === "ArrowDown" || e.key === "ArrowUp" || e.key === "Enter")
        return;

    const query = this.value.trim();

    if(query.length < 2){
        resultadoClientePDV.innerHTML = '';
        clientes = [];
        return;
    }

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        buscarClientes(query);
    }, 300);

});

async function buscarClientes(query){

    // cancela requisição anterior
    if(controller) controller.abort();
    controller = new AbortController();

    resultadoClientePDV.innerHTML = `
        <div class="text-center py-2 text-muted">
            Buscando clientes...
        </div>
    `;

    try{

        const res = await fetch(`<?php echo e(route('pdv.buscarCliente')); ?>?query=` + encodeURIComponent(query), {
            signal: controller.signal
        });

        const data = await res.json();

        clientes = Array.isArray(data) ? data : (data.clientes ?? data);
        clienteIndex = -1;

        if(!clientes.length){
            resultadoClientePDV.innerHTML = `
                <div class="text-center py-2 text-muted">
                    Nenhum cliente encontrado
                </div>
            `;
            return;
        }

        let html = '';

        clientes.forEach((c,i)=>{

            html += `
            <div class="cliente-row" data-index="${i}">

                <div>${c.id}</div>
                <div>${c.nome}</div>
                <div>${c.tipo}</div>
                <div>${c.cpf_cnpj ?? ''}</div>
                <div>${c.telefone ?? ''}</div>
                <div>${c.endereco ?? ''}</div>
                <div>${c.numero ?? ''}</div>
                <div>${c.cep ?? ''}</div>
                <div>${c.bairro ?? ''}</div>
                <div>${c.cidade ?? ''}</div>
                <div>${c.estado ?? ''}</div>

            </div>`;
        });

        resultadoClientePDV.innerHTML = html;

    }catch(e){

        if(e.name === 'AbortError') return;

        console.error(e);

        resultadoClientePDV.innerHTML = `
            <div class="text-center text-danger py-2">
                Erro ao buscar clientes
            </div>
        `;
    }

}

// ===============================
// NAVEGAÇÃO TECLADO
// ===============================
document.addEventListener('keydown', function(e){

    const rows = document.querySelectorAll(".cliente-row");

    if(rows.length === 0) return;

    if(e.key === "ArrowDown"){
        e.preventDefault();
        clienteIndex++;
        if(clienteIndex >= rows.length) clienteIndex = rows.length - 1;
    }

    if(e.key === "ArrowUp"){
        e.preventDefault();
        clienteIndex--;
        if(clienteIndex < 0) clienteIndex = 0;
    }

    if(e.key === "Enter"){
        e.preventDefault();

        if(clienteIndex >= 0){
            const c = clientes[clienteIndex];

            selecionarClientePDV(
                c.id,
                c.nome,
                c.tipo,
                c.telefone,
                c.endereco,
                c.numero,
                c.cep,
                c.bairro,
                c.cidade,
                c.estado
            );
        }
    }

    rows.forEach(r => r.classList.remove("active"));

    if(rows[clienteIndex])
        rows[clienteIndex].classList.add("active");

});

// ===============================
// SELECIONAR CLIENTE
// ===============================
function selecionarClientePDV(
    id,nome,tipo,telefone='',endereco='',numero='',
    cep='',bairro='',cidade='',estado=''
){

    document.querySelector('input[name="cliente_id"]').value = id;

    document.querySelector('input[name="nome"]').value = nome;
    document.querySelector('input[name="pessoa"]').value = tipo;
    document.querySelector('input[name="telefone"]').value = telefone;

    const enderecoCompleto =
        `${endereco} ${numero} - ${bairro}, ${cidade} - ${estado}, CEP: ${cep}`;

    document.querySelector('input[name="endereco"]').value = enderecoCompleto;

    const modalElement = document.getElementById('modalCliente');
    let modal = bootstrap.Modal.getInstance(modalElement);

    if(!modal)
        modal = new bootstrap.Modal(modalElement);

    modal.hide();

}

</script><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/pdv/modals/modal_cliente_pdv.blade.php ENDPATH**/ ?>