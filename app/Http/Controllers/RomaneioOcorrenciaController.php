<?php

namespace App\Http\Controllers;

use App\Models\Romaneio;
use App\Models\RomaneioEquipe;
use App\Models\Lote;
use App\Models\RomaneioOcorrencia;
use App\Models\RomaneioOcorrenciaAvaliacao;
use App\Models\RomaneioOcorrenciaAnexo;
use App\Models\User;
use App\Services\Expedicao\RomaneioService;
use App\Services\Expedicao\RomaneioOcorrenciaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RomaneioOcorrenciaController extends Controller
{
    private const LIMITE_EVIDENCIAS = 4;

    public function __construct(
        private readonly RomaneioOcorrenciaService $ocorrenciaService,
        private readonly RomaneioService $romaneioService
    ) {
    }

    public function index(Romaneio $romaneio): View
    {
        $this->validarUsuarioAutenticado();

        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.venda.cliente',
            'entrega.orcamento.cliente',
            'ocorrencias' => fn ($query) => $query->latest('id'),
            'ocorrencias.entrega',
            'ocorrencias.entregaItem.vendaItem.produto',
            'ocorrencias.entregaItem.vendaItem.lote',
            'ocorrencias.entregaItem.itemOrcamento.produto',
            'ocorrencias.romaneioItem.entregaItem.vendaItem.produto',
            'ocorrencias.romaneioItem.entregaItem.vendaItem.lote',
            'ocorrencias.romaneioItem.entregaItem.itemOrcamento.produto',
            'ocorrencias.responsavelAnalise',
            'ocorrencias.autorizador',
            'ocorrencias.anexos.usuarioEnvio',
            'ocorrencias.historicos.usuarioRegistro',
            'ocorrencias.orcamentoReposicao',
            'ocorrencias.avaliacoes.lote',
            'ocorrencias.devolucao.lotes.avaliacao',
            'ocorrencias.devolucao.lotes.lote',
            'ocorrencias.devolucao.lotes.movimentacoes',
        ]);

        $responsaveis = User::query()
            ->orderBy('name')
            ->get();

        $equipe = RomaneioEquipe::query()
            ->with([
                'motorista',
                'ajudante',
                'veiculo',
            ])
            ->where(
                'romaneio_id',
                $romaneio->id
            )
            ->latest('id')
            ->first();

        return view(
            'romaneios.ocorrencias.index',
            compact(
                'romaneio',
                'responsaveis',
                'equipe'
            )
        );
    }
    
    public function triagem(
            Romaneio $romaneio
        ): View {
            $this->validarUsuarioAutenticado();

            $romaneio->load([
            'entrega.cliente',

            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.vendaItem.lote',
            'itens.entregaItem.itemOrcamento.produto',

            'ocorrencias' => fn ($query) =>
                $query->orderBy('id'),

            'ocorrencias.anexos',
            'ocorrencias.avaliacoes',
            'ocorrencias.avaliacoes.lote',

            'ocorrencias.entregaItem.vendaItem.produto',
            'ocorrencias.entregaItem.vendaItem.lote',
            'ocorrencias.entregaItem.itemOrcamento.produto',

            'ocorrencias.romaneioItem.entregaItem.vendaItem.produto',
            'ocorrencias.romaneioItem.entregaItem.vendaItem.lote',
            'ocorrencias.romaneioItem.entregaItem.itemOrcamento.produto',
        ]);

        $itensDisponiveis = $romaneio->itens
            ->filter(function ($romaneioItem) {
                $entregaItem =
                    $romaneioItem->entregaItem;

                if (! $entregaItem) {
                    return false;
                }

                return ! in_array(
                    $entregaItem->status,
                    [
                        'Cancelado',
                        'Cancelada',
                    ],
                    true
                );
            })
            ->values();

        $ocorrenciasAtivas = $romaneio->ocorrencias
            ->filter(
                fn (RomaneioOcorrencia $ocorrencia) =>
                    $ocorrencia->status !== 'Cancelada'
            )
            ->values();

        $ocorrenciasPorItem = $ocorrenciasAtivas
            ->groupBy(
                fn (RomaneioOcorrencia $ocorrencia) =>
                    (int) $ocorrencia->romaneio_item_id
            );

        /*
        * No fluxo originado por orçamento, item_vendas.lote_id
        * pode permanecer vazio. Nesse caso, o lote original
        * continua registrado em item_orcamento_lotes.
        */
        $itensOrcamentoIds = $itensDisponiveis
            ->map(
                fn ($romaneioItem) =>
                    $romaneioItem
                        ->entregaItem
                        ?->item_orcamento_id
            )
            ->filter()
            ->map(
                fn ($itemOrcamentoId) =>
                    (int) $itemOrcamentoId
            )
            ->unique()
            ->values();

        /*
        * Consulta única para evitar N+1.
        */
        $lotesOrigemPorItemOrcamento =
            $itensOrcamentoIds->isEmpty()
                ? collect()
                : \Illuminate\Support\Facades\DB::table(
                    'item_orcamento_lotes as iol'
                )
                    ->join(
                        'lotes as l',
                        'l.id',
                        '=',
                        'iol.lote_id'
                    )
                    ->whereIn(
                        'iol.item_orcamento_id',
                        $itensOrcamentoIds->all()
                    )
                    ->select([
                        'iol.id',
                        'iol.item_orcamento_id',
                        'iol.lote_id',
                        'iol.quantidade_reservada',
                        'iol.quantidade_atendida',
                        'l.numero_lote',
                        'l.validade_lote',
                    ])
                    ->orderBy('iol.id')
                    ->get()
                    ->groupBy(
                        fn ($registro) =>
                            (int) $registro
                                ->item_orcamento_id
                    );

        $dadosMateriais = $itensDisponiveis
            ->map(function ($romaneioItem) use (
                $ocorrenciasPorItem,
                $lotesOrigemPorItemOrcamento
            ) {
                $entregaItem =
                    $romaneioItem->entregaItem;

                $vendaItem =
                    $entregaItem?->vendaItem;

                $produto =
                    $vendaItem?->produto
                    ?? $entregaItem
                        ?->itemOrcamento
                        ?->produto;

                /*
                * Primeira fonte: lote gravado no item da venda.
                */
                $loteVenda =
                    $vendaItem?->lote;

                /*
                * Segunda fonte: lote preservado na reserva
                * do item do orçamento.
                */
                $lotesOrcamento = collect(
                    $lotesOrigemPorItemOrcamento
                        ->get(
                            (int) (
                                $entregaItem
                                    ?->item_orcamento_id
                                ?? 0
                            ),
                            collect()
                        )
                )
                    ->unique('lote_id')
                    ->values();

                /*
                * O fallback só é permitido quando existe
                * exatamente um lote de origem.
                *
                * Se houver vários lotes, nenhum deles será
                * escolhido arbitrariamente.
                */
                $loteOrcamento =
                    ! $loteVenda
                    && $lotesOrcamento->count() === 1
                        ? $lotesOrcamento->first()
                        : null;

                $loteId =
                    $loteVenda?->id
                    ?? $loteOrcamento?->lote_id;

                $numeroLote =
                    $loteVenda?->numero_lote
                    ?? $loteOrcamento?->numero_lote;

                $validadeLote =
                    $loteVenda?->validade_lote
                    ?? $loteOrcamento?->validade_lote;

                $ocorrenciasDoItem = collect(
                    $ocorrenciasPorItem->get(
                        (int) $romaneioItem->id,
                        collect()
                    )
                )->values();

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    3
                );

                $quantidadeAfetada = round(
                    (float) $ocorrenciasDoItem
                        ->sum('quantidade_envolvida'),
                    3
                );

                $quantidadeDisponivel = round(
                    max(
                        0,
                        $quantidadeSaida
                        - $quantidadeAfetada
                    ),
                    3
                );

                $possuiTriagemPendente =
                    $ocorrenciasDoItem->contains(
                        fn ($ocorrencia) =>
                            $ocorrencia->triagem_status
                            !== 'Concluida'
                    );

                $todasTriagensConcluidas =
                    $ocorrenciasDoItem->isNotEmpty()
                    && ! $possuiTriagemPendente;

                $situacaoTriagem = match (true) {
                    $ocorrenciasDoItem->isEmpty() =>
                        'Sem_ocorrencia',

                    $possuiTriagemPendente =>
                        'Pendente',

                    $todasTriagensConcluidas =>
                        'Concluida',

                    default =>
                        'Pendente',
                };

                return [
                    'romaneio_item_id' =>
                        (int) $romaneioItem->id,

                    'entrega_item_id' =>
                        (int) $romaneioItem
                            ->entrega_item_id,

                    'produto_id' =>
                        $produto?->id,

                    'produto' =>
                        $produto?->nome
                        ?? 'Produto não identificado',

                    'codigo_produto' =>
                        $produto?->codigo
                        ?? $produto?->id,

                    'lote_id' =>
                        $loteId
                            ? (int) $loteId
                            : null,

                    'lote' =>
                        $numeroLote,

                    'validade_lote' =>
                        $validadeLote,

                    'validade_produto' =>
                        $produto?->validade_produto,

                    'controla_validade' =>
                        (bool) (
                            $produto?->controla_validade
                            ?? false
                        ),

                    'possui_lote_origem' =>
                        ! empty($loteId),

                    'quantidade_saida' =>
                        $quantidadeSaida,

                    'quantidade_afetada' =>
                        $quantidadeAfetada,

                    'quantidade_disponivel' =>
                        $quantidadeDisponivel,

                    'status_item' =>
                        $romaneioItem->status,

                    'situacao_triagem' =>
                        $situacaoTriagem,

                    'ocorrencias' =>
                        $ocorrenciasDoItem,
                ];
            })
            ->values();

        $ocorrenciasPendentes = $ocorrenciasAtivas
            ->filter(
                fn (RomaneioOcorrencia $ocorrencia) =>
                    $ocorrencia->triagem_status
                    !== 'Concluida'
            )
            ->values();

        $ocorrenciasConcluidas = $ocorrenciasAtivas
            ->filter(
                fn (RomaneioOcorrencia $ocorrencia) =>
                    $ocorrencia->triagem_status
                    === 'Concluida'
            )
            ->values();

        $itensSemOcorrencia = $dadosMateriais
            ->where(
                'situacao_triagem',
                'Sem_ocorrencia'
            )
            ->values();

        $itensComOcorrencia = $dadosMateriais
            ->where(
                'situacao_triagem',
                '!=',
                'Sem_ocorrencia'
            )
            ->values();

        $totaisTriagem = [
            'produtos_transportados' =>
                $dadosMateriais->count(),

            'produtos_com_ocorrencia' =>
                $itensComOcorrencia->count(),

            'produtos_sem_ocorrencia' =>
                $itensSemOcorrencia->count(),

            'ocorrencias_pendentes' =>
                $ocorrenciasPendentes->count(),

            'ocorrencias_concluidas' =>
                $ocorrenciasConcluidas->count(),

            'quantidade_saida' =>
                round(
                    (float) $dadosMateriais
                        ->sum('quantidade_saida'),
                    3
                ),

            'quantidade_afetada' =>
                round(
                    (float) $dadosMateriais
                        ->sum('quantidade_afetada'),
                    3
                ),
        ];

        $tiposResultado = [
            'Devolucao' =>
                'Devolução de material',

            'Recusa' =>
                'Material recusado pelo cliente',

            'Avaria' =>
                'Material avariado',

            'Extravio' =>
                'Material perdido ou extraviado',
        ];

        $opcoesTriagem = [
            'embalagens' =>
                RomaneioOcorrenciaAvaliacao::EMBALAGENS,

            'conteudos' =>
                RomaneioOcorrenciaAvaliacao::CONTEUDOS,

            'integridades' =>
                RomaneioOcorrenciaAvaliacao::INTEGRIDADES,

            'statusValidade' =>
                RomaneioOcorrenciaAvaliacao::STATUS_VALIDADE,

            'reaproveitamentos' =>
                RomaneioOcorrenciaAvaliacao::REAPROVEITAMENTOS,

            'destinos' =>
                RomaneioOcorrenciaAvaliacao::DESTINOS,
        ];

        /*
        * Compatibilidade temporária com componentes anteriores.
        */
        $ocorrencias =
            $ocorrenciasPendentes;

        $itensAvaliados =
            $ocorrenciasConcluidas;

        $itensDevolvidos =
            $ocorrenciasConcluidas;

        return view(
            'romaneios.ocorrencias.triagem',
            compact(
                'romaneio',
                'itensDisponiveis',
                'dadosMateriais',
                'ocorrenciasAtivas',
                'ocorrenciasPendentes',
                'ocorrenciasConcluidas',
                'ocorrenciasPorItem',
                'itensSemOcorrencia',
                'itensComOcorrencia',
                'totaisTriagem',
                'tiposResultado',
                'opcoesTriagem',
                'ocorrencias',
                'itensAvaliados',
                'itensDevolvidos'
            )
        );
    }
    
    public function salvarTriagem(
        Request $request,
        Romaneio $romaneio
        ): RedirectResponse {
            $dados = $request->validate([
                'romaneio_item_id' => [
                    'required',
                    'integer',
                    'exists:romaneio_itens,id',
            ],

            'tipo_resultado' => [
                'required',
                Rule::in([
                    'Devolucao',
                    'Recusa',
                    'Avaria',
                    'Extravio',
                ]),
            ],

            'quantidade_afetada' => [
                'required',
                'numeric',
                'decimal:0,3',
                'min:0.5',
                'multiple_of:0.5',
            ],

            'observacao_ocorrencia' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'avaliacoes' => [
                'nullable',
                'array',
                'max:50',
            ],

            'avaliacoes.*.quantidade' => [
                'required_with:avaliacoes',
                'numeric',
                'decimal:0,3',
                'min:0.5',
                'multiple_of:0.5',
            ],

            'avaliacoes.*.embalagem' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::EMBALAGENS
                ),
            ],

            'avaliacoes.*.conteudo' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::CONTEUDOS
                ),
            ],

            'avaliacoes.*.integridade' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::INTEGRIDADES
                ),
            ],

            'avaliacoes.*.validade_status' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::STATUS_VALIDADE
                ),
            ],

            'avaliacoes.*.reaproveitamento' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::REAPROVEITAMENTOS
                ),
            ],

            'avaliacoes.*.destino_sugerido' => [
                'required_with:avaliacoes',
                Rule::in(
                    RomaneioOcorrenciaAvaliacao::DESTINOS
                ),
            ],

            'avaliacoes.*.observacao' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'justificativa_triagem' => [
                'required',
                'string',
                'min:5',
                'max:3000',
            ],

            'evidencias' => [
                'nullable',
                'array',
                'max:' . self::LIMITE_EVIDENCIAS,
            ],

            'evidencias.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ]);

        $usuarioId = Auth::id();

        if (! $usuarioId) {
            throw ValidationException::withMessages([
                'usuario' =>
                    'Não foi possível identificar o responsável pela triagem.',
            ]);
        }

        $tipoResultado = (string) $dados['tipo_resultado'];

        $exigeAvaliacaoFisica = in_array(
            $tipoResultado,
            [
                'Devolucao',
                'Recusa',
                'Avaria',
            ],
            true
        );

        $avaliacoesRecebidas = collect(
            $dados['avaliacoes']
            ?? []
        );

        if (
            $exigeAvaliacaoFisica
            && $avaliacoesRecebidas->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'avaliacoes' =>
                    'Registre ao menos uma avaliação para o material que retornou fisicamente.',
            ]);
        }

        if (
            ! $exigeAvaliacaoFisica
            && $avaliacoesRecebidas->isNotEmpty()
        ) {
            throw ValidationException::withMessages([
                'avaliacoes' =>
                    'Materiais extraviados não podem receber avaliação física.',
            ]);
        }

        $quantidadeAfetada = $this->paraMilesimos(
            $dados['quantidade_afetada']
        );

        if ($quantidadeAfetada <= 0) {
            throw ValidationException::withMessages([
                'quantidade_afetada' =>
                    'A quantidade afetada deve ser maior que zero.',
            ]);
        }

        if ($exigeAvaliacaoFisica) {
            $quantidadeAvaliada = $avaliacoesRecebidas
                ->sum(
                    fn (array $avaliacao) =>
                        $this->paraMilesimos(
                            $avaliacao['quantidade']
                        )
                );

            if ($quantidadeAvaliada !== $quantidadeAfetada) {
                throw ValidationException::withMessages([
                    'avaliacoes' =>
                        'A soma das avaliações deve ser exatamente igual à quantidade afetada: '
                        . number_format(
                            (float) $dados['quantidade_afetada'],
                            3,
                            ',',
                            '.'
                        )
                        . '.',
                ]);
            }
        }

        $arquivosCriados = [];

        try {
            DB::transaction(function () use (
                $dados,
                $request,
                $romaneio,
                $usuarioId,
                $tipoResultado,
                $exigeAvaliacaoFisica,
                $avaliacoesRecebidas,
                &$arquivosCriados
            ) {
                $romaneioBloqueado = Romaneio::query()
                    ->whereKey($romaneio->id)
                    ->lockForUpdate()
                    ->first();

                if (! $romaneioBloqueado) {
                    throw ValidationException::withMessages([
                        'romaneio' =>
                            'O romaneio informado não foi encontrado.',
                    ]);
                }

                if (! in_array(
                    $romaneioBloqueado->status,
                    [
                        'Em_rota',
                        'Retornando',
                    ],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'romaneio' =>
                            'O romaneio não está disponível para registrar a triagem do retorno.',
                    ]);
                }

                $romaneioItem =
                    \App\Models\RomaneioItem::query()
                        ->with([
                            'entregaItem.vendaItem.produto',
                            'entregaItem.vendaItem.lote',
                            'entregaItem.itemOrcamento.produto',
                        ])
                        ->whereKey(
                            (int) $dados['romaneio_item_id']
                        )
                        ->where(
                            'romaneio_id',
                            $romaneioBloqueado->id
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $romaneioItem) {
                    throw ValidationException::withMessages([
                        'romaneio_item_id' =>
                            'O produto selecionado não pertence a este romaneio.',
                    ]);
                }

                $entregaItem =
                    $romaneioItem->entregaItem;

                $vendaItem =
                    $entregaItem?->vendaItem;

                $produto = $vendaItem?->produto
                    ?? $entregaItem
                        ?->itemOrcamento
                        ?->produto;

                $lote =
                    $this->resolverLoteOrigem(
                        $entregaItem
                    );

                if (! $produto) {
                    throw ValidationException::withMessages([
                        'romaneio_item_id' =>
                            'Não foi possível identificar o produto selecionado.',
                    ]);
                }

                if (
                    $exigeAvaliacaoFisica
                    && ! $lote
                ) {
                    throw ValidationException::withMessages([
                        'romaneio_item_id' =>
                            'O lote utilizado na saída deste produto não foi identificado. A triagem foi bloqueada para proteger a movimentação do estoque.',
                    ]);
                }

                $quantidadeSaida = $this->paraMilesimos(
                    $romaneioItem
                        ->quantidade_conferida_saida
                );

                $quantidadeInformada = $this->paraMilesimos(
                    $dados['quantidade_afetada']
                );

                if ($quantidadeInformada > $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        'quantidade_afetada' =>
                            'A quantidade afetada não pode ultrapassar a quantidade conferida na saída.',
                    ]);
                }

                $ocorrencia =
                    $this->ocorrenciaService
                        ->prepararOcorrenciaDaTriagem(
                            romaneio:
                                $romaneioBloqueado,

                            romaneioItem:
                                $romaneioItem,

                            classificacao:
                                $tipoResultado,

                            quantidade:
                                (float) $dados[
                                    'quantidade_afetada'
                                ],

                            observacao:
                                $dados[
                                    'observacao_ocorrencia'
                                ] ?? null
                        );

                $ocorrencia->load([
                    'avaliacoes',
                    'anexos',
                    'entregaItem.vendaItem.produto',
                    'entregaItem.vendaItem.lote',
                    'entregaItem.itemOrcamento.produto',
                    'romaneioItem.entregaItem.vendaItem.produto',
                    'romaneioItem.entregaItem.vendaItem.lote',
                    'romaneioItem.entregaItem.itemOrcamento.produto',
                ]);

                $this->validarLimiteEvidencias(
                    $ocorrencia,
                    count(
                        $request->file(
                            'evidencias',
                            []
                        )
                    )
                );

                if (
                    $ocorrencia->triagem_status
                    === 'Concluida'
                ) {
                    throw ValidationException::withMessages([
                        'romaneio_item_id' =>
                            'A triagem deste problema já foi concluída.',
                    ]);
                }

                if (
                    in_array(
                        $ocorrencia->status,
                        [
                            'Resolvida',
                            'Cancelada',
                        ],
                        true
                    )
                    || ! empty($ocorrencia->decidida_por)
                ) {
                    throw ValidationException::withMessages([
                        'romaneio_item_id' =>
                            'A ocorrência já foi encaminhada para decisão e não pode ser alterada.',
                    ]);
                }

                $avaliacoesPreparadas = [];

                if ($exigeAvaliacaoFisica) {
                    foreach (
                        $avaliacoesRecebidas->values()
                        as $indice => $avaliacao
                    ) {
                        $dadosValidade =
                            $this->resolverValidade(
                                $ocorrencia,
                                $avaliacao[
                                    'validade_status'
                                ],
                                $lote
                            );

                        $dadosValidade['lote_id'] =
                            $lote->id;

                        $this->validarDestinoReintegracao(
                            $avaliacao,
                            $dadosValidade,
                            $indice
                        );

                        $avaliacoesPreparadas[] =
                            array_merge(
                                [
                                    'ordem' =>
                                        $indice + 1,

                                    'quantidade' =>
                                        $avaliacao[
                                            'quantidade'
                                        ],

                                    'embalagem' =>
                                        $avaliacao[
                                            'embalagem'
                                        ],

                                    'conteudo' =>
                                        $avaliacao[
                                            'conteudo'
                                        ],

                                    'integridade' =>
                                        $avaliacao[
                                            'integridade'
                                        ],

                                    'reaproveitamento' =>
                                        $avaliacao[
                                            'reaproveitamento'
                                        ],

                                    'destino_sugerido' =>
                                        $avaliacao[
                                            'destino_sugerido'
                                        ],

                                    'observacao' =>
                                        $avaliacao[
                                            'observacao'
                                        ] ?? null,

                                    'avaliado_por' =>
                                        $usuarioId,

                                    'avaliado_em' =>
                                        now(),
                                ],
                                $dadosValidade
                            );
                    }
                }

                $statusAnterior =
                    $ocorrencia->status;

                $ocorrencia->avaliacoes()->delete();

                foreach (
                    $avaliacoesPreparadas
                    as $avaliacaoPreparada
                ) {
                    $ocorrencia
                        ->avaliacoes()
                        ->create(
                            $avaliacaoPreparada
                        );
                }

                if ($exigeAvaliacaoFisica) {
                    $condicaoResumo =
                        $this->definirCondicaoResumo(
                            $avaliacoesPreparadas
                        );

                    $destinoResumo =
                        $this->definirDestinoResumo(
                            $avaliacoesPreparadas
                        );
                } else {
                    $condicaoResumo =
                        'Material_nao_retornado';

                    $destinoResumo =
                        'Sem_movimentacao';
                }

                $ocorrencia->update([
                    'triagem_status' =>
                        'Concluida',

                    'condicao_triagem' =>
                        $condicaoResumo,

                    'destino_sugerido' =>
                        $destinoResumo,

                    'descricao' =>
                        $exigeAvaliacaoFisica
                            ? $this->montarDescricaoDefeitos(
                                $avaliacoesPreparadas,
                                $dados[
                                    'justificativa_triagem'
                                ]
                            )
                            : $dados[
                                'justificativa_triagem'
                            ],

                    'justificativa_triagem' =>
                        $dados[
                            'justificativa_triagem'
                        ],

                    'grupo_triagem_uuid' =>
                        null,

                    'triado_por' =>
                        $usuarioId,

                    'triado_em' =>
                        now(),
                ]);

                $descricaoHistorico =
                    $exigeAvaliacaoFisica
                        ? $this->montarDescricaoHistorico(
                            $avaliacoesPreparadas,
                            $dados[
                                'justificativa_triagem'
                            ]
                        )
                        : (
                            'Possível extravio registrado. '
                            . $dados[
                                'justificativa_triagem'
                            ]
                        );

                DB::table(
                    'romaneio_ocorrencia_historicos'
                )->insert([
                    'romaneio_ocorrencia_id' =>
                        $ocorrencia->id,

                    'status_anterior' =>
                        $statusAnterior,

                    'status_novo' =>
                        $statusAnterior,

                    'evento' =>
                        $exigeAvaliacaoFisica
                            ? 'Triagem individual concluída'
                            : 'Extravio registrado na triagem',

                    'descricao' =>
                        $descricaoHistorico,

                    'registrado_por' =>
                        $usuarioId,

                    'registrado_em' =>
                        now(),

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

                foreach (
                    $request->file(
                        'evidencias',
                        []
                    )
                    as $arquivo
                ) {
                    $diretorioRelativo =
                        'uploads/romaneios/'
                        . $romaneioBloqueado->id
                        . '/ocorrencias/'
                        . $ocorrencia->id;

                    $diretorioAbsoluto =
                        public_path(
                            $diretorioRelativo
                        );

                    File::ensureDirectoryExists(
                        $diretorioAbsoluto
                    );

                    $extensao = strtolower(
                        $arquivo
                            ->getClientOriginalExtension()
                    );

                    $nomeArquivo =
                        (string) Str::uuid()
                        . (
                            $extensao !== ''
                                ? '.' . $extensao
                                : ''
                        );

                    $arquivo->move(
                        $diretorioAbsoluto,
                        $nomeArquivo
                    );

                    $caminho =
                        $diretorioRelativo
                        . '/'
                        . $nomeArquivo;

                    $caminhoAbsoluto =
                        public_path($caminho);

                    $arquivosCriados[] =
                        $caminhoAbsoluto;

                    $mimeType =
                        mime_content_type(
                            $caminhoAbsoluto
                        );

                    $this->ocorrenciaService
                        ->registrarAnexo(
                            $ocorrencia,
                            [
                                'tipo' =>
                                    str_starts_with(
                                        (string) $mimeType,
                                        'image/'
                                    )
                                        ? 'Foto'
                                        : 'Documento',

                                'descricao' =>
                                    'Evidência adicionada durante a triagem.',

                                'nome_original' =>
                                    $arquivo
                                        ->getClientOriginalName(),

                                'caminho' =>
                                    $caminho,

                                'mime_type' =>
                                    $mimeType,

                                'tamanho_bytes' =>
                                    filesize(
                                        $caminhoAbsoluto
                                    ),

                                'capturado_em' =>
                                    now(),

                                'hash_arquivo' =>
                                    hash_file(
                                        'sha256',
                                        $caminhoAbsoluto
                                    ),
                            ]
                        );
                }
            });
        } catch (\Throwable $exception) {
            foreach (
                $arquivosCriados
                as $arquivoCriado
            ) {
                if (File::exists($arquivoCriado)) {
                    File::delete($arquivoCriado);
                }
            }

            throw $exception;
        }

        return redirect()
            ->route(
                'romaneios.ocorrencias.triagem',
                $romaneio
            )
            ->with(
                'success',
                'Avaliação salva. O material foi classificado e permanece registrado para a consolidação geral do retorno.'
            );
    }

    public function finalizarTriagem(Request $request, Romaneio $romaneio): RedirectResponse 
    {
            $this->validarUsuarioAutenticado();

        try {
            $this->romaneioService
                ->atualizarOperacao(
                    $romaneio,
                    'finalizar_triagem_retorno'
                );

            /*
            * A consolidação geral da triagem conclui o fluxo
            * operacional do retorno. O middleware liberará o
            * bloqueio da entrega dentro da mesma transação.
            */
            $request->attributes->set(
                'liberarBloqueioEdicaoAoConcluir',
                true
            );

            return redirect()
                ->route(
                    'romaneios.ocorrencias.index',
                    $romaneio
                )
                ->with(
                    'success',
                    'Triagem concluída. O saldo normal foi confirmado como entregue e o romaneio permanece aguardando a tratativa das ocorrências.'
                );
        } catch (
            ValidationException $exception
        ) {
            return redirect()
                ->back()
                ->withErrors(
                    $exception->errors()
                );
        } catch (
            \Throwable $exception
        ) {
            report(
                $exception
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Não foi possível consolidar o retorno. Verifique os dados da triagem e tente novamente.'
                );
        }
    }

    public function atribuirResponsavel(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);

        $dados = $request->validate([
            'responsavel_analise_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $this->ocorrenciaService->atribuirResponsavel(
            $ocorrencia,
            (int) $dados['responsavel_analise_id']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Responsável pela análise definido com sucesso.'
            );
    }

    public function anexarEvidencia(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);

        $dados = $request->validate([
            'tipo' => [
                'required',
                Rule::in([
                    'Foto',
                    'Documento',
                    'Comprovante',
                    'Assinatura',
                    'Outro',
                ]),
            ],
            'descricao' => [
                'nullable',
                'string',
                'max:500',
            ],
            'arquivo' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $mimeType = $arquivo->getMimeType();
        $tamanhoBytes = $arquivo->getSize();
        $diretorioRelativo = 'uploads/romaneios/'
            . $romaneio->id
            . '/ocorrencias/'
            . $ocorrencia->id;

        $diretorioAbsoluto = public_path(
            $diretorioRelativo
        );

        File::ensureDirectoryExists(
            $diretorioAbsoluto
        );

        $extensao = strtolower(
            $arquivo->getClientOriginalExtension()
        );

        $nomeArquivo = (string) Str::uuid()
            . ($extensao !== '' ? '.' . $extensao : '');

        $arquivo->move(
            $diretorioAbsoluto,
            $nomeArquivo
        );

        $caminho = $diretorioRelativo
            . '/'
            . $nomeArquivo;

        $caminhoAbsoluto = public_path(
            $caminho
        );

        try {
            DB::transaction(function () use (
                $ocorrencia,
                $dados,
                $nomeOriginal,
                $caminho,
                $mimeType,
                $tamanhoBytes,
                $caminhoAbsoluto
            ) {
                $ocorrenciaBloqueada =
                    RomaneioOcorrencia::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ocorrencia->id
                        );

                $this->validarLimiteEvidencias(
                    $ocorrenciaBloqueada,
                    1
                );

                $this->ocorrenciaService
                    ->registrarAnexo(
                        $ocorrenciaBloqueada,
                        [
                            'tipo' =>
                                $dados['tipo'],

                            'descricao' =>
                                $dados['descricao']
                                ?? null,

                            'nome_original' =>
                                $nomeOriginal,

                            'caminho' =>
                                $caminho,

                            'mime_type' =>
                                $mimeType,

                            'tamanho_bytes' =>
                                $tamanhoBytes,

                            'capturado_em' =>
                                now(),

                            'hash_arquivo' =>
                                is_file(
                                    $caminhoAbsoluto
                                )
                                    ? hash_file(
                                        'sha256',
                                        $caminhoAbsoluto
                                    )
                                    : null,
                        ]
                    );
            });
        } catch (\Throwable $exception) {
            if (File::exists($caminhoAbsoluto)) {
                File::delete($caminhoAbsoluto);
            }

            throw $exception;
        }

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Evidência anexada com sucesso.'
            );
    }

    public function autorizar(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);
        $this->validarResponsavelEEvidencia($ocorrencia);

        $dados = $request->validate([
            'justificativa_autorizacao' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        $this->ocorrenciaService->autorizar(
            $ocorrencia,
            $dados['justificativa_autorizacao']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência autorizada com sucesso.'
            );
    }

    public function removerEvidencia(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia, RomaneioOcorrenciaAnexo $anexo): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        abort_unless(
            (int) $anexo->romaneio_ocorrencia_id
            === (int) $ocorrencia->id,
            404
        );

        $this->ocorrenciaService->removerAnexo(
            $ocorrencia,
            $anexo
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Evidência removida com sucesso.'
            );
    }

    public function registrarDecisao(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);
        $this->validarResponsavelEEvidencia($ocorrencia);
        $this->validarAutorizacaoParaDecisao($ocorrencia);

        $dados = $request->validate([
            'classificacao_final' => [
                'required',
                Rule::in([
                    'Extravio',
                    'Avaria',
                    'Recusa',
                    'Devolucao',
                    'Perda',
                    'Divergencia',
                    'Improcedente',
                    'Outro',
                ]),
            ],
            'decisao' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
            'destino_estoque' => [
                'required',
                Rule::in([
                    'Sem_movimentacao',
                    'Quarentena',
                    'Reintegracao',
                    'Perda',
                    'Reposicao',
                    'Tratamento_individual',
                ]),
            ],
            'orcamento_reposicao_id' => [
                'nullable',
                'integer',
                'exists:orcamentos,id',
            ],
        ]);

        $this->ocorrenciaService->registrarDecisao(
            $ocorrencia,
            $dados
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Decisão administrativa registrada com sucesso.'
            );
    }

    public function liberarFechamento(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);

        $this->ocorrenciaService->liberarFechamentoLogistico(
            $ocorrencia
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Fechamento logístico liberado para a ocorrência.'
            );
    }

    public function resolver(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );
        $this->validarTriagemConcluida($ocorrencia);

        $dados = $request->validate([
            'solucao' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ]);

        $this->ocorrenciaService->resolver(
            $ocorrencia,
            $dados['solucao']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência resolvida com sucesso.'
            );
    }

    public function cancelar(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'justificativa' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ]);

        $this->ocorrenciaService->cancelar(
            $ocorrencia,
            $dados['justificativa']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência cancelada com sucesso.'
            );
    }

    private function validarUsuarioAutenticado(): int
    {
        $usuarioId = (int) (
            Auth::id()
            ?? 0
        );

        if ($usuarioId <= 0) {
            abort(
                403,
                'É necessário estar autenticado para acessar as ocorrências do romaneio.'
            );
        }

        return $usuarioId;
    }

    private function validarLimiteEvidencias(
        RomaneioOcorrencia $ocorrencia,
        int $quantidadeNova
    ): void {
        $quantidadeNova = max(
            0,
            $quantidadeNova
        );

        $quantidadeExistente = (int) $ocorrencia
            ->anexos()
            ->count();

        if (
            $quantidadeExistente
            + $quantidadeNova
            > self::LIMITE_EVIDENCIAS
        ) {
            throw ValidationException::withMessages([
                'evidencias' =>
                    'A ocorrência pode possuir no máximo '
                    . self::LIMITE_EVIDENCIAS
                    . ' evidências. Remova um arquivo existente antes de adicionar outro.',
            ]);
        }
    }

    private function validarDestinoReintegracao(
        array $avaliacao,
        array $dadosValidade,
        int $indice
    ): void {
        if (
            (
                $avaliacao['destino_sugerido']
                ?? null
            ) !== 'Reintegracao'
        ) {
            return;
        }

        $embalagemAprovada = in_array(
            $avaliacao['embalagem']
            ?? null,
            [
                'Intacta',
                'Nao_se_aplica',
            ],
            true
        );

        $conteudoAprovado = in_array(
            $avaliacao['conteudo']
            ?? null,
            [
                'Preservado',
                'Nao_se_aplica',
            ],
            true
        );

        $integridadeAprovada =
            (
                $avaliacao['integridade']
                ?? null
            ) === 'Integro';

        $validadeAprovada = in_array(
            $dadosValidade['validade_status']
            ?? null,
            [
                'Dentro_validade',
                'Proximo_vencimento',
                'Nao_se_aplica',
            ],
            true
        );

        $reaproveitamentoAprovado =
            (
                $avaliacao['reaproveitamento']
                ?? null
            ) === 'Uso_normal';

        if (
            ! $embalagemAprovada
            || ! $conteudoAprovado
            || ! $integridadeAprovada
            || ! $validadeAprovada
            || ! $reaproveitamentoAprovado
        ) {
            throw ValidationException::withMessages([
                "avaliacoes.$indice.destino_sugerido" =>
                    'A reintegração direta exige embalagem, conteúdo, integridade, validade e reaproveitamento aprovados. Encaminhe o material para quarentena quando alguma condição depender de tratamento.',
            ]);
        }
    }

    private function validarPertencimento(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): void
    {
        $this->validarUsuarioAutenticado();

        abort_unless(
            (int) $ocorrencia->romaneio_id
            === (int) $romaneio->id,
            404
        );
    }

    private function validarTriagemConcluida(
        RomaneioOcorrencia $ocorrencia
        ): void {
        if (! $ocorrencia->triagemConcluida()) {
            throw ValidationException::withMessages([
                'triagem' =>
                    'Conclua a triagem visual antes de iniciar as tratativas desta ocorrência.',
            ]);
        }
    }

    private function paraMilesimos(mixed $quantidade): int
    {
        return (int) round((float) $quantidade * 1000);
    }

    private function resolverLoteOrigem(
        mixed $entregaItem
    ): ?Lote {
        $vendaItem =
            $entregaItem?->vendaItem;

        $loteVenda =
            $vendaItem?->lote;

        if ($loteVenda) {
            return $loteVenda;
        }

        $itemOrcamentoId = (int) (
            $entregaItem?->item_orcamento_id
            ?? 0
        );

        if ($itemOrcamentoId <= 0) {
            return null;
        }

        $loteIds = DB::table(
            'item_orcamento_lotes'
        )
            ->where(
                'item_orcamento_id',
                $itemOrcamentoId
            )
            ->pluck('lote_id')
            ->map(
                fn ($loteId) =>
                    (int) $loteId
            )
            ->filter(
                fn (int $loteId) =>
                    $loteId > 0
            )
            ->unique()
            ->values();

        /*
         * Um retorno nunca escolhe outro lote por FIFO.
         * Quando a saída utilizou mais de um lote, o item precisa
         * possuir o vínculo individual de origem antes da triagem.
         */
        if ($loteIds->count() !== 1) {
            return null;
        }

        $produtoId = (int) (
            $vendaItem?->produto_id
            ?? $entregaItem
                ?->itemOrcamento
                ?->produto_id
            ?? 0
        );

        return Lote::query()
            ->whereKey(
                (int) $loteIds->first()
            )
            ->when(
                $produtoId > 0,
                fn ($query) =>
                    $query->where(
                        'produto_id',
                        $produtoId
                    )
            )
            ->first();
    }

    private function resolverValidade(
        RomaneioOcorrencia $ocorrencia,
        string $statusInformado,
        Lote $lote
    ): array {
        $entregaItem = $ocorrencia->entregaItem
            ?? $ocorrencia->romaneioItem?->entregaItem;

        $vendaItem = $entregaItem?->vendaItem;

        $produto = $vendaItem?->produto
            ?? $entregaItem?->itemOrcamento?->produto;

        if (! $produto || ! (bool) $produto->controla_validade) {
            return [
                'lote_id' => $lote?->id,
                'validade_status' => 'Nao_se_aplica',
                'validade_referencia' => null,
                'origem_validade' => 'Nao_aplicavel',
            ];
        }

        if (in_array(
            $statusInformado,
            ['Data_ilegivel', 'Sem_identificacao'],
            true
        )) {
            return [
                'lote_id' => $lote?->id,
                'validade_status' => $statusInformado,
                'validade_referencia' => null,
                'origem_validade' => 'Manual',
            ];
        }

        $validadeReferencia = $lote?->validade_lote
            ?? $produto->validade_produto;

        $origemValidade = $lote?->validade_lote
            ? 'Lote'
            : ($produto->validade_produto ? 'Produto' : 'Manual');

        if (! $validadeReferencia) {
            return [
                'lote_id' => $lote?->id,
                'validade_status' => 'Sem_identificacao',
                'validade_referencia' => null,
                'origem_validade' => 'Manual',
            ];
        }

        $validade = Carbon::parse($validadeReferencia)->startOfDay();
        $hoje = now()->startOfDay();

        if ($validade->lt($hoje)) {
            $statusCalculado = 'Vencido';
        } elseif ($validade->lte($hoje->copy()->addDays(30))) {
            $statusCalculado = 'Proximo_vencimento';
        } else {
            $statusCalculado = 'Dentro_validade';
        }

        return [
            'lote_id' => $lote?->id,
            'validade_status' => $statusCalculado,
            'validade_referencia' => $validade->toDateString(),
            'origem_validade' => $origemValidade,
        ];
    }

    private function validarResponsavelEEvidencia(
        RomaneioOcorrencia $ocorrencia
    ): void {
        if (empty($ocorrencia->responsavel_analise_id)) {
            throw ValidationException::withMessages([
                'responsavel_analise_id' =>
                    'Defina o responsável pela análise antes de autorizar a ocorrência.',
            ]);
        }

        if (! $ocorrencia->anexos()->exists()) {
            throw ValidationException::withMessages([
                'evidencia' =>
                    'Anexe pelo menos uma evidência antes de autorizar a ocorrência.',
            ]);
        }
    }

    private function validarAutorizacaoParaDecisao(
        RomaneioOcorrencia $ocorrencia
    ): void {
        if (
            (bool) $ocorrencia->exige_autorizacao
            && empty($ocorrencia->autorizada_por)
        ) {
            throw ValidationException::withMessages([
                'autorizacao' =>
                    'Autorize a ocorrência antes de registrar a decisão administrativa.',
            ]);
        }
    }

    private function definirCondicaoResumo(array $avaliacoes): string
    {
        $todasSemAvaria = collect($avaliacoes)->every(
            fn (array $avaliacao) =>
                $avaliacao['embalagem'] === 'Intacta'
                && $avaliacao['conteudo'] === 'Preservado'
                && $avaliacao['integridade'] === 'Integro'
                && in_array(
                    $avaliacao['validade_status'],
                    [
                        'Dentro_validade',
                        'Proximo_vencimento',
                        'Nao_se_aplica',
                    ],
                    true
                )
        );

        if ($todasSemAvaria) {
            return 'Sem_avaria_relevante';
        }

        $todasSemCondicao = collect($avaliacoes)->every(
            fn (array $avaliacao) =>
                $avaliacao['integridade'] === 'Perda_total'
                || $avaliacao['reaproveitamento']
                    === 'Sem_reaproveitamento'
        );

        if ($todasSemCondicao) {
            return 'Sem_condicao_de_uso';
        }

        $possuiInconclusiva = collect($avaliacoes)->contains(
            fn (array $avaliacao) =>
                $avaliacao['integridade']
                    === 'Avaliacao_inconclusiva'
        );

        if ($possuiInconclusiva) {
            return 'Avaliacao_inconclusiva';
        }

        $somenteAvariaLeve = collect($avaliacoes)->every(
            fn (array $avaliacao) =>
                in_array(
                    $avaliacao['integridade'],
                    ['Integro', 'Dano_leve'],
                    true
                )
        );

        if ($somenteAvariaLeve) {
            return 'Pequena_avaria';
        }

        return 'Reaproveitavel_com_restricao';
    }

    private function definirDestinoResumo(array $avaliacoes): string
    {
        $destinos = collect($avaliacoes)
            ->pluck('destino_sugerido')
            ->unique()
            ->values();

        if ($destinos->count() === 1) {
            return (string) $destinos->first();
        }

        return 'Quarentena';
    }

    private function montarDescricaoHistorico(
        array $avaliacoes,
        string $justificativa
    ): string {
        $linhas = collect($avaliacoes)
            ->map(function (array $avaliacao) {
                return '#'
                    .$avaliacao['ordem']
                    .' - Quantidade: '
                    .number_format(
                        (float) $avaliacao['quantidade'],
                        3,
                        ',',
                        '.'
                    )
                    .'; embalagem: '
                    .str_replace('_', ' ', $avaliacao['embalagem'])
                    .'; conteúdo: '
                    .str_replace('_', ' ', $avaliacao['conteudo'])
                    .'; integridade: '
                    .str_replace('_', ' ', $avaliacao['integridade'])
                    .'; validade: '
                    .str_replace(
                        '_',
                        ' ',
                        $avaliacao['validade_status']
                    )
                    .'; reaproveitamento: '
                    .str_replace(
                        '_',
                        ' ',
                        $avaliacao['reaproveitamento']
                    )
                    .'; destino: '
                    .str_replace(
                        '_',
                        ' ',
                        $avaliacao['destino_sugerido']
                    )
                    .'.';
            })
            ->implode(' ');

        return $linhas.' Justificativa: '.$justificativa;
    }

    private function montarDescricaoDefeitos(
        array $avaliacoes,
        string $justificativa
    ): string {
        $descricoes = collect($avaliacoes)
            ->map(function (array $avaliacao) {
                $defeitos = collect();

                if (
                    ! empty($avaliacao['embalagem'])
                    && $avaliacao['embalagem'] !== 'Intacta'
                ) {
                    $defeitos->push(
                        'Embalagem: '
                        . str_replace(
                            '_',
                            ' ',
                            $avaliacao['embalagem']
                        )
                    );
                }

                if (
                    ! empty($avaliacao['conteudo'])
                    && $avaliacao['conteudo'] !== 'Preservado'
                ) {
                    $defeitos->push(
                        'Conteúdo: '
                        . str_replace(
                            '_',
                            ' ',
                            $avaliacao['conteudo']
                        )
                    );
                }

                if (
                    ! empty($avaliacao['integridade'])
                    && $avaliacao['integridade'] !== 'Integro'
                ) {
                    $defeitos->push(
                        'Integridade: '
                        . str_replace(
                            '_',
                            ' ',
                            $avaliacao['integridade']
                        )
                    );
                }

                if (
                    ! empty($avaliacao['validade_status'])
                    && ! in_array(
                        $avaliacao['validade_status'],
                        [
                            'Dentro_validade',
                            'Proximo_vencimento',
                            'Nao_se_aplica',
                        ],
                        true
                    )
                ) {
                    $defeitos->push(
                        'Validade: '
                        . str_replace(
                            '_',
                            ' ',
                            $avaliacao['validade_status']
                        )
                    );
                }

                $observacao = trim(
                    (string) (
                        $avaliacao['observacao']
                        ?? ''
                    )
                );

                if ($observacao !== '') {
                    $defeitos->push($observacao);
                }

                return $defeitos
                    ->unique()
                    ->implode('; ');
            })
            ->filter()
            ->values();

        if ($descricoes->isNotEmpty()) {
            return $descricoes->implode(' | ');
        }

        return trim($justificativa);
    }
}