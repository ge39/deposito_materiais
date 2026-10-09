<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiFinanceiroController extends Controller
{
    // BI07_INTELIGENCIA_CARTEIRA_V4

    public function index(Request $request): View
    {
        abort_unless(
            auth()->check() &&
            in_array(
                auth()->user()->nivel_acesso,
                ['admin', 'gerente'],
                true
            ),
            403
        );

        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $hoje = CarbonImmutable::today();

        $inicio = isset($dados['data_inicio'])
            ? CarbonImmutable::parse($dados['data_inicio'])
            : $hoje->startOfMonth();

        $fim = isset($dados['data_fim'])
            ? CarbonImmutable::parse($dados['data_fim'])
            : $hoje;

        if ($fim->lt($inicio)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $de = $inicio->startOfDay()->toDateTimeString();
        $ate = $fim->endOfDay()->toDateTimeString();
        $dataHoje = $hoje->toDateString();

        /*
         * Compras em Carteira:
         * agregacao dos componentes Carteira de cada venda.
         * Evita contar integralmente uma venda com pagamento misto.
         *
         * Confirmado nao e interpretado como recebimento.
         */
        $componentesCarteira = DB::table('pagamentos_venda')
            ->where('forma_pagamento', 'carteira')
            ->whereNotIn('status', ['Cancelado', 'Estornado'])
            ->select('venda_id')
            ->selectRaw('SUM(valor) as valor_carteira')
            ->groupBy('venda_id');

        $comprasBase = DB::table('vendas as v')
            ->joinSub($componentesCarteira, 'pc', function ($join) {
                $join->on('pc.venda_id', '=', 'v.id');
            })
            ->where('v.status', 'finalizada');

        $comprasPeriodo = (clone $comprasBase)
            ->whereBetween('v.data_venda', [$de, $ate]);

        $comprasAgrupadas = (clone $comprasPeriodo)
            ->select('v.cliente_id')
            ->selectRaw('COUNT(*) as operacoes')
            ->selectRaw('COALESCE(SUM(pc.valor_carteira),0) as total')
            ->groupBy('v.cliente_id')
            ->get()
            ->keyBy('cliente_id');

        $ultimasCompras = (clone $comprasBase)
            ->select('v.cliente_id')
            ->selectRaw('MAX(v.data_venda) as ultima_compra')
            ->groupBy('v.cliente_id')
            ->get()
            ->keyBy('cliente_id');

        /*
         * Posicao das obrigacoes pendentes da Carteira.
         * Nao depende do periodo filtrado.
         */
        $pendenciasBase = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'v.id', '=', 'p.venda_id')
            ->where('p.forma_pagamento', 'carteira')
            ->where('p.status', 'Pendente')
            ->where('v.status', '<>', 'cancelada');

        $pendencias = (clone $pendenciasBase)
            ->select('v.cliente_id')
            ->selectRaw('COUNT(*) as operacoes')
            ->selectRaw('COALESCE(SUM(p.valor),0) as aberto')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.data_vencimento < ? THEN p.valor ELSE 0 END),0) as vencido',
                [$dataHoje]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.data_vencimento >= ? THEN p.valor ELSE 0 END),0) as a_vencer',
                [$dataHoje]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.data_vencimento IS NULL THEN p.valor ELSE 0 END),0) as sem_vencimento'
            )
            ->selectRaw(
                'MIN(CASE WHEN p.data_vencimento < ? THEN p.data_vencimento ELSE NULL END) as vencimento_antigo',
                [$dataHoje]
            )
            ->groupBy('v.cliente_id')
            ->get()
            ->keyBy('cliente_id');

        /*
         * Recebimentos demonstraveis por cliente:
         * creditos de origem pagamento na conta corrente.
         *
         * Nao somar simultaneamente movimentacoes de caixa:
         * essas fontes podem representar o mesmo pagamento.
         */
        $pagamentosHistorico = DB::table('cliente_conta_correntes')
            ->where('tipo', 'credito')
            ->where('origem', 'pagamento');

        $recebimentosAgrupados = (clone $pagamentosHistorico)
            ->whereBetween('created_at', [$de, $ate])
            ->select('cliente_id')
            ->selectRaw('COUNT(*) as operacoes')
            ->selectRaw('COALESCE(SUM(valor),0) as total')
            ->groupBy('cliente_id')
            ->get()
            ->keyBy('cliente_id');

        $ultimosPagamentos = (clone $pagamentosHistorico)
            ->select('cliente_id')
            ->selectRaw('MAX(created_at) as ultimo_pagamento')
            ->selectRaw('COUNT(*) as pagamentos_registrados')
            ->groupBy('cliente_id')
            ->get()
            ->keyBy('cliente_id');

        /*
         * Credito:
         * fonte de consulta existente no ERP.
         * Nao recalcular o saldo cadastrado.
         */
        $creditos = DB::table('vw_cliente_credito_resumo')
            ->get()
            ->keyBy('cliente_id');

        $nomes = DB::table('clientes')
            ->pluck('nome', 'id');

        /*
         * Montar cadastro analitico unico:
         * uma linha por cliente.
         */
        $ids = $comprasAgrupadas->keys()
            ->merge($ultimasCompras->keys())
            ->merge($pendencias->keys())
            ->merge($recebimentosAgrupados->keys())
            ->merge($ultimosPagamentos->keys())
            ->merge($creditos->keys())
            ->unique();

        $clientes = collect();

        foreach ($ids as $id) {
            $c = $creditos->get($id);
            $p = $pendencias->get($id);
            $v = $comprasAgrupadas->get($id);
            $r = $recebimentosAgrupados->get($id);
            $h = $ultimosPagamentos->get($id);
            $u = $ultimasCompras->get($id);

            $aberto = (float) ($p->aberto ?? 0);
            $vencido = (float) ($p->vencido ?? 0);

            $dias = 0;

            if (!empty($p->vencimento_antigo)) {
                $dias = max(
                    0,
                    CarbonImmutable::parse($p->vencimento_antigo)
                        ->diffInDays($hoje, false)
                );
            }

            $limite = $c ? (float) $c->limite_credito : null;
            $usado = $c ? (float) $c->total_usado : null;
            $disponivel = $c ? (float) $c->credito_disponivel : null;

            /*
             * Score provisório e explicavel.
             * Nao estima pontualidade historica sem
             * vencimentos efetivamente conciliados.
             * Sem evidencias suficientes: nao avaliado.
             */
            $score = null;

            $temHistorico = (
                (int) ($h->pagamentos_registrados ?? 0) > 0
                || (int) ($p->operacoes ?? 0) > 0
            );

            if ($temHistorico && $limite !== null && $limite > 0) {
                $base = 7.0;

                if ($dias > 90) {
                    $base -= 5.0;
                } elseif ($dias > 60) {
                    $base -= 4.0;
                } elseif ($dias > 30) {
                    $base -= 3.0;
                } elseif ($dias > 7) {
                    $base -= 2.0;
                } elseif ($dias > 0) {
                    $base -= 1.0;
                } else {
                    $base += 1.0;
                }

                if ($aberto > 0 && $vencido / $aberto >= 0.75) {
                    $base -= 1.0;
                }

                $qtdPagamentos = (int) ($h->pagamentos_registrados ?? 0);

                if ($qtdPagamentos >= 5) {
                    $base += 1.0;
                } elseif ($qtdPagamentos >= 1) {
                    $base += 0.5;
                }

                $score = round(max(0, min(10, $base)), 1);
            }

            $clientes->put($id, (object) [
                'id' => $id,
                'nome' => $nomes[$id] ?? ('Cliente #'.$id),
                'compras_operacoes' => (int) ($v->operacoes ?? 0),
                'compras_valor' => (float) ($v->total ?? 0),
                'recebimentos_operacoes' => (int) ($r->operacoes ?? 0),
                'recebimentos_valor' => (float) ($r->total ?? 0),
                'pendencias_operacoes' => (int) ($p->operacoes ?? 0),
                'aberto' => $aberto,
                'vencido' => $vencido,
                'a_vencer' => (float) ($p->a_vencer ?? 0),
                'sem_vencimento' => (float) ($p->sem_vencimento ?? 0),
                'limite' => $limite,
                'usado' => $usado,
                'disponivel' => $disponivel,
                'ultima_compra' => $u->ultima_compra ?? null,
                'ultimo_pagamento' => $h->ultimo_pagamento ?? null,
                'dias_atraso' => (int) $dias,
                'score' => $score,
            ]);
        }

        /*
         * Evitar que movimentos sem cliente sejam perdidos.
         * Clientes sem ID ficam em grupo separado.
         */
        $semClienteCompras = $comprasAgrupadas->get(null);
        $semClientePendencias = $pendencias->get(null);
        $semClienteRecebimentos = $recebimentosAgrupados->get(null);

        if ($semClienteCompras || $semClientePendencias || $semClienteRecebimentos) {
            $clientes->put('__sem_cliente__', (object) [
                'id' => null,
                'nome' => 'Sem cliente identificado',
                'compras_operacoes' => (int) ($semClienteCompras->operacoes ?? 0),
                'compras_valor' => (float) ($semClienteCompras->total ?? 0),
                'recebimentos_operacoes' => (int) ($semClienteRecebimentos->operacoes ?? 0),
                'recebimentos_valor' => (float) ($semClienteRecebimentos->total ?? 0),
                'pendencias_operacoes' => (int) ($semClientePendencias->operacoes ?? 0),
                'aberto' => (float) ($semClientePendencias->aberto ?? 0),
                'vencido' => (float) ($semClientePendencias->vencido ?? 0),
                'a_vencer' => (float) ($semClientePendencias->a_vencer ?? 0),
                'sem_vencimento' => (float) ($semClientePendencias->sem_vencimento ?? 0),
                'limite' => null,
                'usado' => null,
                'disponivel' => null,
                'ultima_compra' => null,
                'ultimo_pagamento' => null,
                'dias_atraso' => 0,
                'score' => null,
            ]);
        }

        /*
         * Dez indicadores especificos da Carteira.
         */
        $avaliados = $clientes->filter(fn($c) => $c->score !== null);

        $scoreMedio = $avaliados->isNotEmpty()
            ? round($avaliados->avg('score'), 1)
            : null;

        $totalAberto = $clientes->sum('aberto');
        $totalVencido = $clientes->sum('vencido');

        $taxaInadimplencia = $totalAberto > 0
            ? round($totalVencido / $totalAberto * 100, 2)
            : 0;

        $cards = [
            [
                'titulo' => 'Compras em Carteira',
                'campo' => 'compras_valor',
                'operacoes' => 'compras_operacoes',
                'valor' => $clientes->sum('compras_valor'),
                'tipo' => 'moeda',
                'cor' => 'primary',
                'icone' => 'bi-bag-check',
            ],
            [
                'titulo' => 'Recebimentos Carteira',
                'campo' => 'recebimentos_valor',
                'operacoes' => 'recebimentos_operacoes',
                'valor' => $clientes->sum('recebimentos_valor'),
                'tipo' => 'moeda',
                'cor' => 'success',
                'icone' => 'bi-cash-coin',
            ],
            [
                'titulo' => 'Saldo a Receber',
                'campo' => 'aberto',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $totalAberto,
                'tipo' => 'moeda',
                'cor' => 'primary',
                'icone' => 'bi-wallet2',
            ],
            [
                'titulo' => 'Carteira Vencida',
                'campo' => 'vencido',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $totalVencido,
                'tipo' => 'moeda',
                'cor' => 'danger',
                'icone' => 'bi-exclamation-triangle',
            ],
            [
                'titulo' => 'Carteira a Vencer',
                'campo' => 'a_vencer',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $clientes->sum('a_vencer'),
                'tipo' => 'moeda',
                'cor' => 'info',
                'icone' => 'bi-calendar-check',
            ],
            [
                'titulo' => 'Clientes Devedores',
                'campo' => 'aberto',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $clientes->filter(fn($c) => $c->id !== null && $c->aberto > 0)->count(),
                'tipo' => 'inteiro',
                'cor' => 'secondary',
                'icone' => 'bi-people',
            ],
            [
                'titulo' => 'Clientes em Atraso',
                'campo' => 'vencido',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $clientes->filter(fn($c) => $c->id !== null && $c->vencido > 0)->count(),
                'tipo' => 'inteiro',
                'cor' => 'danger',
                'icone' => 'bi-person-exclamation',
            ],
            [
                'titulo' => 'Taxa de Inadimplencia',
                'campo' => 'vencido',
                'operacoes' => 'pendencias_operacoes',
                'valor' => $taxaInadimplencia,
                'tipo' => 'percentual',
                'cor' => 'warning',
                'icone' => 'bi-graph-down-arrow',
            ],
            [
                'titulo' => 'Credito Disponivel',
                'campo' => 'disponivel',
                'operacoes' => null,
                'valor' => $clientes->sum('disponivel'),
                'tipo' => 'moeda',
                'cor' => 'success',
                'icone' => 'bi-credit-card',
            ],
            [
                'titulo' => 'Score Medio',
                'campo' => 'score',
                'operacoes' => null,
                'valor' => $scoreMedio,
                'tipo' => 'score',
                'cor' => 'primary',
                'icone' => 'bi-speedometer2',
            ],
        ];

        /*
         * Grafico contextual:
         * somente aqui as vendas gerais sao utilizadas,
         * exclusivamente como denominador.
         */
        $totalVendasGerais = (float) DB::table('vendas')
            ->where('status', 'finalizada')
            ->whereBetween('data_venda', [$de, $ate])
            ->sum('total');

        $totalComprasCarteira = $clientes->sum('compras_valor');

        $participacaoCarteira = $totalVendasGerais > 0
            ? round($totalComprasCarteira / $totalVendasGerais * 100, 2)
            : 0;

        $participacaoCarteira = min(100, max(0, $participacaoCarteira));

        /*
         * Grafico mensal de compras em Carteira.
         */
        $comprasMensais = (clone $comprasPeriodo)
            ->selectRaw("DATE_FORMAT(v.data_venda,'%Y-%m') as periodo")
            ->selectRaw('COALESCE(SUM(pc.valor_carteira),0) as total')
            ->groupByRaw("DATE_FORMAT(v.data_venda,'%Y-%m')")
            ->orderBy('periodo')
            ->get();

        return view('bi.financeiro.index', compact(
            'inicio',
            'fim',
            'cards',
            'clientes',
            'totalVendasGerais',
            'totalComprasCarteira',
            'participacaoCarteira',
            'comprasMensais',
            'taxaInadimplencia'
        ));
    }
}