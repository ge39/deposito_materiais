<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Veiculo;
use App\Models\Romaneio;
use App\Services\Expedicao\RomaneioService;
use App\Services\Sistema\BloqueioEdicaoService;
use Illuminate\Validation\Rule;
use App\Services\Entregas\EntregaService;

use Throwable;

class EntregaController extends Controller
{
   public function __construct(
        EntregaService $entregaService,
        RomaneioService $romaneioService,
        BloqueioEdicaoService $bloqueioEdicaoService
    )
   {
        $this->entregaService = $entregaService;
        $this->romaneioService = $romaneioService;
        $this->bloqueioEdicaoService = $bloqueioEdicaoService;
    }

    // public function index(Request $request)
    // {
    //     $dadosValidados = $request->validate(
    //         [
    //             'codigo_entrega' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //             ],

    //             'status' => [
    //                 'nullable',
    //                 'string',
    //             ],

    //             'data_inicio' => [
    //                 'nullable',
    //                 'date',
    //             ],

    //             'data_fim' => [
    //                 'nullable',
    //                 'date',
    //                 'after_or_equal:data_inicio',
    //             ],
    //         ],
    //         [
    //             'codigo_entrega.string' =>
    //                 'O código da entrega informado é inválido.',

    //             'codigo_entrega.max' =>
    //                 'O código da entrega pode possuir no máximo 100 caracteres.',

    //             'data_inicio.date' =>
    //                 'A data inicial informada é inválida.',

    //             'data_fim.date' =>
    //                 'A data final informada é inválida.',

    //             'data_fim.after_or_equal' =>
    //                 'A data final deve ser igual ou posterior à data inicial.',
    //         ]
    //     );

    //     $statusMap = [
    //         'pendente_pagamento' =>
    //             'Pendente_pagamento',

    //         'aguardando_faturamento' =>
    //             'Aguardando_faturamento',

    //         'aguardando_separacao' =>
    //             'Aguardando_separacao',

    //         'separando' =>
    //             'Em_preparacao',

    //         'em_preparacao' =>
    //             'Em_preparacao',

    //         'pronta_para_carregamento' =>
    //             'Pronta_para_carregamento',

    //         'carregado' =>
    //             'Carregada',

    //         'carregada' =>
    //             'Carregada',

    //         'liberada' =>
    //             'Liberada',

    //         'em_rota' =>
    //             'Em_rota',

    //         'no_destino' =>
    //             'No_destino',

    //         'entregue' =>
    //             'Entregue',

    //         'parcial' =>
    //             'Entregue_parcial',

    //         'entregue_parcial' =>
    //             'Entregue_parcial',

    //         'entregue_finalizada_com_ocorrencia' =>
    //             'Entregue_finalizada_com_ocorrencia',

    //         'finalizada_com_ocorrencia' =>
    //             'Entregue_finalizada_com_ocorrencia',

    //         'nao_entregue' =>
    //             'Nao_entregue',

    //         'recusada' =>
    //             'Recusada',

    //         'reagendada' =>
    //             'Reagendada',

    //         'devolvido' =>
    //             'Devolvida',

    //         'devolvida' =>
    //             'Devolvida',

    //         'cancelado' =>
    //             'Cancelada',

    //         'cancelada' =>
    //             'Cancelada',
    //     ];

    //     $dataInicio = ! empty(
    //         $dadosValidados['data_inicio'] ?? null
    //     )
    //         ? \Carbon\Carbon::parse(
    //             $dadosValidados['data_inicio']
    //         )->startOfDay()
    //         : now()->subDays(30)->startOfDay();

    //     $dataFim = ! empty(
    //         $dadosValidados['data_fim'] ?? null
    //     )
    //         ? \Carbon\Carbon::parse(
    //             $dadosValidados['data_fim']
    //         )->endOfDay()
    //         : now()->addDays(30)->endOfDay();

    //     $query = Entrega::query()
    //     ->with([
    //         'venda',
    //         'orcamento',
    //         'itens',
    //         'itens.vendaItem.produto',
    //         'itens.itemOrcamento.produto',
    //         'bloqueioEdicaoAtivo.usuario',
    //     ])
    //     ->whereBetween(
    //         'data_prevista',
    //         [
    //             $dataInicio,
    //             $dataFim,
    //         ]
    //     );

    //     $statusFiltroAplicado = false;

    //     if (! empty(
    //         $dadosValidados['status'] ?? null
    //     )) {
    //         $statusInformado = strtolower(
    //             trim(
    //                 (string) $dadosValidados['status']
    //             )
    //         );

    //         if (isset($statusMap[$statusInformado])) {
    //             $statusFiltroAplicado = true;

    //             $query->where(
    //                 'status',
    //                 $statusMap[$statusInformado]
    //             );
    //         }
    //     }

    //     $codigoEntregaInformado = trim(
    //         (string) (
    //             $dadosValidados['codigo_entrega']
    //             ?? ''
    //         )
    //     );

    //     if ($codigoEntregaInformado !== '') {
    //         $query->where(
    //             'codigo_entrega',
    //             'like',
    //             '%' . $codigoEntregaInformado . '%'
    //         );
    //     }

    //     /*
    //     * Entregas concluídas não ocupam a grade operacional.
    //     * Permanecem contabilizadas no card e podem ser localizadas
    //     * pelo filtro de status ou pela busca do código da entrega.
    //     */
    //     if (
    //         ! $statusFiltroAplicado
    //         && $codigoEntregaInformado === ''
    //     ) {
    //         $query->where(
    //             'status',
    //             '<>',
    //             'Entregue'
    //         );
    //     }

    //     /*
    //     * Entregas canceladas ficam sempre no final.
    //     * As demais continuam ordenadas pela data prevista
    //     * e pelo identificador.
    //     */
    //     $query
    //         ->orderByRaw(
    //             "
    //                 CASE
    //                     WHEN LOWER(TRIM(status)) IN (
    //                         'cancelada',
    //                         'cancelado'
    //                     ) THEN 1
    //                     ELSE 0
    //                 END ASC
    //             "
    //         )
    //         ->orderBy('data_prevista')
    //         ->orderBy('id');

    //     $entregas = $query
    //         ->paginate(20)
    //         ->withQueryString();

    //     $usuarioId = (int) (
    //         $request->user()?->id
    //         ?? 0
    //     );

    //     $sessaoId = $request
    //         ->session()
    //         ->getId();

    //     $entregas
    //         ->getCollection()
    //         ->each(function (Entrega $entrega) use (
    //             $usuarioId,
    //             $sessaoId
    //         ) {
    //             $bloqueio =
    //                 $entrega->bloqueioEdicaoAtivo;

    //             $bloqueada = $bloqueio
    //                 && ! $this
    //                     ->bloqueioEdicaoService
    //                     ->podeEditar(
    //                         $bloqueio,
    //                         $usuarioId,
    //                         $sessaoId
    //                     );

    //             $entrega->setAttribute(
    //                 'edicao_bloqueada',
    //                 (bool) $bloqueada
    //             );

    //             $entrega->setAttribute(
    //                 'edicao_bloqueio_mensagem',
    //                 $bloqueada
    //                     ? $this
    //                         ->bloqueioEdicaoService
    //                         ->mensagemBloqueio(
    //                             $bloqueio
    //                         )
    //                     : null
    //             );
    //         });

    //     /*
    //     * A tratativa pertence ao romaneio que gerou as ocorrências,
    //     * e não à tela de consulta da entrega.
    //     *
    //     * A busca é feita uma única vez para todas as entregas da página,
    //     * evitando consultas dentro da Blade e o problema de N+1.
    //     * Quando houver mais de um romaneio para a mesma entrega,
    //     * utilizamos o mais recente que realmente possui ocorrências.
    //     */
    //     $entregasIdsDaPagina = $entregas
    //         ->getCollection()
    //         ->pluck('id')
    //         ->map(
    //             fn ($entregaId) =>
    //                 (int) $entregaId
    //         )
    //         ->filter(
    //             fn (int $entregaId) =>
    //                 $entregaId > 0
    //         )
    //         ->values();

    //     $romaneiosTratativa = collect();

    //     if ($entregasIdsDaPagina->isNotEmpty()) {
    //         $romaneiosTratativa = Romaneio::query()
    //             ->select([
    //                 'id',
    //                 'entrega_id',
    //                 'status',
    //             ])
    //             ->whereIn(
    //                 'entrega_id',
    //                 $entregasIdsDaPagina
    //             )
    //             ->whereHas('ocorrencias')
    //             ->orderByDesc('id')
    //             ->get()
    //             ->unique(
    //                 fn (Romaneio $romaneio) =>
    //                     (int) $romaneio->entrega_id
    //             )
    //             ->keyBy(
    //                 fn (Romaneio $romaneio) =>
    //                     (int) $romaneio->entrega_id
    //             );
    //     }

    //     $resumo = [
    //         'pendente_pagamento' =>
    //             Entrega::where(
    //                 'status',
    //                 'Pendente_pagamento'
    //             )->count(),

    //         'aguardando_separacao' =>
    //             Entrega::where(
    //                 'status',
    //                 'Aguardando_separacao'
    //             )->count(),

    //         'separando' =>
    //             Entrega::where(
    //                 'status',
    //                 'Em_preparacao'
    //             )->count(),

    //         'carregados' =>
    //             Entrega::where(
    //                 'status',
    //                 'Carregada'
    //             )->count(),

    //         'em_rota' =>
    //             Entrega::where(
    //                 'status',
    //                 'Em_rota'
    //             )->count(),

    //         'entregues' =>
    //             Entrega::whereIn(
    //                 'status',
    //                 [
    //                     'Entregue',
    //                     'Entregue_finalizada_com_ocorrencia',
    //                 ]
    //             )->count(),

    //         'parciais' =>
    //             Entrega::where(
    //                 'status',
    //                 'Entregue_parcial'
    //             )->count(),

    //         'devolvidos' =>
    //             Entrega::where(
    //                 'status',
    //                 'Devolvida'
    //             )->count(),

    //         'cancelados' =>
    //             Entrega::where(
    //                 'status',
    //                 'Cancelada'
    //             )->count(),

    //         'atrasadas' =>
    //             Entrega::whereDate(
    //                 'data_prevista',
    //                 '<',
    //                 now()->toDateString()
    //             )
    //                 ->whereNotIn(
    //                     'status',
    //                     [
    //                         'Entregue',
    //                         'Entregue_finalizada_com_ocorrencia',
    //                         'Cancelada',
    //                         'Devolvida',
    //                     ]
    //                 )
    //                 ->count(),
    //     ];

    //     return view(
    //         'entregas.index',
    //         compact(
    //             'entregas',
    //             'romaneiosTratativa',
    //             'resumo',
    //             'dataInicio',
    //             'dataFim'
    //         )
    //     );
    // }
    public function index(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'codigo_entrega' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'status' => [
                    'nullable',
                    'string',
                ],

                'data_inicio' => [
                    'nullable',
                    'date',
                ],

                'data_fim' => [
                    'nullable',
                    'date',
                    'after_or_equal:data_inicio',
                ],
            ],
            [
                'codigo_entrega.string' =>
                    'O código da entrega informado é inválido.',

                'codigo_entrega.max' =>
                    'O código da entrega pode possuir no máximo 100 caracteres.',

                'data_inicio.date' =>
                    'A data inicial informada é inválida.',

                'data_fim.date' =>
                    'A data final informada é inválida.',

                'data_fim.after_or_equal' =>
                    'A data final deve ser igual ou posterior à data inicial.',
            ]
        );

        /*
        * ============================================================
        * GRUPOS DE STATUS
        * ============================================================
        *
        * A mesma definição é utilizada:
        * - pelos cards;
        * - pelo clique dos cards;
        * - pelo filtro manual.
        */
        $statusFiltros = [
            'pendente_pagamento' => [
                'Pendente_pagamento',
            ],

            'aguardando_faturamento' => [
                'Aguardando_faturamento',
            ],

            'aguardando_separacao' => [
                'Aguardando_separacao',
            ],

            'separando' => [
                'Em_preparacao',
            ],

            'em_preparacao' => [
                'Em_preparacao',
            ],

            'pronta_para_carregamento' => [
                'Pronta_para_carregamento',
            ],

            'carregado' => [
                'Carregada',
            ],

            'carregada' => [
                'Carregada',
            ],

            'liberada' => [
                'Liberada',
            ],

            'em_rota' => [
                'Em_rota',
            ],

            'no_destino' => [
                'No_destino',
            ],

            /*
            * O card Entregues representa os dois estados.
            */
            'entregue' => [
                'Entregue',
                'Entregue_finalizada_com_ocorrencia',
            ],

            'finalizada_com_ocorrencia' => [
                'Entregue_finalizada_com_ocorrencia',
            ],

            'entregue_finalizada_com_ocorrencia' => [
                'Entregue_finalizada_com_ocorrencia',
            ],

            'parcial' => [
                'Entregue_parcial',
            ],

            'entregue_parcial' => [
                'Entregue_parcial',
            ],

            'nao_entregue' => [
                'Nao_entregue',
            ],

            'recusada' => [
                'Recusada',
            ],

            'reagendada' => [
                'Reagendada',
            ],

            'devolvido' => [
                'Devolvida',
            ],

            'devolvida' => [
                'Devolvida',
            ],

            'cancelado' => [
                'Cancelada',
            ],

            'cancelada' => [
                'Cancelada',
            ],
        ];

        /*
        * Nomes utilizados nas mensagens da tabela.
        */
        $statusLabelsFiltro = [
            'pendente_pagamento' =>
                'pendente de pagamento',

            'aguardando_faturamento' =>
                'aguardando faturamento',

            'aguardando_separacao' =>
                'aguardando separação',

            'separando' =>
                'em separação',

            'em_preparacao' =>
                'em preparação',

            'pronta_para_carregamento' =>
                'pronta para carregamento',

            'carregado' =>
                'carregada',

            'carregada' =>
                'carregada',

            'liberada' =>
                'liberada',

            'em_rota' =>
                'em rota',

            'no_destino' =>
                'no destino',

            'entregue' =>
                'entregue',

            'finalizada_com_ocorrencia' =>
                'finalizada com ocorrência',

            'entregue_finalizada_com_ocorrencia' =>
                'finalizada com ocorrência',

            'parcial' =>
                'entregue parcialmente',

            'entregue_parcial' =>
                'entregue parcialmente',

            'nao_entregue' =>
                'não entregue',

            'recusada' =>
                'recusada',

            'reagendada' =>
                'reagendada',

            'devolvido' =>
                'devolvida',

            'devolvida' =>
                'devolvida',

            'cancelado' =>
                'cancelada',

            'cancelada' =>
                'cancelada',
        ];

        /*
        * ============================================================
        * PARÂMETROS
        * ============================================================
        */
        $codigoEntregaInformado = trim(
            (string) (
                $dadosValidados['codigo_entrega']
                ?? ''
            )
        );

        $statusInformado = strtolower(
            trim(
                (string) (
                    $dadosValidados['status']
                    ?? ''
                )
            )
        );

        /*
        * Datas explicitamente enviadas pelo formulário.
        *
        * Card:
        * /entregas?status=em_rota
        *
        * Portanto não possui data_inicio/data_fim.
        */
        $dataInicioInformada = ! empty(
            $dadosValidados['data_inicio'] ?? null
        );

        $dataFimInformada = ! empty(
            $dadosValidados['data_fim'] ?? null
        );

        $datasInformadas =
            $dataInicioInformada
            || $dataFimInformada;

        /*
        * Datas padrão da tela.
        */
        $dataInicio = $dataInicioInformada
            ? \Carbon\Carbon::parse(
                $dadosValidados['data_inicio']
            )->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dataFim = $dataFimInformada
            ? \Carbon\Carbon::parse(
                $dadosValidados['data_fim']
            )->endOfDay()
            : now()->addDays(30)->endOfDay();

        /*
        * ============================================================
        * IDENTIFICAÇÃO DO MODO DE CONSULTA
        * ============================================================
        */

        /*
        * Código tem prioridade máxima.
        */
        $filtroPorCodigo =
            $codigoEntregaInformado !== '';

        /*
        * Status sem datas = clique em card.
        */
        $filtroPorCard =
            ! $filtroPorCodigo
            && $statusInformado !== ''
            && ! $datasInformadas;

        /*
        * Status com datas = filtro manual.
        */
        $filtroManualStatus =
            ! $filtroPorCodigo
            && $statusInformado !== ''
            && $datasInformadas;

        /*
        * Tela normal / consulta somente por período.
        */
        $consultaPeriodo =
            ! $filtroPorCodigo
            && ! $filtroPorCard
            && ! $filtroManualStatus;

        /*
        * Informa à Blade que status encerrados devem aparecer.
        *
        * Isso é essencial para o card Entregues.
        */
        $exibirStatusEncerrados =
            $filtroPorCodigo
            || $filtroPorCard
            || $filtroManualStatus;

        /*
        * ============================================================
        * QUERY BASE
        * ============================================================
        */
        $query = Entrega::query()
            ->with([
                'venda',
                'orcamento',
                'itens',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
                'bloqueioEdicaoAtivo.usuario',
            ]);

        /*
        * ============================================================
        * 1. CÓDIGO DA ENTREGA
        * ============================================================
        *
        * Não utiliza:
        * - status;
        * - data inicial;
        * - data final.
        */
        if ($filtroPorCodigo) {
            $query->where(
                'codigo_entrega',
                'like',
                '%' . $codigoEntregaInformado . '%'
            );
        }

        /*
        * ============================================================
        * 2. CARD
        * ============================================================
        *
        * Somente status.
        * Nenhuma restrição por data.
        */
        elseif ($filtroPorCard) {
            if (isset($statusFiltros[$statusInformado])) {
                $query->whereIn(
                    'status',
                    $statusFiltros[$statusInformado]
                );
            }
        }

        /*
        * ============================================================
        * 3. FILTRO MANUAL
        * ============================================================
        *
        * Status + Data Inicial + Data Final.
        */
        elseif ($filtroManualStatus) {
            $query->whereBetween(
                'data_prevista',
                [
                    $dataInicio,
                    $dataFim,
                ]
            );

            if (isset($statusFiltros[$statusInformado])) {
                $query->whereIn(
                    'status',
                    $statusFiltros[$statusInformado]
                );
            }
        }

        /*
        * ============================================================
        * 4. TELA NORMAL
        * ============================================================
        *
        * Usa a janela operacional padrão.
        */
        else {
            $query->whereBetween(
                'data_prevista',
                [
                    $dataInicio,
                    $dataFim,
                ]
            );

            /*
            * Entregas concluídas não ocupam a grade operacional
            * normal.
            */
            $query->whereNotIn(
                'status',
                [
                    'Entregue',
                    'Entregue_finalizada_com_ocorrencia',
                ]
            );
        }

        /*
        * ============================================================
        * ORDENAÇÃO
        * ============================================================
        */
        $query
            ->orderByRaw(
                "
                    CASE
                        WHEN LOWER(TRIM(status)) IN (
                            'cancelada',
                            'cancelado'
                        ) THEN 1
                        ELSE 0
                    END ASC
                "
            )
            ->orderBy('data_prevista')
            ->orderBy('id');

        $entregas = $query
            ->paginate(20)
            ->withQueryString();

        /*
        * ============================================================
        * MENSAGEM QUANDO NÃO HOUVER RESULTADO
        * ============================================================
        */
        if ($filtroPorCodigo) {
            $mensagemSemResultados =
                'Nenhuma entrega encontrada para o código '
                . $codigoEntregaInformado
                . '.';
        } elseif ($filtroPorCard) {
            $nomeStatus =
                $statusLabelsFiltro[$statusInformado]
                ?? str_replace(
                    '_',
                    ' ',
                    $statusInformado
                );

            $mensagemSemResultados =
                'Nenhuma entrega '
                . $nomeStatus
                . ' encontrada.';
        } elseif ($filtroManualStatus) {
            $nomeStatus =
                $statusLabelsFiltro[$statusInformado]
                ?? str_replace(
                    '_',
                    ' ',
                    $statusInformado
                );

            $mensagemSemResultados =
                'Nenhuma entrega '
                . $nomeStatus
                . ' encontrada no período informado.';
        } else {
            $mensagemSemResultados =
                'Nenhuma entrega encontrada no período informado.';
        }

        /*
        * ============================================================
        * BLOQUEIO DE EDIÇÃO
        * ============================================================
        */
        $usuarioId = (int) (
            $request->user()?->id
            ?? 0
        );

        $sessaoId = $request
            ->session()
            ->getId();

        $entregas
            ->getCollection()
            ->each(function (Entrega $entrega) use (
                $usuarioId,
                $sessaoId
            ) {
                $bloqueio =
                    $entrega->bloqueioEdicaoAtivo;

                $bloqueada = $bloqueio
                    && ! $this
                        ->bloqueioEdicaoService
                        ->podeEditar(
                            $bloqueio,
                            $usuarioId,
                            $sessaoId
                        );

                $entrega->setAttribute(
                    'edicao_bloqueada',
                    (bool) $bloqueada
                );

                $entrega->setAttribute(
                    'edicao_bloqueio_mensagem',
                    $bloqueada
                        ? $this
                            ->bloqueioEdicaoService
                            ->mensagemBloqueio(
                                $bloqueio
                            )
                        : null
                );
            });

        /*
        * ============================================================
        * ROMANEIOS / TRATATIVAS
        * ============================================================
        */
        $entregasIdsDaPagina = $entregas
            ->getCollection()
            ->pluck('id')
            ->map(
                fn ($entregaId) =>
                    (int) $entregaId
            )
            ->filter(
                fn (int $entregaId) =>
                    $entregaId > 0
            )
            ->values();

        $romaneiosTratativa = collect();

        if ($entregasIdsDaPagina->isNotEmpty()) {
            $romaneiosTratativa = Romaneio::query()
                ->select([
                    'id',
                    'entrega_id',
                    'status',
                ])
                ->whereIn(
                    'entrega_id',
                    $entregasIdsDaPagina
                )
                ->whereHas('ocorrencias')
                ->orderByDesc('id')
                ->get()
                ->unique(
                    fn (Romaneio $romaneio) =>
                        (int) $romaneio->entrega_id
                )
                ->keyBy(
                    fn (Romaneio $romaneio) =>
                        (int) $romaneio->entrega_id
                );
        }

        /*
        * ============================================================
        * CARDS
        * ============================================================
        *
        * Os cards não possuem nenhuma restrição de data.
        *
        * A contagem usa os mesmos grupos de status utilizados
        * pelo filtro do clique.
        */
        $resumo = [
            'pendente_pagamento' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['pendente_pagamento']
                )->count(),

            'aguardando_separacao' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['aguardando_separacao']
                )->count(),

            'separando' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['separando']
                )->count(),

            'carregados' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['carregado']
                )->count(),

            'em_rota' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['em_rota']
                )->count(),

            'entregues' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['entregue']
                )->count(),

            'parciais' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['entregue_parcial']
                )->count(),

            'devolvidos' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['devolvida']
                )->count(),

            'cancelados' =>
                Entrega::whereIn(
                    'status',
                    $statusFiltros['cancelada']
                )->count(),

            'atrasadas' =>
                Entrega::whereDate(
                    'data_prevista',
                    '<',
                    now()->toDateString()
                )
                    ->whereNotIn(
                        'status',
                        [
                            'Entregue',
                            'Entregue_finalizada_com_ocorrencia',
                            'Cancelada',
                            'Devolvida',
                        ]
                    )
                    ->count(),
        ];

        return view(
            'entregas.index',
            compact(
                'entregas',
                'romaneiosTratativa',
                'resumo',
                'dataInicio',
                'dataFim',
                'exibirStatusEncerrados',
                'mensagemSemResultados'
            )
        );
    }

    public function sincronizarEstados(Request $request): JsonResponse 
    {
        $dadosValidados = $request->validate([
            'entregas_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'entregas_ids.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
        ]);

        $entregasIds = collect(
            $dadosValidados['entregas_ids']
        )
            ->map(
                fn ($entregaId) =>
                    (int) $entregaId
            )
            ->unique()
            ->values();

        $entregas = Entrega::query()
            ->select([
                'id',
                'status',
                'updated_at',
            ])
            ->with([
                'bloqueioEdicaoAtivo.usuario',
            ])
            ->whereIn(
                'id',
                $entregasIds
            )
            ->get()
            ->keyBy(
                fn (Entrega $entrega) =>
                    (int) $entrega->id
            );

        $usuarioId = (int) (
            $request->user()?->id
            ?? 0
        );

        $sessaoId = $request
            ->session()
            ->getId();

        $resultados = $entregasIds
            ->map(function (
                int $entregaId
            ) use (
                $entregas,
                $usuarioId,
                $sessaoId
            ) {
                $entrega = $entregas->get(
                    $entregaId
                );

                if (! $entrega) {
                    return [
                        'entrega_id' =>
                            $entregaId,

                        'existe' =>
                            false,

                        'status' =>
                            null,

                        'bloqueado' =>
                            false,

                        'pode_editar' =>
                            false,

                        'usuario_id' =>
                            null,

                        'mensagem' =>
                            null,
                    ];
                }

                $bloqueio =
                    $entrega->bloqueioEdicaoAtivo;

                $podeEditar = ! $bloqueio
                    || $this
                        ->bloqueioEdicaoService
                        ->podeEditar(
                            $bloqueio,
                            $usuarioId,
                            $sessaoId
                        );

                return [
                    'entrega_id' =>
                        (int) $entrega->id,

                    'existe' =>
                        true,

                    'status' =>
                        strtolower(
                            trim(
                                (string) $entrega->status
                            )
                        ),

                    'bloqueado' =>
                        (bool) $bloqueio,

                    'pode_editar' =>
                        (bool) $podeEditar,

                    'usuario_id' =>
                        $bloqueio
                            ? (int) $bloqueio
                                ->usuario_id
                            : null,

                    'mensagem' =>
                        $bloqueio
                        && ! $podeEditar
                            ? $this
                                ->bloqueioEdicaoService
                                ->mensagemBloqueio(
                                    $bloqueio
                                )
                            : null,

                    'atualizado_em' =>
                        $entrega->updated_at
                            ?->toIso8601String(),
                ];
            })
            ->values();

        return response()
            ->json([
                'resultados' =>
                    $resultados,
            ])
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate'
            );
    }

    public function show(Entrega $entrega)
    {
        $entregaPrincipalId = (int) (
            $entrega->entrega_principal_id
            ?: $entrega->id
        );

        $entregasFamilia = Entrega::query()
            ->with([
                'motorista',
                'veiculo',
                'venda',
                'venda.cliente',
                'venda.itens.produto',
                'orcamento',
                'orcamento.cliente',
                'orcamento.itens.produto',
                'itens',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->where(function ($query) use (
                $entregaPrincipalId
            ) {
                $query
                    ->where(
                        'id',
                        $entregaPrincipalId
                    )
                    ->orWhere(
                        'entrega_principal_id',
                        $entregaPrincipalId
                    );
            })
            ->get()
            ->keyBy('id');

        if (! $entregasFamilia->has($entrega->id)) {
            abort(404);
        }

        /*
        * A cadeia contém somente a entrega atual e suas origens.
        * Entregas futuras ou de outro ramo não entram no histórico.
        */
        $cadeiaEntregas = collect();
        $entregaAtualId = (int) $entrega->id;
        $entregasVisitadas = [];

        while ($entregaAtualId > 0) {
            if (isset($entregasVisitadas[$entregaAtualId])) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "Foi identificado um ciclo na cadeia da entrega #{$entrega->id}.",
                ]);
            }

            $entregasVisitadas[$entregaAtualId] = true;

            $entregaDaCadeia = $entregasFamilia->get(
                $entregaAtualId
            );

            if (! $entregaDaCadeia) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A cadeia de fracionamento da entrega #{$entrega->id} está incompleta.",
                ]);
            }

            $cadeiaEntregas->prepend(
                $entregaDaCadeia
            );

            $entregaAtualId = (int) (
                $entregaDaCadeia->entrega_origem_id
                ?? 0
            );
        }

        $entrega = $entregasFamilia->get(
            (int) $entrega->id
        );

        $entregasIdsCadeia = $cadeiaEntregas
            ->pluck('id')
            ->map(
                fn ($entregaId) =>
                    (int) $entregaId
            )
            ->values();

        $romaneiosDaCadeia = Romaneio::query()
            ->with([
                'motorista',
                'veiculo',
                'eventos',
            ])
            ->whereIn(
                'entrega_id',
                $entregasIdsCadeia->all()
            )
            ->where(
                'status',
                '<>',
                'Cancelado'
            )
            ->orderBy('id')
            ->get();

        $romaneiosPorEntrega = $romaneiosDaCadeia
            ->groupBy(
                fn (Romaneio $romaneio) =>
                    (int) $romaneio->entrega_id
            )
            ->map(
                fn ($romaneiosEntrega) =>
                    $romaneiosEntrega->last()
            );

        $romaneio = $romaneiosPorEntrega->get(
            (int) $entrega->id
        );

        if (! $romaneio) {
            $romaneio = Romaneio::query()
                ->with([
                    'motorista',
                    'veiculo',
                    'eventos',
                ])
                ->where(
                    'entrega_id',
                    $entrega->id
                )
                ->latest('id')
                ->first();
        }

        $entrega->setRelation(
            'romaneio',
            $romaneio
        );

        $itensDaCadeia = $cadeiaEntregas
            ->flatMap(
                fn (Entrega $entregaDaCadeia) =>
                    $entregaDaCadeia->itens
            )
            ->values();

        $entregaItensIds = $itensDaCadeia
            ->pluck('id')
            ->map(
                fn ($entregaItemId) =>
                    (int) $entregaItemId
            )
            ->filter(
                fn (int $entregaItemId) =>
                    $entregaItemId > 0
            )
            ->values();

        $resultadosDiretosPorItem = collect();

        if ($entregaItensIds->isNotEmpty()) {
            $resultadosDiretosPorItem = DB::table(
                'romaneio_itens as ri'
            )
                ->join(
                    'romaneios as r',
                    'r.id',
                    '=',
                    'ri.romaneio_id'
                )
                ->whereIn(
                    'ri.entrega_item_id',
                    $entregaItensIds->all()
                )
                ->where(function ($query) {
                    $query
                        ->whereNull('ri.status')
                        ->orWhere(
                            'ri.status',
                            '<>',
                            'Cancelado'
                        );
                })
                ->where(function ($query) {
                    $query
                        ->whereNull('r.status')
                        ->orWhere(
                            'r.status',
                            '<>',
                            'Cancelado'
                        );
                })
                ->groupBy('ri.entrega_item_id')
                ->selectRaw(
                    'ri.entrega_item_id,
                    COALESCE(SUM(ri.quantidade_entregue), 0)
                        as quantidade_entregue,
                    COALESCE(SUM(ri.quantidade_devolvida), 0)
                        as quantidade_devolvida,
                    COALESCE(SUM(ri.quantidade_recusada), 0)
                        as quantidade_recusada,
                    COALESCE(SUM(ri.quantidade_avariada), 0)
                        as quantidade_avariada,
                    COALESCE(SUM(ri.quantidade_perdida), 0)
                        as quantidade_perdida'
                )
                ->get()
                ->keyBy(
                    fn ($resultado) =>
                        (int) $resultado->entrega_item_id
                );
        }

        $itensAtuaisIds = $entrega->itens
            ->pluck('id')
            ->map(
                fn ($entregaItemId) =>
                    (int) $entregaItemId
            )
            ->values();

        $resultadosItens = $itensAtuaisIds
            ->mapWithKeys(
                fn (int $entregaItemId) => [
                    $entregaItemId =>
                        $resultadosDiretosPorItem->get(
                            $entregaItemId
                        ),
                ]
            )
            ->filter();

        $quantidadesEncaminhadasPorItem = collect();

        if ($entregaItensIds->isNotEmpty()) {
            $quantidadesEncaminhadasPorItem = DB::table(
                'entrega_fracionamentos as ef'
            )
                ->leftJoin(
                    'entregas as ed',
                    'ed.id',
                    '=',
                    'ef.entrega_destino_id'
                )
                ->leftJoin(
                    'romaneios as rd',
                    'rd.id',
                    '=',
                    'ef.romaneio_destino_id'
                )
                ->whereIn(
                    'ef.entrega_item_origem_id',
                    $entregaItensIds->all()
                )
                ->where(function ($query) {
                    $query
                        ->whereNull('ed.status')
                        ->orWhere(
                            'ed.status',
                            '<>',
                            'Cancelada'
                        );
                })
                ->where(function ($query) {
                    $query
                        ->whereNull('rd.status')
                        ->orWhere(
                            'rd.status',
                            '<>',
                            'Cancelado'
                        );
                })
                ->groupBy(
                    'ef.entrega_item_origem_id'
                )
                ->selectRaw(
                    'ef.entrega_item_origem_id,
                    COALESCE(SUM(ef.quantidade), 0)
                        as quantidade'
                )
                ->get()
                ->mapWithKeys(
                    fn ($fracionamento) => [
                        (int) $fracionamento
                            ->entrega_item_origem_id =>
                            round(
                                (float) $fracionamento
                                    ->quantidade,
                                3
                            ),
                    ]
                );
        }

        $normalizarResultado = static function (
            $resultado
        ): array {
            $entregue = round(
                (float) (
                    $resultado?->quantidade_entregue
                    ?? 0
                ),
                3
            );

            $devolvida = round(
                (float) (
                    $resultado?->quantidade_devolvida
                    ?? 0
                ),
                3
            );

            $recusada = round(
                (float) (
                    $resultado?->quantidade_recusada
                    ?? 0
                ),
                3
            );

            $avariada = round(
                (float) (
                    $resultado?->quantidade_avariada
                    ?? 0
                ),
                3
            );

            $perdida = round(
                (float) (
                    $resultado?->quantidade_perdida
                    ?? 0
                ),
                3
            );

            return [
                'entregue' =>
                    $entregue,

                'ocorrencia' =>
                    round(
                        $devolvida
                        + $recusada
                        + $avariada
                        + $perdida,
                        3
                    ),
            ];
        };

        $statusEntregasPorId = $cadeiaEntregas
            ->mapWithKeys(
                fn (Entrega $entregaDaCadeia) => [
                    (int) $entregaDaCadeia->id =>
                        strtolower(
                            trim(
                                (string) $entregaDaCadeia
                                    ->status
                            )
                        ),
                ]
            );

        $contextosItensPorPrincipal = $itensDaCadeia
            ->groupBy(
                fn ($entregaItem) =>
                    (int) (
                        $entregaItem
                            ->entrega_item_principal_id
                        ?: $entregaItem->id
                    )
            )
            ->map(function ($itensDoProduto) use (
                $entrega,
                $resultadosDiretosPorItem,
                $quantidadesEncaminhadasPorItem,
                $statusEntregasPorId,
                $normalizarResultado
            ) {
                $itensAnteriores = $itensDoProduto
                    ->where(
                        'entrega_id',
                        '<>',
                        $entrega->id
                    );

                $itensAtuais = $itensDoProduto
                    ->where(
                        'entrega_id',
                        $entrega->id
                    );

                $entregueAnterior = 0.0;
                $ocorrenciaAnterior = 0.0;
                $ocorrenciaFinalizadaAnterior = 0.0;

                foreach ($itensAnteriores as $itemAnterior) {
                    $resultado = $normalizarResultado(
                        $resultadosDiretosPorItem->get(
                            (int) $itemAnterior->id
                        )
                    );

                    $entregueAnterior +=
                        $resultado['entregue'];

                    $ocorrenciaAnterior +=
                        $resultado['ocorrencia'];

                    $statusEntregaAnterior =
                        $statusEntregasPorId->get(
                            (int) $itemAnterior->entrega_id,
                            ''
                        );

                    $quantidadeEncaminhadaAnterior =
                        (float) $quantidadesEncaminhadasPorItem
                            ->get(
                                (int) $itemAnterior->id,
                                0
                            );

                    if (
                        $statusEntregaAnterior
                            === 'entregue_finalizada_com_ocorrencia'
                        && $quantidadeEncaminhadaAnterior
                            < 0.001
                    ) {
                        $ocorrenciaFinalizadaAnterior +=
                            $resultado['ocorrencia'];
                    }
                }

                $entregueAtual = 0.0;
                $ocorrenciaAtual = 0.0;
                $previstoAtual = 0.0;
                $encaminhadoProxima = 0.0;

                foreach ($itensAtuais as $itemAtual) {
                    $resultado = $normalizarResultado(
                        $resultadosDiretosPorItem->get(
                            (int) $itemAtual->id
                        )
                    );

                    $entregueAtual +=
                        $resultado['entregue'];

                    $ocorrenciaAtual +=
                        $resultado['ocorrencia'];

                    $previstoAtual += (float) (
                        $itemAtual->quantidade_prevista
                        ?? 0
                    );

                    $encaminhadoProxima += (float) (
                        $quantidadesEncaminhadasPorItem->get(
                            (int) $itemAtual->id,
                            0
                        )
                    );
                }

                $statusEntregaAtual = strtolower(
                    trim(
                        (string) $entrega->status
                    )
                );

                $ocorrenciaFinalizadaAtual =
                    $statusEntregaAtual
                        === 'entregue_finalizada_com_ocorrencia'
                    && $encaminhadoProxima < 0.001
                        ? $ocorrenciaAtual
                        : 0.0;

                return [
                    'venda_item_id' =>
                        (int) (
                            $itensDoProduto
                                ->pluck('venda_item_id')
                                ->filter()
                                ->first()
                            ?? 0
                        ),

                    'item_orcamento_id' =>
                        (int) (
                            $itensDoProduto
                                ->pluck('item_orcamento_id')
                                ->filter()
                                ->first()
                            ?? 0
                        ),

                    'possui_item_atual' =>
                        $itensAtuais->isNotEmpty(),

                    'quantidade_prevista_atual' =>
                        round($previstoAtual, 3),

                    'quantidade_entregue_anterior' =>
                        round($entregueAnterior, 3),

                    'quantidade_ocorrencia_anterior' =>
                        round($ocorrenciaAnterior, 3),

                    'quantidade_ocorrencia_finalizada_anterior' =>
                        round(
                            $ocorrenciaFinalizadaAnterior,
                            3
                        ),

                    'quantidade_entregue_atual' =>
                        round($entregueAtual, 3),

                    'quantidade_ocorrencia_atual' =>
                        round($ocorrenciaAtual, 3),

                    'quantidade_ocorrencia_finalizada_atual' =>
                        round(
                            $ocorrenciaFinalizadaAtual,
                            3
                        ),

                    'quantidade_encaminhada_proxima' =>
                        round($encaminhadoProxima, 3),
                ];
            });

        $contextosItensVenda = collect();
        $contextosItensOrcamento = collect();

        foreach (
            $contextosItensPorPrincipal
            as $contextoItem
        ) {
            if ($contextoItem['venda_item_id'] > 0) {
                $contextosItensVenda->put(
                    $contextoItem['venda_item_id'],
                    $contextoItem
                );
            }

            if ($contextoItem['item_orcamento_id'] > 0) {
                $contextosItensOrcamento->put(
                    $contextoItem['item_orcamento_id'],
                    $contextoItem
                );
            }
        }

        /*
        * Gera uma fotografia acumulada de cada documento da cadeia.
        * Cada fotografia considera somente a entrega exibida e suas
        * antecessoras, preservando a ordem cronológica do fracionamento.
        */
        $estadoAcumuladoPorItemPrincipal = collect();

        $documentosEntregasFracionadas = $cadeiaEntregas
            ->values()
            ->map(function (Entrega $entregaDocumento) use (
                &$estadoAcumuladoPorItemPrincipal,
                $resultadosDiretosPorItem,
                $quantidadesEncaminhadasPorItem,
                $normalizarResultado
            ) {
                $movimentoAtualPorItemPrincipal = collect();

                $statusEntregaDocumento = strtolower(
                    trim(
                        (string) $entregaDocumento->status
                    )
                );

                foreach (
                    $entregaDocumento->itens
                    as $entregaItemDocumento
                ) {
                    $itemPrincipalId = (int) (
                        $entregaItemDocumento
                            ->entrega_item_principal_id
                        ?: $entregaItemDocumento->id
                    );

                    $resultadoDocumento = $normalizarResultado(
                        $resultadosDiretosPorItem->get(
                            (int) $entregaItemDocumento->id
                        )
                    );

                    $quantidadeEncaminhada = round(
                        (float) $quantidadesEncaminhadasPorItem
                            ->get(
                                (int) $entregaItemDocumento->id,
                                0
                            ),
                        3
                    );

                    $ocorrenciaFinalizada =
                        $statusEntregaDocumento
                            === 'entregue_finalizada_com_ocorrencia'
                        && $quantidadeEncaminhada < 0.001
                            ? $resultadoDocumento['ocorrencia']
                            : 0.0;

                    $estadoAnterior = $estadoAcumuladoPorItemPrincipal
                        ->get(
                            $itemPrincipalId,
                            [
                                'venda_item_id' =>
                                    0,

                                'item_orcamento_id' =>
                                    0,

                                'quantidade_entregue' =>
                                    0.0,

                                'quantidade_ocorrencia' =>
                                    0.0,

                                'quantidade_ocorrencia_finalizada' =>
                                    0.0,
                            ]
                        );

                    $movimentoAnterior = $movimentoAtualPorItemPrincipal
                        ->get(
                            $itemPrincipalId,
                            [
                                'quantidade_prevista' =>
                                    0.0,

                                'quantidade_entregue' =>
                                    0.0,

                                'quantidade_ocorrencia' =>
                                    0.0,

                                'quantidade_ocorrencia_finalizada' =>
                                    0.0,

                                'quantidade_encaminhada' =>
                                    0.0,

                                'status_operacional' =>
                                    'pendente',
                            ]
                        );

                    $statusOperacional = strtolower(
                        trim(
                            str_replace(
                                ' ',
                                '_',
                                (string) (
                                    $entregaItemDocumento->status
                                    ?? 'pendente'
                                )
                            )
                        )
                    );

                    $movimentoAtualPorItemPrincipal->put(
                        $itemPrincipalId,
                        [
                            'quantidade_prevista' =>
                                round(
                                    $movimentoAnterior[
                                        'quantidade_prevista'
                                    ]
                                    + (float) $entregaItemDocumento
                                        ->quantidade_prevista,
                                    3
                                ),

                            'quantidade_entregue' =>
                                round(
                                    $movimentoAnterior[
                                        'quantidade_entregue'
                                    ]
                                    + $resultadoDocumento['entregue'],
                                    3
                                ),

                            'quantidade_ocorrencia' =>
                                round(
                                    $movimentoAnterior[
                                        'quantidade_ocorrencia'
                                    ]
                                    + $resultadoDocumento['ocorrencia'],
                                    3
                                ),

                            'quantidade_ocorrencia_finalizada' =>
                                round(
                                    $movimentoAnterior[
                                        'quantidade_ocorrencia_finalizada'
                                    ]
                                    + $ocorrenciaFinalizada,
                                    3
                                ),

                            'quantidade_encaminhada' =>
                                round(
                                    $movimentoAnterior[
                                        'quantidade_encaminhada'
                                    ]
                                    + $quantidadeEncaminhada,
                                    3
                                ),

                            'status_operacional' =>
                                $statusOperacional,
                        ]
                    );

                    $estadoAcumuladoPorItemPrincipal->put(
                        $itemPrincipalId,
                        [
                            'venda_item_id' =>
                                (int) (
                                    $entregaItemDocumento
                                        ->venda_item_id
                                    ?: $estadoAnterior[
                                        'venda_item_id'
                                    ]
                                ),

                            'item_orcamento_id' =>
                                (int) (
                                    $entregaItemDocumento
                                        ->item_orcamento_id
                                    ?: $estadoAnterior[
                                        'item_orcamento_id'
                                    ]
                                ),

                            'quantidade_entregue' =>
                                round(
                                    $estadoAnterior[
                                        'quantidade_entregue'
                                    ]
                                    + $resultadoDocumento['entregue'],
                                    3
                                ),

                            'quantidade_ocorrencia' =>
                                round(
                                    $estadoAnterior[
                                        'quantidade_ocorrencia'
                                    ]
                                    + $resultadoDocumento['ocorrencia'],
                                    3
                                ),

                            'quantidade_ocorrencia_finalizada' =>
                                round(
                                    $estadoAnterior[
                                        'quantidade_ocorrencia_finalizada'
                                    ]
                                    + $ocorrenciaFinalizada,
                                    3
                                ),
                        ]
                    );
                }

                $contextosVendaDocumento = collect();
                $contextosOrcamentoDocumento = collect();

                foreach (
                    $estadoAcumuladoPorItemPrincipal
                    as $itemPrincipalId => $estadoAcumulado
                ) {
                    $movimentoAtual = $movimentoAtualPorItemPrincipal
                        ->get(
                            $itemPrincipalId,
                            [
                                'quantidade_prevista' =>
                                    0.0,

                                'quantidade_entregue' =>
                                    0.0,

                                'quantidade_ocorrencia' =>
                                    0.0,

                                'quantidade_ocorrencia_finalizada' =>
                                    0.0,

                                'quantidade_encaminhada' =>
                                    0.0,

                                'status_operacional' =>
                                    'pendente',
                            ]
                        );

                    $contextoDocumento = [
                        'venda_item_id' =>
                            $estadoAcumulado['venda_item_id'],

                        'item_orcamento_id' =>
                            $estadoAcumulado['item_orcamento_id'],

                        'possui_item_atual' =>
                            $movimentoAtualPorItemPrincipal
                                ->has($itemPrincipalId),

                        'quantidade_prevista_atual' =>
                            $movimentoAtual[
                                'quantidade_prevista'
                            ],

                        'quantidade_entregue_anterior' =>
                            round(
                                $estadoAcumulado[
                                    'quantidade_entregue'
                                ]
                                - $movimentoAtual[
                                    'quantidade_entregue'
                                ],
                                3
                            ),

                        'quantidade_ocorrencia_anterior' =>
                            round(
                                $estadoAcumulado[
                                    'quantidade_ocorrencia'
                                ]
                                - $movimentoAtual[
                                    'quantidade_ocorrencia'
                                ],
                                3
                            ),

                        'quantidade_ocorrencia_finalizada_anterior' =>
                            round(
                                $estadoAcumulado[
                                    'quantidade_ocorrencia_finalizada'
                                ]
                                - $movimentoAtual[
                                    'quantidade_ocorrencia_finalizada'
                                ],
                                3
                            ),

                        'quantidade_entregue_atual' =>
                            $movimentoAtual[
                                'quantidade_entregue'
                            ],

                        'quantidade_ocorrencia_atual' =>
                            $movimentoAtual[
                                'quantidade_ocorrencia'
                            ],

                        'quantidade_ocorrencia_finalizada_atual' =>
                            $movimentoAtual[
                                'quantidade_ocorrencia_finalizada'
                            ],

                        'quantidade_encaminhada_proxima' =>
                            $movimentoAtual[
                                'quantidade_encaminhada'
                            ],

                        'status_operacional_atual' =>
                            $movimentoAtual[
                                'status_operacional'
                            ],
                    ];

                    if ($contextoDocumento['venda_item_id'] > 0) {
                        $contextosVendaDocumento->put(
                            $contextoDocumento['venda_item_id'],
                            $contextoDocumento
                        );
                    }

                    if (
                        $contextoDocumento[
                            'item_orcamento_id'
                        ] > 0
                    ) {
                        $contextosOrcamentoDocumento->put(
                            $contextoDocumento[
                                'item_orcamento_id'
                            ],
                            $contextoDocumento
                        );
                    }
                }

                return [
                    'entrega' =>
                        $entregaDocumento,

                    'contextos_venda' =>
                        $contextosVendaDocumento,

                    'contextos_orcamento' =>
                        $contextosOrcamentoDocumento,
                ];
            });

        if ($romaneio) {
            $romaneiosPorEntrega->put(
                (int) $entrega->id,
                $romaneio
            );
        }

        $statusEntregaAtualHistorico = strtolower(
            trim(
                str_replace(
                    ' ',
                    '_',
                    (string) ($entrega->status ?? '')
                )
            )
        );

        $statusRomaneioAtualHistorico = strtolower(
            trim(
                str_replace(
                    ' ',
                    '_',
                    (string) ($romaneio?->status ?? '')
                )
            )
        );

        $entregaAtualFinalizada = in_array(
            $statusEntregaAtualHistorico,
            [
                'entregue',
                'entregue_finalizada_com_ocorrencia',
                'nao_entregue',
                'recusada',
                'devolvida',
                'cancelada',
            ],
            true
        );

        $romaneioAtualFinalizado = in_array(
            $statusRomaneioAtualHistorico,
            [
                'fechado',
                'cancelado',
            ],
            true
        );

        $incluirEntregaAtualNoHistorico =
            $entregaAtualFinalizada
            || $romaneioAtualFinalizado;

        $historicoEntregasFracionadas = $cadeiaEntregas
            ->filter(
                fn (Entrega $entregaHistorica) =>
                    (int) $entregaHistorica->id
                        !== (int) $entrega->id
                    || $incluirEntregaAtualNoHistorico
            )
            ->values()
            ->map(function (Entrega $entregaHistorica) use (
                $romaneiosPorEntrega,
                $resultadosDiretosPorItem,
                $quantidadesEncaminhadasPorItem,
                $normalizarResultado
            ) {
                $itensHistoricos = $entregaHistorica->itens
                    ->map(function ($entregaItem) use (
                        $resultadosDiretosPorItem,
                        $quantidadesEncaminhadasPorItem,
                        $normalizarResultado
                    ) {
                        $resultado = $normalizarResultado(
                            $resultadosDiretosPorItem->get(
                                (int) $entregaItem->id
                            )
                        );

                        $produto = $entregaItem
                            ->vendaItem
                            ?->produto
                            ?? $entregaItem
                                ->itemOrcamento
                                ?->produto;

                        return [
                            'produto' =>
                                $produto?->nome
                                ?? $produto?->descricao
                                ?? 'Produto não identificado',

                            'previsto' =>
                                round(
                                    (float) $entregaItem
                                        ->quantidade_prevista,
                                    3
                                ),

                            'entregue' =>
                                $resultado['entregue'],

                            'ocorrencia' =>
                                $resultado['ocorrencia'],

                            'encaminhado' =>
                                round(
                                    (float) $quantidadesEncaminhadasPorItem
                                        ->get(
                                            (int) $entregaItem->id,
                                            0
                                        ),
                                    3
                                ),
                        ];
                    })
                    ->values();

                $romaneioHistorico = $romaneiosPorEntrega->get(
                    (int) $entregaHistorica->id
                );

                return [
                    'entrega' =>
                        $entregaHistorica,

                    'romaneio' =>
                        $romaneioHistorico,

                    'itens' =>
                        $itensHistoricos,

                    'quantidade_entregue' =>
                        round(
                            (float) $itensHistoricos
                                ->sum('entregue'),
                            3
                        ),

                    'quantidade_encaminhada' =>
                        round(
                            (float) $entregaHistorica
                                ->itens
                                ->sum(
                                    fn ($entregaItem) =>
                                        (float) $quantidadesEncaminhadasPorItem
                                            ->get(
                                                (int) $entregaItem->id,
                                                0
                                            )
                                ),
                            3
                        ),
                ];
            });

        return view(
            'entregas.show',
            compact(
                'entrega',
                'resultadosItens',
                'contextosItensVenda',
                'contextosItensOrcamento',
                'documentosEntregasFracionadas',
                'historicoEntregasFracionadas'
            )
        );
    }

    public function separar(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'Separando', 'Entrega enviada para separação.');
    }

    public function carregar(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'Carregado', 'Entrega marcada como carregada.');
    }

    public function enviarParaRota(Entrega $entrega)
    {
        return $this->alterarStatusComRetorno($entrega, 'Em_rota', 'Entrega enviada para rota.');
    }

    public function confirmar(Entrega $entrega)
    {
        try {
            if ($entrega->status !== 'Em_rota') {
                throw ValidationException::withMessages([
                    'status' => 'A entrega só pode ser confirmada quando estiver Em rota.',
                ]);
            }

            $this->entregaService->confirmarEntrega($entrega);

            return redirect()
                ->back()
                ->with('success', 'Entrega confirmada com sucesso.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao confirmar entrega: ' . $e->getMessage());
        }
    }

    public function confirmarParcial(Request $request, Entrega $entrega)
    {
        $dados = $request->validate([
            'itens' => ['required', 'array'],
            'itens.*.entrega_item_id' => ['required', 'integer', 'exists:entrega_itens,id'],
            'itens.*.quantidade_entregue' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->entregaService->confirmarParcial($entrega, $dados['itens']);

            return redirect()
                ->back()
                ->with('success', 'Entrega parcial registrada com sucesso.');

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao registrar entrega parcial: ' . $e->getMessage());
        }
    }

    public function retorno(Entrega $entrega)
    {
        $romaneio = Romaneio::query()
            ->with([
                'motorista',
                'veiculo',

                'itens.conferenteRetorno',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',
            ])
            ->where('entrega_id', $entrega->id)
            ->whereNotIn('status', [
                'Fechado',
                'Cancelado',
            ])
            ->latest('id')
            ->first();

        if (! $romaneio) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'Não foi encontrado um romaneio ativo para esta entrega.'
                );
        }

        $statusPermitidos = [
            'Em_rota',
            'Retornando',
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
        ];

        if (! in_array(
            $romaneio->status,
            $statusPermitidos,
            true
        )) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'O romaneio não está disponível para operação de retorno.'
                );
        }

        $entrega->loadMissing([
            'cliente',
            'motorista',
            'veiculo',
            'venda.cliente',
            'orcamento.cliente',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ]);

        $funcionariosOperacionais = Funcionario::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        return view(
            'entregas.retorno',
            compact(
                'entrega',
                'romaneio',
                'funcionariosOperacionais'
            )
        );
    }

    public function iniciarConferenciaRetorno(Request $request, Entrega $entrega)
    {
        $dadosValidados = $request->validate(
            [
                'retorno_conferido_por' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],
            ],
            [
                'retorno_conferido_por.required' =>
                    'Informe o funcionário responsável pela conferência do retorno.',

                'retorno_conferido_por.integer' =>
                    'O funcionário informado é inválido.',

                'retorno_conferido_por.exists' =>
                    'O funcionário selecionado não foi encontrado.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where('entrega_id', $entrega->id)
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (
                $romaneio->status
                !== 'Aguardando_conferencia_retorno'
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'O romaneio não está aguardando conferência do retorno.',
                ]);
            }

            /*
            * Enviamos somente a identificação dos itens.
            * As quantidades permanecem com a declaração inicial
            * até o conferente verificar fisicamente os produtos.
            */
            $itens = $romaneio->itens
                ->map(
                    fn ($item) => [
                        'romaneio_item_id' =>
                            $item->id,

                        'entrega_item_id' =>
                            $item->entrega_item_id,
                    ]
                )
                ->values()
                ->all();

            $this->romaneioService->atualizarOperacao(
                $romaneio,
                'iniciar_conferencia_retorno',
                [
                    'retorno_conferido_por' =>
                        $dadosValidados['retorno_conferido_por'],

                    'itens' =>
                        $itens,
                ]
            );

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Conferência física do retorno iniciada.'
                );

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao iniciar a conferência do retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function finalizarConferenciaRetorno(Request $request, Entrega $entrega) 
    {
        $dadosValidados = $request->validate(
            [
                'retorno_conferido_por' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'itens' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'itens.*.romaneio_item_id' => [
                    'required',
                    'integer',
                    'exists:romaneio_itens,id',
                ],

                'itens.*.entrega_item_id' => [
                    'required',
                    'integer',
                    'exists:entrega_itens,id',
                ],

                'itens.*.quantidade_entregue' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_devolvida' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_recusada' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_avariada' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_perdida' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'itens.*.observacao' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ],
            [
                'retorno_conferido_por.required' =>
                    'Informe o responsável pela conferência.',

                'itens.required' =>
                    'Informe o resultado conferido dos produtos.',

                'itens.min' =>
                    'Informe o resultado de pelo menos um produto.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where('entrega_id', $entrega->id)
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (
                $romaneio->status
                !== 'Em_conferencia_retorno'
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'A conferência do retorno ainda não foi iniciada.',
                ]);
            }

            $itensDoRomaneio = $romaneio->itens->keyBy('id');

            foreach (
                $dadosValidados['itens']
                as $indice => $dadosItem
            ) {
                $romaneioItem = $itensDoRomaneio->get(
                    (int) $dadosItem['romaneio_item_id']
                );

                if (
                    ! $romaneioItem
                    || (int) $romaneioItem->entrega_item_id
                        !== (int) $dadosItem['entrega_item_id']
                ) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.romaneio_item_id" =>
                            'O produto informado não pertence a este romaneio.',
                    ]);
                }

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    2
                );

                $totalResultado = round(
                    (float) $dadosItem['quantidade_entregue']
                    + (float) $dadosItem['quantidade_devolvida']
                    + (float) $dadosItem['quantidade_recusada']
                    + (float) $dadosItem['quantidade_avariada']
                    + (float) $dadosItem['quantidade_perdida'],
                    2
                );

                if ($totalResultado !== $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.quantidade_entregue" =>
                            "A soma do resultado do item #{$romaneioItem->entrega_item_id} deve ser igual à quantidade conferida na saída: "
                            . number_format(
                                $quantidadeSaida,
                                2,
                                ',',
                                '.'
                            )
                            . '.',
                    ]);
                }
            }

            $this->romaneioService->atualizarOperacao(
                $romaneio,
                'finalizar_conferencia_retorno',
                [
                    'retorno_conferido_por' =>
                        $dadosValidados['retorno_conferido_por'],

                    'observacao' =>
                        $dadosValidados['observacao']
                        ?? null,

                    'itens' =>
                        $dadosValidados['itens'],
                ]
            );

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Conferência do retorno concluída. O romaneio está aguardando a prestação de contas.'
                );

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao finalizar a conferência do retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function registrarRetorno(Request $request, Entrega $entrega) 
    {
        $dadosBasicos = $request->validate(
            [
                'tipo_retorno' => [
                    'required',
                    Rule::in([
                        'normal',
                        'ocorrencia',
                    ]),
                ],

                'observacao_retorno' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'tipo_retorno.required' =>
                    'Informe se a entrega ocorreu normalmente ou se houve alguma falha.',

                'tipo_retorno.in' =>
                    'O tipo de retorno informado é inválido.',

                'observacao_retorno.max' =>
                    'A observação geral pode possuir no máximo 1000 caracteres.',
            ]
        );

        try {
            $romaneio = Romaneio::query()
                ->with('itens')
                ->where(
                    'entrega_id',
                    $entrega->id
                )
                ->whereNotIn(
                    'status',
                    [
                        'Fechado',
                        'Cancelado',
                    ]
                )
                ->latest('id')
                ->first();

            if (! $romaneio) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Não foi encontrado um romaneio ativo para esta entrega.',
                ]);
            }

            if (
                ! in_array(
                    $romaneio->status,
                    [
                        'Em_rota',
                        'Retornando',
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'O romaneio não está disponível para registro de retorno.',
                ]);
            }

            if (
                $dadosBasicos['tipo_retorno']
                === 'normal'
            ) {
                $itens = $romaneio->itens
                    ->map(function ($romaneioItem) {
                        $quantidadeSaida = round(
                            (float) $romaneioItem
                                ->quantidade_conferida_saida,
                            2
                        );

                        return [
                            'romaneio_item_id' =>
                                $romaneioItem->id,

                            'entrega_item_id' =>
                                $romaneioItem
                                    ->entrega_item_id,

                            'quantidade_entregue' =>
                                $quantidadeSaida,

                            'quantidade_devolvida' =>
                                0,

                            'quantidade_recusada' =>
                                0,

                            'quantidade_avariada' =>
                                0,

                            'quantidade_perdida' =>
                                0,

                            'observacao' =>
                                null,
                        ];
                    })
                    ->values()
                    ->all();

                $observacaoRetorno =
                    'Entrega realizada normalmente, sem ocorrências.';
            } else {
                $dadosOcorrencia = $request->validate(
                    [
                        'itens' => [
                            'required',
                            'array',
                            'min:1',
                        ],

                        'itens.*.romaneio_item_id' => [
                            'required',
                            'integer',
                            'exists:romaneio_itens,id',
                        ],

                        'itens.*.entrega_item_id' => [
                            'required',
                            'integer',
                            'exists:entrega_itens,id',
                        ],

                        'itens.*.quantidade_entregue' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_devolvida' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_recusada' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_avariada' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.quantidade_perdida' => [
                            'required',
                            'numeric',
                            'min:0',
                        ],

                        'itens.*.observacao' => [
                            'nullable',
                            'string',
                            'max:500',
                        ],
                    ],
                    [
                        'itens.required' =>
                            'Informe o resultado dos produtos transportados.',

                        'itens.min' =>
                            'Informe o resultado de pelo menos um produto.',

                        'itens.*.romaneio_item_id.required' =>
                            'Não foi possível identificar um item do romaneio.',

                        'itens.*.entrega_item_id.required' =>
                            'Não foi possível identificar um item da entrega.',

                        'itens.*.quantidade_entregue.required' =>
                            'Informe a quantidade entregue.',

                        'itens.*.quantidade_devolvida.required' =>
                            'Informe a quantidade devolvida.',

                        'itens.*.quantidade_recusada.required' =>
                            'Informe a quantidade recusada.',

                        'itens.*.quantidade_avariada.required' =>
                            'Informe a quantidade avariada.',

                        'itens.*.quantidade_perdida.required' =>
                            'Informe a quantidade perdida.',
                    ]
                );

                $itens =
                    $dadosOcorrencia['itens'];

                $observacaoRetorno = trim(
                    (string) (
                        $dadosBasicos[
                            'observacao_retorno'
                        ]
                        ?? ''
                    )
                );

                $possuiOcorrenciaProduto =
                    collect($itens)
                        ->contains(
                            function ($item) {
                                return
                                    round(
                                        (float) (
                                            $item[
                                                'quantidade_devolvida'
                                            ]
                                            ?? 0
                                        ),
                                        2
                                    ) > 0
                                    || round(
                                        (float) (
                                            $item[
                                                'quantidade_recusada'
                                            ]
                                            ?? 0
                                        ),
                                        2
                                    ) > 0
                                    || round(
                                        (float) (
                                            $item[
                                                'quantidade_avariada'
                                            ]
                                            ?? 0
                                        ),
                                        2
                                    ) > 0
                                    || round(
                                        (float) (
                                            $item[
                                                'quantidade_perdida'
                                            ]
                                            ?? 0
                                        ),
                                        2
                                    ) > 0
                                    || trim(
                                        (string) (
                                            $item[
                                                'observacao'
                                            ]
                                            ?? ''
                                        )
                                    ) !== '';
                            }
                        );

                if (
                    ! $possuiOcorrenciaProduto
                    && $observacaoRetorno === ''
                ) {
                    throw ValidationException::withMessages([
                        'observacao_retorno' =>
                            'Descreva a falha ocorrida no trajeto ou informe a ocorrência de pelo menos um produto.',
                    ]);
                }
            }

            $itensDoRomaneio =
                $romaneio->itens
                    ->keyBy('id');

            foreach (
                $itens
                as $indice => $dadosItem
            ) {
                $romaneioItem =
                    $itensDoRomaneio->get(
                        (int) $dadosItem[
                            'romaneio_item_id'
                        ]
                    );

                if (
                    ! $romaneioItem
                    || (int) $romaneioItem
                        ->entrega_item_id
                        !== (int) $dadosItem[
                            'entrega_item_id'
                        ]
                ) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.romaneio_item_id" =>
                            'O produto informado não pertence a este romaneio.',
                    ]);
                }

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    2
                );

                $quantidadeEntregue = round(
                    (float) $dadosItem[
                        'quantidade_entregue'
                    ],
                    2
                );

                $quantidadeDevolvida = round(
                    (float) $dadosItem[
                        'quantidade_devolvida'
                    ],
                    2
                );

                $quantidadeRecusada = round(
                    (float) $dadosItem[
                        'quantidade_recusada'
                    ],
                    2
                );

                $quantidadeAvariada = round(
                    (float) $dadosItem[
                        'quantidade_avariada'
                    ],
                    2
                );

                $quantidadePerdida = round(
                    (float) $dadosItem[
                        'quantidade_perdida'
                    ],
                    2
                );

                $totalResultado = round(
                    $quantidadeEntregue
                    + $quantidadeDevolvida
                    + $quantidadeRecusada
                    + $quantidadeAvariada
                    + $quantidadePerdida,
                    2
                );

                if (
                    $totalResultado
                    !== $quantidadeSaida
                ) {
                    throw ValidationException::withMessages([
                        "itens.{$indice}.quantidade_entregue" =>
                            'A soma do resultado do produto deve ser igual à quantidade que saiu no romaneio: '
                            . number_format(
                                $quantidadeSaida,
                                2,
                                ',',
                                '.'
                            )
                            . '.',
                    ]);
                }
            }

            $this->romaneioService
                ->atualizarOperacao(
                    $romaneio,
                    'registrar_retorno',
                    [
                        'tipo_retorno' =>
                            $dadosBasicos[
                                'tipo_retorno'
                            ],

                        'itens' =>
                            $itens,

                        'observacao_retorno' =>
                            $observacaoRetorno,
                    ]
                );

            if (
                $dadosBasicos['tipo_retorno']
                === 'normal'
            ) {
                /*
                * O retorno normal conclui o fluxo operacional.
                * O middleware liberará o bloqueio dentro da
                * mesma transação da operação.
                */
                $request->attributes->set(
                    'liberarBloqueioEdicaoAoConcluir',
                    true
                );

                return redirect()
                    ->route(
                        'entregas.index'
                    )
                    ->with(
                        'success',
                        'Entrega finalizada normalmente. O romaneio foi encaminhado para a prestação de contas.'
                    );
            }

            return redirect()
                ->route(
                    'entregas.retorno',
                    $entrega->id
                )
                ->with(
                    'success',
                    'Retorno com ocorrência registrado. O romaneio está aguardando a conferência física.'
                );
        } catch (
            ValidationException $e
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(
                    $e->errors()
                );
        } catch (
            Throwable $e
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao registrar o retorno: '
                    . $e->getMessage()
                );
        }
    }

    public function imprimirRelatorioRetorno(Entrega $entrega) 
    {
        $entrega->loadMissing([
            'cliente',
            'motorista',
            'veiculo',
            'venda.cliente',
            'orcamento.cliente',
            'itens.vendaItem.produto',
            'itens.itemOrcamento.produto',
        ]);

        $romaneio = Romaneio::query()
            ->with([
                'motorista',
                'veiculo',
                'usuarioRegistroRetorno',

                'itens.conferenteRetorno',
                'itens.entregaItem',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',

                'eventos' => function ($query) {
                    $query->orderBy(
                        'ocorrido_em',
                        'asc'
                    );
                },

                'eventos.usuario',
                'eventos.funcionario',
            ])
            ->where(
                'entrega_id',
                $entrega->id
            )
            ->where(
                'status',
                '!=',
                'Cancelado'
            )
            ->latest('id')
            ->first();

        if (! $romaneio) {
            return redirect()
                ->route('entregas.show', $entrega->id)
                ->with(
                    'error',
                    'Não foi encontrado um romaneio para gerar o relatório de retorno.'
                );
        }

        $statusPermitidos = [
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
            'Fechado',
        ];

        if (
            ! in_array(
                $romaneio->status,
                $statusPermitidos,
                true
            )
            && empty($romaneio->data_retorno)
        ) {
            return redirect()
                ->route('entregas.retorno', $entrega->id)
                ->with(
                    'error',
                    'O retorno desta entrega ainda não foi registrado.'
                );
        }

        $cliente =
            $entrega->cliente
            ?? $entrega->venda?->cliente
            ?? $entrega->orcamento?->cliente;

        $eventoRetorno = $romaneio->eventos
            ->filter(function ($evento) {
                $nomeEvento = strtolower(
                    trim(
                        (string) (
                            $evento->evento
                            ?? ''
                        )
                    )
                );

                return str_contains(
                    $nomeEvento,
                    'retorno'
                );
            })
            ->last();

        $observacaoRetorno = trim(
            (string) (
                $eventoRetorno?->observacao
                ?? $romaneio->observacao
                ?? ''
            )
        );

        $possuiOcorrenciaProduto = $romaneio->itens
            ->contains(function ($item) {
                return
                    round(
                        (float) (
                            $item->quantidade_devolvida
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_recusada
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_avariada
                            ?? 0
                        ),
                        2
                    ) > 0
                    || round(
                        (float) (
                            $item->quantidade_perdida
                            ?? 0
                        ),
                        2
                    ) > 0
                    || trim(
                        (string) (
                            $item->observacao
                            ?? ''
                        )
                    ) !== '';
            });

        $eventoIndicaOcorrencia = $romaneio->eventos
            ->contains(function ($evento) {
                $nomeEvento = strtolower(
                    trim(
                        (string) (
                            $evento->evento
                            ?? ''
                        )
                    )
                );

                return str_contains(
                    $nomeEvento,
                    'ocorrência'
                )
                    || str_contains(
                        $nomeEvento,
                        'ocorrencia'
                    );
            });

        $possuiOcorrencias =
            $possuiOcorrenciaProduto
            || $eventoIndicaOcorrencia;

        return view(
            'entregas.relatorio-retorno',
            compact(
                'entrega',
                'romaneio',
                'cliente',
                'eventoRetorno',
                'observacaoRetorno',
                'possuiOcorrencias'
            )
        );
    }

}