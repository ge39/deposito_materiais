@extends('layouts.app')

 @php
     $novaValidade = \Carbon\Carbon::now()->addDays(7)->format('Y/m/d');
@endphp

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Orçamento #{{ $orcamento->codigo_orcamento }}</h2>

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
   
   @if($orcamento->status === 'Expirado')
        <div class="alert alert-danger text-center fw-bold" style="font-size: 18px;">
            ⚠️ ATENÇÃO! ESTE ORÇAMENTO ESTÁ <u>EXPIRADO</u>.<br>
            AO SALVAR A EDIÇÃO, O STATUS SERÁ ALTERADO AUTOMATICAMENTE PARA 
            <span class="text-danger">AGUARDANDO APROVAÇÃO</span>.<br>
            A VALIDADE SERÁ ATUALIZADA PARA <span class="text-primary">{{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }}</span>.
        </div>
    @endif


    <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" id="formOrcamento">
        @csrf
        @method('PUT')
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <!-- Cliente e Datas -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="clienteSelect" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ (old('cliente_id', $orcamento->cliente_id) == $cliente->id) ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data do Orçamento <span class="text-danger">*</span></label>
                        <input type="date" name="data_orcamento" class="form-control"
                               value="{{ old('data_orcamento', $orcamento->data_orcamento->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Validade <span class="text-danger">*</span></label>
                                            @php
                            $validade = $orcamento->status === 'Expirado'
                                ? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d')
                                : (old('validade') ?? \Carbon\Carbon::parse($orcamento->validade)->format('Y-m-d'));
                        @endphp

                        <input type="date" name="validade" class="form-control" value="{{ $validade }}" required>
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
                            <th>Unidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $oldProdutos = old('produtos', $orcamento->itens->map(function($item){
                                return [
                                    'id' => $item->produto_id,
                                    'quantidade' => $item->quantidade,
                                    'preco_unitario' => $item->preco_unitario,
                                    'unidade' => $item->produto->unidadeMedida->nome ?? ''
                                ];
                            })->toArray());
                        @endphp

                        @foreach($oldProdutos as $i => $oldItem)
                            <tr>
                                <td>
                                    <select name="produtos[{{ $i }}][id]" class="form-select produtoSelect" required>
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
                                    <input type="number" name="produtos[{{ $i }}][quantidade]" class="form-control qtd" min="1" value="{{ $oldItem['quantidade'] }}" required>
                                </td>
                                <td>
                                    <label class="form-label unidade">{{ $oldItem['unidade'] }}</label>
                                </td>
                                <td>
                                    <label class="form-label preco">{{ number_format($oldItem['preco_unitario'], 2, ',', '.') }}</label>
                                    <input type="hidden" name="produtos[{{ $i }}][preco_unitario]" value="{{ $oldItem['preco_unitario'] }}">
                                </td>
                                <td>
                                    <label class="form-label subtotal">{{ number_format($oldItem['quantidade'] * $oldItem['preco_unitario'], 2, ',', '.') }}</label>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remover">Remover</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Botões -->
                <div class="text-end">
                    <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
                </div>

                <div class="text-end mb-3" style="margin-top:10px">
                    <h5>Total: R$ <span id="total">{{ number_format($orcamento->total, 2, ',', '.') }}</span></h5>
                </div>

                <!-- Observações -->
                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $orcamento->observacoes ?: 'Sem observações') }}</textarea>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
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
                    ${produtos.map(p => `<option value="${p.id}" data-preco="${p.preco_venda}" data-unidade="${p.unidade_medida?.nome || ''}">${p.nome}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="produtos[${index}][quantidade]" class="form-control qtd" min="1" value="1" required>
            </td>
            <td>
                <label class="form-label unidade"></label>
            </td>
            <td>
                <label class="form-label preco"></label>
                <input type="hidden" name="produtos[${index}][preco_unitario]" value="0">
            </td>
            <td>
                <label class="form-label subtotal">0,00</label>
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
            const preco = parseFloat(tr.querySelector('input[name*="[preco_unitario]"]').value) || 0;
            const subtotal = qtd * preco;
            tr.querySelector('.subtotal').textContent = subtotal.toFixed(2).replace('.', ',');
            total += subtotal;
        });
        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    tableBody.addEventListener('change', e => {
        if (e.target.classList.contains('produtoSelect')) {
            const option = e.target.selectedOptions[0];
            const preco = option.dataset.preco || 0;
            const unidade = option.dataset.unidade || '';
            const tr = e.target.closest('tr');

            tr.querySelector('.preco').textContent = parseFloat(preco).toFixed(2).replace('.', ',');
            tr.querySelector('input[name*="[preco_unitario]"]').value = preco;
            tr.querySelector('.unidade').textContent = unidade;

            atualizarSubtotal();
        }
    });

    tableBody.addEventListener('input', e => {
        if (e.target.classList.contains('qtd')) atualizarSubtotal();
    });

    tableBody.addEventListener('click', e => {
        if (e.target.classList.contains('remover')) {
            e.target.closest('tr').remove();
            atualizarSubtotal();
            if (tableBody.querySelectorAll('tr').length === 0) clienteSelect.classList.remove('readonly-select');
        }
    });

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
            const preco = lastRow.querySelector('input[name*="[preco_unitario]"]').value;
            if (!produto || !qtd || !preco) {
                alert('Preencha o item anterior antes de adicionar um novo.');
                return;
            }
        }
        criarItem();
    });

    atualizarSubtotal();
});
</script>
@endsection
