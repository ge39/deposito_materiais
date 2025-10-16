@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes do Funcionário</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Nome:</strong> {{ $funcionario->nome }}</p>
            <p><strong>Função:</strong> {{ ucfirst($funcionario->funcao) }}</p>
            <p><strong>Telefone:</strong> {{ $funcionario->telefone }}</p>
            <p><strong>Email:</strong> {{ $funcionario->email }}</p>
            <p><strong>Salário:</strong> R$ {{ number_format($funcionario->salario, 2, ',', '.') }}</p>
            <p><strong>Data de Admissão:</strong> {{ $funcionario->data_admissao ? \Carbon\Carbon::parse($funcionario->data_admissao)->format('d/m/Y') : '—' }}</p>
            <p><strong>Observações:</strong> {{ $funcionario->observacoes ?: '—' }}</p>
            <p><strong>Criado em:</strong> {{ $funcionario->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
