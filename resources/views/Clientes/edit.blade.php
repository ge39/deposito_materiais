@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Cliente: {{ $cliente->nome }}</h2>

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

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Dados Pessoais -->
        <h4 class="mb-3">Dados Pessoais</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ $cliente->nome }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="fisica" @if($cliente->tipo == 'fisica') selected @endif>Pessoa Física</option>
                    <option value="juridica" @if($cliente->tipo == 'juridica') selected @endif>Pessoa Jurídica</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Nascimento</label>
                <input type="date" name="data_nascimento" class="form-control" value="{{ $cliente->data_nascimento?->format('Y-m-d') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Sexo</label>
                <select name="sexo" class="form-select">
                    <option value="masculino" @if($cliente->sexo == 'masculino') selected @endif>Masculino</option>
                    <option value="feminino" @if($cliente->sexo == 'feminino') selected @endif>Feminino</option>
                    <option value="outro" @if($cliente->sexo == 'outro') selected @endif>Outro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="{{ $cliente->telefone }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="{{ $cliente->email }}">
            </div>
        </div>

        <!-- Endereço -->
        <h4 class="mb-3">Endereço</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control" value="{{ $cliente->cep }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" class="form-control" value="{{ $cliente->endereco }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control" value="{{ $cliente->numero }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" class="form-control" value="{{ $cliente->bairro }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control" value="{{ $cliente->cidade }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control" value="{{ $cliente->estado }}">
            </div>
        </div>

        <!-- Dados Documentais -->
        <h4 class="mb-3">Documentos</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" class="form-control" value="{{ $cliente->cpf_cnpj }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">RG/Inscrição Estadual</label>
                <input type="text" name="rg_ie" class="form-control" value="{{ $cliente->rg_ie }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Órgão Emissor</label>
                <input type="text" name="orgao_emissor" class="form-control" value="{{ $cliente->orgao_emissor }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Data de Emissão</label>
                <input type="date" name="data_emissao" class="form-control" value="{{ $cliente->data_emissao?->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Limite de Crédito (R$)</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control" value="{{ $cliente->limite_credito }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" rows="1" class="form-control">{{ $cliente->observacoes }}</textarea>
            </div>
        </div>
        <!-- Ativo -->
            <div class="col-md-2 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ $cliente->ativo ? 'checked' : '' }}>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
    // Alerta automático desaparecendo
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => {
            alerta.style.display = 'none';
        }, 5000);
    }
</script>
@endsection
