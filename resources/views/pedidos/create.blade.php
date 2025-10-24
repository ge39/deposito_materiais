@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Novo Pedido de Compra</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf

        {{-- DADOS DO PEDIDO --}}
        <div class="card p-3 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label>Fornecedor</label>
                    <select name="fornecedor_id" id="fornecedorSelect" class="form-control" required>
                        <option value="">Selecione</option>
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
        </div>

        {{-- ITENS DO PEDIDO --}}
        <div class="card p-3 mb-4" id="scrollTableContainer" style="max-height: 400px; overflow-y: auto;">
            <h5>Itens do Pedido</h5>
            <table class="table table-bordered" id="itensTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário (R$)</th>
                        <th>Subtotal (R$)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-secondary" id="addItem">Adicionar Item</button>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Salvar Pedido</button>
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
    const produtos = @json($produtos);
    const table = document.querySelector('#itensTable tbody');
    const fornecedorSelect = document.getElementById('fornecedorSelect');
    const scrollContainer = document.getElementById('scrollTableContainer');
    let fornecedorSelecionado = null;
    let itemIndex = 0;

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
            // fornecedorSelect.disabled = true;
            fornecedorSelect.setAttribute('data-locked', 'true');

        }

        const row = document.createElement('tr');
        let options = '<option value="">Selecione</option>';
        produtos.forEach(p => options += `<option value="${p.id}" data-preco-custo="${p.preco_custo}">${p.nome}</option>`);

        row.innerHTML = `
            <td class="text-center"></td>
            <td>
                <select name="itens[${itemIndex}][produto_id]" class="form-control produto-select" required>
                    ${options}
                </select>
            </td>
            <td><input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade" min="1" value="1" required></td>
            <td><input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control valor_unitario" readonly></td>
            <td><input type="text" name="itens[${itemIndex}][subtotal]" class="form-control subtotal" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeItem">Remover</button></td>
        `;

        table.appendChild(row);
        atualizarValores(row);
        atualizarIndices();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;

        // Evitar produtos duplicados
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

        itemIndex++;
    });
</script>
@endsection
