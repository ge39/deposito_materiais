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
    // ENTER: preencher próximo
    // =========================
    inputsPagamento.forEach((input, index) => {

        input.addEventListener('input', atualizarResumo);

        input.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;

            e.preventDefault();

            const totalVenda = obterTotalVenda();
            let soma = 0;

            inputsPagamento.forEach(i => {
                soma += parseFloat(i.value) || 0;
            });

            const restante = parseFloat((totalVenda - soma).toFixed(2));

            if (restante > 0) {
                const proximo = Array.from(inputsPagamento)
                    .slice(index + 1)
                    .find(i => !i.value || parseFloat(i.value) === 0);

                if (proximo) {
                    proximo.value = restante.toFixed(2);
                    proximo.focus();
                    atualizarResumo();
                    return;
                }
            }

            btnFinalizar.focus();
        });
    });

    // ATALHOS + FOCO + VALOR TOTAL
    // =========================
    document.addEventListener('keydown', function (e) {

        const modalEl = document.getElementById('modalFinalizarVenda');

        // só funciona com o modal aberto
        if (!modalEl || !modalEl.classList.contains('show')) return;

        const tecla = e.key.toLowerCase();

        // buffer para teclas compostas (dd, cc, cd, pi, ca)
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

        // 👉 usa SUA função existente
        const total = obterTotalVenda();

        input.focus();
        input.value = total.toFixed(2);
        input.select();

        // força atualização de restante/troco
        input.dispatchEvent(new Event('input', { bubbles: true }));

        window.__pdvBufferForma = '';
    });


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
