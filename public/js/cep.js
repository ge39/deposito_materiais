// Mascara simples para CPF ou CEP
function mascaraCEP(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length > 5) {
        cep = cep.replace(/^(\d{5})(\d)/, "$1-$2");
    }
    return cep;
}

// Busca CEP via ViaCEP
function buscarCep() {
    const inputCep = document.getElementById('cep');
    const cep = inputCep.value.replace(/\D/g, '');

    // Limpa campos se CEP inválido
    if (cep.length !== 8) {
        limparCampos();
        return;
    }

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => res.json())
        .then(data => {
            limparAlerta();

            if (data.erro) {
                mostrarMensagemCep("CEP não encontrado!");
                limparCampos();
            } else {
                document.getElementById('endereco').value = data.logradouro || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.localidade || '';
                document.getElementById('uf').value = data.uf || '';
            }
        })
        .catch(() => {
            mostrarMensagemCep("Erro ao consultar CEP!");
        });
}

// Limpa campos de endereço
function limparCampos() {
    document.getElementById('endereco').value = '';
    document.getElementById('bairro').value = '';
    document.getElementById('cidade').value = '';
    document.getElementById('uf').value = '';
}

// Mensagem de alerta
function mostrarMensagemCep(msg) {
    let alerta = document.getElementById('alerta-cep');
    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id = 'alerta-cep';
        alerta.className = 'alert alert-danger mt-2';
        document.getElementById('cep').parentNode.appendChild(alerta);
    }
    alerta.textContent = msg;
    setTimeout(() => alerta.remove(), 5000);
}

// Remove alerta antigo
function limparAlerta() {
    const alerta = document.getElementById('alerta-cep');
    if (alerta) alerta.remove();
}

// Adiciona máscara ao digitar
document.addEventListener('DOMContentLoaded', function() {
    const inputCep = document.getElementById('cep');
    inputCep.addEventListener('input', function() {
        this.value = mascaraCEP(this.value);
    });
});
