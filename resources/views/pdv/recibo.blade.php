<!DOCTYPE html>
<html>
<head>
    <title>Recibo da Venda {{ $venda->id }}</title>
    <style>
        body { font-family: Arial; font-size: 14px; padding: 20px; }
        .linha { border-bottom: 1px dashed #aaa; margin: 5px 0; }
        .titulo { text-align: center; font-size: 18px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="titulo">RECIBO DA VENDA Nº {{ $venda->id }}</div>

<div class="linha"></div>
@foreach($venda->itens as $item)
    <p>
        {{ $item->produto->nome }} <br>
        {{ $item->quantidade }} x R$ {{ number_format($item->preco,2,',','.') }}
        <strong class="float-end">
            R$ {{ number_format($item->total,2,',','.') }}
        </strong>
    </p>
    <div class="linha"></div>
@endforeach

<h3>Total: R$ {{ number_format($venda->total,2,',','.') }}</h3>

<p>Forma de pagamento: <strong>{{ $venda->forma_pagamento }}</strong></p>

<br>
<p>Obrigado pela preferência!</p>

<script>
    window.print();
</script>

</body>
</html>
