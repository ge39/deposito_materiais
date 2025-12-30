document.addEventListener('DOMContentLoaded', () => {

    const niveisPermitidos = [
        'gerente',
        'supervisor',
        'operador_caixa'
    ];

    const nivelUsuario = window.USUARIO_LOGADO.nivel;

    const bloqueio = document.getElementById('bloqueio-permissao');
    const form = document.getElementById('form-abertura-caixa');
    const btnAbrir = document.getElementById('btnAbrirCaixa');
    const inputFundo = document.getElementById('fundo_caixa');

    /* ===============================
       VALIDAÇÃO DE PERMISSÃO
       =============================== */
    if (!niveisPermitidos.includes(nivelUsuario)) {
        bloqueio.classList.remove('d-none');
        form.classList.add('d-none');
        return;
    }

    /* ===============================
       MÁSCARA SIMPLES MONETÁRIA
       =============================== */
    inputFundo.addEventListener('input', () => {
        let valor = inputFundo.value.replace(/\D/g, '');
        valor = (valor / 100).toFixed(2) + '';
        valor = valor.replace('.', ',');
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        inputFundo.value = valor;
    });

    /* ===============================
       AÇÃO DE ABERTURA (FRONT)
       =============================== */
    btnAbrir.addEventListener('click', () => {

        if (!inputFundo.value || inputFundo.value === '0,00') {
            alert('Informe um valor válido para o fundo de caixa.');
            return;
        }

        // Por enquanto apenas simulação
        console.log('Abertura de caixa');
        console.log('Operador:', window.USUARIO_LOGADO.id);
        console.log('Fundo:', inputFundo.value);

        alert('Dados validados. Pronto para enviar ao backend.');
    });

});
