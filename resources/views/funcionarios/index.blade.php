@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Lista de Funcionários</h2>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success" id="alerta">{{ session('success') }}</div>
    @endif

    <!-- Botão Novo -->
    <a href="{{ route('funcionarios.create') }}" class="btn btn-success mb-3">Novo Funcionário</a>

    <!-- Tabela -->
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>CPF</th>
                <th>Nome</th>
                <th>Função</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Ativo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($funcionarios as $funcionario)
            <tr>
                <td>{{ $funcionario->cpf }}</td>
                <td>{{ $funcionario->nome }}</td>
                <td>{{ $funcionario->funcao }}</td>
                <td>{{ $funcionario->telefone }}</td>
                <td>{{ $funcionario->email }}</td>
                <td>{{ $funcionario->ativo ? 'Sim' : 'Não' }}</td>
                <td>
                    <a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-primary btn-sm">Editar</a>

                    <form action="{{ route('funcionarios.desativa', $funcionario->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Tem certeza que deseja desativar este funcionário?');">
                            Desativar
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // Alerta automático desaparecendo após 5 segundos
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => {
            alerta.style.display = 'none';
        }, 5000);
    }
</script>
@endsection
