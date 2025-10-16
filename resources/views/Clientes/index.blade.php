@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Lista de Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary mb-3">Novo Cliente</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>CPF/CNPJ</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Limite de Crédito</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->nome }}</td>
                    <td>{{ ucfirst($cliente->tipo) }}</td>
                    <td>{{ $cliente->cpf_cnpj }}</td>
                    <td>{{ $cliente->telefone }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td>R$ {{ number_format($cliente->limite_credito, 2, ',', '.') }}</td>
                    <td>{{ $cliente->observacoes }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este cliente?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Nenhum cliente cadastrado</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
