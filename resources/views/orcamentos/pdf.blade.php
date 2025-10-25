<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento #{{ $orcamento->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
        }

        .info {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .info strong {
            width: 150px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .observacoes {
            margin-top: 30px;
            font-size: 0.9em;
        }

        .carimbo {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 60px;
            color: rgba(255, 0, 0, 0.2);
            font-weight: bold;
            text-transform: uppercase;
            border: 5px solid rgba(255, 0, 0, 0.2);
            padding: 15px 40px;
            border-radius: 15px;
        }

        .carimbo.aprovado {
            color: rgba(0, 128, 0, 0.25);
            border-color: rgba(0, 128, 0, 0.25);
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8em;
            color: #555;
        }
    </style>
</head>
<body>
    {{-- Carimbo de status --}}
    @if($orcamento->status === 'Aprovado')
        <div class="carimbo aprovado">APROVADO</div>
    @elseif($orcamento->status === 'Cancelado')
        <div class="carimbo">CANCELADO</div>
    @else
        <div class="carimbo" style="color:rgba(0,0,0,0.15); border-color:rgba(0,0,0,0.15);">ABERTO</div>
    @endif

    <div class="header">
        <h2>Orçamento de Cliente</h2>
        <p><strong>Depósito de Materiais - Sistema Interno</strong></p>
        <p>Emitido em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <p><strong>Código:</strong> #{{ $orcamento->id }}</p>
        <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</p>
        <p><strong>Data do Orçamento:</strong> {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</p>
        <p><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
        <p><strong>Status:</strong> {{ $orcamento->status }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th style="width: 100px;">Qtd</th>
                <th style="width: 120px;">Preço Unitário (R$)</th>
                <th style="width: 120px;">Subtotal (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orcamento->itens as $item)
                <tr>
                    <td>{{ $item->produto->nome ?? '-' }}</td>
                    <td>{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total: R$ {{ number_format($orcamento->total, 2, ',', '.') }}</p>

    @if($orcamento->observacoes)
        <div class="observacoes">
            <strong>Observações:</strong>
            <p>{{ $orcamento->observacoes }}</p>
        </div>
    @endif

    <div class="footer">
        Documento gerado automaticamente pelo sistema - {{ config('app.name', 'Depósito de Materiais') }}
    </div>
</body>
</html>
