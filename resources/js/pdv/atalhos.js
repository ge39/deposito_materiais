document.addEventListener('DOMContentLoaded', function () {

    console.log('Atalhos carregados');

    document.addEventListener('keydown', function (e) {

        if (e.repeat) return;

        if (e.key === 'F2') {
            e.preventDefault();
            const modal = document.getElementById('modalCliente');
            if (modal) {
                new bootstrap.Modal(modal).show();
            } else {
                console.warn('modalCliente não encontrado');
            }
        }

        if (e.key === 'F3') {
            e.preventDefault();
            const modal = document.getElementById('modalProduto');
            if (modal) {
                new bootstrap.Modal(modal).show();
            } else {
                console.warn('modalProduto não encontrado');
            }
        }

        if (e.key === 'F4') {
            e.preventDefault();
            const modal = document.getElementById('modalOrcamento');
            if (modal) {
                new bootstrap.Modal(modal).show();
            } else {
                console.warn('modalOrcamento não encontrado');
            }
        }

    });

});
