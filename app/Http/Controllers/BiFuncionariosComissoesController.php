<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiFuncionariosComissoesController extends Controller
{
    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_inicio' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'data_fim' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'funcionario_id' => [
                'nullable',
                'integer',
                'exists:funcionarios,id',
            ],
        ]);

        $hoje = CarbonImmutable::today();

        $dataInicio = ! empty($dados['data_inicio'])
            ? CarbonImmutable::parse($dados['data_inicio'])->startOfDay()
            : $hoje->startOfMonth()->startOfDay();

        $dataFim = ! empty($dados['data_fim'])
            ? CarbonImmutable::parse($dados['data_fim'])->endOfDay()
            : $hoje->endOfDay();

        if ($dataInicio->greaterThan($dataFim)) {
            [$dataInicio, $dataFim] = [
                $dataFim->startOfDay(),
                $dataInicio->endOfDay(),
            ];
        }

        $funcionarioId = ! empty($dados['funcionario_id'])
            ? (int) $dados['funcionario_id']
            : null;


        // =====================================================
        // VENDEDORES
        // =====================================================

        $vendedores = DB::table('users')
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'users.funcionario_id'
            )
            ->where(
                'users.nivel_acesso',
                'vendedor'
            )
            ->where(
                'users.ativo',
                1
            )
            ->select([
                'funcionarios.id',
                'funcionarios.nome',
                'users.id as user_id',
            ])
            ->orderBy('funcionarios.nome')
            ->get();


        $vendedoresAtivos = $vendedores->count();


        // =====================================================
        // BASE DE VENDAS
        // vendas.funcionario_id -> users.id
        // users.funcionario_id -> funcionarios.id
        // =====================================================

        $vendasBase = DB::table('vendas')
            ->join(
                'users',
                'users.id',
                '=',
                'vendas.funcionario_id'
            )
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'users.funcionario_id'
            )
            ->where(
                'users.nivel_acesso',
                'vendedor'
            )
            ->where(
                'vendas.status',
                'finalizada'
            )
            ->whereBetween(
                'vendas.data_venda',
                [
                    $dataInicio->format('Y-m-d H:i:s'),
                    $dataFim->format('Y-m-d H:i:s'),
                ]
            );

        if ($funcionarioId) {
            $vendasBase->where(
                'funcionarios.id',
                $funcionarioId
            );
        }


        $vendasFinalizadas = (clone $vendasBase)
            ->count('vendas.id');


        $faturamento = (float) (
            (clone $vendasBase)
                ->sum('vendas.total')
        );


        $ticketMedio = $vendasFinalizadas > 0
            ? $faturamento / $vendasFinalizadas
            : 0.0;


        // =====================================================
        // COMISSOES
        // =====================================================

        $comissoesBase = DB::table('comissoes_vendas')
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'comissoes_vendas.funcionario_id'
            )
            ->whereBetween(
                'comissoes_vendas.data_competencia',
                [
                    $dataInicio->format('Y-m-d'),
                    $dataFim->format('Y-m-d'),
                ]
            );

        if ($funcionarioId) {
            $comissoesBase->where(
                'comissoes_vendas.funcionario_id',
                $funcionarioId
            );
        }


        $comissoesGeradas = (float) (
            (clone $comissoesBase)
                ->whereNotIn(
                    'comissoes_vendas.status',
                    [
                        'estornada',
                        'cancelada',
                    ]
                )
                ->sum(
                    'comissoes_vendas.valor_comissao'
                )
        );


        $comissoesPendentes = (float) (
            (clone $comissoesBase)
                ->whereIn(
                    'comissoes_vendas.status',
                    [
                        'pendente',
                        'aprovada',
                    ]
                )
                ->sum(
                    'comissoes_vendas.valor_comissao'
                )
        );


        $comissoesPagas = (float) (
            (clone $comissoesBase)
                ->where(
                    'comissoes_vendas.status',
                    'paga'
                )
                ->sum(
                    'comissoes_vendas.valor_comissao'
                )
        );


        // =====================================================
        // COMISSAO AGREGADA POR FUNCIONARIO
        // =====================================================

        $comissaoPorFuncionario = DB::table(
            'comissoes_vendas'
        )
            ->select([
                'funcionario_id',

                DB::raw(
                    "
                    SUM(
                        CASE
                            WHEN status NOT IN (
                                'estornada',
                                'cancelada'
                            )
                            THEN valor_comissao
                            ELSE 0
                        END
                    ) AS total_comissao
                    "
                ),

                DB::raw(
                    "
                    SUM(
                        CASE
                            WHEN status = 'paga'
                            THEN valor_comissao
                            ELSE 0
                        END
                    ) AS total_pago
                    "
                ),

                DB::raw(
                    "
                    SUM(
                        CASE
                            WHEN status IN (
                                'pendente',
                                'aprovada'
                            )
                            THEN valor_comissao
                            ELSE 0
                        END
                    ) AS total_pendente
                    "
                ),
            ])
            ->whereBetween(
                'data_competencia',
                [
                    $dataInicio->format('Y-m-d'),
                    $dataFim->format('Y-m-d'),
                ]
            )
            ->groupBy('funcionario_id');


        // =====================================================
        // RANKING
        // =====================================================

        $rankingQuery = DB::table('vendas')
            ->join(
                'users',
                'users.id',
                '=',
                'vendas.funcionario_id'
            )
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'users.funcionario_id'
            )
            ->leftJoinSub(
                $comissaoPorFuncionario,
                'cp',
                function ($join) {
                    $join->on(
                        'cp.funcionario_id',
                        '=',
                        'funcionarios.id'
                    );
                }
            )
            ->where(
                'users.nivel_acesso',
                'vendedor'
            )
            ->where(
                'vendas.status',
                'finalizada'
            )
            ->whereBetween(
                'vendas.data_venda',
                [
                    $dataInicio->format('Y-m-d H:i:s'),
                    $dataFim->format('Y-m-d H:i:s'),
                ]
            )
            ->select([
                'funcionarios.id',
                'funcionarios.nome',

                DB::raw(
                    'COUNT(DISTINCT vendas.id) AS quantidade_vendas'
                ),

                DB::raw(
                    'SUM(vendas.total) AS faturamento'
                ),

                DB::raw(
                    'AVG(vendas.total) AS ticket_medio'
                ),

                DB::raw(
                    'MAX(vendas.data_venda) AS ultima_venda'
                ),

                DB::raw(
                    'COALESCE(MAX(cp.total_comissao), 0) AS total_comissao'
                ),

                DB::raw(
                    'COALESCE(MAX(cp.total_pago), 0) AS total_pago'
                ),

                DB::raw(
                    'COALESCE(MAX(cp.total_pendente), 0) AS total_pendente'
                ),
            ])
            ->groupBy([
                'funcionarios.id',
                'funcionarios.nome',
            ])
            ->orderByDesc('faturamento');


        if ($funcionarioId) {
            $rankingQuery->where(
                'funcionarios.id',
                $funcionarioId
            );
        }


        $ranking = $rankingQuery->get();


        // =====================================================
        // EVOLUCAO MENSAL
        // =====================================================

        $mensalQuery = DB::table('vendas')
            ->join(
                'users',
                'users.id',
                '=',
                'vendas.funcionario_id'
            )
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'users.funcionario_id'
            )
            ->where(
                'users.nivel_acesso',
                'vendedor'
            )
            ->where(
                'vendas.status',
                'finalizada'
            )
            ->whereBetween(
                'vendas.data_venda',
                [
                    $dataInicio->format('Y-m-d H:i:s'),
                    $dataFim->format('Y-m-d H:i:s'),
                ]
            )
            ->selectRaw(
                "
                DATE_FORMAT(
                    vendas.data_venda,
                    '%Y-%m'
                ) AS competencia
                "
            )
            ->selectRaw(
                'COUNT(DISTINCT vendas.id) AS quantidade_vendas'
            )
            ->selectRaw(
                'SUM(vendas.total) AS faturamento'
            )
            ->groupBy('competencia')
            ->orderBy('competencia');


        if ($funcionarioId) {
            $mensalQuery->where(
                'funcionarios.id',
                $funcionarioId
            );
        }


        $mensal = $mensalQuery->get();


        // =====================================================
        // ULTIMAS COMISSOES
        // =====================================================

        $ultimasComissoesQuery = DB::table(
            'comissoes_vendas'
        )
            ->join(
                'funcionarios',
                'funcionarios.id',
                '=',
                'comissoes_vendas.funcionario_id'
            )
            ->join(
                'produtos',
                'produtos.id',
                '=',
                'comissoes_vendas.produto_id'
            )
            ->join(
                'vendas',
                'vendas.id',
                '=',
                'comissoes_vendas.venda_id'
            )
            ->whereBetween(
                'comissoes_vendas.data_competencia',
                [
                    $dataInicio->format('Y-m-d'),
                    $dataFim->format('Y-m-d'),
                ]
            )
            ->select([
                'comissoes_vendas.id',
                'comissoes_vendas.venda_id',
                'funcionarios.nome as vendedor',
                'produtos.nome as produto',
                'comissoes_vendas.percentual_aplicado',
                'comissoes_vendas.valor_base',
                'comissoes_vendas.valor_comissao',
                'comissoes_vendas.status',
                'comissoes_vendas.data_competencia',
                'comissoes_vendas.gerada_em',
            ])
            ->orderByDesc(
                'comissoes_vendas.gerada_em'
            )
            ->limit(20);


        if ($funcionarioId) {
            $ultimasComissoesQuery->where(
                'comissoes_vendas.funcionario_id',
                $funcionarioId
            );
        }


        $ultimasComissoes =
            $ultimasComissoesQuery->get();


        // =====================================================
        // REGRAS ATIVAS
        // =====================================================

        $regrasAtivasQuery = DB::table(
            'comissao_regras'
        )
            ->where('ativo', 1);


        if ($funcionarioId) {
            $regrasAtivasQuery->where(
                'funcionario_id',
                $funcionarioId
            );
        }


        $regrasAtivas =
            $regrasAtivasQuery->count();


        return view(
            'bi.funcionarios-comissoes.index',
            compact(
                'dataInicio',
                'dataFim',
                'funcionarioId',
                'vendedores',
                'vendedoresAtivos',
                'vendasFinalizadas',
                'faturamento',
                'ticketMedio',
                'comissoesGeradas',
                'comissoesPendentes',
                'comissoesPagas',
                'regrasAtivas',
                'ranking',
                'mensal',
                'ultimasComissoes'
            )
        );
    }
}