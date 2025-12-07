<!-- pdv/abrir.blade.php -->
@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0">Abrir Venda</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pdv.abrir') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <select name="cliente_id" class="form-select" required>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 py-2">Abrir Venda</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

        <!-- Painel direito: lista de itens -->
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                    <span>Itens da Venda</span>
                    <span class="fw-bold">Total: R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Qtd.</th>
                                <th class="text-end">Preço</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itens as $item)
                                <tr>
                                    <td>{{ $item->produto->nome }}</td>
                                    <td class="text-center">{{ $item->quantidade }}</td>
                                    <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('pdv.removerItem', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-end d-flex justify-content-between">
                    <a href="{{ route('pdv.cancelar', $venda->id) }}" class="btn btn-warning px-4 py-2 rounded-3">Cancelar Venda</a>
                    <a href="{{ route('pdv.finalizar', $venda->id) }}" class="btn btn-success px-4 py-2 rounded-3">Finalizar Venda</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection