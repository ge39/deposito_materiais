document.addEventListener('DOMContentLoaded', function () {

    const totalGeralEl   = document.getElementById('totalGeral');
    const totalModalEl   = document.getElementById('total-venda-modal');
    const modalEl        = document.getElementById('modalFinalizarVenda');
    const restanteEl     = document.getElementById('valor-restante');
    const trocoEl        = document.getElementById('valor-troco');
    const btnFinalizar   = document.getElementById('btnFinalizar');
    const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');

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

});
