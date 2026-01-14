// resources/js/pdv/produto.js
document.addEventListener("DOMContentLoaded", () => {

    // ===============================
    // ELEMENTOS
    // ===============================
    const inputCodigo        = document.getElementById("codigo_barras");
    const inputDescricao     = document.getElementById("descricao");
    const inputQuantidade    = document.getElementById("quantidade");
    const inputPrecoVenda    = document.getElementById("preco_venda");
    const inputTotalGeral    = document.getElementById("total_geral");
    const qtdDisponivelInput = document.getElementById("qtd_disponivel");
    const imgProduto         = document.getElementById("produto-imagem");
    const tabelaItens        = document.getElementById("lista-itens");
    const totalVenda         = document.getElementById("totalGeral");
    const acoesCarrinho      = document.getElementById("acoes-carrinho");
    const btnDiminuir        = document.getElementById("btnDiminuir");
    const btnRemover         = document.getElementById("btnRemover");
    const btnOcultar         = document.getElementById("btnOcultar");

    window.produtoAtual = null;

    // ===============================
    // ARRASTAR DIV DE AÇÕES
    // ===============================
    let isDragging = false;
    let offsetX = 0, offsetY = 0;
    acoesCarrinho?.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - acoesCarrinho.offsetLeft;
        offsetY = e.clientY - acoesCarrinho.offsetTop;
        acoesCarrinho.style.transition = "none";
    });
    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        acoesCarrinho.style.left = `${e.clientX - offsetX}px`;
        acoesCarrinho.style.top  = `${e.clientY - offsetY}px`;
    });
    document.addEventListener("mouseup", () => {
        if (isDragging) isDragging = false;
    });

    // ===============================
    // FUNÇÕES AUXILIARES
    // ===============================
    function limparCamposProduto() {
        if(inputDescricao) inputDescricao.value = "";
        if(inputPrecoVenda) inputPrecoVenda.value = "";
        if(inputTotalGeral) inputTotalGeral.value = "";
        if(inputQuantidade) inputQuantidade.value = 1;
        if(qtdDisponivelInput) qtdDisponivelInput.value = 0;
        if(imgProduto) imgProduto.src = "/images/produto-sem-imagem.png";
    }

    function calcularTotalProduto() {
        const preco = parseFloat(inputPrecoVenda?.value || 0);
        const qtd   = parseFloat(inputQuantidade?.value || 0);
        if(inputTotalGeral) inputTotalGeral.value = (preco * qtd).toFixed(2);
    }

    function resetarProdutoAtual() {
        window.produtoAtual = null;
        inputQuantidade.value = 1;
        inputCodigo.focus();
    }

    function atualizarTotalGeral(total) {
        if(!totalVenda) return;
        totalVenda.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function atualizarNumeroItens() {
        let contador = 1;
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            linha.children[0].textContent = contador++;
        });
    }

    function atualizarTotalCarrinho() {
        let total = 0;
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            const subtotal = parseFloat(linha.children[6].textContent.replace('R$', '').replace(',', '.')) || 0;
            total += subtotal;
        });
        atualizarTotalGeral(total);
    }

    // ===============================
    // BUSCA DE PRODUTO
    // ===============================
    async function buscarProduto() {
        const codigo = inputCodigo.value.trim();
        if(!codigo) return;

        try {
            const res = await fetch(`/pdv/produto/${encodeURIComponent(codigo)}`, {
                headers: { "Accept": "application/json" }
            });

            if(!res.ok) {
                alert("Produto não encontrado.");
                return;
            }

            const data = await res.json();
            if(data.status !== "ok" || !data.produto) {
                alert(data.mensagem || "Produto não encontrado.");
                return;
            }

            const produto = data.produto;
            window.produtoAtual = produto;

            inputDescricao.value  = produto.nome;
            inputPrecoVenda.value = Number(produto.preco_venda).toFixed(2);
            const qtdDisponivel = produto.quantidade_total_disponivel || 1;
            inputQuantidade.value = 1;
            inputQuantidade.max   = qtdDisponivel;
            if(qtdDisponivelInput) qtdDisponivelInput.value = qtdDisponivel;

            const inputUnidade = document.getElementById("unidade");
            if(inputUnidade) inputUnidade.value = produto.unidade_sigla || "";

            if(imgProduto) imgProduto.src = produto.imagem ? `/storage/${produto.imagem}` : "/images/produto-sem-imagem.png";

            calcularTotalProduto();
            inputCodigo.value = "";
            inputQuantidade.focus();

        } catch(e) {
            console.error(e);
            alert("Erro ao buscar produto.");
        }
    }

    // ===============================
    // ADICIONAR AO CARRINHO
    // ===============================
    // window.adicionarItemCarrinho = function(produto) {
    //     const quantidade = Number(inputQuantidade.value);
    //     const preco = Number(produto.preco_venda);
    //     const loteId = produto.lotes?.[0]?.numero_lote ?? "";

    //     if(quantidade <= 0) {
    //         alert("Informe uma quantidade válida.");
    //         inputQuantidade.focus();
    //         return;
    //     }
    //     if(preco <= 0) {
    //         alert("Produto sem preço de venda.");
    //         return;
    //     }

    //     // Verifica se o produto já existe
    //     const linhas = tabelaItens.querySelectorAll("tr");
    //     for(let linha of linhas) {
    //         if(linha.dataset.produtoId == produto.id && linha.dataset.loteId == loteId) {
    //             const tdQtd = linha.querySelector(".item-quantidade");
    //             const tdSubtotal = linha.querySelector(".subtotal");
    //             const novaQtd = Number(tdQtd.textContent) + quantidade;
    //             if(novaQtd > Number(inputQuantidade.max)) {
    //                 alert("Estoque insuficiente.");
    //                 return;
    //             }
    //             tdQtd.textContent = novaQtd;
    //             tdSubtotal.textContent = (novaQtd * preco).toFixed(2);
    //             atualizarNumeroItens();
    //             atualizarTotalCarrinho();
    //             resetarProdutoAtual();
    //             limparCamposProduto();
    //             return;
    //         }
    //     }

    //     const subtotal = quantidade * preco;
    //     tabelaItens.insertAdjacentHTML("beforeend", `
    //         <tr class="item-carrinho"
    //             data-produto="${produto.id}"
    //             data-lote="${loteId}"
    //             data-qtd="${quantidade}"
    //             data-valor="${preco}">
    //             <td class="item-numero text-center" style="font-size:18px; font-weight:bold;"></td>
    //             <td class="item-lote" style="font-size:18px; font-weight:bold;">${loteId}</td>
    //             <td class="item-descricao" style="font-size:18px; font-weight:bold;">${produto.nome}</td>
    //             <td class="item-quantidade text-center" style="font-size:18px; font-weight:bold;">${quantidade}</td>
    //             <td class="text-center" style="font-size:18px; font-weight:bold;">${produto.unidade_sigla ?? ""}</td>
    //             <td class="item-preco text-end" style="font-size:18px; font-weight:bold;">${preco.toFixed(2)}</td>
    //             <td class="subtotal text-end" style="font-size:18px; font-weight:bold;">${subtotal.toFixed(2)}</td>
    //         </tr>
    //     `);


    //     atualizarNumeroItens();
    //     atualizarTotalCarrinho();
    //     resetarProdutoAtual();
    //     limparCamposProduto();
    // }
    window.adicionarItemCarrinho = function(produto) {
    const quantidade = Number(inputQuantidade.value);
    const preco = Number(produto.preco_venda);

    if(quantidade <= 0) {
        alert("Informe uma quantidade válida.");
        inputQuantidade.focus();
        return;
    }
    if(preco <= 0) {
        alert("Produto sem preço de venda.");
        return;
    }

    // ===============================
    // SELECIONAR LOTE VÁLIDO
    // ===============================
    let loteSelecionado = null;
    if(Array.isArray(produto.lotes)) {
        // Prioriza lotes com quantidade disponível > 0 e não vencidos
        loteSelecionado = produto.lotes.find(lote => {
            const quantidadeLote = Number(lote.quantidade_disponivel || 0);
            const vencimento = lote.data_vencimento ? new Date(lote.data_vencimento) : null;
            const hoje = new Date();
            return quantidadeLote > 0 && (!vencimento || vencimento >= hoje);
        });
    }

    if(!loteSelecionado) {
        alert("Nenhum lote disponível para este produto, Precisa criar um lote antes de adicionar ao carrinho.");
        return;
    }

    const loteId = loteSelecionado.id; // usa ID interno do lote
    const qtdDisponivelLote = Number(loteSelecionado.quantidade_disponivel);

    if(quantidade > qtdDisponivelLote) {
        alert(`Quantidade solicitada excede o lote disponível (${qtdDisponivelLote}).`);
        inputQuantidade.focus();
        return;
    }

    // ===============================
    // VERIFICA SE PRODUTO + LOTE JÁ EXISTE NO CARRINHO
    // ===============================
    const linhas = tabelaItens.querySelectorAll("tr");
    for(let linha of linhas) {
        if(linha.dataset.produto == produto.id && linha.dataset.lote == loteId) {

            const tdQtd = linha.querySelector(".item-quantidade");
            const tdSubtotal = linha.querySelector(".subtotal");
            const novaQtd = Number(tdQtd.textContent) + quantidade;

            if(novaQtd > qtdDisponivelLote) {
                alert("Estoque insuficiente neste lote.");
                return;
            }

            tdQtd.textContent = novaQtd;
            tdSubtotal.textContent = (novaQtd * preco).toFixed(2);
            atualizarNumeroItens();
            atualizarTotalCarrinho();
            resetarProdutoAtual();
            limparCamposProduto();
            return;
        }
    }

    // ===============================
    // ADICIONAR NOVO ITEM NO CARRINHO
    // ===============================
    const subtotal = quantidade * preco;
    tabelaItens.insertAdjacentHTML("beforeend", `
        <tr class="item-carrinho"
            data-produto="${produto.id}"
            data-lote="${loteId}"
            data-qtd="${quantidade}"
            data-valor="${preco}">
            <td class="item-numero text-center" style="font-size:18px; font-weight:bold;"></td>
             <td class="item-lote" style="font-size:18px; font-weight:bold;">${loteId}</td>
            <td class="item-descricao" style="font-size:18px; font-weight:bold;">${produto.nome}</td>
            <td class="item-quantidade text-center" style="font-size:18px; font-weight:bold;">${quantidade}</td>
            <td class="text-center" style="font-size:18px; font-weight:bold;">${produto.unidade_sigla ?? ""}</td>
            <td class="item-preco text-end" style="font-size:18px; font-weight:bold;">${preco.toFixed(2)}</td>
            <td class="subtotal text-end" style="font-size:18px; font-weight:bold;">${subtotal.toFixed(2)}</td>
        </tr>
    `);

    atualizarNumeroItens();
    atualizarTotalCarrinho();
    resetarProdutoAtual();
    limparCamposProduto();
    
    }

    // ===============================
    // EVENTOS
    // ===============================
    inputCodigo?.addEventListener("keydown", e => {
        if(e.key === "Enter") {
            e.preventDefault();
            buscarProduto();
        }
    });

    inputQuantidade?.addEventListener("input", () => {
        const max = Number(inputQuantidade.max);
        if(Number(inputQuantidade.value) > max) inputQuantidade.value = max;
        calcularTotalProduto();
    });


    inputQuantidade?.addEventListener("keydown", e => {
        if (e.key !== "Enter") return;
        e.preventDefault();

        const produto = window.produtoAtual; // captura o estado uma vez

        if (!produto) {
            return; // NÃO alertar aqui
        }

        adicionarItemCarrinho(produto);
    });



    document.addEventListener("keydown", e => {
        if(e.key === "F3") {
            e.preventDefault();
            inputCodigo.focus();
        }
    });

    // ===============================
    // EDIÇÃO DO CARRINHO
    // ===============================
    acoesCarrinho.classList.add("d-none");

    document.addEventListener("click", (e) => {
        const linhaSelecionada = getLinhaSelecionada();
        if (linhaSelecionada && !linhaSelecionada.contains(e.target) && !acoesCarrinho.contains(e.target)) {
            linhaSelecionada.classList.remove("selecionada", "table-warning");
            atualizarVisibilidadeBotoes();
        }
    });

    function getLinhaSelecionada() {
        return tabelaItens.querySelector("tr.table-warning:not(.d-none)");
    }

    function atualizarVisibilidadeBotoes() {
        const linha = getLinhaSelecionada();
        if (linha) acoesCarrinho.classList.remove("d-none");
        else acoesCarrinho.classList.add("d-none");
    }

    tabelaItens?.addEventListener("click", e => {
        const linha = e.target.closest("tr");
        if(!linha || linha.classList.contains("d-none")) return;
        tabelaItens.querySelectorAll('tr.linha-carrinho').forEach(l => l.classList.remove('selecionada'));
        linha.classList.add('selecionada', "table-warning");
        atualizarVisibilidadeBotoes();
    });

    btnDiminuir?.addEventListener("click", () => {
        const linha = getLinhaSelecionada();
        if(!linha) return;
        const tdQtd = linha.children[2];
        let qtd = parseInt(tdQtd.textContent);
        if(qtd > 1) {
            tdQtd.textContent = qtd - 1;
            atualizarSubTotal(linha);
        } else if(confirm("Quantidade é 1. Deseja remover o item?")) {
            linha.remove();
            atualizarTotalVenda();
            reordenarItens();
            atualizarVisibilidadeBotoes();
        }
    });

    btnRemover?.addEventListener("click", () => {
        const linha = getLinhaSelecionada();
        if(!linha) return;
        if(confirm("Deseja remover o item selecionado?")) {
            linha.remove();
            atualizarTotalVenda();
            reordenarItens();
            atualizarVisibilidadeBotoes();
        }
    });

    btnOcultar?.addEventListener("click", () => {
        const linha = getLinhaSelecionada();
        if (!linha) return;
        linha.classList.remove("selecionada", "table-warning");
        atualizarVisibilidadeBotoes();
    });

    function atualizarSubTotal(linha) {
        const qtd = parseInt(linha.children[2].textContent);
        const preco = parseFloat(linha.children[4].textContent.replace('R$', '').replace(',', '.'));
        linha.children[5].textContent = 'R$ ' + (qtd * preco).toFixed(2).replace('.', ',');
        atualizarTotalVenda();
    }

    function atualizarTotalVenda() {
        let total = 0;
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            const subtotal = parseFloat(linha.children[5].textContent.replace('R$', '').replace(',', '.')) || 0;
            total += subtotal;
        });
        if(totalVenda) totalVenda.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    }

    function reordenarItens() {
        let contador = 1;
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            linha.children[0].textContent = contador++;
        });
    }

});

