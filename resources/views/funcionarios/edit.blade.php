@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Funcionário</h2>

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

        <!-- CPF -->
        <div class="mb-3">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" class="form-control" value="{{ $funcionario->cpf }}" required>
        </div>

        <!-- Nome -->
        <div class="mb-3">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" class="form-control" value="{{ $funcionario->nome }}" required>
        </div>

        <!-- Função -->
        <div class="mb-3">
            <label for="funcao">Função:</label>
            <input type="text" name="funcao" id="funcao" class="form-control" value="{{ $funcionario->funcao }}" required>
        </div>

        <!-- Telefone -->
        <div class="mb-3">
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" class="form-control" value="{{ $funcionario->telefone }}">
        </div>

        <!-- E-mail -->
        <div class="mb-3">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $funcionario->email }}">
        </div>

        <!-- Endereço -->
        <div class="mb-3">
            <label for="endereco">Endereço:</label>
            <input type="text" name="endereco" id="endereco" class="form-control" value="{{ $funcionario->endereco }}">
        </div>

        <!-- Cidade -->
        <div class="mb-3">
            <label for="cidade">Cidade:</label>
            <input type="text" name="cidade" id="cidade" class="form-control" value="{{ $funcionario->cidade }}">
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes">Observações:</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="3">{{ $funcionario->observacoes }}</textarea>
        </div>

        <!-- Data de Admissão -->
        <div class="mb-3">
            <label for="data_admissao">Data de Admissão:</label>
            <input type="date" name="data_admissao" id="data_admissao" class="form-control"
                   value="{{ $funcionario->data_admissao ? $funcionario->data_admissao->format('Y-m-d') : '' }}">
        </div>

        <!-- Ativo -->
        <div class="mb-3 form-check">
            <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ $funcionario->ativo ? 'checked' : '' }}>
            <label for="ativo" class="form-check-label">Ativo</label>
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
