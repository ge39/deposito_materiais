<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido de Compra #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
            position: relative;
        }

        /* Cabeçalho */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            text-align: center;
            font-size: 22px;
            text-transform: uppercase;
            flex-grow: 1;
            margin: 0;
        }

        .empresa-info {
            font-size: 12px;
            text-align: left;
        }

        .empresa-info p {
            margin: 2px 0;
        }

        /* Seções */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #f2f2f2;
            padding: 6px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        /* Tabela de itens */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #e9ecef;
        }

        td.num {
            text-align: right;
        }

        /* Total geral */
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 14px;
            padding: 5px;
            background: #f8f9fa;
            border-top: 2px solid #333;
        }

        /* Rodapé */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* Carimbos */
        .stamp {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-weight: bold;
            padding: 20px 40px;
            text-align: center;
            z-index: 1000;
            opacity: 0.4;
            border: 4px solid;
        }

        .stamp-cancelado {
            color: red;
            border-color: rgba(255,0,0,0.5);
            font-size: 60px;
        }

        .stamp-aprovado {
            color: green;
            border-color: rgba(0,128,0,0.5);
            font-size: 60px;
        }

        .stamp-recebido {
            color: blue;
            border-color: rgba(0,0,255,0.5);
            font-size: 50px;
        }
    </style>
</head>
<body>

    <!-- Cabeçalho -->
    <div class="header">
        @if($empresa && $empresa->logo)
            <img src="{{ public_path('storage/' . $empresa->logo) }}" alt="Logo" style="height:60px;">
        @endif
        <h1>Pedido de Compra #{{ $pedido->id }}</h1>
        <div class="empresa-info">
            @if($empresa)
                <p><strong>{{ $empresa->nome ?? $empresa->razao_social }}</strong></p>
                <p>CNPJ: {{ $empresa->cnpj }}</p>
                <p>{{ $empresa->endereco }} - {{ $empresa->cidade }}/{{ $empresa->estado }}</p>
                <p>Telefone: {{ $empresa->telefone }} | E-mail: {{ $empresa->email }}</p>
            @endif
        </div>
    </div>

    <!-- Dados do Pedido -->
    <div class="section">
        <div class="section-title">Dados do Pedido - <small>*Valor da última compra como referência*</small></div>
        <table>
            <tr>
                <td><strong>Data do Pedido:</strong> {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y H:i:s') }}</td>
                <td><strong>Fornecedor:</strong> {{ $pedido->fornecedor->nome ?? $pedido->fornecedor->razao_social }}</td>
            </tr>
            <tr>
                <td><strong>Telefone:</strong> {{ $pedido->fornecedor->telefone ?? '-' }}</td>
                <td><strong>E-mail:</strong> {{ $pedido->fornecedor->email ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Status:</strong> {{ ucfirst($pedido->status) }}</td>
            </tr>
        </table>
    </div>

    <!-- Itens do Pedido -->
    <div class="section">
        <div class="section-title">Itens do Pedido</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produto</th>
                    <th>Unidade</th>
                    <th class="num">Quantidade</th>
                    <th class="num">Valor referência</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>{{ $item->produto->unidadeMedida->nome ?? '-' }}</td>
                        <td class="num" style="text-align:left">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                        <td class="num" style="text-align:left">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="num" style="text-align:left">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total Geral -->
    <div class="total">
        Total Geral: R$ {{ number_format($pedido->itens->sum('subtotal'), 2, ',', '.') }}
    </div>

    <!-- Carimbos -->
    @if($pedido->status === 'cancelado')
        <div class="stamp stamp-cancelado">Pedido Cancelado</div>
    @elseif($pedido->status === 'aprovado')
        <div class="stamp stamp-aprovado">Pedido Autorizado</div>
    @elseif($pedido->status === 'recebido')
        <div class="stamp stamp-recebido">
            Produtos Recebidos<br>
            {{ \Carbon\Carbon::parse($pedido->updated_at)->format('d/m/Y H:i:s') }}
        </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>Gerado automaticamente em {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ $empresa->nome ?? $empresa->razao_social }} © Todos os direitos reservados</p>
    </div>

</body>
</html>
