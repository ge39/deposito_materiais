@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Funcionário: {{ $funcionario->nome }}</h2>

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

    <form action="{{ route('funcionarios.update', $funcionario->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control" value="{{ $funcionario->cpf }}" required>
            </div>
            <div class="col-md-4">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" name="nome" id="nome" class="form-control" value="{{ $funcionario->nome }}" required>
            </div>
            <div class="col-md-4">
                <label for="funcao" class="form-label">Função</label>
                <input type="text" name="funcao" id="funcao" class="form-control" value="{{ $funcionario->funcao }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" name="telefone" id="telefone" class="form-control" value="{{ $funcionario->telefone }}">
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $funcionario->email }}">
            </div>
            <div class="col-md-4">
                <label for="data_admissao" class="form-label">Data de Admissão</label>
                <input type="date" name="data_admissao" id="data_admissao" class="form-control"
                       value="{{ $funcionario->data_admissao ? $funcionario->data_admissao->format('Y-m-d') : '' }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="{{ $funcionario->endereco }}" required>
            </div>
            <div class="col-md-1">
                <label for="numero" class="form-label">Número</label>
                <input type="text" name="numero" id="numero" class="form-control" value="{{ $funcionario->numero }}" required>
            </div>
            <div class="col-md-4">
                <label for="bairro" class="form-label">Bairro</label>
                <input type="text" name="bairro" id="bairro" class="form-control" value="{{ $funcionario->bairro }}" required>
            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control" value="{{ $funcionario->cidade }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <input type="text" name="estado" id="estado" class="form-control" value="{{ $funcionario->estado }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-center">
                <div class="form-check mt-2">
                    <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ $funcionario->ativo ? 'checked' : '' }}>
                    <label for="ativo" class="form-check-label">Ativo</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="3">{{ $funcionario->observacoes }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
    // Máscara simples para CPF
    document.getElementById('cpf').addEventListener('input', function() {
        let cpf = this.value.replace(/\D/g, '');
        cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
        cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        this.value = cpf;
    });

    // Alerta automático desaparecendo
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => {
            alerta.style.display = 'none';
        }, 5000);
    }
</script>
@endsection
