@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card border-dark mx-auto" style="max-width: 380px;">
        <div class="card-body text-center" style="font-family: monospace; font-size: 14px; line-height: 1.4; color: #000;">
            
            {{-- DADOS DA EMPRESA --}}
            <h5 class="fw-bold mb-1 fs-12px">{{ $empresa->nome ?? config('app.name') }}</h5>
            @if($empresa)
                <p class="mb-0">CNPJ: {{ $empresa->cnpj }}</p>
                <p class="mb-0">{{ $empresa->endereco }}, {{ $empresa->numero }}</p>
                <p class="mb-0">{{ $empresa->cidade }} - {{ $empresa->estado }}</p>
                <p class="mb-0">Tel: {{ $empresa->telefone }}</p>
            @endif
            
            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 fw-bold">CUPOM NÃO FISCAL</p>
            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- DADOS DA VENDA --}}
            <div class="text-start mb-2">
                <p class="mb-0"><strong>CÓDIGO:</strong> {{ str_pad($venda->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p class="mb-0"><strong>DATA:</strong> {{ $venda->created_at ? $venda->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s') }}</p>
                <p class="mb-0"><strong>TERMINAL:</strong> {{ $terminalId  }}</p>
                <p class="mb-0"><strong>VENDEDOR:</strong> {{ auth()->user()->name ?? auth()->user()->nome ?? $venda->funcionario->name ?? 'Balcão' }}</p>
                <p class="mb-0"><strong>CLIENTE:</strong> {{ $venda->cliente->nome ?? 'VENDA BALCAO' }}</p>
            </div>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">

                        <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- CABEÇALHO DA LISTAGEM DE ITENS --}}
            <div style="font-family: monospace; font-size: 12px; margin-top: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #000; font-weight:normal; padding-bottom: 3px;">
                    <span style="flex: 2; text-align: left;">PRODUTO</span>
                    <span style="flex: 1.5; text-align: center;">QTD x VL x UN</span>
                    <span style="flex: 1; text-align: right;">TOTAL</span>
                </div>

                {{-- LAÇO DE REPETIÇÃO DOS ITENS --}}
                @php 
                    $totalBrutoAcumulado = 0; 
                @endphp
                @foreach($venda->itens as $item)
                    @php
                        // Mantém os valores cheios da venda para exibição nas linhas
                        $precoUnitario = (float) $item->preco_unitario;
                        $quantidade    = (float) ($item->quantidade ?? $item->quantidade_solicitada ?? 0);
                        $totalItem     = $quantidade * $precoUnitario;
                        
                        // Acumula o valor bruto para justificar o rodapé
                        $totalBrutoAcumulado += $totalItem;

                        $siglaMedida = $item->produto->unidadeMedida->sigla ?? $item->unidade ?? 'UN';
                        $numeroLote  = $item->lote->numero_lote ?? 'N/A';
                    @endphp
                    <div style="margin-top: 4px; padding-bottom: 4px; font-size:12px; border-bottom: 1px dotted #eee; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            
                            {{-- NOME E ID DO PRODUTO --}}
                            <div style="flex: 2; display: flex; flex-direction: column; text-align: left; margin-bottom: -10px;">
                                <span style="font-weight:normal; font-size: 11px; line-height: 1.2;">
                                    {{ $item->produto->nome ?? 'Item não identificado' }}
                                </span>
                                <span style="font-size: 11px; color: #555; margin-top: -1px;">
                                    Lote: {{ $numeroLote }}
                                </span>
                            </div>
                            
                            {{-- QUANTIDADE E UNIDADE COM VALOR CHEIO --}}
                            <div style="flex: 1.5; text-align: center; white-space: nowrap;">
                                <span>{{ (int)$quantidade }} x </span>
                                <span style="font-weight:normal;">
                                    {{ $precoUnitario > 0 ? number_format($precoUnitario, 2, ',', '.') : 'Grátis' }}
                                    {{ strtoupper($siglaMedida) }}
                                </span>
                            </div>

                            {{-- VALOR TOTAL BRUTO DO ITEM --}}
                            <div style="flex: 1; text-align: right; font-weight:normal;">
                                <span>{{ number_format($totalItem, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- FORMAS DE PAGAMENTO --}}
            <div class="text-start mb-2">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                <!-- <div id="formas-pagamento-render">
                    @if(count($venda->pagamentos) > 0)
                        @foreach($venda->pagamentos as $pagamento)
                            <div class="d-flex justify-content-between">
                                <span class="text-uppercase">{{ $pagamento->forma_pagamento ?? $pagamento->tipo }}</span>
                                <span>
                                    @if(($pagamento->forma_pagamento ?? $pagamento->tipo) === 'dinheiro' && isset($troco) && $troco > 0)
                                        R$ {{ number_format($pagamento->valor + $troco, 2, ',', '.') }}
                                    @else
                                        R$ {{ number_format($pagamento->valor, 2, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @elseif(!empty($venda->forma_pagamento))
                        <div class="d-flex justify-content-between">
                            <span class="text-uppercase">{{ $venda->forma_pagamento }}</span>
                            <span>R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div> -->
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- SEÇÃO DE TOTAIS E FECHAMENTO DINÂMICA COM PONTE PARA O ORÇAMENTO --}}
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px; font-family: monospace;">
                @php
                    $totalLiquidoVenda = (float) $venda->total;
                    $totalBrutoExibicao = $totalBrutoAcumulado;
                    $valorDescontoReal = 0;
                    $percentualDesconto = 0;

                    // Captura o ID do orçamento se ele estiver guardado na tabela de vendas
                    $orcamentoId = $venda->orcamento_id ?? null;

                    if ($orcamentoId) {
                        // Captura a soma dos descontos reais salvos nas linhas do orçamento
                        $valorDescontoReal = (float) \DB::table('item_orcamentos')
                            ->where('orcamento_id', $orcamentoId)
                            ->sum('valor_desconto');

                        // Pega o percentual aplicado do primeiro item
                        $percentualDesconto = (float) \DB::table('item_orcamentos')
                            ->where('orcamento_id', $orcamentoId)
                            ->value('desconto_percentual') ?? 0;

                        // Recompõe o bruto oficial para bater com a soma matemática
                        $totalBrutoExibicao = $totalLiquidoVenda + $valorDescontoReal;
                    } else {
                        // Caso seja venda comum do PDV sem orçamento
                        $valorDescontoReal = $totalBrutoAcumulado - $totalLiquidoVenda;
                        if ($valorDescontoReal < 0) $valorDescontoReal = 0;
                        
                        $percentualDesconto = $totalBrutoAcumulado > 0 
                            ? round(($valorDescontoReal / $totalBrutoAcumulado) * 100) 
                            : 0;
                    }
                @endphp

                @if($valorDescontoReal > 0.05)
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                        <span>TOTAL BRUTO:</span>
                        <span>R$ {{ number_format($totalBrutoExibicao, 2, ',', '.') }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: red; font-size: 13px;">
                        <span>DESCONTO ({{ round($percentualDesconto) }}%):</span>
                        <span>(-) R$ {{ number_format($valorDescontoReal, 2, ',', '.') }}</span>
                    </div>
                @endif

                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: 4px;">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ {{ number_format($totalLiquidoVenda, 2, ',', '.') }}</span>
                </div>
            </div>


            <hr class="my-2" style="border-top: 1px dashed #000;">

                      <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- FORMAS DE PAGAMENTO --}}
            <div class="text-start mb-2">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                <div id="formas-pagamento-render">
                    @if(count($venda->pagamentos) > 0)
                        @foreach($venda->pagamentos as $pagamento)
                            <div class="d-flex justify-content-between">
                                <span class="text-uppercase">{{ $pagamento->forma_pagamento ?? $pagamento->tipo }}</span>
                                <span>
                                    @if(($pagamento->forma_pagamento ?? $pagamento->tipo) === 'dinheiro' && isset($troco) && $troco > 0)
                                        R$ {{ number_format($pagamento->valor + $troco, 2, ',', '.') }}
                                    @else
                                        R$ {{ number_format($pagamento->valor, 2, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    @elseif(!empty($venda->forma_pagamento))
                        <div class="d-flex justify-content-between">
                            <span class="text-uppercase">{{ $venda->forma_pagamento }}</span>
                            <span>R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- SEÇÃO DE TOTAIS E FECHAMENTO UNIFICADA (MATEMÁTICA HÍBRIDA) --}}

                        <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- SEÇÃO DE TOTAIS E FECHAMENTO UNIFICADA E FORÇADA --}}
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px; font-family: monospace;">
                @php
                    // 1. Soma real das formas de pagamento cadastradas no caixa para esta venda
                    $totalLiquidoRealPado = (float) $venda->pagamentos->sum('valor');

                    // Fallback caso a coleção de pagamentos esteja vazia no momento da renderização
                    if($totalLiquidoRealPado <= 0) {
                        $totalLiquidoRealPado = (float) $venda->total;
                    }

                    // 2. Calcula a diferença real de desconto concedido
                    $valorDescontoReal = $totalBrutoAcumulado - $totalLiquidoRealPado;
                    if ($valorDescontoReal < 0) $valorDescontoReal = 0;

                    // 3. Calcula o percentual exato do desconto aplicado
                    $percentualDesconto = $totalBrutoAcumulado > 0 
                        ? round(($valorDescontoReal / $totalBrutoAcumulado) * 100) 
                        : 0;
                @endphp

                {{-- Exibe o Total Bruto (Soma das linhas cheias: R$ 16.546,00) --}}
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 2px;">
                    <span>TOTAL BRUTO:</span>
                    <span>R$ {{ number_format($totalBrutoAcumulado, 2, ',', '.') }}</span>
                </div>

                {{-- 🔥 O CAMPO DE DESCONTO QUE ESTAVA FALTANDO — AGORA FORÇADO NA TELA --}}
                @if($valorDescontoReal > 0)
                    <div style="display: flex; justify-content: space-between; color: red; font-size: 13px; margin-bottom: 2px;">
                        <span>DESCONTO ({{ $percentualDesconto }}%):</span>
                        <span>(-) R$ {{ number_format($valorDescontoReal, 2, ',', '.') }}</span>
                    </div>
                @endif

                {{-- Exibe o Total Líquido real batendo com o dinheiro recebido (R$ 15.793,75) --}}
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: 4px; border-top: 1px dotted #000; padding-top: 4px;">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ {{ number_format($totalLiquidoRealPado, 2, ',', '.') }}</span>
                </div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 small text-muted">Obrigado pela preferência, volte sempre!</p>
            
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-primary d-print-none" onclick="window.print()">
                    Reimprimir
                </button>
            </div>

        </div>
    </div>
</div>




{{-- DISPARO COM LEITURA
    DO HISTÓRICO EM SEGUNDO PLANO --}}

{{-- DISPARO COM LEITURA DO HISTÓRICO EM SEGUNDO PLANO --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Captura o objeto de pagamento preenchido no caixa antes de fechar o modal
        let dadosOrigem = window.opener?.pagamentoDataFinal || window.parent?.pagamentoDataFinal;
        
        // 🎯 Correção: Substitua a linha 177 antiga por esta que lê o valor líquido real da venda
        let totalLiquido = parseFloat("{{ $venda->total }}") || 0;


        if (dadosOrigem) {
            let htmlFormas = '';
            let valorDinheiro = parseFloat(dadosOrigem.dinheiro) || 0;
            let valorCredito = parseFloat(dadosOrigem.cartao_credito) || 0;
            let valorDebito = parseFloat(dadosOrigem.cartao_debito) || 0;
            let valorPix = parseFloat(dadosOrigem.pix) || 0;
            let valorCarteira = parseFloat(dadosOrigem.carteira) || 0;

            // Gera as linhas correspondentes a cada pagamento utilizado
            if (valorDinheiro > 0)  htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">DINHEIRO</span><span>R$ ${valorDinheiro.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorCredito > 0)   htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTÃO CRÉDITO</span><span>R$ ${valorCredito.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorDebito > 0)    htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTÃO DÉBITO</span><span>R$ ${valorDebito.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorPix > 0)       htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">PIX</span><span>R$ ${valorPix.toFixed(2).replace('.', ',')}</span></div>`;
            if (valorCarteira > 0)  htmlFormas += `<div class="d-flex justify-content-between"><span class="text-uppercase">CARTEIRA</span><span>R$ ${valorCarteira.toFixed(2).replace('.', ',')}</span></div>`;

            if (htmlFormas !== '') {
                document.getElementById('formas-pagamento-render').innerHTML = htmlFormas;
            }

            // Se houve dinheiro, calcula e injeta o troco e o valor pago em tempo de execução
            if (valorDinheiro > 0) {
                let somaOutros = valorCredito + valorDebito + valorPix + valorCarteira;
                let tetoNecessarioDinheiro = totalLiquido - somaOutros;
                if (tetoNecessarioDinheiro < 0) tetoNecessarioDinheiro = 0;

                let trocoCalculado = valorDinheiro - tetoNecessarioDinheiro;
                if (trocoCalculado < 0) trocoCalculado = 0;

                let htmlTroco = `
                    <div style="display: flex; justify-content: space-between;">
                        <span>PAGO EM DINHEIRO:</span>
                        <span>R$ ${valorDinheiro.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;

                if (trocoCalculado > 0) {
                    htmlTroco += `
                        <div style="display: flex; justify-content: space-between; font-weight:normal;">
                            <span>TROCO:</span>
                            <span>R$ ${troco.toFixed(2).replace('.', ',')}</span>
                        </div>
                    `;
                }
                document.getElementById('bloco-calculo-troco-real').innerHTML = htmlTroco;
            }
        }

        // Executa o foco e o comando de impressão do navegador de forma silenciosa
        window.focus();
        window.print();
    });
</script>



@endsection

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
            box-shadow: none !important;
        }
        .btn {
            display: none !important;
        }
        @page {
            margin: 0;
        }
        body {
            margin: 0.2cm;
            background-color: #fff;
        }
    }
</style>
@endpush
