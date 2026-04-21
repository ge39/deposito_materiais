@extends('layouts.app')

@section('content')
<div class="container">

<h2 class="mb-4">Novo Orçamento</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Erro!</strong> Verifique os campos obrigatórios.
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('orcamentos.store') }}" method="POST" id="formOrcamento">
    @csrf

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <!-- Cliente e Datas -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Cliente *</label>
                    <select name="cliente_id" id="clienteSelect" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Data</label>
                    <input type="date" name="data_orcamento" class="form-control"
                           value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Validade</label>
                    <input type="date" name="validade" class="form-control"
                           value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                </div>
            </div>

            <hr>

            <h5>Itens do Orçamento</h5>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Lote</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody id="itensTable"></tbody>
            </table>

            <div class="text-end">
                <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
                <button type="submit" class="btn btn-success">Salvar Orçamento</button>
                <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
            </div>

            <div class="text-end mt-2">
                <h5>Total: R$ <span id="total">0,00</span></h5>
            </div>

            <div class="mb-3 mt-3 bg-secondary  p-3 rounded">
                <label class="form-label text-warning">Observações:</label>
               <label class="form-label text-light"> insira aqui as informações que vão aparecer impressos no documento de entrega.</label>
               <label class="form-label text-warning"> Ex: melhor periodo para entrega: manha ou tarde, nome da pessoa que vai receber ?</label>
                <textarea name="observacoes" class="form-control" rows="3">Sem observações</textarea>
            </div>

        </div>
    </div>
</form>

</div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const tableBody = document.getElementById('itensTable');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');
        const totalSpan = document.getElementById('total');

        let index = 0;

        // ================================
        // CRIAR ITEM
        // ================================
        function criarItem() {

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                        <option value="">Selecione...</option>
                        ${produtos.map(p => `
                            <option value="${p.id}"
                                data-preco="${p.preco_venda}"
                                data-unidade="${p.unidade_medida?.nome || ''}">
                                ${p.id} - ${p.nome}
                            </option>
                        `).join('')}
                    </select>
                </td>

                <!-- 🔥 LOTE (IGUAL EDIT) -->
                <td>
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </td>

                <td>
                    <input type="number"
                        name="produtos[${index}][quantidade]"
                        class="form-control qtd"
                        value="1" min="1" required>
                </td>

                <td>
                    <span class="unidadeLabel"></span>
                    <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
                </td>

                <td>
                    <span class="precoLabel">0,00</span>
                    <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
                </td>

                <td>
                    <span class="subtotalLabel">0,00</span>
                </td>

                <td>
                    <button type="button" class="btn btn-sm btn-danger remover">X</button>
                </td>
            `;

            tableBody.appendChild(tr);
            index++;
        }

        // ================================
        // ATUALIZAR TOTAL
        // ================================
        function atualizarTotal() {
            let total = 0;

            tableBody.querySelectorAll('tr').forEach(tr => {

                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const preco = parseFloat(tr.querySelector('.preco').value) || 0;

                const subtotal = qtd * preco;

                tr.querySelector('.subtotalLabel').textContent =
                    subtotal.toFixed(2).replace('.', ',');

                total += subtotal;
            });

            totalSpan.textContent = total.toFixed(2).replace('.', ',');
        }

        // ================================
        // PRODUTO ALTERADO + LOTES
        // ================================
        tableBody.addEventListener('change', e => {

            if (!e.target.classList.contains('produtoSelect')) return;

            if (!clienteSelect.value) {
                alert('Selecione o cliente primeiro!');
                e.target.value = '';
                return;
            }

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const tr = e.target.closest('tr');

            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

            tr.querySelector('.unidade').value = unidade;
            tr.querySelector('.unidadeLabel').textContent = unidade;

            // ============================
            // 🔥 LOTES (IGUAL EDIT)
            // ============================
            const loteSelect = tr.querySelector('.loteSelect');
            loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

            if (!produto || !produto.lotes) return;

            // const lotesValidos = produto.lotes.filter(l => {
            //     return l.status == 1;
            // });

            const lotesValidos = produto.lotes.filter(l => 
            {

            const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                return l.status == 1 && disponivel > 0;
            });

            if (lotesValidos.length === 0) {
                loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                return;
            }

            lotesValidos.forEach(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                loteSelect.innerHTML += `
                    <option value="${l.id}">
                        ${l.numero_lote} | Qtd: ${disponivel}
                    </option>
                `;
            });

            atualizarTotal();
        });

        // ================================
        // QUANTIDADE
        // ================================
        tableBody.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        // ================================
        // REMOVER
        // ================================
        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {
                e.target.closest('tr').remove();
                atualizarTotal();
            }
        });

        // ================================
        // ADICIONAR PRODUTO
        // ================================
        addBtn.addEventListener('click', () => {

            if (!clienteSelect.value) {
                alert('Selecione um cliente primeiro!');
                return;
            }

            const lastRow = tableBody.querySelector('tr:last-child');

            if (lastRow) {

                const produto = lastRow.querySelector('.produtoSelect')?.value;
                const lote = lastRow.querySelector('.loteSelect')?.value;
                const qtd = lastRow.querySelector('.qtd')?.value;
                const preco = lastRow.querySelector('.preco')?.value;

                // 🔥 VALIDAÇÃO PRINCIPAL (igual edit)
                if (!produto || !qtd || !preco) {
                    alert('Complete o item antes de adicionar outro');
                    return;
                }

                // 🔥 NOVO: valida lote também
                if (!lote) {
                    alert('Selecione o lote antes de adicionar outro');
                    lastRow.querySelector('.loteSelect')?.focus();
                    return;
                }

                if (qtd <= 0) {
                    alert('Informe uma quantidade válida');
                    lastRow.querySelector('.qtd')?.focus();
                    return;
                }
            }

            criarItem();
        });

    });
</script> -->

<script>
document.addEventListener('DOMContentLoaded', () => {

    const produtos = @json($produtos);
    const tableBody = document.getElementById('itensTable');
    const addBtn = document.getElementById('addProduto');
    const clienteSelect = document.getElementById('clienteSelect');
    const totalSpan = document.getElementById('total');

    let index = 0;

    // ================================
    // 🔥 PRODUTOS SELECIONADOS
    // ================================
    function getProdutosSelecionados() {
        const selecionados = [];

        tableBody.querySelectorAll('.produtoSelect').forEach(select => {
            if (select.value) {
                selecionados.push(select.value);
            }
        });

        return selecionados;
    }

    // ================================
    // 🔥 ATUALIZA OPTIONS (OCULTA USADOS)
    // ================================
    function atualizarOpcoesProdutos() {
        const selecionados = getProdutosSelecionados();

        tableBody.querySelectorAll('.produtoSelect').forEach(select => {

            const valorAtual = select.value;

            select.querySelectorAll('option').forEach(option => {

                if (!option.value) return;

                // mantém o selecionado atual visível
                if (option.value === valorAtual) {
                    option.hidden = false;
                    return;
                }

                // oculta se já foi usado em outro select
                option.hidden = selecionados.includes(option.value);
            });
        });
    }

    // ================================
    // CRIAR ITEM
    // ================================
    function criarItem() {

        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                    <option value="">Selecione...</option>
                    ${produtos.map(p => `
                        <option value="${p.id}"
                            data-preco="${p.preco_venda}"
                            data-unidade="${p.unidade_medida?.nome || ''}">
                            ${p.id} - ${p.nome}
                        </option>
                    `).join('')}
                </select>
            </td>

            <td>
                <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                    <option value="">Selecione o lote</option>
                </select>
            </td>

            <td>
                <input type="number"
                    name="produtos[${index}][quantidade]"
                    class="form-control qtd"
                    value="1" min="1" required>
            </td>

            <td>
                <span class="unidadeLabel"></span>
                <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
            </td>

            <td>
                <span class="precoLabel">0,00</span>
                <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
            </td>

            <td>
                <span class="subtotalLabel">0,00</span>
            </td>

            <td>
                <button type="button" class="btn btn-sm btn-danger remover">X</button>
            </td>
        `;

        tableBody.appendChild(tr);
        index++;

        // 🔥 atualiza opções ao criar nova linha
        atualizarOpcoesProdutos();
    }

    // ================================
    // ATUALIZAR TOTAL
    // ================================
    function atualizarTotal() {
        let total = 0;

        tableBody.querySelectorAll('tr').forEach(tr => {

            const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
            const preco = parseFloat(tr.querySelector('.preco').value) || 0;

            const subtotal = qtd * preco;

            tr.querySelector('.subtotalLabel').textContent =
                subtotal.toFixed(2).replace('.', ',');

            total += subtotal;
        });

        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    // ================================
    // PRODUTO ALTERADO + LOTES
    // ================================
    tableBody.addEventListener('change', e => {

        if (!e.target.classList.contains('produtoSelect')) return;

        if (!clienteSelect.value) {
            alert('Selecione o cliente primeiro!');
            e.target.value = '';
            return;
        }

        const produtoId = e.target.value;
        const produto = produtos.find(p => p.id == produtoId);
        const tr = e.target.closest('tr');

        const preco = parseFloat(produto?.preco_venda || 0);
        const unidade = produto?.unidade_medida?.nome || '';

        tr.querySelector('.preco').value = preco;
        tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

        tr.querySelector('.unidade').value = unidade;
        tr.querySelector('.unidadeLabel').textContent = unidade;

        // ============================
        // LOTES
        // ============================
        const loteSelect = tr.querySelector('.loteSelect');
        loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

        if (!produto || !produto.lotes) return;

        const lotesValidos = produto.lotes.filter(l => {

            const disponivel =
                (parseFloat(l.quantidade) || 0) -
                (parseFloat(l.quantidade_reservada) || 0);

            return l.status == 1 && disponivel > 0;
        });

        if (lotesValidos.length === 0) {
            loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
            return;
        }

        lotesValidos.forEach(l => {

            const disponivel =
                (parseFloat(l.quantidade) || 0) -
                (parseFloat(l.quantidade_reservada) || 0);

            loteSelect.innerHTML += `
                <option value="${l.id}">
                    ${l.numero_lote} | Qtd: ${disponivel}
                </option>
            `;
        });

        // 🔥 ATUALIZA BLOQUEIO DE PRODUTOS
        atualizarOpcoesProdutos();

        atualizarTotal();
    });

    // ================================
    // QUANTIDADE
    // ================================
    tableBody.addEventListener('input', e => {
        if (e.target.classList.contains('qtd')) {
            atualizarTotal();
        }
    });

    // ================================
    // REMOVER
    // ================================
    tableBody.addEventListener('click', e => {
        if (e.target.classList.contains('remover')) {

            e.target.closest('tr').remove();

            // 🔥 libera produto novamente
            atualizarOpcoesProdutos();

            atualizarTotal();
        }
    });

    // ================================
    // ADICIONAR PRODUTO
    // ================================
    addBtn.addEventListener('click', () => {

        if (!clienteSelect.value) {
            alert('Selecione um cliente primeiro!');
            return;
        }

        const lastRow = tableBody.querySelector('tr:last-child');

        if (lastRow) {

            const produto = lastRow.querySelector('.produtoSelect')?.value;
            const lote = lastRow.querySelector('.loteSelect')?.value;
            const qtd = lastRow.querySelector('.qtd')?.value;
            const preco = lastRow.querySelector('.preco')?.value;

            if (!produto || !qtd || !preco) {
                alert('Complete o item antes de adicionar outro');
                return;
            }

            if (!lote) {
                alert('Selecione o lote antes de adicionar outro');
                lastRow.querySelector('.loteSelect')?.focus();
                return;
            }

            if (qtd <= 0) {
                alert('Informe uma quantidade válida');
                lastRow.querySelector('.qtd')?.focus();
                return;
            }
        }

        criarItem();
    });

});
</script>

@endsection