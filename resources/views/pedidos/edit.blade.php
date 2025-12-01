@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Pedido de Compra #{{ $pedido->id }}</h2>

    {{-- Mensagens de feedback --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário principal --}}
    <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST" id="pedidoForm">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Codigo: <span class="text-danger">
                     <input type="text" class="form-control" name="pedido_id" value="{{ $pedido->id }}" readonly>
                    </span>
                </label>

                <label>Fornecedor <span class="text-danger">
                     <input type="text" class="form-control" value="{{ $pedido->fornecedor->nome ?? $pedido->fornecedor->nome_fantasia ?? $pedido->fornecedor->razao_social }}" readonly>
                    </span>
                    <input type="hidden" name="fornecedor_id" value="{{ $pedido->fornecedor_id }}">
                </label>
                <select name="fornecedor_id" id="fornecedorSelect" class="form-control" required {{ $pedido->itens->count() > 0 ? 'disabled' : '' }}>
                    <option value="">-- Selecione --</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ $pedido->fornecedor_id == $fornecedor->id ? 'selected' : '' }}>
                            {{ $fornecedor->nome_fantasia ?? $fornecedor->razao_social }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="data_pedido">Nova Data do Pedido <span class="text-danger">*</span></label>
                <input type="datetime-local" id="data_pedido" name="data_pedido" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
            </div>
        </div>

        <hr>

        <h5 class="mt-4 mb-3">Itens do Pedido</h5>
            
        <div class="table-responsive" id="scrollTableContainer" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered align-middle text-center" id="itensTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário (R$)</th>
                        <th>Subtotal (R$)</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->itens as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <select name="itens[{{ $index }}][produto_id]" class="form-control produto-select" required>
                                    <option value="">Selecione...</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}"
                                            data-preco-custo="{{ $produto->valor_unitario }}"
                                            {{ $produto->id == $item->produto_id ? 'selected' : '' }}>
                                            {{ $produto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control unidade_medida" value="{{ $item->produto->unidadeMedida->nome ?? '' }}" readonly>
                            </td>
                            <td>
                                <input type="number" name="itens[{{ $index }}][quantidade]" class="form-control quantidade" min="1" value="{{ $item->quantidade }}" required>
                            </td>
                            <td>
                                <input type="text" name="itens[{{ $index }}][valor_unitario]" class="form-control valor_unitario" value="{{ number_format($item->valor_unitario ?? $item->produto->valor_unitario, 2, '.', '') }}">
                            </td>
                            <td>
                                <input type="text" name="itens[{{ $index }}][subtotal]" class="form-control subtotal" value="{{ number_format($item->subtotal ?? ($item->quantidade * ($item->valor_unitario ?? $item->produto->valor_unitario)), 2, '.', '') }}">
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pendente' => 'badge bg-warning text-dark',
                                        'aprovado' => 'badge bg-primary',
                                        'recebido' => 'badge bg-success',
                                        'cancelado' => 'badge bg-danger'
                                    ];
                                @endphp
                                <span class="{{ $statusClasses[$pedido->status] ?? 'badge bg-secondary' }}">
                                    {{ ucfirst($pedido->status) }}
                                </span>
                                <input type="hidden" name="status" value="{{ $pedido->status }}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm removeItem">Remover</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-end mb-3">
            <div class="mt-4">
                <button type="button" class="btn btn-primary" id="addItem">Adicionar Item</button>
                <button type="submit" class="btn btn-success">Atualizar Pedido</button>
                <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
            <!-- <h5 class="mb-0">Total: R$ <span id="totalGeral">{{ number_format($pedido->total, 2, '.', '') }}</span></h5> -->
            
            <h5 class="mb-0">
                Total: R$ <span id="totalGeral">{{ number_format($pedido->itens->sum(fn($i) => $i->quantidade * $i->valor_unitario), 2, ',', '.') }}</span>
            </h5>

        </div>
    </form>
</div>

<!-- <script>
    const produtos = @json($produtos->load('unidadeMedida'));
    const table = document.querySelector('#itensTable tbody');
    const scrollContainer = document.getElementById('scrollTableContainer');
    let itemIndex = {{ $pedido->itens->count() }};
    let fornecedorSelecionado = {{ $pedido->fornecedor_id ?? 'null' }};


    document.getElementById('addItem').addEventListener('click', () => {

        const fornecedorAtual = document.getElementById('fornecedorSelect').value;
        if (!fornecedorAtual) {
            alert('Selecione um fornecedor antes de adicionar itens.');
            return;
        }
        if (fornecedorSelecionado && fornecedorSelecionado != fornecedorAtual) {
            alert('Não é permitido trocar o fornecedor após adicionar itens.');
            return;
        }

        const rows = table.querySelectorAll('tr');
        if (rows.length && !rows[rows.length - 1].querySelector('.produto-select').value) {
            alert('Selecione o produto do item anterior antes de adicionar outro.');
            return;
        }

        if (!fornecedorSelecionado) fornecedorSelecionado = fornecedorAtual;

        const row = document.createElement('tr');
        itemIndex++;

        let options = '<option value="">Selecione...</option>';
        produtos.forEach(p => options += `<option value="${p.id}" data-preco-custo="${p.valor_unitario}">${p.nome}</option>`);

        row.innerHTML = `
            <td class="text-center"></td>
            <td>
                <select name="itens[${itemIndex}][produto_id]" class="form-control produto-select" required>
                    ${options}
                </select>
            </td>
            <td><input type="text" name="itens[${itemIndex}][unidade_medida]" class="form-control unidade_medida" readonly></td>
            <td><input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade" min="1" value="1" required></td>
            <td><input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control valor_unitario" readonly></td>
            <td><input type="text" name="itens[${itemIndex}][subtotal]" class="form-control subtotal" readonly></td>
            <td><input type="text" name="itens[${itemIndex}][status]" class="form-control status" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeItem">Remover</button></td>
        `;

        table.appendChild(row);
        adicionarEventos(row);
        atualizarIndices();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    });

    function adicionarEventos(row) {
        const selectProduto = row.querySelector('.produto-select');
        const quantidade = row.querySelector('.quantidade');
        const valor = row.querySelector('.valor_unitario');
        const subtotal = row.querySelector('.subtotal');
        const unidadeInput = row.querySelector('.unidade_medida');

        selectProduto.addEventListener('change', () => {
            // impede produto duplicado
            const duplicado = Array.from(table.querySelectorAll('.produto-select'))
                .filter(s => s !== selectProduto)
                .some(s => s.value == selectProduto.value);
            if (duplicado) {
                alert('Este produto já foi adicionado.');
                selectProduto.value = '';
                unidadeInput.value = '';
                return;
            }

            const produto = produtos.find(p => p.id == selectProduto.value);
            const valorUnit = parseFloat(produto?.valor_unitario || 0);
            valor.value = valorUnit.toFixed(2);
            unidadeInput.value = produto?.unidade_medida?.nome || '';
            subtotal.value = (valorUnit * parseFloat(quantidade.value || 0)).toFixed(2);
            atualizarTotalGeral();
        });

        quantidade.addEventListener('input', () => {
            subtotal.value = (parseFloat(valor.value || 0) * parseFloat(quantidade.value || 0)).toFixed(2);
            atualizarTotalGeral();
        });

        row.querySelector('.removeItem').addEventListener('click', () => {
            row.remove();
            atualizarIndices();
            atualizarTotalGeral();
        });
    }

    function atualizarIndices() {
        table.querySelectorAll('tr').forEach((row, idx) => row.querySelector('td:first-child').textContent = idx + 1);
    }

    function atualizarTotalGeral() {
        let total = 0;
        table.querySelectorAll('.subtotal').forEach(sub => total += parseFloat(sub.value || 0));
        document.getElementById('totalGeral').textContent = total.toFixed(2);
    }

    // Aplica eventos aos itens existentes
    table.querySelectorAll('tr').forEach(row => adicionarEventos(row));
</script> -->

<script>
    const produtos = @json($produtos->load('unidadeMedida'));
    const table = document.querySelector('#itensTable tbody');
    const fornecedorSelect = document.getElementById('fornecedorSelect');
    const scrollContainer = document.getElementById('scrollTableContainer');
    let fornecedorSelecionado = null;
    let itemIndex = table.querySelectorAll('tr').length - 1;

    // Converte string com vírgula para float
    function parseValor(valorStr) {
        if (!valorStr) return 0;
        return parseFloat(valorStr.replace(',', '.')) || 0;
    }

    // Formata número para 2 casas decimais com vírgula
    function formatValor(valor) {
        return valor.toFixed(2).replace('.', ',');
    }

    // Atualiza os índices da tabela
    function atualizarIndices() {
        table.querySelectorAll('tr').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    // Atualiza o total geral
    function atualizarTotalGeral() {
        let total = 0;
        table.querySelectorAll('.subtotal').forEach(sub => {
            total += parseValor(sub.value);
        });
        document.getElementById('totalGeral').textContent = formatValor(total);
    }

    // Adiciona eventos de mudança de produto, quantidade, valor unitário e remoção
    function atualizarValores(row) {
        const selectProduto = row.querySelector('.produto-select');
        const quantidade = row.querySelector('.quantidade');
        const valor = row.querySelector('.valor_unitario');
        const subtotal = row.querySelector('.subtotal');
        const unidadeInput = row.querySelector('.unidade_medida');

        if (selectProduto.value) {
            const produto = produtos.find(p => p.id == selectProduto.value);
            if (produto) {
                const valorUnit = parseFloat(valor.value.replace(',', '.') || produto.preco_compra_atual || 0);
                valor.value = formatValor(valorUnit);
                unidadeInput.value = produto.unidade_medida?.nome || '';
                subtotal.value = formatValor(valorUnit * parseValor(quantidade.value));
            }
        }

        selectProduto.addEventListener('change', () => {
            const duplicado = Array.from(table.querySelectorAll('.produto-select'))
                .filter(s => s !== selectProduto)
                .some(s => s.value === selectProduto.value);
            if (duplicado) {
                alert('Este produto já foi adicionado para este fornecedor.');
                selectProduto.value = '';
                unidadeInput.value = '';
                valor.value = '';
                subtotal.value = '';
                atualizarTotalGeral();
                return;
            }

            const produto = produtos.find(p => p.id == selectProduto.value);
            const valorUnit = parseFloat(produto?.preco_compra_atual || 0);
            valor.value = formatValor(valorUnit);
            unidadeInput.value = produto?.unidade_medida?.nome || '';
            subtotal.value = formatValor(valorUnit * parseValor(quantidade.value));
            atualizarTotalGeral();
        });

        quantidade.addEventListener('input', () => {
            if (parseValor(quantidade.value) <= 0) quantidade.value = 1;
            subtotal.value = formatValor(parseValor(valor.value) * parseValor(quantidade.value));
            atualizarTotalGeral();
        });

        valor.addEventListener('input', () => {
            if (parseValor(valor.value) <= 0) valor.value = '0,01';
            subtotal.value = formatValor(parseValor(valor.value) * parseValor(quantidade.value));
            atualizarTotalGeral();
        });

        row.querySelector('.removeItem').addEventListener('click', () => {
            row.remove();
            atualizarIndices();
            atualizarTotalGeral();
            if (!table.querySelectorAll('tr').length) {
                fornecedorSelecionado = null;
                fornecedorSelect.disabled = false;
            }
        });
    }

    // Adicionar novo item
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
        itemIndex++;

        let options = '<option value="">Selecione</option>';
        produtos.forEach(p => options += `<option value="${p.id}" data-preco-compra="${p.preco_compra_atual}">${p.nome}</option>`);

        row.innerHTML = `
            <td class="text-center"></td>
            <td>
                <select name="itens[${itemIndex}][produto_id]" class="form-control produto-select" required>
                    ${options}
                </select>
            </td>
            <td><input type="text" name="itens[${itemIndex}][unidade_medida]" class="form-control unidade_medida" readonly></td>
            <td><input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade" min="1" value="1" required></td>
            <td><input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control valor_unitario" value="0,01"></td>
            <td><input type="text" name="itens[${itemIndex}][subtotal]" class="form-control subtotal" value="0,01" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeItem">Remover</button></td>
        `;

        table.appendChild(row);
        atualizarValores(row);
        atualizarIndices();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    });

    // Inicializa os itens existentes
    table.querySelectorAll('tr').forEach(row => atualizarValores(row));
    atualizarTotalGeral();
</script>

@endsection