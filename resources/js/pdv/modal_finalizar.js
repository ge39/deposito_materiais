window.carrinho = window.carrinho || [];

document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const totalGeralEl   = document.getElementById('totalGeral');
    const totalModalEl   = document.getElementById('total-venda-modal');
    const modalEl        = document.getElementById('modalFinalizarVenda');
    const restanteEl     = document.getElementById('valor-restante');
    const trocoEl        = document.getElementById('valor-troco');
    const btnFinalizar   = document.getElementById('btnFinalizar');
    const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');

    const clienteId   = document.querySelector('input[name="cliente_id"]').value;
    const operadorId  = document.querySelector('input[name="operador_id"]').value;
    const terminalId  = document.querySelector('input[name="terminal_id"]').value;
    const preco_venda  = document.querySelector('input[name="preco_venda"]').value;
    
    const dataVenda   = document.querySelector('#dataVenda').value;
    const endereco = document.querySelector('#endereco').value;

    // console.log(clienteId, operadorId, terminalId, dataVenda,endereco);

   
    if (!totalGeralEl || !totalModalEl || !modalEl) {
        console.warn('Modal finalizar: elementos não encontrados');
        return;
    }

    const modal = new bootstrap.Modal(modalEl);

    function obterTotalVenda() {
        return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
    }

    function atualizarResumo() {
        const totalVenda = obterTotalVenda();

        let soma = 0;
        inputsPagamento.forEach(i => {
            soma += parseFloat(i.value) || 0;
        });

        let restante = totalVenda - soma;
        let troco = 0;

        if (restante < 0) {
            troco = Math.abs(restante);
            restante = 0;
        }

        restanteEl.textContent = restante.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        trocoEl.textContent = troco.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function abrirModalFinalizar() {
        const total = obterTotalVenda();

        totalModalEl.textContent = total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        inputsPagamento.forEach(i => i.value = '');
        atualizarResumo();

        modal.show();
        inputsPagamento[0].focus();
    }

   
// =========================
// ENTER + INPUT (REGRAS CONSOLIDADAS)
// =========================
inputsPagamento.forEach(input => {

    // ENTER: não muda campo | se valor = 0, preenche com restante
    input.addEventListener('keydown', function (e) {

        if (e.key !== 'Enter') return;

        e.preventDefault();

         // valor restante (já formatado na tela)
        const restanteTexto = restanteEl.textContent
            .replace(/\./g, '')
            .replace(',', '.')
            .replace(/[^\d.]/g, '');

        const restante = parseFloat(restanteTexto) || 0;

        // ✅ NOVA REGRA:
        // se não há mais nada a pagar, vai direto para o botão
        if (restante === 0) {
            btnFinalizar.focus();
            return;
        }
    });

    // INPUT: valida limites + recalcula
    input.addEventListener('input', function () {

        const forma = this.dataset.forma;

        // Limite SOMENTE para CC, CD, PIX e CARTEIRA
        if (['cartao_credito', 'cartao_debito', 'pix', 'carteira'].includes(forma)) {

            const totalVenda = obterTotalVenda();

            let somaOutros = 0;
            inputsPagamento.forEach(i => {
                if (i !== this) {
                    const v = parseFloat(i.value) || 0;
                    if (v > 0) somaOutros += v;
                }
            });

            let valorPermitido = totalVenda - somaOutros;
            if (valorPermitido < 0) valorPermitido = 0;

            const valorDigitado = parseFloat(this.value) || 0;

            if (valorDigitado > valorPermitido) {
                this.value = valorPermitido.toFixed(2);

                // nesses meios, troco sempre zero
                trocoEl.textContent = (0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            }
        }
        // =========================
    // FORMATA VALOR AO PERDER FOCO
    // =========================
    inputsPagamento.forEach(input => {

            input.addEventListener('blur', function () {

                let valor = this.value;

                if (!valor) return;

                // normaliza vírgula para ponto
                valor = valor.replace(',', '.');

                const numero = parseFloat(valor);

                if (isNaN(numero)) {
                    this.value = '';
                    return;
                }

                this.value = numero.toFixed(2);

                // força recálculo de restante / troco
                this.dispatchEvent(new Event('input', { bubbles: true }));
            });

        });
        atualizarResumo();
    });

    });

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

    // =========================
    // Tecla F6 abre modal
    // =========================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F6') {
            e.preventDefault();
            abrirModalFinalizar();
        }
    });

    // Certifique-se de declarar a variável fora de qualquer função
    let carrinhoAtual = [];

    // Função para adicionar itens (exemplo)
    function adicionarAoCarrinho(item) {
        carrinhoAtual.push(item);
    }
   
   
    //Fetch do botão Finalizar (VendaController)
     // BOTÃO FINALIZAR VENDA
     // ===============================
    // BOTÃO FINALIZAR VENDA - MODAL
    // ===============================
    // document.addEventListener('click', async function(e) {
    // if (e.target && e.target.id === 'btnFinalizar') {
    //     e.preventDefault();

    //     console.log('🔹 Botão Finalizar clicado');

    //     // Recupera todos os itens do carrinho do DOM
    //     const tabelaItens = document.getElementById('lista-itens');
    //     if (!tabelaItens) {
    //         console.warn('⚠️ Tabela do carrinho não encontrada.');
    //         return;
    //     }

    //     const trs = tabelaItens.querySelectorAll('tr:not(.d-none)');
    //     if (!trs.length) {
    //         alert('Carrinho vazio');
    //         return;
    //     }

    //     const itens = Array.from(trs).map(tr => {
    //         return {
    //             produto_id: tr.dataset.produto,
    //             lote_id: tr.dataset.lote || null,
    //             quantidade: parseFloat(tr.children[3].textContent) || 0,
    //             valor_unitario: parseFloat(tr.children[5].textContent.replace(',', '.')) || 0
    //         };
    //     });

    //     console.log('Itens a enviar:', itens);

    //     // Coleta pagamentos (assumindo inputs do modal com class pagamento-modal)
    //     const modalEl = document.getElementById('modalFinalizarVenda');
    //     const inputsPagamento = modalEl ? modalEl.querySelectorAll('.pagamento-modal') : [];
    //     const pagamentos = [];

    //     inputsPagamento.forEach(input => {
    //         const valor = parseFloat(input.value.replace(',', '.')) || 0;
    //         if (valor > 0) {
    //             pagamentos.push({ forma: input.dataset.forma, valor });
    //         }
    //     });

    //     if (!pagamentos.length) {
    //         alert('Informe ao menos uma forma de pagamento');
    //         return;
    //     }
       
    //     // Monta payload
    //     const payload = {
    //         cliente_id: clienteId,
    //         // caixa_id: caixaId,
    //         terminal_id: terminalId,
    //         funcionario_id: operadorId,
    //         dataVenda: dataVenda,
    //         endereco: endereco,

    //         itens: carrinhoAtual.map(i => ({
    //             produto_id: i.id,
    //             quantidade: i.quantidade,
    //             valor_unitario: i.preco,
    //             lote_id: i.lote_id || null
    //         })),
    //         pagamentos
    //     };

    //     console.log('Payload a ser enviado:', payload);

    //     try {
    //         const response = await fetch('/vendas', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //             },
    //             body: JSON.stringify(payload)
    //         });

    //         const data = await response.json();
    //         console.log('Resposta do servidor:', data);

    //         if (!response.ok || !data.success) {
    //             throw new Error(data.message || 'Erro ao finalizar venda');
    //         }

    //         alert(`Venda finalizada com sucesso (#${data.venda_id})`);

    //         // Limpa carrinho do DOM
    //         trs.forEach(tr => tr.remove());

    //         // Limpa inputs de pagamento
    //         inputsPagamento.forEach(i => i.value = '');

    //         // Atualiza total
    //         const totalVenda = document.getElementById('totalGeral');
    //         if (totalVenda) totalVenda.textContent = 'R$ 0,00';

            
    //     } catch (err) {
    //         console.error('Erro no fetch de finalizar venda:', err);
    //         alert(err.message);
    //     }
    // }
    // });

    // BOTÃO FINALIZAR VENDA - MODAL
// ===============================
document.addEventListener('click', async function(e) {
    if (e.target && e.target.id === 'btnFinalizar') {
        e.preventDefault();

        console.log('🔹 Botão Finalizar clicado');

        // Recupera todos os itens do carrinho do DOM
        const tabelaItens = document.getElementById('lista-itens');
        if (!tabelaItens) {
            console.warn('⚠️ Tabela do carrinho não encontrada.');
            return;
        }

        const trs = tabelaItens.querySelectorAll('tr:not(.d-none)');
        if (!trs.length) {
            alert('Carrinho vazio');
            return;
        }

        const itens = Array.from(trs).map(tr => {
            return {
                produto_id: tr.dataset.produto,
                lote_id: tr.dataset.lote || null,
                quantidade: parseFloat(tr.children[3].textContent) || 0,
                valor_unitario: parseFloat(tr.children[5].textContent.replace(',', '.')) || 0
            };
        });

        console.log('Itens a enviar:', itens);

        // Coleta pagamentos (assumindo inputs do modal com class pagamento-modal)
        const modalEl = document.getElementById('modalFinalizarVenda');
        const inputsPagamento = modalEl ? modalEl.querySelectorAll('.pagamento-modal') : [];
        const pagamentos = [];

        inputsPagamento.forEach(input => {
            const valor = parseFloat(input.value.replace(',', '.')) || 0;
            if (valor > 0) {
                pagamentos.push({ forma: input.dataset.forma, valor });
            }
        });

        if (!pagamentos.length) {
            alert('Informe ao menos uma forma de pagamento');
            return;
        }

        // Monta payload
        const payload = {
            cliente_id: clienteId,
            // caixa_id: caixaId, // se necessário, pode descomentar depois
            terminal_id: terminalId,
            funcionario_id: operadorId,
            dataVenda: dataVenda,
            endereco: endereco,

            itens: itens.map(i => ({
                produto_id: i.id,
                quantidade: i.quantidade,
                valor_unitario: i.preco,
                lote_id: i.lote_id || null
            })),
            pagamentos
        };

        console.log('Payload a ser enviado:', payload);

        try {
            const response = await fetch('/vendas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // 🔹 força Laravel a retornar JSON
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json(); // 🔹 parse seguro do JSON

            console.log('Resposta do servidor:', data);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erro ao finalizar venda');
            }

            alert(`Venda finalizada com sucesso (#${data.venda_id})`);

            // Limpa carrinho do DOM
            trs.forEach(tr => tr.remove());

            // Limpa inputs de pagamento
            inputsPagamento.forEach(i => i.value = '');

            // Atualiza total
            const totalVenda = document.getElementById('totalGeral');
            if (totalVenda) totalVenda.textContent = 'R$ 0,00';

        } catch (err) {
            console.error('Erro no fetch de finalizar venda:', err);
            alert(err.message);
        }
    }
});

        

});