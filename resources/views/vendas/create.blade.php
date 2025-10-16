@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Venda</h2>

    <form action="{{ route('vendas.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="data" class="form-label">Data</label>
                <input type="date" name="data" id="data" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <h5>Produtos</h5>
        <table class="table table-bordered mb-3" id="produtos_table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Total (R$)</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="produtos[0][produto_id]" class="form-select produto_select" required>
                            <option value="">Selecione</option>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}">{{ $produto->descricao }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="produtos[0][quantidade]" class="form-control quantidade" value="1" min="1" required></td>
                    <td><input type="number" step="0.01" name="produtos[0][preco]" class="form-control preco" readonly></td>
                    <td><input type="number" step="0.01" name="produtos[0][total]" class="form-control total" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove_produto">Excluir</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary mb-3" id="add_produto">Adicionar Produto</button>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="2" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

@push('scripts')
<script>
let contador = 1;

// Atualiza preço e total quando produto ou quantidade mudar
function atualizarLinha(linha) {
    const select = linha.querySelector('.produto_select');
    const precoInput = linha.querySelector('.preco');
    const quantidadeInput = linha.querySelector('.quantidade');
    const totalInput = linha.querySelector('.total');

    const preco = parseFloat(select.selectedOptions[0].dataset.preco || 0);
    const quantidade = parseFloat(quantidadeInput.value || 0);
    precoInput.value = preco.toFixed(2);
    totalInput.value = (preco * quantidade).toFixed(2);
}

document.querySelector('#produtos_table').addEventListener('change', function(e) {
    if(e.target.classList.contains('produto_select') || e.target.classList.contains('quantidade')) {
        const linha = e.target.closest('tr');
        atualizarLinha(linha);
    }
});

document.querySelector('#add_produto').addEventListener('click', function() {
    const tbody = document.querySelector('#produtos_table tbody');
    const novaLinha = tbody.rows[0].cloneNode(true);

    novaLinha.querySelectorAll('select, input').forEach(input => {
        if(input.tagName === 'SELECT') input.selectedIndex = 0;
        else input.value = input.classList.contains('quantidade') ? 1 : 0;
        const name = input.name.replace(/\d+/, contador);
        input.name = name;
    });

    tbody.appendChild(novaLinha);
    contador++;
});

document.querySelector('#produtos_table').addEventListener('click', function(e){
    if(e.target.classList.contains('remove_produto')) {
        const linhas = this.querySelectorAll('tbody tr');
        if(linhas.length > 1) e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection
