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

        try {
            const response = await fetch(`/pdv/orcamento/${encodeURIComponent(codigo)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            const data = await response.json();

            // 🔴 INTERCEPÇÃO DE SEGURANÇA
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Orçamento não encontrado ou não aprovado');
            }

            // Guardamos o ID do orçamento em memória para o fechamento
            window.orcamentoAtualId = data.orcamento.id;

            // Preenche cliente e o carrinho sincronizado
            preencherCliente(data.orcamento.cliente);
            preencherCarrinhoSincronizado(data.orcamento.itens);

            // Fecha modal
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

        } catch (error) {
            console.error(error);
            window.orcamentoAtualId = null;
            alert(error.message);
        }
    };

    // 🔹 FUNÇÃO GLOBAL CORRIGIDA: Envia os dados unificados resolvendo totais, caixa e cupom.blade
    window.faturarOrcamentoNoCaixa = async function () {
        if (!window.orcamentoAtualId) {
            return alert('Não há nenhum orçamento carregado no carrinho para finalizar.');
        }

        if (!confirm('Deseja confirmar o recebimento e faturar esta venda?')) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // 🧮 CALCULA O VALOR REAL BASEADO NO QUE FOI ALIMENTADO NO CARRINHO
            let totalVendaCalculado = 0;
            if (Array.isArray(window.carrinho)) {
                window.carrinho.forEach(item => {
                    totalVendaCalculado += (item.quantidade * item.preco_unitario);
                });
            }

            // Captura o id do cliente se houver nos inputs preenchidos
            const clienteIdEl = document.querySelector('[name="cliente_id"]');
            const clienteId = clienteIdEl ? clienteIdEl.value : null;

            // 📦 PAYLOAD COMPLETO: Agora envia o carrinho e totais idênticos ao fluxo de produto.js
            const payload = {
                orcamento_id: window.orcamentoAtualId,
                cliente_id: clienteId,
                total: totalVendaCalculado,              // Alimenta vendas.total e movimentcoes_caixa.valor
                itens: window.carrinho || []            // Alimenta item_vendas e impede cupom vazio
            };

            const response = await fetch('/pdv/faturar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Falha ao finalizar o faturamento no servidor.');
            }

            alert('Venda finalizada com sucesso! Orçamento marcado como Faturado.');
            
            // 🔄 Limpa o PDV por completo
            window.orcamentoAtualId = null;
            window.carrinho = []; // Zera a memória interna do caixa
            
            if (typeof limparCamposPDV === 'function') {
                limparCamposPDV();
            }
            const tbody = document.getElementById('lista-itens');
            if (tbody) tbody.innerHTML = '';
            
            const totalGeral = document.getElementById('totalGeral');
            if (totalGeral) totalGeral.textContent = 'R\$ 0,00';

        } catch (error) {
            console.error(error);
            alert('Erro ao faturar: ' + error.message);
        }
    };

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
                if (item.preco_unitario !== undefined) {
                    precoUnitario = parseFloat(item.preco_unitario);
                } else if (item.produto && item.produto.preco_venda !== undefined) {
                    precoUnitario = parseFloat(item.produto.preco_venda);
                } else if (item.preco_venda !== undefined) {
                    precoUnitario = parseFloat(item.preco_venda);
                }
                
                // 🛠️ MAPEAMENTO DA QUANTIDADE COM PROVA REAL MATEMÁTICA
                let qtdRaw = 0;
                if (item.quantidade_atendida !== undefined && parseFloat(item.quantidade_atendida) > 0) {
                    qtdRaw = item.quantidade_atendida;
                } else if (item.quantidade_solicitada !== undefined && parseFloat(item.quantidade_solicitada) > 0) {
                    qtdRaw = item.quantidade_solicitada;
                } else if (item.quantidade !== undefined && parseFloat(item.quantidade) > 0) {
                    qtdRaw = item.quantidade;
                }
                
                let quantity = parseFloat(qtdRaw); 
                
                let subtotal = parseFloat(item.subtotal || 0);
                if (subtotal === 0 || isNaN(subtotal)) {
                    subtotal = quantity * precoUnitario;
                }

                // Se a quantidade veio zerada, calcula via engenharia reversa (Subtotal / Preço)
                if ((quantity === 0 || isNaN(quantity)) && subtotal > 0 && precoUnitario > 0) {
                    quantity = subtotal / precoUnitario;
                }

                totalGeralAcumulado += subtotal;

                window.carrinho.push({
                    produto_id: pId,
                    lote_id: lId,
                    quantidade: quantity,
                    preco_unitario: precoUnitario,
                    desconto: 0
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
                const textoPreco = 'R' + 'S' + ' ' + precoUnitario.toFixed(2).replace('.', ',');
                tdPreco.appendChild(document.createTextNode(textoPreco));
                tr.appendChild(tdPreco);

                const tdQtd = document.createElement('td');
                tdQtd.className = 'item-quantidade';
                tdQtd.appendChild(document.createTextNode(quantity));
                tr.appendChild(tdQtd);

                const tdUnidade = document.createElement('td');
                tdUnidade.appendChild(document.createTextNode(siglaUnidade));
                tr.appendChild(tdUnidade);

                const tdSubtotal = document.createElement('td');
                tdSubtotal.className = 'subtotal fw-bold';
                const textoSubtotal = subtotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                tdSubtotal.appendChild(document.createTextNode(textoSubtotal));
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
    });
// Fim do Escopo Seguro (IIFE)

