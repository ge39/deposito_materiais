<style>
    /* 1. Ajuste do Container do Modal para ocupar a largura correta */
    #modalCliente .modal-dialog {
        max-width: 96% !important; /* Mesma largura estendida do modal de produtos */
        width: 96%;
        margin: 1.75rem auto;
        margin-top: -28vh; /* Ajuste para centralizar verticalmente considerando a altura do header */
    }

    /* 2. Grid de 11 Colunas Calibrado com base na foto real */
    .cliente-header,
    .cliente-row {
        display: grid;
        /* Proporções exatas baseadas nos dados reais mostrados na imagem */
        grid-template-columns: 40px 2.2fr 0.8fr 1.3fr 1.3fr 2fr 50px 100px 1.3fr 1.3fr 40px;
        align-items: center;
        font-size: 14px;
        width: 100%;
    }

    /* 3. Cabeçalho Escuro Idêntico ao de Produtos */
    .cliente-header {
        background: #212529 !important;
        color: #ffffff !important;
        font-weight: bold;
        border-radius: 6px;
        margin-top: 10px;
        padding: 4px 0;
    }

    /* 4. Células de Dados e Alinhamento */
    .cliente-row div,
    .cliente-header div {
        padding: 8px 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    /* Alinhamentos específicos para colunas curtas */
    .cliente-row div:nth-child(1), .cliente-header div:nth-child(1),  /* ID */
    .cliente-row div:nth-child(7), .cliente-header div:nth-child(7),  /* Nº */
    .cliente-row div:nth-child(11), .cliente-header div:nth-child(11) /* UF */ {
        text-align: center;
    }

    /* 5. Linhas de Resultado Transparentes */
    #resultadoClientePDV {
        background: transparent !important;
        margin-top: 5px;
    }

    #resultadoClientePDV .cliente-row {
        background: transparent !important;
        color: #212529 !important;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    /* Hover e Seleção */
    #resultadoClientePDV .cliente-row:hover {
        background: #f8f9fa !important;
    }

    #resultadoClientePDV .cliente-row.active {
        background: #0d6efd !important;
        color: #ffffff !important;
    }

    /* 1. Fixa a posição do diálogo do modal na tela e impede que ele suba ou mude de lugar */
    #modalCliente .modal-dialog {
        max-width: 96% !important;
        width: 96%;
        margin: 1.75rem auto;
        display: block; /* Remove o comportamento de centralização vertical flex do Bootstrap */
    }

    /* 2. Força o corpo do modal a ter uma altura máxima e adiciona scroll apenas na lista */
    #modalCliente .modal-body {
        max-height: 65vh; /* Ajuste esta porcentagem se quiser o modal mais alto ou mais baixo */
        overflow-y: auto;  /* Faz aparecer a barra de rolagem vertical aqui quando a lista crescer */
        padding-top: 5px;
    }

    /* 3. Mantém o cabeçalho fixo no topo enquanto você rola a lista para baixo */
    .cliente-header {
        position: sticky;
        top: 0;
        z-index: 10; /* Garante que os nomes passem por baixo do cabeçalho preto */
        background: #212529 !important;
        color: #ffffff !important;
        font-weight: bold;
        border-radius: 6px;
        margin-top: 10px;
        padding: 4px 0;
    }

    /* 4. Grid de 11 Colunas (Mantido o alinhamento que funcionou) */
    .cliente-header,
    .cliente-row {
        display: grid;
        grid-template-columns: 40px 2.2fr 0.8fr 1.3fr 1.3fr 2fr 50px 100px 1.3fr 1.3fr 40px;
        align-items: center;
        font-size: 14px;
        width: 100%;
    }

    /* Restante do CSS de paddings, hover e active (pode manter igual) */
    .cliente-row div, .cliente-header div {
        padding: 8px 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }
    .cliente-row div:nth-child(1), .cliente-header div:nth-child(1),
    .cliente-row div:nth-child(7), .cliente-header div:nth-child(7),
    .cliente-row div:nth-child(11), .cliente-header div:nth-child(11) {
        text-align: center;
    }
    #resultadoClientePDV { background: transparent !important; margin-top: 5px; }
    #resultadoClientePDV .cliente-row { background: transparent !important; color: #212529 !important; border-bottom: 1px solid #dee2e6; cursor: pointer; transition: background 0.15s ease; }
    #resultadoClientePDV .cliente-row:hover { background: #f8f9fa !important; }
    #resultadoClientePDV .cliente-row.active { background: #0d6efd !important; color: #ffffff !important; }

</style>

<!-- MODAL CLIENTE PDV -->
<div class="modal fade" id="modalCliente" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog"> 
        <div class="modal-content shadow-sm" style="border-radius: 12px; padding: 5px;"> 

            <!-- Header Superior -->
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Selecionar Cliente (F2)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body pt-2">

                <!-- Input de Busca -->
                <input type="text"
                       id="buscaClientePDV"
                       class="form-control mb-3"
                       style="border-radius: 8px; padding: 10px;"
                       placeholder="Digite nome, CPF ou telefone e pressione Enter...">

                <!-- Grid protegido contra quebras -->
                <div style="overflow-x: auto; width: 100%;">

                    <!-- CABEÇALHO ESCURO -->
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
</div>


<script>
    let clienteIndex = -1;
    let clientes = [];

    let debounceTimer;
    let controller;

    // guarda referência do handler pra evitar duplicação
    window._clienteKeydown = null;

    document.addEventListener('DOMContentLoaded', function(){

        const modalCliente = document.getElementById('modalCliente');
        const inputBusca = document.getElementById('buscaClientePDV');
        const resultadoClientePDV = document.getElementById('resultadoClientePDV');

        // ===============================
        // MODAL FOCO
        // ===============================
        if (modalCliente) {

            modalCliente.addEventListener('shown.bs.modal', function(){
                setTimeout(() => {
                    if (inputBusca) {
                        inputBusca.focus();
                        inputBusca.select();
                    }
                }, 200);
            });

            modalCliente.addEventListener('hidden.bs.modal', function(){
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
                clienteIndex = -1;
            });
        }

        // ===============================
        // BLOQUEIO CARTEIRA
        // ===============================
        if (window.clienteSelecionado && parseFloat(window.clienteSelecionado.saldo) < 0) {
            const input = document.querySelector('[data-forma="carteira"]');
            if (input) {
                input.disabled = true;
                input.placeholder = "Bloqueado (cliente em débito)";
                input.value = '';
            }
        }

        // ===============================
        // BUSCA CLIENTE
        // ===============================
        if (inputBusca) {

            // 🔥 BLOQUEIA ENTER no input
            inputBusca.addEventListener('keydown', function(e){
                if(e.key === "Enter"){
                    e.preventDefault();
                }
            });

            inputBusca.addEventListener('keyup', function(e){

                if(e.key === "ArrowDown" || e.key === "ArrowUp")
                    return;

                const query = this.value.trim();

                if(query.length < 2){
                    if (resultadoClientePDV) resultadoClientePDV.innerHTML = '';
                    clientes = [];
                    return;
                }

                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    buscarClientes(query);
                }, 300);

            });
        }

        // ===============================
        // EVENTO TECLADO (CONTROLADO)
        // ===============================

        // remove anterior se existir
        if (window._clienteKeydown) {
            document.removeEventListener('keydown', window._clienteKeydown);
        }

        window._clienteKeydown = function(e){

            const modalAberto = document.getElementById('modalCliente');

            // 🔥 só funciona com modal aberto
            if (!modalAberto || !modalAberto.classList.contains('show')) return;

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
                        c.id, c.nome, c.tipo, c.telefone,
                        c.endereco, c.numero, c.cep,
                        c.bairro, c.cidade, c.estado
                    );
                }
            }

            rows.forEach(r => r.classList.remove("active"));

            if(rows[clienteIndex])
                rows[clienteIndex].classList.add("active");
        };

        document.addEventListener('keydown', window._clienteKeydown);

    });

    // ===============================
    // F2 ABRE MODAL
    // ===============================
    document.addEventListener('keydown', function(e){

        if(e.key === "F2"){
            e.preventDefault();
            const modalEl = document.getElementById('modalCliente');

            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

    });

    // ===============================
// BUSCAR CLIENTES
// ===============================
async function buscarClientes(query){
    const resultadoClientePDV = document.getElementById('resultadoClientePDV');
    
    // Cancela request anterior para evitar concorrência (Debounce/Abort)
    if(controller) controller.abort();
    controller = new AbortController();

    if (resultadoClientePDV) {
        resultadoClientePDV.innerHTML = `
            <div class="text-center py-2 text-muted bg-info">Buscando clientes...</div>
        `;
    }

    try{
        const res = await fetch(
            `<?php echo e(route('pdv.buscarCliente')); ?>?query=` + encodeURIComponent(query),
            { signal: controller.signal, headers: { 'Accept': 'application/json' } }
        );

        const text = await res.text();

        if (!res.ok) {
            throw new Error(`Erro backend (${res.status})`);
        }

        let data = [];
        try {
            data = JSON.parse(text);
        } catch(jsonError){
            throw jsonError;
        }

        clientes = Array.isArray(data) ? data : (data.clientes ?? []);
        clienteIndex = -1;

        if(!clientes.length){
            resultadoClientePDV.innerHTML = `
                <div class="text-center py-2 text-light bg-secondary fw-bold">Nenhum cliente encontrado</div>
            `;
            return;
        }

        // Monta o HTML injetando o INDEX do array para capturar o objeto completo depois
        let html = '';
        clientes.forEach((c, i) => {
            html += `
                <div class="cliente-row" data-index="${i}" onclick="selecionarClientePorIndex(${i})">
                    <div>${c.id ?? ''}</div>
                    <div>${c.nome ?? ''}</div>
                    <div>${c.tipo ?? ''}</div>
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

    } catch(e){
        if(e.name === 'AbortError') return;
        console.error('Erro buscarClientes:', e);
        if (resultadoClientePDV) {
            resultadoClientePDV.innerHTML = `
                <div class="text-center text-danger py-2 bg-dark">Erro ao buscar clientes</div>
            `;
        }
    }
}

// ===============================
// NOVA FUNÇÃO AUXILIAR DE SELEÇÃO (SEGURA)
// ===============================
function selecionarClientePorIndex(index) {
    const c = clientes[index];
    if (!c) return;

    selecionarClientePDV(c.id, c.nome, c.tipo, c.telefone, c.endereco, c.numero, c.cep, c.bairro, c.cidade, c.estado);
}

// ===============================
// SELECIONAR CLIENTE
// ===============================
function selecionarClientePDV(id, nome, tipo, telefone = '', endereco = '', numero = '', cep = '', bairro = '', cidade = '', estado = ''){
    
    // Busca garantida dentro do array local mapeado pelo ID
    const clienteData = clientes.find(c => Number(c.id) === Number(id));

    // ===============================
    // INPUTS DO FORMULÁRIO
    // ===============================
    document.querySelector('input[name="cliente_id"]').value = id;
    document.querySelector('input[name="nome"]').value = nome;
    document.querySelector('input[name="pessoa"]').value = tipo;
    document.querySelector('input[name="telefone"]').value = telefone;
    
    const enderecoCompleto = `${endereco} ${numero} - ${bairro}, ${cidade} - ${estado}, CEP: ${cep}`.trim();
    document.querySelector('input[name="endereco"]').value = enderecoCompleto;

    // ===============================
    // CLIENTE GLOBAL (Garante reativação dos novos campos do Laravel)
    // ===============================
    window.cliente = {
        id: id,
        nome: nome,
        tipo: tipo,
        telefone: telefone,
        saldo: Number(clienteData?.saldo ?? 0),
        limite: Number(clienteData?.limite ?? 0),
        credito_usado: Number(clienteData?.credito_usado ?? 0),
        status: clienteData?.status ?? null,
        formas: clienteData?.formas ?? []
    };

    // ===============================
    // MODAL INFO
    // ===============================
    const nomeEl = document.getElementById('nome-cliente-modal');
    const saldoEl = document.getElementById('saldo-cliente-modal');

    if (nomeEl) { nomeEl.textContent = nome; }
    
    if (saldoEl) {
        // Validação visual de segurança baseada no status trazido do banco
        const statusBadge = window.cliente.status === 'ativo' 
            ? '<span class="badge bg-success">Ativo</span>' 
            : '<span class="badge bg-danger">Bloqueado</span>';

        saldoEl.innerHTML = `
            Status: ${statusBadge}<br>
            Saldo: R$ ${window.cliente.saldo.toFixed(2).replace('.', ',')}<br>
            Limite: R$ ${window.cliente.limite.toFixed(2).replace('.', ',')}
        `;
    }

    // ===============================
    // FECHA MODAL
    // ===============================
    const modalEl = document.getElementById('modalCliente');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance?.hide();
        
    }
    
}

</script><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_cliente_pdv.blade.php ENDPATH**/ ?>