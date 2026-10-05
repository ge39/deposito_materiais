<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiVendasController extends Controller
{
    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
            'cliente_id' => ['nullable', 'integer'],
            'produto_id' => ['nullable', 'integer'],
            'responsavel_id' => ['nullable', 'integer'],
        ]);

        $hoje = CarbonImmutable::today();

        $dataInicio = ! empty($dados['data_inicio'])
            ? CarbonImmutable::parse($dados['data_inicio'])->startOfDay()
            : $hoje->startOfMonth()->startOfDay();

        $dataFim = ! empty($dados['data_fim'])
            ? CarbonImmutable::parse($dados['data_fim'])->endOfDay()
            : $hoje->endOfDay();

        if ($dataFim->lessThan($dataInicio)) {
            $inicioOriginal = $dataInicio;

            $dataInicio = $dataFim->startOfDay();
            $dataFim = $inicioOriginal->endOfDay();
        }

        $clienteId = ! empty($dados['cliente_id'])
            ? (int) $dados['cliente_id']
            : null;

        $produtoId = ! empty($dados['produto_id'])
            ? (int) $dados['produto_id']
            : null;

        $responsavelId = ! empty($dados['responsavel_id'])
            ? (int) $dados['responsavel_id']
            : null;


        /*
        |--------------------------------------------------------------------------
        | Combos de filtro
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

        $responsaveis = DB::table('users as u')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'u.funcionario_id')
            ->select([
                'u.id',
                'u.name as usuario',
                'f.nome as funcionario',
                'f.funcao',
            ])
            ->where(function ($query) {
                $query
                    ->where('u.ativo', 1)
                    ->orWhereNull('u.ativo');
            })
            ->orderByRaw('COALESCE(f.nome, u.name)')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Resumo do período
        |--------------------------------------------------------------------------
        */

        $resumo = $this->metricasPeriodo(
            $dataInicio,
            $dataFim,
            $clienteId,
            $produtoId,
            $responsavelId
        );

        $diasPeriodo = $dataInicio
            ->startOfDay()
            ->diffInDays($dataFim->startOfDay()) + 1;

        $periodoAnteriorFim = $dataInicio->subSecond();

        $periodoAnteriorInicio = $dataInicio
            ->subDays($diasPeriodo)
            ->startOfDay();

        $resumoAnterior = $this->metricasPeriodo(
            $periodoAnteriorInicio,
            $periodoAnteriorFim,
            $clienteId,
            $produtoId,
            $responsavelId
        );

        $variacaoFaturamento = null;

        if ($resumoAnterior->faturamento > 0) {
            $variacaoFaturamento = (
                ($resumo->faturamento - $resumoAnterior->faturamento)
                / $resumoAnterior->faturamento
            ) * 100;
        }


        /*
        |--------------------------------------------------------------------------
        | Horários
        |--------------------------------------------------------------------------
        */

        $horariosQuery = DB::table('vendas as v');

        if ($produtoId !== null) {
            $horariosQuery
                ->join('item_vendas as iv', 'iv.venda_id', '=', 'v.id')
                ->where('iv.produto_id', $produtoId);
        }

        $this->aplicarFiltrosVenda(
            $horariosQuery,
            $dataInicio,
            $dataFim,
            $clienteId,
            null,
            $responsavelId
        );

        if ($produtoId !== null) {
            $horariosQuery->selectRaw('
                HOUR(v.data_venda) AS hora,
                COUNT(DISTINCT v.id) AS quantidade_vendas,
                COALESCE(SUM(iv.subtotal), 0) AS faturamento,
                CASE
                    WHEN COUNT(DISTINCT v.id) > 0
                    THEN COALESCE(SUM(iv.subtotal), 0) / COUNT(DISTINCT v.id)
                    ELSE 0
                END AS ticket_medio
            ');
        } else {
            $horariosQuery->selectRaw('
                HOUR(v.data_venda) AS hora,
                COUNT(*) AS quantidade_vendas,
                COALESCE(SUM(v.total), 0) AS faturamento,
                COALESCE(AVG(v.total), 0) AS ticket_medio
            ');
        }

        $horarios = $horariosQuery
            ->groupByRaw('HOUR(v.data_venda)')
            ->orderByRaw('HOUR(v.data_venda)')
            ->get();

        $horarioMaiorVolume = $horarios
            ->sortByDesc('quantidade_vendas')
            ->first();

        $horarioMenorVolume = $horarios
            ->filter(fn ($linha) => (int) $linha->quantidade_vendas > 0)
            ->sortBy('quantidade_vendas')
            ->first();

        $horarioMaiorFaturamento = $horarios
            ->sortByDesc('faturamento')
            ->first();

        $horarioMaiorTicket = $horarios
            ->sortByDesc('ticket_medio')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Ranking de responsáveis
        |--------------------------------------------------------------------------
        */

        $responsaveisQuery = DB::table('vendas as v')
            ->join('users as u', 'u.id', '=', 'v.funcionario_id')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'u.funcionario_id');

        if ($produtoId !== null) {
            $responsaveisQuery
                ->join('item_vendas as iv', 'iv.venda_id', '=', 'v.id')
                ->where('iv.produto_id', $produtoId);
        }

        $this->aplicarFiltrosVenda(
            $responsaveisQuery,
            $dataInicio,
            $dataFim,
            $clienteId,
            null,
            $responsavelId
        );

        if ($produtoId !== null) {
            $responsaveisQuery->selectRaw('
                u.id,
                u.name AS usuario,
                f.nome AS funcionario,
                f.funcao,
                COUNT(DISTINCT v.id) AS vendas,
                COALESCE(SUM(iv.subtotal), 0) AS faturamento
            ');
        } else {
            $responsaveisQuery->selectRaw('
                u.id,
                u.name AS usuario,
                f.nome AS funcionario,
                f.funcao,
                COUNT(v.id) AS vendas,
                COALESCE(SUM(v.total), 0) AS faturamento
            ');
        }

        $rankingResponsaveis = $responsaveisQuery
            ->groupBy(
                'u.id',
                'u.name',
                'f.nome',
                'f.funcao'
            )
            ->orderByDesc('faturamento')
            ->get()
            ->map(function ($linha) {
                $linha->ticket_medio = $linha->vendas > 0
                    ? $linha->faturamento / $linha->vendas
                    : 0;

                return $linha;
            });

        $responsavelDestaque = $rankingResponsaveis->first();


        /*
        |--------------------------------------------------------------------------
        | Clientes
        |--------------------------------------------------------------------------
        */

        $clientesQuery = DB::table('vendas as v')
            ->join('clientes as c', 'c.id', '=', 'v.cliente_id');

        if ($produtoId !== null) {
            $clientesQuery
                ->join('item_vendas as iv', 'iv.venda_id', '=', 'v.id')
                ->where('iv.produto_id', $produtoId);
        }

        $this->aplicarFiltrosVenda(
            $clientesQuery,
            $dataInicio,
            $dataFim,
            $clienteId,
            null,
            $responsavelId
        );

        if ($produtoId !== null) {
            $clientesQuery->selectRaw('
                c.id,
                c.nome,
                c.cidade,
                c.estado,
                COUNT(DISTINCT v.id) AS vendas,
                COALESCE(SUM(iv.subtotal), 0) AS faturamento,
                MAX(v.data_venda) AS ultima_compra
            ');
        } else {
            $clientesQuery->selectRaw('
                c.id,
                c.nome,
                c.cidade,
                c.estado,
                COUNT(v.id) AS vendas,
                COALESCE(SUM(v.total), 0) AS faturamento,
                MAX(v.data_venda) AS ultima_compra
            ');
        }

        $rankingClientes = $clientesQuery
            ->groupBy(
                'c.id',
                'c.nome',
                'c.cidade',
                'c.estado'
            )
            ->orderByDesc('faturamento')
            ->get()
            ->map(function ($linha) {
                $linha->ticket_medio = $linha->vendas > 0
                    ? $linha->faturamento / $linha->vendas
                    : 0;

                return $linha;
            });

        $clienteDestaque = $rankingClientes->first();

        $clientesComRecompra = $rankingClientes
            ->filter(fn ($linha) => (int) $linha->vendas >= 2)
            ->count();

        $taxaRecompra = $rankingClientes->count() > 0
            ? ($clientesComRecompra / $rankingClientes->count()) * 100
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Geografia
        |--------------------------------------------------------------------------
        */

        $geografia = $rankingClientes
            ->groupBy(function ($linha) {
                $cidade = trim((string) ($linha->cidade ?? ''));
                $estado = trim((string) ($linha->estado ?? ''));

                return ($cidade !== '' ? $cidade : 'Sem cidade')
                    . '|'
                    . ($estado !== '' ? $estado : '--');
            })
            ->map(function ($grupo, $chave) {
                [$cidade, $estado] = explode('|', $chave, 2);

                return (object) [
                    'cidade' => $cidade,
                    'estado' => $estado,
                    'clientes' => $grupo->count(),
                    'vendas' => $grupo->sum('vendas'),
                    'faturamento' => $grupo->sum('faturamento'),
                ];
            })
            ->sortByDesc('faturamento')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Produtos
        |--------------------------------------------------------------------------
        */

        $produtosQuery = DB::table('item_vendas as iv')
            ->join('vendas as v', 'v.id', '=', 'iv.venda_id')
            ->join('produtos as p', 'p.id', '=', 'iv.produto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('marcas as m', 'm.id', '=', 'p.marca_id');

        $this->aplicarFiltrosVenda(
            $produtosQuery,
            $dataInicio,
            $dataFim,
            $clienteId,
            $produtoId,
            $responsavelId
        );

        $rankingProdutos = $produtosQuery
            ->selectRaw('
                p.id,
                p.nome,
                c.nome AS categoria,
                m.nome AS marca,
                COALESCE(SUM(iv.quantidade), 0) AS quantidade,
                COALESCE(SUM(iv.subtotal), 0) AS faturamento,
                COUNT(DISTINCT v.id) AS vendas
            ')
            ->groupBy(
                'p.id',
                'p.nome',
                'c.nome',
                'm.nome'
            )
            ->orderByDesc('faturamento')
            ->get();

        $produtoDestaque = $rankingProdutos->first();


        /*
        |--------------------------------------------------------------------------
        | Curva ABC
        |--------------------------------------------------------------------------
        */

        $totalProdutos = (float) $rankingProdutos->sum('faturamento');
        $acumulado = 0;

        $curvaAbc = $rankingProdutos
            ->map(function ($linha) use (&$acumulado, $totalProdutos) {

                $participacao = $totalProdutos > 0
                    ? ((float) $linha->faturamento / $totalProdutos) * 100
                    : 0;

                $acumulado += $participacao;

                if ($acumulado <= 80) {
                    $classe = 'A';
                } elseif ($acumulado <= 95) {
                    $classe = 'B';
                } else {
                    $classe = 'C';
                }

                $linha->participacao = $participacao;
                $linha->acumulado = $acumulado;
                $linha->classe = $classe;

                return $linha;
            });

        $produtosClasseA = $curvaAbc
            ->where('classe', 'A')
            ->count();

        $participacaoClasseA = $curvaAbc
            ->where('classe', 'A')
            ->sum('participacao');


        /*
        |--------------------------------------------------------------------------
        | Histórico mensal
        |--------------------------------------------------------------------------
        */

        $historicoInicio = $dataFim
            ->startOfMonth()
            ->subMonths(11)
            ->startOfDay();

        $historicoQuery = DB::table('vendas as v');

        if ($produtoId !== null) {
            $historicoQuery
                ->join('item_vendas as iv', 'iv.venda_id', '=', 'v.id')
                ->where('iv.produto_id', $produtoId);
        }

        $this->aplicarFiltrosVenda(
            $historicoQuery,
            $historicoInicio,
            $dataFim,
            $clienteId,
            null,
            $responsavelId
        );

        if ($produtoId !== null) {
            $historicoQuery->selectRaw('
                YEAR(v.data_venda) AS ano,
                MONTH(v.data_venda) AS mes,
                COUNT(DISTINCT v.id) AS vendas,
                COALESCE(SUM(iv.subtotal), 0) AS faturamento
            ');
        } else {
            $historicoQuery->selectRaw('
                YEAR(v.data_venda) AS ano,
                MONTH(v.data_venda) AS mes,
                COUNT(v.id) AS vendas,
                COALESCE(SUM(v.total), 0) AS faturamento
            ');
        }

        $historicoMensal = $historicoQuery
            ->groupByRaw('YEAR(v.data_venda), MONTH(v.data_venda)')
            ->orderByRaw('YEAR(v.data_venda), MONTH(v.data_venda)')
            ->get()
            ->map(function ($linha) {
                $linha->ticket_medio = $linha->vendas > 0
                    ? $linha->faturamento / $linha->vendas
                    : 0;

                return $linha;
            });


        /*
        |--------------------------------------------------------------------------
        | Acareação
        |--------------------------------------------------------------------------
        */

        $acareacaoBase = DB::table('vendas as v')
            ->leftJoin('item_vendas as iv', 'iv.venda_id', '=', 'v.id');

        $this->aplicarFiltrosVenda(
            $acareacaoBase,
            $dataInicio,
            $dataFim,
            $clienteId,
            $produtoId,
            $responsavelId
        );

        $acareacaoSub = $acareacaoBase
            ->selectRaw('
                v.id,
                v.data_venda,
                v.total AS total_venda,
                COALESCE(SUM(iv.subtotal), 0) AS total_itens
            ')
            ->groupBy(
                'v.id',
                'v.data_venda',
                'v.total'
            );

        $divergencias = DB::query()
            ->fromSub($acareacaoSub, 'x')
            ->selectRaw('
                x.*,
                ROUND(x.total_venda - x.total_itens, 2) AS diferenca
            ')
            ->whereRaw('ABS(x.total_venda - x.total_itens) > 0.01')
            ->orderByRaw('ABS(x.total_venda - x.total_itens) DESC')
            ->get();

        $totalDivergencias = $divergencias->count();


        return view('bi.vendas.index', compact(
            'dataInicio',
            'dataFim',
            'clienteId',
            'produtoId',
            'responsavelId',
            'clientes',
            'produtos',
            'responsaveis',
            'resumo',
            'resumoAnterior',
            'variacaoFaturamento',
            'periodoAnteriorInicio',
            'periodoAnteriorFim',
            'horarios',
            'horarioMaiorVolume',
            'horarioMenorVolume',
            'horarioMaiorFaturamento',
            'horarioMaiorTicket',
            'rankingResponsaveis',
            'responsavelDestaque',
            'rankingClientes',
            'clienteDestaque',
            'clientesComRecompra',
            'taxaRecompra',
            'geografia',
            'rankingProdutos',
            'produtoDestaque',
            'curvaAbc',
            'produtosClasseA',
            'participacaoClasseA',
            'historicoMensal',
            'divergencias',
            'totalDivergencias'
        ));
    }


    private function aplicarFiltrosVenda(
        Builder $query,
        CarbonImmutable $inicio,
        CarbonImmutable $fim,
        ?int $clienteId,
        ?int $produtoId,
        ?int $responsavelId
    ): void {
        $query
            ->where('v.status', 'finalizada')
            ->whereBetween('v.data_venda', [
                $inicio->format('Y-m-d H:i:s'),
                $fim->format('Y-m-d H:i:s'),
            ]);

        if ($clienteId !== null) {
            $query->where('v.cliente_id', $clienteId);
        }

        if ($responsavelId !== null) {
            $query->where('v.funcionario_id', $responsavelId);
        }

        if ($produtoId !== null) {
            $query->whereExists(function ($subquery) use ($produtoId) {
                $subquery
                    ->selectRaw('1')
                    ->from('item_vendas as iv_filtro')
                    ->whereColumn('iv_filtro.venda_id', 'v.id')
                    ->where('iv_filtro.produto_id', $produtoId);
            });
        }
    }


    private function metricasPeriodo(
        CarbonImmutable $inicio,
        CarbonImmutable $fim,
        ?int $clienteId,
        ?int $produtoId,
        ?int $responsavelId
    ): object {
        if ($produtoId !== null) {

            $query = DB::table('item_vendas as iv')
                ->join('vendas as v', 'v.id', '=', 'iv.venda_id')
                ->where('iv.produto_id', $produtoId);

            $this->aplicarFiltrosVenda(
                $query,
                $inicio,
                $fim,
                $clienteId,
                null,
                $responsavelId
            );

            $linha = $query
                ->selectRaw('
                    COUNT(DISTINCT v.id) AS vendas,
                    COUNT(DISTINCT v.cliente_id) AS clientes,
                    COALESCE(SUM(iv.subtotal), 0) AS faturamento,
                    COALESCE(SUM(iv.quantidade), 0) AS itens
                ')
                ->first();

        } else {

            $query = DB::table('vendas as v');

            $this->aplicarFiltrosVenda(
                $query,
                $inicio,
                $fim,
                $clienteId,
                null,
                $responsavelId
            );

            $linha = $query
                ->selectRaw('
                    COUNT(*) AS vendas,
                    COUNT(DISTINCT v.cliente_id) AS clientes,
                    COALESCE(SUM(v.total), 0) AS faturamento
                ')
                ->first();

            $itensQuery = DB::table('item_vendas as iv')
                ->join('vendas as v', 'v.id', '=', 'iv.venda_id');

            $this->aplicarFiltrosVenda(
                $itensQuery,
                $inicio,
                $fim,
                $clienteId,
                null,
                $responsavelId
            );

            $linha->itens = (float) $itensQuery->sum('iv.quantidade');
        }

        $linha->vendas = (int) ($linha->vendas ?? 0);
        $linha->clientes = (int) ($linha->clientes ?? 0);
        $linha->faturamento = (float) ($linha->faturamento ?? 0);
        $linha->itens = (float) ($linha->itens ?? 0);

        $linha->ticket_medio = $linha->vendas > 0
            ? $linha->faturamento / $linha->vendas
            : 0;

        return $linha;
    }
}