@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Lista de Funcionários</h2>
    <a href="{{ route('funcionarios.create') }}" class="btn btn-primary mb-3">Novo Funcionário</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Função</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Salário (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($funcionarios as $funcionario)
                <tr>
                    <td>{{ $funcionario->id }}</td>
                    <td>{{ $funcionario->nome }}</td>
                    <td>{{ ucfirst($funcionario->funcao) }}</td>
                    <td>{{ $funcionario->telefone }}</td>
                    <td>{{ $funcionario->email }}</td>
                    <td>R$ {{ number_format($funcionario->salario, 2, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('funcionarios.show', $funcionario->id) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('funcionarios.destroy', $funcionario->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este funcionário?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Nenhum funcionário cadastrado</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
