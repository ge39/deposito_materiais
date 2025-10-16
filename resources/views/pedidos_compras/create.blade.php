@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Novo Pedido de Compras</h2>

    <form action="{{ route('pedidos_compras.store') }}" method="POST">
        @csrf

        <!-- Fornecedor e data -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="data_pedido" class="form-label">Data do Pedido</label>
                <input type="date" name="data_pedido" id="data_pedido" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="2" class="form-control"></textarea>
        </div>

        <!-- Itens do pedido -->
        <h5>Itens do Pedido</h5>
        <table class="table table-bordered" id="tabela-itens">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Total (R$)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="itens[0][produto_id]" class="form-select produto-select" required>
                            <option value="">Selecione</option>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}">{{ $produto->descricao }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="itens[0][quantidade]" class="form-control quantidade" value="1" min="1" required></td>
                    <td><input type="number" name="itens[0][preco_unitario]" class="form-control preco_unitario" step="0.01" value="0" required></td>
                    <td><input type="text" class="form-control total_item" value="0" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remover-linha">Remover</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary mb-3" id="adicionar-item">Adicionar Item</button>

        <!-- Total do pedido -->
        <div class="mb-3">
            <label class="form-label"><strong>Total do Pedido (R$):</strong></label>
            <input type="text" id="total_pedido" class="form-control" value="0" readonly>
        </div>

        <button type="submit" class="btn btn-success">Cadastrar Pedido</button>
        <a href="{{ route('pedidos_compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

@push('scripts')
<script>
let contador = 1;

function atualizarTotalLinha(linha){
    const quantidade = parseFloat(linha.querySelector('.quantidade').value) || 0;
    const preco = parseFloat(linha.querySelector('.preco_unitario').value) || 0;
    const total = quantidade * preco;
    linha.querySelector('.total_item').value = total.toFixed(2);
    atualizarTotalPedido();
}

function atualizarTotalPedido(){
    let totalPedido = 0;
    document.querySelectorAll('.total_item').forEach(input => {
        totalPedido += parseFloat(input.value) || 0;
    });
    document.getElementById('total_pedido').value = totalPedido.toFixed(2);
}

document.getElementById('tabela-itens').addEventListener('input', function(e){
    if(e.target.classList.contains('quantidade') || e.target.classList.contains('preco_unitario')){
        atualizarTotalLinha(e.target.closest('tr'));
    }
});

document.getElementById('adicionar-item').addEventListener('click', function(){
    const tabela = document.querySelector('#tabela-itens tbody');
    const novaLinha = document.createElement('tr');
    novaLinha.innerHTML = `
        <td>
            <select name="itens[${contador}][produto_id]" class="form-select produto-select" required>
                <option value="">Selecione</option>
                @foreach($produtos as $produto)
                    <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}">{{ $produto->descricao }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="itens[${contador}][quantidade]" class="form-control quantidade" value="1" min="1" required></td>
        <td><input type="number" name="itens[${contador}][preco_unitario]" class="form-control preco_unitario" step="0.01" value="0" required></td>
        <td><input type="text" class="form-control total_item" value="0" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm remover-linha">Remover</button></td>
    `;
    tabela.appendChild(novaLinha);
    contador++;
});

document.getElementById('tabela-itens').addEventListener('click', function(e){
    if(e.target.classList.contains('remover-linha')){
        e.target.closest('tr').remove();
        atualizarTotalPedido();
    }
});

// Atualiza preço automaticamente ao selecionar produto
document.getElementById('tabela-itens').addEventListener('change', function(e){
    if(e.target.classList.contains('produto-select')){
        const option = e.target.selectedOptions[0];
        const preco = option.dataset.preco || 0;
        const linha = e.target.closest('tr');
        linha.querySelector('.preco_unitario').value = preco;
        atualizarTotalLinha(linha);
    }
});
</script>
@endpush
@endsection
