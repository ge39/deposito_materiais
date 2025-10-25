@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Novo Orçamento</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro!</strong> Verifique os campos obrigatórios.
        </div>
    @endif

    <form action="{{ route('orcamentos.store') }}" method="POST" id="formOrcamento">
        @csrf
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data do Orçamento</label>
                        <input type="date" name="data_orcamento" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Validade</label>
                        <input type="date" name="validade" class="form-control" required>
                    </div>
                </div>

                <hr>

                <h5>Itens do Orçamento</h5>
                <table class="table table-bordered align-middle" id="tabelaProdutos">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th style="width:120px">Qtd</th>
                            <th style="width:150px">Preço (R$)</th>
                            <th style="width:150px">Subtotal (R$)</th>
                            <th style="width:70px" class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="text-end mb-3">
                    <button type="button" class="btn btn-sm btn-secondary" id="addProduto">+ Adicionar Produto</button>
                </div>

                <div class="text-end">
                    <h5>Total: <span id="total">0,00</span></h5>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Salvar Orçamento</button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const produtos = @json($produtos);
    const tabela = document.querySelector('#tabelaProdutos tbody');
    const totalSpan = document.getElementById('total');

    document.getElementById('addProduto').addEventListener('click', () => {
        const linha = document.createElement('tr');
        linha.innerHTML = `
            <td>
                <select name="produtos[][id]" class="form-select produtoSelect" required>
                    <option value="">Selecione...</option>
                    ${produtos.map(p => `<option value="${p.id}" data-preco="${p.preco}">${p.nome}</option>`).join('')}
                </select>
            </td>
            <td><input type="number" name="produtos[][quantidade]" class="form-control qtd" min="1" value="1" required></td>
            <td><input type="number" name="produtos[][preco_unitario]" class="form-control preco" step="0.01" required></td>
            <td class="subtotal">0,00</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remover">X</button></td>
        `;
        tabela.appendChild(linha);
    });

    tabela.addEventListener('input', calcular);
    tabela.addEventListener('change', e => {
        if (e.target.classList.contains('produtoSelect')) {
            const preco = e.target.selectedOptions[0].dataset.preco || 0;
            e.target.closest('tr').querySelector('.preco').value = preco;
            calcular();
        }
    });

    tabela.addEventListener('click', e => {
        if (e.target.classList.contains('remover')) {
            e.target.closest('tr').remove();
            calcular();
        }
    });

    function calcular() {
        let total = 0;
        tabela.querySelectorAll('tr').forEach(tr => {
            const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
            const preco = parseFloat(tr.querySelector('.preco').value) || 0;
            const subtotal = qtd * preco;
            tr.querySelector('.subtotal').textContent = subtotal.toFixed(2).replace('.', ',');
            total += subtotal;
        });
        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }
});
</script>
@endsection
