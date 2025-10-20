document.addEventListener('DOMContentLoaded', function() {

    // ======= Máscara CPF/CNPJ =======
const cpfCnpjs = document.querySelectorAll('[name="cpf_cnpj"], #cpf');
cpfCnpjs.forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, ''); // remove tudo que não for número

        // Delimita o tamanho máximo: 14 dígitos (CNPJ)
        if (val.length > 14) {
            val = val.substring(0, 14);
        }

        if (val.length <= 11) {
            // CPF: 000.000.000-00
            val = val.replace(/(\d{3})(\d)/, "$1.$2")
                     .replace(/(\d{3})(\d)/, "$1.$2")
                     .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            // CNPJ: 00.000.000/0000-00
            val = val.replace(/^(\d{2})(\d)/, "$1.$2")
                     .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
                     .replace(/\.(\d{3})(\d)/, ".$1/$2")
                     .replace(/(\d{4})(\d{1,2})$/, "$1-$2");
        }

        this.value = val;
    });
});


    // ======= Máscara Telefone =======
    const telefones = document.querySelectorAll('[name="telefone"]');
    telefones.forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '');
            if (val.length > 10) {
                val = val.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
            } else {
                val = val.replace(/^(\d{2})(\d{4})(\d{0,4})$/, "($1) $2-$3");
            }
            this.value = val;
        });
    });

    // ======= Máscara CEP (00000-000) =======
    const ceps = document.querySelectorAll('[name="cep"], #cep');
    ceps.forEach(inputCep => {
        inputCep.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, ''); // remove não números
            val = val.substring(0, 8); // limita a 8 dígitos
            if (val.length > 5) {
                val = val.replace(/^(\d{5})(\d)/, "$1-$2");
            }
            this.value = val;
        });

        // Ao sair do campo, faz a busca automática
        inputCep.addEventListener('blur', function() {
            if (this.value.length === 9) { // só busca se formato estiver completo
                buscarCep(this, '#endereco', '#cidade', '#uf');
            }
        });
    });

});

// ======= Buscar CEP =======
function buscarCep(inputCep, enderecoId, cidadeId, ufId) {
    let cep = inputCep.value.replace(/\D/g, '');

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
                mostrarMensagemCep("CEP não encontrado!");
                inputCep.value = "";
                document.querySelector(enderecoId).value = "";
                document.querySelector(cidadeId).value = "";
                document.querySelector(ufId).value = "";
            } else {
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

// ======= Exibir mensagem de erro do CEP =======
function mostrarMensagemCep(msg) {
    let alerta = document.getElementById('alerta-cep');
    if (!alerta) {
        alerta = document.createElement('div');
        alerta.id = 'alerta-cep';
        alerta.className = 'alert alert-danger mt-2';
        document.querySelector('#cep').parentNode.appendChild(alerta);
    }
    alerta.textContent = msg;
    setTimeout(() => alerta.remove(), 5000);
}
