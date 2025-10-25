@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Pedidos de Compras</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Novo Pedido</a>
    </div>

    <div class="card p-3 table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Fornecedor</th>
                    <th>DT Pedido</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Criado por</th>
                    <th>Ações</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->id }}</td>
                        <td >{{ $pedido->fornecedor->nome ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</td>
                        <td>{{ number_format($pedido->total, 2, ',', '.') }}</td>
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
                        </td>
                        <td>{{ $pedido->user->name ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-info btn-sm">Visualizar</a>
                                @if($pedido->status != 'cancelado' && $pedido->status != 'recebido')
                                    <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                @endif
                                <a href="{{ route('pedidos.pdf', $pedido->id) }}" target="_blank" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Imprimir
                                </a>

                                {{-- Botões de alteração de status --}}
                                @if($pedido->status == 'pendente')
                                    <a href="{{ route('pedidos.aprovar', $pedido->id) }}" class="btn btn-primary btn-sm">Aprovar</a>
                                    <a href="{{ route('pedidos.cancelar', $pedido->id) }}" class="btn btn-danger btn-sm">Cancelar</a>
                                @elseif($pedido->status == 'aprovado')
                                    <a href="{{ route('pedidos.receber', $pedido->id) }}" class="btn btn-success btn-sm">Receber</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum pedido encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            <div class="d-inline-block">
                {{ $pedidos->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
