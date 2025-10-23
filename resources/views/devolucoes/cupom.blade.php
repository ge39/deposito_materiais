<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom de Devolução - Duas Vias</title>
    <style>
        @page {
            size: 215mm 315mm;
            margin: 4mm;
        }

        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 0;
            display: flex;
            justify-content: center;
            background-color: #fff;
            color: #000;
        }

        .sheet {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0;
            box-sizing: border-box;
        }

        .ticket {
            width: calc(100% - 8mm);
            background: #fff;
            border: 2px dashed #444;
            border-radius: 8px;
            padding: 8px;
            box-sizing: border-box;
            page-break-inside: avoid;
            margin: 0 auto;
        }

        .ticket-label {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 1.1rem;
            margin: 0;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        hr {
            border: 1px dashed #444;
            margin: 5px 0;
        }

        .cliente, .devolucao {
            margin-top: 6px;
        }

        .cliente p, .devolucao p {
            margin: 3px 0;
        }

        .devolucao {
            background-color: #fff8f5;
            padding: 5px;
            border: 1px solid #e0a19a;
            border-radius: 5px;
        }

        .observacao {
            margin-top: 5px;
            font-size: 11px;
            color: #d9534f;
            font-weight: bold;
            padding: 3px;
            background-color: #fff0f0;
            border-radius: 4px;
        }

        .customer-sign, .signature {
            margin-top: 12px;
            text-align: center;
        }

        .small-text {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
@php
    // Seleciona a empresa/filial correta
    $empresa = $empresa ?? \App\Models\Empresa::first();
@endphp

<div class="sheet">

    <!-- VIA Loja -->
    <div class="ticket">
        <div class="ticket-label">VIA LOJA</div>

        <div class="header">
            <h1>{{ $empresa->nome ?? '---' }}</h1>
            <p><strong>Data:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <p>{{ $empresa->endereco ?? '---' }} {{ $empresa->numero ?? '' }} {{ $empresa->complemento ?? '' }}</p>
            <p>{{ $empresa->bairro ?? '---' }} - {{ $empresa->cidade ?? '---' }} - {{ $empresa->estado ?? '' }} - CEP {{ $empresa->cep ?? '---' }}</p>
            <p>Tel: {{ $empresa->telefone ?? '---' }} | Email: {{ $empresa->email ?? '---' }}</p>
            <hr>
        </div>
        <div class="cliente">
            <h3>Cliente:</h3>
            <p><strong>Nome:</strong> {{ $cliente->nome ?? '---' }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ $cliente->cpf ?? $cliente->cnpj ?? '---' }}</p>
            <p><strong>Endereço:</strong> {{ $cliente->endereco ?? '---' }}</p>
            <p><strong>Telefone:</strong> {{ $cliente->telefone ?? '---' }}</p>
        </div>

        <div class="devolucao">
            <h3>Devolução - {{ $devolucao->produto->nome ?? '---' }} - 000{{ $devolucao->produto->id ?? '---' }}</h3>
            <p><strong>Produto devolvido:</strong> {{ $devolucao->produto->nome ?? '---' }}</p>
            <p><strong>Quantidade:</strong> {{ $devolucao->quantidade }}</p>
            <p><strong>Motivo:</strong> {{ $devolucao->motivo }}</p>
            <p><strong>Status:</strong> {{ ucfirst($devolucao->status) }}</p>
            <p><strong>Valor a ser restituído:</strong> 
                R$ {{ number_format($devolucao->quantidade * ($devolucao->produto->preco_venda ?? 0), 2, ',', '.') }}
            </p>
        </div>

        <div class="observacao">
            Observação: O cliente tem até <strong>7 dias</strong> para efetuar a troca do produto.
        </div>

        <div class="customer-sign">
            <p>Assinatura do Cliente: ____________________________</p>
            <p>Telefone para contato: ____________________________</p>
        </div>
    </div>

    <!-- VIA CLIENTE -->
    <div class="ticket">
        <div class="ticket-label">VIA CLIENTE</div>

        <div class="header">
            <h1>{{ $empresa->nome ?? '---' }}</h1>
            <p><strong>Data:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <p>{{ $empresa->endereco ?? '---' }} {{ $empresa->numero ?? '' }} {{ $empresa->complemento ?? '' }}</p>
            <p>{{ $empresa->bairro ?? '---' }} - {{ $empresa->cidade ?? '---' }} - {{ $empresa->estado ?? '' }} - CEP {{ $empresa->cep ?? '---' }}</p>
            <p>Tel: {{ $empresa->telefone ?? '---' }} | Email: {{ $empresa->email ?? '---' }}</p>
            <hr>
        </div>

        <div class="cliente">
            <h3>Cliente:</h3>
            <p><strong>Nome:</strong> {{ $cliente->nome ?? '---' }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ $cliente->cpf ?? $cliente->cnpj ?? '---' }}</p>
            <p><strong>Endereço:</strong> {{ $cliente->endereco ?? '---' }}</p>
            <p><strong>Telefone:</strong> {{ $cliente->telefone ?? '---' }}</p>
        </div>

        <div class="devolucao">
            <h3>Devolução - {{ $devolucao->produto->nome ?? '---' }} - 000{{ $devolucao->produto->id ?? '---' }}</h3>
            <p><strong>Produto devolvido:</strong> {{ $devolucao->produto->nome ?? '---' }}</p>
            <p><strong>Quantidade:</strong> {{ $devolucao->quantidade }}</p>
            <p><strong>Motivo:</strong> {{ $devolucao->motivo }}</p>
            <p><strong>Status:</strong> {{ ucfirst($devolucao->status) }}</p>
            <p><strong>Valor a ser restituído:</strong> 
                R$ {{ number_format($devolucao->quantidade * ($devolucao->produto->preco_venda ?? 0), 2, ',', '.') }}
            </p>
        </div>
         <div class="observacao">
            Observação: O cliente tem até <strong>7 dias</strong> para efetuar a troca do produto.
        </div>
        <div class="signature">
            <p>___________________________________</p>
            <p>Assinatura do Responsável</p>
        </div>
    </div>

</div>
</body>
</html>
