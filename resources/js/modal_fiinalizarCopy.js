window.carrinho = window.carrinho || [];

document.addEventListener('DOMContentLoaded', function () {

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const totalGeralEl   = document.getElementById('totalGeral');
    const totalModalEl   = document.getElementById('total-venda-modal');
    const modalEl        = document.getElementById('modalFinalizarVenda');
    const restanteEl     = document.getElementById('valor-restante');
    const trocoEl        = document.getElementById('valor-troco');
    const btnFinalizar   = document.getElementById('btnFinalizar');

    // 🔒 proteção total (não quebra tela)
    if (!totalGeralEl || !totalModalEl || !modalEl) {
        console.warn('Elementos principais do PDV não encontrados');
        return;
    }

    const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');

    // 🔒 bootstrap seguro
    let modal = null;
    if (typeof bootstrap !== 'undefined') {
        modal = new bootstrap.Modal(modalEl);
    } else {
        console.warn('Bootstrap não carregado');
    }

    // =========================
    // HELPERS
    // =========================
    function obterTotalVenda() {
        return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
    }

    function obterSaldoCarteira() {
        return window.cliente?.saldo || 0;
    }

    function calcularRestante(inputAtual = null) {

        const total = obterTotalVenda();
        let soma = 0;

        inputsPagamento.forEach(i => {
            if (i !== inputAtual) {
                soma += parseFloat(i.value) || 0;
            }
        });

        let restante = total - soma;
        return restante > 0 ? restante : 0;
    }

    // =========================
    // DINHEIRO: zera se total já fechado
    // =========================
    function controlarDinheiroQuandoFechado() {

        const totalVenda = obterTotalVenda();

        let soma = 0;
        inputsPagamento.forEach(i => {
            const v = parseFloat(i.value) || 0;
            if (v > 0) soma += v;
        });

        const inputDinheiro = document.querySelector(
            '.pagamento-modal[data-forma="dinheiro"]'
        );

        if (!inputDinheiro) return;

        // se já fechou o total, dinheiro sempre 0,00
        if (Math.abs(soma - totalVenda) < 0.01) {

            if (parseFloat(inputDinheiro.value) > 0) {
                inputDinheiro.value = '0.00';

                // zera troco explicitamente
                trocoEl.textContent = (0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });

                atualizarResumo();
            }
        }
    }
    
    // =========================
    // ATALHOS DD / CC / CD / PI / CA
    // =========================
    document.addEventListener('keydown', function (e) {

    const modalEl = document.getElementById('modalFinalizarVenda');
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

    const input = modalEl.querySelector(
        `.pagamento-modal[data-forma="${forma}"]`
    );
    if (!input) return;

    e.preventDefault();

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
    input.select();

        input.dispatchEvent(new Event('input', { bubbles: true }));

        window.__pdvBufferForma = '';
    });

    //constrolarDinheiroPeloRestante();
    function controlarDinheiroPeloRestante() {

    const restanteTexto = restanteEl.textContent
        .replace(/\./g, '')
        .replace(',', '.')
        .replace(/[^\d.]/g, '');
    const restante = parseFloat(restanteTexto) || 0;

    const inputDinheiro = document.querySelector(
        '.pagamento-modal[data-forma="dinheiro"]'
    );

    if (!inputDinheiro) return;

        // se restante = 0, dinheiro volta para 0,00
        if (restante ===  0) {
            if ((parseFloat(inputDinheiro.value) || 0) !== 0) {
                inputDinheiro.value = '0.00';
                inputDinheiro.select();

                // zera troco explicitamente
                trocoEl.textContent = (0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });

                atualizarResumo();
            }
        }
    }

   
    // function aplicarFormasPermitidas() {

    //     if (!window.cliente) return;

    //     const saldo = window.cliente.saldo || 0;
    //     const status = (window.cliente.status_credito || '').toLowerCase();

    //     inputsPagamento.forEach(input => {

    //         const forma = input.dataset.forma;
    //         let permitido = true;

    //         if (forma === 'carteira') {

    //             // 🔥 bloqueia se status inválido
    //             const statusBloqueado = ['bloqueado', 'cancelado'].includes(status);

    //             permitido = !statusBloqueado && saldo > 0;
    //         }

    //         input.disabled = !permitido;

    //         if (!permitido) {
    //             input.value = '';

    //             if (['bloqueado', 'cancelado'].includes(status)) {
    //                 input.placeholder = 'Crédito indisponível';
    //             } else {
    //                 input.placeholder = 'Sem saldo disponível';
    //             }
    //         }
    //     });
    // } 
    
    // function aplicarFormasPermitidas() {

    //     if (!window.cliente) return;

    //     const status = window.cliente.status_credito;

    //     inputsPagamento.forEach(input => {

    //         const forma = input.dataset.forma;

    //         let permitido = true;

    //         // 🔥 carteira só se ativo
    //         if (forma === 'carteira') {
    //             permitido = status === 'ativo';
    //         }

    //         input.disabled = !permitido;

    //         if (!permitido) {
    //             input.value = '';

    //             if (status === 'bloqueado') {
    //                 input.placeholder = 'Crédito bloqueado';
    //             } else if (status === 'cancelado') {
    //                 input.placeholder = 'Crédito cancelado';
    //             } else {
    //                 input.placeholder = 'Sem crédito disponível';
    //             }
    //         }
    //     });
    // }

    // function aplicarFormasPermitidas() {

    //     if (!window.cliente) return;

    //     const saldo = Number(window.cliente.saldo || 0);
    //     const status = window.cliente.status_credito;

    //     const carteiraAtiva = status === 'ativo';

    //     inputsPagamento.forEach(input => {

    //         const forma = input.dataset.forma;

    //         let permitido = true;

    //         // 🔥 CARTEIRA (regra principal corrigida)
    //         if (forma === 'carteira') {
    //             permitido = carteiraAtiva && saldo > 0;
    //         }

    //         input.disabled = !permitido;

    //         if (!permitido) {
    //             input.value = '';

    //             // 🔥 mensagens de forma consistente (sem duplicação de ifs)
    //             if (forma === 'carteira') {

    //                 if (!carteiraAtiva) {
    //                     input.placeholder = 'Carteira indisponível';
    //                 } else if (saldo <= 0) {
    //                     input.placeholder = 'Sem saldo disponível';
    //                 } else {
    //                     input.placeholder = 'Indisponível';
    //                 }
    //             }
    //         }
    //     });
    // }

    function aplicarFormasPermitidas() {

    if (!window.cliente) return;

    const saldo = Number(window.cliente.saldo || 0);

    const status = (window.cliente.status_credito || '').toLowerCase();

    const carteiraAtiva = status === 'ativo';

    inputsPagamento.forEach(input => {

        const forma = input.dataset.forma;

        let permitido = true;

        // 🔴 CARTEIRA
        if (forma === 'carteira') {

            // regra única e clara
            if (!carteiraAtiva) {
                permitido = false;
            } else {
                permitido = saldo > 0;
            }
        }

        input.disabled = !permitido;

        if (!permitido) {
            input.value = '';

            if (forma === 'carteira') {

                if (status === 'bloqueado') {
                    input.placeholder = 'Carteira bloqueada';
                } 
                else if (status === 'cancelado') {
                    input.placeholder = 'Carteira cancelada';
                } 
                 else if (status === 'inativo') {
                    input.placeholder = 'Carteira cancelada';
                    console.log('Inativo');
                } 
                else {
                    input.placeholder = 'Carteira indisponível';
                }
            }
        }
    });
}
    
    function atualizarResumo() {

        const total = obterTotalVenda();

        let soma = 0;
        inputsPagamento.forEach(i => {
            soma += parseFloat(i.value) || 0;
        });

        let restante = total - soma;
        let troco = 0;

        if (restante < 0) {
            troco = Math.abs(restante);
            restante = 0;
        }

        if (restanteEl) {
            restanteEl.textContent = restante.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        if (trocoEl) {
            trocoEl.textContent = troco.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        // 🔥 CARTEIRA
        const inputCarteira = modalEl.querySelector('[data-forma="carteira"]');

            if (inputCarteira) {

                const saldo = obterSaldoCarteira();

                // 🔥 agora só bloque
                // ia se não tiver saldo nenhum
                const bloqueado = saldo <= 0;
                
                // console.log('Saldo carteira:', saldo);

                inputCarteira.disabled = bloqueado;

                if (bloqueado) {
                    inputCarteira.value = '';
                    inputCarteira.placeholder = 'Sem saldo disponível';
                } else {
                    const limite = Math.min(restante, saldo);
                    inputCarteira.placeholder = `Até R$ ${limite.toFixed(2)}`;
                }
            }
    }

    // =========================
    // CARREGAR CLIENTE
    // =========================
    // function carregarClienteFinanceiro(clienteId) {

    //     if (!clienteId) return;

    //     fetch(`/api/cliente/financeiro/${clienteId}`)
    //         .then(res => {
    //             if (!res.ok) throw new Error('Erro na API');
    //             return res.json();
    //         })
    //         .then(data => {

    //             // window.cliente = {
    //             //     id: clienteId,
    //             //     nome: data.cliente,
    //             //     saldo: Number(data.saldo_carteira || 0),
    //             //     limite: Number(data.limite_credito || 0),
    //             //     credito_usado: Number(data.credito_usado || 0),
    //             //     formas: data.formas_pagamento || []
    //             // };
    //             window.cliente = {
    //                 id: clienteId,
    //                 nome: data.cliente,
    //                 saldo: Number(data.saldo_apos || 0),
    //                 limite: Number(data.limite_credito || 0),
    //                 credito_usado: Number(data.credito_usado || 0),
    //                 status_credito: data.status_credito, // 🔥 NOVO
    //                 formas: data.formas_pagamento || []
    //             };

    //             console.log('Cliente carregado:', window.cliente);
    //             // 🔥 AGORA sim o saldo existe
    //             aplicarFormasPermitidas();
    //             atualizarResumo();

    //         })
    //         .catch(err => console.error('Erro cliente:', err));
    // }

    function carregarClienteFinanceiro(clienteId) {

        if (!clienteId) return;

        fetch(`/api/cliente/financeiro/${clienteId}`)
            .then(async res => {

                if (!res.ok) {
                    const erro = await res.text(); // 🔥 pega erro real
                    console.error('🔥 ERRO BACKEND:', erro);
                    throw new Error('Erro na API');
                }

                return res.json();
            })
            .then(data => {

                window.cliente = {
                    id: clienteId,
                    nome: data.cliente,
                    saldo: Number(data.saldo_apos || 0),
                    limite: Number(data.limite_credito || 0),
                    credito_usado: Number(data.credito_usado || 0),
                    status_credito: data.status_credito,
                    formas: data.formas_pagamento || []
                };

                console.log('Cliente carregado:', window.cliente);

                aplicarFormasPermitidas();
                atualizarResumo();

            })
            .catch(err => console.error('Erro cliente:', err));
    }

    function abrirModalFinalizar() {

        const clienteId = document.querySelector('input[name="cliente_id"]')?.value;

        carregarClienteFinanceiro(clienteId);

        const total = obterTotalVenda();

        totalModalEl.textContent = total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        inputsPagamento.forEach(i => i.value = '');

        // aplicarFormasPermitidas();
        // atualizarResumo();

        modal?.show();
        inputsPagamento[0]?.focus();
    }

    // =========================
    // INPUTS
    // =========================
    inputsPagamento.forEach(input => {

        input.addEventListener('input', function () {

            const forma = this.dataset.forma;
            let valor = parseFloat(this.value) || 0;

            const restante = calcularRestante(this);

            let limite = restante;

            if (forma === 'carteira') {
                const saldo = obterSaldoCarteira();
                limite = Math.min(restante, saldo);
            }

            if (valor > limite) {
                this.value = limite.toFixed(2);
            }

            atualizarResumo();
        });

        input.addEventListener('blur', function () {

            let valor = this.value.replace(',', '.');
            const numero = parseFloat(valor);

            if (isNaN(numero)) {
                this.value = '';
                return;
            }

            this.value = numero.toFixed(2);
            this.dispatchEvent(new Event('input'));
        });
    });

    
    // =========================
    // F6
    // =========================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F6') {
            e.preventDefault();
            abrirModalFinalizar();
        }
    });

    // =========================
    // FINALIZAR VENDA
    // =========================
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', async function (e) {

            e.preventDefault();

            const clienteId = document.querySelector('input[name="cliente_id"]')?.value;

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

            try {

                const res = await fetch('/vendas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ cliente_id: clienteId, pagamentos })
                });

                const data = await res.json();

                if (!data.success) throw new Error(data.message);

                modal?.hide();

            } catch (err) {
                console.error(err);
                alert(err.message);
            }
        });
    }

});