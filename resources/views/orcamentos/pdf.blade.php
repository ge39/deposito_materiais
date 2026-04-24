<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento #{{ $orcamento->id }}</title>
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

        .section {
            margin-bottom: 20px;
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
            width: 120px;
            font-weight: bold;
        }

        /* 🔥 TABELA EM DIV (DOMPDF SAFE) */
        .table {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .tr {
            display: table-row;
        }

        .th, .td {
            display: table-cell;
            border: 1px solid #999;
            padding: 6px;
            font-size: 11px;
        }

        .th {
            background: #eee;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 13px;
        }

        .page-break {
            page-break-before: always;
        }

        .assinatura {
            margin-top: 60px;
            width: 100%;
        }

        .assinatura-box {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .linha {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        .contato {
            margin-top: 20px;
            font-size: 11px;
            text-align: center;
            color: #555;
        }

        .carimbo {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 60px;
            color: rgba(0,0,0,0.1);
            border: 4px solid rgba(0,0,0,0.1);
            padding: 10px 30px;
        }

        .aprovado {
            color: rgba(0,128,0,0.2);
            border-color: rgba(0,128,0,0.2);
        }

        .cancelado {
            color: rgba(255,0,0,0.2);
            border-color: rgba(255,0,0,0.2);
        }

        .footer {
            position: fixed;
            bottom: 10px;
            text-align: center;
            width: 100%;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

{{-- CARIMBO --}}
@if($orcamento->status === 'Aprovado')
    <div class="carimbo aprovado">APROVADO</div>
@elseif($orcamento->status === 'Cancelado')
    <div class="carimbo cancelado">CANCELADO</div>
@else
    <div class="carimbo">AGUARDANDO</div>
@endif

<!-- HEADER -->
<div class="header">
    <h2>Orçamento / Pedido</h2>
    <small>Gerado em: {{ now()->format('d/m/Y H:i') }}</small>
</div>

<!-- HEADER EMPRESA -->
<div class="header">
    <strong>{{ $orcamento->empresa->nome ?? 'EMPRESA NAO CADASTRADA' }}</strong><br>
    CNPJ: {{ $orcamento->empresa->cnpj ?? '-' }}<br>
    {{ $orcamento->empresa->endereco ?? '-' }}, {{ $orcamento->empresa->numero ?? '' }}<br>
    {{ $orcamento->empresa->cidade ?? '-' }} - {{ $orcamento->empresa->estado ?? '-' }}<br>
    Tel: {{ $orcamento->empresa->telefone ?? '-' }}
</div>

@php
    $itensAgrupados = $orcamento->itens->groupBy('produto_id');

    $totalVenda = $orcamento->itens->sum(fn($i) => $i->quantidade_solicitada * $i->preco_unitario);
    $totalEntregue = $orcamento->itens->sum(fn($i) => $i->quantidade_atendida * $i->preco_unitario);
    $totalPendente = $orcamento->itens->sum(fn($i) => $i->quantidade_pendente * $i->preco_unitario);
@endphp

<!-- DADOS -->
<div class="section">
    
    <div class="box" style="font-size: 11px; line-height: 1.4;">
        <div style="border: 1px solid gray; padding: 10px;">
            <strong>
                {{ $orcamento->status ?? 'SEM STATUS' }} 
                em: {{ \Carbon\Carbon::parse($orcamento->updated_at)->format('d/m/Y H:i') }}
            </strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Cód:</strong> #{{ $orcamento->codigo_orcamento }}  
                <strong>ID:</strong> {{ $orcamento->id }}
            </div>
            <div>
                <strong>Orçamento:</strong> 
                {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}
            </div>
        </div>

        <div>
            <strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? '-' }}  
            <strong>Tel:</strong> {{ $orcamento->cliente->telefone ?? '-' }}
        </div>

        <div>
            <strong>Prev.Entrega:</strong> 
            {{ $orcamento->itens->first()?->previsao_entrega
                ? \Carbon\Carbon::parse($orcamento->itens->first()->previsao_entrega)->format('d/m/Y H:i')
                : 'Não definido'
            }}
        </div>

        <div>
            <strong>End:</strong>
            {{ $orcamento->cliente->endereco_entrega ?? $orcamento->cliente->endereco }},
            {{ $orcamento->cliente->numero }} -
            {{ $orcamento->cliente->bairro }} -
            {{ $orcamento->cliente->cidade }}/{{ $orcamento->cliente->estado }} -
            {{ $orcamento->cliente->cep }}
        </div>

    </div>
</div>

<!-- ITENS ENTREGUES -->
<div class="section">
    <div class="section-title">Itens Entregues</div>

    <div class="table">

        <!-- HEADER -->
        <div class="tr">
            <div class="th">ID</div>
            <div class="th">Produto</div>
            <div class="th text-center">Solicitado</div>
            <div class="th text-center">Entregue</div>
            <div class="th text-center">Lote</div>
        </div>

        @foreach($itensAgrupados as $itens)

            @php
                $produto = $itens->first()->produto;

                $qtdSolicitada = $itens->sum('quantidade_solicitada');
                $qtdEntregue   = $itens->sum('quantidade_atendida');

                // 🔥 Lotes correto via pivot
                $lotesStr = $itens
                    ->flatMap(fn($item) => $item->lotes)
                    ->pluck('numero_lote')
                    ->filter()
                    ->unique()
                    ->implode(', ');
            @endphp

            @if($qtdEntregue > 0)
                <div class="tr">
                    <div class="td">{{ $produto->id ?? '-' }}</div>
                    <div class="td">{{ $produto->descricao }}</div>
                    <div class="td text-center">{{ number_format($qtdSolicitada, 2, ',', '.') }}</div>
                    <div class="td text-center">{{ number_format($qtdEntregue, 2, ',', '.') }}</div>
                    <div class="td text-center">{{ $lotesStr ?: '-' }}</div>
                </div>
            @endif

        @endforeach

    </div>
    <p class="total" style="font-size:12px;">Total: R$ {{ number_format($orcamento->total, 2, ',', '.') }}</p>
</div>

<div class="page-break"></div>

<!-- PENDENTES -->
<div class="section">
    <div class="section-title">Itens Pendentes / Não Entregues</div>
        <div style="border: 1px solid gray; padding: 10px;">
            <strong>
                {{ $orcamento->status ?? 'SEM STATUS' }} 
                em: {{ \Carbon\Carbon::parse($orcamento->updated_at)->format('d/m/Y H:i') }}
            </strong>
        </div>
    <p style="color:#aa0000; font-weight:bold;">
        ⚠ Estes itens NÃO serão entregues neste pedido.<br>
        Serão fornecidos conforme a previsão de entrega estipulada neste documento.
    </p>

    <div class="table">
        <div class="tr">
            <div class="th">ID</div>
            <div class="th">Produto</div>
            <div class="th text-center">Pendente</div>
            <div class="th text-center">Previsão</div>
        </div>

        @forelse($orcamento->itens->where('quantidade_pendente', '>', 0) as $item)
        <div class="tr">
            <div class="td">{{ $item->produto->id ?? '-' }}</div>
            <div class="td">{{ $item->produto->descricao ?? '-' }}</div>
            <div class="td text-center">{{ number_format($item->quantidade_pendente, 2, ',', '.') }}</div>
            <div class="td text-center">
                {{ $item->previsao_entrega ? \Carbon\Carbon::parse($item->previsao_entrega)->format('d/m/Y') : '-' }}
            </div>
        </div>
        @empty
        <br>
        <div class="tr" >
            <div class="td text-center text-color-info">Nenhum item pendente</div>
            <div class="td text-center text-color-info">Nenhum item pendente</div>
            <div class="td text-center text-color-info">Nenhum item pendente</div>
            <div class="td text-center text-color-info">Nenhum item pendente</div>
        </div>
        @endforelse
    </div>

  <!-- HISTÓRICO / RESUMO POR PRODUTO -->
<!-- HEADER -->
<div style="margin-top:15px; font-size:11px;">

    <div class="section-title">Resumo de Atendimento por Produto</div>

    <!-- HEADER -->
    <div style="
        width: 100%;
        border-bottom: 2px solid #000;
        padding: 6px 0;
        font-weight: bold;
        background: #f0f0f0;
    ">

        <div style="display:inline-block; width:8%;">ID</div>
        <div style="display:inline-block; width:34%;">Produto</div>
        <div style="display:inline-block; width:14%; text-align:center;">Solicitado</div>
        <div style="display:inline-block; width:14%; text-align:center;">Entregue</div>
        <div style="display:inline-block; width:10%; text-align:center;">Pendente</div>
        <div style="display:inline-block; width:16%; text-align:center;">Status</div>

    </div>

    <!-- LINHAS -->
     
    @foreach($orcamento->itens as $item)
        <div style="
            width: 100%;
            border-bottom: 1px solid #eee;
            padding: 6px 0;
        ">

            <div style="display:inline-block; width:8%;">
                {{ $item->produto_id }}
            </div>

            <div style="display:inline-block; width:34%;">
                {{ $item->produto->descricao ?? '-' }}
            </div>

            <div style="display:inline-block; width:14%; text-align:center;">
                {{ number_format($item->quantidade_solicitada, 2, ',', '.') }}
            </div>

            <div style="display:inline-block; width:14%; text-align:center; color:green;">
                {{ number_format($item->quantidade_atendida, 2, ',', '.') }}
            </div>

            <div style="display:inline-block; width:10%; text-align:center; color:#aa0000;">
                {{ number_format($item->quantidade_pendente, 2, ',', '.') }}
            </div>

            <div style="display:inline-block; width:16%; text-align:center;">
                @if($item->quantidade_pendente <= 0)
                    Concluído
                @elseif($item->quantidade_atendida > 0)
                    Parcial
                @else
                    Pendente
                @endif
            </div>

        </div>
    @endforeach

</div>

    <!-- ASSINATURA -->
    <div class="assinatura">
        <div class="assinatura-box">
            <div class="linha">
                Ciente do Cliente<br>
                Data: ____/____/____
            </div>
        </div>
    </div>

    <div class="contato">
        Em caso de dúvidas:<br>
        📞 (11) 99999-9999 | 📧 contato@empresa.com.br
    </div>
</div>

<!-- OBS -->
@if($orcamento->observacoes)
<div class="section">
    <div class="section-title">Observações</div>
    <div class="box">
        {{ $orcamento->observacoes }}
    </div>
</div>
@endif

<!-- FOOTER -->
<div class="footer">
    Documento gerado automaticamente - {{ config('app.name') }}
</div>

</body>
</html>