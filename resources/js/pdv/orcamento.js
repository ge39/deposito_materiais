/**
 * Função chamada pelo botão do modal
 */
window.confirmarOrcamentoFront = async function () {

    const inputCodigo = document.getElementById('inputCodigoOrcamento');
    if (!inputCodigo) return alert('Input do código do orçamento não encontrado.');

    const codigo = inputCodigo.value.trim();
    if (!codigo) return alert('Informe o código do orçamento.');

    try {
        const response = await fetch(`/pdv/orcamento/${encodeURIComponent(codigo)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Orçamento não encontrado ou não aprovado');
        }

        const data = await response.json();

        if (!data.success || !data.orcamento) {
            throw new Error(data.message || 'Resposta inválida do servidor.');
        }

        // ======================
        // DEBUG: exibe itens antes de preencher o carrinho
        // ======================
        console.log('Orçamento completo:', data.orcamento);

        data.orcamento.itens.forEach((item, index) => {
            console.log(`Item ${index + 1}:`);
            console.log('Produto ID:', item.produto_id);
            console.log('Nome:', item.produto?.nome ?? '');
            console.log('Descrição:', item.produto?.descricao ?? '');
            console.log('Unidade:', item.produto?.unidade_medida?.sigla ?? '');
            console.log('Quantidade:', item.quantidade ?? 0);
            console.log('Valor Unitário:', item.valor_unitario ?? 0);
            console.log('Subtotal:', item.subtotal ?? 0);
        });

        // ======================
        // Preenche dados do cliente
        // ======================
        preencherCliente(data.orcamento.cliente);

        // ======================
        // Preenche carrinho
        // ======================
        preencherCarrinho(data.orcamento.itens);

        // ======================
        // Fecha modal
        // ======================
        const modalEl = document.getElementById('modalOrcamento');
        const modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
};

/* =========================
   CLIENTE
   ========================= */

function preencherCliente(cliente) {
    if (!cliente) return;

    setValue('cliente_id', cliente.id);
    setValue('cliente_nome', cliente.nome);
    setValue('cliente_cpf', cliente.cpf_cnpj);
    setValue('cliente_telefone', cliente.telefone);
    setValue('endereco', montarEndereco(cliente));
}

function montarEndereco(cliente) {
    const partes = [];
    if (cliente.endereco) partes.push(cliente.endereco);
    if (cliente.numero) partes.push('nº ' + cliente.numero);
    if (cliente.bairro) partes.push(cliente.bairro);
    if (cliente.cidade) partes.push(cliente.cidade);
    if (cliente.estado) partes.push(cliente.estado);
    if (cliente.cep) partes.push('CEP ' + cliente.cep);
    return partes.join(' - ');
}

function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value ?? '';
}

/* =========================
   CARRINHO
   ========================= */

function preencherCarrinho(itens) {
    if (!Array.isArray(itens)) return;

    const tbody = document.getElementById('lista-itens');
    if (!tbody) return console.warn('Tbody lista-itens não encontrado.');

    tbody.innerHTML = '';

    itens.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.dataset.produtoId = item.produto_id;
        tr.dataset.loteId = item.lote_id ?? '';

        const subtotal = parseFloat(item.subtotal ?? 0);

        tr.innerHTML = `
            <td class="text-center item-numero">${index + 1}</td>
            <td>
                <strong>${item.produto?.nome ?? ''}</strong>
            </td>
            <td class="text-center">${item.quantidade ?? 0}</td>
            <td class="text-center">${item.produto?.unidade_medida?.sigla ?? ''}</td>
            <td class="text-end">${formatar(item.preco_unitario ?? 0)}</td>
            <td class="text-end subtotal" data-valor="${subtotal}">${formatar(subtotal)}</td>
        `;

        tbody.appendChild(tr);
    });

    atualizarTotalVenda();
}


function atualizarTotalVenda() {
    let total = 0;

    document.querySelectorAll('#lista-itens .subtotal').forEach(td => {
        const valor = parseFloat(td.dataset.valor);
        total += isNaN(valor) ? 0 : valor;
    });

    const totalEl = document.getElementById('totalGeral'); // <== ajuste aqui
    if (totalEl) totalEl.textContent = formatar(total);
}

function formatar(valor) {
    return Number(valor).toFixed(2).replace('.', ',');
}

