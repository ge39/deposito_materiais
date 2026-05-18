document.addEventListener('DOMContentLoaded', function () {
    /* =========================
       INPUT CÓDIGO DO ORÇAMENTO
       ========================= */
    const modalEl = document.getElementById('modalOrcamento');
    const inputCodigo = document.getElementById('inputCodigoOrcamento');

    if (modalEl && inputCodigo) {
        // Foco automático quando modal abrir
        modalEl.addEventListener('shown.bs.modal', function () {
            inputCodigo.focus();
            inputCodigo.select();
        });

        // Enter para buscar orçamento
        inputCodigo.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const codigo = inputCodigo.value.trim();
                if (codigo) {
                    window.confirmarOrcamentoFront(); // função já existente
                }
            }
        });
    }
/* =========================
       FUNÇÕES AUXILIARES
       ========================= */

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

            if (!response.ok) throw new Error('Orçamento não encontrado ou não aprovado');

            const data = await response.json();
            if (!data.success || !data.orcamento) throw new Error(data.message || 'Resposta inválida do servidor.');

            // Preenche cliente e carrinho
            preencherCliente(data.orcamento.cliente);
            preencherCarrinho(data.orcamento.itens);

            // Fecha modal
            const modal   = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    };

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
                <td class="text-left"><strong>${item.produto?.nome ?? ''}</strong></td>
                <td class="text-center"><strong>${item.quantidade ?? 0}</strong></td>
                <td class="text-center"><strong>${item.produto?.unidade_medida?.sigla ?? ''}</strong></td>
                <td class="text-end"><strong>${formatar(item.preco_unitario ?? 0)}</strong></td>
                <td class="text-end subtotal" data-valor="${subtotal}"><strong>${formatar(subtotal)}</strong></td>
            `;
            tbody.appendChild(tr);
        });

        bloquearAlteracoesCarrinho();
        atualizarTotalVenda();
    }

    function atualizarTotalVenda() {
        let total = 0;
        document.querySelectorAll('#lista-itens .subtotal').forEach(td => {
            const valor = parseFloat(td.dataset.valor);
            total += isNaN(valor) ? 0 : valor;
        });
        const totalEl = document.getElementById('totalGeral');
        if (totalEl) totalEl.textContent = formatar(total);
    }

    function formatar(valor) {
        return Number(valor).toFixed(2).replace('.', ',');
    }

    function bloquearAlteracoesCarrinho() {
        const botoesAlteracao = document.querySelectorAll('#acoes-carrinho button, .btn-diminuir, .btn-remover, .btn-ocultar');
        botoesAlteracao.forEach(botao => {
            botao.disabled = true;
            botao.classList.add('disabled');
        });

        const inputsQuantidade = document.querySelectorAll('.quantidade-item');
        inputsQuantidade.forEach(input => input.readOnly = true);
    }

    function limparCamposPDV() {
        // Limpa campos do cliente
        setValue('cliente_id', '');
        setValue('cliente_nome', '');
        setValue('cliente_cpf', '');
        setValue('cliente_telefone', '');
        setValue('endereco', '');

        // Limpa carrinho
        const tbody = document.getElementById('lista-itens');
        if (tbody) tbody.innerHTML = '';

        // Zera total
        const totalEl = document.getElementById('totalGeral');
        if (totalEl) totalEl.textContent = '0,00';
    }

});

// Função para atualizar o estado do carrinho
function atualizarEstadoCarrinho() {
    const tbody = document.getElementById('lista-itens');
    return tbody ? tbody.querySelectorAll('tr').length > 0 : false;
}

// Função para carregar o próximo ID de venda
async function carregarProximoIdVenda() {
    try {
        const response = await fetch('/pdv/ultimo-id-venda', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error('Erro ao buscar último ID');

        const data = await response.json();
        const proximoId = (data.ultimo_id ?? 0) + 1;

        const inputVenda = document.querySelector('input[name="id_venda"]');
        if (inputVenda) inputVenda.value = proximoId;

        console.log('Próximo ID de venda carregado:', proximoId);

    } catch (error) {
        console.error('Erro ao carregar próximo ID de venda:', error);
    }
}









