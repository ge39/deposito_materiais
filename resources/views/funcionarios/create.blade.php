@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Funcionário</h2>

    @if(session('success'))
        <div class="alert alert-success" id="alerta">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" id="alerta">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('funcionarios.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text"
                       name="cpf"
                       id="cpf"
                       class="form-control"
                       value="{{ old('cpf') }}"
                       maxlength="14"
                       inputmode="numeric"
                       required>
            </div>

            <div class="col-md-8">
                <label for="nome" class="form-label">Nome</label>
                <input type="text"
                       name="nome"
                       id="nome"
                       class="form-control"
                       value="{{ old('nome') }}"
                       maxlength="255"
                       required>
            </div>

            <div class="col-md-4">
                <label for="funcao" class="form-label">Função</label>
                <select name="funcao"
                        id="funcao"
                        class="form-select"
                        required>
                    @foreach(\App\Models\Funcionario::FUNCOES_LABELS as $valor => $rotulo)
                        <option value="{{ $valor }}"
                                @selected(old('funcao', 'vendedor') === $valor)>
                            {{ $rotulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text"
                       name="telefone"
                       id="telefone"
                       class="form-control"
                       value="{{ old('telefone') }}"
                       maxlength="50">
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       maxlength="100">
            </div>

            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text"
                       name="cep"
                       id="cep"
                       class="form-control"
                       value="{{ old('cep') }}"
                       maxlength="12"
                       onblur="if (typeof buscarCep === 'function') buscarCep()">
            </div>

            <div class="col-md-5">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text"
                       name="endereco"
                       id="endereco"
                       class="form-control"
                       value="{{ old('endereco') }}"
                       maxlength="255">
            </div>

            <div class="col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="text"
                       name="numero"
                       id="numero"
                       class="form-control"
                       value="{{ old('numero') }}"
                       maxlength="12">
            </div>

            <div class="col-md-4">
                <label for="bairro" class="form-label">Bairro</label>
                <input type="text"
                       name="bairro"
                       id="bairro"
                       class="form-control"
                       value="{{ old('bairro') }}"
                       maxlength="255">
            </div>

            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text"
                       name="cidade"
                       id="cidade"
                       class="form-control"
                       value="{{ old('cidade') }}"
                       maxlength="255">
            </div>

            <div class="col-md-2">
                <label for="estado" class="form-label">Estado</label>
                <input type="text"
                       name="estado"
                       id="estado"
                       class="form-control text-uppercase"
                       value="{{ old('estado') }}"
                       maxlength="2">
            </div>

            <div class="col-md-12">
                <label for="observacoes" class="form-label">
                    Observações
                </label>
                <textarea name="observacoes"
                          id="observacoes"
                          class="form-control"
                          rows="3"
                          maxlength="250">{{ old('observacoes') }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="data_admissao" class="form-label">
                    Data de Admissão
                </label>
                <input type="date"
                       name="data_admissao"
                       id="data_admissao"
                       class="form-control"
                       value="{{ old('data_admissao') }}">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox"
                           name="ativo"
                           id="ativo"
                           class="form-check-input"
                           value="1"
                           @checked(old('ativo', 1))>
                    <label for="ativo" class="form-check-label">Ativo</label>
                </div>
            </div>

            <div class="col-12"
                 id="opcao-rastreamento"
                 @if(old('funcao', 'vendedor') !== 'motorista') hidden @endif>
                <div class="border rounded bg-light p-3">
                    <div class="form-check">
                        <input type="checkbox"
                               name="rastreamento_habilitado"
                               id="rastreamento_habilitado"
                               class="form-check-input"
                               value="1"
                               @checked(old('rastreamento_habilitado'))>
                        <label for="rastreamento_habilitado"
                               class="form-check-label fw-semibold">
                            Permitir compartilhamento da localização durante viagens ativas
                        </label>
                    </div>
                    <div class="form-text mt-2">
                        Esta opção apenas habilita o recurso para o motorista.
                        O compartilhamento começará somente após a autorização
                        no celular e o início de uma viagem ativa.
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('funcionarios.index') }}"
               class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const campoFuncao = document.getElementById('funcao');
        const blocoRastreamento = document.getElementById(
            'opcao-rastreamento'
        );
        const campoRastreamento = document.getElementById(
            'rastreamento_habilitado'
        );
        const campoCpf = document.getElementById('cpf');
        const alerta = document.getElementById('alerta');

        function atualizarOpcaoRastreamento() {
            const motorista = campoFuncao.value === 'motorista';

            blocoRastreamento.hidden = ! motorista;
            campoRastreamento.disabled = ! motorista;

            if (! motorista) {
                campoRastreamento.checked = false;
            }
        }

        campoFuncao.addEventListener(
            'change',
            atualizarOpcaoRastreamento
        );

        campoCpf.addEventListener('input', function () {
            let cpf = this.value.replace(/\D/g, '').slice(0, 11);
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = cpf;
        });

        if (alerta) {
            window.setTimeout(function () {
                alerta.style.display = 'none';
            }, 5000);
        }

        atualizarOpcaoRastreamento();
    });
</script>
@endsection