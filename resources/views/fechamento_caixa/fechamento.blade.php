@extends('layouts.app')

@section('content')
<style>
/* Container principal */
.fechamento-container {
    max-width: 1200px;
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
    font-size: 0.95 rem;
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
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.2);
    outline: none;
    transform: scale(1.02);
}

/* Destaque para valores financeiros */
.input-financeiro:focus {
    border-color: #198754;
    background-color: #e6f4ea;
    box-shadow: 0 0 10px rgba(25, 135, 84, 0.2);
}

/* Cards de entrada e saída */
.card.shadow-sm {
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

/* Headers de cartões coloridos */
.card-header.bg-success { background: #198754; }
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

    <form method="POST" action="{{ route('fechamento.fechar', $caixa->id) }}" id="formFechamento">
        @csrf

        {{-- DADOS DO CAIXA --}}
        <div class="card mb-4 border-primary shadow-sm">
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

        {{-- FILTRO DE MOVIMENTAÇÃO --}}
        @if(!DB::table('movimentacoes_caixa')->where('caixa_id', $caixa->id)->whereIn('tipo', ['venda', 'entrada_pagto_carteira', 'entrada'])->exists())
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
        @else
            <div class="row mt-4">
                {{-- 🏪 COLUNA 1: VALORES DE VENDAS DO DIA (PDV) --}}
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold">Valores por Forma de Pagamento (Vendas)</div>
                        <div class="card-body">
                            @foreach (['dinheiro'=>'Dinheiro','pix'=>'Pix','carteira'=>'Carteira','cartao_debito'=>'Cartão Débito','cartao_credito'=>'Cartão Crédito'] as $name => $label)
                                @php
                                    $valorInicial = old('valores_fisicos.'.$name, (isset($totaisPorForma[$name]) ? $totaisPorForma[$name] : 0));
                                @endphp
                                <div class="row align-items-center mb-2">
                                    <div class="col-5 fw-semibold">{{ $label }}</div>
                                    <div class="col-7">
                                        <input type="text" name="valores_fisicos[{{ $name }}]" class="form-control input-financeiro text-end currency-field"
                                            value="R$ {{ number_format($valorInicial, 2, ',', '.') }}" required>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 💳 COLUNA 2: RECEBIMENTOS DE CARTEIRA (APENAS DINHEIRO FÍSICO) --}}
                <!-- <div class="col-md-6">
                    <div class="card shadow-sm border-success" style="height: 100%;">
                        <div class="card-header bg-success text-white fw-bold">Valores de Recebimento em Carteira</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="row align-items-center mb-2">
                                <div class="col-5 fw-semibold">Dinheiro Carteira</div>
                                <div class="col-7">
                                    <input type="text" name="carteira_fisicos[dinheiro]" class="form-control input-financeiro text-end currency-field" readOnly
                                        value="R$ 0,00" required>
                                </div>
                            </div>
                            <div class="alert alert-info mt-auto mb-0" role="alert">
                                <i class="fas fa-info-circle"></i> Informe apenas o total em <strong>espécie (cédulas/moedas)</strong> que foi recebido fisicamente na gaveta para quitação de contas de clientes. Comprovantes de PIX ou Débito já são auditados eletronicamente pelo sistema.
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        @endif

        {{-- Botão de envio final do formulário --}}
        
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ url()->previous() }}" class="btn btn-secondary me-2 d-flex align-items-center shadow-sm">Cancelar</a>
            <button type="button" class="btn btn-success btn-submit shadow-sm">
                Confirmar Fechamento do Caixa
            </button>
        </div>
    </form>
</div>

{{-- ==========================================================================
🌟 SCRIPT CORRIGIDO PARA INPUT TYPE="NUMBER" (NATIVO DO SEU FORMULÁRIO)
========================================================================== --}}


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 🧠 Busca todos os inputs numéricos de finanças da sua tela
        const inputs = document.querySelectorAll('.input-financeiro');

        inputs.forEach(input => {
            // 1️⃣ Força o atributo nativo do HTML para bloquear negativos e aceitar decimais
            input.setAttribute('min', '0');
            input.setAttribute('step', '0.01');

            // 💎 FIX DO NaN NO CARREGAMENTO: Se o input vier vazio do banco ou com texto inválido, força para 0.00
            if (!input.value || isNaN(parseFloat(input.value))) {
                input.value = '0.00';
            } else {
                // Se já vier com valor (ex: do old ou totais), garante a formatação de 2 casas decimais
                input.value = parseFloat(input.value).toFixed(2);
            }

            // 2️⃣ Bloqueia o sinal de menos (-) e a letra 'e' diretamente no teclado do operador
            input.addEventListener('keydown', function(e) {
                if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                    e.preventDefault();
                }
            });

            // 3️⃣ Trata colagens de texto ou manipulações limpando o valor
            input.addEventListener('input', function() {
                if (this.value < 0) {
                    this.value = Math.abs(this.value); // Transforma em positivo se for negativo
                }
            });

            // 4️⃣ Quando o operador muda de campo (blur), aplica o acabamento decimal (ex: 225.00)
            input.addEventListener('blur', function() {
                let valorFloat = parseFloat(this.value);
                
                // 💎 FIX DO NaN NO BLUR: Valida se a conversão gerou um número real
                if (this.value && !isNaN(valorFloat)) {
                    // Garante que o número fique fixado com duas casas decimais padrão do banco
                    this.value = valorFloat.toFixed(2);
                } else {
                    this.value = '0.00';
                }
            });

            // Limpa o valor padrão 0.00 ao focar para agilizar a digitação no balcão
            input.addEventListener('focus', function() {
                let valorAtual = parseFloat(this.value);
                if (this.value === '0.00' || this.value === '0' || isNaN(valorAtual) || valorAtual === 0) {
                    this.value = '';
                }
            });
        });

        // 🎯 BLINDAGEM MESTRE: Monitora o clique diretamente pelo ciclo de vida global do navegador
        document.addEventListener('click', function(event) {
            // 1️⃣ Verifica se o elemento clicado possui a classe do nosso botão de submit
            if (event.target && event.target.classList.contains('btn-submit')) {
                
                // 2️⃣ Descobre o formulário correspondente subindo a árvore do HTML
                const formularioCaixa = event.target.closest('form');
                
                if (formularioCaixa) {
                    // 3️⃣ Realiza a varredura e a higienização dos campos numéricos para zeros
                    const inputsMonetarios = formularioCaixa.querySelectorAll('input[type="text"], input[type="number"]');
                    inputsMonetarios.forEach(input => {
                        let valorFloat = parseFloat(input.value);
                        if (!input.value || isNaN(valorFloat)) {
                            input.value = '0.00';
                        }
                    });

                    // 4️⃣ Dispara a mensagem de confirmação em segunda instância (Double-Check)
                    const operadorConfirmou = confirm("⚠️ ATENÇÃO: CONFIRMAÇÃO DE SEGURANÇA\n\nVocê tem certeza absoluta que deseja encerrar e fechar este caixa definitivamente?");
                    
                    // 5️⃣ Se o operador confirmar, o formulário é enviado de verdade
                    if (operadorConfirmou) {
                        formularioCaixa.submit();
                    }
                } else {
                    console.error("Erro Contábil: Não foi encontrado nenhuma tag <form> ao redor do botão de fechamento.");
                }
            }
        });


    });
</script>

@endsection