@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Todas as Devoluções</h2>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Venda #</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Motivo</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devolucoes as $devolucao)
                <tr>
                    <td>{{ $devolucao->id }}</td>
                    <td>{{ $devolucao->cliente->nome ?? '—' }}</td>
                    <td>{{ $devolucao->venda->id ?? '—' }}</td>
                    <td>{{ $devolucao->produto->nome ?? '—' }}</td>
                    <td>{{ $devolucao->quantidade }}</td>
                    <td>{{ $devolucao->motivo }}</td>
                    <td>
                        <span class="badge 
                            @if($devolucao->status == 'pendente') bg-warning
                            @elseif($devolucao->status == 'aprovada') bg-success
                            @else bg-danger @endif">
                            {{ ucfirst($devolucao->status) }}
                        </span>
                    </td>
                    <td>{{ $devolucao->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="background-color: #f5deb3;">
                        Nenhuma devolução registrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
