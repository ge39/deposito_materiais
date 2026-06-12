@extends('layouts.app')

@section('content')

<style>
    /* Container principal */
    .fechamento-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Título da página */
    .fechamento-container h4 {
        font-size: 2rem;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 30px;
    }

    /* Card do caixa */
    .card-header {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .card-body {
        font-size: 1rem;
    }

    /* Badges de status */
    .badge-status {
        font-size: 0.95rem;
        padding: 0.5em 0.75em;
    }

    /* Inputs */
    .form-control {
        font-size: 1rem;
        border-radius: 6px;
        padding: 10px 12px;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 8px rgba(13,110,253,0.2);
        outline: none;
        transform: scale(1.02);
    }

    /* Destaque para valores financeiros */
    .input-financeiro:focus {
        border-color: #198754;
        background-color: #e6f4ea;
        box-shadow: 0 0 10px rgba(25,135,84,0.2);
    }

    /* Cards de entrada e saída */
    .card.shadow-sm {
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    /* Headers de cartões coloridos */
    .card-header.bg-success { background: #198754; }
    .card-header.bg-danger { background: #dc3545; }
    .card-header.bg-primary { background: #0d6efd; }

    .row.align-items-center { margin-bottom: 10px; }
    .fw-semibold { font-weight: 600; }

    /* Botões finais */
    .btn-submit {
        font-size: 1.05rem;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-submit:hover { transform: scale(1.03); }
</style>

<div class="fechamento-container">

    <h4 class="bg-secondary text-center text-white p-2 rounded">Fechamento do Caixa #{{ $caixa->id }}</h4>

    <form method="POST" action="{{ route('fechamento.fechar', $caixa->id) }}">
        @csrf

        {{-- DADOS DO CAIXA --}}
        <div class="card mb-4 border-primary shadow-sm">
            {{-- 🌟 CORREGIDO: Removido o texto estático fixo "- Sem Movimento" --}}
            <div class="card-header bg-primary text-white">
                ✅ Dados do Caixa - Fechamento #{{ $caixa->id }}
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>ID (Caixa)</strong><br>{{ $caixa->id }}</div>
                    <div class="col-md-4"><strong>Operador</strong><br>{{ $caixa->usuario->name ?? 'Não identificado' }}</div>
                    <div class="col-md-4"><strong>Terminal ID</strong><br>{{ $caixa->terminal_id }}</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Abertura</strong><br>{{ $caixa->data_abertura ? \Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y H:i') : '-' }}</div>
                    
                    <div class="col-md-4">
                        <strong>Status</strong><br>
                        @php
                            $statusLabel = match($caixa->status) {
                                'aberto' => 'Aberto',
                                'pendente' => 'Pendente',
                                'fechado' => 'Fechado',
                                'fechado_sem_movimento' => 'Fechado sem movimento',
                                'inconsistente' => 'Inconsistente',
                                default => ucfirst($caixa->status),
                            };
                        @endphp
                        <span class="badge badge-status bg-success">{{ $statusLabel }}</span>
                    </div>
                    <div class="col-md-4"><strong>Fundo de Troco</strong><br>R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- ==========================================================================
            🌟 CORREÇÃO DE LOGICA: Se não existirem linhas do tipo 'venda' na fita 
            do caixa aberto no banco, exibe o bloco de fechamento sem movimentação.
        ========================================================================== --}}
        @if(!DB::table('movimentacoes_caixa')->where('caixa_id', $caixa->id)->where('tipo', 'venda')->exists())
            <div class="card mb-4 border-success shadow-sm">
                <div class="card-header bg-success text-white">Fechamento sem Movimentação Comercial</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="motivo_fechamento" class="fw-bold">Motivo do fechamento</label>
                        <select name="motivo_fechamento" id="motivo_fechamento" class="form-control" required>
                            <option value="">Selecione o motivo</option>
                            <option value="Caixa aberto sem movimento">Caixa aberto sem movimento</option>
                            <option value="Troca de operador do caixa">Troca de operador do caixa</option>
                            <option value="Sistema indisponível">Sistema indisponível</option>
                            <option value="Loja não abriu">Loja não abriu</option>
                            <option value="Erro hardware">Erro hardware</option>
                            <option value="Erro operacional">Erro operacional</option>
                        </select>
                    </div>
                </div>
            </div>

        {{-- SE HOUVER LINHAS DE VENDA REGISTRADAS NA FITA, LIBERA OS VALORES FÍSICOS --}}
        @else
            <div class="row mt-4">
                {{-- Valores por Forma de Pagamento (Valores pré-preenchidos se já existirem no array) --}}
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold">Valores por Forma de Pagamento (Gaveta)</div>
                        <div class="card-body">
                            @foreach (['dinheiro'=>'Dinheiro','pix'=>'Pix','carteira'=>'Carteira','cartao_debito'=>'Cartão Débito','cartao_credito'=>'Cartão Crédito'] as $name => $label)
                                <div class="row align-items-center">
                                    <div class="col-4 fw-semibold">{{ $label }}</div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="{{ $name }}" class="form-control input-financeiro" 
                                               value="{{ old($name, (isset($totaisPorForma[$name]) ? $totaisPorForma[$name] : 0)) }}" required>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                {{-- Saídas --}}
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white fw-bold">Saídas de Caixa</div>
                        <div class="card-body">
                            @foreach (['saida_sangria'=>'Sangria','saida_despesa'=>'Despesas','saida_ajuste'=>'Ajuste Negativo','saida_outros'=>'Outras Saídas'] as $name => $label)
                                <div class="row align-items-center">
                                    <div class="col-4 fw-bold">{{ $label }}</div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="{{ $name }}" class="form-control input-financeiro text-end" value="{{ old($name, 0) }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Botão de envio final do formulário --}}
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success btn-submit shadow-sm">Confirmar Fechamento do Caixa</button>
        </div>
    </form>
</div>
@endsection
