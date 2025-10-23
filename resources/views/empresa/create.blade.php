@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Nova Empresa / Filial</h2>

    <div class="card shadow-sm border rounded-2 p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('empresa.store') }}" method="POST" id="empresaForm">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                </div>

                <div class="col-md-6">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" class="form-control" value="{{ old('cnpj') }}" maxlength="18">
                </div>

                <div class="col-md-6">
                    <label>Inscrição Estadual</label>
                    <input type="text" name="inscricao_estadual" id="inscricao_estadual" class="form-control" value="{{ old('inscricao_estadual') }}" maxlength="14">
                </div>

                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}">
                </div>

                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

                <div class="col-md-6">
                    <label>Site</label>
                    <input type="text" name="site" class="form-control" value="{{ old('site') }}">
                </div>

                <div class="col-md-4">
                    <label>CEP</label>
                    <input type="text" name="cep" id="cep"
                        onblur="buscarCep(this, '#endereco', '#bairro', '#cidade', '#uf')"
                        class="form-control" value="{{ old('cep') }}" maxlength="9">
                </div>

                <div class="col-md-6">
                    <label>Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="{{ old('endereco') }}" id="endereco">
                </div>

                <div class="col-md-2">
                    <label>Número</label>
                    <input type="text" name="numero" class="form-control" value="{{ old('numero') }}">
                </div>

                <div class="col-md-4">
                    <label>Complemento</label>
                    <input type="text" name="complemento" class="form-control" value="{{ old('complemento') }}">
                </div>

                <div class="col-md-4">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="{{ old('bairro') }}" id="bairro">
                </div>

                <div class="col-md-4">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="{{ old('cidade') }}" id="cidade">
                </div>

                <div class="col-md-2">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" class="form-control" value="{{ old('estado') }}" id="estado">
                </div>

                <div class="col-md-12 form-check mt-2">
                    <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ old('ativo', 1) ? 'checked' : '' }}>
                    <label for="ativo" class="form-check-label">Ativo</label>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</div>

{{-- Scripts --}}
<script>
document.getElementById('cep').addEventListener('blur', function () {
    let cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`/buscar-cep?cep=${cep}`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('endereco').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('estado').value = data.uf || '';
                }
            });
    }
});

// Máscara para CNPJ: 99.999.999/9999-99
document.getElementById('cnpj').addEventListener('input', function(e) {
    let v = this.value.replace(/\D/g,'');
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    this.value = v;
});

// Máscara para Inscrição Estadual: permite apenas números
document.getElementById('inscricao_estadual').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g,'');
});
</script>
<script src="{{ asset('js/form-masks.js') }}"></script>
<script src="{{ asset('js/cep.js') }}"></script>
@endsection
