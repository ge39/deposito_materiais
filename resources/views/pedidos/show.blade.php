@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Visualizar Pedido de Compra #{{ $pedido->id }}</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Código</h6>
                    <p class="card-text fw-bold">{{ $pedido->id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Fornecedor</h6>
                    <p class="card-text fw-bold">
                        {{ $pedido->fornecedor->nome ?? $pedido->fornecedor->nome_fantasia ?? $pedido->fornecedor->razao_social }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Data do Pedido</h6>
                    <p class="card-text fw-bold">{{ $pedido->data_pedido->format('d/m/Y h:i:s') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm mb-2 border-0">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">Status</h6>
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
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mt-4 mb-3">Itens do Pedido</h5>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-borderless align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 250px;">Produto</th>
                    <th style="width: 100px;">Unidade</th>
                    <th style="width: 100px;">Quantidade</th>
                    <th style="width: 120px;">Valor Unitário (R$)</th>
                    <th style="width: 120px;">Subtotal (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>{{ $item->produto->unidadeMedida->nome ?? '-' }}</td>
                        <td>{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-3 mt-3">
        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Voltar</a>
        <h5 class="mb-0">Total: R$ <span id="totalGeral">{{ number_format($pedido->total, 2, ',', '.') }}</span></h5>
    </div>
</div>
@endsection
