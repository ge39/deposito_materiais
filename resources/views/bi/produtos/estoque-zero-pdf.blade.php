<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        BI - Produtos com estoque zero
    </title>

    <style>

        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        h1 {
            margin: 0 0 4px 0;
            font-size: 17px;
        }

        .subtitulo {
            color: #666;
            margin-bottom: 12px;
        }

        .resumo {
            border: 1px solid #bbb;
            padding: 8px;
            margin-bottom: 10px;
        }

        .cabecalho,
        .linha {
            width: 100%;
            clear: both;
        }

        .cabecalho {
            background: #eeeeee;
            font-weight: bold;
            border-top: 1px solid #999;
            border-bottom: 1px solid #999;
        }

        .linha {
            border-bottom: 1px solid #dddddd;
        }

        .col {
            display: inline-block;
            vertical-align: top;
            padding: 5px 3px;
            box-sizing: border-box;
        }

        .produto {
            width: 24%;
        }

        .categoria {
            width: 16%;
        }

        .estoque {
            width: 8%;
            text-align: right;
        }

        .minimo {
            width: 8%;
            text-align: right;
        }

        .ultima {
            width: 11%;
        }

        .dias {
            width: 9%;
            text-align: right;
        }

        .promocao {
            width: 8%;
        }

        .situacao {
            width: 14%;
        }

        .rodape {
            margin-top: 12px;
            font-size: 8px;
            color: #666;
        }

    </style>

</head>

<body>

    <h1>
        BI — Produtos com estoque zero
    </h1>

    <div class="subtitulo">

        Relação para análise e futura tomada de decisão.

    </div>


    <div class="resumo">

        <strong>
            Total:
        </strong>

        {{ $produtos->count() }}
        produto(s).

        Produtos zerados são exibidos neste relatório,
        mas não entram no valor financeiro do estoque.

    </div>


    <div class="cabecalho">

        <div class="col produto">
            Produto
        </div>

        <div class="col categoria">
            Categoria
        </div>

        <div class="col estoque">
            Estoque
        </div>

        <div class="col minimo">
            Mínimo
        </div>

        <div class="col ultima">
            Última venda
        </div>

        <div class="col dias">
            Dias
        </div>

        <div class="col promocao">
            Promoção
        </div>

        <div class="col situacao">
            Situação
        </div>

    </div>


    @foreach ($produtos as $produto)

        <div class="linha">

            <div class="col produto">
                {{ $produto->nome }}
            </div>

            <div class="col categoria">
                {{
                    $produto->categoria
                    ?? 'Sem categoria'
                }}
            </div>

            <div class="col estoque">
                {{
                    number_format(
                        $produto->estoque_disponivel,
                        3,
                        ',',
                        '.'
                    )
                }}
            </div>

            <div class="col minimo">
                {{
                    number_format(
                        $produto->estoque_minimo,
                        3,
                        ',',
                        '.'
                    )
                }}
            </div>

            <div class="col ultima">

                {{
                    $produto->ultima_venda
                        ? \Carbon\Carbon::parse(
                            $produto->ultima_venda
                        )->format('d/m/Y')
                        : 'Nunca'
                }}

            </div>

            <div class="col dias">

                {{
                    $produto->dias_sem_venda
                    === null
                        ? '-'
                        : $produto->dias_sem_venda
                }}

            </div>

            <div class="col promocao">

                {{
                    (int) $produto->em_promocao
                    === 1
                        ? 'Sim'
                        : 'Não'
                }}

            </div>

            <div class="col situacao">
                {{ $produto->situacao }}
            </div>

        </div>

    @endforeach


    <div class="rodape">

        Gerado em
        {{ $geradoEm->format('d/m/Y H:i:s') }}.

    </div>

</body>

</html>