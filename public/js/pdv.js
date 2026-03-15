<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================================
       CAIXAS ESQUECIDOS (>12h abertos)
    =================================*/
    const listaDiv = document.getElementById('listaCaixasEsquecidos');
    const modalBloquear = document.getElementById('modalBloquearCaixa');

    if (listaDiv) {
        fetch('/pdv/caixas-esquecidos')
        .then(response => {
            if (!response.ok) throw new Error('Erro HTTP');
            return response.json();
        })
        .then(data => {

            if (!data || data.length === 0) {
                listaDiv.style.display = 'none';
                return;
            }

            listaDiv.innerHTML = '';
            listaDiv.style.display = 'block';

            data.forEach(caixa => {

                const item = document.createElement('li');

                item.textContent =
                    `Terminal: ${caixa.terminal_id} | ` +
                    `Caixa ID: ${caixa.id} | ` +
                    `Aberto em: ${caixa.data_abertura_br} | ` +
                    `Média horas pdv aberto: ${caixa.pdv_horas_aberto}h | ` +
                    `Operador: ${caixa.usuario.name}`;

                listaDiv.appendChild(item);

            });

            if (modalBloquear && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalBloquear, {
                    backdrop: 'static',
                    keyboard: false
                });

                modal.show();
            }

        })
        .catch(() => {
            listaDiv.style.display = 'none';
        });
    }


    /* ================================
       ALERTA DE LOTES VENCIDOS
    =================================*/
    const data = @json($data ?? []);
    const alerta = document.getElementById('alerta-lote');

    if (alerta) {

        alerta.classList.add('d-none');
        alerta.textContent = '';
        alerta.className = 'fw-bold';

        if (data?.lote_alerta?.tipo === 'vencido') {

            alerta.textContent = data.lote_alerta.mensagem;
            alerta.classList.add('text-danger');
            alerta.classList.remove('d-none');

        }

        if (data?.lote_alerta?.tipo === 'a_vencer') {

            alerta.textContent = data.lote_alerta.mensagem;
            alerta.classList.add('text-warning');
            alerta.classList.remove('d-none');

        }

    }


    /* ================================
       FINALIZAR VENDA (F6)
    =================================*/
    const btnFinalizar = document.getElementById("btnF6");
    const totalInput = document.getElementById("inputTotalGeral");
    const modalTotal = document.getElementById("total-venda-modal");

    if (btnFinalizar) {

        btnFinalizar.addEventListener("click", function () {

            if (totalInput && modalTotal) {

                modalTotal.value = totalInput.value;

            } else {

                console.warn("Elemento de total não encontrado!");

            }

            const modalEl = document.getElementById('modalFinalizar');

            if (modalEl && typeof bootstrap !== 'undefined') {

                const modal = new bootstrap.Modal(modalEl);
                modal.show();

            }

        });

    }

});
</script>


<!-- CAIXA GLOBAL -->
<script>
    const CAIXA_ID = @json($caixa->id ?? null);
    const CAIXA_POSSUI_VENDAS = @json($caixa->possui_vendas ?? false);
</script>
