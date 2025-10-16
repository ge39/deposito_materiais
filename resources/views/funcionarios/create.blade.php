@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Novo Funcionário</h2>
    <form action="{{ route('funcionarios.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Função</label>
            <select name="funcao" class="form-select" required>
                <option value="vendedor">Vendedor</option>
                <option value="administrativo">Administrativo</option>
                <option value="motorista">Motorista</option>
                <option value="estoquista">Estoquista</option>
                <option value="outro">Outro</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Salário (R$)</label>
            <input type="number" step="0.01" name="salario" class="form-control" placeholder="0.00">
        </div>

        <div class="mb-3">
            <label class="form-label">Data de Admissão</label>
            <input type="date" name="data_admissao" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" rows="3" class="form-control" placeholder="Informações adicionais sobre o funcionário..."></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
