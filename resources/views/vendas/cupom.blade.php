@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card border-dark mx-auto" style="max-width: 380px;">
        <div class="card-body text-center" style="font-family: monospace; font-size: 14px; line-height: 1.4; color: #000;">
            
            {{-- DADOS DA EMPRESA --}}
            <h5 class="fw-bold mb-1">{{ $empresa->nome ?? config('app.name') }}</h5>
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
                <p class="mb-0"><strong>DATA:</strong> {{ $venda->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="mb-0"><strong>VENDEDOR:</strong> {{ $venda->funcionario_id ?? 'Balcão' }}</p>
                <p class="mb-0"><strong>CLIENTE:</strong> {{ $venda->cliente->nome ?? 'VENDA BALCAO' }}</p>
            </div>
            
            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- CABEÇALHO DA LISTAGEM DE ITENS --}}
            <div style="font-family: monospace; font-size: 12px; margin-top: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #000; font-weight: bold; padding-bottom: 3px;">
                    <span style="flex: 2; text-align: left;">PRODUTO</span>
                    <span style="flex: 1.5; text-align: center;">QTD x UN</span>
                    <span style="flex: 1; text-align: right;">TOTAL</span>
                </div>

                {{-- LAÇO DE REPETIÇÃO DOS ITENS --}}
                @foreach($venda->itens as $item)
                    <div style="margin-top: 4px; padding-bottom: 4px;font-size:14px; border-bottom: 1px dotted #eee; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            
                            {{-- NOME E ID DO PRODUTO --}}
                            <div style="flex: 2; display: flex; flex-direction: column; text-align: left;">
                                <span style="font-weight: bold; line-height: 1.2;">
                                    {{ $item->produto->nome ?? 'Item não identificado' }}
                                </span>
                                <span style="font-size: 11px; color: #555; margin-top: 1px;">
                                    Cod: {{ $item->produto_id }}
                                </span>
                            </div>

                            {{-- QUANTIDADE E UNIDADE --}}
                            <div style="flex: 1.5; text-align: center; white-space: nowrap;">
                                <span>{{ (int)$item->quantidade }} x </span>
                                <span style="font-weight: bold;">
                                    {{ strtoupper($item->unidade ?? $item->produto->unidade ?? 'UN') }}
                                </span>
                            </div>

                            {{-- VALOR TOTAL LÍQUIDO DO ITEM --}}
                            <div style="flex: 1;text-align: right; font-weight: bold;">
                                <span>R$ {{ number_format(($item->quantidade * $item->preco_unitario) - ($item->desconto ?? 0), 2, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- DESCONTO POR ITEM --}}
                        @if(($item->desconto ?? 0) > 0)
                            <div style="text-align: right; font-size: 10px; color: red; margin-top: 2px;">
                                <span>(-) Desc. Item: R$ {{ number_format($item->desconto, 2, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- FORMAS DE PAGAMENTO --}}
            <div class="text-start mb-2">
                <p class="mb-1 fw-bold">FORMA(S) DE PAGAMENTO:</p>
                @foreach($venda->pagamentos as $pagamento)
                    <div class="d-flex justify-content-between">
                        <span class="text-uppercase">{{ $pagamento->forma_pagamento ?? $pagamento->tipo }}</span>
                        <span>R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">

            {{-- SEÇÃO DE TOTAIS E FECHAMENTO --}}
            <div class="totais-cupom" style="margin-top: 10px; font-size:14px;font-family: monospace;">
                @php
                    $descontoTotal = $venda->itens->sum('desconto');
                    $pagoEmDinheiro = $venda->pagamentos->whereIn('forma_pagamento', ['dinheiro', 'DINHEIRO', 'Dinheiro'])->sum('valor');
                    $totalPago = $venda->pagamentos->sum('valor');
                    $troco = $totalPago > $venda->total ? ($totalPago - $venda->total) : 0;
                @endphp

                @if($descontoTotal > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span>DESCONTO TOTAL ITEMS:</span>
                        <span>R$ {{ number_format($descontoTotal, 2, ',', '.') }}</span>
                    </div>
                @endif

                <div style="display: flex; justify-content: space-between; font-weight: bold; ">
                    <span>TOTAL LÍQUIDO:</span>
                    <span>R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                </div>

                <hr style="border-top: 1px dashed #000; margin: 5px 0;">

                @if($pagoEmDinheiro > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span>PAGO EM DINHEIRO:</span>
                        <span>R$ {{ number_format($pagoEmDinheiro, 2, ',', '.') }}</span>
                    </div>
                @endif

                @if($troco > 0)
                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                        <span>TROCO:</span>
                        <span>R$ {{ number_format($troco, 2, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <hr class="my-2" style="border-top: 1px dashed #000;">
            <p class="mb-0 text-muted fst-italic">Obrigado pela preferência, volte sempre!</p>

            {{-- Correção do Caractere Corrompido aqui --}}
            <button class="btn btn-primary btn-sm mt-3" onclick="window.print()">Reimprimir</button>
        </div>
    </div>
</div>

{{-- DISPARO TRADICIONAL --}}
<script>
    // window.onload = function() {
    //     window.focus();
    //     window.print();
    //     window.onafterprint = function() {
    //         window.close();
    //     };
    // };
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
