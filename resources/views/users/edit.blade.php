@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Usuário</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Funcionário</label>
            <input type="text" class="form-control" value="{{ $user->funcionario->nome ?? '—' }}" readonly>
        </div>

        <div class="mb-3">
            <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
            <select name="nivel_acesso" id="nivel_acesso" class="form-select" required>
                <option value="admin" {{ $user->nivel_acesso === 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="vendedor" {{ $user->nivel_acesso === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                <option value="gerente" {{ $user->nivel_acesso === 'gerente' ? 'selected' : '' }}>Gerente</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (opcional)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label for="ativo" class="form-label">Status</label>
            <select name="ativo" id="ativo" class="form-select">
                <option value="1" {{ $user->ativo ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ !$user->ativo ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
