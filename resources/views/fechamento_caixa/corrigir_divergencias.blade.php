@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ===================== TÍTULO E CONTEXTO ===================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Correção de Divergências de Caixa #{{ $caixa->id }}</h2>
            <small class="text-muted">
                Caixa #{{ $caixa->id }} • Operador: {{ $caixa->usuario->name ?? '-' }}
            </small>
        </div>

        <a href="{{ route('fechamento.lista') }}" class="btn btn-outline-secondary">
            ← Voltar
        </a>
    </div>

    {{-- ===================== BLOCO 1: RESUMO EXECUTIVO ===================== --}}
    <div class="card shadow-sm mb-4 border-left border-primary">
        <div class="card-header bg-primary text-white font-weight-bold">
            Resumo do Caixa
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2">
                    <small class="text-muted">Status</small>
                    <div class="font-weight-bold">{{ ucfirst($caixa->status) }}</div>
                </div>

                <div class="col-md-2">
                    <small class="text-muted">Fundo de Troco</small>
                    <div class="font-weight-bold">
                        R$ {{ number_format($caixa->fundo_troco,2,',','.') }}
                    </div>
                </div>

                <div class="col-md-2">
                    <small class="text-muted">Valor Informado</small>
                    <div class="font-weight-bold text-info">
                        R$ {{ number_format($total_entradas,2,',','.') }}
                    </div>
                </div>

                <div class="col-md-2">
                    <small class="text-muted">Valor Sistema</small>
                    <div class="font-weight-bold text-success">
                        R$ {{ number_format($totalGeralSistema,2,',','.') }}
                    </div>
                </div>

                <div class="col-md-2">
                    <small class="text-muted">Saídas</small>
                    <div class="font-weight-bold">
                        R$ {{ number_format($total_saidas,2,',','.') }}
                    </div>
                </div>

                <div class="col-md-2">
                    <small class="text-muted">Divergência</small>
                    <div class="font-weight-bold text-danger">
                        R$ {{ number_format($totalGeralSistema - $total_entradas,2,',','.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== BLOCO 2: ORIENTAÇÃO OPERACIONAL ===================== --}}
    <div class="alert alert-warning shadow-sm">
        <strong>Atenção:</strong>
        Ajuste os valores informados para que fiquem <u>iguais ao valor do sistema</u>.
        O botão <strong>Salvar Ajustes</strong> será liberado automaticamente quando não houver divergências.
    </div>

    {{-- ===================== BLOCO 3: CORREÇÃO DE DIVERGÊNCIAS ===================== --}}
    <div class="card shadow-sm mb-4 border-left border-warning">
        <div class="card-header bg-warning font-weight-bold">
            Divergências por Forma de Pagamento
        </div>

        <div class="card-body">
            <form action="{{ route('fechamento.ajustar', $caixa->id) }}" method="POST">
                @csrf

                {{-- Cabeçalho --}}
                <div class="row font-weight-bold border-bottom pb-2 mb-3 text-muted">
                    <div class="col-2">Forma</div>
                    <div class="col-2">Sistema</div>
                    <div class="col-2">Informado</div>
                    <div class="col-2">Diferença</div>
                    <div class="col-4">Ajuste Corrigido</div>
                </div>

                {{-- Linhas --}}
                @foreach($divergencias as $forma => $dif)
                    @php
                        $valorSistema = $totaisPorForma[$forma] ?? 0;
                        $valorInformado = $valorSistema + $dif;
                    @endphp

                    <div class="row row-forma align-items-center mb-3 p-2 border rounded bg-light">

                        <div class="col-2 font-weight-bold">
                            {{ ucfirst(str_replace('_',' ', $forma)) }}
                        </div>

                        <div class="col-2">
                            <input type="hidden" class="valor-sistema" value="{{ $valorSistema }}">
                            R$ {{ number_format($valorSistema,2,',','.') }}
                        </div>

                        <div class="col-2">
                            R$ {{ number_format($valorInformado,2,',','.') }}
                        </div>

                        <div class="col-2 {{ $dif == 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                            R$ {{ number_format($dif,2,',','.') }}
                        </div>

                        <div class="col-4">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control ajuste-corrigido"
                                   name="formas[{{ $forma }}]"
                                   value="{{ $valorInformado }}">
                        </div>
                    </div>
                @endforeach

                <div id="mensagemValidacao" class="alert alert-danger mt-3">
                    Ainda existem divergências. Corrija todos os valores.
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-success px-4" id="btnSalvar" disabled>
                        ✔ Salvar Ajustes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== BLOCO 4: HISTÓRICO ===================== --}}
    <div class="card shadow-sm border-left border-secondary">
        <div class="card-header bg-light font-weight-bold">
            Histórico de Movimentações
        </div>

        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Tipo</th>
                        <th>Forma</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($caixa->movimentacoes as $mov)
                        <tr>
                            <td>{{ $mov->tipo }}</td>
                            <td>{{ $mov->forma_pagamento }}</td>
                            <td>R$ {{ number_format($mov->valor,2,',','.') }}</td>
                            <td>{{ $mov->data_movimentacao }}</td>
                            <td>{{ $mov->observacao }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

 <!-- Formata float para R$ BR -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const botao = document.getElementById('btnSalvar');
    const mensagem = document.getElementById('mensagemValidacao');

    function validarAjustes() {
        let todosIguais = true;

        document.querySelectorAll('.row-forma').forEach(row => {
            const valorSistema = Number(
                row.querySelector('.valor-sistema').value
            );

            const valorAjustado = Number(
                row.querySelector('.ajuste-corrigido').value
            );

            if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                todosIguais = false;
                return;
            }

            if (valorSistema !== valorAjustado) {
                todosIguais = false;
            }
        });

        botao.disabled = !todosIguais;

        mensagem.className = todosIguais
            ? 'alert alert-success mt-3'
            : 'alert alert-warning mt-3';

        mensagem.innerText = todosIguais
            ? 'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.'
            : 'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
    }

    document.querySelectorAll('.ajuste-corrigido').forEach(input => {
        input.addEventListener('input', validarAjustes);
    });

    validarAjustes();
});
</script>

