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

        .row-flex {
            display: flex;
            border-bottom: 1px solid #555;
            padding: 6px 0;
            font-size: 12px;
        }

        .col {
            padding-right: 5px;
        }

        .col-produto { width: 180px; }
        .col-qtd { width: 40px; }
        .col-small { width: 60px; }
        .col-status { width: 80px; }
        .col-preco { width: 70px; }

        .header-row {
            font-weight: bold;
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

    {{-- CARIMBO --}}
    @if($orcamento->status === 'Aprovado')
        <div class="carimbo aprovado">APROVADO</div>

    @elseif($orcamento->status === 'Cancelado')
        <div class="carimbo" style="color:rgba(255, 0, 0, 0.35); border-color:rgba(255, 0, 0, 0.35);">CANCELADO</div>

    @elseif($orcamento->status === 'Expirado')
        <div class="carimbo" style="color:rgba(255, 0, 0, 0.35); border-color:rgba(255, 0, 0, 0.35);">
            EXPIRADO
        </div>

    @elseif($orcamento->status === 'Aguardando Estoque')
        <div class="carimbo" style="color:rgba(255,165,0,0.25); border-color:rgba(255,165,0,0.25);">
            ESTOQUE PENDENTE
        </div>

    @else
        <div class="carimbo" style="color:rgba(0,0,0,0.15); border-color:rgba(0,0,0,0.15);">
            AGUARDANDO APROVAÇÃO
        </div>
    @endif


    {{-- HEADER --}}
    <div class="header">
        <h2>Orçamento de Cliente</h2>
        <p><strong>Depósito de Materiais - Sistema Interno</strong></p>
        <small>Gerado em: {{ now()->format('d/m/Y H:i') }}</small>
    </div>

    {{-- INFO --}}
    <div class="info">
        <div><strong>Código:</strong> #{{ $orcamento->codigo_orcamento }}</div>
        <div><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</div>
        <div><strong>Dt.Orçamento:</strong> {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</div>
        <div><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</div>
        <div><strong>Status:</strong> {{ $orcamento->status }}</div>
    </div>

    {{-- TABELA EM DIV --}}
    <div>

        {{-- HEADER --}}
        <div class="row-flex header-row">
            <div class="col col-produto">Produto</div>
            <div class="col col-qtd">Qtd</div>
            <div class="col col-small">Atend.</div>
            <div class="col col-small">Pend.</div>
            <div class="col col-status">Status</div>
            <div class="col col-preco">Unit.</div>
            <div class="col col-preco">Subtotal</div>
        </div>

        {{-- ITENS --}}
        @foreach($orcamento->itens as $item)

            <div class="row-flex">

                <div class="col col-produto">
                    {{ $item->produto->nome ?? '-' }}
                </div>

                <div class="col col-qtd">
                    {{ number_format($item->quantidade, 2, ',', '.') }}
                </div>

                <div class="col col-small">
                    {{ number_format($item->quantidade_atendida, 2, ',', '.') }}
                </div>

                <div class="col col-small">
                    {{ number_format($item->quantidade_pendente, 2, ',', '.') }}
                </div>

                <div class="col col-status">
                    @if($item->status == 'disponivel')
                        <span style="color:green;">OK</span>
                    @elseif($item->status == 'parcial')
                        <span style="color:orange;">Parcial</span>
                    @else
                        <span style="color:red;">Falta</span>
                    @endif
                </div>

                <div class="col col-preco">
                    R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                </div>

                <div class="col col-preco">
                    R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                </div>

            </div>

            {{-- PREVISÃO --}}
            @if($item->quantidade_pendente > 0)
                <div style="font-size:10px; color:#555; margin-bottom:5px;">
                    Entrega prevista:
                    {{ optional($item->previsao_entrega)->format('d/m/Y') ?? 'A definir' }}
                </div>
            @endif

        @endforeach

    </div>

    {{-- TOTAL --}}
    <p class="total">
        Total: R$ {{ number_format($orcamento->total, 2, ',', '.') }}
    </p>

    {{-- OBSERVAÇÕES --}}
    @if($orcamento->observacoes)
        <div class="observacoes">
            <strong>Observações:</strong>
            <p>{{ $orcamento->observacoes }}</p>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Documento gerado automaticamente pelo sistema - {{ config('app.name', 'Depósito de Materiais') }}
    </div>

</body>
</html>