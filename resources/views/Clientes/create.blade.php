@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Novo Cliente</h2>
    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="fisica">Pessoa Física</option>
                <option value="juridica">Pessoa Jurídica</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">CPF/CNPJ</label>
            <input type="text" name="cpf_cnpj" class="form-control" required>
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
            <label class="form-label">Limite de Crédito (R$)</label>
            <input type="number" step="0.01" name="limite_credito" class="form-control" placeholder="0.00">
        </div>

        <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" rows="3" class="form-control" placeholder="Informações adicionais sobre o cliente..."></textarea>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
