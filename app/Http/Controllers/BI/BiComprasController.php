<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiComprasController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            auth()->check()
            && in_array(
                auth()->user()->nivel_acesso,
                ['admin', 'gerente'],
                true
            ),
            403
        );

        $dados = $request->validate([
            'data_inicio' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'data_fim' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'fornecedor_id' => [
                'nullable',
                'integer',
                'exists:fornecedores,id',
            ],
            'produto_id' => [
                'nullable',
                'integer',
                'exists:produtos,id',
            ],
        ]);

        $hoje = CarbonImmutable::today();

        $dataInicio = ! empty($dados['data_inicio'])
            ? CarbonImmutable::parse(
                $dados['data_inicio']
            )->startOfDay()
            : $hoje->startOfMonth();

        $dataFim = ! empty($dados['data_fim'])
            ? CarbonImmutable::parse(
                $dados['data_fim']
            )->endOfDay()
            : $hoje->endOfDay();

        if ($dataFim->lt($dataInicio)) {
            [$dataInicio, $dataFim] = [
                $dataFim->startOfDay(),
                $dataInicio->endOfDay(),
            ];
        }

        $fornecedorId = ! empty($dados['fornecedor_id'])
            ? (int) $dados['fornecedor_id']
            : null;

        $produtoId = ! empty($dados['produto_id'])
            ? (int) $dados['produto_id']
            : null;


        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        $fornecedores = DB::table('fornecedores')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get([
                'id',
                'nome',
            ]);

        $produtos = DB::table('produtos')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get([
                'id',
                'nome',
                'fornecedor_id',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Aplicador comum de filtros de pedido
        |--------------------------------------------------------------------------
        */

        $aplicarFiltros = function ($query) use (
            $dataInicio,
            $dataFim,
            $fornecedorId,
            $produtoId
        ) {
            $query
                ->whereBetween(
                    'pc.data_pedido',
                    [
                        $dataInicio->toDateTimeString(),
                        $dataFim->toDateTimeString(),
                    ]
                )
                ->where(
                    'pc.status',
                    '<>',
                    'cancelado'
                );

            if ($fornecedorId) {
                $query->where(
                    'pc.fornecedor_id',
                    $fornecedorId
                );
            }

            if ($produtoId) {
                $query->whereExists(
                    function ($subquery) use ($produtoId) {
                        $subquery
                            ->selectRaw('1')
                            ->from(
                                'itens_pedido_compra as filtro_ipc'
                            )
                            ->whereColumn(
                                'filtro_ipc.pedido_id',
                                'pc.id'
                            )
                            ->where(
                                'filtro_ipc.produto_id',
                                $produtoId
                            );
                    }
                );
            }

            return $query;
        };


        /*
        |--------------------------------------------------------------------------
        | Pedidos filtrados
        |--------------------------------------------------------------------------
        */

        $pedidosBase = DB::table(
            'pedido_compras as pc'
        );

        $aplicarFiltros($pedidosBase);

        $resumo = (clone $pedidosBase)
            ->selectRaw('
                COUNT(*) AS total_pedidos,
                COUNT(DISTINCT pc.fornecedor_id)
                    AS fornecedores_ativos,
                COALESCE(SUM(pc.total), 0)
                    AS valor_total,
                MAX(pc.data_pedido)
                    AS ultima_compra
            ')
            ->first();

        $pedidosRecebidos = (clone $pedidosBase)
            ->where(
                'pc.status',
                'recebido'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Valor específico de produto quando filtrado
        |--------------------------------------------------------------------------
        */

        if ($produtoId) {

            $queryValorProduto = DB::table(
                'itens_pedido_compra as ipc'
            )
                ->join(
                    'pedido_compras as pc',
                    'pc.id',
                    '=',
                    'ipc.pedido_id'
                )
                ->where(
                    'ipc.produto_id',
                    $produtoId
                );

            $aplicarFiltros($queryValorProduto);

            $resumo->valor_total =
                (float) $queryValorProduto
                    ->sum('ipc.subtotal');
        }


        $ticketMedio =
            ((int) ($resumo->total_pedidos ?? 0)) > 0
                ? (
                    (float) ($resumo->valor_total ?? 0)
                    / (int) $resumo->total_pedidos
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | Ranking de fornecedores
        |--------------------------------------------------------------------------
        */

        $queryRanking = DB::table(
            'pedido_compras as pc'
        )
            ->join(
                'fornecedores as f',
                'f.id',
                '=',
                'pc.fornecedor_id'
            );

        $aplicarFiltros($queryRanking);

        $rankingFornecedores =
            $queryRanking
                ->groupBy(
                    'f.id',
                    'f.nome'
                )
                ->selectRaw('
                    f.id,
                    f.nome,
                    COUNT(DISTINCT pc.id)
                        AS pedidos,
                    COALESCE(SUM(pc.total), 0)
                        AS valor_comprado,
                    MAX(pc.data_pedido)
                        AS ultima_compra
                ')
                ->get();

        foreach ($rankingFornecedores as $fornecedor) {

            $queryItens = DB::table(
                'itens_pedido_compra as ipc'
            )
                ->join(
                    'pedido_compras as pc',
                    'pc.id',
                    '=',
                    'ipc.pedido_id'
                )
                ->where(
                    'pc.fornecedor_id',
                    $fornecedor->id
                );

            $aplicarFiltros($queryItens);

            if ($produtoId) {

                $queryItens->where(
                    'ipc.produto_id',
                    $produtoId
                );

                $fornecedor->valor_comprado =
                    (float) (clone $queryItens)
                        ->sum('ipc.subtotal');
            }

            $fornecedor->produtos_comprados =
                (clone $queryItens)
                    ->distinct()
                    ->count('ipc.produto_id');

            $fornecedor->quantidade_itens =
                (float) (clone $queryItens)
                    ->sum('ipc.quantidade');
        }

        $maxPedidos = max(
            1,
            (int) (
                $rankingFornecedores
                    ->max('pedidos')
                ?? 1
            )
        );

        $maxValor = max(
            1,
            (float) (
                $rankingFornecedores
                    ->max('valor_comprado')
                ?? 1
            )
        );

        $rankingFornecedores =
            $rankingFornecedores
                ->map(function ($item) use (
                    $maxPedidos,
                    $maxValor,
                    $dataFim
                ) {

                    $ultimaCompra =
                        $item->ultima_compra
                            ? CarbonImmutable::parse(
                                $item->ultima_compra
                            )
                            : null;

                    $diasSemCompra =
                        $ultimaCompra
                            ? (int) round(
                                $ultimaCompra
                                    ->startOfDay()
                                    ->diffInDays(
                                        $dataFim->startOfDay()
                                    )
                            )
                            : 999;

                    if ($diasSemCompra > 30) {

                        $periodoSemCompra =
                            number_format(
                                $diasSemCompra / 30,
                                1,
                                ',',
                                '.'
                            )
                            . ' meses';

                    } else {

                        $periodoSemCompra =
                            $diasSemCompra
                            . ' '
                            . (
                                $diasSemCompra === 1
                                    ? 'dia'
                                    : 'dias'
                            );
                    }

                    $scoreFrequencia =
                        min(
                            100,
                            (
                                $item->pedidos
                                / $maxPedidos
                            ) * 100
                        );

                    $scoreValor =
                        min(
                            100,
                            (
                                $item->valor_comprado
                                / $maxValor
                            ) * 100
                        );

                    $scoreRecencia = match (true) {
                        $diasSemCompra <= 7 => 100,
                        $diasSemCompra <= 15 => 85,
                        $diasSemCompra <= 30 => 70,
                        $diasSemCompra <= 60 => 45,
                        $diasSemCompra <= 90 => 25,
                        default => 5,
                    };

                    $indice = round(
                        ($scoreFrequencia * 0.50)
                        + ($scoreValor * 0.30)
                        + ($scoreRecencia * 0.20)
                    );

                    $classificacao = match (true) {
                        $indice >= 80 => 'Muito ativo',
                        $indice >= 60 => 'Ativo',
                        $indice >= 40 => 'Moderado',
                        $indice >= 20 => 'Pouco ativo',
                        default => 'Baixa atividade',
                    };

                    $item->dias_sem_compra =
                        $diasSemCompra;

                    $item->periodo_sem_compra =
                        $periodoSemCompra;

                    $item->indice_atividade =
                        $indice;

                    $item->classificacao =
                        $classificacao;

                    return $item;
                })
                ->sortByDesc(
                    'indice_atividade'
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Manchetes de fornecedor
        |--------------------------------------------------------------------------
        */

        $fornecedorDestaque =
            $rankingFornecedores->first();

        $fornecedorParado =
            $rankingFornecedores
                ->sortByDesc(
                    'dias_sem_compra'
                )
                ->first();

        $fornecedorMaiorValor =
            $rankingFornecedores
                ->sortByDesc(
                    'valor_comprado'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Produtos
        |--------------------------------------------------------------------------
        */

        $queryProdutos = DB::table(
            'itens_pedido_compra as ipc'
        )
            ->join(
                'pedido_compras as pc',
                'pc.id',
                '=',
                'ipc.pedido_id'
            )
            ->join(
                'produtos as p',
                'p.id',
                '=',
                'ipc.produto_id'
            );

        $aplicarFiltros($queryProdutos);

        if ($produtoId) {
            $queryProdutos->where(
                'ipc.produto_id',
                $produtoId
            );
        }

        $produtosMaisComprados =
            $queryProdutos
                ->groupBy(
                    'p.id',
                    'p.nome'
                )
                ->selectRaw('
                    p.id,
                    p.nome,
                    COALESCE(
                        SUM(ipc.quantidade),
                        0
                    ) AS quantidade,
                    COALESCE(
                        SUM(ipc.subtotal),
                        0
                    ) AS valor_total,
                    COALESCE(
                        SUM(ipc.subtotal)
                        / NULLIF(
                            SUM(ipc.quantidade),
                            0
                        ),
                        0
                    ) AS custo_medio
                ')
                ->orderByDesc(
                    'quantidade'
                )
                ->limit(10)
                ->get();

        $produtoMaisComprado =
            $produtosMaisComprados
                ->sortByDesc('quantidade')
                ->first();

        $produtoMaiorGasto =
            $produtosMaisComprados
                ->sortByDesc('valor_total')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Custo médio geral ponderado
        |--------------------------------------------------------------------------
        */

        $queryCustoMedio = DB::table(
            'itens_pedido_compra as ipc'
        )
            ->join(
                'pedido_compras as pc',
                'pc.id',
                '=',
                'ipc.pedido_id'
            );

        $aplicarFiltros($queryCustoMedio);

        if ($produtoId) {
            $queryCustoMedio->where(
                'ipc.produto_id',
                $produtoId
            );
        }

        $custoGeral = $queryCustoMedio
            ->selectRaw('
                COALESCE(
                    SUM(ipc.subtotal)
                    / NULLIF(
                        SUM(ipc.quantidade),
                        0
                    ),
                    0
                ) AS custo_medio
            ')
            ->first();

        $custoMedioGeral =
            (float) (
                $custoGeral->custo_medio
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Maior variação de custo
        |--------------------------------------------------------------------------
        */

        $queryVariacao = DB::table(
            'itens_pedido_compra as ipc'
        )
            ->join(
                'pedido_compras as pc',
                'pc.id',
                '=',
                'ipc.pedido_id'
            )
            ->join(
                'produtos as p',
                'p.id',
                '=',
                'ipc.produto_id'
            );

        $aplicarFiltros($queryVariacao);

        if ($produtoId) {
            $queryVariacao->where(
                'ipc.produto_id',
                $produtoId
            );
        }

        $variacoes =
            $queryVariacao
                ->groupBy(
                    'p.id',
                    'p.nome'
                )
                ->selectRaw('
                    p.id,
                    p.nome,
                    MIN(ipc.valor_unitario)
                        AS menor_custo,
                    MAX(ipc.valor_unitario)
                        AS maior_custo
                ')
                ->get()
                ->map(function ($item) {

                    $menor =
                        (float) $item->menor_custo;

                    $maior =
                        (float) $item->maior_custo;

                    $item->variacao_percentual =
                        $menor > 0
                            ? (
                                (
                                    $maior - $menor
                                )
                                / $menor
                            ) * 100
                            : 0;

                    return $item;
                });

        $maiorVariacao =
            $variacoes
                ->sortByDesc(
                    'variacao_percentual'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Últimos pedidos
        |--------------------------------------------------------------------------
        */

        $queryUltimos = DB::table(
            'pedido_compras as pc'
        )
            ->join(
                'fornecedores as f',
                'f.id',
                '=',
                'pc.fornecedor_id'
            );

        $aplicarFiltros($queryUltimos);

        $ultimosPedidos =
            $queryUltimos
                ->select([
                    'pc.id',
                    'f.nome as fornecedor',
                    'pc.data_pedido',
                    'pc.total',
                    'pc.status',
                ])
                ->orderByDesc(
                    'pc.data_pedido'
                )
                ->limit(15)
                ->get();

        $ultimaCompra =
            $ultimosPedidos->first();


        /*
        |--------------------------------------------------------------------------
        | Histórico mensal
        |--------------------------------------------------------------------------
        */

        $queryHistorico = DB::table(
            'pedido_compras as pc'
        )
            ->where(
                'pc.status',
                '<>',
                'cancelado'
            )
            ->where(
                'pc.data_pedido',
                '>=',
                $hoje
                    ->subMonths(11)
                    ->startOfMonth()
            );

        if ($fornecedorId) {
            $queryHistorico->where(
                'pc.fornecedor_id',
                $fornecedorId
            );
        }

        if ($produtoId) {

            $queryHistorico->whereExists(
                function ($subquery) use ($produtoId) {

                    $subquery
                        ->selectRaw('1')
                        ->from(
                            'itens_pedido_compra as hist_ipc'
                        )
                        ->whereColumn(
                            'hist_ipc.pedido_id',
                            'pc.id'
                        )
                        ->where(
                            'hist_ipc.produto_id',
                            $produtoId
                        );
                }
            );
        }

        $historicoMensal =
            $queryHistorico
                ->groupByRaw(
                    'YEAR(pc.data_pedido),
                     MONTH(pc.data_pedido)'
                )
                ->orderByRaw(
                    'YEAR(pc.data_pedido),
                     MONTH(pc.data_pedido)'
                )
                ->selectRaw('
                    YEAR(pc.data_pedido) AS ano,
                    MONTH(pc.data_pedido) AS mes,
                    COUNT(*) AS pedidos,
                    COALESCE(
                        SUM(pc.total),
                        0
                    ) AS valor_total
                ')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Acareação
        |--------------------------------------------------------------------------
        */

        $queryAcareacao = DB::table(
            'pedido_compras as pc'
        )
            ->leftJoin(
                'itens_pedido_compra as ipc',
                'ipc.pedido_id',
                '=',
                'pc.id'
            );

        $aplicarFiltros($queryAcareacao);

        $divergenciasPedidos =
            $queryAcareacao
                ->groupBy(
                    'pc.id',
                    'pc.total'
                )
                ->havingRaw(
                    'ABS(
                        pc.total
                        - COALESCE(
                            SUM(ipc.subtotal),
                            0
                        )
                    ) > 0.01'
                )
                ->selectRaw('pc.id')
                ->get()
                ->count();


        return view(
            'bi.compras.index',
            compact(
                'dataInicio',
                'dataFim',
                'fornecedorId',
                'produtoId',
                'fornecedores',
                'produtos',
                'resumo',
                'pedidosRecebidos',
                'ticketMedio',
                'rankingFornecedores',
                'fornecedorDestaque',
                'fornecedorParado',
                'fornecedorMaiorValor',
                'produtosMaisComprados',
                'produtoMaisComprado',
                'produtoMaiorGasto',
                'custoMedioGeral',
                'maiorVariacao',
                'variacoes',
                'ultimaCompra',
                'ultimosPedidos',
                'historicoMensal',
                'divergenciasPedidos'
            )
        );
    }
}