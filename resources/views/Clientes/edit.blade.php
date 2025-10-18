@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Cliente</h2>
    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ $cliente->nome }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="fisica" @if($cliente->tipo == 'fisica') selected @endif>Pessoa Física</option>
                <option value="juridica" @if($cliente->tipo == 'juridica') selected @endif>Pessoa Jurídica</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">CPF/CNPJ</label>
            <input type="text" name="cpf_cnpj" class="form-control" value="{{ $cliente->cpf_cnpj }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control" value="{{ $cliente->telefone }}">
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" value="{{ $cliente->email }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Limite de Crédito (R$)</label>
            <input type="number" step="0.01" name="limite_credito" class="form-control" value="{{ $cliente->limite_credito }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" rows="3" class="form-control">{{ $cliente->observacoes }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
