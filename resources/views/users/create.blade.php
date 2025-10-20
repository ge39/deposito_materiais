@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Usuário</h2>

    <div id="alerta" class="alert alert-warning text-center" style="display:none; background-color:#fff3cd; border:1px solid #ffeeba;"></div>

    <form id="formUsuario" action="{{ route('users.store') }}" method="POST">
        @csrf

        <!-- CAMPO CPF -->
        <div class="mb-3">
            <label for="cpf">CPF do Funcionário:</label>
            <div class="input-group">
                <input type="text" name="cpf" id="cpf" class="form-control" placeholder="Digite o CPF">
                <button type="button" id="buscarFuncionario" class="btn btn-primary">Buscar</button>
            </div>
        </div>

        <!-- DADOS DO FUNCIONÁRIO -->
        <div class="mb-3">
            <label for="funcionario_nome">Nome do Funcionário:</label>
            <input type="text" id="funcionario_nome" class="form-control" disabled>
            <input type="hidden" name="funcionario_id" id="funcionario_id">
        </div>

        <div class="mb-3">
            <label for="funcionario_telefone">Telefone do Funcionário:</label>
            <input type="text" id="funcionario_telefone" class="form-control" disabled>
        </div>

        <div class="mb-3">
            <label for="funcionario_email">E-mail do Funcionário:</label>
            <input type="text" id="funcionario_email" class="form-control" disabled>
        </div>

        <!-- DADOS DO USUÁRIO -->
        <div class="mb-3">
            <label for="name">Nome de Usuário:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password">Senha:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation">Confirmar Senha:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
         <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
function mostrarAlerta(mensagem) {
    const alerta = document.getElementById('alerta');
    alerta.style.display = 'block';
    alerta.textContent = mensagem;
    setTimeout(() => { alerta.style.display = 'none'; }, 5000);
}

function mascaraCPF(cpf) {
    cpf = cpf.replace(/\D/g, "");
    cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
    cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return cpf;
}
document.getElementById('cpf').addEventListener('input', function() {
    this.value = mascaraCPF(this.value);
});

document.getElementById('buscarFuncionario').addEventListener('click', function() {
    let cpf = document.getElementById('cpf').value.replace(/\D/g, "");
    fetch('{{ url("/buscar-funcionario") }}/' + cpf)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('funcionario_nome').value = data.data.nome;
                document.getElementById('funcionario_id').value = data.data.id;
                document.getElementById('funcionario_telefone').value = data.data.telefone || '';
                document.getElementById('funcionario_email').value = data.data.email || '';
            } else {
                document.getElementById('funcionario_nome').value = '';
                document.getElementById('funcionario_id').value = '';
                document.getElementById('funcionario_telefone').value = '';
                document.getElementById('funcionario_email').value = '';
                mostrarAlerta(data.message);
            }
        })
        .catch(() => mostrarAlerta('Erro ao buscar funcionário.'));
});

// Validação de senha em tempo real
const senha = document.getElementById('password');
const confirmar = document.getElementById('password_confirmation');

let feedbackSenha = document.getElementById('feedbackSenha');
if(!feedbackSenha){
    feedbackSenha = document.createElement('div');
    feedbackSenha.id = 'feedbackSenha';
    feedbackSenha.style.marginTop = '5px';
    confirmar.parentNode.appendChild(feedbackSenha);
}

function validarSenha() {
    if (senha.value.length < 4 || confirmar.value.length < 4) {
        feedbackSenha.textContent = 'A senha deve ter no mínimo 4 caracteres!';
        feedbackSenha.style.color = 'red';
        return false;
    }
    if (senha.value === confirmar.value) {
        feedbackSenha.textContent = 'As senhas conferem!';
        feedbackSenha.style.color = 'green';
        return true;
    } else {
        feedbackSenha.textContent = 'As senhas não conferem!';
        feedbackSenha.style.color = 'red';
        return false;
    }
}

senha.addEventListener('input', validarSenha);
confirmar.addEventListener('input', validarSenha);

document.getElementById('formUsuario').addEventListener('submit', function(e) {
    if (!validarSenha()) {
        e.preventDefault();
        mostrarAlerta('Corrija a senha antes de enviar o formulário.');
    }
});
</script>
@endsection
