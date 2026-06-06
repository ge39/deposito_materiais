// ========================================================== //
// 🪙 SOM DE BIP NATIVO (FLUXO ULTRA RÁPIDO VIA FREQUÊNCIA)   //
// ========================================================== //
window.emitirBipPDV = function() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.type = 'sine'; 
        oscillator.frequency.value = 1150; // Tom clássico agudo de leitor de caixa
        gainNode.gain.setValueAtTime(0.08, audioCtx.currentTime); 

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.07); 
    } catch (e) {
        console.warn("Áudio bloqueado ou não suportado:", e);
    }
};

// 🔥 PROTEÇÃO ABSOLUTA CONTRA DUPLICIDADE DE ARQUIVO NO VITE
// Se este script já foi carregado uma vez nesta página, cancela a segunda inicialização!
if (window.__pdvProdutoJsCarregado) {
    console.warn("produto.js já estava ativo na memória. Segunda instância abortada.");
} else {
    window.__pdvProdutoJsCarregado = true;

    document.addEventListener("DOMContentLoaded", () => {

        // ========================================== //
        // ELEMENTOS DO DOM                           //
        // ========================================== //
        const inputCodigo        = document.getElementById("codigo_barras");
        const inputId_produto    = document.getElementById("id_produto");
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

        let linhaSelecionada = null;
        window.produtoAtual = null;

        // Força o foco inicial na barra amarela
        if (inputCodigo) inputCodigo.focus();

        // ========================================== //
        // FUNÇÕES AUXILIARES                         //
        // ========================================== //
        function resetarProdutoAtual() {
            window.produtoAtual = null;
            if (inputCodigo) inputCodigo.value = "";
            if (inputId_produto) inputId_produto.value = "";
            if (inputDescricao) inputDescricao.value = ""; 
            if (inputPrecoVenda) inputPrecoVenda.value = ""; 
            if (inputTotalGeral) inputTotalGeral.value = "";
            
            const inputUnidade = document.getElementById("unidade");
            if (inputUnidade) inputUnidade.value = "";

            if (inputQuantidade) {
                inputQuantidade.value = 1;
                inputQuantidade.removeAttribute("max"); 
            }
           
            if (inputCodigo) inputCodigo.focus();
        }

        function calcularTotalProduto() {
            const preco = parseFloat(inputPrecoVenda?.value || 0);
            const qtd   = parseFloat(inputQuantidade?.value || 0);
            if(inputTotalGeral) inputTotalGeral.value = (preco * qtd).toFixed(2);
        }

        function atualizarNumeroItens() {
            let contador = 1;
            tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
                if (linha.children && linha.children[0]) {
                    linha.children[0].textContent = contador++;
                }
            });
        }

        function atualizarTotalCarrinho() {
            let total = 0;
            tabelaItens.querySelectorAll("tr:not(.d-none)").forEach(linha => {
                if (linha.children && linha.children[6]) {
                    let textoValor = linha.children[6].textContent
                        .replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
                    total += parseFloat(textoValor) || 0;
                }
            });
            if (totalVenda) {
                totalVenda.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }
        }

        // ========================================================== //
        // 🛒 OPERAÇÃO DE INSERÇÃO NO CARRINHO (VERSÃO ULTRA PRO)     //
        // ========================================================== //
        window.adicionarItemCarrinho = function(produto) {
            // 1️⃣ CAPTURA A QUANTIDADE REAL DIGITADA NO INPUT ANTES DO RESET
            const inputQtdElement = document.getElementById("quantidade");
            const quantidade = inputQtdElement ? Number(inputQtdElement.value) : 1;
            const preco = Number(produto.preco_venda) || 0;

            // Validação de segurança primária
            if (quantidade <= 0 || preco <= 0) {
                alert("Quantidade ou preço inválidos para inserção.");
                return;
            }

            // 2️⃣ SELEÇÃO DO LOTE DISPONÍVEL (Regra nativa mantida)
            let loteSelecionado = null;
            if (Array.isArray(produto.lotes)) {
                loteSelecionado = produto.lotes.find(lote => {
                    const qtd = Number(lote.quantidade_disponivel || 0);
                    const vencimento = lote.data_vencimento ? new Date(lote.data_vencimento) : null;
                    const hoje = new Date();
                    return qtd > 0 && (!vencimento || vencimento >= hoje);
                });
            }

            if (!loteSelecionado) {
                alert("Nenhum lote disponível ou dentro da validade para este produto.");
                return;
            }

            const loteId = loteSelecionado.id;
            const qtdDisponivelLote = Number(loteSelecionado.quantidade_disponivel);

            // Validação de teto do estoque físico do lote
            if (quantidade > qtdDisponivelLote) {
                alert(`Quantidade excede o lote disponível em estoque (${qtdDisponivelLote}).`);
                return;
            }

            // Inicializa a memória do carrinho global se necessário
            if (!window.carrinho) { window.carrinho = []; }

            // 3️⃣ BUSCA SE O ITEM JÁ EXISTE NO CARRINHO (Mesmo Produto + Mesmo Lote)
            const itemExistente = window.carrinho.find(i => i.produto_id == produto.id && i.lote_id == loteId);

            if (itemExistente) {
                const novaQtd = itemExistente.quantidade + quantidade;
                
                if (novaQtd > qtdDisponivelLote) {
                    alert(`Estoque insuficiente neste lote para somar esta quantidade.`);
                    return;
                }
                
                // Atualiza a memória global
                itemExistente.quantidade = novaQtd;

                // Atualiza a interface visual localizando a linha por atributos e alterando via classe CSS
                const linhaVisual = tabelaItens.querySelector(`tr[data-produto="${produto.id}"][data-lote="${loteId}"]`);
                if (linhaVisual) {
                    const campoQtd = linhaVisual.querySelector('.item-quantidade');
                    const campoSubtotal = linhaVisual.querySelector('.subtotal');

                    if (campoQtd) campoQtd.textContent = novaQtd;
                    if (campoSubtotal) {
                        campoSubtotal.textContent = (novaQtd * preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    }
                }
            } else {
                // 4️⃣ INSERÇÃO DE NOVO ITEM NO CARRINHO
                window.carrinho.push({
                    produto_id: produto.id,
                    lote_id: loteId,
                    quantidade: quantidade,
                    preco_unitario: preco,
                    desconto: 0
                });

                const numeroItem = tabelaItens.querySelectorAll("tr").length + 1;
                const novaLinha = document.createElement("tr");
                novaLinha.dataset.produto = produto.id;
                novaLinha.dataset.lote = loteId;
                novaLinha.style.cursor = "pointer";

                // Renderiza seu HTML nativo funcional perfeitamente alinhado
                novaLinha.innerHTML = `
                    <td>${numeroItem}</td>
                    <td>${produto.id}</td>
                    <td class="text-start">${produto.nome}</td>
                    <td>R$ ${preco.toFixed(2).replace('.', ',')}</td>
                    <td class="item-quantidade">${quantidade}</td>
                    <td>${produto.unidade_sigla || 'UN'}</td>
                    <td class="subtotal fw-bold">${(quantidade * preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                    <td class="text-muted d-none">Lote: ${loteSelecionado.numero_lote || 'S/L'}</td>
                `;
                tabelaItens.appendChild(novaLinha);
            }

            // 5️⃣ ATUALIZAÇÃO DOS INDICADORES FINANCEIROS E FEEDBACK VISUAL/SONORO
            atualizarNumeroItens();
            atualizarTotalCarrinho();
            
            if (typeof window.emitirBipPDV === "function") {
                window.emitirBipPDV();
            }

            // 6⃣ LIMPA O FORMULÁRIO DO TOPO E DEVOLVE O FOCO PARA A PRÓXIMA COMPRA
            resetarProdutoAtual();

            // 🎯 GATILHO ESPELHO DIRETO E CIRÚRGICO INTEGRADO AO PDV_STORAGE
            try {
                if (window.carrinho && window.carrinho.length > 0) {
                    // 🔄 Utiliza o método de salvar do seu PdvStorage centralizado
                    if (typeof PdvStorage !== 'undefined') {
                        PdvStorage.salvarCarrinho(window.carrinho);
                    } else {
                        localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                    }
                    
                    // 📊 EXIBIÇÃO NO F12
                    // console.log("➡️ MOVIMENTAÇÃO DETECTADA NO INPUT!");
                    console.log("💾 LOCALSTORAGE ESPELHADO COM SUCESSO:", window.carrinho);
                } else {
                    // 🧹 Limpa os dados de forma inteligente se o carrinho ficar vazio
                    if (typeof PdvStorage !== 'undefined') {
                        PdvStorage.limparPdv();
                    } else {
                        localStorage.removeItem('pdv_carrinho_atual');
                    }
                }
            } catch (errStorage) {
                console.error("Falha ao espelhar LocalStorage:", errStorage);
            }

        };


        // ========================================================== //
        // 📡 GATILHO: LEITOR DE CÓDIGO DE BARRAS / BARRA PRINCIPAL   //
        // ========================================================== //
        inputCodigo?.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                e.stopPropagation(); // Trava barreira no DOM

                let valorInput = inputCodigo.value.trim();
                if (!valorInput) return;

                let quantidadeDefinida = 1;
                let codigoFinal = valorInput;

                // Regra de negócio do multiplicador (Qtd * Código)
                if (valorInput.includes("*")) {
                    const partes = valorInput.split("*");
                    const qtdInformada = Number(partes[0]);
                    
                    if (!isNaN(qtdInformada) && qtdInformada > 0) {
                        quantidadeDefinida = qtdInformada;
                        codigoFinal = partes[1] ? partes[1].trim() : "";
                    }
                }

                if (!codigoFinal) {
                    alert("Código de barras ou formato multiplicador inválido.");
                    inputCodigo.value = "";
                    return;
                }

                if (inputQuantidade) {
                    inputQuantidade.value = quantidadeDefinida;
                }

                // Executa a requisição direta isolada de escopo usando o código higienizado
                fetch(`/pdv/produto/${encodeURIComponent(codigoFinal)}`, { headers: { "Accept": "application/json" } })
                    .then(res => res.json())
                   .then(dataRes => {
                        if (dataRes.status === "ok" && dataRes.produto) {

                            const produto = dataRes.produto;
                            window.produtoAtual = produto;

                            // Imagem do produto
                            const imgProduto = document.getElementById("produto-imagem");

                            if (imgProduto) {
                                if (produto.imagem && produto.imagem.trim() !== "") {
                                    imgProduto.src = "/" + produto.imagem;
                                    imgProduto.style.display = "block";
                                } else {
                                    imgProduto.src = "/image/produto-sem-imagem.png";
                                    imgProduto.style.display = "block";
                                }
                            }

                            if (inputId_produto) inputId_produto.value = produto.id;
                            if (inputDescricao) inputDescricao.value = produto.nome;
                            if (inputPrecoVenda) inputPrecoVenda.value = Number(produto.preco_venda).toFixed(2);

                            const qtdDisp = produto.quantidade_total_disponivel || 1;
                            if (qtdDisponivelInput) qtdDisponivelInput.value = qtdDisp;

                            const inputUnidade = document.getElementById("unidade");
                            if (inputUnidade) {
                                inputUnidade.value = produto.unidade || produto.unidade_medida || "UN";
                            }

                            calcularTotalProduto();
                            window.adicionarItemCarrinho(produto);

                        } else {
                            alert(dataRes.mensagem || "Produto não encontrado.");
                            if (inputCodigo) inputCodigo.value = "";
                        }
                    })  
                    
                    .catch(err => {
                        console.error(err);
                        if (inputCodigo) inputCodigo.value = "";
                    });
            }
        }); // 🌟 O EVENTO DO ENTER FECHA EXATAMENTE AQUI!

        // ========================================================== //
        // 🎯 SEPARADO E ISOLADO: CONTROLE DE MANUTENÇÃO DO CARRINHO  //
        // ========================================================== //

        // 1️⃣ FAZ A TABELA ESCUTAR O SEU CLIQUE E MARCAR A LINHA SELECIONADA
        tabelaItens?.addEventListener("click", function(e) {
            const linha = e.target.closest("tr");
            if (!linha || linha.rowIndex === 0) return; 

            tabelaItens.querySelectorAll("tr").forEach(tr => tr.classList.remove("table-active"));

            if (linhaSelecionada === linha) {
                linhaSelecionada = null;
                if (acoesCarrinho) acoesCarrinho.classList.add("d-none");
                return;
            }

            linhaSelecionada = linha;
            linha.classList.add("table-active");

            if (acoesCarrinho) {
                acoesCarrinho.classList.remove("d-none");
            }
        });

        // 2️⃣ PROGRAMA O BOTÃO DE DIMINUIR QUANTIDADE (-1)
      // ========================================================== //
        // ➖ AÇÃO: DIMINUIR QUANTIDADE (BLINDADO CONTRA REPETIÇÃO)   //
        // ========================================================== //
        btnDiminuir?.replaceWith(btnDiminuir.cloneNode(true)); // Limpa escutas fantasmas empilhados
        document.getElementById("btnDiminuir")?.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation(); // 🌟 Impede que o clique suba e execute duas vezes

            if (!linhaSelecionada) {
                alert("Clique em um item da tabela primeiro para selecioná-lo.");
                return;
            }

            const produtoId = linhaSelecionada.dataset.produto;
            const loteId    = linhaSelecionada.dataset.lote;

            const item = window.carrinho.find(i => i.produto_id == produtoId && i.lote_id == loteId);

            if (item) {
                item.quantidade--; // Reduz exatamente 1

                if (item.quantidade <= 0) {
                    window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));
                    linhaSelecionada.remove();
                    linhaSelecionada = null;
                    if (acoesCarrinho) acoesCarrinho.classList.add("d-none");
                } else {
                    const campoQtd = linhaSelecionada.querySelector('.item-quantidade');
                    const campoSubtotal = linhaSelecionada.querySelector('.subtotal');
                    const precoUnitario = Number(item.preco_unitario);

                    if (campoQtd) campoQtd.textContent = item.quantidade;
                    if (campoSubtotal) {
                        campoSubtotal.textContent = (item.quantidade * precoUnitario).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    }
                }

                atualizarNumeroItens();
                atualizarTotalCarrinho();
                if (inputCodigo) inputCodigo.focus();
            }
        });

        // ========================================================== //
        // ❌ AÇÃO: REMOVER ITEM INTEIRO (BLINDADO CONTRA REPETIÇÃO)  //
        // ========================================================== //
        btnRemover?.replaceWith(btnRemover.cloneNode(true)); // Limpa escutas fantasmas empilhados
        document.getElementById("btnRemover")?.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation(); // 🌟 Impede execução duplicada no DOM

            if (!linhaSelecionada) {
                alert("Clique em um item da tabela primeiro para selecioná-lo.");
                return;
            }

            const produtoId = linhaSelecionada.dataset.produto;
            const loteId    = linhaSelecionada.dataset.lote;

            window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));

            linhaSelecionada.remove();
            linhaSelecionada = null;

            if (acoesCarrinho) acoesCarrinho.classList.add("d-none");

            atualizarNumeroItens();
            atualizarTotalCarrinho();
            if (inputCodigo) inputCodigo.focus();
        });


        // 3️⃣ PROGRAMA O BOTÃO DE REMOVER O PRODUTO INTEIRO
        btnRemover?.addEventListener("click", function() {
            if (!linhaSelecionada) {
                alert("Clique em um item da tabela primeiro para selecioná-lo.");
                return;
            }

            const produtoId = linhaSelecionada.dataset.produto;
            const loteId    = linhaSelecionada.dataset.lote;

            // Deleta o item direto do array de memória
            window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));

            // Remove a linha visual do HTML
            linhaSelecionada.remove();
            linhaSelecionada = null;

            if (acoesCarrinho) acoesCarrinho.classList.add("d-none");

            atualizarNumeroItens();
            atualizarTotalCarrinho();
            if (inputCodigo) inputCodigo.focus();
        });

        // 4️⃣ PROGRAMA O BOTÃO DE OCULTAR / CANCELAR A SELEÇÃO
        btnOcultar?.addEventListener("click", function() {
            tabelaItens?.querySelectorAll("tr").forEach(tr => tr.classList.remove("table-active"));
            linhaSelecionada = null;
            if (acoesCarrinho) acoesCarrinho.classList.add("d-none");
            if (inputCodigo) inputCodigo.focus();
        });


        inputQuantidade?.addEventListener("input", calcularTotalProduto);

        // ========================================== //
        // PAINEL DE AÇÕES FLUTUANTES (1 EM 1)        //
        // ========================================== //
        tabelaItens?.addEventListener("click", (e) => {
            const linha = e.target.closest("tr");
            if (!linha || !tabelaItens.contains(linha)) return;
            tabelaItens.querySelectorAll("tr").forEach(tr => tr.classList.remove("table-active"));
            linhaSelecionada = linha;
            linhaSelecionada.classList.add("table-active");
            if (acoesCarrinho) acoesCarrinho.style.display = "block";
        });

        btnDiminuir?.addEventListener("click", (e) => {
            e.preventDefault();
            if (!linhaSelecionada) return;

            const prodId = linhaSelecionada.dataset.produto;
            const loteId = linhaSelecionada.dataset.lote;
            const index = window.carrinho?.findIndex(i => i.produto_id == prodId && i.lote_id == loteId);
            if (index === -1 || index === undefined) return;

            if (window.carrinho[index].quantidade <= 1) {
                window.carrinho.splice(index, 1);
                linhaSelecionada.remove();
                linhaSelecionada = null;
                if (acoesCarrinho) acoesCarrinho.style.display = "none";
            } else {
                window.carrinho[index].quantidade--;
                // Atualiza o LocalStorage e a linha visual correspondente
                localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                const novaQtd = window.carrinho[index].quantidade;
                linhaSelecionada.children[4].textContent = novaQtd; 
                linhaSelecionada.children[6].textContent = (novaQtd * window.carrinho[index].preco_unitario).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); 
            }
            atualizarTotalCarrinho();
            atualizarNumeroItens();
        });

        btnRemover?.addEventListener("click", (e) => {
            e.preventDefault();
            if (!linhaSelecionada) return;
            if (confirm("Deseja remover este item do carrinho?")) {
                const prodId = linhaSelecionada.dataset.produto;
                const loteId = linhaSelecionada.dataset.lote;
                window.carrinho = window.carrinho.filter(i => !(i.produto_id == prodId && i.lote_id == loteId));
                // Atualiza o LocalStorage e remove a linha visual correspondente
                localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                linhaSelecionada.remove();
                linhaSelecionada = null;
                if (acoesCarrinho) acoesCarrinho.style.display = "none";
                atualizarTotalCarrinho();
                atualizarNumeroItens();
            }
        });

        btnOcultar?.addEventListener("click", (e) => {
            e.preventDefault();
            if (linhaSelecionada) {
                linhaSelecionada.classList.remove("table-active");
                linhaSelecionada = null;
            }
            if (acoesCarrinho) acoesCarrinho.style.display = "none";
        });
    });

   // =======================================================================
    // 🎯 RESGATAR DADOS DO LOCALSTORAGE NO F5 (REESCRITO COM TODAS AS LINHAS E MULTIPLICADOR)
    // =======================================================================
    window.resgatarCarrinhoF5 = function() {
        try {
            const salvo = localStorage.getItem('pdv_carrinho_atual');
            if (!salvo) return;

            const itensSalvos = JSON.parse(salvo);
            if (!Array.isArray(itensSalvos) || itensSalvos.length === 0) return;

            console.log("📂 [F5 RESTORE] Reconstruindo carrinho na marra...", itensSalvos);

            // Limpa a array temporária e aponta para o elemento da tabela do seu PDV
            window.carrinho = [];
            const tabelaItens = document.getElementById("lista-itens");
            if (!tabelaItens) return;

            itensSalvos.forEach(item => {
                // Lógica de Renderização Direta (Garante a tela cheia mesmo se o fetch falhar)
                function injetarLinhaNaTela(nomeProduto, siglaUnidade) {
                    const precoVenda = Number(item.preco_unitario || 0);
                    const qtdSalva = Number(item.quantidade || 1);

                    // 1. Alimenta a memória global exatamente no formato esperado pelo Laravel
                    window.carrinho.push({
                        produto_id: parseInt(item.produto_id),
                        lote_id: item.lote_id || 0,
                        quantidade: qtdSalva,
                        preco_unitario: precoVenda,
                        desconto: Number(item.desconto || 0),
                        nome: nomeProduto // Mantém o nome na memória ativa do script
                    });

                    // 2. INJEÇÃO DIRETA NO HTML: Desenha a linha fisicamente na tabela com todas as colunas
                    const numeroItem = tabelaItens.querySelectorAll("tr").length + 1;
                    const novaLinha = document.createElement("tr");
                    novaLinha.dataset.produto = item.produto_id;
                    novaLinha.dataset.lote = item.lote_id || 0;
                    novaLinha.style.cursor = "pointer";

                    novaLinha.innerHTML = `
                        <td>${numeroItem}</td>
                        <td>${item.lote_id || 'OK'}</td>
                        <td class="text-start">${nomeProduto}</td>
                        <td>R$ ${precoVenda.toFixed(2).replace('.', ',')}</td>
                        <td class="item-quantidade">${qtdSalva}</td>
                        <td>${siglaUnidade}</td>
                        <td class="subtotal fw-bold">${(qtdSalva * precoVenda).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                        <td class="text-muted d-none">Lote: OK</td>
                    `;
                    tabelaItens.appendChild(novaLinha);

                    // 🛠️ RECALCULA O RODAPÉ AUTOMATICAMENTE
                    let contadorSequencial = 1;
                    tabelaItens.querySelectorAll("tr").forEach(linha => {
                        const primeiraCelula = linha.querySelector("td");
                        if (primeiraCelula) primeiraCelula.textContent = contadorSequencial++;
                    });

                    let subtotalAcumulado = 0;
                    tabelaItens.querySelectorAll("tr").forEach(linha => {
                        const campoSubtotal = linha.querySelector(".subtotal");
                        if (campoSubtotal) {
                            let textoValor = campoSubtotal.textContent
                                .replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
                            subtotalAcumulado += parseFloat(textoValor) || 0;
                        }
                    });

                    const labelTotalGeral = document.getElementById("totalGeral") || document.getElementById("total_geral") || document.getElementById("inputTotalGeral");
                    if (labelTotalGeral) {
                        if (labelTotalGeral.tagName === 'INPUT') {
                            labelTotalGeral.value = subtotalAcumulado.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        } else {
                            labelTotalGeral.textContent = subtotalAcumulado.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        }
                    }
                }

                // Tenta buscar o nome atualizado do produto no banco
                fetch(`/pdv/produto/${item.produto_id}`, { headers: { "Accept": "application/json" } })
                    .then(res => {
                        // Se o Laravel retornar erro (como 404), pula direto para a contingência
                        if (!res.ok) throw new Error("Rota de produto inválida ou não encontrada");
                        return res.json();
                    })
                    .then(dataRes => {
                        if (dataRes.status === "ok" && dataRes.produto) {
                            // Plano A: Renderiza com o nome real vindo do banco de dados
                            injetarLinhaNaTela(dataRes.produto.nome, dataRes.produto.unidade_sigla || 'UN');
                        } else {
                            // Plano B: Se a resposta vier vazia, usa o nome salvo no LocalStorage
                            injetarLinhaNaTela(item.nome || item.descricao || `Produto #${item.produto_id}`, 'UN');
                        }
                    })
                    .catch(err => {
                        console.warn("⚠️ Rota /pdv/produto/ falhou. Ativando renderização de contingência do LocalStorage...", err.message);
                        // 🎯 PLANO C CORRIGIDO: Puxa o nome real armazenado no JSON em vez do texto genérico fixo
                        injetarLinhaNaTela(item.nome || item.descricao || `Produto #${item.produto_id}`, 'UN');
                    });
            });

        } catch (error) {
            console.error("Falha ao ler dados no F5:", error);
        }
    };

    // Gatilho: Executa o script de forma segura dependendo do estado do DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.resgatarCarrinhoF5);
    } else {
        window.resgatarCarrinhoF5();
    }

}
