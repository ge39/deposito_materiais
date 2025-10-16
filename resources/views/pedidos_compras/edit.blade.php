@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Pedido #{{ $pedido->id }}</h2>

    <form action="{{ route('pedidos_compras.update', $pedido->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Fornecedor e data -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fornecedor_id" class="form-label">Fornecedor</label>
                <select name="fornecedor_id" id="fornecedor_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($fornecedores as $fornecedor)
                        <option value="{{ $fornecedor->id }}" {{ $pedido->fornecedor_id == $fornecedor->id ? 'selected' : '' }}>{{ $fornecedor->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="data_pedido" class="form-label">Data do Pedido</label>
                <input type="date" name="data_pedido" id="data_pedido" class="form-control" value="{{ $pedido->data_pedido->format('Y-m-d') }}" required>
            </div>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="pendente" {{ $pedido->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                <option value="recebido" {{ $pedido->status == 'recebido' ? 'selected' : '' }}>Recebido</option>
                <option value="cancelado" {{ $pedido->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="2" class="form-control">{{ $pedido->observacoes }}</textarea>
        </div>

        <!-- Itens do pedido (somente leitura) -->
        <h5>Itens do Pedido</h5>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário (R$)</th>
                    <th>Total (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $item)
                    <tr>
                        <td>{{ $item->produto->descricao }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ number_format($item->preco_unitario,2,',','.') }}</td>
                        <td>{{ number_format($item->total,2,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Atualizar Pedido</button>
        <a href="{{ route('pedidos_compras.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
