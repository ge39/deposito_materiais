@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Novo Pedido de Compra</h2>

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf
        <div class="card p-3 mb-3">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Fornecedor</label>
                    <select name="fornecedor_id" id="fornecedorSelect" class="form-control" required>
                        <option value="">Selecione um fornecedor</option>
                        @foreach($fornecedores as $fornecedor)
                            <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Data do Pedido</label>
                    <input type="date" name="data_pedido" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <h5>Itens do Pedido</h5>
            <div style="max-height: 300px; overflow-y: auto;" id="scrollTableContainer">
                <table class="table table-bordered" id="itensTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Custo</th>
                            <th>Subtotal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <button type="button" class="btn btn-secondary mt-2" id="addItem">Adicionar Item</button>
        </div>

        <button type="submit" class="btn btn-success mt-2">Salvar Pedido</button>
    </form>
</div>

<script>
    const produtos = @json($produtos);
    const table = document.querySelector('#itensTable tbody');
    const fornecedorSelect = document.getElementById('fornecedorSelect');
    const scrollContainer = document.getElementById('scrollTableContainer');
    let fornecedorSelecionado = null;

    function atualizarIndices() {
        table.querySelectorAll('tr').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    function atualizarValores(row) {
        const selectProduto = row.querySelector('.produto-select');
        const quantidade = row.querySelector('.quantidade');
        const valor = row.querySelector('.valor_unitario');
        const subtotal = row.querySelector('.subtotal');

        selectProduto.addEventListener('change', () => {
            const valorUnit = parseFloat(selectProduto.selectedOptions[0].dataset.precoCusto || 0);
            valor.value = valorUnit.toFixed(2);
            subtotal.value = (valorUnit * parseFloat(quantidade.value || 0)).toFixed(2);
        });

        quantidade.addEventListener('input', () => {
            subtotal.value = (parseFloat(valor.value || 0) * parseFloat(quantidade.value || 0)).toFixed(2);
        });

    row.querySelector('.removeItem').addEventListener('click', () => {
        row.remove();
        atualizarIndices();
        if (!table.querySelectorAll('tr').length) {
            fornecedorSelecionado = null;
            fornecedorSelect.disabled = false;
        }
    });
}

document.getElementById('addItem').addEventListener('click', () => {
    const fornecedorAtual = fornecedorSelect.value;

    if (!fornecedorAtual) {
        alert('Selecione um fornecedor antes de adicionar itens.');
        return;
    }

    if (fornecedorSelecionado && fornecedorSelecionado !== fornecedorAtual) {
        alert('Não é permitido trocar o fornecedor após adicionar itens.');
        return;
    }

    const rows = table.querySelectorAll('tr');
    if (rows.length > 0) {
        const lastRowSelect = rows[rows.length - 1].querySelector('.produto-select');
        if (!lastRowSelect.value) {
            alert('Selecione o produto do item anterior antes de adicionar outro.');
            return;
        }
    }

    if (!fornecedorSelecionado) {
        fornecedorSelecionado = fornecedorAtual;
        fornecedorSelect.disabled = true;
    }

    const row = document.createElement('tr');
    let options = '<option value="">Selecione</option>';
    produtos.forEach(p => options += `<option value="${p.id}" data-preco-custo="${p.preco_custo}">${p.nome}</option>`);

    row.innerHTML = `
        <td class="text-center"></td>
        <td>
            <select name="itens[][produto_id]" class="form-control produto-select" required>
                ${options}
            </select>
        </td>
        <td><input type="number" name="itens[][quantidade]" class="form-control quantidade" min="1" value="1" required></td>
        <td><input type="text" name="itens[][valor_unitario]" class="form-control valor_unitario" readonly></td>
        <td><input type="text" class="form-control subtotal" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm removeItem">Remover</button></td>
    `;

    table.appendChild(row);
    atualizarValores(row);
    atualizarIndices();
    scrollContainer.scrollTop = scrollContainer.scrollHeight;

    const produtoSelect = row.querySelector('.produto-select');
    produtoSelect.addEventListener('change', () => {
        const selectedValue = produtoSelect.value;
        const duplicado = Array.from(table.querySelectorAll('.produto-select'))
            .filter(s => s !== produtoSelect)
            .some(s => s.value === selectedValue);
        if (duplicado) {
            alert('Este produto já foi adicionado para este fornecedor.');
            produtoSelect.value = '';
        }
    });
});
</script>
@endsection
