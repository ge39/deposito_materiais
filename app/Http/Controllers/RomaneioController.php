<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Models\Veiculo;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RomaneioController extends Controller
{
    public function __construct(
        protected RomaneioService $romaneioService
    ) {
    }

    public function index(Request $request)
    {
        $statusValidos = [
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

        $romaneios = Romaneio::query()
            ->with([
                'entrega.cliente',
                'entrega.orcamento.cliente',
                'entrega.venda.cliente',
                'motorista',
                'veiculo',
                'itens',
                'ocorrencias',
            ])
            ->when(
                $request->filled('status')
                && in_array(
                    $request->input('status'),
                    $statusValidos,
                    true
                ),
                fn ($query) => $query->where(
                    'status',
                    $request->input('status')
                )
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'romaneios.index',
            compact(
                'romaneios',
                'statusValidos'
            )
        );
    }

    public function create(Request $request)
    {
        $entregaId = $request->integer('entrega_id');

        if (
            ! $entregaId
            && $request->filled('entregas_id')
        ) {
            $entregaId = (int) $request->input(
                'entregas_id'
            );
        }

        $statusEntregasOperacionais = [
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

        $entregasDisponiveis = Entrega::query()
            ->with([
                'cliente',
                'orcamento.cliente',
                'venda.cliente',
                'itens.produto',
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->whereIn(
                'status',
                $statusEntregasOperacionais
            )
            ->when(
                $entregaId,
                fn ($query) => $query->where(
                    'id',
                    $entregaId
                )
            )
            ->orderBy('data_prevista')
            ->orderBy('id')
            ->get();

        $romaneioAtivo = null;

        if ($entregaId) {
            $romaneioAtivo = Romaneio::query()
                ->with([
                    'motorista',
                    'veiculo',
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
                    'entrega.cliente',
                    'entrega.orcamento.cliente',
                    'entrega.venda.cliente',
                    'itens.separador',
                    'itens.conferenteSeparacao',
                    'itens.carregador',
                    'itens.conferenteSaida',
                    'itens.conferenteRetorno',
                    'itens.entregaItem.entrega.cliente',
                    'itens.entregaItem.produto',
                    'itens.entregaItem.vendaItem.produto',
                    'itens.entregaItem.itemOrcamento.produto',
                    'ocorrencias',
                    'eventos.usuario',
                    'eventos.funcionario',
                ])
                ->where(
                    'entrega_id',
                    $entregaId
                )
                ->whereNotIn('status', [
                    'Fechado',
                    'Cancelado',
                ])
                ->latest('id')
                ->first();

            if ($romaneioAtivo?->entrega) {
                $romaneioAtivo->entrega->loadMissing([
                    'cliente',
                    'orcamento.cliente',
                    'venda.cliente',
                    'itens.produto',
                    'itens.vendaItem.produto',
                    'itens.itemOrcamento.produto',
                ]);

                $entregasDisponiveis = collect([
                    $romaneioAtivo->entrega,
                ]);
            }
        }

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

        $funcionariosOperacionais = Funcionario::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $motoristas = $funcionariosOperacionais
            ->where('funcao', 'motorista')
            ->values();

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view(
            'romaneios.create',
            compact(
                'entregasDisponiveis',
                'funcionariosOperacionais',
                'motoristas',
                'veiculos',
                'entregaId',
                'romaneioAtivo'
            )
        );
    }

    public function store(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'entrega_id' => [
                    'nullable',
                    'integer',
                    'exists:entregas,id',
                ],

                'entregas' => [
                    'nullable',
                    'array',
                ],

                'entregas.*' => [
                    'nullable',
                    'integer',
                    'exists:entregas,id',
                ],

                'entrega_itens' => [
                    'nullable',
                    'array',
                ],

                'entrega_itens.*' => [
                    'nullable',
                    'integer',
                    'exists:entrega_itens,id',
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

                'itens.*.quantidade' => [
                    'required_with:itens',
                    'numeric',
                    'gt:0',
                ],

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

                'observacao' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'entrega_id.exists' =>
                    'A entrega selecionada não foi encontrada.',

                'entregas.array' =>
                    'A seleção de entregas é inválida.',

                'entregas.*.integer' =>
                    'Uma das entregas selecionadas é inválida.',

                'entregas.*.exists' =>
                    'Uma das entregas selecionadas não foi encontrada.',

                'entrega_itens.array' =>
                    'A seleção de itens é inválida.',

                'entrega_itens.*.integer' =>
                    'Um dos itens selecionados é inválido.',

                'entrega_itens.*.exists' =>
                    'Um dos itens selecionados não foi encontrado.',

                'itens.array' =>
                    'Os itens informados são inválidos.',

                'itens.*.entrega_item_id.required_with' =>
                    'Não foi possível identificar um dos itens.',

                'itens.*.entrega_item_id.integer' =>
                    'Um dos itens informados é inválido.',

                'itens.*.entrega_item_id.exists' =>
                    'Um dos itens da entrega não foi encontrado.',

                'itens.*.quantidade.required_with' =>
                    'Informe a quantidade de todos os itens do romaneio.',

                'itens.*.quantidade.numeric' =>
                    'A quantidade do item deve ser numérica.',

                'itens.*.quantidade.gt' =>
                    'A quantidade de cada item deve ser maior que zero.',

                'motorista_id.required' =>
                    'Selecione o motorista responsável pela entrega.',

                'motorista_id.integer' =>
                    'O motorista selecionado é inválido.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.required' =>
                    'Selecione o veículo que realizará a entrega.',

                'veiculo_id.integer' =>
                    'O veículo selecionado é inválido.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 1000 caracteres.',
            ]
        );

        $possuiEntrega =
            ! empty($dadosValidados['entrega_id'] ?? null)
            || ! empty($dadosValidados['entregas'] ?? []);

        $possuiItens =
            ! empty($dadosValidados['entrega_itens'] ?? [])
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
            $romaneio = $this->romaneioService
                ->criarRomaneio(
                    $dadosValidados
                );

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
                    'Romaneio criado com sucesso e aguardando o início da separação.'
                );
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao criar romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function show(Romaneio $romaneio)
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
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
            'ocorrencias.autorizador',
            'ocorrencias.registrador',
            'ocorrencias.resolvedor',
            'ocorrencias.anexos',
            'eventos.usuario',
            'eventos.funcionario',
        ]);

        return view(
            'romaneios.show',
            compact('romaneio')
        );
    }

    public function atualizarOperacao(Request $request,Romaneio $romaneio) 
    {
        $acoesPermitidas = [
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

                'motorista_id' => [
                    'nullable',
                    'integer',
                    'exists:funcionarios,id',
                ],

                'veiculo_id' => [
                    'nullable',
                    'integer',
                    'exists:veiculos,id',
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

                'motorista_id.integer' =>
                    'O motorista informado é inválido.',

                'motorista_id.exists' =>
                    'O motorista selecionado não foi encontrado.',

                'veiculo_id.integer' =>
                    'O veículo informado é inválido.',

                'veiculo_id.exists' =>
                    'O veículo selecionado não foi encontrado.',

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
            $romaneioAtualizado = $this->romaneioService
                ->atualizarOperacao(
                    $romaneio,
                    $dadosValidados['acao'],
                    $dadosValidados
                );

            $mensagem = match ($dadosValidados['acao']) {
                'salvar_andamento' =>
                    'Andamento salvo com sucesso.',

                'iniciar_separacao' =>
                    'Separação iniciada com sucesso.',

                'finalizar_separacao' =>
                    'Separação finalizada com sucesso.',

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
                    'Saída do veículo registrada. O romaneio está em rota.',

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
                            $romaneioAtualizado->entrega_id,
                    ]
                )
                ->with('success', $mensagem);

        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao atualizar a operação do romaneio: ' .
                    $e->getMessage()
                );
        }
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

        if (! in_array(
            $romaneio->status,
            $statusPermitidos,
            true
        )) {
            return back()->with(
                'error',
                'O romaneio somente pode ser impresso após a conferência final de saída.'
            );
        }

        try {
            $romaneio->update([
                'impresso_em' => now(),
                'impresso_por' => auth()->id(),
            ]);

            return redirect()->route(
                'romaneios.imprimir',
                $romaneio
            );
        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Erro ao registrar a impressão do romaneio: ' .
                $e->getMessage()
            );
        }
    }

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
            'itens.separador',
            'itens.conferenteSeparacao',
            'itens.carregador',
            'itens.conferenteSaida',
            'itens.entregaItem.entrega.cliente',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $romaneio->setRelation(
            'itens',
            $romaneio->itens
                ->sortBy(function ($item) {
                    return
                        $item->entregaItem?->produto
                            ?->localizacao_estoque
                        ?? $item->entregaItem?->vendaItem
                            ?->produto?->localizacao_estoque
                        ?? $item->entregaItem?->itemOrcamento
                            ?->produto?->localizacao_estoque
                        ?? 'ZZZ';
                })
                ->values()
        );

        return view(
            'romaneios.imprimir',
            compact('romaneio')
        );
    }

    public function cancelar(Request $request, Romaneio $romaneio) 
    {
        $dadosValidados = $request->validate(
            [
                'motivo_cancelamento' => [
                    'required',
                    'string',
                    'min:5',
                    'max:500',
                ],
            ],
            [
                'motivo_cancelamento.required' =>
                    'Informe o motivo do cancelamento.',

                'motivo_cancelamento.min' =>
                    'O motivo do cancelamento deve possuir pelo menos 5 caracteres.',

                'motivo_cancelamento.max' =>
                    'O motivo do cancelamento pode possuir no máximo 500 caracteres.',
            ]
        );

        try {
            $this->romaneioService->cancelar(
                $romaneio,
                $dadosValidados['motivo_cancelamento']
            );

            return redirect()
                ->route('romaneios.index')
                ->with(
                    'success',
                    'Romaneio cancelado com sucesso.'
                );
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao cancelar o romaneio: ' .
                    $e->getMessage()
                );
        }
    }

    public function atribuirEquipe(Romaneio $romaneio)
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.orcamento.cliente',
            'entrega.cliente',
        ]);

        $motoristas = Funcionario::query()
            ->where('funcao', 'motorista')
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('nome')
            ->get();

        $veiculos = Veiculo::query()
            ->where(function ($query) {
                $query
                    ->where('ativo', 1)
                    ->orWhereNull('ativo');
            })
            ->orderBy('observacao')
            ->get();

        return view(
            'expedicao.atribuir-equipe',
            compact(
                'romaneio',
                'motoristas',
                'veiculos'
            )
        );
    }

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
            ]
        );

        $statusBloqueados = [
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

        if (in_array(
            $romaneio->status,
            $statusBloqueados,
            true
        )) {
            return back()->with(
                'error',
                'A equipe não pode ser alterada no status atual do romaneio.'
            );
        }

        $romaneio->update([
            'motorista_id' =>
                $dadosValidados['motorista_id'],

            'veiculo_id' =>
                $dadosValidados['veiculo_id'],
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
                'Equipe atribuída ao romaneio com sucesso.'
            );
    }

    public function separacao(Romaneio $romaneio)
    {
        return redirect()->route(
            'romaneios.create',
            [
                'entrega_id' =>
                    $romaneio->entrega_id,
            ]
        );
    }

    private function mensagemOperacao(string $acao): string
    {
        return match ($acao) {
            'salvar_andamento' =>
                'Andamento salvo com sucesso.',

            'iniciar_separacao' =>
                'Separação iniciada com sucesso.',

            'finalizar_separacao' =>
                'Separação finalizada e encaminhada para conferência.',

            'iniciar_conferencia_separacao' =>
                'Conferência da separação iniciada.',

            'finalizar_conferencia_separacao' =>
                'Conferência da separação concluída. O romaneio está disponível para carregamento.',

            'iniciar_carregamento' =>
                'Carregamento iniciado com sucesso.',

            'finalizar_carregamento' =>
                'Carregamento finalizado e encaminhado para conferência de saída.',

            'iniciar_conferencia_saida' =>
                'Conferência final de saída iniciada.',

            'finalizar_conferencia_saida' =>
                'Conferência final de saída concluída.',

            'liberar_veiculo' =>
                'Veículo liberado. Registre a saída física.',

            'registrar_saida' =>
                'Saída registrada. O romaneio está em rota.',

            'registrar_retorno' =>
                'Retorno do veículo registrado.',

            'iniciar_conferencia_retorno' =>
                'Conferência do retorno iniciada.',

            'finalizar_conferencia_retorno' =>
                'Conferência do retorno concluída.',

            'iniciar_prestacao_contas' =>
                'Prestação de contas iniciada.',

            'finalizar_prestacao_contas' =>
                'Prestação de contas concluída. O romaneio está aguardando fechamento.',

            'fechar_romaneio' =>
                'Romaneio fechado com sucesso.',

            'voltar_etapa' =>
                'O romaneio retornou para a etapa anterior.',

            default =>
                'Operação atualizada com sucesso.',
        };
    }
}