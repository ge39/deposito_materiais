@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Funcionário</h2>
    <form action="{{ route('funcionarios.update', $funcionario->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ $funcionario->nome }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Função</label>
            <select name="funcao" class="form-select" required>
                <option value="vendedor" @if($funcionario->funcao == 'vendedor') selected @endif>Vendedor</option>
                <option value="administrativo" @if($funcionario->funcao == 'administrativo') selected @endif>Administrativo</option>
                <option value="motorista" @if($funcionario->funcao == 'motorista') selected @endif>Motorista</option>
                <option value="estoquista" @if($funcionario->funcao == 'estoquista') selected @endif>Estoquista</option>
                <option value="outro" @if($funcionario->funcao == 'outro') selected @endif>Outro</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control" value="{{ $funcionario->telefone }}">
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" value="{{ $funcionario->email }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Salário (R$)</label>
            <input type="number" step="0.01" name="salario" class="form-control" value="{{ $funcionario->salario }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Data de Admissão</label>
            <input type="date" name="data_admissao" class="form-control" value="{{ $funcionario->data_admissao }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" rows="3" class="form-control">{{ $funcionario->observacoes }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
