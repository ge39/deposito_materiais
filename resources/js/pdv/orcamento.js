addItemCarrinho({
    produto_id,
    descricao,
    quantidade,
    preco,
    origem: 'orcamento'
});

document.addEventListener('DOMContentLoaded', function () {
    const inputCodigo = document.getElementById('codigo_orcamento');
    const btnBuscar = document.getElementById('btnBuscarOrcamento');

    if (!inputCodigo || !btnBuscar) return;

    btnBuscar.addEventListener('click', function () {
        const codigo = inputCodigo.value.trim();
        if (!codigo) return alert('Digite o código do orçamento');

        fetch(`/pdv/orcamento/${encodeURIComponent(codigo)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Orçamento não encontrado');
            return res.json();
        })
        .then(data => {
            const modalBody = document.getElementById('modalOrcamento').querySelector('.modal-body');
            modalBody.innerHTML = `
                <p><strong>Código:</strong> ${data.codigo_orcamento}</p>
                <p><strong>Cliente:</strong> ${data.cliente_nome}</p>
                <p><strong>Total:</strong> R$ ${data.total}</p>
            `;
            const modal = new bootstrap.Modal(document.getElementById('modalOrcamento'));
            modal.show();
        })
        .catch(err => alert(err.message));
    });
});
