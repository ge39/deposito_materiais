function buscarCep(inputCep, enderecoId, cidadeId, ufId) {
    let cep = inputCep.value.replace(/\D/g, '');

    // Limpa campos se estiver vazio
    if (cep === "") {
        document.querySelector(enderecoId).value = "";
        document.querySelector(cidadeId).value = "";
        document.querySelector(ufId).value = "";
        return;
    }

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                // CEP não encontrado
                mostrarMensagemCep("CEP não encontrado!");
                
                // Limpa os campos
                inputCep.value = "";
                document.querySelector(enderecoId).value = "";
                document.querySelector(cidadeId).value = "";
                document.querySelector(ufId).value = "";
            } else {
                // Preenche campos automaticamente
                document.querySelector(enderecoId).value = data.logradouro || '';
                document.querySelector(cidadeId).value = data.localidade || '';
                document.querySelector(ufId).value = data.uf || '';
            }
        })
        .catch(error => {
            console.error("Erro ao consultar CEP:", error);
            mostrarMensagemCep("Erro ao consultar CEP!");
        });
}

// Função para exibir mensagem de CEP
function mostrarMensagemCep(msg) {
    let alerta = document.getElementById('alerta-cep');
    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id = 'alerta-cep';
        alerta.className = 'alert alert-danger mt-2';
        document.querySelector('#cep').parentNode.appendChild(alerta);
    }
    alerta.textContent = msg;

    // Desaparece após 5 segundos
    setTimeout(() => {
        alerta.remove();
    }, 5000);
}
