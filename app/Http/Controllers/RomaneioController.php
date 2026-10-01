<?php


namespace App\Http\Controllers;


use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Models\Veiculo;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Empresa;
use Throwable;

use Illuminate\Validation\ValidationException;

class RomaneioController extends Controller
{
    private const STATUS_ROMANEIOS = [
        'Montagem',


        'Aguardando_separacao',


        'Em_separacao',


        'Aguardando_conferencia_separacao',


        'Em_conferencia_separacao',


        'Separacao_conferida',


        'Aguardando_carregamento',


        'Carregando',


        'Aguardando_conferencia_saida',


        'Em_conferencia_saida',


        'Aguardando_liberacao',


        'Liberado',


        'Em_rota',


        'Retornando',


        'Aguardando_conferencia_retorno',


        'Em_conferencia_retorno',


        'Aguardando_prestacao_contas',


        'Em_prestacao_contas',


        'Aguardando_fechamento',


        'Fechado',


        'Cancelado',


    ];


    private const STATUS_ENTREGAS_OPERACIONAIS = [
        'Aguardando_separacao',


        'Em_preparacao',


        'Pronta_para_carregamento',


        'Carregada',


        'Liberada',


        'Em_rota',


        'No_destino',


        'Entregue_parcial',


        'Nao_entregue',


        'Recusada',


        'Reagendada',


        'Devolvida',


    ];


    private const ACOES_OPERACIONAIS = [
        'concluir_montagem',


        'salvar_andamento',


        'iniciar_separacao',


        'finalizar_separacao',


        'iniciar_conferencia_separacao',


        'finalizar_conferencia_separacao',


        'iniciar_carregamento',


        'finalizar_carregamento',


        'iniciar_conferencia_saida',


        'finalizar_conferencia_saida',


        'liberar_veiculo',


        'registrar_saida',


        'registrar_retorno',


        'iniciar_conferencia_retorno',


        'finalizar_conferencia_retorno',


        'iniciar_prestacao_contas',


        'finalizar_prestacao_contas',


        'fechar_romaneio',


        'voltar_etapa',


        'navegar_etapa',


    ];


    public function __construct(protected RomaneioService $romaneioService) 
    {
    }


    public function index(Request $request)
    {
        $statusValidos = self::STATUS_ROMANEIOS;


        $romaneios = Romaneio::query()
            ->with([
                'entrega.cliente',


                'entrega.orcamento.cliente',


                'entrega.venda.cliente',


                'motorista',


                'veiculo',


                'motoristaExecutante',


                'veiculoExecutante',


                'itens',


                'ocorrencias',


            ])
            ->when(
                $request->filled('status')
                && in_array($request->input('status'), $statusValidos, true),


                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();


        return view('romaneios.index', compact('romaneios', 'statusValidos'));


    }

    // public function create(Request $request)
    // {
    //     $entregaId = $request->integer(
    //         'entrega_id'
    //     );

    //     if (
    //         ! $entregaId
    //         && $request->filled('entregas_id')
    //     ) {
    //         $entregaId = (int) $request->input(
    //             'entregas_id'
    //         );
    //     }

    //     $entregasDisponiveis = Entrega::query()
    //         ->with([
    //             'cliente',
    //             'orcamento.cliente',
    //             'venda.cliente',
    //             'itens.vendaItem.produto',
    //             'itens.itemOrcamento.produto',
    //         ])
    //         ->whereIn(
    //             'status',
    //             self::STATUS_ENTREGAS_OPERACIONAIS
    //         )
    //         ->when(
    //             $entregaId,
    //             fn ($query) =>
    //                 $query->where(
    //                     'id',
    //                     $entregaId
    //                 )
    //         )
    //         ->orderBy('data_prevista')
    //         ->orderBy('id')
    //         ->get();

    //     if (
    //         $entregaId
    //         && $entregasDisponiveis->isEmpty()
    //     ) {
    //         return redirect()
    //             ->route('entregas.index')
    //             ->with(
    //                 'error',
    //                 'A entrega selecionada não está disponível para operação de romaneio.'
    //             );
    //     }

    //     $romaneiosAtivos = collect();
    //     $romaneioAtivo = null;

    //     if ($entregaId) {
    //         $romaneiosAtivos = Romaneio::query()
    //             ->with(
    //                 $this->relacionamentosOperacionais()
    //             )
    //             ->where(
    //                 'entrega_id',
    //                 $entregaId
    //             )
    //             ->whereNotIn('status', [
    //                 'Fechado',
    //                 'Cancelado',
    //             ])
    //             ->orderByDesc('id')
    //             ->get();

    //         $romaneioAtivo =
    //             $romaneiosAtivos->first();
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Romaneios vinculados à saída do caminhão
    //     |--------------------------------------------------------------------------
    //     |
    //     | Quando o romaneio atual já possui um veículo, carregamos todos os
    //     | romaneios operacionais vinculados ao mesmo caminhão.
    //     |
    //     | Romaneios já em rota, em retorno, fechados ou cancelados não fazem
    //     | parte da próxima saída física.
    //     |
    //     */

    //     $romaneiosVinculadosSaida = collect();

    //     if (
    //         $romaneioAtivo
    //         && ! empty($romaneioAtivo->veiculo_id)
    //     ) {
    //         $romaneiosVinculadosSaida = Romaneio::query()
    //             ->with(
    //                 $this->relacionamentosOperacionais()
    //             )
    //             ->where(
    //                 'veiculo_id',
    //                 $romaneioAtivo->veiculo_id
    //             )
    //             ->whereHas(
    //                 'entrega',
    //                 fn ($query) =>
    //                     $query->whereNotIn('status', [
    //                         'Entregue',
    //                         'Cancelada',
    //                         'Cancelado',
    //                     ])
    //             )
    //             ->whereNotIn('status', [
    //                 'Fechado',
    //                 'Cancelado',
    //                 'Em_rota',
    //                 'Retornando',
    //                 'Aguardando_conferencia_retorno',
    //                 'Em_conferencia_retorno',
    //                 'Aguardando_prestacao_contas',
    //                 'Em_prestacao_contas',
    //                 'Aguardando_fechamento',
    //             ])
    //             ->orderBy('ordem_execucao')
    //             ->orderBy('id')
    //             ->get();
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Classificação da saída
    //     |--------------------------------------------------------------------------
    //     */

    //     $romaneiosLiberadosSaida =
    //         $romaneiosVinculadosSaida
    //             ->filter(
    //                 fn ($item) =>
    //                     $item->status === 'Liberado'
    //             )
    //             ->values();

    //     $romaneiosPendentesSaida =
    //         $romaneiosVinculadosSaida
    //             ->reject(
    //                 fn ($item) =>
    //                     $item->status === 'Liberado'
    //             )
    //             ->values();

    //     /*
    //     * Detecta divergência de motorista entre os romaneios
    //     * vinculados ao mesmo caminhão.
    //     */
    //     $romaneiosMotoristaDivergente =
    //         collect();

    //     if (
    //         $romaneioAtivo
    //         && ! empty($romaneioAtivo->motorista_id)
    //     ) {
    //         $romaneiosMotoristaDivergente =
    //             $romaneiosLiberadosSaida
    //                 ->filter(
    //                     fn ($item) =>
    //                         (int) $item->motorista_id
    //                         !== (int) $romaneioAtivo
    //                             ->motorista_id
    //                 )
    //                 ->values();
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Funcionários operacionais
    //     |--------------------------------------------------------------------------
    //     */

    //     $funcionariosOperacionais =
    //         Funcionario::query()
    //             ->where(function ($query) {
    //                 $query
    //                     ->where('ativo', 1)
    //                     ->orWhereNull('ativo');
    //             })
    //             ->orderBy('nome')
    //             ->get();

    //     $motoristas =
    //         $funcionariosOperacionais
    //             ->filter(function ($funcionario) {
    //                 return strtolower(
    //                     trim(
    //                         (string) $funcionario->funcao
    //                     )
    //                 ) === 'motorista';
    //             })
    //             ->values();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Veículos
    //     |--------------------------------------------------------------------------
    //     */

    //     $veiculos = Veiculo::query()
    //         ->where(function ($query) {
    //             $query
    //                 ->where('ativo', 1)
    //                 ->orWhereNull('ativo');
    //         })
    //         ->orderBy('placa')
    //         ->get();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Retorno da view
    //     |--------------------------------------------------------------------------
    //     */

    //     return view(
    //         'romaneios.create',
    //         compact(
    //             'entregasDisponiveis',
    //             'funcionariosOperacionais',
    //             'motoristas',
    //             'veiculos',
    //             'entregaId',
    //             'romaneioAtivo',
    //             'romaneiosAtivos',
    //             'romaneiosVinculadosSaida',
    //             'romaneiosLiberadosSaida',
    //             'romaneiosPendentesSaida',
    //             'romaneiosMotoristaDivergente'
    //         )
    //     );
    // }
    public function create(Request $request)
    {
        $entregaId = $request->integer(
            'entrega_id'
        );

        if (
            ! $entregaId
            && $request->filled('entregas_id')
        ) {
            $entregaId = (int) $request->input(
                'entregas_id'
            );
        }

        $entregasDisponiveis = Entrega::query()
            ->with([
                'cliente',
                'orcamento.cliente',
                'venda.cliente',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->whereIn(
                'status',
                self::STATUS_ENTREGAS_OPERACIONAIS
            )
            ->when(
                $entregaId,
                fn ($query) =>
                    $query->where(
                        'id',
                        $entregaId
                    )
            )
            ->orderBy('data_prevista')
            ->orderBy('id')
            ->get();

        if (
            $entregaId
            && $entregasDisponiveis->isEmpty()
        ) {
            return redirect()
                ->route('entregas.index')
                ->with(
                    'error',
                    'A entrega selecionada não está disponível para operação de romaneio.'
                );
        }

        $romaneiosAtivos = collect();
        $romaneioAtivo = null;

        if ($entregaId) {
            $romaneiosAtivos = Romaneio::query()
                ->with(
                    $this->relacionamentosOperacionais()
                )
                ->where(
                    'entrega_id',
                    $entregaId
                )
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->orderByDesc('id')
                ->get();

            $romaneioAtivo =
                $romaneiosAtivos->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Estados pertencentes à preparação da saída
        |--------------------------------------------------------------------------
        |
        | Somente romaneios que ainda estão sendo preparados para a próxima
        | viagem podem participar do agrupamento do caminhão.
        |
        | Estados de rota, retorno, devolução, ocorrência, prestação de contas
        | e fechamento pertencem a viagens anteriores e não podem bloquear uma
        | nova saída física do veículo.
        |
        */

        $statusRomaneiosPreparacaoSaida = [
            'Montagem',
            'Aguardando_separacao',
            'Em_separacao',
            'Aguardando_conferencia_separacao',
            'Em_conferencia_separacao',
            'Separacao_conferida',
            'Aguardando_carregamento',
            'Carregando',
            'Aguardando_conferencia_saida',
            'Em_conferencia_saida',
            'Aguardando_liberacao',
            'Liberado',
        ];

        $statusEntregasPreparacaoSaida = [
            'Aguardando_separacao',
            'Em_preparacao',
            'Pronta_para_carregamento',
            'Carregada',
            'Liberada',
        ];

        /*
        |--------------------------------------------------------------------------
        | Romaneios vinculados à próxima saída do caminhão
        |--------------------------------------------------------------------------
        |
        | Quando o romaneio atual possui um veículo, carregamos somente os
        | romaneios pré-viagem vinculados ao mesmo caminhão.
        |
        | Romaneios em rota ou em qualquer etapa posterior não pertencem à
        | próxima saída, mesmo que ainda possuam devoluções ou ocorrências.
        |
        */

        $romaneiosVinculadosSaida = collect();

        if (
            $romaneioAtivo
            && ! empty($romaneioAtivo->veiculo_id)
        ) {
            $romaneiosVinculadosSaida = Romaneio::query()
                ->with(
                    $this->relacionamentosOperacionais()
                )
                ->where(
                    'veiculo_id',
                    $romaneioAtivo->veiculo_id
                )
                ->whereIn(
                    'status',
                    $statusRomaneiosPreparacaoSaida
                )
                ->whereHas(
                    'entrega',
                    fn ($query) =>
                        $query->whereIn(
                            'status',
                            $statusEntregasPreparacaoSaida
                        )
                )
                ->orderBy('ordem_execucao')
                ->orderBy('id')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Classificação da saída
        |--------------------------------------------------------------------------
        */

        $romaneiosLiberadosSaida =
            $romaneiosVinculadosSaida
                ->filter(
                    fn ($item) =>
                        $item->status === 'Liberado'
                )
                ->values();

        $romaneiosPendentesSaida =
            $romaneiosVinculadosSaida
                ->reject(
                    fn ($item) =>
                        $item->status === 'Liberado'
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Divergência de motorista
        |--------------------------------------------------------------------------
        |
        | Detecta divergência de motorista entre os romaneios liberados
        | vinculados à mesma próxima saída do caminhão.
        |
        */

        $romaneiosMotoristaDivergente = collect();

        if (
            $romaneioAtivo
            && ! empty($romaneioAtivo->motorista_id)
        ) {
            $romaneiosMotoristaDivergente =
                $romaneiosLiberadosSaida
                    ->filter(
                        fn ($item) =>
                            (int) $item->motorista_id
                            !== (int) $romaneioAtivo
                                ->motorista_id
                    )
                    ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Funcionários operacionais
        |--------------------------------------------------------------------------
        */

        $funcionariosOperacionais =
            Funcionario::query()
                ->where(function ($query) {
                    $query
                        ->where('ativo', 1)
                        ->orWhereNull('ativo');
                })
                ->orderBy('nome')
                ->get();

        $motoristas =
            $funcionariosOperacionais
                ->filter(function ($funcionario) {
                    return strtolower(
                        trim(
                            (string) $funcionario->funcao
                        )
                    ) === 'motorista';
                })
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Veículos
        |--------------------------------------------------------------------------
        */

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('placa')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Retorno da view
        |--------------------------------------------------------------------------
        */

        return view(
            'romaneios.create',
            compact(
                'entregasDisponiveis',
                'funcionariosOperacionais',
                'motoristas',
                'veiculos',
                'entregaId',
                'romaneioAtivo',
                'romaneiosAtivos',
                'romaneiosVinculadosSaida',
                'romaneiosLiberadosSaida',
                'romaneiosPendentesSaida',
                'romaneiosMotoristaDivergente'
            )
        );
    }

    public function store(Request $request)
    {
        $dadosValidados = $request->validate(
            $this->regrasCriacao(),


            $this->mensagensCriacao()
        );


        $possuiEntrega = ! empty($dadosValidados['entrega_id'] ?? null)
            || ! empty($dadosValidados['entregas'] ?? []);

        $possuiItens = ! empty($dadosValidados['entrega_itens'] ?? [])
            || ! empty($dadosValidados['itens'] ?? []);

        if (! $possuiEntrega && ! $possuiItens) {
            return back()
                ->withInput()
                ->with(
                    'error',

                    'Selecione pelo menos uma entrega ou item para criar o romaneio.'
                );

        }

        try {
            $romaneio = $this->romaneioService->criarRomaneio($dadosValidados);

            return redirect()
                ->route('romaneios.create', ['entrega_id' => $romaneio->entrega_id])
                ->with(
                    'success',

                    'Romaneio criado com sucesso e aguardando o início da separação.'
                );

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível criar o romaneio.');

        }

    }

    public function show(Romaneio $romaneio)
    {
        $romaneio->load($this->relacionamentosOperacionais(true));

        return view('romaneios.show', compact('romaneio'));

    }

        
    public function atualizarOperacao( Request $request, Romaneio $romaneio)
    {
        $romaneio->loadMissing('entrega');

        $exigirDataEntregaComplementar =
            $request->input('acao') === 'concluir_montagem'
            && (int) (
                $romaneio->entrega?->entrega_origem_id
                ?? 0
            ) > 0;

        $acoesPermitidas = [
            'concluir_montagem',
            'salvar_andamento',
            'iniciar_separacao',
            'finalizar_separacao',
            'iniciar_conferencia_separacao',
            'finalizar_conferencia_separacao',
            'iniciar_carregamento',
            'finalizar_carregamento',
            'iniciar_conferencia_saida',
            'finalizar_conferencia_saida',
            'liberar_veiculo',
            'registrar_saida',
            'registrar_retorno',
            'iniciar_conferencia_retorno',
            'finalizar_conferencia_retorno',
            'iniciar_prestacao_contas',
            'finalizar_prestacao_contas',
            'fechar_romaneio',
            'navegar_etapa',
        ];

        $dadosValidados = $request->validate(
            [
                'acao' => [
                    'required',
                    'string',
                    Rule::in($acoesPermitidas),
                ],

                'etapa_destino' => [
                    'nullable',
                    'required_if:acao,navegar_etapa',
                    'string',
                    Rule::in([
                        'montagem',
                        'separacao',
                        'conferencia_separacao',
                        'carregamento',
                        'conferencia_saida',
                        'liberacao',
                    ]),
                ],

                'motivo_movimentacao' => [
                    'nullable',
                    'required_if:acao,navegar_etapa',
                    'string',
                    'min:5',
                    'max:1000',
                ],

                'metodo_identificacao' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'Sistema',
                        'codigo_barras',
                        'qr_code',
                        'codigo_operacional',
                        'pesquisa_manual',
                    ]),
                ],

                /*
                * Motorista e veículo permanecem opcionais durante
                * montagem, separação e carregamento.
                *
                * Tornam-se obrigatórios somente na liberação.
                */
                'motorista_id' => [
                    'nullable',
                    'required_if:acao,liberar_veiculo',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'veiculo_id' => [
                    'nullable',
                    'required_if:acao,liberar_veiculo',
                    'integer',
                    'exists:veiculos,id',
                ],

                /*
                * Na saída, o operador precisa confirmar todos os
                * romaneios entregues ao motorista.
                */
                'romaneios_confirmados' => [
                    'nullable',
                    'required_if:acao,registrar_saida',
                    'array',
                    'min:1',
                ],

                'romaneios_confirmados.*' => [
                    'integer',
                    'distinct',
                    'exists:romaneios,id',
                ],

                'separado_por' => [
                    'nullable',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'conferencia_separacao_por' => [
                    'nullable',
                    'required_if:acao,iniciar_conferencia_separacao',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'carregado_por' => [
                    'nullable',
                    'required_if:acao,iniciar_carregamento',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'conferencia_saida_por' => [
                    'nullable',
                    'required_if:acao,iniciar_conferencia_saida',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'retorno_conferido_por' => [
                    'nullable',
                    'required_if:acao,iniciar_conferencia_retorno',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'data_prevista_entrega' => [
                    'nullable',
                    Rule::requiredIf(
                        $exigirDataEntregaComplementar
                    ),
                    'date',
                    'after_or_equal:today',
                ],

                'observacao_retorno' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'metodo_fechamento' => [
                    'nullable',
                    'required_if:acao,fechar_romaneio',
                    'string',
                    Rule::in([
                        'codigo_barras',
                        'qr_code',
                        'codigo_operacional',
                        'pesquisa_manual',
                    ]),
                ],

                'justificativa_fechamento_manual' => [
                    'nullable',
                    'required_if:metodo_fechamento,pesquisa_manual',
                    'string',
                    'min:5',
                    'max:1000',
                ],

                'tipo_saldo' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'Entrega_fracionada',
                        'Promessa_sem_estoque',
                    ]),
                ],

                'proximo_motorista_id' => [
                    'nullable',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'proximo_veiculo_id' => [
                    'nullable',
                    'integer',
                    'exists:veiculos,id',
                ],

                'data_prevista_saldo' => [
                    'nullable',
                    'required_if:tipo_saldo,Promessa_sem_estoque',
                    'date',
                    'after_or_equal:today',
                ],

                'observacao_saldo' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'itens' => [
                    'nullable',
                    'array',
                ],

                'itens.*.entrega_item_id' => [
                    'required_with:itens',
                    'integer',
                    'exists:entrega_itens,id',
                ],

                'itens.*.romaneio_item_id' => [
                    'nullable',
                    'integer',
                    'exists:romaneio_itens,id',
                ],

                'itens.*.quantidade_separada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_conferida_separacao' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_carregada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_conferida_saida' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_entregue' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_devolvida' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_recusada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_avariada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'itens.*.quantidade_perdida' => [
                    'nullable',
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
                'acao.required' =>
                    'A ação operacional não foi informada.',

                'acao.in' =>
                    'A ação operacional informada é inválida.',

                'etapa_destino.required_if' =>
                    'Informe a etapa operacional de destino.',

                'etapa_destino.in' =>
                    'A etapa operacional de destino é inválida.',

                'motivo_movimentacao.required_if' =>
                    'Informe o motivo da alteração de etapa.',

                'motivo_movimentacao.min' =>
                    'O motivo da alteração deve possuir pelo menos 5 caracteres.',

                'motivo_movimentacao.max' =>
                    'O motivo da alteração pode possuir no máximo 1000 caracteres.',

                'metodo_identificacao.in' =>
                    'O método de identificação informado é inválido.',

                'motorista_id.required_if' =>
                    'Selecione o motorista antes de liberar o veículo.',

                'motorista_id.integer' =>
                    'O motorista informado é inválido.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.required_if' =>
                    'Selecione o veículo antes da liberação.',

                'veiculo_id.integer' =>
                    'O veículo informado é inválido.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',

                'romaneios_confirmados.required_if' =>
                    'Confirme os romaneios entregues ao motorista antes de registrar a saída.',

                'romaneios_confirmados.array' =>
                    'A confirmação dos romaneios é inválida.',

                'romaneios_confirmados.min' =>
                    'Confirme pelo menos um romaneio para registrar a saída.',

                'romaneios_confirmados.*.integer' =>
                    'Um dos romaneios confirmados possui identificação inválida.',

                'romaneios_confirmados.*.distinct' =>
                    'Existem romaneios duplicados na confirmação da saída.',

                'romaneios_confirmados.*.exists' =>
                    'Um dos romaneios confirmados não foi encontrado.',

                'separado_por.integer' =>
                    'O funcionário responsável pela separação é inválido.',

                'separado_por.exists' =>
                    'O funcionário responsável pela separação não foi encontrado.',

                'conferencia_separacao_por.required_if' =>
                    'Informe o funcionário responsável pela conferência da separação.',

                'conferencia_separacao_por.integer' =>
                    'O funcionário responsável pela conferência da separação é inválido.',

                'conferencia_separacao_por.exists' =>
                    'O funcionário responsável pela conferência da separação não foi encontrado.',

                'carregado_por.required_if' =>
                    'Informe o funcionário responsável pelo carregamento.',

                'carregado_por.integer' =>
                    'O funcionário responsável pelo carregamento é inválido.',

                'carregado_por.exists' =>
                    'O funcionário responsável pelo carregamento não foi encontrado.',

                'conferencia_saida_por.required_if' =>
                    'Informe o funcionário responsável pela conferência de saída.',

                'conferencia_saida_por.integer' =>
                    'O funcionário responsável pela conferência de saída é inválido.',

                'conferencia_saida_por.exists' =>
                    'O funcionário responsável pela conferência de saída não foi encontrado.',

                'retorno_conferido_por.required_if' =>
                    'Informe o funcionário responsável pela conferência do retorno.',

                'retorno_conferido_por.integer' =>
                    'O funcionário responsável pela conferência do retorno é inválido.',

                'retorno_conferido_por.exists' =>
                    'O funcionário responsável pela conferência do retorno não foi encontrado.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 1000 caracteres.',

                'data_prevista_entrega.required' =>
                    'Informe a data prevista da entrega complementar.',

                'data_prevista_entrega.date' =>
                    'A data prevista da entrega complementar é inválida.',

                'data_prevista_entrega.after_or_equal' =>
                    'A data prevista da entrega complementar não pode ser anterior à data atual.',

                'observacao_retorno.string' =>
                    'A observação do retorno deve ser um texto.',

                'observacao_retorno.max' =>
                    'A observação do retorno pode possuir no máximo 1000 caracteres.',

                'metodo_fechamento.required_if' =>
                    'Informe o método utilizado para localizar e fechar o romaneio.',

                'metodo_fechamento.in' =>
                    'O método de fechamento informado é inválido.',

                'justificativa_fechamento_manual.required_if' =>
                    'Informe a justificativa para o fechamento por pesquisa manual.',

                'justificativa_fechamento_manual.min' =>
                    'A justificativa do fechamento manual deve possuir pelo menos 5 caracteres.',

                'justificativa_fechamento_manual.max' =>
                    'A justificativa do fechamento manual pode possuir no máximo 1000 caracteres.',

                'tipo_saldo.in' =>
                    'O tratamento selecionado para o saldo é inválido.',

                'proximo_motorista_id.integer' =>
                    'O motorista planejado para o próximo romaneio é inválido.',

                'proximo_motorista_id.exists' =>
                    'O motorista planejado para o próximo romaneio não foi encontrado.',

                'proximo_veiculo_id.integer' =>
                    'O veículo planejado para o próximo romaneio é inválido.',

                'proximo_veiculo_id.exists' =>
                    'O veículo planejado para o próximo romaneio não foi encontrado.',

                'data_prevista_saldo.required_if' =>
                    'Informe a data prevista para a promessa de entrega.',

                'data_prevista_saldo.date' =>
                    'A data prevista para o saldo é inválida.',

                'data_prevista_saldo.after_or_equal' =>
                    'A data prevista para o saldo não pode ser anterior à data atual.',

                'observacao_saldo.string' =>
                    'A observação do saldo deve ser um texto.',

                'observacao_saldo.max' =>
                    'A observação do saldo pode possuir no máximo 500 caracteres.',

                'itens.array' =>
                    'Os dados dos itens são inválidos.',

                'itens.*.entrega_item_id.required_with' =>
                    'Não foi possível identificar um dos itens da entrega.',

                'itens.*.entrega_item_id.integer' =>
                    'Um dos itens da entrega possui identificação inválida.',

                'itens.*.entrega_item_id.exists' =>
                    'Um dos itens da entrega não foi encontrado.',

                'itens.*.romaneio_item_id.integer' =>
                    'Um dos itens do romaneio possui identificação inválida.',

                'itens.*.romaneio_item_id.exists' =>
                    'Um dos itens do romaneio não foi encontrado.',

                'itens.*.quantidade_separada.numeric' =>
                    'A quantidade separada deve ser numérica.',

                'itens.*.quantidade_separada.min' =>
                    'A quantidade separada não pode ser negativa.',

                'itens.*.quantidade_conferida_separacao.numeric' =>
                    'A quantidade conferida na separação deve ser numérica.',

                'itens.*.quantidade_conferida_separacao.min' =>
                    'A quantidade conferida na separação não pode ser negativa.',

                'itens.*.quantidade_carregada.numeric' =>
                    'A quantidade carregada deve ser numérica.',

                'itens.*.quantidade_carregada.min' =>
                    'A quantidade carregada não pode ser negativa.',

                'itens.*.quantidade_conferida_saida.numeric' =>
                    'A quantidade conferida na saída deve ser numérica.',

                'itens.*.quantidade_conferida_saida.min' =>
                    'A quantidade conferida na saída não pode ser negativa.',

                'itens.*.quantidade_entregue.numeric' =>
                    'A quantidade entregue deve ser numérica.',

                'itens.*.quantidade_entregue.min' =>
                    'A quantidade entregue não pode ser negativa.',

                'itens.*.quantidade_devolvida.numeric' =>
                    'A quantidade devolvida deve ser numérica.',

                'itens.*.quantidade_devolvida.min' =>
                    'A quantidade devolvida não pode ser negativa.',

                'itens.*.quantidade_recusada.numeric' =>
                    'A quantidade recusada deve ser numérica.',

                'itens.*.quantidade_recusada.min' =>
                    'A quantidade recusada não pode ser negativa.',

                'itens.*.quantidade_avariada.numeric' =>
                    'A quantidade avariada deve ser numérica.',

                'itens.*.quantidade_avariada.min' =>
                    'A quantidade avariada não pode ser negativa.',

                'itens.*.quantidade_perdida.numeric' =>
                    'A quantidade perdida deve ser numérica.',

                'itens.*.quantidade_perdida.min' =>
                    'A quantidade perdida não pode ser negativa.',

                'itens.*.observacao.string' =>
                    'A observação do item deve ser um texto.',

                'itens.*.observacao.max' =>
                    'A observação do item pode possuir no máximo 500 caracteres.',
            ]
        );

        try {
            $romaneioAtualizado =
                $this->romaneioService
                    ->atualizarOperacao(
                        $romaneio,
                        $dadosValidados['acao'],
                        $dadosValidados
                    );

            $mensagem = match (
                $dadosValidados['acao']
            ) {
                'concluir_montagem' =>
                    'Montagem concluída com sucesso.',

                'salvar_andamento' =>
                    'Andamento salvo com sucesso.',

                'iniciar_separacao' =>
                    'Separação iniciada com sucesso.',

                'finalizar_separacao' =>
                    isset($dadosValidados['tipo_saldo'])
                        ? 'Separação finalizada e saldo encaminhado para o próximo romaneio.'
                        : 'Separação finalizada com sucesso.',

                'iniciar_conferencia_separacao' =>
                    'Conferência da separação iniciada com sucesso.',

                'finalizar_conferencia_separacao' =>
                    'Conferência da separação finalizada com sucesso.',

                'iniciar_carregamento' =>
                    'Carregamento iniciado com sucesso.',

                'finalizar_carregamento' =>
                    'Carregamento finalizado com sucesso.',

                'iniciar_conferencia_saida' =>
                    'Conferência de saída iniciada com sucesso.',

                'finalizar_conferencia_saida' =>
                    'Conferência de saída finalizada com sucesso.',

                'liberar_veiculo' =>
                    'Veículo liberado com sucesso. Registre a saída física.',

                'registrar_saida' =>
                    'Saída registrada. Os romaneios confirmados estão em rota.',

                'registrar_retorno' =>
                    'Retorno do veículo registrado com sucesso.',

                'iniciar_conferencia_retorno' =>
                    'Conferência do retorno iniciada com sucesso.',

                'finalizar_conferencia_retorno' =>
                    'Conferência do retorno finalizada com sucesso.',

                'iniciar_prestacao_contas' =>
                    'Prestação de contas iniciada com sucesso.',

                'finalizar_prestacao_contas' =>
                    'Prestação de contas finalizada com sucesso.',

                'fechar_romaneio' =>
                    'Romaneio fechado com sucesso.',

                'navegar_etapa' =>
                    'Etapa operacional alterada com sucesso e registrada no histórico.',

                default =>
                    'Operação atualizada com sucesso.',
            };

            return redirect()
                ->route(
                    'romaneios.create',
                    [
                        'entrega_id' =>
                            $romaneioAtualizado
                                ->entrega_id,
                    ]
                )
                ->with(
                    'success',
                    $mensagem
                );

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(
                    $e->errors()
                );

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao atualizar a operação do romaneio: '
                    . $e->getMessage()
                );
        }
    }

    public function cancelar(Request $request, Romaneio $romaneio)
    {
        $dadosValidados = $request->validate(
            [
                'motivo_cancelamento' => ['required', 'string', 'min:5', 'max:500'],

            ],

            [
                'motivo_cancelamento.required' => 'Informe o motivo do cancelamento.',

                'motivo_cancelamento.min' => 'O motivo do cancelamento deve possuir pelo menos 5 caracteres.',

                'motivo_cancelamento.max' => 'O motivo do cancelamento pode possuir no máximo 500 caracteres.',

            ]
        );

        try {
            $this->romaneioService->cancelar(
                $romaneio,
                $dadosValidados['motivo_cancelamento']
            );

            return redirect()
                ->route('romaneios.index')
                ->with('success', 'Romaneio cancelado com sucesso.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível cancelar o romaneio.');

        }

    }

    public function atribuirEquipe(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',

            'veiculo',

            'motoristaExecutante',

            'veiculoExecutante',

            'entrega.orcamento.cliente',

            'entrega.cliente',

        ]);

        $motoristas = Funcionario::query()
            ->where('funcao', 'motorista')
            ->where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');

            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query->where('ativo', 1)->orWhereNull('ativo');

            })
            ->orderBy('observacao')
            ->get();

        return view(
            'expedicao.atribuir-equipe',

            compact('romaneio', 'motoristas', 'veiculos')
        );

    }

    // public function salvarEquipe(Request $request, Romaneio $romaneio)
    // {
    //     $dadosValidados = $request->validate(
    //         [
    //             'motorista_id' => ['required', 'integer', 'exists:funcionarios,id'],

    //             'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],

    //             'data_prevista_saida' => ['nullable', 'date'],

    //             'data_prevista_retorno' => [
    //                 'nullable',

    //                 'date',

    //                 'after_or_equal:data_prevista_saida',

    //             ],

    //             'ordem_execucao' => ['nullable', 'integer', 'min:1'],

    //             'prioridade' => ['nullable', Rule::in(['Baixa', 'Normal', 'Alta', 'Urgente'])],

    //         ],

    //         [
    //             'motorista_id.required' => 'Selecione o motorista.',

    //             'motorista_id.exists' => 'O motorista selecionado não foi encontrado.',

    //             'veiculo_id.required' => 'Selecione o veículo.',

    //             'veiculo_id.exists' => 'O veículo selecionado não foi encontrado.',

    //             'data_prevista_retorno.after_or_equal' => 'O retorno previsto não pode ser anterior à saída prevista.',

    //         ]
    //     );

    //     if (! $romaneio->podeAlterarPlanejamento()) {
    //         return back()->with(
    //             'error',

    //             'A equipe e o planejamento não podem ser alterados após a liberação da viagem.'
    //         );

    //     }

    //     $romaneio->update([
    //         'motorista_id' => $dadosValidados['motorista_id'],

    //         'veiculo_id' => $dadosValidados['veiculo_id'],

    //         'data_prevista_saida' => $dadosValidados['data_prevista_saida'] ?? $romaneio->data_prevista_saida,

    //         'data_prevista_retorno' => $dadosValidados['data_prevista_retorno'] ?? $romaneio->data_prevista_retorno,

    //         'ordem_execucao' => $dadosValidados['ordem_execucao'] ?? $romaneio->ordem_execucao,

    //         'prioridade' => $dadosValidados['prioridade'] ?? $romaneio->prioridade,

    //     ]);

    //     return redirect()
    //         ->route('romaneios.create', ['entrega_id' => $romaneio->entrega_id])
    //         ->with('success', 'Equipe e planejamento atualizados com sucesso.');

    // }

    public function salvarEquipe(Request $request, Romaneio $romaneio)
    {
        $dadosValidados = $request->validate(
            [
                'motorista_id' => [
                    'required',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'veiculo_id' => [
                    'required',
                    'integer',
                    'exists:veiculos,id',
                ],

                'data_prevista_saida' => [
                    'nullable',
                    'date',
                ],

                'data_prevista_retorno' => [
                    'nullable',
                    'date',
                    'after_or_equal:data_prevista_saida',
                ],

                'ordem_execucao' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'prioridade' => [
                    'nullable',
                    Rule::in([
                        'Baixa',
                        'Normal',
                        'Alta',
                        'Urgente',
                    ]),
                ],
            ],
            [
                'motorista_id.required' =>
                    'Selecione o motorista.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.required' =>
                    'Selecione o veículo.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',

                'data_prevista_retorno.after_or_equal' =>
                    'O retorno previsto não pode ser anterior à saída prevista.',
            ]
        );

        if (! $romaneio->podeAlterarPlanejamento()) {
            return back()->with(
                'error',
                'A equipe e o planejamento não podem ser alterados após a liberação da viagem.'
            );
        }

        try {
            /*
            * Atribuição/substituição de motorista e veículo.
            *
            * IMPORTANTE:
            * Toda regra de conflito, disponibilidade e histórico
            * deve permanecer centralizada no ExpedicaoService.
            */
            $this->expedicaoService->salvarEquipe(
                $romaneio,
                [
                    'motorista_id' =>
                        $dadosValidados['motorista_id'],

                    'veiculo_id' =>
                        $dadosValidados['veiculo_id'],
                ]
            );

            /*
            * Campos de planejamento pertencentes ao romaneio.
            */
            $romaneio->refresh();

            $romaneio->update([
                'data_prevista_saida' =>
                    $dadosValidados['data_prevista_saida']
                    ?? $romaneio->data_prevista_saida,

                'data_prevista_retorno' =>
                    $dadosValidados['data_prevista_retorno']
                    ?? $romaneio->data_prevista_retorno,

                'ordem_execucao' =>
                    $dadosValidados['ordem_execucao']
                    ?? $romaneio->ordem_execucao,

                'prioridade' =>
                    $dadosValidados['prioridade']
                    ?? $romaneio->prioridade,
            ]);

            return redirect()
                ->route(
                    'romaneios.create',
                    [
                        'entrega_id' =>
                            $romaneio->entrega_id,
                    ]
                )
                ->with(
                    'success',
                    'Equipe e planejamento atualizados com sucesso.'
                );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function separacao(Romaneio $romaneio)
    {
        return redirect()->route(
            'romaneios.create',

            ['entrega_id' => $romaneio->entrega_id]
        );

    }

    private function regrasCriacao(): array
    {
        return [
            'entrega_id' => ['nullable', 'integer', 'exists:entregas,id'],

            'entregas' => ['nullable', 'array'],

            'entregas.*' => ['nullable', 'integer', 'exists:entregas,id'],

            'entrega_itens' => ['nullable', 'array'],

            'entrega_itens.*' => ['nullable', 'integer', 'exists:entrega_itens,id'],

            'itens' => ['nullable', 'array'],

            'itens.*.entrega_item_id' => [
                'required_with:itens',

                'integer',

                'distinct',

                'exists:entrega_itens,id',

            ],

            'itens.*.quantidade' => ['required_with:itens', 'numeric', 'gt:0'],

            'motorista_id' => ['nullable', 'integer', 'exists:funcionarios,id'],

            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],

            'data_prevista_saida' => ['nullable', 'date'],

            'data_prevista_retorno' => [
                'nullable',

                'date',

                'after_or_equal:data_prevista_saida',

            ],

            'ordem_execucao' => ['nullable', 'integer', 'min:1'],

            'prioridade' => ['nullable', Rule::in(['Baixa', 'Normal', 'Alta', 'Urgente'])],

            'planejamento_confirmado' => ['nullable', 'boolean'],

            'romaneio_origem_id' => ['nullable', 'integer', 'exists:romaneios,id'],

            'observacao' => ['nullable', 'string', 'max:1000'],

        ];

    }

    private function mensagensCriacao(): array
    {
        return [
            'entrega_id.exists' => 'A entrega selecionada não foi encontrada.',

            'entregas.array' => 'A seleção de entregas é inválida.',

            'entregas.*.integer' => 'Uma das entregas selecionadas é inválida.',

            'entregas.*.exists' => 'Uma das entregas selecionadas não foi encontrada.',

            'entrega_itens.array' => 'A seleção de itens é inválida.',

            'entrega_itens.*.integer' => 'Um dos itens selecionados é inválido.',

            'entrega_itens.*.exists' => 'Um dos itens selecionados não foi encontrado.',

            'itens.array' => 'Os itens informados são inválidos.',

            'itens.*.entrega_item_id.required_with' => 'Não foi possível identificar um dos itens.',

            'itens.*.entrega_item_id.integer' => 'Um dos itens informados é inválido.',

            'itens.*.entrega_item_id.distinct' => 'O mesmo item foi informado mais de uma vez.',

            'itens.*.entrega_item_id.exists' => 'Um dos itens da entrega não foi encontrado.',

            'itens.*.quantidade.required_with' => 'Informe a quantidade de todos os itens do romaneio.',

            'itens.*.quantidade.numeric' => 'A quantidade do item deve ser numérica.',

            'itens.*.quantidade.gt' => 'A quantidade de cada item deve ser maior que zero.',

            'motorista_id.integer' => 'O motorista selecionado é inválido.',

            'motorista_id.exists' => 'O motorista selecionado não foi encontrado.',

            'veiculo_id.integer' => 'O veículo selecionado é inválido.',

            'veiculo_id.exists' => 'O veículo selecionado não foi encontrado.',

            'data_prevista_saida.date' => 'A data prevista de saída é inválida.',

            'data_prevista_retorno.date' => 'A data prevista de retorno é inválida.',

            'data_prevista_retorno.after_or_equal' => 'O retorno previsto não pode ser anterior à saída prevista.',

            'ordem_execucao.integer' => 'A ordem de execução deve ser um número inteiro.',

            'ordem_execucao.min' => 'A ordem de execução deve ser maior que zero.',

            'prioridade.in' => 'A prioridade informada é inválida.',

            'planejamento_confirmado.boolean' => 'A confirmação do planejamento é inválida.',

            'romaneio_origem_id.exists' => 'O romaneio de origem não foi encontrado.',

            'observacao.string' => 'A observação deve ser um texto.',

            'observacao.max' => 'A observação pode possuir no máximo 1000 caracteres.',

        ];

    }

    private function regrasOperacao(): array
    {
        return [
            'acao' => ['required', 'string', Rule::in(self::ACOES_OPERACIONAIS)],

            'etapa_destino' => [
                'nullable',

                'required_if:acao,navegar_etapa',

                'string',

                Rule::in([
                    'montagem',

                    'separacao',

                    'conferencia_separacao',

                    'carregamento',

                    'conferencia_saida',

                    'liberacao',

                ]),

            ],

            'motivo_movimentacao' => [
                'nullable',

                'required_if:acao,navegar_etapa',

                'string',

                'min:5',

                'max:1000',

            ],

            'motivo_retorno' => [
                'nullable',

                'required_if:acao,voltar_etapa',

                'string',

                'min:5',

                'max:1000',

            ],

            'metodo_identificacao' => [
                'nullable',

                'string',

                Rule::in([
                    'Sistema',

                    'codigo_barras',

                    'qr_code',

                    'codigo_operacional',

                    'pesquisa_manual',

                ]),

            ],

            'motorista_id' => ['nullable', 'integer', 'exists:funcionarios,id'],

            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],

            'separado_por' => ['nullable', 'integer', 'exists:funcionarios,id'],

            'conferencia_separacao_por' => [
                'nullable',

                'required_if:acao,iniciar_conferencia_separacao',

                'integer',

                'exists:funcionarios,id',

            ],

            'carregado_por' => [
                'nullable',

                'required_if:acao,iniciar_carregamento',

                'integer',

                'exists:funcionarios,id',

            ],

            'conferencia_saida_por' => [
                'nullable',

                'required_if:acao,iniciar_conferencia_saida',

                'integer',

                'exists:funcionarios,id',

            ],

            'retorno_conferido_por' => [
                'nullable',

                'required_if:acao,iniciar_conferencia_retorno',

                'integer',

                'exists:funcionarios,id',

            ],

            'observacao' => ['nullable', 'string', 'max:1000'],

            'observacao_retorno' => ['nullable', 'string', 'max:1000'],

            'metodo_fechamento' => [
                'nullable',

                'required_if:acao,fechar_romaneio',

                'string',

                Rule::in([
                    'codigo_barras',

                    'qr_code',

                    'codigo_operacional',

                    'pesquisa_manual',

                ]),

            ],

            'justificativa_fechamento_manual' => [
                'nullable',

                'required_if:metodo_fechamento,pesquisa_manual',

                'string',

                'min:5',

                'max:1000',

            ],

            'itens' => ['nullable', 'array'],

            'itens.*.entrega_item_id' => [
                'required_with:itens',

                'integer',

                'exists:entrega_itens,id',

            ],

            'itens.*.romaneio_item_id' => [
                'nullable',

                'integer',

                'exists:romaneio_itens,id',

            ],

            'itens.*.quantidade_separada' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_conferida_separacao' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_carregada' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_conferida_saida' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_entregue' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_devolvida' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_recusada' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_avariada' => ['nullable', 'numeric', 'min:0'],

            'itens.*.quantidade_perdida' => ['nullable', 'numeric', 'min:0'],

            'itens.*.observacao' => ['nullable', 'string', 'max:500'],

        ];

    }

    private function mensagensOperacaoValidacao(): array
    {
        return [
            'acao.required' => 'A ação operacional não foi informada.',

            'acao.in' => 'A ação operacional informada é inválida.',

            'etapa_destino.required_if' => 'Informe a etapa operacional de destino.',

            'etapa_destino.in' => 'A etapa operacional de destino é inválida.',

            'motivo_movimentacao.required_if' => 'Informe o motivo da alteração de etapa.',

            'motivo_movimentacao.min' => 'O motivo da alteração deve possuir pelo menos 5 caracteres.',

            'motivo_retorno.required_if' => 'Informe o motivo do retorno da etapa.',

            'motivo_retorno.min' => 'O motivo do retorno deve possuir pelo menos 5 caracteres.',

            'metodo_identificacao.in' => 'O método de identificação informado é inválido.',

            'motorista_id.exists' => 'O motorista selecionado não foi encontrado.',

            'veiculo_id.exists' => 'O veículo selecionado não foi encontrado.',

            'separado_por.exists' => 'O funcionário responsável pela separação não foi encontrado.',

            'conferencia_separacao_por.required_if' => 'Informe o funcionário responsável pela conferência da separação.',

            'conferencia_separacao_por.exists' => 'O funcionário responsável pela conferência da separação não foi encontrado.',

            'carregado_por.required_if' => 'Informe o funcionário responsável pelo carregamento.',

            'carregado_por.exists' => 'O funcionário responsável pelo carregamento não foi encontrado.',

            'conferencia_saida_por.required_if' => 'Informe o funcionário responsável pela conferência de saída.',

            'conferencia_saida_por.exists' => 'O funcionário responsável pela conferência de saída não foi encontrado.',

            'retorno_conferido_por.required_if' => 'Informe o funcionário responsável pela conferência do retorno.',

            'retorno_conferido_por.exists' => 'O funcionário responsável pela conferência do retorno não foi encontrado.',

            'metodo_fechamento.required_if' => 'Informe o método utilizado para localizar e fechar o romaneio.',

            'metodo_fechamento.in' => 'O método de fechamento informado é inválido.',

            'justificativa_fechamento_manual.required_if' => 'Informe a justificativa para o fechamento por pesquisa manual.',

            'justificativa_fechamento_manual.min' => 'A justificativa do fechamento manual deve possuir pelo menos 5 caracteres.',

            'itens.array' => 'Os dados dos itens são inválidos.',

            'itens.*.entrega_item_id.exists' => 'Um dos itens da entrega não foi encontrado.',

            'itens.*.romaneio_item_id.exists' => 'Um dos itens do romaneio não foi encontrado.',

            'itens.*.quantidade_separada.min' => 'A quantidade separada não pode ser negativa.',

            'itens.*.quantidade_conferida_separacao.min' => 'A quantidade conferida na separação não pode ser negativa.',

            'itens.*.quantidade_carregada.min' => 'A quantidade carregada não pode ser negativa.',

            'itens.*.quantidade_conferida_saida.min' => 'A quantidade conferida na saída não pode ser negativa.',

            'itens.*.quantidade_entregue.min' => 'A quantidade entregue não pode ser negativa.',

            'itens.*.quantidade_devolvida.min' => 'A quantidade devolvida não pode ser negativa.',

            'itens.*.quantidade_recusada.min' => 'A quantidade recusada não pode ser negativa.',

            'itens.*.quantidade_avariada.min' => 'A quantidade avariada não pode ser negativa.',

            'itens.*.quantidade_perdida.min' => 'A quantidade perdida não pode ser negativa.',

        ];

    }

    private function relacionamentosOperacionais(bool $comOcorrenciasDetalhadas = false): array
    {
        $relacionamentos = [
            'motorista',

            'veiculo',

            'motoristaExecutante',

            'veiculoExecutante',

            'liberador',

            'romaneioOrigem',

            'romaneiosFilhos',

            'impressor',

            'criador',

            'iniciador',

            'carregador',

            'conferente',

            'usuarioInicioConferenciaSeparacao',

            'usuarioFimConferenciaSeparacao',

            'usuarioInicioConferenciaSaida',

            'usuarioFimConferenciaSaida',

            'usuarioRegistroRetorno',

            'usuarioPrestacaoContas',

            'usuarioFechamento',

            'cancelador',

            'entrega.cliente',

            'entrega.orcamento.cliente',

            'entrega.venda.cliente',

            'itens.separador',

            'itens.conferenteSeparacao',

            'itens.carregador',

            'itens.conferenteSaida',

            'itens.conferenteRetorno',

            'itens.entregaItem.entrega.cliente',

            'itens.entregaItem.vendaItem.produto',

            'itens.entregaItem.itemOrcamento.produto',

            'eventos.usuario',

            'eventos.funcionario',

        ];

        if ($comOcorrenciasDetalhadas) {
            $relacionamentos = array_merge($relacionamentos, [
                'finalizador',

                'ocorrencias.autorizador',

                'ocorrencias.registrador',

                'ocorrencias.resolvedor',

                'ocorrencias.anexos',

            ]);

        } else {
            $relacionamentos[] = 'ocorrencias';

        }

        return array_values(array_unique($relacionamentos));

    }

    private function mensagemOperacao(string $acao): string
    {
        return match ($acao) {
            'concluir_montagem' => 'Montagem concluída com sucesso.',

            'salvar_andamento' => 'Andamento salvo com sucesso.',

            'iniciar_separacao' => 'Separação iniciada com sucesso.',

            'finalizar_separacao' => 'Separação finalizada e encaminhada para conferência.',

            'iniciar_conferencia_separacao' => 'Conferência da separação iniciada.',

            'finalizar_conferencia_separacao' => 'Conferência da separação concluída. O romaneio está disponível para carregamento.',

            'iniciar_carregamento' => 'Carregamento iniciado com sucesso.',

            'finalizar_carregamento' => 'Carregamento finalizado e encaminhado para conferência de saída.',

            'iniciar_conferencia_saida' => 'Conferência final de saída iniciada.',

            'finalizar_conferencia_saida' => 'Conferência final de saída concluída.',

            'liberar_veiculo' => 'Veículo liberado. Registre a saída física.',

            'registrar_saida' => 'Saída registrada. O romaneio está em rota.',

            'registrar_retorno' => 'Retorno do veículo registrado.',

            'iniciar_conferencia_retorno' => 'Conferência do retorno iniciada.',

            'finalizar_conferencia_retorno' => 'Conferência do retorno concluída.',

            'iniciar_prestacao_contas' => 'Prestação de contas iniciada.',

            'finalizar_prestacao_contas' => 'Prestação de contas concluída. O romaneio está aguardando fechamento.',

            'fechar_romaneio' => 'Romaneio fechado com sucesso.',

            'voltar_etapa' => 'O romaneio retornou para a etapa anterior.',

            'navegar_etapa' => 'Etapa operacional alterada com sucesso e registrada no histórico.',

            default => 'Operação atualizada com sucesso.',
        };

    }

    // public function imprimir(Romaneio $romaneio)
    // {
    //     $romaneio->load([
    //         ...$this->relacionamentosOperacionais(),
    //         'finalizador',
    //         'impressor',
    //     ]);

    //     $romaneio->setRelation(
    //         'itens',
    //         $romaneio->itens
    //             ->sortBy(function ($item) {
    //                 $produto = $item->entregaItem?->vendaItem?->produto
    //                     ?? $item->entregaItem?->itemOrcamento?->produto;

    //                 return $produto?->localizacao_estoque ?? 'ZZZ';
    //             })
    //             ->values()
    //     );

    //     return view('romaneios.imprimir', compact('romaneio'));
    // }
    public function imprimir(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'criador',
            'iniciador',
            'carregador',
            'conferente',
            'finalizador',
            'impressor',
            'entrega.cliente',
            'entrega.orcamento.cliente',
            'entrega.venda.cliente',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $romaneio->setRelation(
            'itens',
            $romaneio->itens
                ->sortBy(function ($item) {
                    $entregaItem = $item->entregaItem;

                    $produto = $entregaItem?->produto
                        ?? $entregaItem?->vendaItem?->produto
                        ?? $entregaItem?->itemOrcamento?->produto
                        ?? null;

                    return strtolower(
                        trim(
                            (string) (
                                $produto?->localizacao_estoque
                                ?? 'ZZZ'
                            )
                        )
                    );
                })
                ->values()
        );

        $entrega = $romaneio->entrega;

        $enderecoDestino = trim(
            (string) (
                $entrega?->endereco_entrega
                ?? ''
            )
        );

        if ($enderecoDestino === '') {
            $enderecoDestino = trim(
                (string) (
                    $entrega?->endereco_entrega_concatenado
                    ?? ''
                )
            );
        }

        $urlRota = null;
        $qrCodeRota = null;

        if ($enderecoDestino !== '') {
            $urlRota =
                'https://www.google.com/maps/dir/?api=1'
                . '&destination='
                . rawurlencode($enderecoDestino)
                . '&travelmode=driving'
                . '&dir_action=navigate';

            $geradorQrCode =
                new \Endroid\QrCode\Builder\Builder(
                    writer:
                        new \Endroid\QrCode\Writer\SvgWriter(),
                    writerOptions: [],
                    validateResult: false,
                    data: $urlRota,
                    encoding:
                        new \Endroid\QrCode\Encoding\Encoding(
                            'ISO-8859-1'
                        ),
                    errorCorrectionLevel:
                        \Endroid\QrCode\ErrorCorrectionLevel::High,
                    size: 300,
                    margin: 10,
                    roundBlockSizeMode:
                        \Endroid\QrCode\RoundBlockSizeMode::Margin
                );

            $qrCodeRota = $geradorQrCode
                ->build()
                ->getDataUri();
        }

        return view(
            'romaneios.imprimir',
            compact(
                'romaneio',
                'enderecoDestino',
                'urlRota',
                'qrCodeRota'
            )
        );
    }

    public function imprimirNotaEntrega(Romaneio $romaneio)
    {
        $statusPermitidos = [
            'Aguardando_liberacao',
            'Liberado',
            'Em_rota',
            'Retornando',
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
            'Fechado',
        ];

        abort_unless(
            in_array((string) $romaneio->status, $statusPermitidos, true),
            422,
            'A Nota de Entrega somente pode ser emitida após a conferência final de saída.'
        );

        $romaneio->load([
            'entrega.cliente',
            'entrega.orcamento.cliente',
            'entrega.venda.cliente',
            'motorista',
            'veiculo',
            'itens.entregaItem.itemOrcamento.produto',
            'itens.entregaItem.vendaItem.produto',
        ]);

        $romaneio->setRelation(
            'itens',
            $romaneio->itens
                ->sortBy(fn ($item) => (int) ($item->ordem ?? PHP_INT_MAX))
                ->values()
        );

        $empresa = Empresa::ativa();

        return view(
            'romaneios.nota-entrega',
            compact('romaneio', 'empresa')
        );
    }

    public function registrarImpressao(Romaneio $romaneio)
    {
        $statusPermitidos = [
            'Aguardando_liberacao',
            'Liberado',
            'Em_rota',
            'Retornando',
            'Aguardando_conferencia_retorno',
            'Em_conferencia_retorno',
            'Aguardando_prestacao_contas',
            'Em_prestacao_contas',
            'Aguardando_fechamento',
            'Fechado',
        ];

        if (! in_array((string) $romaneio->status, $statusPermitidos, true)) {
            return back()->with(
                'error',
                'O romaneio somente pode ser impresso após a conferência final de saída.'
            );
        }

        try {
            $romaneio->forceFill([
                'impresso_em' => now(),
                'impresso_por' => auth()->id(),
            ])->save();

            return redirect()->route('romaneios.imprimir', $romaneio);
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Não foi possível registrar a impressão do romaneio.'
            );
        }
    }

}