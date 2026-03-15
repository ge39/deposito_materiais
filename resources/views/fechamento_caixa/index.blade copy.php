<style>
    .tabela-movimentacoes {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    /* Overflow controlado e texto cortado */
    .tabela-movimentacoes th,
    .tabela-movimentacoes td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 10px 8px;
    }

    /* Larguras das colunas */
    .tabela-movimentacoes th:nth-child(1),
    .tabela-movimentacoes td:nth-child(1) { width: 50px; }   /* ID */
    .tabela-movimentacoes th:nth-child(2),
    .tabela-movimentacoes td:nth-child(2) { width: 150px; }  /* Tipo */
    .tabela-movimentacoes th:nth-child(3),
    .tabela-movimentacoes td:nth-child(3) { width: 180px; }  /* Valor */
    .tabela-movimentacoes th:nth-child(4),
    .tabela-movimentacoes td:nth-child(4) { width: 100px; }   /* Origem */
    .tabela-movimentacoes th:nth-child(5),
    .tabela-movimentacoes td:nth-child(5) { width: 150px; }  /* Data */
    .tabela-movimentacoes th:nth-child(6),
    .tabela-movimentacoes td:nth-child(6) { width: auto; }   /* Observação */

    /* Zebra striping suave */
    .tabela-movimentacoes tbody tr:nth-child(odd) {
        background-color: #f9f9f9; /* linha clara */
    }

    .tabela-movimentacoes tbody tr:nth-child(even) {
        background-color: #ffffff; /* linha branca */
    }

    /* Efeito hover */
    .tabela-movimentacoes tbody tr:hover {
        background-color: #e0f3ff; /* destaque suave */
    }

    .movimentacoes-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.95rem;
    }

    .movimentacao-item:hover {
        background-color: #f8f9fa;
    }

    .movimentacoes-container .row {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
    }

    .movimentacoes-container .col-1,
    .movimentacoes-container .col-2 {
        padding: 0.5rem;
        border-right: 1px solid #dee2e6;
    }

    .movimentacoes-container .col-2:last-child,
    .movimentacoes-container .col-1:last-child {
        border-right: none;
    }

    .bg-light {
        background-color: #f1f3f5 !important;
    }

    .fw-bold {
        font-weight: 600;
    }
</style>

@extends('layouts.app')

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="container mt-4">
    <h3>Fechamento / Auditoria de Caixa #{{ $caixa->id }}</h3>

    {{-- =======================
        CARDS DE RESUMO
    ======================== --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-2 ">
                <div class="card-header fs-5 bg-primary text-white fw-bold"> Abertura:</div>
                <strong>✅ Abertura:</strong> R$ {{ number_format($caixa->valor_abertura, 2, ',', '.') }}<br>
                <strong>Fundo de Troco:</strong> R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}<br>
                <strong>Data Abertura:</strong> {{ $caixa->data_abertura->format('d/m/Y H:i') }}<br>
                <strong>Status:</strong> {{ ucfirst($caixa->status) }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <div class="card-header fs-5 bg-primary text-white fw-bold"> Total Entradas / Saidas:</div>
                <strong>✅ Total Entradas:</strong> R$ {{ number_format($total_entradas, 2, ',', '.') }}<br>
                <strong>Total Saídas:</strong> R$ {{ number_format($total_saidas, 2, ',', '.') }}<br>
                <strong>Total Sangria:</strong> <span> R$ {{ number_format($total_sangrias, 2, ',', '.') }}</span>
                <strong>Total  Esperado Dinheiro:</strong> R$ {{ number_format($caixa->fundo_troco + ($totaisPorForma['dinheiro'] ?? 0) - $total_saidas, 2, ',', '.') }}<br>
                
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <div class="card-header fs-5 bg-primary text-white fw-bold">Formas Pagamento (Sistema):</div>
                <strong>✅  Sistema</strong>
                <ul class="list-unstyled mb-0">
                    @foreach(['dinheiro','pix','carteira','cartao_debito','cartao_credito'] as $forma)
                        <li>{{ ucfirst(str_replace('_',' ',$forma)) }}: 
                            R$ {{ number_format($totaisPorForma[$forma] ?? 0, 2, ',', '.') }}
                        </li>
                    @endforeach
                </ul>
                <ul class="list-unstyled mb-0">
                     ✅ 
                       <strong>Total Sistema:</strong>
                        R$ {{ number_format($totalGeralSistema, 2, ',', '.') }}
                </ul>
            </div>
        </div>
    </div>

    {{-- =======================
        FORMULÁRIO DE FECHAMENTO
    ======================== --}}
    <!-- @if($caixa->estaAberto() && auth()->user()->can('fechar-caixa'))
    <form method="POST" action="{{ route('fechamento.fechar', $caixa->id) }}">
        @csrf
        <h5>Valores Físicos Conferidos</h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dinheiro" class="form-label">Dinheiro</label>
                <input type="text" class="form-control" name="dinheiro" id="dinheiro" 
                       value="{{ number_format($totaisPorForma['dinheiro'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-4">
                <label for="pix" class="form-label">Pix</label>
                <input type="text" class="form-control" name="pix" id="pix" 
                       value="{{ number_format($totaisPorForma['pix'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-4">
                <label for="carteira" class="form-label">Carteira</label>
                <input type="text" class="form-control" name="carteira" id="carteira" 
                       value="{{ number_format($totaisPorForma['carteira'] ?? 0, 2, ',', '.') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cartao_debito" class="form-label">Cartão Débito</label>
                <input type="text" class="form-control" name="cartao_debito" id="cartao_debito" 
                       value="{{ number_format($totaisPorForma['cartao_debito'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-6">
                <label for="cartao_credito" class="form-label">Cartão Crédito</label>
                <input type="text" class="form-control" name="cartao_credito" id="cartao_credito" 
                       value="{{ number_format($totaisPorForma['cartao_credito'] ?? 0, 2, ',', '.') }}">
            </div>
        </div>
           @if($vm->semMovimento)
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Motivo do fechamento sem movimento
                    </label>
                    <textarea name="motivo_fechamento"
                            class="form-control"
                            required
                            placeholder="Ex.: falha no terminal, pinpad inoperante, abertura indevida..."></textarea>
                </div>
            @endif



        <button type="submit" class="btn btn-success">Fechar Caixa</button>
    </form>
    @endif -->

    {{-- =======================
        TABELA DE MOVIMENTAÇÕES
    ======================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card-header fs-5 bg-primary p-1 text-white fw-bold"> Movimentações do Caixa</div>
            <div class="movimentacoes-container">

            {{-- Cabeçalho --}}
            <div class="row bg-light fw-bold py-2 px-3 border-bottom">
                <div class="col-1">ID</div>
                <div class="col-2">Tipo</div>
                <div class="col-2">Forma</div>
                <div class="col-2">Valor</div>
                <div class="col-1">Origem</div>
                <div class="col-2">Data</div>
                <div class="col-2">Observação</div>
            </div>

            {{-- Linhas de movimentação --}}
            @foreach($caixa->movimentacoes as $mov)
                <div class="row py-2 px-3 border-bottom align-items-center movimentacao-item">
                    <div class="col-1">{{ $mov->id }}</div>
                    <div class="col-2">{{ ucfirst($mov->tipo) }}</div>
                    <div class="col-2">{{ ucfirst(str_replace('_',' ',$mov->forma_pagamento)) }}</div>
                    <div class="col-2">R$ {{ number_format($mov->valor, 2, ',', '.') }}</div>
                    <div class="col-1">{{ $mov->origem_id ?? '-' }}</div>
                    <div class="col-2">{{ $mov->data_movimentacao->format('d/m/Y H:i') }}</div>
                    <div class="col-2">{{ $mov->observacao ?? '-' }}</div>
                </div>
            @endforeach

        </div>

            <div class="mt-3">
                    <form action="{{ route('fechamento.fechar', $caixa) }}" method="POST">
                        @csrf

                        @if($caixa->estaAberto())
                            <button type="submit" class="btn btn-primary">
                                Lançamento de Valores Manuais
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>
                                Caixa já fechado
                            </button>
                        @endif

                        <a href="{{ route('fechamento.lista') }}" class="btn btn-secondary">
                            Cancelar
                        </a>

                    </form>

            </div>

        </div>
    </div>

</div>
@endsection
