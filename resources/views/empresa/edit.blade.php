@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Empresa / Filial</h2>

    <div class="card shadow-sm border rounded-2 p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('empresa.update', $empresa->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $empresa->nome) }}" required>
                </div>

                <div class="col-md-6">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" class="form-control" value="{{ old('cnpj', $empresa->cnpj) }}">
                </div>

                <div class="col-md-6">
                    <label>Inscrição Estadual</label>
                    <input type="text" name="inscricao_estadual" class="form-control" value="{{ old('inscricao_estadual', $empresa->inscricao_estadual) }}">
                </div>

                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $empresa->telefone) }}">
                </div>

                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}">
                </div>

                <div class="col-md-6">
                    <label>Site</label>
                    <input type="text" name="site" class="form-control" value="{{ old('site', $empresa->site) }}">
                </div>

                <div class="col-md-6">
                    <label>Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="{{ old('endereco', $empresa->endereco) }}">
                </div>

                <div class="col-md-2">
                    <label>Número</label>
                    <input type="text" name="numero" class="form-control" value="{{ old('numero', $empresa->numero) }}">
                </div>

                <div class="col-md-4">
                    <label>Complemento</label>
                    <input type="text" name="complemento" class="form-control" value="{{ old('complemento', $empresa->complemento) }}">
                </div>

                <div class="col-md-4">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="{{ old('bairro', $empresa->bairro) }}">
                </div>

                <div class="col-md-4">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $empresa->cidade) }}">
                </div>

                <div class="col-md-2">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" class="form-control" value="{{ old('estado', $empresa->estado) }}">
                </div>

                <div class="col-md-2">
                    <label>CEP</label>
                    <input type="text" name="cep" class="form-control" value="{{ old('cep', $empresa->cep) }}">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</div>
@endsection
