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
        <tr @if($orcamento->status === 'Expirado') class="text-danger" @endif>
            <td>{{ $orcamento->id }}</td>
            <td>{{ $orcamento->cliente->nome ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</td>
            <td>R$ {{ number_format($orcamento->total, 2, ',', '.') }}</td>
            <td>{{ ucfirst($orcamento->status) }}</td>
            <td>
                <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-primary" target="_blank">Gerar PDF</a>
            </td>
        </tr>
        @endforeach
        </tbody>

    </table>
</div>
@endsection
