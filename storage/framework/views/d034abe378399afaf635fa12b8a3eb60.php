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
        <div class="col-md-3 border border-2 p-2 " style="background:#3a3a3a; color:white;">
            <div class="border p-2 mb-2 ">
                <label class="fw-bold">Código Barras — (F3) Para pesquisar produtos</label>
                <input class="form-control bg-warning fw-bold" id="codigo_barras" name = "codigo_barras" autofocus>
            </div>
              <!-- descrição -->
            <div class="border p-2 mb-2 ">
                <label class="fw-bold">Descrição</label>
                <input class="form-control fw-bold" id="descricao" readonly>
            </div>

           <div class="row mt-2 border ">
                <!-- Quantidade -->
                <div class="col-md-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input type="number" name="quantidade" id="quantidade" 
                        class="form-control form-control-sm" min="1" value="1">
                    <small id="msgEstoque" class="text-danger fw-bold"></small>
                </div>

                <!-- Unidade -->
                <div class="col-md-4">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input type="text" name="unidade" id="unidade" readOnly class="form-control form-control-sm">
                </div>
                <!-- Preço venda -->
                <div class="col-md-5">  
                    <label for="preco_venda" class="form-label">Preço Venda</label>
                    <input id="preco_venda" class="form-control mb-2 fw-bold bg-warning" readonly>
                </div>

            </div>

            <div class="row mt-2 border">
                <!-- Quantidade -->
                <div class="col-md-4">
                    <label class="fw-bold">Qtd.Disponivel</label>
                    <input class="form-control" id="qtd_disponivel" type="text" class="form-control mb-2 fw-bold" min="1" step="1" readOnly>
                </div>

                    <!-- Total Geral -->
                <div class="col-md-8">
                    <label class="fw-bold">TOTAL</label>
                    <input class="form-control bg-warning fw-bold fw-bold" id="total_geral" type="text" readOnly>
                </div>
            </div>

            <!-- CAMPO DE IMAGEM DO PRODUTO -->
            <div class="border bg-white mt-1" style="height: 130px; display:flex; align-items:center; justify-content:center;">
                <img id="produto-imagem" src="" alt="Imagem" style="max-width:100%; max-height:100%; object-fit:contain;">
            </div>
        </div>

        <!-- LADO DIREITO: LISTA DE ITENS -->
        <div class="col-md-9 " style="background:#3a3a3a; color:white;">
            <table class="table table-bordered table-sm bg-white">
                <thead class="table-primary fw-bold text-center fs-10px">
                    <tr>
                        <th class="text-center" style="width:50px">Item</th>
                        <th class="text-center" style="width:250px">Descrição</th>
                        <th class="text-center" style="width:70px">Qtde</th>
                        <th class="text-center" style="width:100px">Unid</th>
                        <th class="text-center" style="width:100px">Preço</th>
                        <th class="text-center" style="width:150px">SubTotal</th>
                    </tr>
                </thead>
                <tbody id="lista-itens"></tbody>
            </table>
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
            <button class="btn btn-secondary w-100">F4 Fin. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F5 Exc. Produto</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F6 Cancel. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F8 Local. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F9 Alt. Qtde</button>
        </div>

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">F10 Cad. Produto</button>
        </div> -->

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">Observ. na venda</button>
        </div> -->

        <div class="col">
            <button class="btn btn-danger w-100">Esc Fechar</button>
        </div>

    </div>
    
</div>
<?php $__env->stopSection(); ?>

<!-- SCRIPT FINAL – QUANTIDADE × PREÇO = TOTAL -->
<script>
    document.addEventListener("DOMContentLoaded", () => {

        const inputQtd = document.getElementById("quantidade");
        const inputPreco = document.getElementById("preco_venda");
        const inputQtdDisp = document.getElementById("quantidade_disponivel");
        const inputTotal = document.getElementById("total");
        const erroQtd = document.getElementById("erro-quantidade");

        /**
         * Permitir apenas números no campo quantidade
         */
        inputQtd.addEventListener("input", () => {
            inputQtd.value = inputQtd.value.replace(/\D/g, ""); // remove tudo que não é número
            validarQuantidade();
            atualizarTotal();
        });

        /**
         * Validação da quantidade com base na quantidade_disponivel
         */
        function validarQuantidade() {
            const inputQtd = Number(inputQuantidade.value);
            const qtdDisp = Number(inputQuantidade_disponivel.value);

            if (qtd > qtdDisp) {
                erroQtd.textContent = "Quantidade informada excede a quantidade disponível.";
                inputQtd.classList.add("is-invalid");
                return false;
            }

            erroQtd.textContent = "";
            inputQtd.classList.remove("is-invalid");
            return true;
        }

        /**
         * Calcula o total automaticamente
         */
        function atualizarTotal() {
            if (!validarQuantidade()) {
                inputTotal.value = "";
                return;
            }

            const qtd = Number(inputQtd.value);
            const preco = Number(inputPreco.value);

            const total = qtd * preco;

            inputTotal.value = total.toFixed(2);
        }

    });

</script>

<!-- Quantidade venda vs quantidade_disponivel -->
<script>
    document.addEventListener("DOMContentLoaded", () => {

    const inputQtd = document.getElementById("quantidade");
    const inputQtdDisp = document.getElementById("quantidade_disponivel");
    const erroQtd = document.getElementById("erroQtd");

    function validarQuantidade() {
        const qtd = Number(inputQtd.value);
        const qtdDisp = Number(inputQtdDisp.value);

        if (!qtdDisp || qtdDisp < 0) return true; // proteção simples

        if (qtd > qtdDisp) {
            erroQtd.textContent = "Quantidade informada é maior que a disponível.";
            inputQtd.classList.add("is-invalid");
            return false;
        }

        erroQtd.textContent = "";
        inputQtd.classList.remove("is-invalid");
        return true;
    }

    // Permitir apenas números
    inputQtd.addEventListener("input", () => {
        inputQtd.value = inputQtd.value.replace(/\D/g, ""); // somente dígitos
        validarQuantidade();
    });

});
</script>

<!-- Busca Clientes e Produtos -->
<script>
    document.addEventListener('keydown', function (e) {

        switch (e.key) {

            case 'F2': // CLIENTE
                e.preventDefault();
                new bootstrap.Modal(document.getElementById('modalCliente')).show();
                break;

            case 'F3': // PRODUTO
                e.preventDefault();
                new bootstrap.Modal(document.getElementById('modalProduto')).show();
                break;
        }

    });

    function selecionarClientePDV(id, nomeEncoded) {
        // Decodifica o nome caso tenha caracteres especiais
        let nome = decodeURIComponent(nomeEncoded);

        // Preenche os campos do PDV
        document.getElementById('cliente_id').value = id;
        document.getElementById('cliente_nome').value = nome;

        // Fecha o modal automaticamente
        let modalEl = document.getElementById('modalCliente');
        let modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        // Opcional: foca no próximo campo (por exemplo, produto)
        setTimeout(() => {
            document.getElementById('buscaProdutoPDV').focus();
        }, 200);
    }
    //Busca produto por código de barras
   document.addEventListener("DOMContentLoaded", () => {
    const inputCodigo = document.getElementById("codigo_barras");
    const inputDescricao = document.getElementById("descricao");
    const inputQuantidade = document.getElementById("quantidade");
    const qtd_disponivel = document.getElementById("qtd_disponivel") || inputQuantidade;
    const inputPrecoVenda = document.getElementById("preco_venda");
    const inputTotalGeral = document.getElementById("total_geral");
    const imgProduto = document.getElementById("produto-imagem");

    const hProdutoID = document.getElementById("produto_id");
    const hCat = document.getElementById("produto_categoria");
    const hForn = document.getElementById("produto_fornecedor");
    const hMarca = document.getElementById("produto_marca");
    const hUnid = document.getElementById("produto_unidadeMedida->sigla");

    const hLoteID = document.getElementById("lote_id");
    const hLoteValidade = document.getElementById("lote_validade");
    const hLoteEstoque = document.getElementById("lote_estoque");

    // calcular total
    function calcularTotal() {
        const preco = parseFloat((inputPrecoVenda?.value || "0").replace(",", ".")) || 0;
        const qtd = parseFloat(inputQuantidade?.value || "1") || 1;
        if (inputTotalGeral) inputTotalGeral.value = (preco * qtd).toFixed(2);
        }

        if (inputQuantidade) {
            inputQuantidade.addEventListener("input", calcularTotal);
        }

    function limparCampos() {
        if (inputDescricao) inputDescricao.value = "";
        if (inputPrecoVenda) inputPrecoVenda.value = "";
        if (inputTotalGeral) inputTotalGeral.value = "";
        if (inputQuantidade) inputQuantidade.value = 1;
        if (imgProduto) imgProduto.src = "";

        [hProdutoID, hCat, hForn, hMarca, hUnid, hLoteID, hLoteValidade, hLoteEstoque].forEach(e => {
            if (e) e.value = "";
        });
    }
    
    // ENTER para buscar
    inputCodigo.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            buscarProduto();
        }
    });

    inputCodigo.focus();

    });
   

    // Busca produto pelo código de barras 
        // Busca produto pelo código de barras
    document.addEventListener("DOMContentLoaded", () => {

        const inputCodigo = document.getElementById("codigo_barras");
        const inputDescricao = document.getElementById("descricao");
        const inputQuantidade = document.getElementById("quantidade");
        const qtd_disponivel = document.getElementById("qtd_disponivel"); // agora usamos o input correto
        const inputPrecoVenda = document.getElementById("preco_venda");
        const inputTotalGeral = document.getElementById("total_geral");
        const imgProduto = document.getElementById("produto-imagem");

        const hProdutoID = document.getElementById("produto_id");
        const hCat = document.getElementById("produto_categoria");
        const hForn = document.getElementById("produto_fornecedor");
        const hMarca = document.getElementById("produto_marca");
        // const hUnid = document.getElementById("produto_unidade");
        const hUnid = document.getElementById("unidade");
        const hLoteID = document.getElementById("lote_id");
        const hLoteValidade = document.getElementById("lote_validade");
        const hLoteEstoque = document.getElementById("lote_estoque");

            // Calcula total
        function calcularTotal() {
            const preco = parseFloat((inputPrecoVenda?.value || "0").replace(",", ".")) || 0;
            const qtd = parseFloat(inputQuantidade?.value || "1") || 1;
            if (inputTotalGeral) inputTotalGeral.value = (preco * qtd).toFixed(2);
        }

        if (inputQuantidade) {
            inputQuantidade.addEventListener("input", calcularTotal);
        }

        function limparCampos() {
            if (inputDescricao) inputDescricao.value = "";
            if (inputPrecoVenda) inputPrecoVenda.value = "";
            if (inputTotalGeral) inputTotalGeral.value = "";
            if (inputQuantidade) inputQuantidade.value = 1;
            if (qtd_disponivel) qtd_disponivel.value = 1;
            if (imgProduto) imgProduto.src = "";

            [hProdutoID, hCat, hForn, hMarca, hUnid, hLoteID, hLoteValidade, hLoteEstoque].forEach(e => {
                if (e) e.value = "";
            });
        }

        // Busca produto pelo código de barras
        async function buscarProduto() {
            const codigo = inputCodigo.value.trim();
            if (!codigo) return;

            const url = "/pdv/produto/" + encodeURIComponent(codigo);

            try {
                const res = await fetch(url, {
                    method: "GET",
                    headers: { "Accept": "application/json" }
                });

                if (!res.ok) {
                    alert("Produto não encontrado (HTTP " + res.status + ")");
                    limparCampos();
                    inputCodigo.focus();
                    return;
                }

                const data = await res.json();

                if (data.status !== "ok" || !data.produto) {
                    alert(data.mensagem || "Produto não encontrado");
                    limparCampos();
                    inputCodigo.focus();
                    return;
                }

                const produto = data.produto;
                const lote = produto.lotes && produto.lotes.length > 0 ? produto.lotes[0] : null;

                // Preenche campos do produto
                if (inputDescricao) inputDescricao.value = produto.nome || "";
                if (inputPrecoVenda) inputPrecoVenda.value = produto.preco_venda ? Number(produto.preco_venda).toFixed(2) : "0.00";
                if (hProdutoID) hProdutoID.value = produto.id;
                if (hCat) hCat.value = produto.categoria?.nome || "";
                if (hForn) hForn.value = produto.fornecedor?.nome || "";
                if (hMarca) hMarca.value = produto.marca?.nome || "";
                if (hUnid) hUnid.value = produto.unidade_medida?.nome || "";

                // Imagem
                // if (imgProduto && produto.imagem) {
                //     imgProduto.src = "/storage/" + produto.imagem;
                // } else if (imgProduto) {
                //     imgProduto.src = "";
                // }
                // Imagem
                const imagemPadrao = "/images/produto-sem-imagem.png";

                if (imgProduto) {
                    if (produto.imagem && produto.imagem !== "") {
                        imgProduto.src = "/storage/" + produto.imagem;
                    } else {
                        imgProduto.src = imagemPadrao;
                    }
                }

                // Preenche lote
                if (lote) {
                    if (hLoteID) hLoteID.value = lote.numero_lote || "";
                    if (hLoteValidade) hLoteValidade.value = lote.validade_lote ? lote.validade_lote.split('T')[0] : "";
                    if (hLoteEstoque) hLoteEstoque.value = lote.quantidade_disponivel || 0;
                } else {
                    if (hLoteID) hLoteID.value = "";
                    if (hLoteValidade) hLoteValidade.value = "";
                    if (hLoteEstoque) hLoteEstoque.value = 0;
                }

                // Preenche quantidade total disponível (soma de todos os lotes)
                if (qtd_disponivel) qtd_disponivel.value = produto.quantidade_total_disponivel || 1;
                // if (inputQuantidade) inputQuantidade.value = produto.quantidade_total_disponivel || 1;
                              
    
                calcularTotal();

                // Limpa campo código e foca novamente
                inputCodigo.value = "";
                inputCodigo.focus();

            } catch (err) {
                console.error("Erro ao consultar produto:", err);
                alert("Não foi possível consultar o produto no momento.");
                limparCampos();
                inputCodigo.focus();
            }
        }

        // ENTER para buscar
        if (inputCodigo) {
            inputCodigo.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    buscarProduto();
                }
            });
            inputCodigo.focus();
        }

    });

    //CALCULA SUBTOTAL
    function atualizarSubtotal() {
    let q = parseFloat(document.getElementById('quantidade').value) || 0;
    let pv = parseFloat(document.getElementById('preco_venda').value) || 0;

    document.getElementById('subtotal').value = (q * pv).toFixed(2);
    }

    document.getElementById('quantidade').addEventListener('input', atualizarSubtotal);



</script>

 <!-- //  FUNÇÃO PRINCIPAL: ADICIONAR ITEM AO CARRINHO -->
<script>
    // ======================================================================
    //  FUNÇÃO PRINCIPAL: ADICIONAR ITEM AO CARRINHO
    // ======================================================================
    function adicionarItemCarrinho(produto) {

        const tabela = document.querySelector('#tabelaCarrinho tbody');

        // Obtém a quantidade digitada no PDV
        const quantidade = Number(document.getElementById('quantidade').value);

        // Preço vindo do produto (já retornado pela busca)
        const preco = Number(produto.preco_venda);

        // Subtotal
        const subtotal = quantidade * preco;

        // Monta a linha do carrinho
        const linha = `
            <tr>
                <td class="text-center item-numero"></td>
                <td>${produto.nome}</td>
                <td class="text-center">${quantidade}</td>
                <td class="text-center">${produto.unidade_sigla ?? ''}</td>
                <td class="text-end">${preco.toFixed(2)}</td>
                <td class="text-end subtotal">${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remover-item">X</button>
                </td>
            </tr>
        `;

        // Insere no carrinho
        tabela.insertAdjacentHTML('beforeend', linha);

        // Atualiza numeração e total
        atualizarNumeroItens();
        atualizarTotal();

        // Limpa quantidade para evitar duplicações acidentais
        document.getElementById('quantidade').value = 1;
    }



    // ======================================================================
    //  FUNÇÃO: REORGANIZA A NUMERAÇÃO DOS ITENS DO CARRINHO
    // ======================================================================
    function atualizarNumeroItens() {
        const linhas = document.querySelectorAll('#tabelaCarrinho tbody tr');
        let contador = 1;

        linhas.forEach(linha => {
            linha.querySelector('.item-numero').textContent = contador;
            contador++;
        });

        // Atualiza contador visual (opcional)
        const totalItensSpan = document.getElementById('totalItens');
        if (totalItensSpan) {
            totalItensSpan.textContent = linhas.length;
        }
    }



    // ======================================================================
    //  FUNÇÃO: RECAUCULAR TOTAL DO CARRINHO
    // ======================================================================
    function atualizarTotal() {
        const subtotais = document.querySelectorAll('.subtotal');
        let total = 0;

        subtotais.forEach(st => {
            total += Number(st.textContent);
        });

        const totalGeral = document.getElementById('totalGeral');
        if (totalGeral) {
            totalGeral.textContent = total.toFixed(2);
        }
    }



    // ======================================================================
    //  EVENTO: REMOVER ITEM DO CARRINHO
    // ======================================================================
    document.addEventListener('click', function(e) {

        if (e.target.classList.contains('remover-item')) {

            // Remove a linha clicada
            e.target.closest('tr').remove();

            // Recalcula tudo
            atualizarNumeroItens();
            atualizarTotal();
        }

    });
</script>

<!-- Mensagem quantidade excedeu estoque -->
<script>
    document.getElementById('quantidade').addEventListener('input', function() {
        let max = parseInt(this.max);
        let valor = parseInt(this.value);
        let msg = document.getElementById('msgEstoque');

        if (valor == max) {
            msg.textContent = "Quantidade digitada excede o estoque disponível (" + max + ").";
            this.value = max;
        } else {
            msg.textContent = "";
        }
    });

</script>

<?php echo $__env->make('pdv.modals.modal_cliente_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_produto_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>