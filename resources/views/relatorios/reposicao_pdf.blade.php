<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Reposição</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h2 {
            margin-bottom: 5px;
        }

        .subtitulo {
            margin-bottom: 15px;
            color: #666;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
        }

        .totais {
            display: flex;
            justify-content: space-between;
        }

        .totais div {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: #fff;
        }

        .danger { background: #e74c3c; }
        .warning { background: #f39c12; }
        .success { background: #27ae60; }

        .data-box {
            padding: 3px 5px;
            border-radius: 4px;
            text-align: center;
        }

        .linha-par {
            background: #fafafa;
        }

    </style>
</head>
<body>
<div class="col-md-2">
    <label>Orientação PDF</label>
    <select name="orientacao" class="form-control">
        <option value="portrait" {{ request('orientacao') == 'portrait' ? 'selected' : '' }}>
            Retrato
        </option>
        <option value="landscape" {{ request('orientacao') == 'landscape' ? 'selected' : '' }}>
            Paisagem
        </option>
    </select>
</div>

<h2>📦 Relatório de Reposição de Estoque</h2>
<div class="subtitulo">
    Gerado em: {{ now()->format('d/m/Y H:i') }}
</div>

{{-- 💰 TOTAIS --}}
<div class="card totais">
    <div>
        <strong>Total de itens Pendentes:</strong><br>
        {{ number_format($totais->total_pendente ?? 0, 2, ',', '.') }}
    </div>

    <div>
        <strong>Valor Total - Orçamentos Pendentes:</strong><br>
        R$ {{ number_format($totais->valor_total ?? 0, 2, ',', '.') }}
    </div>
</div>

{{-- 📊 RESUMO --}}
<div class="card">
    <strong>🔥 Produtos com maior necessidade de compra</strong>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Orçamentos</th>
                <th>Unidade</th>
                <th>Pendente</th>
                <th>Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumo as $r)
                <tr class="{{ $loop->even ? 'linha-par' : '' }}">
                    <td>{{ $r->nome }}</td>
                    <td style="max-width: 180px; word-wrap: break-word;">
                        @php
                            $codigos = explode(',', $r->codigos_orcamento ?? '');
                        @endphp

                        @foreach($codigos as $codigo)
                            <div style="
                                display: inline-block;
                                background: #3490dc;
                                color: #fff;
                                padding: 2px 6px;
                                margin: 2px;
                                border-radius: 3px;
                                font-size: 10px;
                            ">
                                {{ trim($codigo) }}
                            </div>
                        @endforeach
                    </td>
                    <td class="text-center">{{ $r->unidade ?? '-' }}</td>
                    <td class="text-right ">
                        <strong>{{ number_format($r->total_pendente, 2, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="badge danger">
                            {{ number_format($r->total_pendente, 2, ',', '.') }} {{$r->unidade}}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- 📋 DETALHAMENTO --}}
<div class="card">
    <strong>📋 Detalhamento por Produto</strong>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Cód. Barras</th>
                <th>Un</th>
                <th>Total</th>
                <th>Qtd Atendida</th>
                <th>Pendente</th>
                <th>Entrega</th>
                <th>Necessário</th>
                <th>Valor</th>
            </tr>
        </thead>

        <tbody>
            @foreach($dados as $item)
                @php
                    $dataEntrega = $item->previsao_entrega
                        ? \Carbon\Carbon::parse($item->previsao_entrega)
                        : null;

                    $dias = $dataEntrega
                        ? now()->diffInDays($dataEntrega, false)
                        : null;

                    if ($dias === null) {
                        $cor = '#eeeeee';
                    } elseif ($dias <= 0) {
                        $cor = '#ff4d4d';
                    } elseif ($dias <= 3) {
                        $cor = '#ff9999';
                    } elseif ($dias <= 7) {
                        $cor = '#ffcc66';
                    } elseif ($dias <= 14) {
                        $cor = '#ffff99';
                    } else {
                        $cor = '#99ff99';
                    }
                @endphp

                <tr class="{{ $loop->even ? 'linha-par' : '' }}">

                    <td>{{ $item->produto_nome }}</td>

                    <td>{{ $item->codigo_barras }}</td>

                    <td class="text-center">
                        {{ $item->unidade ?? '-' }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->total_quantidade, 2, ',', '.') }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->total_atendida, 2, ',', '.') }}
                    </td>

                    <td class="text-right">
                        <strong>
                            {{ number_format($item->total_pendente, 2, ',', '.') }}
                        </strong>
                    </td>

                    <td class="text-center">
                        @if($dataEntrega)
                            <div class="data-box" style="background: {{ $cor }}">
                                {{ $dataEntrega->format('d/m/Y') }}
                            </div>
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-center">
                        @if($item->necessidade_reposicao > 0)
                            <span class="badge danger">
                                {{ number_format($item->necessidade_reposicao, 2, ',', '.') }}
                            </span>
                        @else
                            <span class="badge success">OK</span>
                        @endif
                    </td>

                    <td class="text-right">
                        R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>