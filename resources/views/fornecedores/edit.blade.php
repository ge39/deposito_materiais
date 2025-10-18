@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Fornecedor</h2>

    <form action="{{ route('fornecedores.update', $fornecedor) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="{{ $fornecedor->nome }}" required>
        </div>
        <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ</label>
            <input type="text" name="cnpj" id="cnpj" class="form-control" value="{{ $fornecedor->cnpj }}">
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" value="{{ $fornecedor->telefone }}">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $fornecedor->email }}">
        </div>
        <div class="mb-3">
            <label for="cidade" class="form-label">Cidade</label>
            <input type="text" name="cidade" id="cidade" class="form-control" value="{{ $fornecedor->cidade }}">
        </div>
        <div class="mb-3">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" name="endereco" id="endereco" class="form-control" value="{{ $fornecedor->endereco }}">
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="3" class="form-control">{{ $fornecedor->observacoes }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
