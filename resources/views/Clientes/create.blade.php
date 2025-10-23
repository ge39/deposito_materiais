@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Novo Cliente</h2>

    @if ($errors->any())
        <div class="alert alert-danger" id="alerta">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        <div class="row g-3">

            <!-- Dados Pessoais -->
            <div class="col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="fisica" @if(old('tipo')=='fisica') selected @endif>Pessoa Física</option>
                    <option value="juridica" @if(old('tipo')=='juridica') selected @endif>Pessoa Jurídica</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" class="form-control" value="{{ old('cpf_cnpj') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">RG / IE</label>
                <input type="text" name="rg_ie" class="form-control" value="{{ old('rg_ie') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Órgão Emissor</label>
                <input type="text" name="orgao_emissor" class="form-control" value="{{ old('orgao_emissor') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Data de Emissão</label>
                <input type="date" name="data_emissao" class="form-control" value="{{ old('data_emissao') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Data de Nascimento</label>
                <input type="date" name="data_nascimento" class="form-control" value="{{ old('data_nascimento') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Sexo</label>
                <select name="sexo" class="form-select">
                    <option value="">Selecione</option>
                    <option value="masculino" @if(old('sexo')=='masculino') selected @endif>Masculino</option>
                    <option value="feminino" @if(old('sexo')=='feminino') selected @endif>Feminino</option>
                    <option value="outro" @if(old('sexo')=='outro') selected @endif>Outro</option>
                </select>
            </div>

            <!-- Contato -->
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>

            <!-- Cep -->
            <div class="col-md-4">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" id="cep"
                    onblur="buscarCep(this, '#endereco', '#bairro', '#cidade', '#uf')"
                    class="form-control" value="{{ old('cep') }}" maxlength="9">
            </div>

            <div class="col-md-4">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="{{ old('endereco') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control" value="{{ old('numero') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" id="bairro" class="form-control" value="{{ old('bairro') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control" value="{{ old('cidade') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" id="uf" class="form-control" value="{{ old('estado') }}" maxlength="2">
            </div>

            <!-- Financeiro -->
            <div class="col-md-4">
                <label class="form-label">Limite de Crédito (R$)</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control" value="{{ old('limite_credito') }}">
            </div>

            <div class="col-md-8">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" rows="3" class="form-control">{{ old('observacoes') }}</textarea>
            </div>

            <div class="col-md-12 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ old('ativo', 1) ? 'checked' : '' }}>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

<!-- <script>
    // Máscara simples para CPF/CNPJ
    const cpfCnpj = document.querySelector('[name="cpf_cnpj"]');
    cpfCnpj?.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        if(val.length <= 11) {
            val = val.replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        }
        this.value = val;
    });

    // Alerta automático desaparecendo
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => alerta.style.display='none', 3000);
    }
</script> -->
<script src="{{ asset('js/form-masks.js') }}"></script>
<script src="{{ asset('js/cep.js') }}"></script>

@endsection
