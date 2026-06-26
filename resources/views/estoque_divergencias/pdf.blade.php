<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Divergências de Estoque</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 30px;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0 0 15px 0;
        }

        .empresa {
            margin-top: 10px;
            line-height: 1.5;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            background: #f0f0f0;
            padding: 6px;
            font-weight: bold;
            border: 1px solid #ccc;
        }

        .box {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .row {
            margin-bottom: 5px;
        }

        .label {
            display: inline-block;
            width: 130px;
            font-weight: bold;
        }

        .table {
            display: table;
            width: 100%;
            margin-top: 5px;
            border-collapse: collapse;
        }

        .tr {
            display: table-row;
        }

        .th, .td {
            display: table-cell;
            border: 1px solid #999;
            padding: 5px;
            font-size: 10px;
            vertical-align: middle;
        }

        .th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-danger {
            color: #b00020;
            font-weight: bold;
        }

        .produto-titulo {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .resumo-produto {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>Relatório de Divergências de Estoque</h2>

    <div>
        Gerado em: {{ now()->format('d/m/Y H:i') }}
    </div>

    @isset($empresa)
        <div class="empresa">
            <strong>{{ $empresa->nome ?? $empresa->razao_social ?? 'Empresa' }}</strong><br>

            @if(!empty($empresa->cnpj))
                CNPJ: {{ $empresa->cnpj }}<br>
            @endif

            @if(!empty($empresa->endereco))
                {{ $empresa->endereco }}<br>
            @endif

            @if(!empty($empresa->telefone))
                Tel: {{ $empresa->telefone }}
            @endif
        </div>
    @endisset
</div>

<div class="section">
    <div class="section-title">Filtros Aplicados</div>
    <div class="box">
        <div class="row">
            <span class="label">Produto:</span>
            {{ request('produto') ?: 'Todos' }}
        </div>

        <div class="row">
            <span class="label">Tipo:</span>
            {{ request('tipo') ?: 'Todos' }}
        </div>

        <div class="row">
            <span class="label">Data Inicial:</span>
            {{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : now()->format('d/m/Y') }}
        </div>

        <div class="row">
            <span class="label">Data Final:</span>
            {{ request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : now()->format('d/m/Y') }}
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Resumo por Produto</div>
    <div class="box">

        @forelse($divergencias as $produtoId => $grupo)
            @php
                $primeira = $grupo->first();
                $produtoNome = $primeira->produto->nome ?? 'Produto não encontrado';

                $totalSolicitado = $grupo->sum('quantidade_solicitada');
                $totalAtendido = $grupo->sum('quantidade_atendida');
                $totalDiferenca = $grupo->sum('diferenca');
                $totalOcorrencias = $grupo->count();
            @endphp

            <div style="margin-bottom: 18px;">
                <div class="produto-titulo">
                    Produto: {{ $produtoNome }}
                </div>

                <div class="resumo-produto">
                    <strong>Ocorrências:</strong> {{ $totalOcorrencias }} |
                    <strong>Total Solicitado:</strong> {{ number_format($totalSolicitado, 3, ',', '.') }} |
                    <strong>Total Atendido:</strong> {{ number_format($totalAtendido, 3, ',', '.') }} |
                    <strong class="text-danger">Total Divergente:</strong>
                    <span class="text-danger">{{ number_format($totalDiferenca, 3, ',', '.') }}</span>
                </div>

                <div class="table">
                    <div class="tr">
                        <div class="th">ID</div>
                        <div class="th">Venda</div>
                        <div class="th">Caixa</div>
                        <div class="th text-right">Solicitado</div>
                        <div class="th text-right">Atendido</div>
                        <div class="th text-right">Diferença</div>
                        <div class="th">Tipo</div>
                        <div class="th">Usuário</div>
                        <div class="th">Data</div>
                    </div>

                    @foreach($grupo as $divergencia)
                        <div class="tr">
                            <div class="td">{{ $divergencia->id }}</div>
                            <div class="td">{{ $divergencia->venda_id ?? '-' }}</div>
                            <div class="td">{{ $divergencia->caixa_id ?? '-' }}</div>

                            <div class="td text-right">
                                {{ number_format($divergencia->quantidade_solicitada, 3, ',', '.') }}
                            </div>

                            <div class="td text-right">
                                {{ number_format($divergencia->quantidade_atendida, 3, ',', '.') }}
                            </div>

                            <div class="td text-right text-danger">
                                {{ number_format($divergencia->diferenca, 3, ',', '.') }}
                            </div>

                            <div class="td">{{ ucfirst($divergencia->tipo) }}</div>
                            <div class="td">{{ $divergencia->usuario->name ?? '-' }}</div>
                            <div class="td">{{ optional($divergencia->created_at)->format('d/m/Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

        @empty
            <div class="text-center">
                Nenhuma divergência encontrada.
            </div>
        @endforelse

    </div>
</div>

<div class="footer">
    Relatório gerado automaticamente pelo sistema — {{ config('app.name', 'Depósito de Materiais') }}
</div>

</body>
</html>