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
    // JS para arrastar a div
    const acoes = document.getElementById("acoes-carrinho");
    let isDragging = false;
    let offsetX = 0, offsetY = 0;

    acoes.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - acoes.offsetLeft;
        offsetY = e.clientY - acoes.offsetTop;
        acoes.style.transition = "none"; // remove animação enquanto arrasta
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        acoes.style.left = `${e.clientX - offsetX}px`;
        acoes.style.top  = `${e.clientY - offsetY}px`;
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
            const subtotal = parseFloat(linha.children[5].textContent.replace('R$', '').replace(',', '.')) || 0;
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

            // Preenche inputs
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
    window.adicionarItemCarrinho = function(produto) {
        const quantidade = Number(inputQuantidade.value);
        const preco = Number(produto.preco_venda);
        const loteId = produto.lotes?.[0]?.numero_lote ?? "";

        if(quantidade <= 0) {
            alert("Informe uma quantidade válida.");
            inputQuantidade.focus();
            return;
        }
        if(preco <= 0) {
            alert("Produto sem preço de venda.");
            return;
        }

        // Verifica se o produto já existe
        const linhas = tabelaItens.querySelectorAll("tr");
        for(let linha of linhas) {
            if(linha.dataset.produtoId == produto.id && linha.dataset.loteId == loteId) {
                const tdQtd = linha.querySelector(".item-quantidade");
                const tdSubtotal = linha.querySelector(".subtotal");
                const novaQtd = Number(tdQtd.textContent) + quantidade;
                if(novaQtd > Number(inputQuantidade.max)) {
                    alert("Estoque insuficiente.");
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

        const subtotal = quantidade * preco;
        tabelaItens.insertAdjacentHTML("beforeend", `
            <tr class="linha-carrinho" data-produto-id="${produto.id}" data-lote-id="${loteId}">
                <td class="item-numero text-center" style="font-size:20px; font-weight:bold;"></td>
                <td class="text-center" style="font-size:20px; font-weight:bold;">${produto.nome}</td>
                <td class="item-quantidade text-center" style="font-size:20px; font-weight:bold;">${quantidade}</td>
                <td class="text-center" style="font-size:20px; font-weight:bold;">${produto.unidade_sigla ?? ""}</td>
                <td class="item-preco text-end" style="font-size:20px; font-weight:bold;">${preco.toFixed(2)}</td>
                <td class="subtotal text-end subtotal" style="font-size:20px; font-weight:bold;">${subtotal.toFixed(2)}</td>
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
        if(e.key !== "Enter") return;
        e.preventDefault();
        if(!window.produtoAtual) {
            alert("Nenhum produto carregado. Leia o código de barras.");
            return;
        }
        adicionarItemCarrinho(window.produtoAtual);
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
   
    // Inicialmente oculta os botões
    
     acoesCarrinho.classList.add("d-none");
  // Ocultar os botões ao clicar fora do carrinho
    document.addEventListener("click", (e) => {
        const linhaSelecionada = getLinhaSelecionada();

        // Se existe uma linha selecionada e o clique NÃO foi nela nem na div de ações
        if (linhaSelecionada && !linhaSelecionada.contains(e.target) && !acoesCarrinho.contains(e.target)) {
            // Remove destaque da linha
            linhaSelecionada.classList.remove("selecionada", "table-warning");
            // Oculta os botões
            atualizarVisibilidadeBotoes();
        }
    });

    // Função para pegar a linha selecionada visível
    function getLinhaSelecionada() {
        return tabelaItens.querySelector("tr.table-warning:not(.d-none)");
    }

    // Atualiza visibilidade dos botões
    function atualizarVisibilidadeBotoes() {
        const linha = getLinhaSelecionada();
        if (linha) acoesCarrinho.classList.remove("d-none");
        else acoesCarrinho.classList.add("d-none");
    }

    // Evento click na linha do carrinho
    tabelaItens?.addEventListener("click", e => {
        const linha = e.target.closest("tr");
        if(!linha || linha.classList.contains("d-none")) return;

        // Remove seleção das outras linhas
        tabelaItens.querySelectorAll('tr.linha-carrinho').forEach(l => l.classList.remove('selecionada'));
        linha.classList.add('selecionada');

        // Marca a linha clicada
        linha.classList.add("table-warning");

        // Exibe os botões
        atualizarVisibilidadeBotoes();

        // Se existe uma linha selecionada e o clique NÃO foi nela nem na div de ações
        if (linhaSelecionada && !linhaSelecionada.contains(e.target) && !acoesCarrinho.contains(e.target)) {
            // Remove destaque da linha
            linhaSelecionada.classList.remove("selecionada", "table-warning");
            // Oculta os botões
            atualizarVisibilidadeBotoes();
        }
    });

    // Diminuir quantidade
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

    // Remover item
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

    // Ocultar item (apenas esconde os botões, não remove a linha)
    btnOcultar?.addEventListener("click", () => {
        const linha = getLinhaSelecionada();
        if (!linha) return;

        // Remove destaque da linha
        linha.classList.remove("selecionada", "table-warning");

        // Oculta os botões
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
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            const subtotal = parseFloat(linha.children[5].textContent.replace('R$', '').replace(',', '.')) || 0;
            total += subtotal;
        });
        if(totalVenda) totalVenda.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    }

    // Reordenar coluna Item
    function reordenarItens() {
        let contador = 1;
        tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
            linha.children[0].textContent = contador++;
        });
    }
});

