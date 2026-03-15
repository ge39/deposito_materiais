<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<title>Cupom</title>

<style>

    body{
        font-family: monospace;
        width:300px;
        margin:0;
        font-size:12px;
    }

    .cupom{
        padding-left:10px;
        padding-right:10px;
        margin: 20px;
    }

    .center{
        text-align:center;
    }

    .hr{
        border-top:1px dashed #000;
        margin:6px 0;
    }

    .row{
        display:flex;
        justify-content:space-between;
    }

    .item{
        margin-top:4px;
    }

    .total{
        font-size:16px;
        font-weight:bold;
    }

    .qrcode{
        margin-top:10px;
    }

</style>
</head>

    <body onload="window.print()">

        <!-- EMPRESA -->

        <div class="center">

        <strong>{{ $empresa->nome }}</strong><br>

        CNPJ: {{ $empresa->cnpj }}<br>

        @if($empresa->inscricao_estadual)
        IE: {{ $empresa->inscricao_estadual }}<br>
        @endif

        {{ $empresa->endereco }}, {{ $empresa->numero }}<br>

        {{ $empresa->bairro }}<br>

        {{ $empresa->cidade }} - {{ $empresa->estado }}<br>

        @if($empresa->telefone)
        Tel: {{ $empresa->telefone }}<br>
        @endif

        </div>

        <div class="hr"></div>

        <!-- DADOS DA VENDA -->

        <div>Venda: {{ $venda->id }}</div>
        <div>Data: {{ $venda->created_at->format('d/m/Y H:i') }}</div>
        <div>Cliente: {{ $venda->cliente->nome ?? 'CONSUMIDOR' }}</div>
        <div>{{ $venda->cliente->endereco_entrega  ?? $venda->cliente->endereco .'-'. $venda->cliente->bairro .'-'. $venda->cliente->cidade . '-'. $venda->cliente->estado }}</div>
        <div>Operador: {{ $venda->funcionario->nome ?? 'PDV' }}</div>
        <div>Caixa: {{ $venda->caixa_id }}</div>

        <div class="hr"></div>

        <!-- ITENS -->

        @foreach($venda->itens as $item)

        <div class="item">

        <div>
        {{ $item->produto->nome }}
        </div>

        <div class="row">

        <div>
        {{ $item->quantidade }} x {{ number_format($item->preco_unitario,2,',','.') }}
        </div>

        <div>
        {{ number_format($item->quantidade * $item->preco_unitario,2,',','.') }}
        </div>

        </div>

        </div>

        @endforeach

        <div class="hr"></div>

        <!-- TOTAL -->

        <div class="row total">
        <div>TOTAL</div>
        <div>R$ {{ number_format($venda->total,2,',','.') }}</div>
        </div>

        <div class="hr"></div>

        <!-- PAGAMENTOS -->

        @php
        $valorDinheiro = 0;
        $temPix = false;
        @endphp

        @foreach($venda->pagamentos as $pag)

        @if($pag->valor > 0)

        <div class="row">
        <div>{{ strtoupper($pag->forma_pagamento) }}</div>
        <div>R$ {{ number_format($pag->valor,2,',','.') }}</div>
        </div>

        @if($pag->forma_pagamento == 'dinheiro')
        @php $valorDinheiro = $pag->valor; @endphp
        @endif

        @if($pag->forma_pagamento == 'pix')
        @php $temPix = true; @endphp
        @endif

        @endif

        @endforeach

        @php
        $troco = $valorDinheiro - $venda->total;
        @endphp

        @if($troco > 0)

        <div class="hr"></div>

        <div class="row">
        <div>TROCO</div>
        <div>R$ {{ number_format($troco,2,',','.') }}</div>
        </div>

        @endif

        <!-- QR CODE PIX -->

        @if($temPix)

        <!-- <div class="center qrcode">

            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=PIX-{{ $venda->id }}">

            <br>

            Pague com PIX

        </div> -->

        @endif

        <div class="hr"></div>

        <div class="center">

        OBRIGADO PELA PREFERÊNCIA

        <br><br>

        *** CUPOM NÃO FISCAL ***

        </div>

    </body>
    
</html>