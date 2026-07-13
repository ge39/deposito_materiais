 // Atrelando ao escopo global window para garantir o acesso de qualquer lugar
window.exibirAlertaBootstrap = function(mensagem, classe = 'warning') {
    
    // =======================================================================
    // 1️⃣ FECHA APENAS OS MODAIS DO BOOTSTRAP ANTES DE ABRIR O NOVO ALERTA
    // =======================================================================
    if (typeof bootstrap !== 'undefined') {
        const modaisAbertos = document.querySelectorAll('.modal.show');
        modaisAbertos.forEach(modEl => {
            // Fecha a instância oficial do Bootstrap com segurança
            const instancia = bootstrap.Modal.getInstance(modEl) || bootstrap.Modal.getOrCreateInstance(modEl);
            if (instancia) {
                instancia.hide();
            }
        });
    }

    // Remove as películas escuras (backdrops) dos modais antigos que foram fechados
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

    // Remove apenas a classe de travamento que o Bootstrap põe no body, mantendo seus estilos intactos
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';

    // =======================================================================
    // 2️⃣ MONTAGEM E CONFIGURAÇÃO DO NOVO MODAL DE ALERTA OPERACIONAL
    // =======================================================================
    let icone = '⚠️';
    let titulo = 'Atenção operacional';
    let bgHeader = '#e03e4d'; // Tom exato de vermelho da sua imagem

    if (classe === 'success') { icone = '✅'; titulo = 'Sucesso'; bgHeader = '#198754'; }
    if (classe === 'info') { icone = 'ℹ️'; titulo = 'Informação'; bgHeader = '#0dcaf0'; }

    const modalId = 'modal_alerta_' + Date.now();

    const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true" style="z-index: 1095;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    
                    <!-- Cabeçalho -->
                    <div class="modal-header border-0 py-3 px-4 d-flex align-items-center justify-content-between" style="background-color: ${bgHeader}; color: #ffffff;">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2 m-0" style="font-size: 1.2rem; letter-spacing: 0.3px;">
                            <span style="font-size: 1.1rem;">${icone}</span> ${titulo}
                        </h5>
                        <button type="button" class="btn-close btn-close-white shadow-none m-0 p-0" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.8;"></button>
                    </div>
                    
                    <!-- Corpo -->
                    <div class="modal-body p-5 text-center" style="background-color: #ffffff; color: #6c757d;">
                        <div style="font-size: 1.25rem; line-height: 1.5; font-weight: 500;">
                            ${mensagem}
                        </div>
                    </div>
                    
                    <!-- Rodapé -->
                    <div class="modal-footer border-0 pb-4 pt-0 justify-content-center" style="background-color: #ffffff;">
                        <button type="button" class="btn fw-bold px-4 py-2 border-0 text-white" data-bs-dismiss="modal" style="background-color: #6c757d; border-radius: 8px; font-size: 1rem; min-width: 110px; transition: background 0.2s;">
                            Entendi
                        </button>
                    </div>

                </div>
            </div>
        </div>
    `;

    // Injeta o novo alerta no final da página
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Exibe o novo alerta operacional
    const modalElement = document.getElementById(modalId);
    if (modalElement && typeof bootstrap !== 'undefined') {
        const bsModal = new bootstrap.Modal(modalElement);
        bsModal.show();

        // Limpa o HTML do alerta e joga o foco no input de código de barras após o fechamento
                // Limpa o HTML do alerta e joga o foco no input de código de barras após o fechamento
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove(); 
            
            const inputCodigo = document.getElementById('codigo_barras');
            if (inputCodigo) {
                inputCodigo.focus();
                inputCodigo.select();
            }

            // 🎯 GATILHOS DE RETORNO PARA VENDA BALCÃO:
            // 1️⃣ Quando falha na validação (danger/error)
            // 2️⃣ Quando a venda é concluída com sucesso como orçamento (success)
            if (classe === 'danger' || classe === 'error' || classe === 'success') {
                if (typeof window.vendaBalcao === 'function') {
                    window.vendaBalcao();
                }
            }
        });
    } else if (modalElement) {
        alert(mensagem.replace(/<\/?[^>]+(>|$)/g, ''));
        modalElement.remove();

        // Fallback básico caso o Bootstrap não esteja carregado
        if (classe === 'danger' || classe === 'error' || classe === 'success') {
            if (typeof window.vendaBalcao === 'function') {
                window.vendaBalcao();
            }
        }
    }

};

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

document.addEventListener('DOMContentLoaded', function () {
    // 💾 VARIÁVEL GLOBAL DO CAIXA: Armazena o ID do orçamento ativo para a finalização
    window.orcamentoAtualId = null;

    /* =========================
    INPUT CÓDIGO DO ORÇAMENTO
    ========================= */
    if (typeof limparCamposPDV === 'function') {
        limparCamposPDV();
    }
    
    const modalEl = document.getElementById('modalOrcamento');
    const inputCodigo = document.getElementById('inputCodigoOrcamento');
    
    if (modalEl && inputCodigo) {
        modalEl.addEventListener('shown.bs.modal', function () {
            inputCodigo.focus();
            inputCodigo.select();
        });

        inputCodigo.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const codigo = inputCodigo.value.trim();
                if (codigo) window.confirmarOrcamentoFront();
            }
        });
    }

    /* =========================
       FUNÇÕES AUXILIARES E AJUSTES DO PDV
       ========================= */

    window.confirmarOrcamentoFront = async function () {
    const inputCodigo = document.getElementById('inputCodigoOrcamento');
    if (!inputCodigo) return alert('Input do código do orçamento não encontrado.');

    const codigo = inputCodigo.value.trim();
    if (!codigo) return alert('Informe o código do orçamento.');

    const modalEl = document.getElementById('modalOrcamento'); 

    try {
        const response = await fetch(`/pdv/orcamento/${encodeURIComponent(codigo)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });

        // 1️⃣ LEITURA ÚNICA DO JSON
        const data = await response.json();

        // 2️⃣ DEFINIÇÃO DO OBJETO PRINCIPAL
        const orcamentoObj = data.orcamento || data.data || data; 

        // ==========================================
        // DEBUG - DADOS RECEBIDOS DO ORÇAMENTO
        // ==========================================

        // console.group('========== ORÇAMENTO RECEBIDO ==========');

        //console.log('Objeto Completo:', orcamentoObj);

        //console.log('Dados do Cliente:', orcamentoObj.cliente);

        //console.log('Itens:', orcamentoObj.itens);

        console.groupEnd();

        // 🎯 CORREÇÃO DE ESCOPO: Inicializa as variáveis no topo para estarem disponíveis em qualquer lugar da função
        const codigoPedido = orcamentoObj?.codigo_orcamento || orcamentoObj?.id || orcamentoObj?.codigo || 'N/A';
        const nomeCliente = orcamentoObj?.cliente?.nome || orcamentoObj?.nome_cliente || 'Cliente não identificado';

        // 3️⃣ VALIDAÇÃO DINÂMICA DE STATUS VINDOS DO BANCO DE DADOS
        if (orcamentoObj && orcamentoObj.status) {
            // Limpa acentos, espaços e padroniza tudo em minúsculo
            const status = String(orcamentoObj.status)
                .trim()
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
            
            let mensagemStatus = '';

            switch (status) {
                case 'faturado':
                    mensagemStatus = `
                        O orçamento <strong>#${codigoPedido}</strong> já foi <strong>Faturado</strong>!<br>
                        <span style="font-size: 0.95rem; display:block; margin-top: 5px;">
                            Cliente: ${nomeCliente}<br>
                            Venda já concluída no sistema. Não é possível alterar itens no caixa.
                        </span>
                    `;
                    break;

                case 'cancelado':
                    mensagemStatus = `
                        O orçamento <strong>#${codigoPedido}</strong> está <strong>Cancelado</strong>!<br>
                        <span style="font-size: 0.95rem; display:block; margin-top: 5px;">
                            Cliente: ${nomeCliente}<br>
                            Este pedido foi anulado e não pode ser faturado no PDV.
                        </span>
                    `;
                    break;

                case 'expirado':
                    mensagemStatus = `
                        A validade do orçamento <strong>#${codigoPedido}</strong> está <strong>Expirada</strong>!<br>
                        <span style="font-size: 0.95rem; display:block; margin-top: 5px;">
                            Cliente: ${nomeCliente}<br>
                            O prazo de reserva de preços terminou. É necessário atualizar a validade.
                        </span>
                    `;
                    break;

                // case 'aguardando estoque':
                //     mensagemStatus = `
                //         Orçamento <strong>#${codigoPedido}</strong> retido: <strong>Aguardando Estoque</strong>!<br>
                //         <span style="font-size: 0.95rem; display:block; margin-top: 5px;">
                //             Cliente: ${nomeCliente}<br>
                //             Existem itens neste pedido sem saldo físico disponível no depósito.
                //         </span>
                //     `;
                //     break;

                case 'aguardando aprovacao':
                    mensagemStatus = `
                        O orçamento <strong>#${codigoPedido}</strong> pendente: <strong>Aguardando Aprovação</strong>!<br>
                        <span style="font-size: 0.95rem; display:block; margin-top: 5px;">
                            Cliente: ${nomeCliente}<br>
                            Este pedido precisa ser liberado pela gerência ou setor financeiro.
                        </span>
                    `;
                    break;
            }

            if (mensagemStatus !== '') {
                window.exibirAlertaBootstrap(mensagemStatus, 'danger');
                return; // Trava o fluxo e impede o preenchimento do carrinho
            }
        }

        // 4️⃣ INTERCEPTAÇÃO DE SEGURANÇA SEGUNDO NÍVEL (Caso o orçamento não exista no banco)
        if (!response.ok || !data.success) {
            const mensagemErro = data.message || 'Não foi possível localizar este orçamento.';
            window.exibirAlertaBootstrap(`
                <strong>Não foi possível continuar</strong><br>
                ${mensagemErro}<br>
                Verifique o código informado e tente novamente.
            `, 'danger');
            return;
        }

        // 5️⃣ CONTINUAÇÃO DO SEU CÓDIGO ORIGINAL (Apenas se o status for válido)
        if (data.orcamento && data.orcamento.id) {
            window.orcamentoAtualId = data.orcamento.id;
            const orcamentoObj = data.orcamento || data.data || data;

            window.orcamentoAtual = orcamentoObj;
            window.orcamentoAtualId = Number(orcamentoObj.id);

            const inputOrcamento = document.getElementById('orcamento_id');

            // //console.log('CSI ORÇAMENTO - INPUT EXISTE?', !!inputOrcamento);
            // //console.log('CSI ORÇAMENTO - ID DO OBJETO:', orcamentoObj?.id);

            if (inputOrcamento && orcamentoObj?.id) {
                inputOrcamento.value = Number(orcamentoObj.id);
                //console.log('CSI ORÇAMENTO - INPUT PREENCHIDO COM:', inputOrcamento.value);
            } else {
                console.error('CSI ORÇAMENTO - FALHA AO PREENCHER INPUT', {
                    inputExiste: !!inputOrcamento,
                    orcamentoId: orcamentoObj?.id || null
                });
            }
            
            //console.log('ORÇAMENTO CARREGADO NO PDV:', window.orcamentoAtual);

            preencherCliente(data.orcamento.cliente);
            preencherCarrinhoSincronizado(data.orcamento.itens);

            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                if (modal) modal.hide();
            }
        } else {
            throw new Error('Dados do orçamento estruturados incorretamente pela API.');
        }

    } catch (error) {
        console.error(error);
        window.orcamentoAtualId = null;
        window.exibirAlertaBootstrap(`
            <strong>Erro na requisição</strong><br>
            ${error.message}
        `, 'danger');
    }

    // Função para preencher os inputs do cliente
    function preencherCliente(cliente) {
        if (!cliente) return;

        const map = {
            cliente_id: cliente.id ?? '',
            nome: cliente.nome ?? '',
            pessoa: cliente.tipo === 'fisica' ? 'Física' : 'Jurídica',
            telefone: cliente.telefone ?? '',
            endereco: montarEndereco(cliente)
        };

        Object.entries(map).forEach(([name, value]) => {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) el.value = value;
        });
    }

    function montarEndereco(cliente) {
        const partes = [];
        if (cliente.endereco) partes.push(cliente.endereco);
        if (cliente.numero) partes.push('nº ' + cliente.numero);
        if (cliente.bairro) partes.push(cliente.bairro);
        if (cliente.cidade) partes.push(cliente.cidade);
        if (cliente.estado) partes.push(cliente.estado);
        if (cliente.cep) partes.push('CEP ' + cliente.cep);
        return partes.join(' - ');
    }

    // 🛒 POPULA O COMPORTAMENTO EXATO EXIGIDO PELO PRODUTO.JS (data attributes e window.carrinho)
            /* ==========================================================
           🛒 ALIMENTAÇÃO DO ARRAY GLOBAL CORRIGIDA (QUANTIDADE DISPARADA)
           ========================================================== */
        function preencherCarrinhoSincronizado(itens) {
            if (!Array.isArray(itens)) return;

            const tbody = document.getElementById('lista-itens');
            if (!tbody) {
                console.warn('Tbody lista-itens nao encontrado.');
                return;
            }

            tbody.innerHTML = '';
            window.carrinho = []; 

            let totalGeralAcumulado = 0;
            itens.forEach(function(item, index) {
                const pId = item.produto_id || item.id;
                const lId = item.lote_id || '';
                
                let nomeProd = 'Produto nao identificado';
                if (item.produto && item.produto.nome) {
                    nomeProd = item.produto.nome;
                } else if (item.nome) {
                    nomeProd = item.nome;
                }

                let siglaUnidade = 'UN';
                if (item.produto && item.produto.unidade_medida && item.produto.unidade_medida.sigla) {
                    siglaUnidade = item.produto.unidade_medida.sigla;
                } else if (item.unidade_medida && item.unidade_medida.sigla) {
                    siglaUnidade = item.unidade_medida.sigla;
                }

                let precoUnitario = 0;

                if (item.preco_liquido !== undefined && parseFloat(item.preco_liquido) > 0) {
                    precoUnitario = parseFloat(item.preco_liquido);
                } else if (item.preco_unitario !== undefined) {
                    precoUnitario = parseFloat(item.preco_unitario);
                } else if (item.produto && item.produto.preco_venda !== undefined) {
                    precoUnitario = parseFloat(item.produto.preco_venda);
                } else if (item.preco_venda !== undefined) {
                    precoUnitario = parseFloat(item.preco_venda);
                }

                /*
                |--------------------------------------------------------------------------
                | REGRA DE NEGÓCIO
                |--------------------------------------------------------------------------
                | O PDV deve SEMPRE cobrar a quantidade SOLICITADA pelo cliente.
                |
                | quantidade_solicitada = quantidade vendida (financeiro)
                | quantidade_atendida   = quantidade disponível para entrega
                | quantidade_pendente   = saldo para entrega futura
                |--------------------------------------------------------------------------
                */

                let qtdRaw = 0;

                if (item.quantidade_solicitada !== undefined && parseFloat(item.quantidade_solicitada) > 0) {
                    qtdRaw = item.quantidade_solicitada;

                } else if (item.quantidade !== undefined && parseFloat(item.quantidade) > 0) {
                    qtdRaw = item.quantidade;

                } else if (item.quantidade_atendida !== undefined && parseFloat(item.quantidade_atendida) > 0) {
                    qtdRaw = item.quantidade_atendida;
                }

                let quantity = parseFloat(qtdRaw);

                if (isNaN(quantity)) {
                    quantity = 0;
                }

                // O subtotal financeiro deve vir da tabela item_orcamentos.
                // Regra: subtotal já considera quantidade solicitada, preço líquido e desconto.
                let subtotal = 0;

                if (item.subtotal !== undefined && parseFloat(item.subtotal) > 0) {
                    subtotal = parseFloat(item.subtotal);
                } else {
                    subtotal = quantity * precoUnitario;
                }

                // Compatibilidade com registros antigos sem quantidade informada
                if ((quantity === 0 || isNaN(quantity)) && subtotal > 0 && precoUnitario > 0) {
                    quantity = subtotal / precoUnitario;
                }

                totalGeralAcumulado += subtotal;

                window.carrinho.push({
                    produto_id: pId,
                    lote_id: lId,
                    quantidade: parseFloat(item.quantidade_solicitada),
                    preco_unitario: parseFloat(item.preco_liquido ?? item.preco_unitario),
                    desconto: parseFloat(item.valor_desconto ?? 0),
                    subtotal: parseFloat(item.subtotal ?? 0)
                });

                const tr = document.createElement('tr');
                tr.setAttribute('data-produto', pId);
                tr.setAttribute('data-lote', lId);
                tr.style.cursor = 'pointer';

                const tdIndex = document.createElement('td');
                tdIndex.appendChild(document.createTextNode(index + 1));
                tr.appendChild(tdIndex);

                const tdId = document.createElement('td');
                tdId.appendChild(document.createTextNode(pId));
                tr.appendChild(tdId);

                const tdNome = document.createElement('td');
                tdNome.className = 'text-start';
                tdNome.appendChild(document.createTextNode(nomeProd));
                tr.appendChild(tdNome);

                const tdPreco = document.createElement('td');
                tdPreco.appendChild(
                    document.createTextNode(
                        'R$ ' + precoUnitario.toFixed(2).replace('.', ',')
                    )
                );
                tr.appendChild(tdPreco);

                const tdQtd = document.createElement('td');
                tdQtd.className = 'item-quantidade fw-bold';
                tdQtd.appendChild(document.createTextNode(quantity));
                tr.appendChild(tdQtd);

                const tdUnidade = document.createElement('td');
                tdUnidade.appendChild(document.createTextNode(siglaUnidade));
                tr.appendChild(tdUnidade);

                const tdSubtotal = document.createElement('td');
                tdSubtotal.className = 'subtotal fw-bold';
                tdSubtotal.appendChild(
                    document.createTextNode(
                        subtotal.toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        })
                    )
                );
                tr.appendChild(tdSubtotal);

                const tdLote = document.createElement('td');
                tdLote.className = 'text-muted d-none';
                tdLote.appendChild(document.createTextNode('Lote: ' + lId));
                tr.appendChild(tdLote);

                tbody.appendChild(tr);
            });

            const totalVendaEl = document.getElementById('totalGeral');
            if (totalVendaEl) {
                totalVendaEl.textContent = totalGeralAcumulado.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }

            if (typeof window.emitirBipPDV === 'function') {
                window.emitirBipPDV();
            }
        }
       
    }

 });
// Fim do Escopo Seguro (IIFE)

