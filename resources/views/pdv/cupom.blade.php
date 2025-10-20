<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cupom Fiscal</title>
    <style>
        body { font-family: monospace; font-size:12px; }
        .center { text-align:center; }
        .line { border-bottom:1px dashed #000; margin:5px 0; }
        table { width:100%; border-collapse: collapse; }
        td { padding:2px; }
    </style>
</head>
<body>
    <div class="center">
        <h3>Minha Loja</h3>
        <p>PDV Laravel - Cupom Fiscal</p>
        <p>Data: {{ date('d/m/Y H:i') }}</p>
    </div>
    <div class="line"></div>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qtde</th>
                <th>Preço</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $item)
            <tr>
                <td>{{ $item->produto->nome }}</td>
                <td>{{ $item->quantidade }}</td>
                <td>R$ {{ number_format($item->preco,2,",",".") }}</td>
                <td>R$ {{ number_format($item->subtotal,2,",",".") }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="line"></div>
    <p>Total: R$ {{ number_format($total,2,",",".") }}</p>
    <p>Pago: R$ {{ number_format($valor_pago,2,",",".") }}</p>
    <p>Troco: R$ {{ number_format($troco,2,",",".") }}</p>
    <p>Forma de pagamento: {{ ucfirst($forma_pagamento) }}</p>
    <div class="line"></div>
    <div class="center">
        <p>Obrigado pela preferência!</p>
    </div>

    <script>
        window.onload = function(){
            window.print(); // Imprime automaticamente
            setTimeout(function(){ window.close(); }, 1000);
        }
    </script>
</body>
</html>
