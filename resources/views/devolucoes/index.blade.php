@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Devoluções e Trocas</h2>
    <a href="{{ route('devolucoes.create') }}" class="btn btn-primary mb-3">Nova Devolução/Troca</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Venda</th>
                <th>Produto</th>
                <th>Tipo</th>
                <th>Produto Troca</th>
                <th>Quantidade</th>
                <th>Diferença (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devolucoes as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->venda->id }}</td>
                    <td>{{ $d->produto->descricao }}</td>
                    <td>{{ ucfirst($d->tipo) }}</td>
                    <td>{{ $d->produtoTroca->descricao ?? '-' }}</td>
                    <td>{{ $d->quantidade }}</td>
                    <td>{{ number_format($d->diferenca,2,',','.') }}</td>
                    <td>
                        <a href="{{ route('devolucoes.show', $d->id) }}" class="btn btn-info btn-sm">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Nenhuma devolução registrada</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
