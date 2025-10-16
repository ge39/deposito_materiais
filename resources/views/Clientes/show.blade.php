@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes do Cliente</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Nome:</strong> {{ $cliente->nome }}</p>
            <p><strong>Tipo:</strong> {{ ucfirst($cliente->tipo) }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
            <p><strong>Telefone:</strong> {{ $cliente->telefone }}</p>
            <p><strong>Email:</strong> {{ $cliente->email }}</p>
            <p><strong>Limite de Crédito:</strong> R$ {{ number_format($cliente->limite_credito, 2, ',', '.') }}</p>
            <p><strong>Observações:</strong> {{ $cliente->observacoes ?: '—' }}</p>
            <p><strong>Criado em:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    <a href="{{ route('clientes.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
