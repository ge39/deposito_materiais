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

            // 🔴 INTERCEPÇÃO DE SEGURANÇA: Se o Laravel retornar erro (ex: Já Faturado), exibe o alerta correto
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Orçamento não encontrado ou não aprovado');
            }

            // Guardamos o ID do orçamento em memória para o fechamento
            window.orcamentoAtualId = data.orcamento.id;

            // Preenche cliente e carrinho
            preencherCliente(data.orcamento.cliente);
            preencherCarrinho(data.orcamento.itens);

            // Fecha modal
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

        } catch (error) {
            console.error(error);
            // Zera o ID em memória caso dê erro na busca
            window.orcamentoAtualId = null;
            alert(error.message);
        }
    };

    // 🔹 NOVA FUNÇÃO GLOBAL: EXECUTA O FATURAMENTO E A MUTAÇÃO DE STATUS NO BANCO
    window.faturarOrcamentoNoCaixa = async function () {
        if (!window.orcamentoAtualId) {
            return alert('Não há nenhum orçamento carregado no carrinho para finalizar.');
        }

        if (!confirm('Deseja confirmar o recebimento e faturar esta venda?')) return;

        try {
            // Captura o token CSRF padrão do Laravel injetado na Meta tag do Blade
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/pdv/faturar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    orcamento_id: window.orcamentoAtualId
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Falha ao finalizar o faturamento no servidor.');
            }

            alert('Venda finalizada com sucesso! Orçamento marcado como Faturado.');
            
            // 🔄 Limpa o PDV para a próxima venda
            window.orcamentoAtualId = null;
            if (typeof limparCamposPDV === 'function') {
                limparCamposPDV();
            }
            document.getElementById('lista-itens').innerHTML = '';
            document.getElementById('totalGeral').textContent = '0,00';

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

    // Função auxiliar para montar o endereço
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

    function setValue(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
    }

    // LISTAGEM DO CARRINHO AJUSTADA PARA O RETORNO REAL DA SUA API
    async function preencherCarrinho(itens) {
        if (!Array.isArray(itens)) return;

        const tbody = document.getElementById('lista-itens');
        if (!tbody) return console.warn('⚠️ Tbody lista-itens não encontrado.');

        tbody.innerHTML = '';

        // Uso de for...of para processar os nós assíncronos em background
        for (const [index, item] of itens.entries()) {
            const tr = document.createElement('tr');
            
            tr.dataset.produtoId = item.produto_id ?? item.id;
            tr.dataset.loteId = item.lote_id ?? '';
            
            const nomeProduto = item.produto?.nome ?? item.nome ?? 'Produto não identificado';
            const siglaUnidade = item.produto?.unidade_medida?.sigla ?? item.unidade_medida?.sigla ?? '';

            const precoUnitario = parseFloat(item.preco_unitario ?? item.produto?.preco_venda ?? item.preco_venda ?? 0);
            const subtotal = parseFloat(item.subtotal ?? 0);

            let qtdRaw = item.quantidade_atendida ?? item.quantidade_solicitada ?? item.quantidade ?? 0;
            let quantity = parseFloat(qtdRaw); 

            if (quantity === 0 && subtotal > 0 && precoUnitario > 0) {
                quantity = subtotal / precoUnitario; 
            }

            // Monta a linha com o indicador visual temporário na coluna do Lote
            tr.innerHTML = `
                <td class="text-center item-numero"><strong>${index + 1}</strong></td>
                <td class="text-center item-lote"><span class="badge bg-secondary opacity-75">Buscando...</span></td>
                <td class="text-left"><strong>${nomeProduto}</strong></td>
                <td class="text-end"><strong>${formatar(precoUnitario)}</strong></td>
                <td class="text-center"><strong>${formatarQuantidade(quantity)}</strong></td>
                <td class="text-center"><strong>${siglaUnidade}</strong></td>
                <td class="text-end subtotal" data-valor="${subtotal}"><strong>${formatar(subtotal)}</strong></td>
            `;
            tbody.appendChild(tr);

            // 🔍 CONSULTA VIA INDICE NA SUA API DE LOTES ATIVOS
            let numeroLote = 'Sem lote';
            try {
                const idProduto = item.produto_id;
                const responseLote = await fetch(`/api/lotes/${idProduto}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (responseLote.ok) {
                    const lotesAtivos = await responseLote.json();
                    
                    // 🔴 CORREÇÃO DO ARRAY: Acessa a primeira posição da tabela retornada
                    if (Array.isArray(lotesAtivos) && lotesAtivos.length > 0) {
                        numeroLote = lotesAtivos[0].numero_lote ?? 'Sem lote';
                        tr.dataset.loteId = lotesAtivos[0].id; // Salva o ID do lote na linha para uso do caixa
                    }
                }
            } catch (error) {
                console.error('Falha ao buscar lote para o produto ' + item.produto_id, error);
                numeroLote = 'Sem lote';
            }

            // Altera apenas o texto interno da coluna injetando o número do lote real
            const celulaLote = tr.querySelector('.item-lote');
            if (celulaLote) {
                celulaLote.innerHTML = `<strong>${numeroLote}</strong>`;
            }
        }

        if (typeof bloquearAlteracoesCarrinho === 'function') {
            bloquearAlteracoesCarrinho();
        }
        atualizarTotalVenda();
    }


    function atualizarTotalVenda() {
        let total = 0;
        document.querySelectorAll('#lista-itens .subtotal').forEach(td => {
            const valor = parseFloat(td.dataset.valor);
            total += isNaN(valor) ? 0 : valor;
        });
        const totalEl = document.getElementById('totalGeral');
        if (totalEl) totalEl.textContent = formatar(total);
    }

    // =========================================
    // FUNÇÕES DE FORMATAÇÃO (CURRENCY BRL)
    // =========================================

    /**
     * Formata um valor numérico bruto para o padrão R$ 0.000,00
     * @param {number|string} valor 
     * @returns {string} Valor formatado em moeda nacional
     */
    function formatar(valor) {
        const numero = parseFloat(valor);
        if (isNaN(numero)) return 'R$ 0,00';
        
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(numero);
    }

    /**
     * Formata a quantidade para exibição limpa no caixa
     * @param {number|string} valor 
     */
    function formatarQuantidade(valor) {
        const numero = parseFloat(valor);
        if (isNaN(numero)) return '0';
        
        // Se for número redondo (ex: 110.00), vira '110'. Se for fracionado (ex: 1.5), vira '1,50'
        return numero % 1 === 0 ? numero.toFixed(0) : numero.toFixed(2).replace('.', ',');
    }


    

});
