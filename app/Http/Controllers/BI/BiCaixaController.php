<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiCaixaController extends Controller
{
    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim'    => ['nullable', 'date_format:Y-m-d'],
            'status'      => ['nullable', 'string', 'max:50'],
            'user_id'     => ['nullable', 'integer'],
            'terminal'    => ['nullable', 'string', 'max:255'],
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

        $inicioDt = $inicio->startOfDay();
        $fimDt    = $fim->endOfDay();

        $status   = $dados['status'] ?? null;
        $userId   = ! empty($dados['user_id']) ? (int) $dados['user_id'] : null;
        $terminal = trim((string) ($dados['terminal'] ?? ''));


        /*
        |--------------------------------------------------------------------------
        | BASE CAIXAS
        |--------------------------------------------------------------------------
        */

        $caixasBase = DB::table('caixas as c')
            ->whereBetween('c.data_abertura', [
                $inicioDt->toDateTimeString(),
                $fimDt->toDateTimeString(),
            ]);

        if ($status !== null && $status !== '') {
            $caixasBase->where('c.status', $status);
        }

        if ($userId) {
            $caixasBase->where('c.user_id', $userId);
        }

        if ($terminal !== '') {
            $caixasBase->where('c.terminal', $terminal);
        }


        /*
        |--------------------------------------------------------------------------
        | BASE MOVIMENTACOES
        |--------------------------------------------------------------------------
        */

        $movBase = DB::table('movimentacoes_caixa as m')
            ->join('caixas as c', 'c.id', '=', 'm.caixa_id')
            ->whereBetween('m.data_movimentacao', [
                $inicioDt->toDateTimeString(),
                $fimDt->toDateTimeString(),
            ]);

        if ($status !== null && $status !== '') {
            $movBase->where('c.status', $status);
        }

        if ($userId) {
            $movBase->where('c.user_id', $userId);
        }

        if ($terminal !== '') {
            $movBase->where('c.terminal', $terminal);
        }


        $tiposEntrada = [
            'venda',
            'entrada',
            'entrada_pagto_carteira',
            'entrada_manual',
        ];

        $tiposSaida = [
            'saida_manual',
            'cancelamento_venda',
            'estorno',
            'saida',
            'outras_saidas',
            'ajuste_negativo',
            'despesa',
            'sangria',
        ];


        /*
        |--------------------------------------------------------------------------
        | KPIs
        |--------------------------------------------------------------------------
        */

        $totalEntradas = (float) (
            (clone $movBase)
                ->whereIn('m.tipo', $tiposEntrada)
                ->sum('m.valor') ?? 0
        );

        $totalSaidas = (float) (
            (clone $movBase)
                ->whereIn('m.tipo', $tiposSaida)
                ->sum('m.valor') ?? 0
        );

        $movimentoLiquido = $totalEntradas - $totalSaidas;

        $entradasManuais = (float) (
            (clone $movBase)
                ->where('m.tipo', 'entrada_manual')
                ->sum('m.valor') ?? 0
        );

        $qtdEntradasManuais = (int) (
            (clone $movBase)
                ->where('m.tipo', 'entrada_manual')
                ->count()
        );


        /*
        |--------------------------------------------------------------------------
        | SANGRIAS
        |--------------------------------------------------------------------------
        */

        $sangriasBase = DB::table('sangrias as s')
            ->join('caixas as c', 'c.id', '=', 's.caixa_id')
            ->whereBetween('s.created_at', [
                $inicioDt->toDateTimeString(),
                $fimDt->toDateTimeString(),
            ]);

        if ($status !== null && $status !== '') {
            $sangriasBase->where('c.status', $status);
        }

        if ($userId) {
            $sangriasBase->where('c.user_id', $userId);
        }

        if ($terminal !== '') {
            $sangriasBase->where('c.terminal', $terminal);
        }

        $totalSangrias = (float) (
            (clone $sangriasBase)->sum('s.valor') ?? 0
        );

        $qtdSangrias = (int) (
            (clone $sangriasBase)->count()
        );


        /*
        |--------------------------------------------------------------------------
        | STATUS CAIXAS
        |--------------------------------------------------------------------------
        */

        $caixasAbertos = (int) (
            (clone $caixasBase)
                ->where('c.status', 'aberto')
                ->count()
        );

        $caixasFechados = (int) (
            (clone $caixasBase)
                ->whereIn('c.status', [
                    'fechado',
                    'fechado_sem_movimento',
                ])
                ->count()
        );

        $caixasInconsistentes = (int) (
            (clone $caixasBase)
                ->where('c.status', 'inconsistente')
                ->count()
        );


        /*
        |--------------------------------------------------------------------------
        | ULTIMA AUDITORIA DE CADA CAIXA
        |--------------------------------------------------------------------------
        */

        $ultimaAuditoriaIds = DB::table('auditorias_caixa')
            ->whereBetween('data_auditoria', [
                $inicioDt->toDateTimeString(),
                $fimDt->toDateTimeString(),
            ])
            ->selectRaw('MAX(id) as id')
            ->groupBy('caixa_id');

        $auditoriasBase = DB::table('auditorias_caixa as a')
            ->joinSub(
                $ultimaAuditoriaIds,
                'ua',
                'ua.id',
                '=',
                'a.id'
            )
            ->join('caixas as c', 'c.id', '=', 'a.caixa_id');

        if ($status !== null && $status !== '') {
            $auditoriasBase->where('c.status', $status);
        }

        if ($userId) {
            $auditoriasBase->where('c.user_id', $userId);
        }

        if ($terminal !== '') {
            $auditoriasBase->where('c.terminal', $terminal);
        }


        $totalSistema = (float) (
            (clone $auditoriasBase)->sum('a.total_sistema') ?? 0
        );

        $totalFisico = (float) (
            (clone $auditoriasBase)->sum('a.total_fisico') ?? 0
        );

        $diferencaTotal = (float) (
            (clone $auditoriasBase)->sum('a.diferenca') ?? 0
        );

        $auditoriasInconsistentes = (int) (
            (clone $auditoriasBase)
                ->where('a.status', 'inconsistente')
                ->count()
        );


        /*
        |--------------------------------------------------------------------------
        | ACAREACAO
        |--------------------------------------------------------------------------
        */

        $movimentacoesSemTipo = (int) (
            (clone $movBase)
                ->where(function ($q) {
                    $q->whereNull('m.tipo')
                        ->orWhere('m.tipo', '');
                })
                ->count()
        );

        $valorSemTipo = (float) (
            (clone $movBase)
                ->where(function ($q) {
                    $q->whereNull('m.tipo')
                        ->orWhere('m.tipo', '');
                })
                ->sum('m.valor') ?? 0
        );

        $caixasSemStatus = (int) (
            (clone $caixasBase)
                ->where(function ($q) {
                    $q->whereNull('c.status')
                        ->orWhere('c.status', '');
                })
                ->count()
        );

        $totalAlertas =
            $movimentacoesSemTipo
            + $caixasSemStatus
            + $auditoriasInconsistentes;


        /*
        |--------------------------------------------------------------------------
        | DETALHES ENTRADAS
        |--------------------------------------------------------------------------
        */

        $entradasDetalhes = (clone $movBase)
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->whereIn('m.tipo', $tiposEntrada)
            ->select(
                'm.id',
                'm.caixa_id',
                'm.tipo',
                'm.forma_pagamento',
                'm.valor',
                'm.observacao',
                'm.data_movimentacao',
                'c.terminal',
                'u.name as usuario_nome'
            )
            ->orderByDesc('m.data_movimentacao')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DETALHES SAIDAS
        |--------------------------------------------------------------------------
        */

        $saidasDetalhes = (clone $movBase)
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->whereIn('m.tipo', $tiposSaida)
            ->select(
                'm.id',
                'm.caixa_id',
                'm.tipo',
                'm.forma_pagamento',
                'm.valor',
                'm.observacao',
                'm.data_movimentacao',
                'c.terminal',
                'u.name as usuario_nome'
            )
            ->orderByDesc('m.data_movimentacao')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ENTRADAS MANUAIS
        |--------------------------------------------------------------------------
        */

        $entradasManuaisDetalhes = (clone $movBase)
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.tipo', 'entrada_manual')
            ->select(
                'm.id',
                'm.caixa_id',
                'm.forma_pagamento',
                'm.valor',
                'm.observacao',
                'm.data_movimentacao',
                'c.terminal',
                'u.name as usuario_nome'
            )
            ->orderByDesc('m.data_movimentacao')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | MOVIMENTACOES POR TIPO - SUBQUERY
        |--------------------------------------------------------------------------
        */

        $movimentacoesPorTipoBase = (clone $movBase)
            ->selectRaw(
                "COALESCE(NULLIF(m.tipo,''), 'SEM CLASSIFICACAO') as tipo_exibicao"
            )
            ->addSelect('m.valor');

        $movimentacoesPorTipo = DB::query()
            ->fromSub(
                $movimentacoesPorTipoBase,
                'mt'
            )
            ->select('mt.tipo_exibicao')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(mt.valor),0) as valor_total')
            ->groupBy('mt.tipo_exibicao')
            ->orderByDesc('valor_total')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | FORMAS DE PAGAMENTO - SUBQUERY
        |--------------------------------------------------------------------------
        */

        $formasPagamentoBase = (clone $movBase)
            ->whereIn('m.tipo', $tiposEntrada)
            ->selectRaw(
                "COALESCE(NULLIF(m.forma_pagamento,''), 'Nao informado') as forma"
            )
            ->addSelect('m.valor');

        $formasPagamento = DB::query()
            ->fromSub(
                $formasPagamentoBase,
                'fp'
            )
            ->select('fp.forma')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(fp.valor),0) as valor_total')
            ->groupBy('fp.forma')
            ->orderByDesc('valor_total')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | EVOLUCAO DIARIA
        |--------------------------------------------------------------------------
        */

        $entradasDia = (clone $movBase)
            ->whereIn('m.tipo', $tiposEntrada)
            ->selectRaw('DATE(m.data_movimentacao) as data')
            ->selectRaw('COALESCE(SUM(m.valor),0) as valor')
            ->groupByRaw('DATE(m.data_movimentacao)')
            ->get()
            ->keyBy('data');

        $saidasDia = (clone $movBase)
            ->whereIn('m.tipo', $tiposSaida)
            ->selectRaw('DATE(m.data_movimentacao) as data')
            ->selectRaw('COALESCE(SUM(m.valor),0) as valor')
            ->groupByRaw('DATE(m.data_movimentacao)')
            ->get()
            ->keyBy('data');

        $datas = $entradasDia
            ->keys()
            ->merge($saidasDia->keys())
            ->unique()
            ->sort()
            ->values();

        $evolucao = $datas->map(function ($data) use (
            $entradasDia,
            $saidasDia
        ) {
            $entrada = (float) ($entradasDia->get($data)->valor ?? 0);
            $saida   = (float) ($saidasDia->get($data)->valor ?? 0);

            return (object) [
                'data'     => $data,
                'entradas' => $entrada,
                'saidas'   => $saida,
                'liquido'  => $entrada - $saida,
            ];
        });


        /*
        |--------------------------------------------------------------------------
        | AUDITORIAS
        |--------------------------------------------------------------------------
        */

        $auditorias = (clone $auditoriasBase)
            ->leftJoin('users as au', 'au.id', '=', 'a.user_id')
            ->select(
                'a.id',
                'a.caixa_id',
                'a.codigo_auditoria',
                'a.total_sistema',
                'a.total_fisico',
                'a.diferenca',
                'a.status',
                'a.observacao',
                'a.data_auditoria',
                'au.name as auditor_nome',
                'c.terminal'
            )
            ->orderByDesc('a.data_auditoria')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SANGRIAS DETALHADAS
        |--------------------------------------------------------------------------
        */

        $sangrias = (clone $sangriasBase)
            ->leftJoin('users as su', 'su.id', '=', 's.user_id')
            ->select(
                's.id',
                's.caixa_id',
                's.codigo_operacao',
                's.numero_pdv',
                's.valor',
                's.saldo_antes',
                's.saldo_depois',
                's.motivo',
                's.created_at',
                'su.name as usuario_nome'
            )
            ->orderByDesc('s.created_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | CAIXAS ABERTOS
        |--------------------------------------------------------------------------
        */

        $caixasAbertosDetalhes = (clone $caixasBase)
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.status', 'aberto')
            ->select(
                'c.id',
                'c.terminal',
                'c.fundo_troco',
                'c.valor_abertura',
                'c.data_abertura',
                'c.status',
                'u.name as operador_nome'
            )
            ->orderByDesc('c.data_abertura')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | CAIXAS FECHADOS
        |--------------------------------------------------------------------------
        */

        $caixasFechadosDetalhes = (clone $caixasBase)
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->whereIn('c.status', [
                'fechado',
                'fechado_sem_movimento',
            ])
            ->select(
                'c.id',
                'c.terminal',
                'c.fundo_troco',
                'c.valor_abertura',
                'c.valor_fechamento',
                'c.data_abertura',
                'c.data_fechamento',
                'c.status',
                'u.name as operador_nome'
            )
            ->orderByDesc('c.data_abertura')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | CAIXAS INCONSISTENTES
        |--------------------------------------------------------------------------
        */

        $caixasInconsistentesDetalhes = (clone $caixasBase)
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.status', 'inconsistente')
            ->select(
                'c.id',
                'c.terminal',
                'c.fundo_troco',
                'c.valor_abertura',
                'c.valor_fechamento',
                'c.data_abertura',
                'c.data_fechamento',
                'c.status',
                'c.observacao_divergencia',
                'u.name as operador_nome'
            )
            ->orderByDesc('c.data_abertura')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ACAREACAO DETALHADA
        |--------------------------------------------------------------------------
        */

        $movimentosSemTipoDetalhes = (clone $movBase)
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where(function ($q) {
                $q->whereNull('m.tipo')
                    ->orWhere('m.tipo', '');
            })
            ->select(
                'm.id',
                'm.caixa_id',
                'm.forma_pagamento',
                'm.valor',
                'm.observacao',
                'm.data_movimentacao',
                'c.terminal',
                'u.name as usuario_nome'
            )
            ->orderByDesc('m.data_movimentacao')
            ->limit(500)
            ->get();

        $caixasSemStatusDetalhes = (clone $caixasBase)
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where(function ($q) {
                $q->whereNull('c.status')
                    ->orWhere('c.status', '');
            })
            ->select(
                'c.id',
                'c.terminal',
                'c.fundo_troco',
                'c.data_abertura',
                'c.data_fechamento',
                'u.name as operador_nome'
            )
            ->orderByDesc('c.data_abertura')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $operadores = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $terminais = DB::table('caixas')
            ->whereNotNull('terminal')
            ->where('terminal', '<>', '')
            ->distinct()
            ->orderBy('terminal')
            ->pluck('terminal');

        $statusDisponiveis = [
            'aberto',
            'bloqueado',
            'fechado',
            'fechado_sem_movimento',
            'pendente',
            'aguardando_auditoria',
            'inconsistente',
        ];


        return view('bi.caixa.index', compact(
            'inicio',
            'fim',
            'status',
            'userId',
            'terminal',

            'totalEntradas',
            'totalSaidas',
            'movimentoLiquido',

            'entradasManuais',
            'qtdEntradasManuais',

            'totalSangrias',
            'qtdSangrias',

            'caixasAbertos',
            'caixasFechados',
            'caixasInconsistentes',

            'totalSistema',
            'totalFisico',
            'diferencaTotal',

            'auditoriasInconsistentes',
            'movimentacoesSemTipo',
            'valorSemTipo',
            'caixasSemStatus',
            'totalAlertas',

            'entradasDetalhes',
            'saidasDetalhes',
            'entradasManuaisDetalhes',

            'movimentacoesPorTipo',
            'formasPagamento',
            'evolucao',

            'auditorias',
            'sangrias',

            'caixasAbertosDetalhes',
            'caixasFechadosDetalhes',
            'caixasInconsistentesDetalhes',

            'movimentosSemTipoDetalhes',
            'caixasSemStatusDetalhes',

            'operadores',
            'terminais',
            'statusDisponiveis'
        ));
    }
}