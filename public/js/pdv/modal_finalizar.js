// # modal_finalizar.js (Refatorado e Corrigido)

// javascript
window.carrinho = window.carrinho || [];

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // ELEMENTOS PRINCIPAIS
    // =====================================================
    const token           = document.querySelector('meta[name="csrf-token"]')?.content;
    const totalGeralEl    = document.getElementById('totalGeral');
    const totalModalEl    = document.getElementById('total-venda-modal');
    const modalEl         = document.getElementById('modalFinalizarVenda');
    const restanteEl      = document.getElementById('valor-restante');
    const trocoEl         = document.getElementById('valor-troco');
    const btnFinalizar    = document.getElementById('btnFinalizar');

    if (!totalGeralEl || !totalModalEl || !modalEl) {
        console.warn('Elementos principais do PDV não encontrados');
        return;
    }

    const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');

    let modal = null;

    if (typeof bootstrap !== 'undefined') {
        modal = bootstrap.Modal.getInstance(modalEl)
            || new bootstrap.Modal(modalEl);
    }

    // =====================================================
    // VARIÁVEIS GLOBAIS INTERNAS
    // =====================================================
    window.__pdvUltimaFormaFocada = 'dinheiro';
    window.__pdvBufferForma = '';

    // =====================================================
    // HELPERS
    // =====================================================
    function obtenerTotalVenda() {
        return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
    }

    function obtenerSaldoCarteira() {
        return window.cliente?.saldo || 0;
    }

    function formatMoney(valor) {
        return Number(valor || 0).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function parseMoney(valor) {
        if (!valor) return 0;

        return parseFloat(
            valor
                .toString()
                .replace('R$', '')
                .replace(/\./g, '')
                .replace(',', '.')
                .replace(/\s/g, '')
        ) || 0;
    }

    function calcularRestante(inputAtual = null) {

        const total = obtenerTotalVenda();

        let soma = 0;

        inputsPagamento.forEach(input => {
            if (input !== inputAtual) {
                soma += parseFloat(input.value) || 0;
            }
        });

        const restante = total - soma;

        return restante > 0 ? restante : 0;
    }

    // =====================================================
    // ABRIR MODAL FINALIZAR
    // =====================================================
    // async function abrirModalFinalizar() {

    //     const inputCliente =
    //         document.querySelector('input[name="cliente_id"]') ||
    //         document.getElementById('input-cliente-id');

    //     const clienteId = inputCliente?.value;

    //     // =====================================
    //     // ATUALIZA SALDO REAL DO CLIENTE
    //     // =====================================
    //     if (clienteId) {

    //         try {

    //             const response = await fetch(`/clientes/${clienteId}/financeiro`);

    //             const data = await response.json();

    //             if (data.success && window.cliente) {

    //                 window.cliente.saldo = Number(data.saldo || 0);
    //                 window.cliente.limite = Number(data.limite || 0);
    //                 window.cliente.status = data.status || 'bloqueado';

    //                 const saldoEl = document.getElementById('saldo-cliente-modal');

    //                 if (saldoEl) {

    //                     const statusBadge =
    //                         data.status === 'ativo'
    //                             ? '<span class="badge bg-success">Ativo</span>'
    //                             : '<span class="badge bg-danger">Bloqueado</span>';

    //                     saldoEl.innerHTML = `
    //                         Status: ${statusBadge}<br>
    //                         Saldo: R$ ${Number(data.saldo).toFixed(2).replace('.', ',')}<br>
    //                         Limite: R$ ${Number(data.limite).toFixed(2).replace('.', ',')}
    //                     `;
    //                 }
    //             }

    //         } catch (error) {

    //             console.error('Erro ao atualizar financeiro:', error);
    //         }
    //     }

    //     // =====================================
    //     // RESTO DA SUA LÓGICA ORIGINAL
    //     // =====================================
    //     const total = obtenerTotalVenda();

    //     totalModalEl.textContent = total.toLocaleString('pt-BR', {
    //         style: 'currency',
    //         currency: 'BRL'
    //     });

    //     inputsPagamento.forEach(i => {
    //         i.value = '';
    //         i.disabled = false;
    //     });

    //     window.__pdvUltimaFormaFocada = 'dinheiro';

    //     atualizarResumo();

    //     modal?.show();

    //     setTimeout(() => {
    //         if (inputsPagamento && inputsPagamento.length > 0) {
    //             inputsPagamento[0].focus();
    //         }
    //     }, 300);
    // }

    async function abrirModalFinalizar() {
    // =====================================
    // TRAVA DE SEGURANÇA: VALIDAÇÃO DO CARRINHO VISUAL
    // =====================================
    // IMPORTANTE: Altere o seletor abaixo para o ID ou classe real da sua tabela de itens
    // Exemplo: '#tabela-itens tbody tr' ou '.table-produtos tbody tr'
    const itensNoCarrinho = document.querySelectorAll('#tabelaProdutos tbody tr');
    
    // Se a tabela não tiver linhas (ou apenas a linha de 'nenhum produto encontrado')
    if (itensNoCarrinho.length === 0) {
        // Exibe o modal de atenção operacional nativo do seu sistema
        const modalAtencao = window.bootstrap?.Modal?.getInstance(document.getElementById('modalAtencaoCarrinhoVazio')) 
            || new bootstrap.Modal(document.getElementById('modalAtencaoCarrinhoVazio'));
        
        modalAtencao.show();
        return; // Aborta a execução imediatamente para não tentar abrir o fechamento
    }

    const inputCliente =
        document.querySelector('input[name="cliente_id"]') ||
        document.getElementById('input-cliente-id');

    const clienteId = inputCliente?.value;

    // =====================================
    // ATUALIZA SALDO REAL DO CLIENTE
    // =====================================
    if (clienteId) {
        try {
            const response = await fetch(`/clientes/${clienteId}/financeiro`);
            const data = await response.json();

            if (data.success && window.cliente) {
                window.cliente.saldo = Number(data.saldo || 0);
                window.cliente.limite = Number(data.limite || 0);
                window.cliente.status = data.status || 'bloqueado';

                const saldoEl = document.getElementById('saldo-cliente-modal');

                if (saldoEl) {
                    const statusBadge =
                        data.status === 'ativo'
                            ? '<span class="badge bg-success">Ativo</span>'
                            : '<span class="badge bg-danger">Bloqueado</span>';

                    saldoEl.innerHTML = `
                        Status: ${statusBadge}<br>
                        Saldo: R$ ${Number(data.saldo).toFixed(2).replace('.', ',')}<br>
                        Limite: R$ ${Number(data.limite).toFixed(2).replace('.', ',')}
                    `;
                }
            }
        } catch (error) {
            console.error('Erro ao atualizar financeiro:', error);
        }
    }

    // =====================================
    // PROCESSAMENTO DO FECHAMENTO
    // =====================================
    const total = obtenerTotalVenda();

    totalModalEl.textContent = total.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    inputsPagamento.forEach(i => {
        i.value = '';
        i.disabled = false;
    });

    window.__pdvUltimaFormaFocada = 'dinheiro';

    atualizarResumo();

    modal?.show();

    setTimeout(() => {
        if (inputsPagamento && inputsPagamento.length > 0) {
            inputsPagamento[0].focus();
        }
    }, 300);
    }


    window.abrirModalFinalizar = abrirModalFinalizar;

    // =====================================================
    // CLIENTE FINANCEIRO
    // =====================================================
    function carregarClienteFinanceiro(clienteId) {

        if (!clienteId || clienteId == 6) {

            window.cliente = {
                status: 'bloqueado',
                saldo: 0
            };

            aplicarFormasPermitidas();
            atualizarResumo();

            return;
        }

        fetch(`/api/cliente/financeiro/${clienteId}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Erro na API');
                }
                return res.json();
            })
            .then(data => {

                window.cliente = {
                    id: clienteId,
                    nome: data.cliente?.nome ?? data.cliente ?? '',
                    saldo: Number(data.saldo ?? data.saldo_atual ?? 0),
                    limite: Number(data.limite ?? data.limite_credito ?? 0),
                    credito_usado: Number(data.credito_usado ?? 0),
                    formas: data.formas_pagamento ?? [],
                    status: data.status ?? data.credito_status ?? 'ativo'
                };

                aplicarFormasPermitidas();
                atualizarResumo();
            })
            .catch(err => {
                console.error('Erro cliente financeiro:', err);
            });
            
    }

    // =====================================================
    // FORMAS PERMITIDAS
    // =====================================================
    function aplicarFormasPermitidas() {

        if (!window.cliente) return;

        const saldo = obtenerSaldoCarteira();
        const statusCredito = window.cliente.status;

        inputsPagamento.forEach(input => {

            const forma = input.dataset.forma;

            if (forma === 'carteira') {

                input.disabled = false;

                if (statusCredito !== 'ativo') {
                    input.placeholder = 'Crédito Bloqueado';
                }
                else if (saldo <= 0) {
                    input.placeholder = 'Sem saldo disponível';
                }
                else {
                    input.placeholder = `Até R$ ${saldo.toFixed(2)}`;
                }
            }
        });
    }

    // =====================================================
    // ATUALIZAR RESUMO
    // =====================================================
    function atualizarResumo() {

        const total = obtenerTotalVenda();

        let soma = 0;

        inputsPagamento.forEach(input => {
            soma += parseFloat(input.value) || 0;
        });

        let restante = total - soma;
        let troco = 0;

        if (restante < 0) {
            troco = Math.abs(restante);
            restante = 0;
        }

        restanteEl.textContent = formatMoney(restante);
        trocoEl.textContent = formatMoney(troco);
    }

    // =====================================================
    // BLOQUEIO DE PAGAMENTOS
    // =====================================================
    function controlarBloqueioPagamento() {

        const inputDinheiro = modalEl.querySelector(
            '.pagamento-modal[data-forma="dinheiro"]'
        );

        if (!inputDinheiro) return false;

        const valorDinheiro = parseFloat(
            inputDinheiro.value.replace(',', '.')
        ) || 0;

        const total = obtenerTotalVenda();

        const bloquear = valorDinheiro >= total;

        inputsPagamento.forEach(input => {

            const forma = input.dataset.forma;

            if (forma === 'dinheiro') {
                input.disabled = false;
                return;
            }

            input.disabled = bloquear;

            if (bloquear) {
                input.value = '';
            }
        });

        atualizarResumo();

        return bloquear;
    }

    // =====================================================
    // CARTEIRA INTELIGENTE
    // =====================================================
    function recalcularETransferirExcedenteCarteira(inputCarteira) {

        const saldoDisponivel = obtenerSaldoCarteira();
        const statusCredito = window.cliente?.status;
        const valorDigitado = parseFloat(inputCarteira.value) || 0;

        let formaDestino =
            window.__pdvUltimaFormaFocada === 'carteira'
                ? 'dinheiro'
                : window.__pdvUltimaFormaFocada;

        const inputDestino = modalEl.querySelector(
            `.pagamento-modal[data-forma="${formaDestino}"]`
        );

        if (!inputDestino) return;

        if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {

            if (valorDigitado > 0) {

                const valorAtualDestino = parseFloat(inputDestino.value) || 0;

                inputDestino.value = (
                    valorAtualDestino + valorDigitado
                ).toFixed(2);

                inputCarteira.value = '';

                inputDestino.dispatchEvent(
                    new Event('input', { bubbles: true })
                );
            }

            return;
        }

        if (valorDigitado > saldoDisponivel) {

            const excedente = valorDigitado - saldoDisponivel;

            inputCarteira.value = saldoDisponivel.toFixed(2);

            const valorAtualDestino = parseFloat(inputDestino.value) || 0;

            inputDestino.value = (
                valorAtualDestino + excedente
            ).toFixed(2);

            inputDestino.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        }
    }

    // =====================================================
    // INPUTS PAGAMENTO
    // =====================================================
    inputsPagamento.forEach(input => {

        input.addEventListener('focus', function () {
            window.__pdvUltimaFormaFocada = this.dataset.forma;
        });

        input.addEventListener('input', function () {

            const forma = this.dataset.forma;

            let valor = parseFloat(this.value) || 0;

            const restanteSemEsteInput = calcularRestante(this);

            if (forma === 'carteira') {

                recalcularETransferirExcedenteCarteira(this);

                const saldoDisponivel = obtenerSaldoCarteira();

                if (valor > saldoDisponivel) {
                    this.value = saldoDisponivel.toFixed(2);
                    valor = saldoDisponivel;
                }
            }

            if (forma !== 'dinheiro' && valor > restanteSemEsteInput) {
                this.value = restanteSemEsteInput.toFixed(2);
            }

            atualizarResumo();

            if (forma === 'dinheiro') {
                controlarBloqueioPagamento();
            }
        });

        input.addEventListener('blur', function () {

            let valor = this.value.replace(',', '.');
            const numero = parseFloat(valor);

            if (isNaN(numero) || numero <= 0) {
                this.value = '';
                atualizarResumo();
                return;
            }

            this.value = numero.toFixed(2);

            atualizarResumo();
        });

        input.addEventListener('keydown', function (e) {

            if (e.key === 'Enter') {

                e.preventDefault();

                const restante = parseMoney(restanteEl.textContent);

                if (restante <= 0) {
                    btnFinalizar?.focus();
                }
            }
        });
    });

    // =====================================================
    // ATALHOS TECLADO
    // =====================================================
    document.addEventListener('keydown', function (e) {

        if (!modalEl.classList.contains('show')) return;

        const tecla = e.key.toLowerCase();

        window.__pdvBufferForma += tecla;
        window.__pdvBufferForma = window.__pdvBufferForma.slice(-2);

        let forma = null;

        switch (window.__pdvBufferForma) {
            case 'dd': forma = 'dinheiro'; break;
            case 'cc': forma = 'cartao_credito'; break;
            case 'cd': forma = 'cartao_debito'; break;
            case 'pi': forma = 'pix'; break;
            case 'ca': forma = 'carteira'; break;
        }

        if (!forma) return;

        const input = modalEl.querySelector(
            `.pagamento-modal[data-forma="${forma}"]`
        );

        if (!input) return;

        const bloqueado = controlarBloqueioPagamento();

        if (bloqueado && forma !== 'dinheiro') {
            window.__pdvBufferForma = '';
            return;
        }

        e.preventDefault();

        const valorRestante = parseMoney(restanteEl.textContent);
        const valorAtualCampo = parseFloat(input.value) || 0;

        let valorFinal = valorRestante + valorAtualCampo;

        if (forma === 'carteira') {

            const saldoDisponivel = obtenerSaldoCarteira();
            const statusCredito = window.cliente?.status;

            if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
                window.__pdvBufferForma = '';
                return;
            }

            if (valorFinal > saldoDisponivel) {
                valorFinal = saldoDisponivel;
            }
        }

        input.focus();
        input.value = valorFinal.toFixed(2);
        input.select();

        input.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        window.__pdvBufferForma = '';
    });

    // =====================================================
    // LIMPEZA TOTAL PDV
    // =====================================================
    function limparPDV() {

        window.carrinho = [];

        inputsPagamento.forEach(input => {
            input.value = '';
            input.disabled = false;
        });

        atualizarResumo();

        const campos = [
            'descricao',
            'codigo_barras',
            'preco_venda',
            'quantidade',
            'qtd_disponivel',
            'total_geral',
            'unidade'
        ];

        campos.forEach(id => {
            const campo = document.getElementById(id);
            if (campo) {
                campo.value = '';
            }
        });

        if (totalGeralEl) {
            totalGeralEl.textContent = 'R$ 0,00';
        }

        modal?.hide();

        setTimeout(() => {
            document.getElementById('codigo_barras')?.focus();
        }, 150);
    }

    // =====================================================
    // FINALIZAR VENDA
    // =====================================================
    async function finalizarVenda(e) {

        e.preventDefault();

        if (btnFinalizar.disabled) return;

        const inputCliente =
            document.querySelector('input[name="cliente_id"]')
            || document.getElementById('input-cliente-id');

        const inputFuncionario =
            document.querySelector('input[name="operador_id"]')
            || document.querySelector('input[name="funcionario_id"]')
            || document.getElementById('input-operador-id');

        const inputCaixa =
            document.querySelector('input[name="caixa_id"]')
            || document.getElementById('input-caixa-id');

        const inputData = document.getElementById('dataVenda');

        const cliente_id = inputCliente?.value || null;
        const funcionario_id = inputFuncionario?.value || null;
        const caixa_id = inputCaixa?.value || null;
        const dataVenda = inputData?.value || null;

        if (!funcionario_id || !caixa_id) {
            alert('Operador ou caixa não identificados');
            return;
        }

        // Captura o carrinho da memória
        let itens = window.carrinho || [];

        // Dupla Validação: Remove da memória qualquer item inválido ou sem ID de produto
        itens = itens.filter(item => item && (item.produto_id || item.id || item.codigo));

        // Validação Visual: Conta as linhas reais de produtos na tabela do seu PDV
        // AJUSTE SEU SELETOR: Substitua '#tabelaItensPDV tbody tr' pelo ID real da sua tabela de vendas
        const linhasTabelaProdutos = document.querySelectorAll('#tabelaItensPDV tbody tr').length;

        // Se a memória estiver vazia OU se não houver linhas visíveis na tabela do PDV, BLOQUEIA!
        if (itens.length === 0 || linhasTabelaProdutos === 0) {
            alert('🚨 ERRO GRAVÍSSIMO: Não é possível finalizar uma venda sem produtos no carrinho!');
            
            // Força a limpeza da memória para evitar lixo acumulado de vendas anteriores
            window.carrinho = []; 
            return;
        }

        const pagamentos = [];

        inputsPagamento.forEach(input => {

            const valor = parseFloat(input.value) || 0;

            if (valor > 0) {
                pagamentos.push({
                    forma: input.dataset.forma,
                    valor
                });
            }
        });

        if (!pagamentos.length) {
            alert('Informe uma forma de pagamento');
            return;
        }

        const textoOriginalBtn = btnFinalizar.innerHTML;

        btnFinalizar.disabled = true;

        btnFinalizar.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            Processando...
        `;

        try {

            const res = await fetch('/vendas/finalizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    cliente_id,
                    funcionario_id,
                    caixa_id,
                    dataVenda,
                    pagamentos,
                    itens,
                    // 🎯 AQUI ESTÁ A IDENTIFICAÇÃO EXATA:
                    // Envia o ID ou Código do orçamento que está aberto na tela (se houver)
                    orcamento_id: window.orcamentoAtualId 
                })
            });

            const dataFinal = await res.json();

            // if (dataFinal.cupom_url) {
            //     let urlCorreta = dataFinal.cupom_url;
                
            //     // Se a URL enviada pelo Laravel não tiver o prefixo correto, ajusta dinamicamente
            //     if (!urlCorreta.includes('/vendas/')) {
            //         urlCorreta = urlCorreta.replace('/venda/', '/vendas/venda/');
            //     }

            //     window.location.href = urlCorreta;
            //     return;
            // }

            // limparPDV();

            // if (dataFinal.cupom_url) {
            //     window.location.href = dataFinal.cupom_url;
            //     return;
            // }

            // alert('Venda finalizada com sucesso!');
            // Aqui você pode optar por recarregar a página ou apenas limpar o modal e o carrinho, dependendo do fluxo desejado
            // location.reload();

            const dataFinal = await res.json();

            if (!res.ok || !dataFinal.success) {
                throw new Error(dataFinal.erro || 'Erro ao finalizar venda');
            }

            // 🎯 GATILHO DE SUCESSO: Limpa a memória do navegador antes de atualizar a página ou cupom
            if (typeof PdvStorage !== 'undefined') {
                PdvStorage.limparPdv();
            }

            limparPDV(); // Sua função original que zera o HTML e a array da tela

            if (dataFinal.cupom_url) {
                window.location.href = dataFinal.cupom_url;
                return;
            }


        }

        catch (err) {

            console.error(err);
            alert(err.message);

            btnFinalizar.disabled = false;
            btnFinalizar.innerHTML = textoOriginalBtn;

            btnFinalizar.focus();
        }
        finally {

            btnFinalizar.disabled = false;

            btnFinalizar.innerHTML = 'Finalizar Venda';
        }
    }

    // =====================================================
    // EVENTOS FINALIZAÇÃO
    // =====================================================
       btnFinalizar.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            // 🎯 GATILHO CHAVE: Garante que os dados finais da memória estejam gravados 
            // no LocalStorage no milissegundo anterior ao disparo do fetch
            if (typeof PdvStorage !== 'undefined' && window.carrinho) {
                PdvStorage.salvarCarrinho(window.carrinho);
            }

            finalizarVenda(e);
        }
    });

});

