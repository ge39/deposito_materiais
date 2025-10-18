@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Usuários Ativos</h2>

    {{-- Mensagens de sucesso ou erro --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($users->count() > 0)
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Funcionário</th>
                    <th>E-mail</th>
                    <th>Nível de Acesso</th>
                    <th>Status</th>
                    <th width="180">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->funcionario->nome ?? '—' }}</td>
                        <td>{{ $user->funcionario->email ?? '—' }}</td>
                        <td>{{ ucfirst($user->nivel_acesso) }}</td>
                        <td>
                            <span class="badge bg-success">Ativo</span>
                        </td>
                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Editar</a>
                           <form action="{{ route('users.desativa', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger btn-sm">Desativar</button>
                            </form>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-warning text-center">
            Nenhum usuário ativo encontrado.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('users.create') }}" class="btn btn-success">+ Novo Usuário</a>
    </div>
</div>
@endsection
