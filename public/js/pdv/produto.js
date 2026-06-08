// ========================================================== //
// 🪙 SOM DE BIP NATIVO (FLUXO ULTRA RÁPIDO VIA FREQUÊNCIA)   //
// ========================================================== //
window.emitirBipPDV = function() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        
        const imgBg = document.getElementById('produto-imagem-bg');

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
    // Onde você carrega a imagem do produto após o bipe:
    document.getElementById('produto-imagem').src = urlImagemDoLaravel;
    if (imgBg) {
        imgBg.src = urlImagemDoLaravel;
        imgBg.style.display = 'block'; // Garante que o blur reative para fotos reais
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
                    descricao: produto.nome, // 🌟 Correção: Salvando a descrição/nome do produto
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

            // ========================================================== //
            // 🎯 INJEÇÃO CIRÚRGICA: CAPTURA A DESCRIÇÃO AO CLICAR NA LINHA
            // ========================================================== //
            if (linhaSelecionada) {
                const celulas = linhaSelecionada.getElementsByTagName("td");
                
                if (celulas && celulas.length >= 3) {
                    // Captura o texto exato da 3ª coluna (Descrição)
                    const descricaoProduto = celulas[2].textContent.trim(); 
                    
                    // Injeta dinamicamente no elemento ao lado do botão - Diminuir
                    const campoTextoBarra = document.getElementById("modalNomeProduto");
                    if (campoTextoBarra) {
                        campoTextoBarra.textContent = descricaoProduto;
                    }

                    // 🚀 ADICIONADO: Injeta simultaneamente a mesma descrição no modal de remoção
                    const campoModalRemover = document.getElementById("modalNomeProdutoRemover");
                    if (campoModalRemover) {
                        campoModalRemover.textContent = descricaoProduto;
                    }
                }
            }
        });



        // ========================================================== //
        // ❌ MODAL DINÂMICO COM NOME DO PRODUTO PARA REMOÇÃO        //
        // ========================================================== //
        btnRemover?.replaceWith(btnRemover.cloneNode(true));

        // ========================================================== //
        // ❌ MODAL CORRIGIDO: SEM ERROS DE SELEÇÃO NO CONSOLE       //
        // ========================================================== //
        
        if (!window.btnRemoverConfigurado) {
            document.getElementById("btnRemover")?.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!linhaSelecionada) {
                    alert("Clique em um item da tabela primeiro para selecioná-lo.");
                    return;
                }

                const produtoId = linhaSelecionada.dataset.produto;
                const loteId = linhaSelecionada.dataset.lote;

                // 🌟 CORREÇÃO: Captura especificamente a célula de descrição (índice 2)
                const celulas = linhaSelecionada.querySelectorAll("td");
                let nomeProduto = "Produto selecionado";
                
                if (celulas && celulas.length >= 3) {
                    nomeProduto = celulas[2].textContent.trim(); // Índice 2 corresponde à 3ª coluna (Descrição)
                }

                const modalElemento = document.getElementById('modalPdvRemover');
                if (!modalElemento) {
                    if (confirm(`⚠️ ADVERTÊNCIA!\n\nYou está prestes a remover o produto:\n"${nomeProduto}"\n\nDeseja confirmar?`)) {
                        executarRemocaoPdv(produtoId, loteId);
                    }
                    if (inputCodigo) inputCodigo.focus();
                    return;
                }

                // Injeta dinamicamente o nome do produto no corpo do modal
                const campoNomeModal = document.getElementById("modalNomeProduto");
                if (campoNomeModal) campoNomeModal.textContent = nomeProduto;

                // Inicializa e exibe o modal do Bootstrap
                const meuModal = new bootstrap.Modal(modalElemento);
                meuModal.show();

                // Remove o fundo escuro se o modal sumir
                modalElemento.addEventListener('hidden.bs.modal', function () {
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    if (inputCodigo) inputCodigo.focus();
                }, { once: true });

                // Gerenciamento seguro dos botões internos do modal
                const btnConf = document.getElementById("btnModalConfirmar");
                const btnCanc = document.getElementById("btnModalCancelar");

                const acaoConfirmar = function() {
                    executarRemocaoPdv(produtoId, loteId);
                    meuModal.hide();
                    btnConf.removeEventListener("click", acaoConfirmar);
                };
                
                const acaoCancelar = function() {
                    meuModal.hide();
                    btnCanc.removeEventListener("click", acaoCancelar);
                };

                btnConf.addEventListener("click", acaoConfirmar, { once: true });
                btnCanc.addEventListener("click", acaoCancelar, { once: true });

                // Atalhos de teclado seguros
                const escutarTecladoModal = function(tecla) {
                    if (tecla.key === "Enter") {
                        tecla.preventDefault();
                        btnConf.click();
                        window.removeEventListener("keydown", escutarTecladoModal);
                    }
                    if (tecla.key === "Escape") {
                        tecla.preventDefault();
                        btnCanc.click();
                        window.removeEventListener("keydown", escutarTecladoModal);
                    }
                };
                window.addEventListener("keydown", escutarTecladoModal);
            });

            window.btnRemoverConfigurado = true;
        }

        // // 📦 FUNÇÃO CENTRALIZADA QUE FAZ A LIMPEZA REAL NO LOCALSTORAGE E NA TELA
        function executarRemocaoPdv(produtoId, loteId) {
            window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));
            linhaSelecionada.remove();
            linhaSelecionada = null;

            if (acoesCarrinho) acoesCarrinho.classList.add("d-none");

            try {
                if (window.carrinho && window.carrinho.length > 0) {
                    if (typeof PdvStorage !== 'undefined') PdvStorage.salvarCarrinho(window.carrinho);
                    else localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                } else {
                    if (typeof PdvStorage !== 'undefined') PdvStorage.limparPdv();
                    else localStorage.removeItem('pdv_carrinho_atual');
                }
            } catch (errStorage) {
                console.error("Erro ao salvar LocalStorage:", errStorage);
            }

            atualizarNumeroItens();
            atualizarTotalCarrinho();
        }

        // 📦 FUNÇÃO ISOLADA QUE FAZ A LIMPEZA REAL NO LOCALSTORAGE E NA TELA
        // function executarRemocaoPdv(produtoId, loteId) {
        //     // 1. Remove do array de memória
        //     window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));

        //     // 2. Remove a linha física do HTML
        //     linhaSelecionada.remove();
        //     linhaSelecionada = null;

        //     // 3. Oculta a barra lateral do Bootstrap
        //     if (acoesCarrinho) acoesCarrinho.classList.add("d-none");

        //     // 4. 💾 Sincroniza o LocalStorage
        //     try {
        //         if (window.carrinho && window.carrinho.length > 0) {
        //             if (typeof PdvStorage !== 'undefined') PdvStorage.salvarCarrinho(window.carrinho);
        //             else localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
        //         } else {
        //             if (typeof PdvStorage !== 'undefined') PdvStorage.limparPdv();
        //             else localStorage.removeItem('pdv_carrinho_atual');
        //         }
        //     } catch (errStorage) {
        //         console.error("Erro ao salvar LocalStorage:", errStorage);
        //     }

        //     // 5. Atualiza os totais da tela de venda
        //     atualizarNumeroItens();
        //     atualizarTotalCarrinho();
        // }

        // 📦 FUNÇÃO ISOLADA PARA NÃO REPETIR CÓDIGO
        function ejecutarRemocaoLogica(produtoId, loteId) {
            window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));
            linhaSelecionada.remove();
            linhaSelecionada = null;

            if (acoesCarrinho) acoesCarrinho.classList.add("d-none");

            try {
                if (window.carrinho && window.carrinho.length > 0) {
                    if (typeof PdvStorage !== 'undefined') PdvStorage.salvarCarrinho(window.carrinho);
                    else localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                } else {
                    if (typeof PdvStorage !== 'undefined') PdvStorage.limparPdv();
                    else localStorage.removeItem('pdv_carrinho_atual');
                }
            } catch (errStorage) {
                console.error("Falha ao espelhar LocalStorage:", errStorage);
            }

            atualizarNumeroItens();
            atualizarTotalCarrinho();
        }

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
        // // ========================================== //
        tabelaItens?.addEventListener("click", (e) => {
            const linha = e.target.closest("tr");
            if (!linha || !tabelaItens.contains(linha)) return;
            tabelaItens.querySelectorAll("tr").forEach(tr => tr.classList.remove("table-active"));
            linhaSelecionada = linha;
            linhaSelecionada.classList.add("table-active");
            if (acoesCarrinho) acoesCarrinho.style.display = "block";
        });

        // ========================================================== //
        // ➖ AÇÃO UNIFICADA E SINCRONIZADA: DIMINUIR QUANTIDADE       //
        // ========================================================== //

        btnDiminuir?.replaceWith(btnDiminuir.cloneNode(true)); 

        document.getElementById("btnDiminuir")?.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!linhaSelecionada) {
                alert("Clique em um item da tabela primeiro para selecioná-lo.");
                return;
            }

            const produtoId = linhaSelecionada.dataset.produto;
            const loteId = linhaSelecionada.dataset.lote;
            const item = window.carrinho.find(i => i.produto_id == produtoId && i.lote_id == loteId);

            if (item) {
                item.quantidade--; 

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

                // 💾 SALVA NO LOCALSTORAGE EM UM SÓ LUGAR
                try {
                    if (window.carrinho && window.carrinho.length > 0) {
                        if (typeof PdvStorage !== 'undefined') {
                            PdvStorage.salvarCarrinho(window.carrinho);
                        } else {
                            localStorage.setItem('pdv_carrinho_atual', JSON.stringify(window.carrinho));
                        }
                    } else {
                        if (typeof PdvStorage !== 'undefined') {
                            PdvStorage.limparPdv();
                        } else {
                            localStorage.removeItem('pdv_carrinho_atual');
                        }
                    }
                } catch (errStorage) {
                    console.error("Falha ao espelhar LocalStorage:", errStorage);
                }

                atualizarNumeroItens();
                atualizarTotalCarrinho();
                if (inputCodigo) inputCodigo.focus();
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
                        descricao: item.descricao || nomeProduto, // 🌟 Correção: Salvando a descrição/nome do produto
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

                // ========================================================== //
                // 📡 VALIDAÇÃO INTEGRAL ANTES DE CONSULTAR A ROTA DO BANCO   //
                // ========================================================== //
                const idValido = parseInt(item.produto_id);

                if (!idValido || isNaN(idValido)) {
                    // 🎯 SE O ID FOR INVÁLIDO, PULA A API E CAI DIRETO NA CONTINGÊNCIA PERFEITA
                    console.log(`📦 [F5 RECOVERY] ID ausente. Renderizando diretamente via LocalStorage:`, item.nome || item.descricao);
                    injetarLinhaNaTela(item.nome || item.descricao || "Produto Sem Nome", 'UN');
                    return; // Encerra o processamento deste item atual no laço do forEach
                }

                // Executa a busca oficial no Laravel apenas com a certeza do ID numérico
                fetch(`/pdv/produto/${idValido}`, { headers: { "Accept": "application/json" } })
                    .then(res => {
                        if (!res.ok) throw new Error("Rota de produto inválida ou não encontrada");
                        return res.json();
                    })
                    .then(dataRes => {
                        if (dataRes.status === "ok" && dataRes.produto) {
                            // Plano A: Renderiza com o nome atualizado vindo direto do banco de dados
                            injetarLinhaNaTela(dataRes.produto.nome, dataRes.produto.unidade_sigla || 'UN');
                        } else {
                            // Plano B: Se a resposta da API vier incompleta, usa os metadados locais
                            injetarLinhaNaTela(item.nome || item.descricao || `Produto #${idValido}`, 'UN');
                        }
                    })
                    .catch(err => {
                        // Plano C: Se a internet cair ou o Laravel der timeout, a contingência assume
                        injetarLinhaNaTela(item.nome || item.descricao || `Produto #${idValido}`, 'UN');
                    });
            }); // Fim do foreach

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
