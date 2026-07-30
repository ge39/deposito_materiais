<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>
        Vale-troca {{ $valeCompra->codigo ?? '' }}
    </title>

<style>
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: #222222;
        background: #ffffff;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
        line-height: 1.35;
    }

    .page {
        width: 100%;
    }

    .voucher {
        width: 100%;
        min-height: 126mm;
        background: #ffffff;
        border: 1px solid #8c8c8c;
        border-radius: 4px;
        overflow: hidden;
        page-break-inside: avoid;
    }

    .voucher-header {
        width: 100%;
        padding: 9px 12px;
        color: #ffffff;
        background: #333333;
    }

    .voucher-header-table,
    .info-table,
    .product-table,
    .signature-table,
    .total-table {
        width: 100%;
        border-collapse: collapse;
    }

    .voucher-header-table td {
        vertical-align: middle;
    }

    .company-name {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
    }

    .company-document {
        margin-top: 2px;
        color: #e3e3e3;
        font-size: 8px;
    }

    .copy-label {
        text-align: right;
    }

    .copy-label span {
        display: inline-block;
        padding: 3px 8px;
        color: #333333;
        background: #ffffff;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .voucher-title {
        padding: 7px 12px;
        background: #f2f2f2;
        border-bottom: 1px solid #cccccc;
        text-align: center;
    }

    .voucher-title h1 {
        margin: 0;
        color: #222222;
        font-size: 14px;
        font-weight: bold;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .voucher-title p {
        margin: 2px 0 0;
        color: #666666;
        font-size: 8px;
    }

    .code-box {
        margin: 8px 12px 0;
        padding: 6px;
        background: #ffffff;
        border: 1px solid #777777;
        border-radius: 3px;
        text-align: center;
    }

    .code-label {
        color: #666666;
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .code-value {
        margin-top: 2px;
        color: #111111;
        font-family: DejaVu Sans Mono, monospace;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .content {
        padding: 8px 12px 9px;
    }

    .section-title {
        margin: 0 0 5px;
        padding-bottom: 3px;
        color: #333333;
        border-bottom: 1px solid #cccccc;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .info-table {
        margin-bottom: 7px;
    }

    .info-table td {
        width: 50%;
        padding: 2px 8px 2px 0;
        vertical-align: top;
    }

    .label {
        display: block;
        color: #777777;
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .value {
        display: block;
        margin-top: 1px;
        color: #222222;
        font-size: 9px;
        font-weight: bold;
    }

    .product-table {
        margin-bottom: 7px;
        border: 1px solid #b5b5b5;
    }

    .product-table th {
        padding: 5px;
        color: #ffffff;
        background: #555555;
        border: 1px solid #555555;
        font-size: 7px;
        font-weight: bold;
        text-align: left;
        text-transform: uppercase;
    }

    .product-table td {
        padding: 6px 5px;
        color: #222222;
        background: #ffffff;
        border: 1px solid #d0d0d0;
        vertical-align: top;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .total-box {
        width: 100%;
        padding: 7px 9px;
        color: #111111;
        background: #eeeeee;
        border: 1px solid #888888;
        border-radius: 3px;
    }

    .total-table td {
        vertical-align: middle;
    }

    .total-label {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .total-value {
        font-size: 17px;
        font-weight: bold;
        text-align: right;
    }

    .rules {
        margin-top: 7px;
        padding: 6px 8px;
        color: #444444;
        background: #f7f7f7;
        border: 1px solid #dddddd;
        border-left: 3px solid #777777;
        font-size: 8px;
    }

    .rules p {
        margin: 1px 0;
    }

    .signature-table {
        margin-top: 12px;
    }

    .signature-table td {
        width: 50%;
        padding: 0 12px;
        text-align: center;
    }

    .signature-line {
        padding-top: 4px;
        color: #555555;
        border-top: 1px solid #666666;
        font-size: 8px;
    }

    .voucher-footer {
        padding: 5px 12px;
        color: #666666;
        background: #f5f5f5;
        border-top: 1px solid #d4d4d4;
        font-size: 7px;
        text-align: center;
    }

    .cut-line {
        position: relative;
        height: 12mm;
        margin: 0;
        border-bottom: 1px dashed #888888;
    }

    .cut-label {
        position: absolute;
        bottom: -5px;
        left: 43%;
        padding: 0 6px;
        color: #777777;
        background: #ffffff;
        font-size: 7px;
        text-transform: uppercase;
    }

    .status-badge {
        display: inline-block;
        padding: 2px 6px;
        color: #222222;
        background: #e5e5e5;
        border: 1px solid #999999;
        border-radius: 3px;
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>

</head>

<body>
@php
    $produto = $devolucao->produto
        ?? $devolucao->itemVenda?->produto;

    $dataEmissao = ! empty($valeCompra?->created_at)
        ? \Carbon\Carbon::parse($valeCompra->created_at)
        : now();

    $dataValidade = ! empty($valeCompra?->validade_em)
        ? \Carbon\Carbon::parse($valeCompra->validade_em)
        : $dataEmissao->copy()->addDays(7);

    $quantidade = (float) $devolucao->quantidade;
    $valorUnitario = (float) ($valorUnitarioPago ?? 0);
    $valorTotal = (float) (
        $valeCompra->valor
        ?? $valorTotalEstornado
        ?? 0
    );

    $documentoCliente = $cliente->cpf
        ?? $cliente->cnpj
        ?? 'Não informado';

    $vias = [
        'VIA LOJA',
        'VIA CLIENTE',
    ];
@endphp

<div class="page">
    @foreach ($vias as $indice => $via)
        <section class="voucher">
            <header class="voucher-header">
                <table class="voucher-header-table">
                    <tr>
                        <td>
                            <div class="company-name">
                                {{ $empresa->nome ?? 'Empresa não informada' }}
                            </div>

                            <div class="company-document">
                                {{ $empresa->endereco ?? '' }}
                                {{ $empresa->numero ?? '' }}
                                {{ $empresa->bairro ? ' - '.$empresa->bairro : '' }}
                                {{ $empresa->cidade ? ' - '.$empresa->cidade : '' }}
                                {{ $empresa->estado ? '/'.$empresa->estado : '' }}
                            </div>
                        </td>

                        <td class="copy-label">
                            <span>{{ $via }}</span>
                        </td>
                    </tr>
                </table>
            </header>

            <div class="voucher-title">
                <h1>Voucher de troca de mercadoria</h1>
                <p>
                    Crédito vinculado ao cliente e à devolução de origem
                </p>
            </div>

            <div class="code-box">
                <div class="code-label">Código do voucher</div>

                <div class="code-value">
                    {{ $valeCompra->codigo ?? 'CÓDIGO NÃO GERADO' }}
                </div>
            </div>

            <div class="content">
                <h2 class="section-title">Identificação</h2>

                <table class="info-table">
                    <tr>
                        <td>
                            <span class="label">Cliente</span>
                            <span class="value">
                                {{ $cliente->nome ?? 'Não informado' }}
                            </span>
                        </td>

                        <td>
                            <span class="label">CPF/CNPJ</span>
                            <span class="value">{{ $documentoCliente }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span class="label">Devolução</span>
                            <span class="value">
                                #{{ $devolucao->id }}
                            </span>
                        </td>

                        <td>
                            <span class="label">Ocorrência</span>
                            <span class="value">
                                {{ $devolucao->romaneio_ocorrencia_id
                                    ? '#'.$devolucao->romaneio_ocorrencia_id
                                    : 'Não vinculada' }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span class="label">Emissão</span>
                            <span class="value">
                                {{ $dataEmissao->format('d/m/Y H:i') }}
                            </span>
                        </td>

                        <td>
                            <span class="label">Validade</span>
                            <span class="value">
                                {{ $dataValidade->format('d/m/Y') }}
                            </span>
                        </td>
                    </tr>
                </table>

                <h2 class="section-title">Mercadoria devolvida</h2>

                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-right">Valor unitário pago</th>
                            <th class="text-right">Crédito</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                <strong>
                                    {{ $produto->nome ?? 'Produto não encontrado' }}
                                </strong>

                                <br>

                                <span style="color: #6f7c86; font-size: 7px;">
                                    Código:
                                    {{ str_pad(
                                        (string) ($produto->id ?? 0),
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </span>
                            </td>

                            <td class="text-center">
                                {{ number_format($quantidade, 3, ',', '.') }}
                            </td>

                            <td class="text-right">
                                R$ {{ number_format($valorUnitario, 2, ',', '.') }}
                            </td>

                            <td class="text-right">
                                <strong>
                                    R$ {{ number_format($valorTotal, 2, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="total-box">
                    <table class="total-table">
                        <tr>
                            <td class="total-label">
                                Crédito disponível
                            </td>

                            <td class="total-value">
                                R$ {{ number_format($valorTotal, 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="rules">
                    <p>
                        <strong>Motivo:</strong>
                        {{ $devolucao->motivo }}
                    </p>

                    <p>
                        Este voucher é pessoal, vinculado ao cliente identificado
                        acima e válido até
                        <strong>{{ $dataValidade->format('d/m/Y') }}</strong>.
                    </p>

                    <p>
                        Status:
                        <span class="status-badge">
                            {{ $valeCompra->status ?? 'ativo' }}
                        </span>
                    </p>
                </div>

                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="signature-line">
                                Assinatura do cliente
                            </div>
                        </td>

                        <td>
                            <div class="signature-line">
                                Responsável pela emissão
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <footer class="voucher-footer">
                {{ $empresa->telefone ? 'Telefone: '.$empresa->telefone : '' }}

                {{ $empresa->email ? ' | '.$empresa->email : '' }}

                | Voucher {{ $valeCompra->codigo ?? 'não identificado' }}
            </footer>
        </section>

        @if ($indice === 0)
            <div class="cut-line">
                <span class="cut-label">linha de corte</span>
            </div>
        @endif
    @endforeach
</div>
</body>
</html>