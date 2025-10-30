@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Orçamentos</h2>
        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
            Novo Orçamento
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Total</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orcamentos as $orcamento)
            <tr>
                <td>{{ $orcamento->id }}</td>
                <td>{{ $orcamento->cliente->nome ?? '-' }}</td>
                <td>{{ $orcamento->data_orcamento }}</td>
                <td>R$ {{ number_format($orcamento->total, 2, ',', '.') }}</td>
                <td>{{ ucfirst($orcamento->status) }}</td>
                <td>
                    <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-sm btn-info">Ver</a>
                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
