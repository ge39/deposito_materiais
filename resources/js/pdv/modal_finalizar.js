window.carrinho = window.carrinho || [];

document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const totalGeralEl = document.getElementById('totalGeral');
    const totalModalEl = document.getElementById('total-venda-modal');
    const modalEl = document.getElementById('modalFinalizarVenda');
    const restanteEl = document.getElementById('valor-restante');
    const trocoEl = document.getElementById('valor-troco');
    const btnFinalizar = document.getElementById('btnFinalizar');

    // Variável global interna para lembrar qual foi o último input focado pelo operador
    window.__pdvUltimaFormaFocada = 'dinheiro';

    if (!totalGeralEl || !totalModalEl || !modalEl) {
        console.warn('Elementos principais do PDV não encontrados');
        return;
    }

    const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');
    let modal = null;

    if (typeof bootstrap !== 'undefined') {
        modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    } else {
        console.warn('Bootstrap não carregado');
    }

    // ========================================== //
    // HELPERS CORRIGIDOS                         //
    // ========================================== //
    function obterTotalVenda() {
        return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
    }

    function obterSaldoCarteira() {
        return window.cliente?.saldo || 0;
    }

    function calcularRestante(inputAtual = null) {
        const total = obterTotalVenda();
        let s = 0;
        inputsPagamento.forEach(i => {
            if (i !== inputAtual) {
                s += parseFloat(i.value) || 0;
            }
        });
        const r = total - s;
        return r > 0 ? r : 0;
    }

    // ========================================================== //
    // 🔥 HELPER INTELIGENTE: DISTRIBUI EXCEDENTE NA FORMA CORRETA //
    // ========================================================== //
    function recalcularETransferirExcedenteCarteira(inputCarteira) {
        const saldoDisponivel = obterSaldoCarteira();
        const statusCredito = window.cliente?.status;
        const valorDigitado = parseFloat(inputCarteira.value) || 0;

        let formaDestino = window.__pdvUltimaFormaFocada === 'carteira' ? 'dinheiro' : window.__pdvUltimaFormaFocada;
        const inputDestino = modalEl.querySelector(`.pagamento-modal[data-forma="${formaDestino}"]`);
        if (!inputDestino) return;

        const nomeFormaAmigavel = formaDestino.replace('_', ' ').toUpperCase();

        // 🔴 CENÁRIO 1: Crédito bloqueado ou sem saldo
        if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
            if (valorDigitado > 0) {
                const valorAtualDestino = parseFloat(inputDestino.value) || 0;
                inputDestino.value = (valorAtualDestino + valorDigitado).toFixed(2);
                inputCarteira.value = '';
                inputDestino.dispatchEvent(new Event('input', { bubbles: true }));
                inputDestino.focus();
                alert(`Este cliente está com o crediário bloqueado ou sem saldo. O valor foi transferido para ${nomeFormaAmigavel}.`);
            }
            return;
        }

        // 🟡 CENÁRIO 2: Valor digitado maior que o saldo restante da carteira
        if (valorDigitado > saldoDisponivel) {
            const excedente = valorDigitado - saldoDisponivel;
            inputCarteira.value = saldoDisponivel.toFixed(2);
            const valorAtualDestino = parseFloat(inputDestino.value) || 0;
            inputDestino.value = (valorAtualDestino + excedente).toFixed(2);
            inputDestino.dispatchEvent(new Event('input', { bubbles: true }));
            inputDestino.focus();
            alert(`O saldo da carteira é de R$ ${saldoDisponivel.toFixed(2).replace('.', ',')}. O restante (R$ ${excedente.toFixed(2).replace('.', ',')}) foi direcionado para ${nomeFormaAmigavel}.`);
        }
    }

    // ========================================== //
    // DINHEIRO: ZERA SE TOTAL JÁ FECHADO         //
    // ========================================== //
    function controlarDinheiroQuandoFechado() {
        const totalVenda = obterTotalVenda();
        let s = 0;
        inputsPagamento.forEach(i => {
            const v = parseFloat(i.value) || 0;
            if (v > 0) s += v;
        });

        const inputDinheiro = document.querySelector('.pagamento-modal[data-forma="dinheiro"]');
        if (!inputDinheiro) return;

        if (Math.abs(s - totalVenda) < 0.01) {
            if (parseFloat(inputDinheiro.value) > 0) {
                inputDinheiro.value = '0.00';
                trocoEl.textContent = (0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                atualizarResumo();
            }
        }
    }

    // ========================================== //
    // ATALHOS TECLADO COM DESBLOQUEIO DE SELEÇÃO //
    // ========================================== //
    document.addEventListener('keydown', function (e) {
        if (!modalEl || !modalEl.classList.contains('show')) return;
        const tecla = e.key.toLowerCase();
        window.__pdvBufferForma = (window.__pdvBufferForma || '') + tecla;
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
        const input = modalEl.querySelector(`.pagamento-modal[data-forma="${forma}"]`);
        if (!input) return;

        e.preventDefault();
        window.__pdvUltimaFormaFocada = forma;
        
        const totalVenda = obterTotalVenda();
        let somaOutros = 0;
        inputsPagamento.forEach(i => {
            if (i !== input) {
                const v = parseFloat(i.value) || 0;
                if (v > 0) somaOutros += v;
            }
        });

        let valorDisponivel = totalVenda - somaOutros;
        if (valorDisponivel < 0) valorDisponivel = 0;

        input.focus();
        input.value = valorDisponivel.toFixed(2);
        
        // Micro-delay para garantir compatibilidade do select() após a renderização do foco
        setTimeout(() => input.select(), 50);
        
        input.dispatchEvent(new Event('input', { bubbles: true }));
        window.__pdvBufferForma = '';
    });

    // ========================================== //
    // REGRAS DE PERMISSÃO E EXIBIÇÃO DE CRÉDITO   //
    // ========================================== //
    function aplicarFormasPermitidas() {
        if (!window.cliente) return;
        const saldo = obterSaldoCarteira();
        const statusCredito = window.cliente.status;

        inputsPagamento.forEach(input => {
            const forma = input.dataset.forma;
            if (forma === 'carteira') {
                input.disabled = false;
                if (statusCredito !== 'ativo') {
                    input.placeholder = 'Crédito Bloqueado';
                } else if (saldo <= 0) {
                    input.placeholder = 'Sem saldo disponível';
                } else {
                    input.placeholder = `Até R$ ${saldo.toFixed(2)}`;
                }
            }
        });
    }

    function atualizarResumo() {
        const total = obterTotalVenda();
        let s = 0;
        inputsPagamento.forEach(i => {
            s += parseFloat(i.value) || 0;
        });

        let restante = total - s;
        let troco = 0;

        if (restante < 0) {
            troco = Math.abs(restante);
            restante = 0;
        }

        if (restanteEl) {
            restanteEl.textContent = restante.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }
        if (trocoEl) {
            trocoEl.textContent = troco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        const inputCarteira = modalEl.querySelector('[data-forma="carteira"]');
        if (inputCarteira) {
            const saldo = obterSaldoCarteira();
            const statusCredito = window.cliente?.status;
            if (statusCredito !== 'ativo') {
                inputCarteira.placeholder = 'Crédito Bloqueado';
            } else if (saldo <= 0) {
                inputCarteira.placeholder = 'Saldo Insuficiente';
            } else {
                inputCarteira.placeholder = `Até R$ ${saldo.toFixed(2)}`;
            }
        }
    }

    // ========================================== //
    // CARREGAMENTO DA API FINANCEIRA             //
    // ========================================== //
    function carregarClienteFinanceiro(clienteId) {
        if (!clienteId || clienteId == 6) {
            // Limpa o estado ou define cliente balcão explicitamente bloqueado para carteira
            window.cliente = { status: 'bloqueado', saldo: 0 };
            aplicarFormasPermitidas();
            atualizarResumo();
            return;
        }

        fetch(`/api/cliente/financeiro/${clienteId}`)
            .then(res => {
                if (!res.ok) throw new Error('Erro na API');
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
            .catch(err => console.error('Erro cliente financeiro:', err));
    }

    function abrirModalFinalizar() {
        const inputCliente = document.querySelector('input[name="cliente_id"]') || document.getElementById('input-cliente-id');
        const clienteId = inputCliente?.value;
        
        carregarClienteFinanceiro(clienteId);
        
        const total = obterTotalVenda();
        totalModalEl.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        inputsPagamento.forEach(i => i.value = '');
        window.__pdvUltimaFormaFocada = 'dinheiro';
        atualizarResumo();
        
        modal?.show();

        setTimeout(() => {
            if (inputsPagamento && inputsPagamento.length > 0) {
                inputsPagamento[0].focus();
            }
        }, 300);
    }

    // ========================================== //
    // INPUTS EVENTS COM CAPTURA DE FOCO ATIVO    //
    // ========================================== //
    inputsPagamento.forEach(input => {
        input.addEventListener('focus', function() {
            window.__pdvUltimaFormaFocada = this.dataset.forma;
        });

        input.addEventListener('input', function () {
            const forma = this.dataset.forma;
            let valor = parseFloat(this.value) || 0;
            const restanteSemEsteInput = calcularRestante(this);

            if (forma === 'carteira') {
                const saldoDisponivel = obterSaldoCarteira();
                const statusCredito = window.cliente?.status;
                if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
                    this.value = '';
                    atualizarResumo();
                    return;
                }
                if (valor > saldoDisponivel) {
                    this.value = saldoDisponivel.toFixed(2);
                    valor = saldoDisponivel;
                }
            }

            if (forma !== 'dinheiro' && valor > restanteSemEsteInput) {
                this.value = restanteSemEsteInput.toFixed(2);
            }
            atualizarResumo();
        });

        input.addEventListener('blur', function () {
            let valor = this.value.replace(',', '.');
            const n = parseFloat(valor);
            if (isNaN(n) || n <= 0) {
                this.value = '';
                atualizarResumo();
                return;
            }
            this.value = n.toFixed(2);
            atualizarResumo();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                let valorTexto = this.value.replace(',', '.');
                const n = parseFloat(valorTexto);
                if (!isNaN(n) && n > 0) {
                    this.value = n.toFixed(2);
                } else {
                    this.value = '';
                }

                const totalVenda = obterTotalVenda();
                let somaTotalInputs = 0;
                inputsPagamento.forEach(i => {
                    somaTotalInputs += parseFloat(i.value) || 0;
                });

                let valorRestanteReal = totalVenda - somaTotalInputs;
                if (valorRestanteReal < 0) valorRestanteReal = 0;

                if (Math.abs(valorRestanteReal) < 0.01) {
                    e.preventDefault();
                    atualizarResumo();
                    if (btnFinalizar) {
                        btnFinalizar.focus();
                    }
                }
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'F6') {
            e.preventDefault();
            abrirModalFinalizar();
        }
    });

    // ========================================== //
    // FINALIZAR VENDA (UMA REQUISIÇÃO INTEGRADA) //
    // ========================================== //
    if (btnFinalizar) {
        async function finalizarVenda(e) {
            e.preventDefault();
            if (btnFinalizar.disabled) return;

              // 🔥 BLINDAGEM VISUAL: Exibe o conteúdo real do carrinho no console do navegador antes do fetch
            // console.log("=== RASTREAMENTO DO CARRINHO ANTES DO ENVIO ===");
            // console.log("Tipo do objeto:", typeof window.carrinho);
            // console.log("É um Array?", Array.isArray(window.carrinho));
            // console.log("Quantidade de itens:", window.carrinho ? window.carrinho.length : 0);
            // console.table(window.carrinho); // Renderiza uma tabela estruturada com todas as colunas e chaves

            // // 🔥 ALERTA NA TELA COM O JSON BRUTO: Interrompe a execução para você ler as propriedades
            // alert("Conteúdo atual do carrinho:\n\n" + JSON.stringify(window.carrinho, null, 2));

            // Para parar o envio aqui e permitir que você analise com calma, descomente a linha abaixo:
            // return;
            
            const inputCliente = document.querySelector('input[name="cliente_id"]') || document.getElementById('input-cliente-id');
            const inputFuncionario = document.querySelector('input[name="operador_id"]') || document.querySelector('input[name="funcionario_id"]') || document.getElementById('input-operador-id');
            const inputCaixa = document.querySelector('input[name="caixa_id"]') || document.getElementById('input-caixa-id');
            const inputData = document.getElementById('dataVenda');

            const cliente_id = inputCliente?.value || null;
            const funcionario_id = inputFuncionario?.value || null;
            const caixa_id = inputCaixa?.value || null;
            const dataVenda = inputData?.value || null;

            if (!funcionario_id || !caixa_id) {
                alert(`Erro local: Operador (${funcionario_id}) ou Caixa (${caixa_id}) não identificados no formulário.`);
                return;
            }

            const itens = window.carrinho || [];
            if (!itens.length) {
                alert('Adicione itens antes de finalizar');
                return;
            }

            const pagamentos = [];
            inputsPagamento.forEach(input => {
                const valor = parseFloat(input.value) || 0;
                if (valor > 0) {
                    pagamentos.push({ forma: input.dataset.forma, valor });
                }
            });

            if (!pagamentos.length) {
                alert('Informe uma forma de pagamento');
                return;
            }

            const textoOriginalBtn = btnFinalizar.innerHTML;
            btnFinalizar.disabled = true;
            btnFinalizar.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...`;

            try {
                const res = await fetch('/vendas/finalizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ cliente_id, funcionario_id, caixa_id, dataVenda, pagamentos, itens })
                });

                const text = await res.text();

                if (!res.ok) {
                    let msgErro = "Erro no servidor ao processar venda";
                    try {
                        const jsonErro = JSON.parse(text);
                        if (jsonErro.erro) msgErro = jsonErro.erro;
                    } catch (e) {}
                    console.error("ERRO HTTP DETALHADO:", res.status, text);
                    throw new Error(msgErro);
                }

                let dataFinal;
                try {
                    dataFinal = JSON.parse(text);
                } catch (e) {
                    throw new Error("Servidor retornou resposta inválida (HTML)");
                }

                if (!dataFinal.success) {
                    throw new Error(dataFinal.erro || 'Erro ao processar pagamentos');
                }

                window.carrinho = [];
                modal?.hide();

                // Intercepta e executa o redirecionamento de sangria emitido pelo backend
                if (dataFinal.redirect_sangria && dataFinal.url) {
                    alert('Venda finalizada! O limite do caixa foi atingido. Redirecionando para Sangria...');
                    window.location.href = dataFinal.url;
                } else {
                    alert('Venda finalizada com sucesso!');
                    location.reload();
                }

            } catch (err) {
                console.error(err);
                alert(err.message);
                btnFinalizar.disabled = false;
                btnFinalizar.innerHTML = textoOriginalBtn;
                btnFinalizar.focus();
            }
        }

        btnFinalizar.addEventListener('click', finalizarVenda);
        btnFinalizar.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') finalizarVenda(e);
        });
    }
});
