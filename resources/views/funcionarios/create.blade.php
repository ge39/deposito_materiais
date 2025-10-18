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

        <div class="mb-3">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" class="form-control" placeholder="000.000.000-00" required>
        </div>

        <div class="mb-3">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>

       <!-- Função -->
        <div class="mb-3">
            <label class="form-label">Função</label>
            <select name="funcao" class="form-select" required>
                <option value="vendedor">Vendedor</option>
                <option value="administrativo">Administrativo</option>
                <option value="motorista">Motorista</option>
                <option value="estoquista">Estoquista</option>
                <option value="repositor">Repositor</option>
                <option value="diarista">Diarista</option>
                <option value="manobrista">Manobrista</option>
                <option value="caixa">Caixa</option>
                <option value="outro">Outro</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" class="form-control">
        </div>

        <div class="mb-3">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" class="form-control">
        </div>

        <div class="mb-3">
            <label for="endereco">Endereço:</label>
            <input type="text" name="endereco" id="endereco" class="form-control">
        </div>

        <div class="mb-3">
            <label for="cidade">Cidade:</label>
            <input type="text" name="cidade" id="cidade" class="form-control">
        </div>

        <div class="mb-3">
            <label for="observacoes">Observações:</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="data_admissao">Data de Admissão:</label>
            <input type="date" name="data_admissao" id="data_admissao" class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" checked>
            <label for="ativo" class="form-check-label">Ativo</label>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('funcionarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
    // Máscara simples CPF
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
