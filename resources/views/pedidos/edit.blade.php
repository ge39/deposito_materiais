@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Pedido de Compra</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card p-3 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label>Fornecedor</label>
                    <select name="fornecedor_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($fornecedores as $fornecedor)
                            <option value="{{ $fornecedor->id }}" {{ $pedido->fornecedor_id == $fornecedor->id ? 'selected' : '' }}>
                                {{ $fornecedor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Data do Pedido</label>
                    <input type="date" name="data_pedido" class="form-control" value="{{ $pedido->data_pedido->format('Y-m-d') }}" required>
                </div>
            </div>
        </div>

        <div class="card p-3 mb-4">
            <h5>Itens do Pedido</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->itens as $index => $item)
                    <tr>
                        <td>
                            <select name="itens[{{ $index }}][produto_id]" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach($produtos as $produto)
                                    <option value="{{ $produto->id }}" {{ $item->produto_id == $produto->id ? 'selected' : '' }}>
                                        {{ $produto->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="itens[{{ $index }}][quantidade]" class="form-control" min="1" value="{{ $item->quantidade }}" required></td>
                        <td><input type="number" name="itens[{{ $index }}][valor_unitario]" class="form-control" step="0.01" value="{{ $item->valor_unitario }}" required></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success mb-3">Atualizar Pedido</button>
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary mb-3">Voltar</a>
        </div>
    </form>
</div>
@endsection
