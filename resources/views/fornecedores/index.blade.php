@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Lista de Fornecedores</h2>
    <a href="{{ route('fornecedores.create') }}" class="btn btn-primary mb-3">Novo Fornecedor</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Endereço</th>
                <th>Cidade</th>
                <th>Observações</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fornecedores as $fornecedor)
                <tr>
                    <td>{{ $fornecedor->id }}</td>
                    <td>{{ $fornecedor->nome }}</td>
                    <td>{{ $fornecedor->cnpj }}</td>
                    <td>{{ $fornecedor->telefone }}</td>
                    <td>{{ $fornecedor->email }}</td>
                    <td>{{ $fornecedor->endereco }}</td>
                    <td>{{ $fornecedor->cidade }}</td>
                    <td>{{ $fornecedor->observacoes }}</td>
                    <td>
                        <a href="{{ route('fornecedores.show', $fornecedor->id) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-warning">Editar</a>

                        <form action="{{ route('fornecedores.destroy', $fornecedor->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este fornecedor?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Nenhum fornecedor cadastrado</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
