@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Orçamentos de Clientes</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">Novo Orçamento</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th>Total (R$)</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orcamentos as $orcamento)
                        <tr>
                            <td>{{ $orcamento->id }}</td>
                            <td>{{ $orcamento->cliente->nome ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $orcamento->status == 'Aprovado' ? 'success' : ($orcamento->status == 'Cancelado' ? 'danger' : 'secondary') }}">
                                    {{ $orcamento->status }}
                                </span>
                            </td>
                            <td>{{ number_format($orcamento->total, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Excluir este orçamento?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Nenhum orçamento encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
