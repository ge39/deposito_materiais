<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiOrcamentosController extends Controller
{
    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'string', 'max:50'],
            'cliente_id' => ['nullable', 'integer'],
            'produto_id' => ['nullable', 'integer'],
            'bairro' => ['nullable', 'string', 'max:150'],
        ]);

        $hoje = CarbonImmutable::today();

        $inicio = ! empty($dados['data_inicio'])
            ? CarbonImmutable::parse($dados['data_inicio'])
            : $hoje->startOfMonth();

        $fim = ! empty($dados['data_fim'])
            ? CarbonImmutable::parse($dados['data_fim'])
            : $hoje;

        if ($fim->lt($inicio)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $status = $dados['status'] ?? null;

        $clienteId = ! empty($dados['cliente_id'])
            ? (int) $dados['cliente_id']
            : null;

        $produtoId = ! empty($dados['produto_id'])
            ? (int) $dados['produto_id']
            : null;

        $bairro = trim((string) ($dados['bairro'] ?? ''));


        /*
        |--------------------------------------------------------------------------
        | Filtro base de orçamentos
        |--------------------------------------------------------------------------
        */

        $aplicarFiltros = function ($query) use (
            $inicio,
            $fim,
            $status,
            $clienteId,
            $produtoId,
            $bairro
        ) {
            $query->whereBetween(
                'o.data_orcamento',
                [
                    $inicio->toDateString(),
                    $fim->toDateString(),
                ]
            );

            if ($status) {
                $query->where('o.status', $status);
            }

            if ($clienteId) {
                $query->where('o.cliente_id', $clienteId);
            }

            if ($produtoId) {
                $query->whereExists(function ($sub) use ($produtoId) {
                    $sub->selectRaw('1')
                        ->from('item_orcamentos as iof')
                        ->whereColumn('iof.orcamento_id', 'o.id')
                        ->where('iof.produto_id', $produtoId);
                });
            }

            if ($bairro !== '') {
                $query->where(function ($q) use ($bairro) {
                    $q->where('o.bairro_entrega', $bairro)
                        ->orWhereExists(function ($sub) use ($bairro) {
                            $sub->selectRaw('1')
                                ->from('clientes as cb')
                                ->whereColumn('cb.id', 'o.cliente_id')
                                ->where('cb.bairro', $bairro);
                        });
                });
            }

            return $query;
        };


        $base = $aplicarFiltros(
            DB::table('orcamentos as o')
        );


        /*
        |--------------------------------------------------------------------------
        | KPIs principais
        |--------------------------------------------------------------------------
        */

        $quantidadeOrcamentos = (int) (clone $base)->count();

        $valorTotalOrcado = (float) (
            (clone $base)->sum('o.total') ?? 0
        );

        $ticketMedioOrcado = $quantidadeOrcamentos > 0
            ? $valorTotalOrcado / $quantidadeOrcamentos
            : 0;

        $quantidadeFaturados = (int) (
            (clone $base)
                ->where('o.status', 'Faturado')
                ->count()
        );

        $valorFaturado = (float) (
            (clone $base)
                ->where('o.status', 'Faturado')
                ->sum('o.total') ?? 0
        );

        $taxaConversao = $quantidadeOrcamentos > 0
            ? ($quantidadeFaturados / $quantidadeOrcamentos) * 100
            : 0;

        $quantidadeEmAberto = (int) (
            (clone $base)
                ->whereIn('o.status', [
                    'Aguardando Aprovacao',
                    'Aguardando Estoque',
                    'Aprovado',
                ])
                ->count()
        );

        $valorEmAberto = (float) (
            (clone $base)
                ->whereIn('o.status', [
                    'Aguardando Aprovacao',
                    'Aguardando Estoque',
                    'Aprovado',
                ])
                ->sum('o.total') ?? 0
        );

        $quantidadePerdidos = (int) (
            (clone $base)
                ->whereIn('o.status', [
                    'Cancelado',
                    'Expirado',
                ])
                ->count()
        );

        $valorPerdido = (float) (
            (clone $base)
                ->whereIn('o.status', [
                    'Cancelado',
                    'Expirado',
                ])
                ->sum('o.total') ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | Ticket médio vendido
        |--------------------------------------------------------------------------
        | Comparação comercial do mesmo período.
        | Não depende de vendas.orcamento_id.
        |--------------------------------------------------------------------------
        */

        $vendasBase = DB::table('vendas as v')
            ->where('v.status', 'finalizada')
            ->whereDate('v.data_venda', '>=', $inicio->toDateString())
            ->whereDate('v.data_venda', '<=', $fim->toDateString());

        if ($clienteId) {
            $vendasBase->where('v.cliente_id', $clienteId);
        }

        $quantidadeVendas = (int) (clone $vendasBase)->count();

        $valorVendido = (float) (
            (clone $vendasBase)->sum('v.total') ?? 0
        );

        $ticketMedioVendido = $quantidadeVendas > 0
            ? $valorVendido / $quantidadeVendas
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Cliente destaque
        |--------------------------------------------------------------------------
        */

        $clienteDestaque = (clone $base)
            ->join('clientes as c', 'c.id', '=', 'o.cliente_id')
            ->select(
                'c.id',
                'c.nome'
            )
            ->selectRaw('COUNT(o.id) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->groupBy('c.id', 'c.nome')
            ->orderByDesc('valor_total')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Itens de orçamento
        |--------------------------------------------------------------------------
        */

        $itensOrcados = DB::table('item_orcamentos as io')
            ->join('orcamentos as o', 'o.id', '=', 'io.orcamento_id');

        $itensOrcados = $aplicarFiltros($itensOrcados);

        if ($produtoId) {
            $itensOrcados->where('io.produto_id', $produtoId);
        }

        $produtoMaisOrcado = (clone $itensOrcados)
            ->join('produtos as p', 'p.id', '=', 'io.produto_id')
            ->select('p.id', 'p.nome')
            ->selectRaw(
                'COALESCE(SUM(io.quantidade_solicitada),0) as quantidade'
            )
            ->selectRaw(
                'COALESCE(SUM(io.subtotal),0) as valor_total'
            )
            ->groupBy('p.id', 'p.nome')
            ->orderByDesc('quantidade')
            ->first();

        $descontoTotal = (float) (
            (clone $itensOrcados)
                ->sum('io.valor_desconto') ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | Funil por status
        |--------------------------------------------------------------------------
        */

        $funil = (clone $base)
            ->select('o.status')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->groupBy('o.status')
            ->get()
            ->keyBy('status');

        $statusFunil = [
            'Aguardando Aprovacao',
            'Aguardando Estoque',
            'Aprovado',
            'Faturado',
            'Expirado',
            'Cancelado',
        ];


        /*
        |--------------------------------------------------------------------------
        | Ranking de clientes
        |--------------------------------------------------------------------------
        */

        $rankingClientes = (clone $base)
            ->join('clientes as c', 'c.id', '=', 'o.cliente_id')
            ->select(
                'c.id',
                'c.nome',
                'c.bairro',
                'c.cidade',
                'c.estado'
            )
            ->selectRaw('COUNT(o.id) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->selectRaw('COALESCE(AVG(o.total),0) as ticket_medio')
            ->groupBy(
                'c.id',
                'c.nome',
                'c.bairro',
                'c.cidade',
                'c.estado'
            )
            ->orderByDesc('valor_total')
            ->limit(20)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Orçado por produto
        |--------------------------------------------------------------------------
        */

        $orcadoProdutos = (clone $itensOrcados)
            ->join('produtos as p', 'p.id', '=', 'io.produto_id')
            ->select(
                'p.id',
                'p.nome'
            )
            ->selectRaw(
                'COALESCE(SUM(io.quantidade_solicitada),0) as quantidade_orcada'
            )
            ->selectRaw(
                'COALESCE(SUM(io.subtotal),0) as valor_orcado'
            )
            ->groupBy('p.id', 'p.nome')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Vendido por produto
        |--------------------------------------------------------------------------
        */

        $vendidoProdutosQuery = DB::table('item_vendas as iv')
            ->join('vendas as v', 'v.id', '=', 'iv.venda_id')
            ->join('produtos as p', 'p.id', '=', 'iv.produto_id')
            ->where('v.status', 'finalizada')
            ->whereDate('v.data_venda', '>=', $inicio->toDateString())
            ->whereDate('v.data_venda', '<=', $fim->toDateString());

        if ($clienteId) {
            $vendidoProdutosQuery->where('v.cliente_id', $clienteId);
        }

        if ($produtoId) {
            $vendidoProdutosQuery->where('iv.produto_id', $produtoId);
        }

        $vendidoProdutos = $vendidoProdutosQuery
            ->select(
                'p.id',
                'p.nome'
            )
            ->selectRaw(
                'COALESCE(SUM(iv.quantidade),0) as quantidade_vendida'
            )
            ->selectRaw(
                'COALESCE(SUM(iv.subtotal),0) as valor_vendido'
            )
            ->groupBy('p.id', 'p.nome')
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | Comparativo Orçado x Vendido
        |--------------------------------------------------------------------------
        */

        $comparativoProdutos = $orcadoProdutos
            ->map(function ($produto) use ($vendidoProdutos) {

                $vendido = $vendidoProdutos->get($produto->id);

                $quantidadeOrcada =
                    (float) $produto->quantidade_orcada;

                $quantidadeVendida =
                    (float) ($vendido->quantidade_vendida ?? 0);

                return (object) [
                    'id' => $produto->id,
                    'nome' => $produto->nome,

                    'quantidade_orcada' =>
                        $quantidadeOrcada,

                    'valor_orcado' =>
                        (float) $produto->valor_orcado,

                    'quantidade_vendida' =>
                        $quantidadeVendida,

                    'valor_vendido' =>
                        (float) ($vendido->valor_vendido ?? 0),

                    'indice_realizacao' =>
                        $quantidadeOrcada > 0
                            ? ($quantidadeVendida / $quantidadeOrcada) * 100
                            : 0,
                ];
            })
            ->sortByDesc('valor_orcado')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Curva ABC
        |--------------------------------------------------------------------------
        */

        $criarAbc = function (
            Collection $dados,
            string $campoValor
        ): Collection {

            $ordenados = $dados
                ->sortByDesc($campoValor)
                ->values();

            $total = (float) $ordenados->sum($campoValor);

            $acumulado = 0;

            return $ordenados->map(
                function ($item) use (
                    &$acumulado,
                    $total,
                    $campoValor
                ) {

                    $valor = (float) $item->{$campoValor};

                    $percentual = $total > 0
                        ? ($valor / $total) * 100
                        : 0;

                    $antes = $acumulado;
                    $acumulado += $percentual;

                    if ($antes < 80) {
                        $classe = 'A';
                    } elseif ($antes < 95) {
                        $classe = 'B';
                    } else {
                        $classe = 'C';
                    }

                    return (object) [
                        'nome' => $item->nome,
                        'valor' => $valor,
                        'percentual' => $percentual,
                        'acumulado' => $acumulado,
                        'classe' => $classe,
                    ];
                }
            );
        };

        $abcOrcado = $criarAbc(
            $orcadoProdutos,
            'valor_orcado'
        );

        $dadosVendidosAbc = $vendidoProdutos
            ->values()
            ->map(function ($item) {
                return (object) [
                    'nome' => $item->nome,
                    'valor_vendido' =>
                        (float) $item->valor_vendido,
                ];
            });

        $abcVendido = $criarAbc(
            $dadosVendidosAbc,
            'valor_vendido'
        );


        /*
        |--------------------------------------------------------------------------
        | Análise temporal - dia da semana
        |--------------------------------------------------------------------------
        */

        $diasSemana = (clone $base)
            ->selectRaw('DAYOFWEEK(o.data_orcamento) as numero_dia')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->groupBy('numero_dia')
            ->orderBy('numero_dia')
            ->get();

        $nomesDias = [
            1 => 'Domingo',
            2 => 'Segunda',
            3 => 'Terça',
            4 => 'Quarta',
            5 => 'Quinta',
            6 => 'Sexta',
            7 => 'Sábado',
        ];

        $diasSemana = $diasSemana->map(
            function ($linha) use ($nomesDias) {
                $linha->dia =
                    $nomesDias[(int) $linha->numero_dia] ?? '-';

                return $linha;
            }
        );

        $maiorDiaQuantidade = max(
            1,
            (int) ($diasSemana->max('quantidade') ?? 1)
        );


        /*
        |--------------------------------------------------------------------------
        | Análise temporal - horário de criação
        |--------------------------------------------------------------------------
        */

        $horarios = (clone $base)
            ->whereNotNull('o.created_at')
            ->selectRaw('HOUR(o.created_at) as hora')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        $maiorHoraQuantidade = max(
            1,
            (int) ($horarios->max('quantidade') ?? 1)
        );


        /*
        |--------------------------------------------------------------------------
        | Região / bairro
        |--------------------------------------------------------------------------
        */

        $regioesBase = (clone $base)
            ->join('clientes as cr', 'cr.id', '=', 'o.cliente_id')
            ->selectRaw(
                "COALESCE(NULLIF(o.bairro_entrega,''), NULLIF(cr.bairro,''), 'Não informado') as bairro"
            )
            ->addSelect('o.total');

        $regioes = DB::query()
            ->fromSub($regioesBase, 'rb')
            ->select('rb.bairro')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(rb.total),0) as valor_total')
            ->groupBy('rb.bairro')
            ->orderByDesc('valor_total')
            ->limit(15)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Evolução mensal
        |--------------------------------------------------------------------------
        */

        $evolucaoMensal = (clone $base)
            ->selectRaw(
                "DATE_FORMAT(o.data_orcamento, '%Y-%m') as competencia"
            )
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(o.total),0) as valor_total')
            ->groupBy('competencia')
            ->orderBy('competencia')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Acareação
        |--------------------------------------------------------------------------
        */

        $somaItens = DB::table('item_orcamentos')
            ->select('orcamento_id')
            ->selectRaw(
                'COALESCE(SUM(subtotal),0) as total_itens'
            )
            ->groupBy('orcamento_id');

        $acareacao = DB::table('orcamentos as o')
            ->leftJoinSub(
                $somaItens,
                'si',
                'si.orcamento_id',
                '=',
                'o.id'
            );

        $acareacao = $aplicarFiltros($acareacao);

        $detalhesDivergencias = (clone $acareacao)
            ->whereRaw(
                'ABS(COALESCE(o.total,0) - COALESCE(si.total_itens,0)) > 0.01'
            )
            ->select(
                'o.id',
                'o.codigo_orcamento',
                'o.data_orcamento',
                'o.status',
                'o.total'
            )
            ->selectRaw(
                'COALESCE(si.total_itens,0) as total_itens'
            )
            ->selectRaw(
                'COALESCE(o.total,0) - COALESCE(si.total_itens,0) as diferenca'
            )
            ->orderByDesc('o.data_orcamento')
            ->get();

        $divergencias =
            $detalhesDivergencias->count();


        /*
        |--------------------------------------------------------------------------
        | Lista documental dos orçamentos
        |--------------------------------------------------------------------------
        */

        $listaQuery = DB::table('orcamentos as o')
            ->join('clientes as c', 'c.id', '=', 'o.cliente_id');

        $listaQuery = $aplicarFiltros($listaQuery);

        $orcamentosLista = $listaQuery
            ->select(
                'o.id',
                'o.codigo_orcamento',
                'o.data_orcamento',
                'o.validade',
                'o.status',
                'o.tipo_entrega',
                'o.valor_frete',
                'o.total',
                'o.observacoes',
                'o.responsavel_recebimento',
                'o.telefone_recebimento',
                'o.endereco_entrega',
                'o.bairro_entrega',
                'c.nome as cliente_nome'
            )
            ->orderByDesc('o.data_orcamento')
            ->orderByDesc('o.id')
            ->get();

        $idsOrcamentos = $orcamentosLista
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $itensPorOrcamento = collect();

        if (! empty($idsOrcamentos)) {

            $itensPorOrcamento =
                DB::table('item_orcamentos as io')
                    ->join(
                        'produtos as p',
                        'p.id',
                        '=',
                        'io.produto_id'
                    )
                    ->whereIn(
                        'io.orcamento_id',
                        $idsOrcamentos
                    )
                    ->select(
                        'io.id',
                        'io.orcamento_id',
                        'p.nome as produto_nome',
                        'io.quantidade_solicitada',
                        'io.quantidade_atendida',
                        'io.quantidade_pendente',
                        'io.preco_unitario',
                        'io.preco_liquido',
                        'io.desconto_percentual',
                        'io.valor_desconto',
                        'io.subtotal',
                        'io.status as item_status'
                    )
                    ->orderBy('io.orcamento_id')
                    ->orderBy('p.nome')
                    ->get()
                    ->groupBy('orcamento_id');
        }


        /*
        |--------------------------------------------------------------------------
        | Combos dos filtros
        |--------------------------------------------------------------------------
        */

        $clientes = DB::table('clientes')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        $produtos = DB::table('produtos')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        $bairros = DB::table('clientes')
            ->whereNotNull('bairro')
            ->where('bairro', '<>', '')
            ->distinct()
            ->orderBy('bairro')
            ->pluck('bairro');

        $statusDisponiveis = [
            'Aguardando Aprovacao',
            'Aguardando Estoque',
            'Aprovado',
            'Faturado',
            'Expirado',
            'Cancelado',
        ];


        return view(
            'bi.orcamentos.index',
            compact(
                'inicio',
                'fim',
                'status',
                'clienteId',
                'produtoId',
                'bairro',

                'quantidadeOrcamentos',
                'valorTotalOrcado',
                'ticketMedioOrcado',

                'quantidadeFaturados',
                'valorFaturado',
                'taxaConversao',

                'quantidadeVendas',
                'valorVendido',
                'ticketMedioVendido',

                'quantidadeEmAberto',
                'valorEmAberto',

                'quantidadePerdidos',
                'valorPerdido',

                'clienteDestaque',
                'produtoMaisOrcado',
                'descontoTotal',

                'funil',
                'statusFunil',

                'rankingClientes',
                'comparativoProdutos',
                'abcOrcado',
                'abcVendido',

                'diasSemana',
                'maiorDiaQuantidade',
                'horarios',
                'maiorHoraQuantidade',

                'regioes',
                'evolucaoMensal',

                'divergencias',
                'detalhesDivergencias',

                'orcamentosLista',
                'itensPorOrcamento',

                'clientes',
                'produtos',
                'bairros',
                'statusDisponiveis'
            )
        );
    }
}