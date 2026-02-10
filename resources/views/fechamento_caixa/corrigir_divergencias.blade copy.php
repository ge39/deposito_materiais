@extends('layouts.app')

@section('content')
<div class="alert alert-info">
    <strong>Como corrigir as divergências:</strong>
    <ol class="mb-0">
        <li>Compare o <strong>Valor Sistema</strong> com o <strong>Valor Informado</strong>.</li>
        <li>Ajuste o campo <strong>Ajuste Corrigido</strong> até que ele fique <strong>igual ao Valor Sistema</strong>.</li>
        <li>O botão <strong>Salvar Ajustes</strong> será liberado automaticamente quando todos os valores estiverem corretos.</li>
    </ol>
</div>

<h2>Correção de Divergências - Caixa #{{ $caixa->id }}</h2>

{{-- Informações gerais --}}
<table class="table table-sm">
    <tr><th>Status:</th><td>{{ $caixa->status }}</td></tr>
    <tr><th>Operador:</th><td>{{ $caixa->usuario->name ?? '' }}</td></tr>
    <tr><th>Fundo de Troco:</th><td>R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}</td></tr>
     <tr><th>Total Valor Informado :</th><td>R$ {{ number_format($total_entradas,2,',','.') }}</td></tr>
     <!-- <tr><th>Valor Entradas Manual:</th><td>R$ {{ number_format($totalGeralSistema,2,',','.') }}</td></tr> -->
    <tr><th>Total Entrada Sistema:</th><td>R$ {{ number_format($totalGeralSistema,2,',','.') }}</td></tr>
     <tr><th>Total Saídas:</th><td>R$ {{ number_format($total_saidas,2,',','.') }}</td></tr>
    <tr><th>Divergência Total:</th><td>R$ {{ number_format($divergencia,2,',','.') }}</td></tr>
</table>


{{-- Tabela de Divergências --}}
<h3>Divergências</h3>

<form action="{{ route('fechamento.ajustar', $caixa->id) }}" method="POST">
    @csrf

    <div class="row mb-3 font-weight-bold border-bottom pb-2">
        <div class="col-2">Forma de Pagamento</div>
        <div class="col-2">Valor Sistema</div>
        <div class="col-2">Valor Informado</div>
        <div class="col-2">Diferença</div>
        <div class="col-4">Ajuste Corrigido</div>
    </div>

    @foreach($divergencias as $forma => $dif)
        @php
            $valorSistema = $totaisPorForma[$forma] ?? 0;
            $valorInformado = $valorSistema + $dif;
            $classeDif = $dif == 0 ? 'text-success' : 'text-danger';
        @endphp

        <div class="row row-forma">
            <div class="col forma-nome">
                {{ ucfirst(str_replace('_',' ', $forma)) }}
            </div>
            <div class="col valor-sistema-container">
                <input type="hidden" class="valor-sistema" value="{{ $valorSistema }}">
                R$ {{ number_format($valorSistema, 2, ',', '.') }}
            </div>
            <div class="col valor-informado">
                R$ {{ number_format($valorInformado, 2, ',', '.') }}
            </div>
            <div class="col diferenca {{ $classeDif }}">
                R$ {{ number_format($dif, 2, ',', '.') }}
            </div>
            <div class="col ajuste-corrigido-container">
                <input type="number"
                       step="0.01"
                       min="0"
                       class="form-control ajuste-corrigido"
                       name="formas[{{ $forma }}]"
                       value="{{ $valorInformado }}">
            </div>
        </div>
    @endforeach

    <div id="mensagemValidacao" class="alert alert-warning mt-3">
        Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.
    </div>

    <button type="submit" class="btn btn-primary mt-2" id="btnSalvar" disabled>
        Salvar Ajustes
    </button>
</form>


<!-- Botão fora do form -->

{{-- Histórico de Movimentações --}}
<h3>Movimentações do Caixa</h3>
<div class="row border-bottom py-2 align-items-center p-2 mb-6">
    {{-- Linhas --}}
    <div class="border rounded mb-4">

    {{-- Cabeçalho --}}
    <div class="row font-weight-bold bg-light border-bottom py-2">
        <div class="col-2">Tipo</div>
        <div class="col-2">Valor</div>
        <div class="col-3">Data</div>
        <div class="col-5">Observação</div>
    </div>

    {{-- Linhas --}}
    @foreach($caixa->movimentacoes as $mov)
        <div class="row border-bottom py-2 align-items-center">
            <div class="col-2">
                {{ ucfirst(str_replace('_',' ', $mov->tipo)) }}
            </div>

            <div class="col-2">
                R$ {{ number_format($mov->valor, 2, ',', '.') }}
            </div>

            <div class="col-3">
                {{ \Carbon\Carbon::parse($mov->data_movimentacao)->format('d/m/Y H:i') }}
            </div>

            <div class="col-5">
                {{ $mov->observacao ?? '-' }}
            </div>
        </div>
    @endforeach

</div>



 <!-- Formata float para R$ BR -->
<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {

        const botao = document.getElementById('btnSalvar');
        const mensagem = document.getElementById('mensagemValidacao');

        // Converte string BR para float
        function parseBR(valor) {
            if (!valor) return NaN;
            valor = valor.replace(/[^\d,.-]/g, '');
            if (valor.indexOf(',') !== -1) {
                valor = valor.replace(/\./g, '').replace(',', '.');
            }
            return parseFloat(valor);
        }

        // Formata float para R$ BR
        function formatBR(valor) {
            return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function validarAjustes() {
            let todosIguais = true;

            // percorre todas as linhas de forma de pagamento
            document.querySelectorAll('.row-forma').forEach(row => {
                const valorSistemaInput = row.querySelector('.valor-sistema');
                const ajusteInput = row.querySelector('.ajuste-corrigido');

                const valorSistema = parseFloat(valorSistemaInput.value);
                const valorAjustado = parseBR(ajusteInput.value);

                if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                    todosIguais = false;
                    return;
                }

                if (Math.abs(valorSistema - valorAjustado) > 0.009) {
                    todosIguais = false;
                }
            });

            botao.disabled = !todosIguais;

            if (todosIguais) {
                mensagem.classList.remove('alert-warning');
                mensagem.classList.add('alert-success');
                mensagem.innerText =
                    'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.';
            } else {
                mensagem.classList.remove('alert-success');
                mensagem.classList.add('alert-warning');
                mensagem.innerText =
                    'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
            }
        }

        // Formata campos e adiciona validação em tempo real
        document.querySelectorAll('.ajuste-corrigido').forEach(input => {

            input.addEventListener('input', validarAjustes);

            input.addEventListener('focus', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) input.value = valor.toFixed(2).replace('.', ',');
            });

            input.addEventListener('blur', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) input.value = formatBR(valor);
            });

        });

        // Valida tudo na inicialização
        validarAjustes();
    });

</script> -->

<!-- validação campo moeda -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('btnSalvar');
    const mensagem = document.getElementById('mensagemValidacao');

    function parseBR(valor) {
        if (!valor) return NaN;
        valor = valor.replace(/[^\d,.-]/g, '');
        if (valor.indexOf(',') !== -1) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(valor);
    }

    function formatBR(valor) {
        return valor.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function validarAjustes() {
        let todosIguais = true;
        document.querySelectorAll('.row-forma').forEach(row => {
            const input = row.querySelector('.ajuste-corrigido');
            const valorSistema = parseBR(row.querySelector('.valor-sistema').value);
            const valorAjustado = parseBR(input.value);

            if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                todosIguais = false;
                return;
            }

            if (Math.abs(valorSistema - valorAjustado) > 0.009) {
                todosIguais = false;
            }
        });

        botao.disabled = !todosIguais;

        if (todosIguais) {
            mensagem.classList.remove('alert-warning');
            mensagem.classList.add('alert-success');
            mensagem.innerText = 'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.';
        } else {
            mensagem.classList.remove('alert-success');
            mensagem.classList.add('alert-warning');
            mensagem.innerText = 'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
        }
    }

    document.querySelectorAll('.ajuste-corrigido').forEach(input => {
        input.addEventListener('input', validarAjustes);

        input.addEventListener('focus', function () {
            const valor = parseBR(input.value);
            if (!isNaN(valor)) input.value = valor.toFixed(2).replace('.', ',');
        });

        input.addEventListener('blur', function () {
            const valor = parseBR(input.value);
            if (!isNaN(valor)) input.value = formatBR(valor);
        });
    });

    validarAjustes();
    });

</script>









@endsection


