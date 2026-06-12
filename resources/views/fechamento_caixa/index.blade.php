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
                <span class="text-primary fw-bold"> ✅ Total Esperado Dinheiro:</span> R$ {{ number_format($caixa->fundo_troco + ($totaisPorForma['dinheiro'] ?? 0), 2, ',', '.') }}<br>
                <strong>Divergência:</strong> 
                <span class="{{ $divergencia != 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                    R$ {{ number_format($divergencia, 2, ',', '.') }}
                </span>
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
     {{ $caixa->status }}

    @if($caixa->estaAberto() && auth()->user()->can('fechar-caixa'))
   

    <form method="POST" action="{{ route('fechamento.fechar', $caixa->id) }}">
        @csrf
        <h5>Valores Físicos Conferidos caixa </h5>

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
    @endif

    {{-- =======================
        TABELA DE MOVIMENTAÇÕES
    ======================== --}}
    <div class="row mt-4">
        {{-- ========================================================================= --}}
        {{-- 💳 TABELA 1: MOVIMENTAÇÕES - RECEBIMENTO CARTEIRA --}}
        {{-- ========================================================================= --}}
        <div class="col-12 mb-4">
            <div class="card-header fs-5 bg-primary p-1 text-white fw-bold"> Movimentações - Recebimento Carteira</div>
            <div class="movimentacoes-container">

            {{-- Cabeçalho --}}
            <div class="row bg-light fw-bold py-2 px-3 border-bottom">
                <div class="col-2">Tipo</div>
                <div class="col-2">Forma</div>
                <div class="col-2">Valor</div>
                <div class="col-1">Origem</div>
                <div class="col-2">Data</div>
                <div class="col-3">Observação</div>
            </div>

            {{-- Filtra APENAS os tipos de recebimento de carteira e agrupa por forma --}}
            @php
                $carteiraMovimentacoes = $caixa->movimentacoes->filter(function($mov) {
                    return in_array($mov->tipo, ['entrada_pagto_carteira', 'entrada']);
                });

                $movimentacoesAgrupadasCarteira = $carteiraMovimentacoes->groupBy(function($mov) {
                    return strtolower(trim($mov->forma_pagamento));
                });
            @endphp

            @forelse($movimentacoesAgrupadasCarteira as $formaGrupo => $itensDoGrupo)
                @php
                    $totalDoGrupo = $itensDoGrupo->sum('valor');
                    $primeiroItem = $itensDoGrupo->first();
                @endphp
                <div class="row py-2 px-3 border-bottom align-items-center movimentacao-item">
                    <div class="col-2">
                        {{ $itensDoGrupo->pluck('tipo')->unique()->count() > 1 ? 'Entradas' : ucfirst(str_replace('_', ' ', $primeiroItem->tipo)) }}
                    </div>
                    
                    <div class="col-2 font-weight-bold">
                        {{ ucfirst(str_replace('_', ' ', $formaGrupo)) }}
                    </div>
                    
                    <div class="col-2 text-success font-weight-bold">
                        R$ {{ number_format($totalDoGrupo, 2, ',', '.') }}
                    </div>
                    
                    <div class="col-1">
                         Caixa {{$caixa->id }}
                    </div>
                    
                    <div class="col-2">
                         {{ $itensDoGrupo->max('data_movimentacao') ? \Carbon\Carbon::parse($itensDoGrupo->max('data_movimentacao'))->format('d/m/Y') : '' }}
                    </div>
                    
                    <div class="col-3 text-muted" style="font-size: 0.9rem;">
                         {{ $primeiroItem->observacao ?: 'Recebimento de saldo de carteira.' }}
                    </div>
                </div>
            @empty
                <div class="row py-2 px-3 border-bottom text-muted justify-content-center">Nenhum recebimento de carteira neste turno.</div>
            @endforelse
            
            <strong>✅ Total Carteira:</strong> R$ {{ number_format($carteiraMovimentacoes->sum('valor'), 2, ',', '.') }}<br>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 🏪 TABELA 2: MOVIMENTAÇÕES DO CAIXA (VENDAS GERAIS E SAÍDAS) --}}
        {{-- ========================================================================= --}}
        <div class="col-12">
            <div class="card-header fs-5 bg-primary p-1 text-white fw-bold"> Movimentações do Caixa</div>
            <div class="movimentacoes-container">

            {{-- Cabeçalho --}}
            <div class="row bg-light fw-bold py-2 px-3 border-bottom">
                <div class="col-2">Tipo</div>
                <div class="col-2">Forma</div>
                <div class="col-2">Valor</div>
                <div class="col-1">Origem</div>
                <div class="col-2">Data</div>
                <div class="col-3">Observação</div>
            </div>

           {{-- Filtra as movimentações normais (Gerais) REMOVENDO recebimentos de carteira e saídas/sangrias da soma de vendas --}}
            @php
                // Pega tudo que não é recebimento de carteira
                $geralMovimentacoes = $caixa->movimentacoes->filter(function($mov) {
                    return !in_array($mov->tipo, ['entrada_pagto_carteira', 'entrada']);
                });

                // Isola APENAS as vendas reais do dia para o cálculo correto do rodapé do bloco
                $vendasReaisDoBloco = $geralMovimentacoes->filter(function($mov) {
                    return $mov->tipo === 'venda';
                });

                // Agrupa para a listagem em tela
                $movimentacoesAgrupadasGeral = $geralMovimentacoes->groupBy(function($mov) {
                    $forma = strtolower(trim($mov->forma_pagamento));
                    return $forma === 'abertura' ? 'dinheiro' : $forma;
                });
            @endphp

            @forelse($movimentacoesAgrupadasGeral as $formaGrupo => $itensDoGrupo)
                @php
                    // Se o grupo contiver uma sangria ou saída, tratamos a cor e o comportamento de soma
                    $contemSaida = $itensDoGrupo->contains(function($item) {
                        return in_array($item->tipo, ['sangria', 'saida_manual', 'despesa', 'saida']);
                    });

                    $totalDoGrupo = $itensDoGrupo->sum('valor');
                    $primeiroItem = $itensDoGrupo->first();
                @endphp
                <div class="row py-2 px-3 border-bottom align-items-center movimentacao-item">
                    <div class="col-2">
                        {{ $itensDoGrupo->pluck('tipo')->unique()->count() > 1 ? 'Mix' : ucfirst(str_replace('_', ' ', $primeiroItem->tipo)) }}
                    </div>
                    
                    <div class="col-2 font-weight-bold">
                        {{ ucfirst(str_replace('_', ' ', $formaGrupo)) }}
                    </div>
                    
                    {{-- 💎 FIX COR DO VALOR: Se for saída/sangria, exibe em vermelho. Se for entrada/venda, exibe em verde --}}
                    <div class="col-2 font-weight-bold {{ $contemSaida ? 'text-danger' : 'text-success' }}">
                        {{ $contemSaida ? '-' : '' }} R$ {{ number_format($totalDoGrupo, 2, ',', '.') }}
                    </div>
                    
                    <div class="col-1">
                         Caixa {{$caixa->id }}
                    </div>
                    
                    <div class="col-2">
                        {{ $itensDoGrupo->max('data_movimentacao') ? \Carbon\Carbon::parse($itensDoGrupo->max('data_movimentacao'))->format('d/m/Y') : '' }}
                    </div>
                    
                    <div class="col-3 text-muted" style="font-size: 0.9rem;">
                         {{ $primeiroItem->observacao ?: 'Movimentação padrão de turno.' }}
                    </div>
                </div>
            @empty
                <div class="row py-2 px-3 border-bottom text-muted justify-content-center">Nenhuma movimentação geral de caixa registrada.</div>
            @endforelse
            
                {{-- 💎 FIX DO TOTALIZADOR: Soma estritamente as Vendas do PDV, ignorando a sangria --}}
                <strong>✅ Total Vendas:</strong> R$ {{ number_format($vendasReaisDoBloco->sum('valor'), 2, ',', '.') }}<br>
            </div>

            <!-- <strong>✅ Movimentações Gerais:</strong> R$ {{ number_format($geralMovimentacoes->sum('valor'), 2, ',', '.') }}<br> -->
            
            <div class="mt-3 p-2 bg-light border rounded d-flex justify-content-between align-items-center">
    <span class="text-muted fw-bold">Total Geral Movimentações:</span>
    <span class="fs-5 fw-bold text-success">R$ {{ number_format($total_entradas, 2, ',', '.') }}</span>
</div>
                

            {{-- ========================================================================= --}}
            {{-- BOTOÕES DE AÇÃO --}}
            {{-- ========================================================================= --}}
            <div class="mt-3">
                    @if($caixa->estaAberto())
                        <a href="{{ route('fechamento.view', $caixa->id) }}"
                        class="btn btn-primary">
                            Lançamento de Valores Manuais
                        </a>
                    @else
                        <button class="btn btn-primary" disabled
                                title="Caixa já fechado">
                            Lançamento de Valores Manuais
                        </button>
                    @endif

                    <a href="{{ url()->previous() }}" class="btn btn-secondary ">
                        Cancelar
                    </a>
            </div>

        </div>
    </div>


</div>
@endsection