<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Devolução</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .container { width: 90%; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 10px; }
        h2, h4 { text-align: center; margin: 5px 0; }
        .info { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .total { text-align: right; font-weight: bold; }
        .vale { background: #f8f8f8; border: 1px solid #ddd; padding: 10px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Comprovante de Devolução</h2>
        <h4>{{ config('app.name') }}</h4>

        <div class="info">
            <p><strong>Cliente:</strong> {{ $item->venda->cliente->nome }}</p>
            <p><strong>Data:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $devolucao->motivo }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $item->produto->nome }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($valorTotal, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="vale">
            <p><strong>Vale-Compra Gerado:</strong> {{ $vale->codigo }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($vale->valor, 2, ',', '.') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($vale->status) }}</p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            _______________________________________________<br>
            Assinatura do Responsável
        </p>

        <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #555;">
            Documento gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>
</body>
</html>
