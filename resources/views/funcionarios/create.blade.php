@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Funcionário</h2>

    @if(session('success'))
        <div class="alert alert-success" id="alerta">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" id="alerta">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('funcionarios.store') }}" method="POST">
        @csrf

        <div class="row g-3">

            <!-- Dados Pessoais -->
            <div class="col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control" value="{{ old('cpf') }}" required>
            </div>

            <div class="col-md-8">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" class="form-control" value="{{ old('nome') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Função</label>
                <select name="funcao" class="form-select" required>
                    <option value="vendedor" @if(old('funcao')=='vendedor') selected @endif>Vendedor</option>
                    <option value="administrativo" @if(old('funcao')=='administrativo') selected @endif>Administrativo</option>
                    <option value="motorista" @if(old('funcao')=='motorista') selected @endif>Motorista</option>
                    <option value="estoquista" @if(old('funcao')=='estoquista') selected @endif>Estoquista</option>
                    <option value="repositor" @if(old('funcao')=='repositor') selected @endif>Repositor</option>
                    <option value="diarista" @if(old('funcao')=='diarista') selected @endif>Diarista</option>
                    <option value="manobrista" @if(old('funcao')=='manobrista') selected @endif>Manobrista</option>
                    <option value="caixa" @if(old('funcao')=='caixa') selected @endif>Caixa</option>
                    <option value="outro" @if(old('funcao')=='outro') selected @endif>Outro</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" name="telefone" id="telefone" class="form-control" value="{{ old('telefone') }}">
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
            </div>

            <!-- Endereço com CEP -->
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" name="cep" id="cep" class="form-control" value="{{ old('cep') }}" onblur="buscarCep()">
            </div>

            <div class="col-md-5">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="{{ old('endereco') }}">
            </div>

            <div class="col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="text" name="numero" id="numero" class="form-control" value="{{ old('numero') }}">
            </div>
            <div class="col-md-4">
                <label for="bairro" class="form-label">Bairro</label>
                <input type="text" name="bairro" id="bairro" class="form-control" value="{{ old('bairro') }}">

            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control" value="{{ old('cidade') }}">
            </div>

            <div class="col-md-2">
                <label for="uf" class="form-label">Estado</label>
                <input type="text" name="uf" id="uf" class="form-control" value="{{ old('uf') }}">
            </div>

            <div class="col-md-12">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea name="observacoes" id="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="data_admissao" class="form-label">Data de Admissão</label>
                <input type="date" name="data_admissao" id="data_admissao" class="form-control" value="{{ old('data_admissao') }}">
            </div>

            <div class="col-md-12 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ old('ativo', 1) ? 'checked' : '' }}>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>

        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

@endsection
