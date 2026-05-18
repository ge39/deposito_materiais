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
            if (qtdDisponivelInput) qtdDisponivelInput.value = ""; 
            if (imgProduto) imgProduto.src = "/images/produto-sem-imagem.png";
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
                // 4️⃣ ADICIONA NOVO OBJETO AO ARRAY GLOBAL
                window.carrinho.push({
                    produto_id: produto.id,
                    lote_id: loteId,
                    nome: produto.nome,
                    unidade: produto.unidade_sigla || '',
                    quantidade: quantidade,
                    preco_unitario: preco // Sincronizado estritamente com a chave usada no btnDiminuir
                });

                // 5️⃣ ADICIONA NOVA LINHA FÍSICA NA TABELA HTML
                const novaLinha = document.createElement("tr");
                novaLinha.setAttribute("data-produto", produto.id);
                novaLinha.setAttribute("data-lote", loteId);
                novaLinha.style.cursor = "pointer";

                const subtotalOriginal = quantidade * preco;
                const unidadeMedida = produto.unidade_sigla || "";

                novaLinha.innerHTML = `
                    <td></td>
                    <td>${loteId}</td>
                    <td>${produto.nome}</td>
                    <td>${preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                    <td class="text-center item-quantidade">${quantidade}</td>
                    <td>${unidadeMedida}</td>
                    <td class="subtotal">${subtotalOriginal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                `;

                novaLinha.addEventListener("click", function() {
                    tabelaItens.querySelectorAll("tr").forEach(r => r.classList.remove("table-active"));
                    
                    if (linhaSelecionada === this) {
                        linhaSelecionada = null;
                        if (acoesCarrinho) acoesCarrinho.classList.add("d-none");
                        return;
                    }

                    linhaSelecionada = this;
                    this.classList.add("table-active");

                    if (acoesCarrinho) {
                        acoesCarrinho.classList.remove("d-none");
                    }
                });

                tabelaItens.appendChild(novaLinha);
            }

            if (typeof window.emitirBipPDV === "function") window.emitirBipPDV();
            atualizarNumeroItens();
            atualizarTotalCarrinho();
            resetarProdutoAtual();
        };

        // ========================================== //
        // ARRASTAR DIV DE AÇÕES                      //
        // ========================================== //
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

        // ========================================== //
        // GATILHO DA BUSCA INTEGRADA (ENTER / X*)    //
        // ========================================== //
        // if (inputCodigo) {
        //     inputCodigo.addEventListener("keydown", (e) => {
        //         if (e.key === "Enter") {
        //             e.preventDefault();

        //             let termoOriginal = inputCodigo.value.trim();
        //             if (!termoOriginal) return;

        //             let quantidadeDefinida = 1;
        //             let codigoFinal = termoOriginal;

        //             if (termoOriginal.includes('*')) {
        //                 const partes = termoOriginal.split('*');
        //                 const qtdInformada = parseFloat(partes[0]);
        //                 if (!isNaN(qtdInformada) && qtdInformada > 0) {
        //                     quantidadeDefinida = qtdInformada;
        //                     codigoFinal = partes[1].trim();
        //                 }
        //             }

        //             if (!codigoFinal) {
        //                 alert("Código de barras inválido.");
        //                 return;
        //             }

        //             if (inputQuantidade) inputQuantidade.value = quantidadeDefinida;

        //             // Executa a requisição direta isolada de escopo usando o código higienizado
        //             fetch(`/pdv/produto/${encodeURIComponent(codigoFinal)}`, { headers: { "Accept": "application/json" } })
        //                 .then(res => res.json())
        //                 .then(dataRes => {
        //                     if (dataRes.status === "ok" && dataRes.produto) {
        //                         const produto = dataRes.produto;
        //                         window.produtoAtual = produto;

        //                         if (inputId_produto) inputId_produto.value = produto.id;
        //                         if (inputDescricao) inputDescricao.value = produto.nome;
        //                         if (inputPrecoVenda) inputPrecoVenda.value = Number(produto.preco_venda).toFixed(2);
                                
        //                         const qtdDisp = produto.quantidade_total_disponivel || 1;
        //                         if (qtdDisponivelInput) qtdDisponivelInput.value = qtdDisp;

        //                         const inputUnidade = document.getElementById("unidade");
        //                         if (inputUnidade) {
        //                             inputUnidade.value = produto.unidade || produto.unidade_medida || "UN";
        //                         }

        //                         calcularTotalProduto();

        //                         // Executa o fluxo contínuo de inserção
        //                         window.adicionarItemCarrinho(produto);
                                
        //                     } else {
        //                         alert(dataRes.mensagem || "Produto não encontrado.");
        //                         if (inputCodigo) inputCodigo.value = "";
        //                     }
        //                 })
        //                 .catch(err => {
        //                     console.error(err);
        //                     if (inputCodigo) inputCodigo.value = "";
        //                 });
        //         }
        //     }); 
        // }

                // =========================================================================
        // 🔥 CORRIGIDO: GATILHO DA BUSCA POR TECLADO (ENTER / X*) COM FIX DOS BUGS
        // =========================================================================
        if (inputCodigo) {
            // 🔥 CORREÇÃO BUG 1: Garante que ao focar na barra amarela, o cursor vá para o início (esquerda) e não selecione o texto
            inputCodigo.addEventListener("focus", function() {
                setTimeout(() => {
                    this.setSelectionRange(0, 0);
                }, 10);
            });

            inputCodigo.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();

                    let termoOriginal = inputCodigo.value.trim();
                    if (!termoOriginal) return;

                    let quantidadeDefinida = 1;
                    let codigoFinal = termoOriginal;

                    // Processa o fator multiplicador (Ex: 3*789)
                    if (termoOriginal.includes('*')) {
                        const partes = termoOriginal.split('*');
                        const qtdInformada = parseFloat(partes[0]); // Pega estritamente a parte antes do asterisco
                        if (!isNaN(qtdInformada) && qtdInformada > 0) {
                            quantidadeDefinida = qtdInformada;
                            codigoFinal = partes[1].trim(); // Pega o código após o asterisco
                        }
                    }

                    if (!codigoFinal) {
                        alert("Código de barras inválido.");
                        return;
                    }

                    // Executa a requisição direta isolada de escopo usando o código higienizado
                    fetch(`/pdv/produto/${encodeURIComponent(codigoFinal)}`, { headers: { "Accept": "application/json" } })
                        .then(res => res.json())
                        .then(dataRes => {
                            if (dataRes.status === "ok" && dataRes.produto) {
                                const produto = dataRes.produto;
                                window.produtoAtual = produto;

                                if (inputId_produto) inputId_produto.value = produto.id;
                                if (inputDescricao) inputDescricao.value = produto.nome;
                                if (inputPrecoVenda) inputPrecoVenda.value = Number(produto.preco_venda).toFixed(2);
                                
                                const qtdDisp = produto.quantidade_total_disponivel || 1;
                                if (qtdDisponivelInput) qtdDisponivelInput.value = qtdDisp;

                                const inputUnidade = document.getElementById("unidade");
                                if (inputUnidade) {
                                    inputUnidade.value = produto.unidade || produto.unidade_medida || "UN";
                                }

                                // 🔥 CORREÇÃO BUG 2: Força o input de quantidade a receber o valor do multiplicador antes de calcular o total e inserir
                                if (inputQuantidade) {
                                    inputQuantidade.value = quantidadeDefinida;
                                    inputQuantidade.max = qtdDisp;
                                }

                                calcularTotalProduto();

                                // Executa o fluxo contínuo de inserção no carrinho rústico
                                window.adicionarItemCarrinho(produto);
                                
                                // 🔥 CORREÇÃO BUG 2 (Complemento): Se o usuário usou multiplicador (ex: 3*), mantém o valor "3" visível no input 
                                // para ele ver o que foi inserido, em vez de resetar bruscamente para "1".
                                if (termoOriginal.includes('*') && inputQuantidade) {
                                    inputQuantidade.value = quantidadeDefinida;
                                    calcularTotalProduto();
                                }
                                
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
            }); 
        }


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
        btnDiminuir?.replaceWith(btnDiminuir.cloneNode(true)); 
        document.getElementById("btnDiminuir")?.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation(); 

            if (!linhaSelecionada) {
                alert("Clique em um item da tabela primeiro para selecioná-lo.");
                return;
            }

            const produtoId = linhaSelecionada.dataset.produto;
            const loteId    = linhaSelecionada.dataset.lote;

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

                atualizarNumeroItens();
                atualizarTotalCarrinho();
                if (inputCodigo) inputCodigo.focus();
            }
        });

        // ========================================================== //
        // ❌ AÇÃO: REMOVER ITEM INTEIRO (BLINDADO CONTRA REPETIÇÃO)  //
        // ========================================================== //
        btnRemover?.replaceWith(btnRemover.cloneNode(true)); 
        document.getElementById("btnRemover")?.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation(); 

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

            window.carrinho = window.carrinho.filter(i => !(i.produto_id == produtoId && i.lote_id == loteId));

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
}
