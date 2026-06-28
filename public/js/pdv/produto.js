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
        // 🚀 AJUSTE DE PARÂMETRO: Adicionado 'qtdManual' para receber o multiplicador síncrono do Enter

        window.adicionarItemCarrinho = function(produto, qtdManual) {
            // 1️⃣ CAPTURA A QUANTIDADE REAL DIGITADA NO INPUT ANTES DO RESET
            const inputQtdElement = document.getElementById("quantidade");
            
            // Se o parâmetro 'qtdManual' vier preenchido (venda multiplicada), usa ele na hora. 
            // Se não, lê o input HTML da tela normalmente.
            const quantidade = qtdManual ? Number(qtdManual) : (inputQtdElement ? Number(inputQtdElement.value) : 1);
            
            // ====================================================================
            // INTERCEPTAÇÃO CIRÚRGICA: PREÇO BASEADO NO PERFIL DO CLIENTE NO CARRINHO
            // ====================================================================
            const tipoMarkupAtivo = document.getElementById('tipo_cliente_pdv')?.value || 'markup_1';

            const precoVenda1 = Number(produto.preco_venda || 0);
            const precoVenda2 = Number(produto.preco_venda_2 || 0);
            const precoVenda3 = Number(produto.preco_venda_3 || 0);

            let preco = precoVenda1;

            if (tipoMarkupAtivo === 'markup_2' && precoVenda2 > 0) {
                preco = precoVenda2;
            } else if (tipoMarkupAtivo === 'markup_3' && precoVenda3 > 0) {
                preco = precoVenda3;
            }

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
                alert("Atenção: nenhum lote disponível ou dentro da validade. A venda será permitida e a diferença será registrada.");

                loteSelecionado = {
                    id: null,
                    numero_lote: 'SEM LOTE',
                    quantidade_disponivel: 0
                };
            }

            const loteId = loteSelecionado.id;
            const qtdDisponivelLote = Number(loteSelecionado.quantidade_disponivel);

            // Validação de teto do estoque físico do lote
            if (quantidade > qtdDisponivelLote) {
            }

            // Inicializa a memória do carrinho global se necessário
            if (!window.carrinho) { window.carrinho = []; }

            // 3️⃣ BUSCA SE O ITEM JÁ EXISTE NO CARRINHO (Mesmo Produto + Mesmo Lote)
            const loteChave = loteId ?? 'SEM_LOTE';

            const itemExistente = window.carrinho.find(i => i.produto_id == produto.id && i.lote_id == loteChave);

            const quantidadeFinalSolicitada = itemExistente
                ? Number(itemExistente.quantidade) + Number(quantidade)
                : Number(quantidade);

            const vendaAcimaEstoque = quantidadeFinalSolicitada > qtdDisponivelLote;

            if (vendaAcimaEstoque) {
            }

            if (itemExistente) {
            const novaQtd = Number(itemExistente.quantidade) + Number(quantidade);

            if (novaQtd > qtdDisponivelLote) {
            }

            itemExistente.quantidade = novaQtd;

            const linhaVisual = tabelaItens.querySelector(`tr[data-produto="${produto.id}"][data-lote="${loteChave}"]`);

            if (linhaVisual) {
                const campoQtd = linhaVisual.querySelector('.item-quantidade');
                const campoSubtotal = linhaVisual.querySelector('.subtotal');

                if (campoQtd) {
                    campoQtd.textContent = novaQtd;
                }

                if (campoSubtotal) {
                    campoSubtotal.textContent = (novaQtd * preco).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                }
            }

            } else {
                window.carrinho.push({
                    produto_id: produto.id,
                    lote_id: loteId || 'SEM_LOTE',
                    descricao: produto.nome,
                    quantidade: quantidade,
                    preco_unitario: preco,
                    desconto: 0
                });

                const numeroItem = tabelaItens.querySelectorAll("tr").length + 1;
                const novaLinha = document.createElement("tr");

                novaLinha.dataset.produto = produto.id;
                novaLinha.dataset.lote = loteId || 'SEM_LOTE';
                novaLinha.style.cursor = "pointer";

               novaLinha.innerHTML = `
                    <td class="text-center">${numeroItem}</td>
                    <td class="text-center">${loteId || 'SEM_LOTE'}</td>
                    <td class="text-start">${produto.nome}</td>
                    <td class="text-end">R$ ${preco.toFixed(2).replace('.', ',')}</td>
                    <td class="text-center item-quantidade">${quantidade}</td>
                    <td class="text-center">${produto.unidade_sigla || 'UN'}</td>
                    <td class="text-end subtotal fw-bold">${(quantidade * preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                    <td class="text-muted d-none">Lote: ${loteSelecionado.numero_lote || 'SEM LOTE'}</td>
                `;

                tabelaItens.appendChild(novaLinha);

                // remove seleção anterior
                // tabelaItens.querySelectorAll("tr").forEach(tr =>
                //     tr.classList.remove("table-active")
                // );

                // // seleciona a última restaurada
                // linhaSelecionada = novaLinha;
                // novaLinha.classList.add("table-active");

                // if (acoesCarrinho) {
                //     acoesCarrinho.style.display = "block";
                // }
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
                    // console.log("💾 LOCALSTORAGE ESPELHADO COM SUCESSO:", window.carrinho);
                } else {
                    // 🧹 Limpa os dados de forma inteligente se o carrinho ficar vazio
                    if (typeof PdvStorage !== 'undefined') {
                        PdvStorage.limparPdv();
                    } else {
                        localStorage.removeItem('pdv_carrinho_atual');
                    }
                }
            } catch (errStorage) {
                // console.error("Falha ao espelhar LocalStorage:", errStorage);
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

                            // Altera os valores na tela e calcula o subtotal do bloco esquerdo
                            calcularTotalProduto();
                            
                            // 🚀 CORREÇÃO CIRÚRGICA: Passa a quantidade extraída do multiplicador para o carrinho
                            window.adicionarItemCarrinho(produto, quantidadeDefinida);

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
    // 🎯 RESGATAR CARRINHO DO LOCALSTORAGE APÓS F5
    // =======================================================================
    window.resgatarCarrinhoF5 = async function () {
        try {
            const salvo = localStorage.getItem('pdv_carrinho_atual');
            if (!salvo) return;

            const itensSalvos = JSON.parse(salvo);

            if (!Array.isArray(itensSalvos) || itensSalvos.length === 0) return;

            const tabelaItens = document.getElementById("lista-itens");

            if (!tabelaItens) {
                console.warn("Tabela de itens não encontrada.");
                return;
            }

            // Evita duplicidade caso a função rode mais de uma vez
            tabelaItens.innerHTML = '';
            window.carrinho = [];

            for (const item of itensSalvos) {
                const produtoId = parseInt(item.produto_id);
                const loteId = item.lote_id || 0;
                const quantidade = Number(item.quantidade || 1);
                const precoUnitario = Number(item.preco_unitario || 0);
                const desconto = Number(item.desconto || 0);

                let nomeProduto = item.nome || item.descricao || `Produto #${produtoId || ''}`;
                let siglaUnidade = item.unidade_sigla || item.unidade || 'UN';

                // Busca dados atualizados no banco, se houver ID válido
                if (produtoId && !isNaN(produtoId)) {
                    try {
                        const response = await fetch(`/pdv/produto/${produtoId}`, {
                            headers: { "Accept": "application/json" }
                        });

                        if (response.ok) {
                            const data = await response.json();

                            if (data.status === "ok" && data.produto) {
                                nomeProduto = data.produto.nome || nomeProduto;
                                siglaUnidade = data.produto.unidade_sigla || siglaUnidade;
                            }
                        }
                    } catch (erroFetch) {
                        console.warn("Falha ao consultar produto no F5. Usando LocalStorage.", erroFetch);
                    }
                }

                // Recria memória global do carrinho
                window.carrinho.push({
                    produto_id: produtoId || 0,
                    lote_id: loteId,
                    descricao: nomeProduto,
                    quantidade: quantidade,
                    preco_unitario: precoUnitario,
                    desconto: desconto,
                    nome: nomeProduto
                });

                // Renderiza linha
                const numeroItem = tabelaItens.querySelectorAll("tr").length + 1;
                const subtotal = quantidade * precoUnitario;

                const novaLinha = document.createElement("tr");
                novaLinha.dataset.produto = produtoId || 0;
                novaLinha.dataset.lote = loteId;
                novaLinha.style.cursor = "pointer";

                novaLinha.innerHTML = `
                    <td class="text-center">${numeroItem}</td>
                    <td class="text-center">${loteId || 'OK'}</td>
                    <td class="text-start">${nomeProduto}</td>
                    <td class="text-end">R$ ${precoUnitario.toFixed(2).replace('.', ',')}</td>
                    <td class="text-center item-quantidade">${quantidade}</td>
                    <td class="text-center">${siglaUnidade}</td>
                    <td class="text-end subtotal fw-bold">
                        ${subtotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                    </td>
                    <td class="text-muted d-none">Lote: ${loteId || 'OK'}</td>
                `;

                tabelaItens.appendChild(novaLinha);
            }

            atualizarTotalCarrinhoF5();

            console.log("Carrinho restaurado após F5:", window.carrinho);

        } catch (error) {
            console.error("Falha ao restaurar carrinho após F5:", error);
        }
    };


    // =======================================================================
    // 🧮 ATUALIZAR TOTAL DO CARRINHO RESTAURADO
    // =======================================================================
    function atualizarTotalCarrinhoF5() {
        const total = (window.carrinho || []).reduce((soma, item) => {
            const quantidade = Number(item.quantidade || 0);
            const preco = Number(item.preco_unitario || 0);
            const desconto = Number(item.desconto || 0);

            return soma + ((quantidade * preco) - desconto);
        }, 0);

        const campoTotal =
            document.getElementById("totalGeral") ||
            document.getElementById("total_geral") ||
            document.getElementById("inputTotalGeral");

        if (!campoTotal) return;

        const totalFormatado = total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        if (campoTotal.tagName === 'INPUT') {
            campoTotal.value = totalFormatado;
        } else {
            campoTotal.textContent = totalFormatado;
        }
    }


    // Gatilho: Executa o script de forma segura dependendo do estado do DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.resgatarCarrinhoF5);
    } else {
        window.resgatarCarrinhoF5();
    }

}
