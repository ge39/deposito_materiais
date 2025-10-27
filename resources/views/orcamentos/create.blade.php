@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Novo Orçamento</h2>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Mensagens de erro --}}
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
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
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
                        <label class="form-label">Data do Orçamento <span class="text-danger">*</span></label>
                        <input type="date" name="data_orcamento" class="form-control" 
                               value="{{ old('data_orcamento', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Validade <span class="text-danger">*</span></label>
                        <input type="date" name="validade" class="form-control" 
                               value="{{ old('validade', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    </div>
                </div>

                <hr>

                <!-- Itens do orçamento -->
                <h5>Itens do Orçamento <span class="text-danger">*</span></h5>
                <table class="table table-bordered align-middle" id="itensTable">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(old('produtos'))
                            @foreach(old('produtos') as $i => $oldItem)
                                <tr>
                                    <td>
                                        <select name="produtos[{{ $i }}][id]" class="form-select produtoSelect" required>
                                            <option value="">Selecione...</option>
                                            @foreach($produtos as $produto)
                                                <option value="{{ $produto->id }}" {{ $oldItem['id'] == $produto->id ? 'selected' : '' }}>
                                                    {{ $produto->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="produtos[{{ $i }}][quantidade]" class="form-control qtd" min="1" value="{{ $oldItem['quantidade'] }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="produtos[{{ $i }}][preco_unitario]" class="form-control preco" step="0.01" value="{{ $oldItem['preco_unitario'] }}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal" value="0,00" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger remover">X</button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <div class="text-end mb-3">
                    <button type="button" class="btn btn-sm btn-secondary" id="addProduto">+ Adicionar Produto</button>
                </div>

                <div class="text-end mb-3">
                    <h5>Total: R$ <span id="total">0,00</span></h5>
                </div>

                <!-- Observações -->
                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
                </div>

                <!-- Botões -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Salvar Orçamento</button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
/* Bloqueia seleção do cliente sem desabilitar o input */
.readonly-select {
    pointer-events: none;
    background-color: #e9ecef;
}
</style>

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
                    ${produtos.map(p => `<option value="${p.id}" data-preco="${p.preco}">${p.nome}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="produtos[${index}][quantidade]" class="form-control qtd" min="1" value="1" required>
            </td>
            <td>
                <input type="number" name="produtos[${index}][preco_unitario]" class="form-control preco" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control subtotal" value="0,00" readonly>
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
            tr.querySelector('.subtotal').value = subtotal.toFixed(2).replace('.', ',');
            total += subtotal;
        });
        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    addBtn.addEventListener('click', () => {
        // Validar cliente
        if (!clienteSelect.value) {
            alert('Selecione um cliente antes de adicionar produtos.');
            clienteSelect.focus();
            return;
        }

        // Validar último item
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

    // Evitar duplicidade ao selecionar produto
    tableBody.addEventListener('change', e => {
        if (e.target.classList.contains('produtoSelect')) {
            const selecionado = e.target.value;
            const produtosSelecionados = Array.from(tableBody.querySelectorAll('.produtoSelect'))
                .map(s => s.value)
                .filter(v => v !== '');
            const count = produtosSelecionados.filter(v => v === selecionado).length;
            if (count > 1) {
                alert('Este produto já foi adicionado.');
                e.target.value = '';
                return;
            }
            const preco = e.target.selectedOptions[0].dataset.preco || 0;
            e.target.closest('tr').querySelector('.preco').value = preco;
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
