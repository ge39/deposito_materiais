<!-- View completa do PDV gerada conforme layout solicitado -->
<!-- Você pode ajustar rota, ids e classes conforme sua lógica -->


<?php $__env->startSection('content'); ?> 

<style>
    /* reset / box model */
    *, *::before, *::after { box-sizing: border-box !important; }

    /* garante 0 nas margens/paddings principais */
    html, body, #app, .app, .main, .container, .container-fluid {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        /* overflow-x: hidden !important; */
    }

    /* força o container a ocupar exatamente a largura sem deslocamento */
    .container-fluid {
        position: relative !important;
        left: 0 !important;
        right: 0 !important;
    }

    /* zera o gutter do Bootstrap (remove espaçamento lateral gerado pela grid) */
    .row {
        --bs-gutter-x: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* colunas compactas (ajuste fino para espaçamento interno) */
    [class*="col-"] {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }

    /* garante que tabelas não forcem overflow lateral */
    table {
        width: 100% !important;
        margin: 0 !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    /* inputs / selects / buttons - sem margens laterais extras */
    input, select, textarea, button {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* fonte conforme pedido */
    .container-fluid * {
        font-size: 12px !important;
    }
    input#descricao,
    input#codigo_barras,
    input#preco_venda,
    input#quantidade,
    input#qtd_disponivel,
    input#total_geral,
    input#unidade {
        font-size: 28px !important; font-weight: bold;
    }
    /*remover itens do carrinho*/
    .linha-carrinho {
        cursor: pointer;
    }

    .linha-carrinho.selecionada {
        background-color: #dbeafe !important;
        outline: 3px solid #2563eb;
    }

    #acoes-carrinho {
        display: none;
        gap: 10px;
    }
    #açoes-carrinho,
   .pdv-area {
    position: relative; /* cria o contexto do PDV */
    }

    .acoes-carrinho {
    position: absolute; /* flutua DENTRO do PDV */

    width:97%;
    display: none;
    background: #ffffff;
    border: 2px solid #ced4da;
    border-radius: 10px;
    padding: 10px;
    z-index: 1000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .linha-selecionada {
    background-color: #fff3cd !important;
    outline: 3px solid #ffc107;
    }


</style>

<!-- ...aqui segue o resto da sua view (mantive o restante igual) -->
<div class="container-fluid p-0"  style="background:#e6e6e6; width:100%; margin-top:-20 ; overflow-x:hidden;">
    <!-- TOPO -->
    <div class="row g-2 mb-2 align-items-center">

        <!-- Venda -->
        <!-- <div class="col-md-1">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox">
                <label class="fw-bold mb-0">Venda</label>
            </div>
        </div> -->

        <!-- Consignado -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox">
                <label class="fw-bold mb-0">Consignado</label>
            </div>
        </div> -->

        <!-- Nota Fiscal -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0">Nota Fiscal:</label>
                <select class="form-select form-select-sm" style="width: 70px;">
                    <option>SIM</option>
                    <option>NÃO</option>
                </select>
            </div>
        </div> -->

        <!-- Operação -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0">Operação:</label>
                <select class="form-select form-select-sm">
                    <option>VENDA</option>
                </select>
            </div>
        </div> -->

        <!-- Botão Contas a receber -->
        <!-- <div class="col-md-2">
            <button class="btn btn-secondary w-10 fw-bold">Contas a receber</button>
        </div> -->

        <!-- Andamento -->
        <!-- <div class="col-md-2">
            <div class="bg-success text-white fw-bold text-center p-1 rounded">
                ANDAMENTO
            </div>
        </div> -->

    </div>
    <!-- CAMPOS DA VENDA -->
    <div class="row g-2 mb-2 p-2" style="background:#3a3a3a; color:white;">
        <div class="col-md-1 fw-bold mb-0">
            <label>Nº Venda</label>
            <input class="form-control">
        </div>
        <div class="col-md-1 fw-bold mb-0">
            <label>Data Venda</label>
            <input class="form-control" type="date" value="<?php echo e(date('Y-m-d')); ?>" readonly>
        </div>
        <div class="col-md-2 fw-bold mb-0">
             <!-- <label>ID</label> -->
            <input type="hidden" name="cliente_id">
            <label>Cliente</label>
            <input class="form-control" name="nome" required readonly>
        </div>
         <div class="col-md-1 fw-bold mb-0">
            <label>Pessoa</label>
            <input class="form-control" name="pessoa" required readonly>
        </div>
        <div class="col-md-1 fw-bold mb-0">
            <label>Telefone</label>
            <input class="form-control" name="telefone" required readonly>
        </div>
         <div class="col-md-4 fw-bold mb-0">
            <label>Endereço</label>
            <input class="form-control" name="endereco" required readonly>
        </div>
        <div class="col-md-2 fw-bold mb-0">
            <label>Op. de Caixa</label>
                <input class="form-control" name="nome" required readonly>
        
        </div>

    </div>

    <!-- CORPO PRINCIPAL -->
    <div class="row g-2 ">

        <!-- LADO ESQUERDO -->
        <div class="col-md-5 border border-2 p-2 " style="background:#3a3a3a; color:white;">
            <div class="border p-2 mb-2 ">
                <label class="fw-bold">Código Barras — (F3) Para pesquisar produtos</label>
                <input class="form-control bg-warning" id="codigo_barras" name = "codigo_barras" autofocus>
            </div>
              <!-- descrição -->
            <div class="border p-2 mb-2 ">
                <label class="fw-bold">Descrição</label>
                <input class="form-control form-control-sm fs-1 fw-bold" id="descricao" readonly>
            </div>

           <div class="row mt-2 border ">
                <!-- Quantidade -->
                <div class="col-md-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input class="form-control form-control-sm fw-bold"  type="number" name="quantidade" id="quantidade" value= "1" min="1" >
                    <small id="msgEstoque" class="text-danger fw-bold"></small>
                </div>

                <!-- Unidade -->
                <div class="col-md-4">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input type="text" name="unidade" id="unidade" readOnly class="form-control form-control-sm fs-1 fw-bold">
                </div>
                <!-- Preço venda -->
                <div class="col-md-5">  
                    <label for="preco_venda" class="form-label">Preço Venda</label>
                    <input id="preco_venda" class="form-control form-control-sm fw-bold bg-warning" style="font-size: 20px;" readonly>
                </div>

            </div>

            <div class="row mt-2 border">
                <!-- Quantidade -->
                <div class="col-md-4">
                    <label class="fw-bold">Qtd.Disponivel</label>
                    <input class="form-control form-control-sm" name="qtd_disponivel" id="qtd_disponivel" type="text" min="1" step="1" readOnly>
                </div>

                    <!-- Total Geral -->
                <div class="col-md-8">
                    <label class="fw-bold">Sub Total</label>
                    <input class="form-control form-control-sm bg-warning" style="font-size: 20px;" name="total_geral" id="total_geral" type="text" readOnly>
                </div>
            </div>

            <!-- CAMPO DE IMAGEM DO PRODUTO -->
            <div class="border bg-white mt-1" style="height: 130px; display:flex; align-items:center; justify-content:center;">
                <img id="produto-imagem" src="" alt="Imagem" style="max-width:100%; max-height:100%; object-fit:contain;">
            </div>
        </div>

        <!-- LADO DIREITO: LISTA DE ITENS -->
        <div id="pdv-area" class="pdv-area col-md-7" style="background:#3a3a3a; color:white;">
            <table class="table table-bordered table-sm bg-white">
                <thead class="table-primary fw-bold text-center fs-10px">
                    <tr>
                        <td class="text-center" style="width:50px">Item</td>
                        <td class="text-center" style="width:250px">Descrição</td>
                        <td class="text-center" style="width:70px">Qtde</td>
                        <td class="text-center" style="width:100px">Unid</td>
                        <td class="text-center" style="width:100px">Preço</td>
                        <td class="text-center" style="width:150px">SubTotal</td>
                    </tr>
                </thead>
                <tbody id="lista-itens"></tbody>
            </table>

            <!-- BOTÕES DE AÇÃO DO ITEM SELECIONADO -->
            <div id="acoes-carrinho" class="acoes-carrinho mt- 0 bg-dark d-none">
                <div class="d-flex gap-2 justify-content-end">
                    <button id="btnDiminuir" class="btn btn-warning btn-lg">− Diminuir</button>
                    <button id="btnRemover" class="btn btn-danger btn-lg">Remover</button>
                    <button id="btnOcultar" class="btn btn-secondary btn-lg">Ocultar</button>
                </div>
            </div>
        </div>
       

    </div>

   <!-- RODAPÉ DOS BOTÕES -->
    <div class="col-md-12 row mt-1 d-flex flex-wrap gap-1">

        <div class="col">
            <button class="btn btn-primary w-100" >F1 Inicio Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-warning w-100" id="btnF2" >F2 Cliente</button>
        </div>

        <div class="col">
            <button class="btn btn-danger w-100">F3 Produto</button>
        </div>

        <div class="col">
            <button  class="btn btn-primary w-100">F4 Orçamento</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F5 Fin. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F6 Cancel. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F8 Local. Venda</button>
        </div>

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">F9 Alt. Qtde</button>
        </div> -->

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">F10 Cad. Produto</button>
        </div> -->

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">Observ. na venda</button>
        </div> -->
         
        <div class="col btn btn-dark w-100 fw-bold d-flex flex-column align-items-center justify-content-center">
            <span class="fw-bold fs-1 fw-bold text-uppercase">Total</span>
            <span id="totalGeral" class="fw-bold text-warning" style="font-size: 20px !important;">R$ 0.00</span>
        </div>
    </div>
    
</div>
<?php $__env->stopSection(); ?>

<!-- Busca Clientes e Produtos -->
<script>
    /* =====================================================
    ATALHOS DE TECLADO – ISOLADO (SEM CONFLITO)
    ===================================================== */
    (function () {

    document.addEventListener('keydown', function (e) {

        // Ignora se algum input/textarea estiver digitando
        const tag = document.activeElement?.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA') {
            // Permitimos F2 e F3 mesmo assim
        }

        if (e.key === 'F2') {
            e.preventDefault();
            const modalCliente = document.getElementById('modalCliente');
            if (modalCliente) {
                new bootstrap.Modal(modalCliente).show();
            }
        }

        if (e.key === 'F3') {
            e.preventDefault();
            const modalProduto = document.getElementById('modalProduto');
            if (modalProduto) {
                new bootstrap.Modal(modalProduto).show();
            }
        }

    });

    /* =====================================================
       SELEÇÃO DE CLIENTE (ISOLADA)
    ===================================================== */
    window.selecionarClientePDV = function (id, nomeEncoded) {

        const nome = decodeURIComponent(nomeEncoded || '');

        const inputClienteId   = document.getElementById('cliente_id');
        const inputClienteNome = document.getElementById('cliente_nome');

        if (inputClienteId)   inputClienteId.value = id;
        if (inputClienteNome) inputClienteNome.value = nome;

        const modalEl = document.getElementById('modalCliente');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        // Fluxo PDV: foco no código de barras
        setTimeout(() => {
            const inputCodigo = document.getElementById('codigo_barras');
            if (inputCodigo) inputCodigo.focus();
        }, 150);
    };

    })();
</script>

<script>
  document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
    ELEMENTOS
    ===================================================== */
    const inputCodigo        = document.getElementById("codigo_barras");
    const inputDescricao     = document.getElementById("descricao");
    const inputQuantidade    = document.getElementById("quantidade");
    const inputPrecoVenda    = document.getElementById("preco_venda");
    const inputTotalGeral    = document.getElementById("total_geral");
    const qtdDisponivelInput = document.getElementById("qtd_disponivel");
    const imgProduto         = document.getElementById("produto-imagem");

    const tabelaItens = document.getElementById("lista-itens");
    const totalCarrinho = document.getElementById("totalGeral");

    /* =====================================================
    ESTADO GLOBAL
    ===================================================== */
    window.produtoAtual = null;

    /* =====================================================
    FUNÇÕES AUXILIARES
    ===================================================== */
    function limparCamposProduto() {
        if (inputDescricao) inputDescricao.value = "";
        if (inputPrecoVenda) inputPrecoVenda.value = "";
        if (inputTotalGeral) inputTotalGeral.value = "";
        if (inputQuantidade) inputQuantidade.value = 1;
        if (inputMarca) inputMarca.max   = "";
        if (qtdDisponivelInput) qtdDisponivelInput.value = 0;
        if (imgProduto) imgProduto.src = "/images/produto-sem-imagem.png";
    }

    function calcularTotalProduto() {
        const preco = parseFloat(inputPrecoVenda?.value || 0);
        const qtd   = parseFloat(inputQuantidade?.value || 0);
        if (inputTotalGeral) {
            inputTotalGeral.value = (preco * qtd).toFixed(2);
        }
    }

    function atualizarNumeroItens() {
        const linhas = tabelaItens.querySelectorAll("tr");
        let contador = 1;
        linhas.forEach(linha => {
            linha.querySelector(".item-numero").textContent = contador++;
        });
    }

    function atualizarTotalCarrinho() {
        let total = 0;

        // Percorre todas as linhas da tabela e soma os valores da coluna "subtotal"
        tabelaItens.querySelectorAll(".subtotal").forEach(el => {
            // Converte o texto para número, substituindo vírgula por ponto caso exista
            total += Number(el.textContent.replace(',', '.')) || 0;
        });

        // Atualiza o elemento do total no padrão brasileiro (R$ 1.234,56)
        atualizarTotalGeral(total);
    }

    function resetarProdutoAtual() {
        window.produtoAtual = null;
        inputQuantidade.value = 1;
        inputCodigo.focus();
    }

    function atualizarTotalGeral(total) {
    const totalEl = document.getElementById("totalGeral");
    if (!totalEl) return;

    // Formata o número no padrão brasileiro de moeda
    totalEl.textContent = total.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
    }

    /* =====================================================
    BUSCA DE PRODUTO (ROTA ORIGINAL)
    ===================================================== */
    async function buscarProduto() {
    const codigo = inputCodigo.value.trim();
    if (!codigo) return;

    try {
        const res = await fetch(`/pdv/produto/${encodeURIComponent(codigo)}`, {
            headers: { "Accept": "application/json" }
        });

        if (!res.ok) {
            alert("Produto não encontrado.");
            return;
        }

        const data = await res.json();
        if (data.status !== "ok" || !data.produto) {
            alert("Produto não encontrado.");
            return;
        }

        const produto = data.produto;
        const lote = produto.lotes && produto.lotes.length
            ? produto.lotes[0]
            : null;

        window.produtoAtual = produto;

        // Preenche os campos do produto
        inputDescricao.value  = produto.nome;
        inputPrecoVenda.value = Number(produto.preco_venda).toFixed(2);

        const qtdDisponivel = produto.quantidade_total_disponivel || 1;
        inputQuantidade.value = 1;
        inputQuantidade.max   = qtdDisponivel;

        if (qtdDisponivelInput) qtdDisponivelInput.value = qtdDisponivel;

        // Preenche o campo Unidade
        const inputUnidade = document.getElementById('unidade');
            if (inputUnidade) {
            inputUnidade.value = produto.unidade_sigla || ""; // Preenche a unidade
        }

        // Exibe a imagem do produto
        if (imgProduto) {
            imgProduto.src = produto.imagem
                ? `/storage/${produto.imagem}`
                : "/images/produto-sem-imagem.png";
        }

        // Calcula o total do produto
        calcularTotalProduto();

        // Limpa o campo de código de barras e foca na quantidade
        inputCodigo.value = "";
        inputQuantidade.focus();

        } catch (e) {
            console.error(e);
            alert("Erro ao buscar produto.");
        }
    }



    /* =====================================================
    EVENTOS
    ===================================================== */
    inputCodigo.addEventListener("keydown", e => {
        if (e.key === "Enter") {
            e.preventDefault();
            buscarProduto();
        }
    });

    inputQuantidade.addEventListener("input", () => {
        const max = Number(inputQuantidade.max);
        if (Number(inputQuantidade.value) > max) {
            inputQuantidade.value = max;
        }
        calcularTotalProduto();
    });

    inputQuantidade.addEventListener("keydown", e => {
        if (e.key !== "Enter") return;

        e.preventDefault();

        if (!window.produtoAtual) {
            alert("Nenhum produto carregado. Leia o código de barras.");
            return;
        }

        adicionarItemCarrinho(window.produtoAtual);
    });

    document.addEventListener("keydown", e => {
        if (e.key === "F3") {
            e.preventDefault();
            inputCodigo.focus();
        }
    });

    /* =====================================================
    CARRINHO
    ===================================================== */
   
    window.adicionarItemCarrinho = function (produto) {

        const quantidade = Number(inputQuantidade.value);
        const preco = Number(produto.preco_venda);

        const loteId = produto.lotes?.[0]?.numero_lote ?? "";

        if (quantidade <= 0) {
            alert("Informe uma quantidade válida.");
            inputQuantidade.focus();
            return;
        }

        if (preco <= 0) {
            alert("Produto sem preço de venda.");
            return;
        }

        const linhas = tabelaItens.querySelectorAll("tr");

        for (let linha of linhas) {
            if (
                linha.dataset.produtoId == produto.id &&
                linha.dataset.loteId == loteId
            ) {
                const tdQtd = linha.querySelector(".item-quantidade");
                const tdSubtotal = linha.querySelector(".subtotal");

                const novaQtd = Number(tdQtd.textContent) + quantidade;
                if (novaQtd > Number(inputQuantidade.max)) {
                    alert("Estoque insuficiente.");
                    return;
                }

                tdQtd.textContent = novaQtd;
                tdSubtotal.textContent = (novaQtd * preco).toFixed(2);

                atualizarNumeroItens(); //Soma os itens corretamente
                atualizarTotalCarrinho(); //Soma os subtotais corretamente
                resetarProdutoAtual(); // Reseta o produto atual
                limparCamposProduto(); // Limpa os campos do produto
                return;
            }
        }

        const subtotal = quantidade * preco;

        tabelaItens.insertAdjacentHTML("beforeend", `
            <tr class="linha-carrinho" data-produto-id="${produto.id}" data-lote-id="${loteId}">
                <td class="item-numero text-center " style="font-size:32px; font-weight:bold;"></td>
                <td class="text-center" style="font-size:32px; font-weight:bold;">${produto.nome}</td>
                <td class=" item-quantidade text-center" style="font-size:32px; font-weight:bold;">${quantidade}</td>
                <td class="text-center" style="font-size:32px; font-weight:bold;">${produto.unidade_sigla ?? ""}</td>
                <td class="item-preco text-end" style="font-size:32px; font-weight:bold;">${preco.toFixed(2)}</td>
                <td class="subtotal text-end subtotal" style="font-size:32px; font-weight:bold;">${subtotal.toFixed(2)}</td>
            </tr>

        `);

        atualizarNumeroItens();
        atualizarTotalCarrinho();
        resetarProdutoAtual();
        limparCamposProduto();
    };
  });
</script>

 <!-- //remove itens do carrinho -->
<script>
    //remove itens do carrinho
    document.addEventListener("DOMContentLoaded", function () {

        const tabelaItens = document.getElementById("lista-itens");
        const acoesCarrinho = document.getElementById("acoes-carrinho");

        let linhaSelecionada = null;

        tabelaItens.addEventListener("click", function (e) {

            const tr = e.target.closest("tr.linha-carrinho");
            if (!tr) return;

            document.querySelectorAll("#lista-itens tr").forEach(l => {
                l.classList.remove("table-warning");
            });

            tr.classList.add("table-warning");
            linhaSelecionada = tr;

            acoesCarrinho.style.display = "block";

            console.log("CLIQUE OK NA LINHA", tr);
        });

        document.getElementById("btnOcultar").addEventListener("click", function () {
        document.getElementById("acoes-carrinho").style.display = "none";
        document.addEventListener("click", function (e) {
            if (
                !e.target.closest(".linha-carrinho") &&
                !e.target.closest("#acoes-carrinho")
            ) {
                ocultarAcoesCarrinho();
            }
        });

        document.querySelectorAll("#lista-itens tr").forEach(l => {
            l.classList.remove("table-warning");
        });

        linhaSelecionada = null;
    });


    });
  
    function ocultarAcoesCarrinho() {
        document.getElementById("acoes-carrinho").style.display = "none";

        document.querySelectorAll("#lista-itens tr").forEach(l => {
            l.classList.remove("linha-selecionada");
        });

        linhaSelecionada = null;
    }

   function posicionarAcoesCarrinho(tr) {

    const acoes = document.getElementById("acoes-carrinho");
    const pdv   = document.getElementById("pdv-area");

    const trRect  = tr.getBoundingClientRect();
    const pdvRect = pdv.getBoundingClientRect();

    const GAP = 10; // distância visual abaixo da linha

    const top  = (trRect.top - pdvRect.top) + tr.offsetHeight + GAP;
    const left = (trRect.left - pdvRect.left) + 10;

    acoes.style.top  = top  + "px";
    acoes.style.left = left + "px";

    acoes.style.display = "block";
    }

    document.getElementById("lista-itens").addEventListener("click", function (e) {

    const tr = e.target.closest("tr.linha-carrinho");
    if (!tr) return;

    // Remove ações anteriores
    document.querySelectorAll(".linha-acoes").forEach(l => l.remove());
    document.querySelectorAll(".linha-carrinho").forEach(l => {
        l.classList.remove("linha-selecionada");
    });

    // Marca a linha selecionada
    tr.classList.add("linha-selecionada");

    // 🔹 CRIA A LINHA DE AÇÕES
    const linhaAcoes = document.createElement("tr");
    linhaAcoes.className = "linha-acoes";

    linhaAcoes.innerHTML = `
        <td colspan="6" class="text-center">
            <button class="btn btn-warning btn-lg me-3" id="btnDiminuir">
                − Diminuir
            </button>
            <button class="btn btn-danger btn-lg" id="btnRemover">
                Remover
            </button>
        </td>
    `;

    // 🔹 INSERE A LINHA DE AÇÕES LOGO ABAIXO DA LINHA CLICADA
    tr.after(linhaAcoes);

    // ===============================
    // 🔧 AQUI É ONDE VOCÊ COLOCA O AJUSTE DE ALTURA
    // ===============================

    const alturaLinha = tr.offsetHeight;
    const tdAcoes = linhaAcoes.querySelector("td");

    tdAcoes.style.minHeight  = alturaLinha + "px";
    tdAcoes.style.lineHeight = alturaLinha + "px";

    });
</script>

<!-- Alinhamento dos botoes abaixo da linha dos itens do carrinho -->
<script>
 document.addEventListener("DOMContentLoaded", () => {

    const tabela = document.getElementById("lista-itens");
    const acoes  = document.getElementById("acoes-carrinho");
    const pdv    = document.querySelector(".pdv-area");

    tabela.addEventListener("click", (e) => {

        const tr = e.target.closest("tr.linha-carrinho");
        if (!tr) return;

        // destaque
        document.querySelectorAll(".linha-carrinho")
            .forEach(l => l.classList.remove("linha-selecionada"));

        tr.classList.add("linha-selecionada");

        // cálculos corretos
        const trRect  = tr.getBoundingClientRect();
        const pdvRect = pdv.getBoundingClientRect();

        const GAP = 8;

        let top  = (trRect.bottom - pdvRect.top) + GAP;
        let left = (trRect.left - pdvRect.left);

        // limites de segurança
        const maxLeft = pdv.clientWidth - acoes.offsetWidth - 10;
        if (left > maxLeft) left = maxLeft;
        if (left < 10) left = 10;

        acoes.style.top  = top + "px";
        acoes.style.left = left + "px";
        acoes.style.display = "block";

    });

    // ocultar ao clicar fora
    document.addEventListener("click", (e) => {
        if (!e.target.closest(".linha-carrinho") &&
            !e.target.closest("#acoes-carrinho")) {

            acoes.style.display = "none";
            document.querySelectorAll(".linha-carrinho")
                .forEach(l => l.classList.remove("linha-selecionada"));
        }
    });

 });
</script>

<!-- Funções dos botões diminuir, remover e ocultar itens do carrinho -->
 <script>
    document.addEventListener('DOMContentLoaded', () => {
        const listaItens = document.getElementById('lista-itens');
        const btnDiminuir = document.getElementById('btnDiminuir');
        const btnRemover = document.getElementById('btnRemover');
        const btnOcultar = document.getElementById('btnOcultar');
        const totalVenda = document.getElementById('totalGeral');
        const acoesCarrinho = document.getElementById('acoes-carrinho');

        // Função para pegar a linha selecionada visível
        function getLinhaSelecionada() {
            return document.querySelector('#lista-itens tr.table-warning:not(.d-none)');
        }

        // Atualiza visibilidade dos botões
        function atualizarVisibilidadeBotoes() {
            const linha = getLinhaSelecionada();
            if (linha) {
                acoesCarrinho.classList.remove('d-none'); // mostra
            } else {
                acoesCarrinho.classList.add('d-none'); // oculta
            }
        }

        // Inicialmente oculta os botões
        acoesCarrinho.classList.add('d-none');

        // Selecionar linha do carrinho
        listaItens.addEventListener('click', (e) => {
            const linha = e.target.closest('tr');
            if (!linha || linha.classList.contains('d-none')) return;

            document.querySelectorAll('#lista-itens tr').forEach(l => l.classList.remove('table-warning'));
            linha.classList.add('table-warning'); // marca a linha

            atualizarVisibilidadeBotoes(); // mostra os botões
        });

        // Diminuir quantidade
        btnDiminuir.addEventListener('click', () => {
            const linhaSelecionada = getLinhaSelecionada();
            if (!linhaSelecionada);

            const tdQtde = linhaSelecionada.children[2];
            let qtd = parseInt(tdQtde.textContent);
            if (qtd > 1) {
                tdQtde.textContent = qtd - 1;
                atualizarSubTotal(linhaSelecionada);
            } else if (confirm('Quantidade é 1. Deseja remover o item?')) {
                linhaSelecionada.remove();
                atualizarTotalVenda();
                reordenarItens();
                atualizarVisibilidadeBotoes();
            }
        });

        // Remover item
        btnRemover.addEventListener('click', () => {
            const linhaSelecionada = getLinhaSelecionada();
            if (!linhaSelecionada);

            if (confirm('Deseja remover o item selecionado?')) {
                linhaSelecionada.remove();
                atualizarTotalVenda();
                reordenarItens();
                atualizarVisibilidadeBotoes();
            }
        });

        // Ocultar item
        btnOcultar.addEventListener('click', () => {
            const linhaSelecionada = getLinhaSelecionada();
            if (!linhaSelecionada) ;

            linhaSelecionada.classList.add('d-none');
            linhaSelecionada.classList.remove('table-warning');
            atualizarTotalVenda();
            reordenarItens();
            atualizarVisibilidadeBotoes();
        });

        // Atualizar subtotal da linha
        function atualizarSubTotal(linha) {
            const qtd = parseInt(linha.children[2].textContent);
            const preco = parseFloat(linha.children[4].textContent.replace('R$', '').replace(',', '.'));
            linha.children[5].textContent = 'R$ ' + (qtd * preco).toFixed(2).replace('.', ',');
            atualizarTotalVenda();
        }

        // Atualizar total da venda
        function atualizarTotalVenda() {
            let total = 0;
            document.querySelectorAll('#lista-itens tr:not(.d-none)').forEach(linha => {
                const subtotal = parseFloat(linha.children[5].textContent.replace('R$', '').replace(',', '.'));
                total += subtotal;
            });
            totalVenda.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }

        // Reordenar coluna Item
        function reordenarItens() {
            let contador = 1;
            document.querySelectorAll('#lista-itens tr:not(.d-none)').forEach(linha => {
                linha.children[0].textContent = contador++;
            });
        }
    });
</script>

<!-- carregar orçamento no PDV -->
<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {

    const modalEl     = document.getElementById('modalOrcamento');
    const inputCodigo = document.getElementById('inputCodigoOrcamento');

    if (!modalEl || !inputCodigo) {
        console.error('Modal ou input do orçamento não encontrado');
        return;
    }

    const modalOrcamento = new bootstrap.Modal(modalEl);

    /**
     * ===============================
     * ABRIR MODAL COM F4
     * ===============================
     */
    window.addEventListener('keydown', function (e) {
        if (e.code === 'F4') {
            e.preventDefault();
            modalOrcamento.show();
        }
    });

    /**
     * ===============================
     * FOCO AUTOMÁTICO
     * ===============================
     */
    modalEl.addEventListener('shown.bs.modal', function () {
        inputCodigo.value = '';
        inputCodigo.focus();
    });

    /**
     * ===============================
     * CONFIRMAR ORÇAMENTO
     * ===============================
     */
    window.confirmarOrcamentoFront = function () {
        const codigo = inputCodigo.value.trim();

        if (!codigo) {
            alert('Informe o código do orçamento');
            inputCodigo.focus();
            return;
        }

        fetch(`/orcamentos/buscar?codigo_orcamento=${encodeURIComponent(codigo)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Orçamento não encontrado');
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Erro ao buscar orçamento');
                return;
            }

            // INSERE NO CARRINHO
            inserirOrcamentoNoCarrinho(data.orcamento);

            // FECHA MODAL
            modalOrcamento.hide();
        })
        .catch(err => {
            alert(err.message);
            inputCodigo.focus();
        });
    };

    });
</script> -->

<!-- carregar orçamento no PDV -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalEl     = document.getElementById('modalOrcamento');
    const inputCodigo = document.getElementById('inputCodigoOrcamento');
    const carrinhoTbody = document.querySelector('#carrinho tbody');
    const totalVendaEl  = document.getElementById('total-venda');

    const codigo = Number(inputCodigo.value.trim());
    if (!codigo) {
        alert('Informe o código do orçamento');
        inputCodigo.focus();
        return;
    }

    fetch(`/orcamentos/buscar?codigo_orcamento=${codigo}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })

    if (!modalEl || !inputCodigo) {
        console.error('Modal ou input do orçamento não encontrado');
        return;
    }

    const modalOrcamento = new bootstrap.Modal(modalEl);

    // ===============================
    // ABRIR MODAL COM F4
    // ===============================
    window.addEventListener('keydown', function (e) {
        if (e.code === 'F4') {
            e.preventDefault();
            modalOrcamento.show();
        }
    });

    // ===============================
    // FOCO AUTOMÁTICO
    // ===============================
    modalEl.addEventListener('shown.bs.modal', function () {
        inputCodigo.value = '';
        inputCodigo.focus();
    });

    // ===============================
    // CONFIRMAR ORÇAMENTO
    // ===============================
    window.confirmarOrcamentoFront = function () {
        const codigo = inputCodigo.value.trim();

        if (!codigo) {
            alert('Informe o código do orçamento');
            inputCodigo.focus();
            return;
        }

        fetch(`/orcamentos/buscar?codigo_orcamento=${encodeURIComponent(codigo)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Orçamento não encontrado');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Erro ao buscar orçamento');
                return;
            }

            const orcamento = data.orcamento;

            if (!orcamento.itens || orcamento.itens.length === 0) {
                alert('O orçamento não possui itens');
                return;
            }

            // ===============================
            // INSERE ITENS NO CARRINHO
            // ===============================
            if (!carrinhoTbody) {
                console.error('Tabela do carrinho não encontrada');
                return;
            }

            carrinhoTbody.innerHTML = ''; // limpa carrinho

            let totalVenda = 0;

            orcamento.itens.forEach((item, index) => {
                const quantidade = parseFloat(item.quantidade);
                const preco = parseFloat(item.preco_unitario);
                const subtotal = quantidade * preco;
                totalVenda += subtotal;

                const tr = document.createElement('tr');
                tr.dataset.produtoId = item.produto_id;
                tr.innerHTML = `
                    <td class="text-center item-numero">${index + 1}</td>
                    <td>${item.produto?.nome ?? 'Produto não especificado'}</td>
                    <td class="text-center">${quantidade}</td>
                    <td class="text-end">${preco.toFixed(2)}</td>
                    <td class="text-end subtotal-item">${subtotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remover-item">X</button>
                    </td>
                `;

                // botão remover item
                tr.querySelector('.btn-remover-item').addEventListener('click', function () {
                    tr.remove();
                    atualizarNumerosItens();
                    recalcularTotal();
                });

                carrinhoTbody.appendChild(tr);
            });

            // Atualiza total da venda
            if (totalVendaEl) totalVendaEl.textContent = totalVenda.toFixed(2);

            // ===============================
            // FECHA MODAL
            // ===============================
            modalOrcamento.hide();

            // ===============================
            // FUNÇÕES AUXILIARES
            // ===============================
            function atualizarNumerosItens() {
                const itens = carrinhoTbody.querySelectorAll('.item-numero');
                itens.forEach((td, i) => td.textContent = i + 1);
            }

            function recalcularTotal() {
                let total = 0;
                carrinhoTbody.querySelectorAll('tr').forEach(tr => {
                    const subtotal = parseFloat(tr.querySelector('.subtotal-item').textContent);
                    total += subtotal;
                });
                if (totalVendaEl) totalVendaEl.textContent = total.toFixed(2);
            }

        })
        .catch(err => {
            alert(err.message);
            inputCodigo.focus();
        });
    };

});
</script>



<!-- inserir orcamento no carrinho -->
<script>
    function inserirOrcamentoNoCarrinho(orcamento) {

        // 1. Observação da venda (vem do orçamento)
        const campoObs = document.getElementById('observacoes_venda');
        if (campoObs && orcamento.observacao) {
            campoObs.value = orcamento.observacao;
        }

        // 2. Dados do cliente (uso futuro: entrega / retirada)
        window.clienteOrcamento = {
            nome: orcamento.cliente?.nome || '',
            telefone: orcamento.cliente?.telefone || '',
            endereco: orcamento.cliente?.endereco || ''
        };

        // 3. Limpa carrinho atual (regra de negócio)
        if (typeof limparCarrinho === 'function') {
            limparCarrinho();
        }

        // 4. Insere itens
        orcamento.itens.forEach(item => {

            const produto = {
                id: item.produto_id,
                nome: item.descricao,
                preco_venda: item.preco,
                unidade_sigla: item.unidade
            };

            // reaproveita sua função já existente
            adicionarAoCarrinho(
                produto,
                item.lote_id,
                item.quantidade
            );
        });

        // 5. Atualiza total
        if (typeof atualizarTotalVenda === 'function') {
            atualizarTotalVenda();
        }

        console.log('Orçamento carregado no carrinho:', orcamento.codigo);
    }
</script>

<?php echo $__env->make('pdv.modals.modal_cliente_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_produto_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_orcamento', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>