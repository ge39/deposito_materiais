@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Novo Cliente</h2>

    @if ($errors->any())
        <div class="alert alert-danger" id="alerta">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('clientes.store') }}"
        method="POST"
        id="formCliente"
        novalidate
    >
        @csrf

        <div class="row g-3">

            <!-- Dados pessoais -->
            <div class="col-md-4">
                <label for="nome" class="form-label">
                    Nome
                </label>

                <input
                    type="text"
                    name="nome"
                    id="nome"
                    class="form-control"
                    value="{{ old('nome') }}"
                    required
                >

                @error('nome')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="tipo" class="form-label">
                    Tipo
                </label>

                <select
                    name="tipo"
                    id="tipo"
                    class="form-select"
                    required
                >
                    <option
                        value="fisica"
                        {{ old('tipo', 'fisica') === 'fisica' ? 'selected' : '' }}
                    >
                        Pessoa Física
                    </option>

                    <option
                        value="juridica"
                        {{ old('tipo') === 'juridica' ? 'selected' : '' }}
                    >
                        Pessoa Jurídica
                    </option>
                </select>

                @error('tipo')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label
                    for="tipo_cliente"
                    class="form-label fw-bold"
                >
                    Perfil de Preço / Tabela de Markup
                </label>

                <select
                    name="tipo_cliente"
                    id="tipo_cliente"
                    class="form-select"
                >
                    <option
                        value="markup_1"
                        {{ old('tipo_cliente', 'markup_1') === 'markup_1' ? 'selected' : '' }}
                    >
                        Varejo (Markup 1 - Padrão)
                    </option>

                    <option
                        value="markup_2"
                        {{ old('tipo_cliente') === 'markup_2' ? 'selected' : '' }}
                    >
                        Empresa / Empreiteiro (Markup 2)
                    </option>

                    <option
                        value="markup_3"
                        {{ old('tipo_cliente') === 'markup_3' ? 'selected' : '' }}
                    >
                        Atacado (Markup 3)
                    </option>
                </select>

                <div
                    class="mt-2 p-2 rounded border bg-light text-muted small"
                    id="box_explicativo_perfil"
                    style="min-height: 50px; line-height: 1.4;"
                ></div>

                @error('tipo_cliente')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Documentos -->
            <div class="col-md-4">
                <label
                    for="cpf_cnpj"
                    class="form-label"
                    id="cpfCnpjLabel"
                >
                    CPF
                </label>

                <input
                    type="text"
                    name="cpf_cnpj"
                    id="cpf_cnpj"
                    class="form-control"
                    value="{{ old('cpf_cnpj') }}"
                    inputmode="numeric"
                    autocomplete="off"
                    required
                >

                <div
                    class="form-text"
                    id="cpfCnpjAjuda"
                >
                    Informe os 11 números do CPF.
                </div>

                <div
                    class="invalid-feedback"
                    id="cpfCnpjFeedback"
                >
                    Documento inválido.
                </div>

                @error('cpf_cnpj')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label
                    for="rg_ie"
                    class="form-label"
                    id="rgIeLabel"
                >
                    RG
                </label>

                <input
                    type="text"
                    name="rg_ie"
                    id="rg_ie"
                    class="form-control"
                    value="{{ old('rg_ie') }}"
                    maxlength="20"
                    autocomplete="off"
                >

                <div
                    class="form-text"
                    id="rgIeAjuda"
                >
                    Informe o RG com o dígito verificador, quando existir.
                </div>

                <div
                    class="invalid-feedback"
                    id="rgIeFeedback"
                >
                    Documento inválido.
                </div>

                @error('rg_ie')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="orgao_emissor" class="form-label">
                    Órgão Emissor
                </label>

                <input
                    type="text"
                    name="orgao_emissor"
                    id="orgao_emissor"
                    class="form-control text-uppercase"
                    value="{{ old('orgao_emissor') }}"
                    maxlength="20"
                >

                @error('orgao_emissor')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="data_emissao" class="form-label">
                    Data de Emissão
                </label>

                <input
                    type="date"
                    name="data_emissao"
                    id="data_emissao"
                    class="form-control"
                    value="{{ old('data_emissao') }}"
                >

                @error('data_emissao')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="data_nascimento" class="form-label">
                    Data de Nascimento
                </label>

                <input
                    type="date"
                    name="data_nascimento"
                    id="data_nascimento"
                    class="form-control"
                    value="{{ old('data_nascimento') }}"
                >

                @error('data_nascimento')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="sexo" class="form-label">
                    Sexo
                </label>

                <select
                    name="sexo"
                    id="sexo"
                    class="form-select"
                >
                    <option value="">Selecione</option>

                    <option
                        value="masculino"
                        {{ old('sexo') === 'masculino' ? 'selected' : '' }}
                    >
                        Masculino
                    </option>

                    <option
                        value="feminino"
                        {{ old('sexo') === 'feminino' ? 'selected' : '' }}
                    >
                        Feminino
                    </option>

                    <option
                        value="outro"
                        {{ old('sexo') === 'outro' ? 'selected' : '' }}
                    >
                        Outro
                    </option>
                </select>

                @error('sexo')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Contato -->
            <div class="col-md-4">
                <label for="telefone" class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    id="telefone"
                    class="form-control"
                    value="{{ old('telefone') }}"
                    autocomplete="tel"
                >

                @error('telefone')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    autocomplete="email"
                >

                @error('email')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Endereço -->
            <div class="col-md-4">
                <label for="cep" class="form-label">
                    CEP
                </label>

                <div class="input-group">
                    <span class="input-group-text text-primary">
                        <i class="bi bi-geo-alt"></i>
                    </span>

                    <input
                        type="text"
                        name="cep"
                        id="cep"
                        class="form-control"
                        value="{{ old('cep') }}"
                        placeholder="00000-000"
                        maxlength="9"
                        inputmode="numeric"
                        autocomplete="postal-code"
                    >

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        id="btnBuscarCep"
                        onclick="consultarCepCliente()"
                    >
                        <i class="bi bi-search me-1"></i>
                        Buscar
                    </button>
                </div>

                <div class="form-text">
                    Digite os oito números do CEP e clique em Buscar.
                </div>

                @error('cep')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="endereco" class="form-label">
                    Endereço
                </label>

                <input
                    type="text"
                    name="endereco"
                    id="endereco"
                    class="form-control"
                    value="{{ old('endereco') }}"
                    autocomplete="address-line1"
                >

                @error('endereco')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="numero" class="form-label">
                    Número
                </label>

                <input
                    type="text"
                    name="numero"
                    id="numero"
                    class="form-control"
                    value="{{ old('numero') }}"
                    autocomplete="address-line2"
                >

                @error('numero')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="bairro" class="form-label">
                    Bairro
                </label>

                <input
                    type="text"
                    name="bairro"
                    id="bairro"
                    class="form-control"
                    value="{{ old('bairro') }}"
                >

                @error('bairro')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="cidade" class="form-label">
                    Cidade
                </label>

                <input
                    type="text"
                    name="cidade"
                    id="cidade"
                    class="form-control"
                    value="{{ old('cidade') }}"
                    autocomplete="address-level2"
                >

                @error('cidade')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="uf" class="form-label">
                    Estado
                </label>

                <input
                    type="text"
                    name="estado"
                    id="uf"
                    class="form-control text-uppercase"
                    value="{{ old('estado') }}"
                    maxlength="2"
                    autocomplete="address-level1"
                >

                @error('estado')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Financeiro -->
            <div class="col-md-4">
                <label for="limite_credito" class="form-label">
                    Limite de Crédito (R$)
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="limite_credito"
                    id="limite_credito"
                    class="form-control"
                    value="{{ old('limite_credito') }}"
                >

                @error('limite_credito')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="observacoes" class="form-label">
                    Observações
                </label>

                <textarea
                    name="observacoes"
                    id="observacoes"
                    rows="3"
                    class="form-control"
                >{{ old('observacoes') }}</textarea>

                @error('observacoes')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-12 mt-2">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="ativo"
                        id="ativo"
                        class="form-check-input"
                        value="1"
                        {{ old('ativo', 1) ? 'checked' : '' }}
                    >

                    <label for="ativo" class="form-check-label">
                        Ativo
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button
                type="submit"
                class="btn btn-success"
                id="btnSalvarCliente"
            >
                <i class="bi bi-check-circle me-1"></i>
                Salvar
            </button>

            <a
                href="{{ route('clientes.index') }}"
                class="btn btn-secondary"
            >
                <i class="bi bi-arrow-left-circle me-1"></i>
                Voltar
            </a>
        </div>
    </form>
</div>

<script>
    /*
    |--------------------------------------------------------------------------
    | BUSCA DE CEP
    |--------------------------------------------------------------------------
    */

    window.consultarCepCliente = function () {
        const cepInput = document.getElementById('cep');
        const cepNumerico = String(cepInput?.value || '')
            .replace(/\D/g, '');

        if (!cepInput) {
            return;
        }

        cepInput.setCustomValidity('');
        cepInput.classList.remove('is-invalid');

        if (cepNumerico.length !== 8) {
            cepInput.setCustomValidity(
                'O CEP deve possuir oito números.'
            );

            cepInput.classList.add('is-invalid');
            cepInput.reportValidity();
            cepInput.focus();

            return;
        }

        if (typeof window.buscarCep !== 'function') {
            cepInput.setCustomValidity(
                'O serviço de busca de CEP não está disponível.'
            );

            cepInput.classList.add('is-invalid');
            cepInput.reportValidity();

            return;
        }

        window.buscarCep(
            cepInput,
            '#endereco',
            '#bairro',
            '#cidade',
            '#uf'
        );
    };

    /*
    |--------------------------------------------------------------------------
    | PERFIL DE PREÇO
    |--------------------------------------------------------------------------
    */

    function atualizarDicaPerfil() {
        const select = document.getElementById('tipo_cliente');
        const box = document.getElementById('box_explicativo_perfil');

        if (!select || !box) {
            return;
        }

        const descricoes = {
            markup_1:
                '🛒 <strong>Varejo (Novo):</strong> Aplica a margem padrão (Markup 1) e limite de desconto 1. Ideal para consumidores finais esporádicos.',

            markup_2:
                '🏗️ <strong>Empresa / Empreiteiro:</strong> Preços diferenciados (Markup 2) para construtoras, empreiteiros e prestadores de serviço parceiros.',

            markup_3:
                '📦 <strong>Atacado:</strong> Margem mínima de lucro (Markup 3) para grandes volumes de compra ou faturamento corporativo estrito.'
        };

        box.innerHTML =
            descricoes[select.value]
            || '💡 Selecione um perfil para visualizar as regras de preço.';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const formulario = document.getElementById('formCliente');
        const tipoPessoa = document.getElementById('tipo');
        const tipoCliente = document.getElementById('tipo_cliente');

        const cpfCnpj = document.getElementById('cpf_cnpj');
        const cpfCnpjLabel = document.getElementById('cpfCnpjLabel');
        const cpfCnpjAjuda = document.getElementById('cpfCnpjAjuda');
        const cpfCnpjFeedback = document.getElementById(
            'cpfCnpjFeedback'
        );

        const rgIe = document.getElementById('rg_ie');
        const rgIeLabel = document.getElementById('rgIeLabel');
        const rgIeAjuda = document.getElementById('rgIeAjuda');
        const rgIeFeedback = document.getElementById('rgIeFeedback');

        const orgaoEmissor = document.getElementById('orgao_emissor');
        const cepInput = document.getElementById('cep');
        const alerta = document.getElementById('alerta');

        /*
        |--------------------------------------------------------------------------
        | FUNÇÕES AUXILIARES
        |--------------------------------------------------------------------------
        */

        function somenteNumeros(valor) {
            return String(valor || '').replace(/\D/g, '');
        }

        function definirErro(campo, feedback, mensagem) {
            campo.setCustomValidity(mensagem);
            campo.classList.remove('is-valid');
            campo.classList.add('is-invalid');

            if (feedback) {
                feedback.textContent = mensagem;
            }
        }

        function limparErro(campo) {
            campo.setCustomValidity('');
            campo.classList.remove('is-invalid');
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO DE CPF
        |--------------------------------------------------------------------------
        */

        function validarCpf(cpf) {
            cpf = somenteNumeros(cpf);

            if (cpf.length !== 11) {
                return false;
            }

            if (/^(\d)\1{10}$/.test(cpf)) {
                return false;
            }

            let soma = 0;

            for (let i = 0; i < 9; i++) {
                soma += Number(cpf.charAt(i)) * (10 - i);
            }

            let primeiroDigito = 11 - (soma % 11);

            if (primeiroDigito >= 10) {
                primeiroDigito = 0;
            }

            if (primeiroDigito !== Number(cpf.charAt(9))) {
                return false;
            }

            soma = 0;

            for (let i = 0; i < 10; i++) {
                soma += Number(cpf.charAt(i)) * (11 - i);
            }

            let segundoDigito = 11 - (soma % 11);

            if (segundoDigito >= 10) {
                segundoDigito = 0;
            }

            return segundoDigito === Number(cpf.charAt(10));
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO DE CNPJ
        |--------------------------------------------------------------------------
        */

        function validarCnpj(cnpj) {
            cnpj = somenteNumeros(cnpj);

            if (cnpj.length !== 14) {
                return false;
            }

            if (/^(\d)\1{13}$/.test(cnpj)) {
                return false;
            }

            function calcularDigito(base, pesos) {
                let soma = 0;

                for (let i = 0; i < pesos.length; i++) {
                    soma += Number(base.charAt(i)) * pesos[i];
                }

                const resto = soma % 11;

                return resto < 2
                    ? 0
                    : 11 - resto;
            }

            const primeiroDigito = calcularDigito(
                cnpj.substring(0, 12),
                [
                    5, 4, 3, 2,
                    9, 8, 7, 6,
                    5, 4, 3, 2
                ]
            );

            if (primeiroDigito !== Number(cnpj.charAt(12))) {
                return false;
            }

            const segundoDigito = calcularDigito(
                cnpj.substring(0, 12) + primeiroDigito,
                [
                    6, 5, 4, 3, 2,
                    9, 8, 7, 6,
                    5, 4, 3, 2
                ]
            );

            return segundoDigito === Number(cnpj.charAt(13));
        }

        /*
        |--------------------------------------------------------------------------
        | MÁSCARAS
        |--------------------------------------------------------------------------
        */

        function aplicarMascaraCpf(valor) {
            return somenteNumeros(valor)
                .slice(0, 11)
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(
                    /^(\d{3})\.(\d{3})(\d)/,
                    '$1.$2.$3'
                )
                .replace(
                    /^(\d{3})\.(\d{3})\.(\d{3})(\d)/,
                    '$1.$2.$3-$4'
                );
        }

        function aplicarMascaraCnpj(valor) {
            return somenteNumeros(valor)
                .slice(0, 14)
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(
                    /^(\d{2})\.(\d{3})(\d)/,
                    '$1.$2.$3'
                )
                .replace(
                    /^(\d{2})\.(\d{3})\.(\d{3})(\d)/,
                    '$1.$2.$3/$4'
                )
                .replace(
                    /^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/,
                    '$1.$2.$3/$4-$5'
                );
        }

        function aplicarMascaraDocumento() {
            if (!cpfCnpj) {
                return;
            }

            const pessoaJuridica =
                tipoPessoa?.value === 'juridica';

            cpfCnpj.value = pessoaJuridica
                ? aplicarMascaraCnpj(cpfCnpj.value)
                : aplicarMascaraCpf(cpfCnpj.value);

            cpfCnpj.maxLength = pessoaJuridica
                ? 18
                : 14;
        }

        /*
        |--------------------------------------------------------------------------
        | CONFIGURAÇÃO DOS DOCUMENTOS
        |--------------------------------------------------------------------------
        */

        function configurarCamposDocumentos() {
            const pessoaJuridica =
                tipoPessoa?.value === 'juridica';

            if (cpfCnpjLabel) {
                cpfCnpjLabel.textContent = pessoaJuridica
                    ? 'CNPJ'
                    : 'CPF';
            }

            if (cpfCnpjAjuda) {
                cpfCnpjAjuda.textContent = pessoaJuridica
                    ? 'Informe os 14 números do CNPJ.'
                    : 'Informe os 11 números do CPF.';
            }

            if (cpfCnpj) {
                cpfCnpj.placeholder = pessoaJuridica
                    ? '00.000.000/0000-00'
                    : '000.000.000-00';

                limparErro(cpfCnpj);
                cpfCnpj.classList.remove('is-valid');

                aplicarMascaraDocumento();
            }

            if (rgIeLabel) {
                rgIeLabel.textContent = pessoaJuridica
                    ? 'Inscrição Estadual'
                    : 'RG';
            }

            if (rgIeAjuda) {
                rgIeAjuda.textContent = pessoaJuridica
                    ? 'O formato da Inscrição Estadual varia conforme a UF.'
                    : 'Informe o RG com o dígito verificador, quando existir.';
            }

            if (rgIe) {
                rgIe.placeholder = pessoaJuridica
                    ? 'Inscrição Estadual'
                    : '00.000.000-0';

                limparErro(rgIe);
                rgIe.classList.remove('is-valid');
            }

            if (orgaoEmissor) {
                orgaoEmissor.placeholder = pessoaJuridica
                    ? 'Órgão de registro, se aplicável'
                    : 'Ex.: SSP/SP';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO DE CPF/CNPJ
        |--------------------------------------------------------------------------
        */

        function validarDocumento() {
            const pessoaJuridica =
                tipoPessoa?.value === 'juridica';

            const documento = somenteNumeros(
                cpfCnpj?.value
            );

            limparErro(cpfCnpj);
            cpfCnpj.classList.remove('is-valid');

            if (documento === '') {
                definirErro(
                    cpfCnpj,
                    cpfCnpjFeedback,
                    pessoaJuridica
                        ? 'Informe o CNPJ.'
                        : 'Informe o CPF.'
                );

                return false;
            }

            if (pessoaJuridica) {
                if (documento.length !== 14) {
                    definirErro(
                        cpfCnpj,
                        cpfCnpjFeedback,
                        'O CNPJ deve possuir 14 números.'
                    );

                    return false;
                }

                if (!validarCnpj(documento)) {
                    definirErro(
                        cpfCnpj,
                        cpfCnpjFeedback,
                        'O CNPJ informado é inválido.'
                    );

                    return false;
                }
            } else {
                if (documento.length !== 11) {
                    definirErro(
                        cpfCnpj,
                        cpfCnpjFeedback,
                        'O CPF deve possuir 11 números.'
                    );

                    return false;
                }

                if (!validarCpf(documento)) {
                    definirErro(
                        cpfCnpj,
                        cpfCnpjFeedback,
                        'O CPF informado é inválido.'
                    );

                    return false;
                }
            }

            cpfCnpj.classList.add('is-valid');

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO DE RG/IE
        |--------------------------------------------------------------------------
        */

        function normalizarRgIe(valor) {
            return String(valor || '')
                .toUpperCase()
                .replace(/[^0-9A-Z.\-\/]/g, '')
                .slice(0, 20);
        }

        function validarRgIe() {
            if (!rgIe) {
                return true;
            }

            const pessoaJuridica =
                tipoPessoa?.value === 'juridica';

            const valor = rgIe.value.trim();

            limparErro(rgIe);
            rgIe.classList.remove('is-valid');

            /*
             * Campo opcional conforme o formulário original.
             */
            if (valor === '') {
                return true;
            }

            const valorSemFormatacao = valor.replace(
                /[^0-9A-Z]/g,
                ''
            );

            if (valorSemFormatacao.length < 5) {
                definirErro(
                    rgIe,
                    rgIeFeedback,
                    pessoaJuridica
                        ? 'A Inscrição Estadual informada está incompleta.'
                        : 'O RG informado está incompleto.'
                );

                return false;
            }

            if (/^([0-9A-Z])\1+$/.test(valorSemFormatacao)) {
                definirErro(
                    rgIe,
                    rgIeFeedback,
                    pessoaJuridica
                        ? 'A Inscrição Estadual informada é inválida.'
                        : 'O RG informado é inválido.'
                );

                return false;
            }

            rgIe.classList.add('is-valid');

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | EVENTOS
        |--------------------------------------------------------------------------
        */

        tipoCliente?.addEventListener(
            'change',
            atualizarDicaPerfil
        );

        tipoPessoa?.addEventListener('change', function () {
            configurarCamposDocumentos();
        });

        cpfCnpj?.addEventListener('input', function () {
            aplicarMascaraDocumento();
            limparErro(cpfCnpj);
            cpfCnpj.classList.remove('is-valid');
        });

        cpfCnpj?.addEventListener('blur', function () {
            validarDocumento();
        });

        rgIe?.addEventListener('input', function () {
            rgIe.value = normalizarRgIe(rgIe.value);
            limparErro(rgIe);
            rgIe.classList.remove('is-valid');
        });

        rgIe?.addEventListener('blur', function () {
            validarRgIe();
        });

        orgaoEmissor?.addEventListener('input', function () {
            orgaoEmissor.value =
                orgaoEmissor.value.toUpperCase();
        });

        cepInput?.addEventListener('input', function () {
            let cep = somenteNumeros(cepInput.value)
                .slice(0, 8);

            if (cep.length > 5) {
                cep = cep.replace(
                    /^(\d{5})(\d)/,
                    '$1-$2'
                );
            }

            cepInput.value = cep;
            limparErro(cepInput);
        });

        cepInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                consultarCepCliente();
            }
        });

        formulario?.addEventListener('submit', function (event) {
            const cpfCnpjValido = validarDocumento();
            const rgIeValido = validarRgIe();

            if (!cpfCnpjValido || !rgIeValido) {
                event.preventDefault();
                event.stopPropagation();

                const primeiroCampoInvalido =
                    formulario.querySelector(':invalid');

                primeiroCampoInvalido?.focus();
                primeiroCampoInvalido?.reportValidity();

                return;
            }

            if (!formulario.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                const primeiroCampoInvalido =
                    formulario.querySelector(':invalid');

                primeiroCampoInvalido?.focus();
                primeiroCampoInvalido?.reportValidity();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | INICIALIZAÇÃO
        |--------------------------------------------------------------------------
        */

        atualizarDicaPerfil();
        configurarCamposDocumentos();

        if (cpfCnpj?.value) {
            aplicarMascaraDocumento();
            validarDocumento();
        }

        if (rgIe?.value) {
            rgIe.value = normalizarRgIe(rgIe.value);
            validarRgIe();
        }

        if (alerta) {
            setTimeout(function () {
                alerta.style.display = 'none';
            }, 3000);
        }
    });
</script>

<script src="{{ asset('js/form-masks.js') }}"></script>
<script src="{{ asset('js/cep.js') }}"></script>
@endsection