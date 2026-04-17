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
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data</label>
                        <input type="date" name="data_orcamento" class="form-control"
                               value="{{ old('data_orcamento', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Validade</label>
                        <input type="date" name="validade" class="form-control"
                               value="{{ old('validade', date('Y-m-d', strtotime('+7 days'))) }}">
                    </div>
                </div>

                <hr>

                <h5>Itens do Orçamento</h5>

                <table class="table table-bordered" id="itensTable">
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

                    <tbody>
                        @if(old('produtos'))
                            @foreach(old('produtos') as $i => $oldItem)
                            <tr>
                                <td>
                                    <select name="produtos[{{ $i }}][id]" class="form-select produtoSelect">
                                        <option value="">Selecione...</option>
                                        @foreach($produtos as $produto)
                                            <option value="{{ $produto->id }}"
                                                data-preco="{{ $produto->preco_venda }}"
                                                data-unidade="{{ $produto->unidadeMedida->nome ?? '' }}"
                                                {{ $oldItem['id'] == $produto->id ? 'selected' : '' }}>
                                                {{ $produto->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <select name="produtos[{{ $i }}][lote_id]" class="form-select loteSelect">
                                        <option value="">Selecione...</option>
                                    </select>
                                </td>

                                <td>
                                    <input  type="number" name="produtos[{{ $i }}][quantidade]" class="form-control col-md-1" min="1" value="{{ $oldItem['quantidade'] ?? 1 }}">
                                </td>

                                <td>
                                    <label class="form-label unidadeLabel"></label>
                                    <input type="hidden" name="produtos[{{ $i }}][unidade]" class="unidade">
                                </td>

                                <td>
                                    <label class="form-label precoLabel">R$ 0,00</label>
                                    <input type="hidden" name="produtos[{{ $i }}][preco_unitario]" class="preco">
                                </td>

                                <td>
                                    <label class="subtotalLabel">0,00</label>
                                    <input type="hidden" class="subtotal">
                                </td>

                                <td>
                                    <button type="button" class="btn btn-danger remover">X</button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                                  <!-- Botões -->
                <div class="text-end">
                    <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
                    <button type="submit" class="btn btn-success">Salvar Orçamento</button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
                
                <div class="text-end mb-3" style="margin-top:10px">
                    <h5>Total: R$ <span id="total">0,00</span></h5>
                </div>

                <!-- Observações -->
                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', 'Sem observações') }}</textarea>

                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const tableBody = document.querySelector('#itensTable tbody');
        const totalSpan = document.getElementById('total');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');

        let index = tableBody.querySelectorAll('tr').length || 0;

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
                                ${p.nome}
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
                    <input type="number" name="produtos[${index}][quantidade]" class="form-control qtd" min="1" value="1" required>
                </td>

                <td>
                    <label class="form-label unidadeLabel"></label>
                    <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
                </td>

                <td>
                    <label class="form-label precoLabel">R$ 0,00</label>
                    <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
                </td>

                <td>
                    <label class="form-label subtotalLabel">0,00</label>
                    <input type="hidden" class="subtotal">
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remover">X</button>
                </td>
            `;

            tableBody.appendChild(tr);
            index++;
            atualizarSubtotal();
            clienteSelect.classList.add('readonly-select');
        }

        function atualizarSubtotal() {
            let total = 0;

            tableBody.querySelectorAll('tr').forEach(tr => {
                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const preco = parseFloat(tr.querySelector('.preco').value) || 0;

                const subtotal = qtd * preco;

                tr.querySelector('.subtotal').value = subtotal.toFixed(2);
                tr.querySelector('.subtotalLabel').textContent = subtotal.toFixed(2).replace('.', ',');

                tr.querySelector('.precoLabel').textContent =
                     preco.toFixed(2).replace('.', ',');

                tr.querySelector('.unidadeLabel').textContent =
                    tr.querySelector('.unidade').value;

                total += subtotal;
            });

            totalSpan.textContent = total.toFixed(2).replace('.', ',');
        }

        addBtn.addEventListener('click', () => {
            if (!clienteSelect.value) {
                alert('Selecione um cliente antes de adicionar produtos.');
                clienteSelect.focus();
                return;
            }

            const lastRow = tableBody.querySelector('tr:last-child');

            if (lastRow) {
                const produto = lastRow.querySelector('.produtoSelect').value;
                const qtd = lastRow.querySelector('.qtd').value;
                const preco = lastRow.querySelector('.preco').value;

                if (!produto || !qtd || !preco) {
                    alert('Preencha o item anterior antes de adicionar um novo.');
                    return;
                }
            }

            criarItem();
        });

        // 🔥 AO SELECIONAR PRODUTO
        tableBody.addEventListener('change', e => {

            if (e.target.classList.contains('produtoSelect')) {

                if (!clienteSelect.value) {
                    alert('Selecione o cliente primeiro!');
                    e.target.value = '';
                    return;
                }

                const produtoId = e.target.value;
                const produto = produtos.find(p => p.id == produtoId);

                const tr = e.target.closest('tr');

                const preco = produto?.preco_venda || 0;
                const unidade = produto?.unidade_medida?.nome || '';

                tr.querySelector('.preco').value = preco;
                tr.querySelector('.unidade').value = unidade;

                // 🔥 PREENCHER LOTES
                const loteSelect = tr.querySelector('.loteSelect');
                loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

                const hoje = new Date();

                if (produto && produto.lotes) {

                   const hoje = new Date();

                    const lotesValidos = produto.lotes.filter(l => {

                        // 🔥 calcula estoque REAL
                        const disponivel = (parseFloat(l.quantidade) || 0) - (parseFloat(l.quantidade_reservada) || 0);

                        if (l.status != 1) return false;
                        if (disponivel <= 0) return false;

                        // 🔥 produto NÃO controla validade
                        if (produto.controla_validade == 0) {
                            return true;
                        }

                        // 🔥 produto controla validade
                        if (!l.validade_lote) return true;

                        return new Date(l.validade_lote) >= hoje;
                    });

                    if (lotesValidos.length === 0) {
                        // verifica se produto controla validade
                        if (produto.controla_validade === 1) {
                            alert('Produto sem lote válido (validade vencida ou inexistente)!');
                        } else {
                            alert('Produto sem lote disponível em estoque!');
                        }

                        e.target.value = '';
                        return;
                    }

                    lotesValidos.forEach(l => {

                        const disponivel = (parseFloat(l.quantidade) || 0) - (parseFloat(l.quantidade_reservada) || 0);

                        loteSelect.innerHTML += `
                            <option value="${l.id}">
                                ${l.numero_lote} | Qtd: ${disponivel}
                            </option>
                        `;
                    });
                }

                atualizarSubtotal();
            }
        });

        tableBody.addEventListener('input', atualizarSubtotal);

        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {
                e.target.closest('tr').remove();
                atualizarSubtotal();

                if (tableBody.querySelectorAll('tr').length === 0) {
                    clienteSelect.classList.remove('readonly-select');
                }
            }
        });

        atualizarSubtotal();
    });
</script>

@endsection