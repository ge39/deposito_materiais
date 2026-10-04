<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\EntregaFracionamento;
use App\Models\EntregaItem;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
use App\Services\Entregas\EntregaService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RomaneioService
{
    private const STATUS_MONTAGEM = 'Montagem';
    private const STATUS_AGUARDANDO_SEPARACAO = 'Aguardando_separacao';
    private const STATUS_EM_SEPARACAO = 'Em_separacao';
    private const STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO = 'Aguardando_conferencia_separacao';
    private const STATUS_EM_CONFERENCIA_SEPARACAO = 'Em_conferencia_separacao';
    private const STATUS_SEPARACAO_CONFERIDA = 'Separacao_conferida';
    private const STATUS_AGUARDANDO_CARREGAMENTO = 'Aguardando_carregamento';
    private const STATUS_CARREGANDO = 'Carregando';
    private const STATUS_AGUARDANDO_CONFERENCIA_SAIDA = 'Aguardando_conferencia_saida';
    private const STATUS_EM_CONFERENCIA_SAIDA = 'Em_conferencia_saida';
    private const STATUS_AGUARDANDO_LIBERACAO = 'Aguardando_liberacao';
    private const STATUS_LIBERADO = 'Liberado';
    private const STATUS_EM_ROTA = 'Em_rota';
    private const STATUS_RETORNANDO = 'Retornando';
    private const STATUS_AGUARDANDO_CONFERENCIA_RETORNO = 'Aguardando_conferencia_retorno';
    private const STATUS_EM_CONFERENCIA_RETORNO = 'Em_conferencia_retorno';
    private const STATUS_AGUARDANDO_TRATATIVA_OCORRENCIA = 'Aguardando_tratativa_ocorrencia';
    private const STATUS_AGUARDANDO_PRESTACAO_CONTAS = 'Aguardando_prestacao_contas';
    private const STATUS_EM_PRESTACAO_CONTAS = 'Em_prestacao_contas';
    private const STATUS_AGUARDANDO_FECHAMENTO = 'Aguardando_fechamento';
    private const STATUS_FECHADO = 'Fechado';
    private const STATUS_FECHADO_COM_OCORRENCIA = 'Fechado_com_ocorrencia';
    private const STATUS_CANCELADO = 'Cancelado';

    public function __construct(
        private readonly RomaneioEventoService $eventoService,
        private readonly RomaneioOcorrenciaService $ocorrenciaService,
        private readonly EntregaService $entregaService
    ) {
    }

    public function criarRomaneio(array $dados): Romaneio
    {
        return DB::transaction(function () use ($dados) {
            $entregasIds = collect($dados['entregas'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $entregaItensIds = collect($dados['entrega_itens'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $itensComQuantidade = collect($dados['itens'] ?? [])
                ->filter(function ($item) {
                    return is_array($item)
                        && (int) ($item['entrega_item_id'] ?? 0) > 0
                        && (float) ($item['quantidade'] ?? 0) > 0;
                })
                ->map(function ($item) {
                    return [
                        'entrega_item_id' => (int) $item['entrega_item_id'],
                        'quantidade' => round(
                            (float) $item['quantidade'],
                            2
                        ),
                    ];
                })
                ->values();

            if ((int) ($dados['entrega_id'] ?? 0) > 0) {
                $entregasIds
                    ->push((int) $dados['entrega_id']);

                $entregasIds = $entregasIds
                    ->unique()
                    ->values();
            }

            if ($itensComQuantidade->isNotEmpty()) {
                $entregaItensIds = $entregaItensIds
                    ->merge(
                        $itensComQuantidade->pluck(
                            'entrega_item_id'
                        )
                    )
                    ->unique()
                    ->values();
            }

            if (
                $entregasIds->isEmpty()
                && $entregaItensIds->isEmpty()
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Selecione pelo menos uma entrega ou item para criar o romaneio.',
                ]);
            }

            $entregaItens = $this->buscarItensParaRomaneio(
                $entregasIds->all(),
                $entregaItensIds->all()
            );

            if ($entregaItens->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Nenhum item disponível foi encontrado para criação do romaneio.',
                ]);
            }

            $this->validarEntregasDosItens(
                $entregaItens
            );

            $itensPreparados = $this->prepararItensDoRomaneio(
                $entregaItens,
                $itensComQuantidade
            );

            if ($itensPreparados->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Os itens selecionados não possuem saldo disponível.',
                ]);
            }

            $entregaPrincipal = $itensPreparados
                ->first()['entrega_item']
                ->entrega;

            if (! $entregaPrincipal) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        'Não foi possível identificar a entrega principal.',
                ]);
            }

            $romaneio = Romaneio::create([
                'entrega_id' => $entregaPrincipal->id,
                'criado_por' => Auth::id(),
                'codigo_romaneio' => $this->gerarCodigoRomaneio(),
                'token_abertura' => Str::random(64),
                'token_fechamento' => Str::random(64),
                'status' => self::STATUS_MONTAGEM,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'motorista_id' => $dados['motorista_id'] ?? null,
                'data_emissao' => now(),
                'percentual_carregado' => 0,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            foreach (
                $itensPreparados->values()
                as $indice => $itemPreparado
            ) {
                RomaneioItem::create([
                    'romaneio_id' => $romaneio->id,
                    'entrega_item_id' =>
                        $itemPreparado['entrega_item']->id,
                    'ordem' => $indice + 1,
                    'quantidade_prevista' =>
                        $itemPreparado['quantidade'],
                    'quantidade_separada' => 0,
                    'quantidade_conferida_separacao' => 0,
                    'quantidade_conferida' => 0,
                    'quantidade_carregada' => 0,
                    'quantidade_conferida_saida' => 0,
                    'quantidade_entregue' => 0,
                    'quantidade_devolvida' => 0,
                    'quantidade_recusada' => 0,
                    'quantidade_avariada' => 0,
                    'quantidade_perdida' => 0,
                    'status' => 'Pendente',
                ]);
            }

            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' =>
                    self::STATUS_AGUARDANDO_SEPARACAO,
            ]);

            $this->atualizarStatusEntregas(
                $romaneio,
                'Aguardando_separacao'
            );

            $romaneio->refresh();

            $this->eventoService->registrarCriacao(
                $romaneio
            );

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Montagem concluída',
                etapa: 'Montagem',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        });
    }


    public function atualizarOperacao(Romaneio $romaneio,string $acao, array $dados = []): Romaneio 
    {
        return DB::transaction(function () use (
            $romaneio,
            $acao,
            $dados
        ) {
            $romaneio = $this->bloquearRomaneio(
                $romaneio->id
            );

            $this->validarAcaoPermitida(
                $romaneio,
                $acao
            );

            return match ($acao) {
                'concluir_montagem' =>
                    $this->concluirMontagem(
                        $romaneio,
                        $dados
                    ),

                'salvar_andamento' =>
                    $this->salvarAndamento(
                        $romaneio,
                        $dados
                    ),

                'iniciar_separacao' =>
                    $this->iniciarSeparacao(
                        $romaneio,
                        $dados
                    ),

                'finalizar_separacao' =>
                    $this->finalizarSeparacao(
                        $romaneio,
                        $dados
                    ),

                'iniciar_conferencia_separacao' =>
                    $this->iniciarConferenciaSeparacao(
                        $romaneio,
                        $dados
                    ),

                'finalizar_conferencia_separacao' =>
                    $this->finalizarConferenciaSeparacao(
                        $romaneio,
                        $dados
                    ),

                'iniciar_carregamento' =>
                    $this->iniciarCarregamento(
                        $romaneio,
                        $dados
                    ),

                'finalizar_carregamento' =>
                    $this->finalizarCarregamento(
                        $romaneio,
                        $dados
                    ),

                'iniciar_conferencia_saida' =>
                    $this->iniciarConferenciaSaida(
                        $romaneio,
                        $dados
                    ),

                'finalizar_conferencia_saida' =>
                    $this->finalizarConferenciaSaida(
                        $romaneio,
                        $dados
                    ),

                /*
                * Motorista e veículo selecionados na última etapa
                * são enviados para a liberação.
                */
                'liberar_veiculo' =>
                    $this->liberarVeiculo(
                        $romaneio,
                        $dados
                    ),

                /*
                * A confirmação documental dos romaneios segue
                * para o registro conjunto da saída.
                */
                'registrar_saida' =>
                    $this->registrarSaida(
                        $romaneio,
                        $dados
                    ),

                'registrar_retorno' =>
                    $this->registrarRetorno(
                        $romaneio,
                        $dados
                    ),

                'finalizar_triagem_retorno' =>
                    $this->finalizarTriagemRetorno(
                        $romaneio
                    ),

                'iniciar_conferencia_retorno' =>
                    $this->iniciarConferenciaRetorno(
                        $romaneio,
                        $dados
                    ),

                'finalizar_conferencia_retorno' =>
                    $this->finalizarConferenciaRetorno(
                        $romaneio,
                        $dados
                    ),

                'iniciar_prestacao_contas' =>
                    $this->iniciarPrestacaoContas(
                        $romaneio
                    ),

                'finalizar_prestacao_contas' =>
                    $this->finalizarPrestacaoContas(
                        $romaneio,
                        $dados
                    ),

                'fechar_romaneio' =>
                    $this->fecharRomaneio(
                        $romaneio,
                        $dados
                    ),

                'voltar_etapa' =>
                    $this->retornarEtapaAnterior(
                        $romaneio,
                        $dados
                    ),

                'navegar_etapa' =>
                    $this->navegarParaEtapa(
                        $romaneio,
                        $dados
                    ),

                default =>
                    throw ValidationException::withMessages([
                        'acao' =>
                            'A ação operacional informada é inválida.',
                    ]),
            };
        });
    }

    private function concluirMontagem(Romaneio $romaneio, array $dados): Romaneio 
    {
        $romaneio->load('itens');

        if ($romaneio->itens->isEmpty()) {
            throw ValidationException::withMessages([
                'itens' =>
                    'O romaneio não possui itens para concluir a montagem.',
            ]);
        }

        foreach ($romaneio->itens as $item) {
            if ((float) $item->quantidade_prevista <= 0) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$item->entrega_item_id} possui quantidade prevista inválida.",
                ]);
            }
        }

        $this->atualizarDataPrevistaEntregaComplementar(
            $romaneio,
            $dados
        );

        /*
        * Motorista e veículo são opcionais durante a montagem.
        * Quando informados, ficam registrados antecipadamente.
        * A obrigatoriedade será aplicada somente na liberação.
        */
        $motoristaId = ! empty(
            $dados['motorista_id'] ?? null
        )
            ? (int) $dados['motorista_id']
            : $romaneio->motorista_id;

        $veiculoId = ! empty(
            $dados['veiculo_id'] ?? null
        )
            ? (int) $dados['veiculo_id']
            : $romaneio->veiculo_id;

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'motorista_id' =>
                $motoristaId,

            'veiculo_id' =>
                $veiculoId,

            'observacao' =>
                array_key_exists(
                    'observacao',
                    $dados
                )
                    ? (
                        $dados['observacao']
                            ?: null
                    )
                    : $romaneio->observacao,

            'status' =>
                self::STATUS_AGUARDANDO_SEPARACAO,

            'percentual_carregado' =>
                0,
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Aguardando_separacao'
        );

        $romaneio->refresh();

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Montagem concluída',
            etapa: 'Montagem',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function atualizarDataPrevistaEntregaComplementar(
        Romaneio $romaneio,
        array $dados
    ): void {
        $entrega = Entrega::query()
            ->lockForUpdate()
            ->find($romaneio->entrega_id);

        if (! $entrega) {
            throw ValidationException::withMessages([
                'entrega' =>
                    'Não foi possível localizar a entrega vinculada ao romaneio.',
            ]);
        }

        $entregaComplementar =
            (int) $entrega->entrega_origem_id > 0;

        if (! $entregaComplementar) {
            if (
                array_key_exists(
                    'data_prevista_entrega',
                    $dados
                )
            ) {
                throw ValidationException::withMessages([
                    'data_prevista_entrega' =>
                        'A alteração da data nesta etapa é permitida somente para entregas complementares.',
                ]);
            }

            return;
        }

        $dataInformada = trim(
            (string) (
                $dados['data_prevista_entrega']
                ?? ''
            )
        );

        if ($dataInformada === '') {
            throw ValidationException::withMessages([
                'data_prevista_entrega' =>
                    'Informe a data prevista da entrega complementar.',
            ]);
        }

        try {
            $dataPrevista = \Carbon\Carbon::parse(
                $dataInformada
            )->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'data_prevista_entrega' =>
                    'A data prevista da entrega complementar é inválida.',
            ]);
        }

        if ($dataPrevista->lt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'data_prevista_entrega' =>
                    'A data prevista da entrega complementar não pode ser anterior à data atual.',
            ]);
        }

        $dataPrevistaFormatada =
            $dataPrevista->toDateString();

        $entrega->update([
            'data_prevista' =>
                $dataPrevistaFormatada,

            'data_prevista_entrega' =>
                $dataPrevistaFormatada,
        ]);
    }

    private function salvarAndamento(Romaneio $romaneio, array $dados): Romaneio 
    {
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $this->atualizarPercentualCarregado(
            $romaneio
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarSeparacao(Romaneio $romaneio, array $dados): Romaneio 
    {
        $separadorId = $this->validarFuncionario(
            $dados,
            'separado_por',
            'Informe o funcionário responsável pela separação.'
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_EM_SEPARACAO,
            'iniciado_por' =>
                $romaneio->iniciado_por ?? Auth::id(),
            'data_inicio_separacao' =>
                $romaneio->data_inicio_separacao ?? now(),
        ]);

        $romaneio->load('itens');

        foreach ($romaneio->itens as $romaneioItem) {
            $romaneioItem->update([
                'separado_por' => $separadorId,
                'separado_em' =>
                    $romaneioItem->separado_em ?? now(),
            ]);
        }

        $this->salvarDadosOperacionais(
            $romaneio,
            array_merge(
                $dados,
                [
                    'separado_por' => $separadorId,
                ]
            )
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->eventoService->registrarAbertura(
            $romaneio,
            $statusAnterior,
            $dados['metodo_identificacao']
                ?? 'Sistema'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function finalizarSeparacao(Romaneio $romaneio, array $dados): Romaneio 
    {
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarSeparacaoParaFinalizacao(
            $romaneio
        );

        $romaneioSaldo = $this->processarSaldosSeparacao(
            $romaneio,
            $dados
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO,

            'data_fim_separacao' =>
                $romaneio->data_fim_separacao ?? now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Material_separado'
        );

        $observacaoEvento = $romaneioSaldo
            ? sprintf(
                'Saldo encaminhado para o romaneio %s.',
                $romaneioSaldo->codigo_romaneio
            )
            : null;

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Separação finalizada',
            etapa: 'Separacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            observacao: $observacaoEvento
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarConferenciaSeparacao(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $conferenteId = $this->validarFuncionario(
            $dados,
            'conferencia_separacao_por',
            'Informe o funcionário responsável pela conferência da separação.'
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_EM_CONFERENCIA_SEPARACAO,
            'data_inicio_conferencia_separacao' =>
                $romaneio->data_inicio_conferencia_separacao
                ?? now(),
            'conferencia_separacao_iniciada_por' =>
                Auth::id(),
        ]);

        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            array_merge(
                $dados,
                [
                    'conferencia_separacao_por' =>
                        $conferenteId,
                ]
            )
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Conferência da separação iniciada',
            etapa: 'Conferencia_separacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            funcionarioId: $conferenteId
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function finalizarConferenciaSeparacao(Romaneio $romaneio, array $dados): Romaneio 
    {
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarConferenciaSeparacaoCompleta(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_SEPARACAO_CONFERIDA,
            'data_fim_conferencia_separacao' =>
                $romaneio->data_fim_conferencia_separacao
                ?? now(),
            'conferencia_separacao_finalizada_por' =>
                Auth::id(),
        ]);

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Conferência da separação concluída',
            etapa: 'Conferencia_separacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_CARREGAMENTO,
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Separacao_conferida'
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Romaneio liberado para carregamento',
            etapa: 'Carregamento',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarCarregamento(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $carregadorId = $this->validarFuncionario(
            $dados,
            'carregado_por',
            'Informe o funcionário responsável pelo carregamento.'
        );

        $this->validarConferenciaSeparacaoCompleta(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_CARREGANDO,
            'carregado_por' => $carregadorId,
            'data_inicio_carregamento' =>
                $romaneio->data_inicio_carregamento
                ?? now(),
        ]);

        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            array_merge(
                $dados,
                [
                    'carregado_por' =>
                        $carregadorId,
                ]
            )
        );

        $this->atualizarPercentualCarregado(
            $romaneio
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Carregamento iniciado',
            etapa: 'Carregamento',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            funcionarioId: $carregadorId
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function finalizarCarregamento(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarCarregamentoCompleto(
            $romaneio
        );

        $this->atualizarPercentualCarregado(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_CONFERENCIA_SAIDA,
            'data_fim_carregamento' =>
                $romaneio->data_fim_carregamento ?? now(),
            'percentual_carregado' => 100,
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Material_carregado'
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Carregamento finalizado',
            etapa: 'Carregamento',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            funcionarioId: $romaneio->carregado_por
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarConferenciaSaida(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $conferenteId = $this->validarFuncionario(
            $dados,
            'conferencia_saida_por',
            'Informe o funcionário responsável pela conferência de saída.'
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_EM_CONFERENCIA_SAIDA,
            'data_inicio_conferencia_saida' =>
                $romaneio->data_inicio_conferencia_saida
                ?? now(),
            'conferencia_saida_iniciada_por' =>
                Auth::id(),
        ]);

        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            array_merge(
                $dados,
                [
                    'conferencia_saida_por' =>
                        $conferenteId,
                ]
            )
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Conferência de saída iniciada',
            etapa: 'Conferencia_saida',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            funcionarioId: $conferenteId
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function finalizarConferenciaSaida(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarConferenciaSaidaCompleta(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_LIBERACAO,
            'data_fim_conferencia_saida' =>
                $romaneio->data_fim_conferencia_saida
                ?? now(),
            'conferencia_saida_finalizada_por' =>
                Auth::id(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Saida_conferida'
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Conferência de saída concluída',
            etapa: 'Conferencia_saida',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }
    
    private function liberarVeiculo(Romaneio $romaneio, array $dados): Romaneio 
    {
        $motoristaId = (int) (
            $dados['motorista_id']
            ?? $romaneio->motorista_id
            ?? 0
        );

        $veiculoId = (int) (
            $dados['veiculo_id']
            ?? $romaneio->veiculo_id
            ?? 0
        );

        if ($motoristaId <= 0) {
            throw ValidationException::withMessages([
                'motorista_id' =>
                    'Selecione o motorista antes de liberar o veículo.',
            ]);
        }

        if ($veiculoId <= 0) {
            throw ValidationException::withMessages([
                'veiculo_id' =>
                    'Selecione o veículo antes da liberação.',
            ]);
        }

        /*
        * Persiste a equipe definida na etapa de liberação.
        */
        $romaneio->update([
            'motorista_id' =>
                $motoristaId,

            'veiculo_id' =>
                $veiculoId,

            'observacao' =>
                array_key_exists(
                    'observacao',
                    $dados
                )
                    ? (
                        $dados['observacao']
                        ?: $romaneio->observacao
                    )
                    : $romaneio->observacao,
        ]);

        $romaneio->refresh();

        $romaneio->load([
            'itens',
            'ocorrencias',
            'motorista',
            'veiculo',
        ]);

        /*
        * Confirma carga, impressão, motorista, veículo
        * e inexistência de ocorrências bloqueantes.
        */
        $this->validarLiberacao(
            $romaneio
        );

        $statusAnterior =
            $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_LIBERADO,

            'finalizado_por' =>
                Auth::id(),

            'liberado_por' =>
                Auth::id(),

            'liberado_em' =>
                now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Liberado'
        );

        $this->eventoService->registrarTransicao(
            romaneio:
                $romaneio,

            evento:
                'Veículo liberado',

            etapa:
                'Liberacao',

            statusAnterior:
                $statusAnterior,

            statusNovo:
                $romaneio->status,

            dados: [
                'motorista_id' =>
                    $motoristaId,

                'motorista' =>
                    $romaneio->motorista?->nome,

                'veiculo_id' =>
                    $veiculoId,

                'veiculo' =>
                    $romaneio->veiculo?->placa,
            ]
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function registrarSaida(Romaneio $romaneio, array $dados): Romaneio 
    {
        $romaneio->refresh();

        $motoristaId = (int) (
            $romaneio->motorista_id
            ?? 0
        );

        $veiculoId = (int) (
            $romaneio->veiculo_id
            ?? 0
        );

        if ($motoristaId <= 0) {
            throw ValidationException::withMessages([
                'motorista_id' =>
                    'O romaneio não possui motorista definido.',
            ]);
        }

        if ($veiculoId <= 0) {
            throw ValidationException::withMessages([
                'veiculo_id' =>
                    'O romaneio não possui veículo definido.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Estados de entrega pertencentes à preparação da saída
        |--------------------------------------------------------------------------
        |
        | Estados posteriores à saída, como retorno, ocorrência, devolução,
        | prestação de contas e fechamento, não pertencem à próxima viagem.
        |
        */

        $statusEntregasPreparacaoSaida = [
            'Aguardando_separacao',
            'Material_separado',
            'Separacao_conferida',
            'Material_carregado',
            'Saida_conferida',
            'Liberado',

            // Compatibilidade temporária com entregas abertas antes deste ajuste.
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
        | STATUS_QUE_RESERVAM_SALDO contém exclusivamente os estados:
        | Montagem até Liberado.
        |
        | Em_rota e todos os estados pós-viagem ficam fora desta consulta.
        |
        */

        $romaneiosDoVeiculo = Romaneio::query()
            ->with([
                'entrega',
                'motorista',
                'veiculo',
                'itens',
            ])
            ->where(
                'veiculo_id',
                $veiculoId
            )
            ->whereIn(
                'status',
                [
                    self::STATUS_MONTAGEM,
                    self::STATUS_AGUARDANDO_SEPARACAO,
                    self::STATUS_EM_SEPARACAO,
                    self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO,
                    self::STATUS_EM_CONFERENCIA_SEPARACAO,
                    self::STATUS_SEPARACAO_CONFERIDA,
                    self::STATUS_AGUARDANDO_CARREGAMENTO,
                    self::STATUS_CARREGANDO,
                    self::STATUS_AGUARDANDO_CONFERENCIA_SAIDA,
                    self::STATUS_EM_CONFERENCIA_SAIDA,
                    self::STATUS_AGUARDANDO_LIBERACAO,
                    self::STATUS_LIBERADO,
                ]
            )
            ->whereHas(
                'entrega',
                fn ($query) =>
                    $query->whereIn(
                        'status',
                        $statusEntregasPreparacaoSaida
                    )
            )
            ->lockForUpdate()
            ->orderBy('ordem_execucao')
            ->orderBy('id')
            ->get();

        if ($romaneiosDoVeiculo->isEmpty()) {
            throw ValidationException::withMessages([
                'romaneios_confirmados' =>
                    'Não foram encontrados romaneios disponíveis para a saída deste veículo.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Confirmação do romaneio atual
        |--------------------------------------------------------------------------
        */

        if (
            ! $romaneiosDoVeiculo->contains(
                fn ($item) =>
                    (int) $item->id
                    === (int) $romaneio->id
            )
        ) {
            throw ValidationException::withMessages([
                'romaneios_confirmados' =>
                    'O romaneio atual não está disponível para a saída deste veículo.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Romaneios ainda não liberados
        |--------------------------------------------------------------------------
        |
        | Somente cargas pertencentes à próxima viagem podem bloquear a saída.
        |
        */

        $romaneiosPendentes = $romaneiosDoVeiculo
            ->filter(
                fn ($item) =>
                    $item->status
                    !== self::STATUS_LIBERADO
            )
            ->values();

        if ($romaneiosPendentes->isNotEmpty()) {
            $descricaoPendentes = $romaneiosPendentes
                ->map(function ($item) {
                    $codigoEntrega =
                        $item->entrega?->codigo_entrega
                        ?? "#{$item->entrega_id}";

                    $status = str_replace(
                        '_',
                        ' ',
                        (string) $item->status
                    );

                    return
                        $item->codigo_romaneio
                        . ' / '
                        . $codigoEntrega
                        . ' — '
                        . $status;
                })
                ->implode('; ');

            throw ValidationException::withMessages([
                'romaneios_pendentes' =>
                    'O caminhão possui cargas da próxima viagem ainda não liberadas e deverá aguardar: '
                    . $descricaoPendentes
                    . '.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Conferência do motorista
        |--------------------------------------------------------------------------
        */

        $romaneioMotoristaDivergente =
            $romaneiosDoVeiculo->first(
                fn ($item) =>
                    (int) $item->motorista_id
                    !== $motoristaId
            );

        if ($romaneioMotoristaDivergente) {
            $motoristaDivergente =
                $romaneioMotoristaDivergente
                    ->motorista?->nome
                ?? 'não identificado';

            throw ValidationException::withMessages([
                'motorista_id' =>
                    'O romaneio '
                    . $romaneioMotoristaDivergente
                        ->codigo_romaneio
                    . ' está vinculado ao motorista '
                    . $motoristaDivergente
                    . '. Todos os romaneios do caminhão devem possuir o mesmo motorista.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Conferência da impressão
        |--------------------------------------------------------------------------
        */

        $romaneioNaoImpresso =
            $romaneiosDoVeiculo->first(
                fn ($item) =>
                    empty($item->impresso_em)
            );

        if ($romaneioNaoImpresso) {
            throw ValidationException::withMessages([
                'romaneios_confirmados' =>
                    'Imprima o romaneio '
                    . $romaneioNaoImpresso
                        ->codigo_romaneio
                    . ' antes de registrar a saída.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Romaneios confirmados pelo operador
        |--------------------------------------------------------------------------
        */

        $idsEsperados = $romaneiosDoVeiculo
            ->pluck('id')
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->sort()
            ->values();

        $idsConfirmados = collect(
            $dados['romaneios_confirmados']
            ?? []
        )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->filter(
                fn ($id) =>
                    $id > 0
            )
            ->unique()
            ->sort()
            ->values();

        if ($idsConfirmados->isEmpty()) {
            throw ValidationException::withMessages([
                'romaneios_confirmados' =>
                    'Confirme os romaneios entregues ao motorista antes de registrar a saída.',
            ]);
        }

        if (
            $idsConfirmados->all()
            !== $idsEsperados->all()
        ) {
            throw ValidationException::withMessages([
                'romaneios_confirmados' =>
                    'Confirme a entrega de todos os romaneios vinculados ao caminhão antes da saída.',
            ]);
        }

        $dataSaida = now();

        /*
        |--------------------------------------------------------------------------
        | Registro da saída conjunta
        |--------------------------------------------------------------------------
        */

        foreach (
            $romaneiosDoVeiculo
            as $romaneioDaViagem
        ) {
            $statusAnterior =
                $romaneioDaViagem->status;

            $romaneioDaViagem->update([
                'status' =>
                    self::STATUS_EM_ROTA,

                'data_saida' =>
                    $dataSaida,
            ]);

            $this->atualizarStatusEntregas(
                $romaneioDaViagem,
                'Em_rota'
            );

            $this->eventoService->registrarTransicao(
                romaneio:
                    $romaneioDaViagem,

                evento:
                    'Saída do veículo registrada',

                etapa:
                    'Em_rota',

                statusAnterior:
                    $statusAnterior,

                statusNovo:
                    self::STATUS_EM_ROTA,

                funcionarioId:
                    $motoristaId,

                dados: [
                    'motorista_id' =>
                        $motoristaId,

                    'veiculo_id' =>
                        $veiculoId,

                    'romaneios_da_viagem' =>
                        $idsEsperados->all(),

                    'documentos_confirmados' =>
                        true,

                    'data_saida_conjunta' =>
                        $dataSaida->toDateTimeString(),
                ]
            );
        }

        $romaneio->refresh();

        return $this->carregarRomaneio(
            $romaneio
        );

    }

        private function registrarRetorno(Romaneio $romaneio, array $dados): Romaneio 
        {
            $tipoRetorno = strtolower(
                trim(
                    (string) (
                        $dados['tipo_retorno']
                        ?? ''
                    )
                )
            );

            if (! in_array(
                $tipoRetorno,
                [
                    'normal',
                    'ocorrencia',
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'tipo_retorno' =>
                        'O tipo de retorno informado é inválido.',
                ]);
            }

            $romaneio->loadMissing([
                'itens.entregaItem',
            ]);

            $itensRecebidos = collect(
                $dados['itens'] ?? []
            )->keyBy('romaneio_item_id');

            if ($itensRecebidos->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Informe o resultado dos produtos transportados.',
                ]);
            }

            foreach ($romaneio->itens as $romaneioItem) {
                $dadosItem = $itensRecebidos->get(
                    $romaneioItem->id
                );

                if (! is_array($dadosItem)) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "Informe o resultado do produto vinculado ao item #{$romaneioItem->entrega_item_id}.",
                    ]);
                }

                if (
                    (int) ($dadosItem['entrega_item_id'] ?? 0)
                    !== (int) $romaneioItem->entrega_item_id
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$romaneioItem->entrega_item_id} não pertence ao produto informado.",
                    ]);
                }

                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    2
                );

                $quantidadeEntregue = round(
                    (float) (
                        $dadosItem['quantidade_entregue']
                        ?? 0
                    ),
                    2
                );

                $quantidadeDevolvida = round(
                    (float) (
                        $dadosItem['quantidade_devolvida']
                        ?? 0
                    ),
                    2
                );

                $quantidadeRecusada = round(
                    (float) (
                        $dadosItem['quantidade_recusada']
                        ?? 0
                    ),
                    2
                );

                $quantidadeAvariada = round(
                    (float) (
                        $dadosItem['quantidade_avariada']
                        ?? 0
                    ),
                    2
                );

                $quantidadePerdida = round(
                    (float) (
                        $dadosItem['quantidade_perdida']
                        ?? 0
                    ),
                    2
                );

                $quantidades = [
                    'quantidade_entregue' =>
                        $quantidadeEntregue,

                    'quantidade_devolvida' =>
                        $quantidadeDevolvida,

                    'quantidade_recusada' =>
                        $quantidadeRecusada,

                    'quantidade_avariada' =>
                        $quantidadeAvariada,

                    'quantidade_perdida' =>
                        $quantidadePerdida,
                ];

                foreach ($quantidades as $quantidade) {
                    if ($quantidade < 0) {
                        throw ValidationException::withMessages([
                            'itens' =>
                                "O item #{$romaneioItem->entrega_item_id} possui uma quantidade negativa.",
                        ]);
                    }

                    if ($quantidade > $quantidadeSaida) {
                        throw ValidationException::withMessages([
                            'itens' =>
                                "O resultado do item #{$romaneioItem->entrega_item_id} não pode ultrapassar a quantidade conferida na saída.",
                        ]);
                    }
                }

                $totalResultado = round(
                    $quantidadeEntregue
                    + $quantidadeDevolvida
                    + $quantidadeRecusada
                    + $quantidadeAvariada
                    + $quantidadePerdida,
                    2
                );

                if ($totalResultado !== $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        'itens' =>
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

            $this->salvarDadosOperacionais(
                $romaneio,
                [
                    'itens' =>
                        $dados['itens'],
                ]
            );

            $romaneio->refresh();

            $romaneio->load([
                'itens.entregaItem',
            ]);

            $statusAnterior = $romaneio->status;

            if ($tipoRetorno === 'normal') {
                $romaneio->update([
                    'status' =>
                        self::STATUS_AGUARDANDO_PRESTACAO_CONTAS,

                    'data_retorno' =>
                        now(),

                    'retorno_registrado_por' =>
                        Auth::id(),
                ]);

                $romaneio->refresh();

                $romaneio->load([
                    'itens.entregaItem',
                ]);

                /*
                * Como todas as quantidades foram confirmadas como entregues,
                * o resultado da entrega torna-se definitivo sem a necessidade
                * de conferência física.
                */
                $this->atualizarResultadoFinalEntregas(
                    $romaneio
                );

                $this->eventoService->registrarTransicao(
                    romaneio: $romaneio,
                    evento: 'Entrega realizada normalmente',
                    etapa: 'Retorno',
                    statusAnterior: $statusAnterior,
                    statusNovo: $romaneio->status,
                    observacao:
                        $dados['observacao_retorno']
                        ?? 'Entrega realizada normalmente, sem ocorrências.'
                );

                return $this->carregarRomaneio(
                    $romaneio
                );
            }

            /*
            * Somente o retorno com ocorrência segue para conferência física.
            * A entrega permanece sem resultado definitivo até a conclusão
            * dessa conferência.
            */
            $romaneio->update([
                'status' =>
                    self::STATUS_AGUARDANDO_CONFERENCIA_RETORNO,

                'data_retorno' =>
                    now(),

                'retorno_registrado_por' =>
                    Auth::id(),
            ]);

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Retorno com ocorrência registrado',
                etapa: 'Retorno',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status,
                observacao:
                    $dados['observacao_retorno']
                    ?? null
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function iniciarConferenciaRetorno(Romaneio $romaneio, array $dados ): Romaneio 
        {
            $conferenteId = $this->validarFuncionario(
                $dados,
                'retorno_conferido_por',
                'Informe o funcionário responsável pela conferência do retorno.'
            );

            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' =>
                    self::STATUS_EM_CONFERENCIA_RETORNO,
            ]);

            $romaneio->load('itens');

            $this->salvarDadosOperacionais(
                $romaneio,
                array_merge(
                    $dados,
                    [
                        'retorno_conferido_por' =>
                            $conferenteId,
                    ]
                )
            );

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Conferência do retorno iniciada',
                etapa: 'Conferencia_retorno',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status,
                funcionarioId: $conferenteId
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function finalizarConferenciaRetorno(
                Romaneio $romaneio,
                    array $dados
                ): Romaneio {
                    $usuarioId = Auth::id();

                    if (! $usuarioId) {
                        throw ValidationException::withMessages([
                            'usuario' =>
                                'Não foi possível identificar o responsável pela conclusão do retorno.',
                    ]);
                }

            $romaneio->load([
                'itens.entregaItem.vendaItem.lote',
                'ocorrencias' => fn ($query) =>
                    $query
                        ->whereNotIn(
                            'status',
                            [
                                'Cancelada',
                            ]
                        )
                        ->orderBy('id'),
                'ocorrencias.avaliacoes',
            ]);

            $ocorrencias = $romaneio->ocorrencias
                ->filter(
                    fn ($ocorrencia) =>
                        $ocorrencia->status !== 'Cancelada'
                )
                ->values();

            if ($ocorrencias->isEmpty()) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        'Nenhum produto com problema foi identificado na triagem do retorno.',
                ]);
            }

            $ocorrenciasPendentes = $ocorrencias
                ->filter(
                    fn ($ocorrencia) =>
                        $ocorrencia->triagem_status !== 'Concluida'
                )
                ->values();

            if ($ocorrenciasPendentes->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        'Existem produtos com avaliação visual pendente. Conclua todas as avaliações antes de finalizar o retorno.',
                ]);
            }

            $mapeamentoClassificacoes = [
                'Devolucao' => [
                    'campo' =>
                        'quantidade_devolvida',

                    'exige_avaliacao' =>
                        true,
                ],

                'Recusa' => [
                    'campo' =>
                        'quantidade_recusada',

                    'exige_avaliacao' =>
                        true,
                ],

                'Avaria' => [
                    'campo' =>
                        'quantidade_avariada',

                    'exige_avaliacao' =>
                        true,
                ],

                'Extravio' => [
                    'campo' =>
                        'quantidade_perdida',

                    'exige_avaliacao' =>
                        false,
                ],
            ];

            $itensConsolidados = [];

            foreach ($romaneio->itens as $romaneioItem) {
                $quantidadeSaida = round(
                    (float) $romaneioItem
                        ->quantidade_conferida_saida,
                    3
                );

                if ($quantidadeSaida <= 0) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$romaneioItem->entrega_item_id} não possui quantidade conferida na saída.",
                    ]);
                }

                $quantidadesResultado = [
                    'quantidade_devolvida' =>
                        0.0,

                    'quantidade_recusada' =>
                        0.0,

                    'quantidade_avariada' =>
                        0.0,

                    'quantidade_perdida' =>
                        0.0,
                ];

                $ocorrenciasDoItem = $ocorrencias
                    ->filter(
                        fn ($ocorrencia) =>
                            (int) $ocorrencia->romaneio_item_id
                            === (int) $romaneioItem->id
                    )
                    ->values();

                foreach ($ocorrenciasDoItem as $ocorrencia) {
                    $classificacao = trim(
                        (string) (
                            $ocorrencia->classificacao_final
                            ?: $ocorrencia->classificacao_inicial
                        )
                    );

                    if (! isset(
                        $mapeamentoClassificacoes[$classificacao]
                    )) {
                        throw ValidationException::withMessages([
                            'ocorrencias' =>
                                "A ocorrência #{$ocorrencia->id} possui uma classificação inválida.",
                        ]);
                    }

                    $quantidadeOcorrencia = round(
                        (float) $ocorrencia->quantidade_envolvida,
                        3
                    );

                    if ($quantidadeOcorrencia <= 0) {
                        throw ValidationException::withMessages([
                            'ocorrencias' =>
                                "A ocorrência #{$ocorrencia->id} não possui uma quantidade válida.",
                        ]);
                    }

                    $configuracao =
                        $mapeamentoClassificacoes[$classificacao];

                    if ($configuracao['exige_avaliacao']) {
                        $loteOrigemId =
                            $romaneioItem
                                ->entregaItem
                                ?->vendaItem
                                ?->lote_id
                            ?? $romaneioItem
                                ->entregaItem
                                ?->vendaItem
                                ?->lote
                                ?->id;

                        if (! $loteOrigemId) {
                            throw ValidationException::withMessages([
                                'lote' =>
                                    "O item #{$romaneioItem->entrega_item_id} não possui o lote original utilizado na saída. A movimentação de retorno foi bloqueada.",
                            ]);
                        }

                        if ($ocorrencia->avaliacoes->isEmpty()) {
                            throw ValidationException::withMessages([
                                'avaliacoes' =>
                                    "A ocorrência #{$ocorrencia->id} não possui avaliação física.",
                            ]);
                        }

                        $quantidadeAvaliada = round(
                            (float) $ocorrencia
                                ->avaliacoes
                                ->sum('quantidade'),
                            3
                        );

                        if (
                            abs(
                                $quantidadeAvaliada
                                - $quantidadeOcorrencia
                            ) >= 0.001
                        ) {
                            throw ValidationException::withMessages([
                                'avaliacoes' =>
                                    "A soma das avaliações da ocorrência #{$ocorrencia->id} deve ser igual à quantidade afetada.",
                            ]);
                        }

                        foreach (
                            $ocorrencia->avaliacoes
                            as $avaliacao
                        ) {
                            if (
                                (int) $avaliacao->lote_id
                                !== (int) $loteOrigemId
                            ) {
                                throw ValidationException::withMessages([
                                    'lote' =>
                                        "A avaliação #{$avaliacao->id} não preservou o lote original do item #{$romaneioItem->entrega_item_id}.",
                                ]);
                            }
                        }
                    }

                    $campoQuantidade =
                        $configuracao['campo'];

                    $quantidadesResultado[$campoQuantidade] =
                        round(
                            $quantidadesResultado[$campoQuantidade]
                            + $quantidadeOcorrencia,
                            3
                        );
                }

                $quantidadeComProblema = round(
                    array_sum($quantidadesResultado),
                    3
                );

                if ($quantidadeComProblema > $quantidadeSaida) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade total com problema do item #{$romaneioItem->entrega_item_id} ultrapassa a quantidade conferida na saída.",
                    ]);
                }

                /*
                * A parte do produto que não recebeu ocorrência
                * foi entregue normalmente e não precisa ser
                * reavaliada pelo operador.
                */
                $quantidadeEntregue = round(
                    $quantidadeSaida
                    - $quantidadeComProblema,
                    3
                );

                $itensConsolidados[] = [
                    'romaneio_item_id' =>
                        $romaneioItem->id,

                    'entrega_item_id' =>
                        $romaneioItem->entrega_item_id,

                    'quantidade_entregue' =>
                        $quantidadeEntregue,

                    'quantidade_devolvida' =>
                        $quantidadesResultado[
                            'quantidade_devolvida'
                        ],

                    'quantidade_recusada' =>
                        $quantidadesResultado[
                            'quantidade_recusada'
                        ],

                    'quantidade_avariada' =>
                        $quantidadesResultado[
                            'quantidade_avariada'
                        ],

                    'quantidade_perdida' =>
                        $quantidadesResultado[
                            'quantidade_perdida'
                        ],
                ];
            }

            $statusAnterior = $romaneio->status;

            $this->salvarDadosOperacionais(
                $romaneio,
                [
                    'itens' =>
                        $itensConsolidados,

                    'retorno_conferido_por' =>
                        $usuarioId,
                ]
            );

            $romaneio->refresh();

            $romaneio->load([
                'itens.entregaItem',
            ]);

            /*
            * Consolida o resultado definitivo somente depois
            * que toda a triagem visual estiver concluída.
            */
            $this->atualizarResultadoFinalEntregas(
                $romaneio
            );

            /*
            * As ocorrências já foram criadas durante a triagem.
            * Neste ponto o fluxo apenas segue para a tratativa.
            */
            $romaneio->update([
                'status' =>
                    self::STATUS_AGUARDANDO_TRATATIVA_OCORRENCIA,

                'data_retorno' =>
                    $romaneio->data_retorno ?? now(),

                'retorno_registrado_por' =>
                    $romaneio->retorno_registrado_por
                    ?? $usuarioId,
            ]);

            $romaneio->refresh();

            $quantidadeOcorrencias =
                $ocorrencias->count();

            $quantidadeAfetada = round(
                (float) $ocorrencias
                    ->sum('quantidade_envolvida'),
                3
            );

            $observacaoEvento =
                $dados['observacao_retorno']
                ?? (
                    'Triagem do retorno concluída com '
                    . $quantidadeOcorrencias
                    . (
                        $quantidadeOcorrencias === 1
                            ? ' ocorrência material'
                            : ' ocorrências materiais'
                    )
                    . ' e '
                    . number_format(
                        $quantidadeAfetada,
                        3,
                        ',',
                        '.'
                    )
                    . ' unidade(s) afetada(s). '
                    . 'O romaneio foi encaminhado para tratativa.'
                );

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Conferência do retorno concluída',
                etapa: 'Conferencia_retorno',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status,
                observacao: $observacaoEvento
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function finalizarTriagemRetorno(
            Romaneio $romaneio): Romaneio {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' =>
                        'Não foi possível identificar o responsável pela conclusão do retorno.',
                ]);
            }

            $romaneio->load([
                'itens.entregaItem.vendaItem.lote',
                'itens.entregaItem.itemOrcamento',

                'ocorrencias' => fn ($query) =>
                    $query
                        ->whereNotIn(
                            'status',
                            [
                                'Cancelada',
                            ]
                        )
                        ->orderBy('id'),

                'ocorrencias.avaliacoes',
            ]);

            $ocorrencias = $romaneio->ocorrencias
                ->values();

            if ($ocorrencias->isEmpty()) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        'Nenhum produto com problema foi informado. Se a entrega ocorreu normalmente, utilize o fluxo de retorno normal.',
                ]);
            }

            $ocorrenciasPendentes = $ocorrencias
                ->filter(
                    fn ($ocorrencia) =>
                        $ocorrencia->triagem_status
                            !== 'Concluida'
                )
                ->values();

            if ($ocorrenciasPendentes->isNotEmpty()) {
                $quantidadePendente =
                    $ocorrenciasPendentes->count();

                throw ValidationException::withMessages([
                    'triagem' =>
                        'Ainda existem '
                        . $quantidadePendente
                        . (
                            $quantidadePendente === 1
                                ? ' produto com triagem pendente.'
                                : ' produtos com triagem pendente.'
                        ),
                ]);
            }

            $itensDoRomaneio = $romaneio->itens
                ->keyBy(
                    fn ($romaneioItem) =>
                        (int) $romaneioItem->id
                );

            $ocorrenciaSemItemValido = $ocorrencias
                ->first(function ($ocorrencia) use (
                    $itensDoRomaneio
                ) {
                    $romaneioItemId = (int) (
                        $ocorrencia->romaneio_item_id
                        ?? 0
                    );

                    return
                        $romaneioItemId <= 0
                        || ! $itensDoRomaneio->has(
                            $romaneioItemId
                        );
                });

            if ($ocorrenciaSemItemValido) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        "A ocorrência #{$ocorrenciaSemItemValido->id} não está vinculada a um item válido deste romaneio.",
                ]);
            }

            $ocorrenciasPorItem = $ocorrencias
                ->groupBy(
                    fn ($ocorrencia) =>
                        (int) $ocorrencia
                            ->romaneio_item_id
                );

            $itens = [];

            foreach (
                $romaneio->itens
                as $romaneioItem
            ) {
                $quantidadeSaida = round(
                    (float) (
                        $romaneioItem
                            ->quantidade_conferida_saida
                        ?? 0
                    ),
                    3
                );

                $ocorrenciasDoItem =
                    $ocorrenciasPorItem->get(
                        (int) $romaneioItem->id,
                        collect()
                    );

                $quantidades = [
                    'Devolucao' => 0.0,
                    'Recusa' => 0.0,
                    'Avaria' => 0.0,
                    'Extravio' => 0.0,
                ];

                $descricoes = [];

                $loteOrigemId =
                    $this->resolverLoteOrigemIdRetorno(
                        $romaneioItem
                    );

                foreach (
                    $ocorrenciasDoItem
                    as $ocorrencia
                ) {
                    $classificacao = trim(
                        (string) (
                            $ocorrencia
                                ->classificacao_final
                            ?: $ocorrencia
                                ->classificacao_inicial
                            ?? ''
                        )
                    );

                    if (! array_key_exists(
                        $classificacao,
                        $quantidades
                    )) {
                        throw ValidationException::withMessages([
                            'ocorrencias' =>
                                "A ocorrência #{$ocorrencia->id} possui uma classificação de retorno inválida.",
                        ]);
                    }

                    $quantidadeOcorrencia = round(
                        (float) (
                            $ocorrencia
                                ->quantidade_envolvida
                            ?? 0
                        ),
                        3
                    );

                    if ($quantidadeOcorrencia <= 0) {
                        throw ValidationException::withMessages([
                            'ocorrencias' =>
                                "A ocorrência #{$ocorrencia->id} não possui uma quantidade válida.",
                        ]);
                    }

                    if (in_array(
                        $classificacao,
                        [
                            'Devolucao',
                            'Recusa',
                            'Avaria',
                        ],
                        true
                    )) {
                        if ($ocorrencia->avaliacoes->isEmpty()) {
                            throw ValidationException::withMessages([
                                'triagem' =>
                                    "A ocorrência #{$ocorrencia->id} não possui avaliações físicas.",
                            ]);
                        }

                        if (! $loteOrigemId) {
                            throw ValidationException::withMessages([
                                'lote' =>
                                    "O item #{$romaneioItem->entrega_item_id} não possui um único lote original identificado. A consolidação foi bloqueada sem aplicar FIFO.",
                            ]);
                        }

                        $quantidadeAvaliada = round(
                            (float) $ocorrencia
                                ->avaliacoes
                                ->sum(
                                    fn ($avaliacao) =>
                                        (float) (
                                            $avaliacao
                                                ->quantidade
                                            ?? 0
                                        )
                                ),
                            3
                        );

                        if (
                            abs(
                                $quantidadeAvaliada
                                - $quantidadeOcorrencia
                            ) >= 0.001
                        ) {
                            throw ValidationException::withMessages([
                                'triagem' =>
                                    "A quantidade avaliada na ocorrência #{$ocorrencia->id} não corresponde à quantidade envolvida.",
                            ]);
                        }

                        $avaliacaoComLoteIncorreto =
                            $ocorrencia->avaliacoes
                                ->contains(
                                    fn ($avaliacao) =>
                                        (int) (
                                            $avaliacao->lote_id
                                            ?? 0
                                        )
                                        !== $loteOrigemId
                                );

                        if ($avaliacaoComLoteIncorreto) {
                            throw ValidationException::withMessages([
                                'lote' =>
                                    "A ocorrência #{$ocorrencia->id} não preservou o lote original utilizado na saída do item #{$romaneioItem->entrega_item_id}.",
                            ]);
                        }
                    }

                    $quantidades[$classificacao] =
                        round(
                            $quantidades[$classificacao]
                            + $quantidadeOcorrencia,
                            3
                        );

                    $descricao = trim(
                        (string) (
                            $ocorrencia->descricao
                            ?? ''
                        )
                    );

                    if ($descricao !== '') {
                        $descricoes[] =
                            $descricao;
                    }
                }

                $quantidadeComProblema = round(
                    array_sum($quantidades),
                    3
                );

                if (
                    $quantidadeComProblema
                    > $quantidadeSaida
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A soma das ocorrências do item #{$romaneioItem->entrega_item_id} ultrapassa a quantidade conferida na saída.",
                    ]);
                }

                $quantidadeEntregue = round(
                    $quantidadeSaida
                    - $quantidadeComProblema,
                    3
                );

                $observacao = trim(
                    implode(
                        ' | ',
                        array_unique(
                            $descricoes
                        )
                    )
                );

                if (
                    mb_strlen($observacao)
                    > 500
                ) {
                    $observacao = mb_substr(
                        $observacao,
                        0,
                        500
                    );
                }

                $itens[] = [
                    'romaneio_item_id' =>
                        $romaneioItem->id,

                    'entrega_item_id' =>
                        $romaneioItem
                            ->entrega_item_id,

                    'quantidade_entregue' =>
                        $quantidadeEntregue,

                    'quantidade_devolvida' =>
                        $quantidades['Devolucao'],

                    'quantidade_recusada' =>
                        $quantidades['Recusa'],

                    'quantidade_avariada' =>
                        $quantidades['Avaria'],

                    'quantidade_perdida' =>
                        $quantidades['Extravio'],

                    'observacao' =>
                        $observacao !== ''
                            ? $observacao
                            : null,
                ];
            }

            $statusAnterior =
                $romaneio->status;

            $this->salvarDadosOperacionais(
                $romaneio,
                [
                    'itens' =>
                        $itens,
                ]
            );

            $romaneio->refresh();

            $romaneio->load([
                'itens.entregaItem',
                'ocorrencias.avaliacoes',
            ]);

            $this->atualizarResultadoFinalEntregas(
                $romaneio
            );

            $romaneio->update([
                'status' =>
                    self::STATUS_AGUARDANDO_TRATATIVA_OCORRENCIA,

                'data_retorno' =>
                    $romaneio->data_retorno
                    ?? now(),

                'retorno_registrado_por' =>
                    $romaneio->retorno_registrado_por
                    ?? $usuarioId,
            ]);

            $romaneio->refresh();

            $quantidadeProdutos =
                $romaneio->itens->count();

            $quantidadeProdutosComProblema =
                $ocorrenciasPorItem->count();

            $quantidadeProdutosNormais =
                max(
                    0,
                    $quantidadeProdutos
                    - $quantidadeProdutosComProblema
                );

            $this->eventoService
                ->registrarTransicao(
                    romaneio: $romaneio,
                    evento:
                        'Triagem do retorno concluída',

                    etapa:
                        'Triagem_retorno',

                    statusAnterior:
                        $statusAnterior,

                    statusNovo:
                        $romaneio->status,

                    observacao:
                        $quantidadeProdutosComProblema
                        . (
                            $quantidadeProdutosComProblema
                                === 1
                                ? ' produto com ocorrência e '
                                : ' produtos com ocorrência e '
                        )
                        . $quantidadeProdutosNormais
                        . (
                            $quantidadeProdutosNormais
                                === 1
                                ? ' produto confirmado como entregue normalmente.'
                                : ' produtos confirmados como entregues normalmente.'
                        )
                        . ' As ocorrências permanecem abertas para tratativa administrativa, sem movimentação de estoque nesta etapa.'
                );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function resolverLoteOrigemIdRetorno(
            RomaneioItem $romaneioItem
        ): ?int {
            $entregaItem =
                $romaneioItem->entregaItem;

            $loteVendaId = (int) (
                $entregaItem
                    ?->vendaItem
                    ?->lote_id
                ?? $entregaItem
                    ?->vendaItem
                    ?->lote
                    ?->id
                ?? 0
            );

            if ($loteVendaId > 0) {
                return $loteVendaId;
            }

            $itemOrcamentoId = (int) (
                $entregaItem
                    ?->item_orcamento_id
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
            * Retorno não seleciona lote por FIFO.
            * O fallback do orçamento só é seguro quando existe
            * exatamente um lote original vinculado ao item.
            */
            if ($loteIds->count() !== 1) {
                return null;
            }

            return (int) $loteIds->first();
        }

        /**
         * Atualiza somente o resultado físico das entregas vinculadas
         * diretamente ao romaneio informado.
         *
         * Resultados de entregas filhas não são gravados nas entregas
         * anteriores. O acumulado da família fracionada deve ser usado
         * apenas para consulta e histórico, preservando o resultado de
         * cada documento e de cada viagem.
         */
        public function atualizarResultadoFinalEntregas(
            Romaneio $romaneio,
            bool $tratativaFinalizada = false
        ): void {
            $romaneio->loadMissing([
                'itens.entregaItem',
            ]);

            $entregasDiretasIds = $romaneio->itens
                ->map(
                    fn (RomaneioItem $romaneioItem) =>
                        (int) (
                            $romaneioItem
                                ->entregaItem
                                ?->entrega_id
                            ?? 0
                        )
                )
                ->filter(
                    fn (int $entregaId) =>
                        $entregaId > 0
                )
                ->unique()
                ->values();

            if ($entregasDiretasIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        'Não foi possível identificar as entregas vinculadas aos produtos do romaneio.',
                ]);
            }

            $entregasDiretas = Entrega::query()
                ->with('itens')
                ->whereIn(
                    'id',
                    $entregasDiretasIds->all()
                )
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if (
                $entregasDiretas->count()
                !== $entregasDiretasIds->count()
            ) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        'Uma ou mais entregas vinculadas ao romaneio não foram localizadas.',
                ]);
            }

            foreach ($entregasDiretas as $entrega) {
                if ($entrega->status === 'Cancelada') {
                    continue;
                }

                $entregaItensIds = $entrega->itens
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

                if ($entregaItensIds->isEmpty()) {
                    throw ValidationException::withMessages([
                        'entrega' =>
                            "A entrega #{$entrega->id} não possui produtos vinculados.",
                    ]);
                }

                $resultadosDiretos = DB::table(
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
                                self::STATUS_CANCELADO
                            );
                    })
                    ->where(function ($query) {
                        $query
                            ->whereNull('r.status')
                            ->orWhere(
                                'r.status',
                                '<>',
                                self::STATUS_CANCELADO
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
                    ->keyBy('entrega_item_id');

                $quantidadePrevistaTotal = 0.0;
                $quantidadeEntregueTotal = 0.0;
                $quantidadeNaoEntregueTotal = 0.0;
                $todosItensEntregues = true;

                foreach ($entrega->itens as $entregaItem) {
                    $resultadoDireto = $resultadosDiretos->get(
                        (int) $entregaItem->id
                    );

                    $quantidadePrevista = round(
                        (float) $entregaItem->quantidade_prevista,
                        3
                    );

                    $quantidadeEntregue = round(
                        (float) (
                            $resultadoDireto
                                ?->quantidade_entregue
                            ?? 0
                        ),
                        3
                    );

                    $quantidadeDevolvida = round(
                        (float) (
                            $resultadoDireto
                                ?->quantidade_devolvida
                            ?? 0
                        ),
                        3
                    );

                    $quantidadeRecusada = round(
                        (float) (
                            $resultadoDireto
                                ?->quantidade_recusada
                            ?? 0
                        ),
                        3
                    );

                    $quantidadeAvariada = round(
                        (float) (
                            $resultadoDireto
                                ?->quantidade_avariada
                            ?? 0
                        ),
                        3
                    );

                    $quantidadePerdida = round(
                        (float) (
                            $resultadoDireto
                                ?->quantidade_perdida
                            ?? 0
                        ),
                        3
                    );

                    $quantidadeNaoEntregue = round(
                        $quantidadeDevolvida
                        + $quantidadeRecusada
                        + $quantidadeAvariada
                        + $quantidadePerdida,
                        3
                    );

                    $quantidadeApurada = round(
                        $quantidadeEntregue
                        + $quantidadeNaoEntregue,
                        3
                    );

                    if (
                        $quantidadeApurada
                        > $quantidadePrevista + 0.001
                    ) {
                        throw ValidationException::withMessages([
                            'entrega_item' =>
                                "O resultado direto do item #{$entregaItem->id} ultrapassa a quantidade prevista da entrega #{$entrega->id}.",
                        ]);
                    }

                    $statusItem = match (true) {
                        $quantidadePrevista > 0
                            && abs(
                                $quantidadeEntregue
                                - $quantidadePrevista
                            ) < 0.001 =>
                                'Entregue',

                        $quantidadeEntregue > 0 =>
                            'Entregue_parcial',

                        $quantidadeNaoEntregue > 0 =>
                            'Devolvido',

                        default =>
                            'Pendente',
                    };

                    $entregaItem->update([
                        'quantidade_entregue' =>
                            $quantidadeEntregue,

                        'quantidade_devolvida' =>
                            $quantidadeDevolvida,

                        'quantidade_recusada' =>
                            $quantidadeRecusada,

                        'quantidade_avariada' =>
                            $quantidadeAvariada,

                        'status' =>
                            $statusItem,
                    ]);

                    $quantidadePrevistaTotal +=
                        $quantidadePrevista;

                    $quantidadeEntregueTotal +=
                        $quantidadeEntregue;

                    $quantidadeNaoEntregueTotal +=
                        $quantidadeNaoEntregue;

                    if (
                        abs(
                            $quantidadeEntregue
                            - $quantidadePrevista
                        ) >= 0.001
                    ) {
                        $todosItensEntregues = false;
                    }
                }

                $quantidadePrevistaTotal = round(
                    $quantidadePrevistaTotal,
                    3
                );

                $quantidadeEntregueTotal = round(
                    $quantidadeEntregueTotal,
                    3
                );

                $quantidadeNaoEntregueTotal = round(
                    $quantidadeNaoEntregueTotal,
                    3
                );

                $quantidadeApuradaTotal = round(
                    $quantidadeEntregueTotal
                    + $quantidadeNaoEntregueTotal,
                    3
                );

                $resultadoTotalApurado =
                    $quantidadePrevistaTotal > 0
                    && abs(
                        $quantidadeApuradaTotal
                        - $quantidadePrevistaTotal
                    ) < 0.001;

                $possuiOcorrencia =
                    $quantidadeNaoEntregueTotal > 0;

                $statusEntrega = match (true) {
                    $quantidadePrevistaTotal > 0
                        && $todosItensEntregues =>
                            'Entregue',

                    $tratativaFinalizada
                        && $resultadoTotalApurado
                        && $possuiOcorrencia =>
                            'Entregue_finalizada_com_ocorrencia',

                    $quantidadeEntregueTotal > 0 =>
                        'Entregue_parcial',

                    default =>
                        'Nao_entregue',
                };

                $entregaFinalizada = in_array(
                    $statusEntrega,
                    [
                        'Entregue',
                        'Entregue_parcial',
                        'Entregue_finalizada_com_ocorrencia',
                        'Nao_entregue',
                    ],
                    true
                );

                $entrega->update([
                    'status' =>
                        $statusEntrega,

                    'data_realizada' =>
                        $entregaFinalizada
                            ? (
                                $entrega->data_realizada
                                ?? now()
                            )
                            : null,
                ]);
            }
        }

        /**
         * @deprecated Mantido temporariamente para comparação durante
         * a homologação do histórico fracionado. Não deve ser chamado.
         */
        private function atualizarResultadoFinalEntregasConsolidadoLegado(
    Romaneio $romaneio,
    bool $tratativaFinalizada = false
): void {
    $buscarResultados = function (array $entregaItensIds) {
        if (empty($entregaItensIds)) {
            return collect();
        }

        return DB::table('romaneio_itens as ri')
            ->join(
                'romaneios as r',
                'r.id',
                '=',
                'ri.romaneio_id'
            )
            ->whereIn(
                'ri.entrega_item_id',
                $entregaItensIds
            )
            ->where(function ($query) {
                $query
                    ->whereNull('ri.status')
                    ->orWhere(
                        'ri.status',
                        '<>',
                        self::STATUS_CANCELADO
                    );
            })
            ->where(function ($query) {
                $query
                    ->whereNull('r.status')
                    ->orWhere(
                        'r.status',
                        '<>',
                        self::STATUS_CANCELADO
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
            ->keyBy('entrega_item_id');
    };

    $normalizarResultado = static function ($resultado): array {
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

        $naoEntregue = round(
            $devolvida
            + $recusada
            + $avariada
            + $perdida,
            3
        );

        return [
            'entregue' =>
                $entregue,

            'devolvida' =>
                $devolvida,

            'recusada' =>
                $recusada,

            'avariada' =>
                $avariada,

            'perdida' =>
                $perdida,

            'nao_entregue' =>
                $naoEntregue,

            'resultado' =>
                round(
                    $entregue
                    + $naoEntregue,
                    3
                ),
        ];
    };

    $resolverStatusItem = static function (
        float $quantidadePrevista,
        array $resultado
    ): string {
        return match (true) {
            $quantidadePrevista > 0
                && abs(
                    $resultado['entregue']
                    - $quantidadePrevista
                ) < 0.001 =>
                    'Entregue',

            $resultado['entregue'] > 0 =>
                'Entregue_parcial',

            $resultado['nao_entregue'] > 0 =>
                'Devolvido',

            default =>
                'Pendente',
        };
    };

    $romaneio->loadMissing([
        'itens.entregaItem',
    ]);

    $entregasDiretasIds = $romaneio->itens
        ->map(
            fn ($romaneioItem) =>
                (int) (
                    $romaneioItem
                        ->entregaItem
                        ?->entrega_id
                    ?? 0
                )
        )
        ->filter(
            fn ($entregaId) =>
                $entregaId > 0
        )
        ->unique()
        ->values();

    if ($entregasDiretasIds->isEmpty()) {
        throw ValidationException::withMessages([
            'entrega' =>
                'Não foi possível identificar as entregas vinculadas aos produtos do romaneio.',
        ]);
    }

    $entregasDiretas = Entrega::query()
        ->whereIn(
            'id',
            $entregasDiretasIds->all()
        )
        ->lockForUpdate()
        ->get([
            'id',
            'entrega_origem_id',
            'entrega_principal_id',
            'status',
        ])
        ->keyBy('id');

    if (
        $entregasDiretas->count()
        !== $entregasDiretasIds->count()
    ) {
        throw ValidationException::withMessages([
            'entrega' =>
                'Uma ou mais entregas vinculadas ao romaneio não foram localizadas.',
        ]);
    }

    $entregasProcessadas = [];

    foreach ($entregasDiretas as $entregaDireta) {
        if ($entregaDireta->status === 'Cancelada') {
            continue;
        }

        $entregaPrincipalId = (int) (
            $entregaDireta->entrega_principal_id
            ?: $entregaDireta->id
        );

        $entregasFamilia = Entrega::query()
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
            ->lockForUpdate()
            ->get([
                'id',
                'entrega_origem_id',
                'entrega_principal_id',
                'status',
            ])
            ->keyBy('id');

        if (
            ! $entregasFamilia->has(
                $entregaPrincipalId
            )
        ) {
            throw ValidationException::withMessages([
                'entrega' =>
                    "A entrega principal #{$entregaPrincipalId} não foi localizada.",
            ]);
        }

        if (
            $entregasFamilia
                ->get($entregaPrincipalId)
                ->status
            === 'Cancelada'
        ) {
            continue;
        }

        /*
        * Monta a cadeia completa:
        * entrega atual -> origem -> principal.
        */
        $entregasAlvoIds = collect();
        $entregaAtualId = (int) $entregaDireta->id;
        $entregasVisitadas = [];

        while ($entregaAtualId > 0) {
            if (
                isset(
                    $entregasVisitadas[
                        $entregaAtualId
                    ]
                )
            ) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "Foi identificado um ciclo na cadeia da entrega #{$entregaDireta->id}.",
                ]);
            }

            $entregasVisitadas[
                $entregaAtualId
            ] = true;

            $entregaAtual = $entregasFamilia->get(
                $entregaAtualId
            );

            if (! $entregaAtual) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A cadeia de origem da entrega #{$entregaDireta->id} está incompleta.",
                ]);
            }

            if ($entregaAtual->status === 'Cancelada') {
                break;
            }

            if (
                ! isset(
                    $entregasProcessadas[
                        $entregaAtualId
                    ]
                )
            ) {
                $entregasAlvoIds->push(
                    $entregaAtualId
                );
            }

            if (
                $entregaAtualId
                === $entregaPrincipalId
            ) {
                break;
            }

            $entregaAtualId = (int) (
                $entregaAtual->entrega_origem_id
                ?? 0
            );

            if ($entregaAtualId <= 0) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A entrega #{$entregaDireta->id} não possui uma cadeia válida até a entrega principal #{$entregaPrincipalId}.",
                ]);
            }
        }

        if ($entregasAlvoIds->isEmpty()) {
            continue;
        }

        $entregasFamiliaAtivas =
            $entregasFamilia->reject(
                fn ($entregaFamilia) =>
                    $entregaFamilia->status
                    === 'Cancelada'
            );

        $itensFamilia = EntregaItem::query()
            ->whereIn(
                'entrega_id',
                $entregasFamiliaAtivas
                    ->keys()
                    ->all()
            )
            ->lockForUpdate()
            ->get([
                'id',
                'entrega_id',
                'entrega_item_origem_id',
                'entrega_item_principal_id',
                'quantidade_prevista',
            ]);

        if ($itensFamilia->isEmpty()) {
            throw ValidationException::withMessages([
                'entrega' =>
                    "A família da entrega #{$entregaPrincipalId} não possui produtos vinculados.",
            ]);
        }

        $itensFamiliaPorId =
            $itensFamilia->keyBy('id');

        $resultadosFamilia = $buscarResultados(
            $itensFamilia
                ->pluck('id')
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->all()
        );

        /*
        * Verifica se uma entrega pertence ao ramo da entrega
        * que está sendo consolidada.
        */
        $pertenceAoEscopo = function (
            int $entregaCandidataId,
            int $entregaAlvoId
        ) use (
            $entregasFamilia
        ): bool {
            $entregaAtualId =
                $entregaCandidataId;

            $visitadas = [];

            while ($entregaAtualId > 0) {
                if (
                    isset(
                        $visitadas[
                            $entregaAtualId
                        ]
                    )
                ) {
                    throw ValidationException::withMessages([
                        'entrega' =>
                            "Foi identificado um ciclo na cadeia da entrega #{$entregaCandidataId}.",
                    ]);
                }

                $visitadas[
                    $entregaAtualId
                ] = true;

                $entregaAtual =
                    $entregasFamilia->get(
                        $entregaAtualId
                    );

                if (
                    ! $entregaAtual
                    || $entregaAtual->status
                        === 'Cancelada'
                ) {
                    return false;
                }

                if (
                    $entregaAtualId
                    === $entregaAlvoId
                ) {
                    return true;
                }

                $entregaAtualId = (int) (
                    $entregaAtual->entrega_origem_id
                    ?? 0
                );
            }

            return false;
        };

        /*
        * A ordem já está da entrega mais recente para a principal.
        * Assim cada origem intermediária também é recalculada.
        */
        foreach (
            $entregasAlvoIds
            as $entregaAlvoId
        ) {
            $entregaAlvo = Entrega::query()
                ->with('itens')
                ->lockForUpdate()
                ->find($entregaAlvoId);

            if (! $entregaAlvo) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A entrega #{$entregaAlvoId} não foi localizada durante a consolidação.",
                ]);
            }

            if ($entregaAlvo->status === 'Cancelada') {
                continue;
            }

            if ($entregaAlvo->itens->isEmpty()) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A entrega #{$entregaAlvoId} não possui produtos vinculados.",
                ]);
            }

            $itensAlvoIds = $entregaAlvo
                ->itens
                ->pluck('id')
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values();

            $resultadosPorItemAlvo =
                $itensAlvoIds->mapWithKeys(
                    fn ($itemId) => [
                        $itemId => [
                            'entregue' =>
                                0.0,

                            'devolvida' =>
                                0.0,

                            'recusada' =>
                                0.0,

                            'avariada' =>
                                0.0,

                            'perdida' =>
                                0.0,
                        ],
                    ]
                );

            $itensEscopo = $itensFamilia
                ->filter(
                    fn ($itemFamilia) =>
                        $pertenceAoEscopo(
                            (int) $itemFamilia
                                ->entrega_id,

                            (int) $entregaAlvoId
                        )
                );

            foreach (
                $itensEscopo
                as $itemEscopo
            ) {
                $itemAtual = $itemEscopo;
                $itensVisitados = [];
                $itemAlvoId = 0;

                /*
                * Percorre:
                * item atual -> item de origem -> item da entrega alvo.
                */
                while ($itemAtual) {
                    $itemAtualId =
                        (int) $itemAtual->id;

                    if (
                        isset(
                            $itensVisitados[
                                $itemAtualId
                            ]
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'entrega_item' =>
                                "Foi identificado um ciclo na cadeia do item #{$itemEscopo->id}.",
                        ]);
                    }

                    $itensVisitados[
                        $itemAtualId
                    ] = true;

                    if (
                        (int) $itemAtual->entrega_id
                        === (int) $entregaAlvoId
                    ) {
                        $itemAlvoId =
                            $itemAtualId;

                        break;
                    }

                    $itemOrigemId = (int) (
                        $itemAtual
                            ->entrega_item_origem_id
                        ?? 0
                    );

                    if ($itemOrigemId <= 0) {
                        /*
                        * Compatibilidade com fracionamentos que possuam
                        * apenas entrega_item_principal_id.
                        */
                        if (
                            (int) $entregaAlvoId
                            === $entregaPrincipalId
                        ) {
                            $itemPrincipalId = (int) (
                                $itemAtual
                                    ->entrega_item_principal_id
                                ?? 0
                            );

                            if (
                                $itensAlvoIds->contains(
                                    $itemPrincipalId
                                )
                            ) {
                                $itemAlvoId =
                                    $itemPrincipalId;

                                break;
                            }
                        }

                        throw ValidationException::withMessages([
                            'entrega_item' =>
                                "O item fracionado #{$itemEscopo->id} não possui uma cadeia válida até a entrega #{$entregaAlvoId}.",
                        ]);
                    }

                    $itemAtual =
                        $itensFamiliaPorId->get(
                            $itemOrigemId
                        );

                    if (! $itemAtual) {
                        throw ValidationException::withMessages([
                            'entrega_item' =>
                                "O item de origem #{$itemOrigemId} do item fracionado #{$itemEscopo->id} não foi localizado.",
                        ]);
                    }
                }

                if (
                    $itemAlvoId <= 0
                    || ! $resultadosPorItemAlvo
                        ->has($itemAlvoId)
                ) {
                    throw ValidationException::withMessages([
                        'entrega_item' =>
                            "O resultado do item #{$itemEscopo->id} não pôde ser associado à entrega #{$entregaAlvoId}.",
                    ]);
                }

                $resultadoEscopo =
                    $normalizarResultado(
                        $resultadosFamilia->get(
                            $itemEscopo->id
                        )
                    );

                $resultadoAlvo =
                    $resultadosPorItemAlvo->get(
                        $itemAlvoId
                    );

                foreach (
                    [
                        'entregue',
                        'devolvida',
                        'recusada',
                        'avariada',
                        'perdida',
                    ]
                    as $campo
                ) {
                    $resultadoAlvo[$campo] =
                        round(
                            $resultadoAlvo[$campo]
                            + $resultadoEscopo[$campo],
                            3
                        );
                }

                $resultadosPorItemAlvo->put(
                    $itemAlvoId,
                    $resultadoAlvo
                );
            }

            $quantidadePrevistaTotal = 0.0;
            $quantidadeEntregueTotal = 0.0;
            $quantidadeNaoEntregueTotal = 0.0;
            $todosItensEntregues = true;

            foreach (
                $entregaAlvo->itens
                as $itemAlvo
            ) {
                $quantidadePrevista = round(
                    (float) $itemAlvo
                        ->quantidade_prevista,
                    3
                );

                $resultado =
                    $resultadosPorItemAlvo->get(
                        (int) $itemAlvo->id
                    );

                $resultado['nao_entregue'] =
                    round(
                        $resultado['devolvida']
                        + $resultado['recusada']
                        + $resultado['avariada']
                        + $resultado['perdida'],
                        3
                    );

                $resultado['resultado'] =
                    round(
                        $resultado['entregue']
                        + $resultado['nao_entregue'],
                        3
                    );

                if (
                    $resultado['resultado']
                    > $quantidadePrevista + 0.001
                ) {
                    throw ValidationException::withMessages([
                        'entrega_item' =>
                            "O resultado consolidado do item #{$itemAlvo->id} ultrapassa a quantidade prevista da entrega #{$entregaAlvoId}.",
                    ]);
                }

                DB::table('entrega_itens')
                    ->where(
                        'id',
                        $itemAlvo->id
                    )
                    ->update([
                        'quantidade_entregue' =>
                            $resultado['entregue'],

                        'quantidade_devolvida' =>
                            $resultado['devolvida'],

                        'quantidade_recusada' =>
                            $resultado['recusada'],

                        'quantidade_avariada' =>
                            $resultado['avariada'],

                        'status' =>
                            $resolverStatusItem(
                                $quantidadePrevista,
                                $resultado
                            ),

                        'updated_at' =>
                            now(),
                    ]);

                $quantidadePrevistaTotal +=
                    $quantidadePrevista;

                $quantidadeEntregueTotal +=
                    $resultado['entregue'];

                $quantidadeNaoEntregueTotal +=
                    $resultado['nao_entregue'];

                if (
                    abs(
                        $resultado['entregue']
                        - $quantidadePrevista
                    ) >= 0.001
                ) {
                    $todosItensEntregues = false;
                }
            }

            $quantidadePrevistaTotal = round(
                $quantidadePrevistaTotal,
                3
            );

            $quantidadeEntregueTotal = round(
                $quantidadeEntregueTotal,
                3
            );

            $quantidadeNaoEntregueTotal = round(
                $quantidadeNaoEntregueTotal,
                3
            );

            $quantidadeResultadoTotal = round(
                $quantidadeEntregueTotal
                + $quantidadeNaoEntregueTotal,
                3
            );

            $resultadoTotalApurado =
                $quantidadePrevistaTotal > 0
                && abs(
                    $quantidadeResultadoTotal
                    - $quantidadePrevistaTotal
                ) < 0.001;

            $possuiOcorrencia =
                $quantidadeNaoEntregueTotal > 0;

            $entregasEscopo =
                $entregasFamiliaAtivas->filter(
                    fn ($entregaEscopo) =>
                        $pertenceAoEscopo(
                            (int) $entregaEscopo->id,
                            (int) $entregaAlvoId
                        )
                );

            $tratativaEscopoFinalizada =
                $tratativaFinalizada
                || $entregasEscopo->contains(
                    fn ($entregaEscopo) =>
                        $entregaEscopo->status
                        ===
                        'Entregue_finalizada_com_ocorrencia'
                );

            $statusEntrega = match (true) {
                $quantidadePrevistaTotal > 0
                    && $todosItensEntregues =>
                        'Entregue',

                $tratativaEscopoFinalizada
                    && $resultadoTotalApurado
                    && $possuiOcorrencia =>
                        'Entregue_finalizada_com_ocorrencia',

                $quantidadeEntregueTotal > 0 =>
                    'Entregue_parcial',

                default =>
                    'Nao_entregue',
            };

            $statusFinal = in_array(
                $statusEntrega,
                [
                    'Entregue',
                    'Entregue_finalizada_com_ocorrencia',
                    'Nao_entregue',
                ],
                true
            );

            $entregaAlvo->update([
                'status' =>
                    $statusEntrega,

                'data_realizada' =>
                    $statusFinal
                        ? (
                            $entregaAlvo
                                ->data_realizada
                            ?? now()
                        )
                        : null,
            ]);

            $entregasProcessadas[
                (int) $entregaAlvoId
            ] = true;

            if (
                $entregasFamilia->has(
                    $entregaAlvoId
                )
            ) {
                $entregasFamilia
                    ->get($entregaAlvoId)
                    ->status = $statusEntrega;
            }

            if (
                $entregasFamiliaAtivas->has(
                    $entregaAlvoId
                )
            ) {
                $entregasFamiliaAtivas
                    ->get($entregaAlvoId)
                    ->status = $statusEntrega;
            }
        }
    }
}

        private function iniciarPrestacaoContas(Romaneio $romaneio): Romaneio 
        {
            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' =>
                    self::STATUS_EM_PRESTACAO_CONTAS,
                'data_inicio_prestacao_contas' =>
                    $romaneio->data_inicio_prestacao_contas
                    ?? now(),
                'prestacao_contas_por' =>
                    Auth::id(),
            ]);

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Prestação de contas iniciada',
                etapa: 'Prestacao_contas',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function finalizarPrestacaoContas(Romaneio $romaneio, array $dados): Romaneio 
        {
            $romaneio->load('itens');

            $this->salvarDadosOperacionais(
                $romaneio,
                $dados
            );

            $romaneio->refresh();
            $romaneio->load('itens');

            $this->validarPrestacaoContas(
                $romaneio
            );

            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' =>
                    self::STATUS_AGUARDANDO_FECHAMENTO,
                'data_fim_prestacao_contas' =>
                    $romaneio->data_fim_prestacao_contas
                    ?? now(),
                'prestacao_contas_por' =>
                    Auth::id(),
            ]);

            $this->eventoService->registrarTransicao(
                romaneio: $romaneio,
                evento: 'Prestação de contas concluída',
                etapa: 'Prestacao_contas',
                statusAnterior: $statusAnterior,
                statusNovo: $romaneio->status
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function fecharRomaneio(Romaneio $romaneio, array $dados): Romaneio
        {
            $metodo = strtolower(
                trim(
                    (string) (
                        $dados['metodo_fechamento']
                        ?? 'pesquisa_manual'
                    )
                )
            );

            $metodosPermitidos = [
                'codigo_barras',
                'qr_code',
                'codigo_operacional',
                'pesquisa_manual',
            ];

            if (! in_array($metodo, $metodosPermitidos, true)) {
                throw ValidationException::withMessages([
                    'metodo_fechamento' =>
                        'O método de fechamento informado é inválido.',
                ]);
            }

            $justificativaManual = trim(
                (string) (
                    $dados['justificativa_fechamento_manual']
                    ?? ''
                )
            );

            if (
                $metodo === 'pesquisa_manual'
                && mb_strlen($justificativaManual) < 5
            ) {
                throw ValidationException::withMessages([
                    'justificativa_fechamento_manual' =>
                        'Informe a justificativa para o fechamento por pesquisa manual.',
                ]);
            }

            $romaneio->refresh();

            $romaneio->load([
                'itens',
                'ocorrencias',
            ]);

            if (! $romaneio->podeSerFechado()) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'O romaneio possui pendências que impedem o fechamento logístico.',
                ]);
            }

            $possuiOcorrencias =
                $romaneio->ocorrencias()
                    ->whereNotIn(
                        'status',
                        [
                            'Cancelada',
                        ]
                    )
                    ->exists();

            $statusFinal =
                $possuiOcorrencias
                    ? self::STATUS_FECHADO_COM_OCORRENCIA
                    : self::STATUS_FECHADO;

            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' =>
                    $statusFinal,

                'fechado_em' =>
                    now(),

                'fechado_por' =>
                    Auth::id(),

                'finalizado_por' =>
                    Auth::id(),

                'data_baixa' =>
                    now(),

                'metodo_fechamento' =>
                    $metodo,

                'justificativa_fechamento_manual' =>
                    $justificativaManual !== ''
                        ? $justificativaManual
                        : null,
            ]);

            $this->eventoService->registrarFechamento(
                $romaneio,
                $statusAnterior,
                $metodo,
                $justificativaManual !== ''
                    ? $justificativaManual
                    : null
            );

            if ($possuiOcorrencias) {
                $this->eventoService->registrarTransicao(
                    romaneio: $romaneio,
                    evento: 'Romaneio fechado com ocorrência',
                    etapa: 'Fechamento',
                    statusAnterior: $statusAnterior,
                    statusNovo: $romaneio->status,
                    observacao:
                        'O ciclo logístico foi encerrado, mas as ocorrências permanecem disponíveis para análise administrativa.'
                );
            }

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function salvarDadosOperacionais(Romaneio $romaneio, array $dados): void 
        {
            if (array_key_exists('observacao', $dados)) {
                $romaneio->update([
                    'observacao' =>
                        $dados['observacao'] ?: null,
                ]);
            }

            $romaneio->loadMissing('itens');

            $itensRecebidos = collect(
                $dados['itens'] ?? []
            );

            foreach ($romaneio->itens as $romaneioItem) {
                $dadosItem = $this->localizarDadosDoItem(
                    $itensRecebidos,
                    $romaneioItem
                );

                if (! is_array($dadosItem)) {
                    continue;
                }

                $atualizacao = [];

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_separada'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_conferida_separacao'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_carregada'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_conferida_saida'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_entregue'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_devolvida'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_recusada'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_avariada'
                );

                $this->preencherQuantidade(
                    $atualizacao,
                    $dadosItem,
                    'quantidade_perdida'
                );

                if (isset($dados['separado_por'])) {
                    $atualizacao['separado_por'] =
                        (int) $dados['separado_por'];

                    $atualizacao['separado_em'] =
                        $romaneioItem->separado_em ?? now();
                }

                if (isset($dados['conferencia_separacao_por'])) {
                    $atualizacao['conferencia_separacao_por'] =
                        (int) $dados['conferencia_separacao_por'];

                    $atualizacao['conferencia_separacao_em'] =
                        now();
                }

                if (isset($dados['carregado_por'])) {
                    $atualizacao['carregado_por'] =
                        (int) $dados['carregado_por'];

                    $atualizacao['carregado_em'] =
                        $romaneioItem->carregado_em ?? now();
                }

                if (isset($dados['conferencia_saida_por'])) {
                    $atualizacao['conferencia_saida_por'] =
                        (int) $dados['conferencia_saida_por'];

                    $atualizacao['conferencia_saida_em'] =
                        now();
                }

                if (isset($dados['retorno_conferido_por'])) {
                    $atualizacao['retorno_conferido_por'] =
                        (int) $dados['retorno_conferido_por'];

                    $atualizacao['retorno_conferido_em'] =
                        now();
                }

                if (
                    array_key_exists(
                        'observacao',
                        $dadosItem
                    )
                ) {
                    $atualizacao['observacao'] =
                        $dadosItem['observacao'] ?: null;
                }

                $atualizacao['status'] =
                    $this->resolverStatusItem(
                        $romaneioItem,
                        $atualizacao
                    );

                $romaneioItem->update(
                    $atualizacao
                );
            }
        }

        private function preencherQuantidade(array &$atualizacao, array $dadosItem, string $campo): void 
        {
            if (! array_key_exists($campo, $dadosItem)) {
                return;
            }

            $quantidade = round(
                (float) $dadosItem[$campo],
                2
            );

            if ($quantidade < 0) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "A quantidade informada em {$campo} não pode ser negativa.",
                ]);
            }

            $atualizacao[$campo] = $quantidade;
        }

        private function resolverStatusItem(RomaneioItem $item, array $atualizacao): string 
        {
            $dados = array_merge(
                $item->only([
                    'quantidade_prevista',
                    'quantidade_separada',
                    'quantidade_conferida_separacao',
                    'quantidade_carregada',
                    'quantidade_conferida_saida',
                    'quantidade_entregue',
                    'quantidade_devolvida',
                    'quantidade_recusada',
                    'quantidade_avariada',
                    'quantidade_perdida',
                ]),
                $atualizacao
            );

            $prevista = (float) $dados['quantidade_prevista'];
            $separada = (float) $dados['quantidade_separada'];
            $conferidaSeparacao =
                (float) $dados['quantidade_conferida_separacao'];
            $carregada =
                (float) $dados['quantidade_carregada'];
            $conferidaSaida =
                (float) $dados['quantidade_conferida_saida'];

            if ((float) $dados['quantidade_perdida'] > 0) {
                return 'Perdido';
            }

            if ((float) $dados['quantidade_avariada'] > 0) {
                return 'Avariado';
            }

            if ((float) $dados['quantidade_recusada'] > 0) {
                return 'Recusado';
            }

            if ((float) $dados['quantidade_devolvida'] > 0) {
                return 'Devolvido';
            }

            if ((float) $dados['quantidade_entregue'] > 0) {
                return (float) $dados['quantidade_entregue']
                    >= $carregada
                    ? 'Entregue'
                    : 'Entregue_parcial';
            }

            if (
                $conferidaSaida > 0
                && abs($conferidaSaida - $carregada) >= 0.001
            ) {
                return 'Divergente_saida';
            }

            if (
                $conferidaSaida > 0
                && abs($conferidaSaida - $carregada) < 0.001
            ) {
                return 'Saida_conferida';
            }

            if ($carregada > 0) {
                return 'Carregado';
            }

            if (
                $conferidaSeparacao > 0
                && abs($conferidaSeparacao - $separada) >= 0.001
            ) {
                return 'Divergente_separacao';
            }

            if (
                $conferidaSeparacao > 0
                && abs($conferidaSeparacao - $separada) < 0.001
            ) {
                return 'Separacao_conferida';
            }

            if ($separada >= $prevista && $prevista > 0) {
                return 'Separado';
            }

            if ($separada > 0) {
                return 'Separando';
            }

            return 'Pendente';
        }

        private function validarSeparacaoCompleta(Romaneio $romaneio): void 
        {
            $this->validarSeparacaoParaFinalizacao(
                $romaneio
            );
        }

        private function validarSeparacaoParaFinalizacao(Romaneio $romaneio): void 
        {
            $romaneio->loadMissing('itens');

            if ($romaneio->itens->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'O romaneio não possui itens para finalizar a separação.',
                ]);
            }

            $possuiQuantidadeSeparada = false;

            foreach ($romaneio->itens as $item) {
                $quantidadePrevista = round(
                    (float) $item->quantidade_prevista,
                    2
                );

                $quantidadeSeparada = round(
                    (float) $item->quantidade_separada,
                    2
                );

                if ($quantidadePrevista <= 0) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} possui quantidade prevista inválida.",
                    ]);
                }

                if ($quantidadeSeparada < 0) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} possui quantidade separada inválida.",
                    ]);
                }

                if (
                    $quantidadeSeparada
                    - $quantidadePrevista
                    >= 0.001
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade separada do item #{$item->entrega_item_id} não pode ser maior que a quantidade do romaneio.",
                    ]);
                }

                if ($quantidadeSeparada > 0) {
                    $possuiQuantidadeSeparada = true;
                }
            }

            if (! $possuiQuantidadeSeparada) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Informe pelo menos uma quantidade separada antes de finalizar a separação.',
                ]);
            }
        }

        private function processarSaldosSeparacao(    Romaneio $romaneio, array $dados): ?Romaneio 
        {
            $romaneio->loadMissing('itens');

            $itensComSaldo = $romaneio->itens
                ->filter(function (RomaneioItem $item) {
                    return $this->calcularSaldoSeparacao(
                        $item
                    ) > 0;
                })
                ->values();

            if ($itensComSaldo->isEmpty()) {
                foreach ($romaneio->itens as $item) {
                    $item->update([
                        'motivo_saldo' => null,
                        'destino_saldo' => null,
                        'data_prevista_saldo' => null,
                        'saldo_decidido_por' => null,
                        'observacao_saldo' => null,
                        'saldo_liberado_novo_romaneio' => false,
                        'status' => 'Separado',
                    ]);
                }

                return null;
            }

            $tipoSaldo = trim(
                (string) ($dados['tipo_saldo'] ?? '')
            );

            if (! in_array(
                $tipoSaldo,
                [
                    'Entrega_fracionada',
                    'Promessa_sem_estoque',
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'tipo_saldo' =>
                        'Escolha entre Entrega fracionada ou Promessa de entrega sem estoque.',
                ]);
            }

            $promessaSemEstoque =
                $tipoSaldo === 'Promessa_sem_estoque';

            $dataPrevistaSaldo =
                $dados['data_prevista_saldo'] ?? null;

            if (
                $promessaSemEstoque
                && empty($dataPrevistaSaldo)
            ) {
                throw ValidationException::withMessages([
                    'data_prevista_saldo' =>
                        'Informe a data prevista para a promessa de entrega.',
                ]);
            }

            $motivoSaldo = $promessaSemEstoque
                ? 'Falta_estoque'
                : 'Fracionamento_carga';

            $destinoSaldo = $promessaSemEstoque
                ? 'Aguardar_estoque'
                : 'Novo_romaneio';

            $saldoLiberado = ! $promessaSemEstoque;

            foreach ($romaneio->itens as $item) {
                $saldo = $this->calcularSaldoSeparacao(
                    $item
                );

                if ($saldo <= 0) {
                    $item->update([
                        'motivo_saldo' => null,
                        'destino_saldo' => null,
                        'data_prevista_saldo' => null,
                        'saldo_decidido_por' => null,
                        'observacao_saldo' => null,
                        'saldo_liberado_novo_romaneio' => false,
                        'status' => 'Separado',
                    ]);

                    continue;
                }

                $item->update([
                    'motivo_saldo' => $motivoSaldo,
                    'destino_saldo' => $destinoSaldo,
                    'data_prevista_saldo' =>
                        $promessaSemEstoque
                            ? $dataPrevistaSaldo
                            : null,

                    'saldo_decidido_por' => Auth::id(),

                    'observacao_saldo' =>
                        $dados['observacao_saldo'] ?? null,

                    'saldo_liberado_novo_romaneio' =>
                        $saldoLiberado,

                    'status' =>
                        'Divergente_separacao',
                ]);
            }

            return $this->criarRomaneioDoSaldo(
                $romaneio,
                $itensComSaldo,
                $dados,
                $promessaSemEstoque
            );
        }

        private function calcularSaldoSeparacao(RomaneioItem $item): float 
        {
            return max(
                round(
                    (float) $item->quantidade_prevista
                    - (float) $item->quantidade_separada,
                    2
                ),
                0
            );
        }

        private function criarRomaneioDoSaldo(
            Romaneio $romaneioOrigem,
            Collection $itensComSaldo,
            array $dados,
            bool $aguardandoEstoque
        ): Romaneio {
            $motoristaId = ! empty(
                $dados['proximo_motorista_id']
            )
                ? (int) $dados['proximo_motorista_id']
                : null;

            $veiculoId = ! empty(
                $dados['proximo_veiculo_id']
            )
                ? (int) $dados['proximo_veiculo_id']
                : null;

            $observacaoTipo = $aguardandoEstoque
                ? 'Romaneio gerado por promessa de entrega sem estoque.'
                : 'Romaneio gerado por fracionamento de carga.';

            $observacaoInformada = trim(
                (string) ($dados['observacao_saldo'] ?? '')
            );

            $observacao = $observacaoInformada !== ''
                ? $observacaoTipo
                    . PHP_EOL
                    . $observacaoInformada
                : $observacaoTipo;

            $motivoFracionamento = $aguardandoEstoque
                ? 'Falta_estoque'
                : 'Fracionamento_carga';

            foreach ($itensComSaldo as $itemOrigem) {
                $itemOrigem->loadMissing(
                    'entregaItem.entrega'
                );
            }

            $entregasOrigem = $itensComSaldo
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id')
                ->values();

            if ($entregasOrigem->count() !== 1) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        'O fracionamento deve gerar uma nova entrega para cada entrega de origem.',
                ]);
            }

            $entregaOrigem = $entregasOrigem->first();

            $itensEntregaFracionada = $itensComSaldo
                ->map(function (RomaneioItem $itemOrigem) {
                    return [
                        'entrega_item_origem_id' =>
                            $itemOrigem->entrega_item_id,

                        'quantidade' =>
                            $this->calcularSaldoSeparacao(
                                $itemOrigem
                            ),
                    ];
                })
                ->values()
                ->all();

            /*
            * A entrega física complementar é criada antes do romaneio.
            * Assim ela possui código, data, período, equipe e status próprios
            * e aparece normalmente no módulo de Entregas.
            */
            $entregaFilha = $this
                ->entregaService
                ->criarEntregaFracionada(
                    $entregaOrigem,
                    $itensEntregaFracionada,
                    [
                        'data_prevista' =>
                            $dados['data_prevista_saldo']
                            ?? null,

                        'data_prevista_entrega' =>
                            $dados['data_prevista_saldo']
                            ?? null,

                        'motorista_id' =>
                            $motoristaId,

                        'veiculo_id' =>
                            $veiculoId,

                        'observacao' =>
                            $observacaoInformada,
                    ]
                );

            $entregaFilha->load('itens');

            $itensEntregaDestinoPorOrigem = $entregaFilha
                ->itens
                ->keyBy('entrega_item_origem_id');

            /*
            * Uma finalização repetida da mesma separação não pode gerar
            * outro romaneio para o mesmo saldo. Isso pode acontecer quando
            * a operação retorna da conferência para a separação.
            */
            $romaneioFilho = $this
                ->localizarRomaneioFilhoAtivoDoSaldo(
                    $romaneioOrigem
            );

            if ($romaneioFilho) {
                if (
                    (int) $romaneioFilho->entrega_id
                    !== (int) $entregaFilha->id
                ) {
                    throw ValidationException::withMessages([
                        'fracionamento' =>
                            "O romaneio complementar {$romaneioFilho->codigo_romaneio} não está vinculado à entrega complementar {$entregaFilha->codigo_entrega}.",
                    ]);
                }

                $romaneioFilho->load(
                    'itens.entregaItem'
                );

                $itensDestinoPorOrigem = $romaneioFilho
                    ->itens
                    ->filter(
                        fn (RomaneioItem $item) =>
                            (int) $item->romaneio_item_origem_id > 0
                    )
                    ->keyBy('romaneio_item_origem_id');

                if (
                    $itensDestinoPorOrigem->count()
                    !== $itensComSaldo->count()
                ) {
                    throw ValidationException::withMessages([
                        'fracionamento' =>
                            "O romaneio complementar {$romaneioFilho->codigo_romaneio} já existe com uma composição diferente. Revise ou cancele esse romaneio antes de finalizar novamente a separação.",
                    ]);
                }

                foreach ($itensComSaldo as $itemOrigem) {
                    $saldo = $this->calcularSaldoSeparacao(
                        $itemOrigem
                    );

                    $itemDestino = $itensDestinoPorOrigem->get(
                        $itemOrigem->id
                    );

                    if (
                        ! $itemDestino
                        || (int) (
                            $itemDestino
                                ->entregaItem
                                ?->entrega_item_origem_id
                            ?? 0
                        )
                            !== (int) $itemOrigem->entrega_item_id
                        || abs(
                            (float) $itemDestino->quantidade_prevista
                            - $saldo
                        ) >= 0.001
                    ) {
                        throw ValidationException::withMessages([
                            'fracionamento' =>
                                "O saldo atual do item #{$itemOrigem->entrega_item_id} é diferente do saldo já encaminhado ao romaneio {$romaneioFilho->codigo_romaneio}. Revise ou cancele o romaneio complementar antes de finalizar novamente.",
                        ]);
                    }

                    $this->registrarEntregaFracionada(
                        $romaneioOrigem,
                        $romaneioFilho,
                        $itemOrigem,
                        $itemDestino,
                        $saldo,
                        $motivoFracionamento,
                        $observacaoInformada
                    );
                }

                return $romaneioFilho->load([
                    'itens',
                    'motorista',
                    'veiculo',
                ]);
            }

            $novoRomaneio = Romaneio::create([
                'entrega_id' =>
                    $entregaFilha->id,

                'romaneio_origem_id' =>
                    $romaneioOrigem->id,

                'criado_por' =>
                    Auth::id(),

                'codigo_romaneio' =>
                    $this->gerarCodigoRomaneio(),

                'token_abertura' =>
                    Str::random(64),

                'token_fechamento' =>
                    Str::random(64),

                'status' =>
                    self::STATUS_MONTAGEM,

                'motorista_id' =>
                    $motoristaId,

                'veiculo_id' =>
                    $veiculoId,

                'data_emissao' =>
                    now(),

                'data_prevista_saida' =>
                    $dados['data_prevista_saldo'] ?? null,

                'planejamento_confirmado' =>
                    false,

                'prioridade' =>
                    $romaneioOrigem->prioridade ?? 'Normal',

                'percentual_carregado' =>
                    0,

                'observacao' =>
                    $observacao,
            ]);

            foreach (
                $itensComSaldo->values()
                as $indice => $itemOrigem
            ) {
                $saldo = $this->calcularSaldoSeparacao(
                    $itemOrigem
                );

                if ($saldo <= 0) {
                    continue;
                }

                $entregaItemDestino =
                    $itensEntregaDestinoPorOrigem->get(
                        $itemOrigem->entrega_item_id
                    );

                if (! $entregaItemDestino) {
                    throw ValidationException::withMessages([
                        'fracionamento' =>
                            "Não foi possível localizar o item complementar do item #{$itemOrigem->entrega_item_id}.",
                    ]);
                }

                $itemDestino = RomaneioItem::create([
                    'romaneio_id' =>
                        $novoRomaneio->id,

                    'romaneio_item_origem_id' =>
                        $itemOrigem->id,

                    'entrega_item_id' =>
                        $entregaItemDestino->id,

                    'ordem' =>
                        $indice + 1,

                    'quantidade_prevista' =>
                        $saldo,

                    'quantidade_separada' =>
                        0,

                    'quantidade_conferida_separacao' =>
                        0,

                    'quantidade_conferida' =>
                        0,

                    'quantidade_carregada' =>
                        0,

                    'quantidade_conferida_saida' =>
                        0,

                    'quantidade_entregue' =>
                        0,

                    'quantidade_devolvida' =>
                        0,

                    'quantidade_recusada' =>
                        0,

                    'quantidade_avariada' =>
                        0,

                    'quantidade_perdida' =>
                        0,

                    'status' =>
                        'Pendente',
                ]);

                $this->registrarEntregaFracionada(
                    $romaneioOrigem,
                    $novoRomaneio,
                    $itemOrigem,
                    $itemDestino,
                    $saldo,
                    $motivoFracionamento,
                    $observacaoInformada
                );
            }

            $novoRomaneio->refresh();

            $this->eventoService->registrarCriacao(
                $novoRomaneio
            );

            return $novoRomaneio->load([
                'itens',
                'motorista',
                'veiculo',
            ]);
        }

        private function localizarRomaneioFilhoAtivoDoSaldo(
            Romaneio $romaneioOrigem
        ): ?Romaneio {
            $romaneiosFilhos = Romaneio::query()
                ->where(
                    'romaneio_origem_id',
                    $romaneioOrigem->id
                )
                ->where(
                    'status',
                    '<>',
                    self::STATUS_CANCELADO
                )
                ->lockForUpdate()
                ->get();

            if ($romaneiosFilhos->count() > 1) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        "O romaneio {$romaneioOrigem->codigo_romaneio} possui mais de um romaneio complementar ativo. Corrija a duplicidade antes de continuar.",
                ]);
            }

            return $romaneiosFilhos->first();
        }

        private function registrarEntregaFracionada(
            Romaneio $romaneioOrigem,
            Romaneio $romaneioDestino,
            RomaneioItem $itemOrigem,
            RomaneioItem $itemDestino,
            float $quantidade,
            string $motivo,
            string $observacao
        ): void {
            $itemOrigem->loadMissing('entregaItem');
            $itemDestino->loadMissing('entregaItem');

            $entregaItemOrigem =
                $itemOrigem->entregaItem;

            $entregaItemDestino =
                $itemDestino->entregaItem;

            $entregaId = (int) (
                $entregaItemOrigem?->entrega_id
                ?? 0
            );

            $entregaDestinoId = (int) (
                $entregaItemDestino?->entrega_id
                ?? 0
            );

            if (
                $entregaId <= 0
                || ! $entregaItemOrigem
            ) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        "Não foi possível identificar a entrega vinculada ao item #{$itemOrigem->entrega_item_id}.",
                ]);
            }

            if (
                $entregaDestinoId <= 0
                || ! $entregaItemDestino
            ) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        "Não foi possível identificar a entrega complementar vinculada ao item #{$itemDestino->entrega_item_id}.",
                ]);
            }

            if (
                (int) $romaneioOrigem->entrega_id
                    !== $entregaId
                || (int) $romaneioDestino->entrega_id
                    !== $entregaDestinoId
            ) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        'Os romaneios do fracionamento não correspondem às entregas de origem e destino dos itens.',
                ]);
            }

            if (
                (int) $entregaItemDestino
                    ->entrega_item_origem_id
                !== (int) $entregaItemOrigem->id
            ) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        "O item complementar #{$entregaItemDestino->id} não referencia o item de origem #{$entregaItemOrigem->id}.",
                ]);
            }

            $fracionamento = EntregaFracionamento::query()
                ->firstOrNew([
                    'romaneio_item_origem_id' =>
                        $itemOrigem->id,

                    'romaneio_item_destino_id' =>
                        $itemDestino->id,
                ]);

            if (
                $fracionamento->exists
                && ! empty($fracionamento->motivo)
                && $fracionamento->motivo !== $motivo
            ) {
                throw ValidationException::withMessages([
                    'fracionamento' =>
                        "O fracionamento do item #{$itemOrigem->entrega_item_id} já foi registrado com outro motivo.",
                ]);
            }

            $fracionamento->fill([
                'entrega_id' =>
                    $entregaId,

                'entrega_destino_id' =>
                    $entregaDestinoId,

                'entrega_item_origem_id' =>
                    $entregaItemOrigem->id,

                'entrega_item_destino_id' =>
                    $entregaItemDestino->id,

                'romaneio_origem_id' =>
                    $romaneioOrigem->id,

                'romaneio_destino_id' =>
                    $romaneioDestino->id,

                'romaneio_item_origem_id' =>
                    $itemOrigem->id,

                'romaneio_item_destino_id' =>
                    $itemDestino->id,

                'quantidade' =>
                    round($quantidade, 2),

                'motivo' =>
                    $motivo,

                'observacao' =>
                    $observacao !== ''
                        ? $observacao
                        : null,
            ]);

            if (! $fracionamento->exists) {
                $fracionamento->criado_por =
                    Auth::id();
            }

            $fracionamento->save();
        }

        private function validarConferenciaSeparacaoCompleta(
                Romaneio $romaneio
            ): void {
                $this->validarSeparacaoCompleta(
                    $romaneio
                );

            foreach ($romaneio->itens as $item) {
                if ($item->possuiDivergenciaSeparacao()) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} possui divergência na conferência da separação.",
                    ]);
                }
            }
        }

        private function validarCarregamentoCompleto(
            Romaneio $romaneio
            ): void {
            $this->validarConferenciaSeparacaoCompleta(
                $romaneio
            );

            foreach ($romaneio->itens as $item) {
                if (
                    abs(
                        (float) $item->quantidade_carregada
                        - (float) $item->quantidade_conferida_separacao
                    ) >= 0.001
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} ainda não foi totalmente carregado.",
                    ]);
                }
            }
        }

        private function validarConferenciaSaidaCompleta(
            Romaneio $romaneio
            ): void {
            $this->validarCarregamentoCompleto(
                $romaneio
            );

            foreach ($romaneio->itens as $item) {
                if ($item->possuiDivergenciaSaida()) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} possui divergência na conferência de saída.",
                    ]);
                }
            }
        }

        private function validarConferenciaRetornoCompleta(
            Romaneio $romaneio
            ): void {
            foreach ($romaneio->itens as $item) {
                if (empty($item->retorno_conferido_por)) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O retorno do item #{$item->entrega_item_id} ainda não foi conferido.",
                    ]);
                }
            }
        }

        private function validarPrestacaoContas(
            Romaneio $romaneio
            ): void {
            if ($romaneio->possuiOcorrenciaBloqueante()) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        'Existem ocorrências bloqueantes ainda abertas.',
                ]);
            }

            foreach ($romaneio->itens as $item) {
                if (! $item->prestacaoContasConciliada()) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "O item #{$item->entrega_item_id} não está conciliado na prestação de contas.",
                    ]);
                }
            }
        }
        private function validarLiberacao(Romaneio $romaneio): void 
        {
            $romaneio->loadMissing([
                'itens',
                'ocorrencias',
                'motorista',
                'veiculo',
            ]);

            /*
            * A carga precisa estar totalmente conferida
            * antes da liberação.
            */
            $this->validarConferenciaSaidaCompleta(
                $romaneio
            );

            /*
            * Motorista obrigatório somente na etapa final.
            */
            if (
                empty($romaneio->motorista_id)
                || ! $romaneio->motorista
            ) {
                throw ValidationException::withMessages([
                    'motorista_id' =>
                        'Selecione um motorista válido antes de liberar o veículo.',
                ]);
            }

            /*
            * O funcionário selecionado precisa estar ativo.
            */
            if (
                ! is_null($romaneio->motorista->ativo)
                && ! (bool) $romaneio->motorista->ativo
            ) {
                throw ValidationException::withMessages([
                    'motorista_id' =>
                        'O motorista selecionado está inativo.',
                ]);
            }

            /*
            * O funcionário precisa estar cadastrado como motorista.
            */
            if (
                strtolower(
                    trim(
                        (string) $romaneio
                            ->motorista
                            ->funcao
                    )
                ) !== 'motorista'
            ) {
                throw ValidationException::withMessages([
                    'motorista_id' =>
                        'O funcionário selecionado não está cadastrado como motorista.',
                ]);
            }

            /*
            * Veículo obrigatório somente na etapa final.
            */
            if (
                empty($romaneio->veiculo_id)
                || ! $romaneio->veiculo
            ) {
                throw ValidationException::withMessages([
                    'veiculo_id' =>
                        'Selecione um veículo válido antes da liberação.',
                ]);
            }

            /*
            * O veículo selecionado precisa estar ativo.
            */
            if (
                ! is_null($romaneio->veiculo->ativo)
                && ! (bool) $romaneio->veiculo->ativo
            ) {
                throw ValidationException::withMessages([
                    'veiculo_id' =>
                        'O veículo selecionado está inativo.',
                ]);
            }

            /*
            * O romaneio físico deve estar impresso antes
            * da liberação do caminhão.
            */
            if (empty($romaneio->impresso_em)) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Imprima o romaneio antes da liberação.',
                ]);
            }

            /*
            * Ocorrências bloqueantes precisam ser resolvidas
            * antes da liberação.
            */
            if ($romaneio->possuiOcorrenciaBloqueante()) {
                throw ValidationException::withMessages([
                    'ocorrencias' =>
                        'Existem ocorrências bloqueantes que impedem a liberação.',
                ]);
            }
        }

        private function validarAcaoPermitida(
                Romaneio $romaneio,
                string $acao
            ): void {
                $statusNormalizado = $this->normalizarStatus(
                    $romaneio->status
                );

            if ($acao === 'navegar_etapa') {
                if (in_array(
                    $statusNormalizado,
                    [
                        'em_rota',
                        'retornando',
                        'aguardando_conferencia_retorno',
                        'em_conferencia_retorno',
                        'aguardando_tratativa_ocorrencia',
                        'aguardando_prestacao_contas',
                        'em_prestacao_contas',
                        'aguardando_fechamento',
                        'fechado',
                        'fechado_com_ocorrencia',
                        'cancelado',
                    ],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'acao' =>
                            'A navegação manual não está disponível após a saída do veículo.',
                    ]);
                }

                return;
            }

            $acoesPermitidas = match ($romaneio->status) {
                self::STATUS_MONTAGEM => [
                    'concluir_montagem',
                ],

                self::STATUS_AGUARDANDO_SEPARACAO => [
                    'iniciar_separacao',
                ],

                self::STATUS_EM_SEPARACAO => [
                    'salvar_andamento',
                    'finalizar_separacao',
                ],

                self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO => [
                    'iniciar_conferencia_separacao',
                    'voltar_etapa',
                ],

                self::STATUS_EM_CONFERENCIA_SEPARACAO => [
                    'salvar_andamento',
                    'finalizar_conferencia_separacao',
                    'voltar_etapa',
                ],

                self::STATUS_AGUARDANDO_CARREGAMENTO => [
                    'iniciar_carregamento',
                    'voltar_etapa',
                ],

                self::STATUS_CARREGANDO => [
                    'salvar_andamento',
                    'finalizar_carregamento',
                    'voltar_etapa',
                ],

                self::STATUS_AGUARDANDO_CONFERENCIA_SAIDA => [
                    'iniciar_conferencia_saida',
                    'voltar_etapa',
                ],

                self::STATUS_EM_CONFERENCIA_SAIDA => [
                    'salvar_andamento',
                    'finalizar_conferencia_saida',
                    'voltar_etapa',
                ],

                self::STATUS_AGUARDANDO_LIBERACAO => [
                    'liberar_veiculo',
                    'voltar_etapa',
                ],

                self::STATUS_LIBERADO => [
                    'registrar_saida',
                    'voltar_etapa',
                ],

                self::STATUS_EM_ROTA,
                self::STATUS_RETORNANDO => [
                    'registrar_retorno',
                    'finalizar_triagem_retorno',
                ],

                self::STATUS_AGUARDANDO_CONFERENCIA_RETORNO => [
                    'iniciar_conferencia_retorno',
                ],

                self::STATUS_EM_CONFERENCIA_RETORNO => [
                    'salvar_andamento',
                    'finalizar_conferencia_retorno',
                ],

                self::STATUS_AGUARDANDO_TRATATIVA_OCORRENCIA => [
                    'iniciar_prestacao_contas',
                ],

                self::STATUS_AGUARDANDO_PRESTACAO_CONTAS => [
                    'iniciar_prestacao_contas',
                ],

                self::STATUS_EM_PRESTACAO_CONTAS => [
                    'salvar_andamento',
                    'finalizar_prestacao_contas',
                ],

                self::STATUS_AGUARDANDO_FECHAMENTO => [
                    'fechar_romaneio',
                ],

                self::STATUS_FECHADO,
                self::STATUS_FECHADO_COM_OCORRENCIA,
                self::STATUS_CANCELADO => [],

                default =>
                    throw ValidationException::withMessages([
                        'status' =>
                            "O status atual do romaneio ({$romaneio->status}) é inválido.",
                    ]),
            };

            if (! in_array(
                $acao,
                $acoesPermitidas,
                true
            )) {
                throw ValidationException::withMessages([
                    'acao' =>
                        "A ação {$acao} não é permitida para o status {$romaneio->status}.",
                ]);
            }

            if (
                $romaneio->status
                === self::STATUS_AGUARDANDO_TRATATIVA_OCORRENCIA
                && $acao === 'iniciar_prestacao_contas'
            ) {
                $possuiOcorrenciaBloqueadora =
                    $romaneio->ocorrencias()
                        ->whereNotIn(
                            'status',
                            [
                                'Resolvida',
                                'Cancelada',
                            ]
                        )
                        ->where(function ($query) {
                            $query
                                ->where(function ($query) {
                                    $query
                                        ->where(
                                            'exige_autorizacao',
                                            true
                                        )
                                        ->whereNull(
                                            'autorizada_por'
                                        );
                                })
                                ->orWhere(function ($query) {
                                    $query
                                        ->where(
                                            'bloqueia_operacao',
                                            true
                                        )
                                        ->where(function ($query) {
                                            $query
                                                ->where(
                                                    'permite_fechamento_logistico',
                                                    false
                                                )
                                                ->orWhereNull(
                                                    'permite_fechamento_logistico'
                                                );
                                        });
                                });
                        })
                        ->exists();

                if ($possuiOcorrenciaBloqueadora) {
                    throw ValidationException::withMessages([
                        'ocorrencias' =>
                            'O romaneio possui ocorrências pendentes. Registre as evidências e libere o fechamento logístico antes de iniciar a prestação de contas.',
                    ]);
                }
            }
        }

        private function retornarEtapaAnterior(Romaneio $romaneio, array $dados): Romaneio 
        {
            $motivo = trim(
                (string) (
                    $dados['motivo_retorno'] ?? ''
                )
            );

            if (mb_strlen($motivo) < 5) {
                throw ValidationException::withMessages([
                    'motivo_retorno' =>
                        'Informe o motivo do retorno da etapa.',
                ]);
            }

            [$statusNovo, $statusEntrega, $etapa] = match (
                $romaneio->status
            ) {
                self::STATUS_LIBERADO => [
                    self::STATUS_AGUARDANDO_LIBERACAO,
                    'Saida_conferida',
                    'Liberacao',
                ],

                self::STATUS_AGUARDANDO_LIBERACAO => [
                    self::STATUS_EM_CONFERENCIA_SAIDA,
                    'Material_carregado',
                    'Conferencia_saida',
                ],

                self::STATUS_AGUARDANDO_CONFERENCIA_SAIDA => [
                    self::STATUS_CARREGANDO,
                    'Separacao_conferida',
                    'Carregamento',
                ],

                self::STATUS_AGUARDANDO_CARREGAMENTO => [
                    self::STATUS_EM_CONFERENCIA_SEPARACAO,
                    'Material_separado',
                    'Conferencia_separacao',
                ],

                self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO => [
                    self::STATUS_EM_SEPARACAO,
                    'Aguardando_separacao',
                    'Separacao',
                ],

                default => throw ValidationException::withMessages([
                    'acao' =>
                        'O romaneio não pode retornar de etapa no status atual.',
                ]),
            };

            $statusAnterior = $romaneio->status;

            $romaneio->update([
                'status' => $statusNovo,
            ]);

            $this->atualizarStatusEntregas(
                $romaneio,
                $statusEntrega
            );

            $this->eventoService->registrarRetornoEtapa(
                $romaneio,
                $etapa,
                $statusAnterior,
                $statusNovo,
                $motivo
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        }

        private function navegarParaEtapa(Romaneio $romaneio, array $dados): Romaneio 
        {
            $etapaDestino = strtolower(
                trim(
                    (string) (
                        $dados['etapa_destino']
                        ?? ''
                    )
                )
            );

            $motivo = trim(
                (string) (
                    $dados['motivo_movimentacao']
                    ?? ''
                )
            );

            if (mb_strlen($motivo) < 5) {
                throw ValidationException::withMessages([
                    'motivo_movimentacao' =>
                        'Informe um motivo com pelo menos 5 caracteres.',
                ]);
            }

            $etapas = $this->mapaEtapasOperacionais();

            if (! isset($etapas[$etapaDestino])) {
                throw ValidationException::withMessages([
                    'etapa_destino' =>
                        'A etapa de destino informada é inválida.',
                ]);
            }

            $etapaAtual = $this->resolverEtapaOperacional(
                $romaneio
            );

            if (! isset($etapas[$etapaAtual])) {
                throw ValidationException::withMessages([
                    'etapa_atual' =>
                        'Não foi possível identificar a etapa atual do romaneio.',
                ]);
            }

            if ($etapaDestino === $etapaAtual) {
                throw ValidationException::withMessages([
                    'etapa_destino' =>
                        'O romaneio já está nesta etapa.',
                ]);
            }

            $ordemAtual =
                (int) $etapas[$etapaAtual]['ordem'];

            $ordemDestino =
                (int) $etapas[$etapaDestino]['ordem'];

            if ($ordemDestino > $ordemAtual) {
                throw ValidationException::withMessages([
                    'etapa_destino' =>
                        'Não é permitido avançar manualmente para uma etapa futura. Utilize a conclusão normal da operação.',
                ]);
            }

            $statusAnterior = $romaneio->status;

            $configuracaoDestino =
                $etapas[$etapaDestino];

            /*
            * Primeiro desfaz os dados operacionais posteriores
            * à etapa escolhida.
            */
            $this->prepararRetornoParaEtapa(
                $romaneio,
                $etapaDestino
            );

            /*
            * Depois sincroniza quantidades e situações
            * dos itens do romaneio.
            */
            $this->sincronizarItensParaEtapa(
                $romaneio,
                $etapaDestino
            );

            $romaneio->refresh();

            $romaneio->update([
                'status' =>
                    $configuracaoDestino['status_romaneio'],

                'observacao' =>
                    $this->adicionarHistoricoNaObservacao(
                        $romaneio->observacao,
                        sprintf(
                            'Navegação manual de %s para %s. Motivo: %s',
                            $etapas[$etapaAtual]['label'],
                            $configuracaoDestino['label'],
                            $motivo
                        )
                    ),
            ]);

            $this->atualizarStatusEntregas(
                $romaneio,
                $configuracaoDestino['status_entrega']
            );

            $romaneio->refresh();
            $romaneio->load('itens');

            /*
            * O percentual precisa ser recalculado depois
            * da limpeza das quantidades carregadas.
            */
            $this->atualizarPercentualCarregado(
                $romaneio
            );

            $romaneio->refresh();

            DB::table('romaneio_eventos')->insert([
                'romaneio_id' =>
                    $romaneio->id,

                'evento' =>
                    'Etapa alterada manualmente',

                'etapa' =>
                    $configuracaoDestino['label'],

                'status_anterior' =>
                    $statusAnterior,

                'status_novo' =>
                    $configuracaoDestino['status_romaneio'],

                'metodo_identificacao' =>
                    'Sistema',

                'usuario_id' =>
                    Auth::id(),

                'funcionario_id' =>
                    null,

                'terminal' =>
                    request()->userAgent(),

                'endereco_ip' =>
                    request()->ip(),

                'observacao' =>
                    $motivo,

                'dados' => json_encode(
                    [
                        'etapa_origem' =>
                            $etapaAtual,

                        'etapa_destino' =>
                            $etapaDestino,

                        'motivo' =>
                            $motivo,
                    ],
                    JSON_UNESCAPED_UNICODE
                ),

                'ocorrido_em' =>
                    now(),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

            return $this->carregarRomaneio(
                $romaneio->fresh()
            );
        }

        private function prepararRetornoParaEtapa(Romaneio $romaneio, string $etapaDestino): void 
        {
            $dados = match ($etapaDestino) {
                'montagem' => [
                    'data_inicio_separacao' =>
                        null,

                    'data_fim_separacao' =>
                        null,

                    'data_inicio_conferencia_separacao' =>
                        null,

                    'data_fim_conferencia_separacao' =>
                        null,

                    'data_inicio_carregamento' =>
                        null,

                    'data_fim_carregamento' =>
                        null,

                    'data_inicio_conferencia_saida' =>
                        null,

                    'data_fim_conferencia_saida' =>
                        null,

                    'data_saida' =>
                        null,

                    'iniciado_por' =>
                        null,

                    'separado_por' =>
                        null,

                    'conferencia_separacao_por' =>
                        null,

                    'carregado_por' =>
                        null,

                    'conferido_por' =>
                        null,

                    'conferencia_saida_por' =>
                        null,

                    'finalizado_por' =>
                        null,

                    'percentual_carregado' =>
                        0,
                ],

                'separacao' => [
                    'data_inicio_separacao' =>
                        now(),

                    'data_fim_separacao' =>
                        null,

                    'data_inicio_conferencia_separacao' =>
                        null,

                    'data_fim_conferencia_separacao' =>
                        null,

                    'data_inicio_carregamento' =>
                        null,

                    'data_fim_carregamento' =>
                        null,

                    'data_inicio_conferencia_saida' =>
                        null,

                    'data_fim_conferencia_saida' =>
                        null,

                    'data_saida' =>
                        null,

                    'conferencia_separacao_por' =>
                        null,

                    'carregado_por' =>
                        null,

                    'conferido_por' =>
                        null,

                    'conferencia_saida_por' =>
                        null,

                    'finalizado_por' =>
                        null,

                    'percentual_carregado' =>
                        0,
                ],

                'conferencia_separacao' => [
                    'data_inicio_conferencia_separacao' =>
                        now(),

                    'data_fim_conferencia_separacao' =>
                        null,

                    'data_inicio_carregamento' =>
                        null,

                    'data_fim_carregamento' =>
                        null,

                    'data_inicio_conferencia_saida' =>
                        null,

                    'data_fim_conferencia_saida' =>
                        null,

                    'data_saida' =>
                        null,

                    'carregado_por' =>
                        null,

                    'conferido_por' =>
                        null,

                    'conferencia_saida_por' =>
                        null,

                    'finalizado_por' =>
                        null,

                    'percentual_carregado' =>
                        0,
                ],

                'carregamento' => [
                    'data_inicio_carregamento' =>
                        now(),

                    'data_fim_carregamento' =>
                        null,

                    'data_inicio_conferencia_saida' =>
                        null,

                    'data_fim_conferencia_saida' =>
                        null,

                    'data_saida' =>
                        null,

                    'conferido_por' =>
                        null,

                    'conferencia_saida_por' =>
                        null,

                    'finalizado_por' =>
                        null,
                ],

                'conferencia_saida' => [
                    'data_inicio_conferencia_saida' =>
                        now(),

                    'data_fim_conferencia_saida' =>
                        null,

                    'data_saida' =>
                        null,

                    'conferido_por' =>
                        null,

                    'conferencia_saida_por' =>
                        null,

                    'finalizado_por' =>
                        null,
                ],

                'liberacao' => [
                    'data_saida' =>
                        null,

                    'finalizado_por' =>
                        null,
                ],

                default => [],
            };

            if (! empty($dados)) {
                $romaneio->update($dados);
            }
        }

        private function sincronizarItensParaEtapa(Romaneio $romaneio, string $etapaDestino): void 
        {
            $romaneio->loadMissing('itens');

            foreach ($romaneio->itens as $item) {
                $atualizacao = match ($etapaDestino) {
                    /*
                    * Retorno completo ao início da operação.
                    */
                    'montagem' => [
                        'quantidade_separada' =>
                            0,

                        'quantidade_conferida_separacao' =>
                            0,

                        'quantidade_conferida' =>
                            0,

                        'quantidade_carregada' =>
                            0,

                        'quantidade_conferida_saida' =>
                            0,

                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'carregado_por' =>
                            null,

                        'conferido_por' =>
                            null,

                        'conferido_em' =>
                            null,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    /*
                    * A separação já informada é preservada.
                    * As etapas posteriores são descartadas.
                    */
                    'separacao' => [
                        'quantidade_conferida_separacao' =>
                            0,

                        'quantidade_conferida' =>
                            0,

                        'quantidade_carregada' =>
                            0,

                        'quantidade_conferida_saida' =>
                            0,

                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'carregado_por' =>
                            null,

                        'conferido_por' =>
                            null,

                        'conferido_em' =>
                            null,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    /*
                    * Preserva a separação.
                    * Reinicia a conferência da separação
                    * e todas as etapas posteriores.
                    */
                    'conferencia_separacao' => [
                        'quantidade_conferida_separacao' =>
                            0,

                        'quantidade_conferida' =>
                            0,

                        'quantidade_carregada' =>
                            0,

                        'quantidade_conferida_saida' =>
                            0,

                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'carregado_por' =>
                            null,

                        'conferido_por' =>
                            null,

                        'conferido_em' =>
                            null,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    /*
                    * Preserva separação e conferência
                    * da separação.
                    */
                    'carregamento' => [
                        'quantidade_carregada' =>
                            0,

                        'quantidade_conferida_saida' =>
                            0,

                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'carregado_por' =>
                            null,

                        'conferido_por' =>
                            null,

                        'conferido_em' =>
                            null,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    /*
                    * Preserva o carregamento.
                    * Reinicia a conferência de saída
                    * e as etapas posteriores.
                    */
                    'conferencia_saida' => [
                        'quantidade_conferida_saida' =>
                            0,

                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'conferido_por' =>
                            null,

                        'conferido_em' =>
                            null,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    /*
                    * Preserva toda a operação até a
                    * conferência de saída e descarta
                    * qualquer movimentação posterior.
                    */
                    'liberacao' => [
                        'quantidade_entregue' =>
                            0,

                        'quantidade_devolvida' =>
                            0,

                        'quantidade_recusada' =>
                            0,

                        'quantidade_avariada' =>
                            0,

                        'quantidade_perdida' =>
                            0,

                        'retorno_conferido_por' =>
                            null,

                        'retorno_conferido_em' =>
                            null,
                    ],

                    default => [],
                };

                if (empty($atualizacao)) {
                    continue;
                }

                /*
                * O status é sempre derivado das quantidades.
                * Não deve ser atribuído manualmente.
                */
                $atualizacao['status'] =
                    $this->resolverStatusItem(
                        $item,
                        $atualizacao
                    );

                $item->update($atualizacao);
            }
        }

        private function mapaEtapasOperacionais(): array
        {
            return [
                'montagem' => [
                    'ordem' => 1,
                    'label' => 'Montagem',
                    'status_romaneio' => 'Montagem',
                    'status_entrega' => 'Aguardando_separacao',
                ],

                'separacao' => [
                    'ordem' => 2,
                    'label' => 'Separação',
                    'status_romaneio' => 'Em_separacao',
                    'status_entrega' => 'Aguardando_separacao',
                ],

                'conferencia_separacao' => [
                    'ordem' => 3,
                    'label' => 'Conferência da Separação',
                    'status_romaneio' => 'Em_conferencia_separacao',
                    'status_entrega' => 'Material_separado',
                ],

                'carregamento' => [
                    'ordem' => 4,
                    'label' => 'Carregamento',
                    'status_romaneio' => 'Carregando',
                    'status_entrega' => 'Separacao_conferida',
                ],

                'conferencia_saida' => [
                    'ordem' => 5,
                    'label' => 'Conferência de Saída',
                    'status_romaneio' => 'Em_conferencia_saida',
                    'status_entrega' => 'Material_carregado',
                ],

                'liberacao' => [
                    'ordem' => 6,
                    'label' => 'Liberação',
                    'status_romaneio' => 'Aguardando_liberacao',
                    'status_entrega' => 'Saida_conferida',
                ],

                'em_rota' => [
                    'ordem' => 7,
                    'label' => 'Em Rota',
                    'status_romaneio' => 'Em_rota',
                    'status_entrega' => 'Em_rota',
                ],
            ];
        }

        private function resolverEtapaOperacional(Romaneio $romaneio): string 
        {
            return match (
                $this->normalizarStatus(
                    $romaneio->status
                )
            ) {
                'montagem' =>
                    'montagem',

                'aguardando_separacao',
                'em_separacao' =>
                    'separacao',

                'aguardando_conferencia_separacao',
                'em_conferencia_separacao',
                'separacao_conferida' =>
                    'conferencia_separacao',

                'aguardando_carregamento',
                'carregando' =>
                    'carregamento',

                'aguardando_conferencia_saida',
                'em_conferencia_saida' =>
                    'conferencia_saida',

                'aguardando_liberacao',
                'liberado' =>
                    'liberacao',

                'em_rota' =>
                    'em_rota',

                default =>
                    '',
            };
        }

        private function adicionarHistoricoNaObservacao(
                ?string $observacaoAtual,
                string $registro
            ): string {
            $linha = sprintf(
                '[%s] %s',
                now()->format('d/m/Y H:i'),
                $registro
            );

            $observacaoAtual = trim(
                (string) $observacaoAtual
            );

            return $observacaoAtual !== ''
                ? $observacaoAtual . PHP_EOL . $linha
                : $linha;
        }

        public function cancelar(Romaneio $romaneio, string $motivo): void 
        {
            DB::transaction(function () use (
                $romaneio,
                $motivo
            ) {
                $romaneio = $this->bloquearRomaneio(
                    $romaneio->id
                );

                if (in_array(
                    $romaneio->status,
                    [
                        self::STATUS_EM_ROTA,
                        self::STATUS_RETORNANDO,
                        self::STATUS_AGUARDANDO_CONFERENCIA_RETORNO,
                        self::STATUS_EM_CONFERENCIA_RETORNO,
                        self::STATUS_AGUARDANDO_PRESTACAO_CONTAS,
                        self::STATUS_EM_PRESTACAO_CONTAS,
                        self::STATUS_AGUARDANDO_FECHAMENTO,
                        self::STATUS_FECHADO,
                        self::STATUS_CANCELADO,
                    ],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'romaneio' =>
                            'Este romaneio não pode mais ser cancelado.',
                    ]);
                }

                $motivo = trim($motivo);

                if (mb_strlen($motivo) < 5) {
                    throw ValidationException::withMessages([
                        'motivo_cancelamento' =>
                            'Informe um motivo válido para o cancelamento.',
                    ]);
                }

                $statusAnterior = $romaneio->status;

                $romaneio->update([
                    'status' => self::STATUS_CANCELADO,
                    'motivo_cancelamento' => $motivo,
                    'cancelado_em' => now(),
                    'cancelado_por' => Auth::id(),
                ]);

                $romaneio->itens()
                    ->update([
                        'status' => 'Cancelado',
                    ]);

                $this->atualizarStatusEntregas(
                    $romaneio,
                    'Aguardando_separacao'
                );

                $this->eventoService->registrarCancelamento(
                    $romaneio,
                    $statusAnterior,
                    $motivo
                );
            });
        }

        private function validarFuncionario(
            array $dados,
            string $campo,
            string $mensagem
            ): int {
            $funcionarioId = (int) (
                $dados[$campo] ?? 0
            );

            if ($funcionarioId <= 0) {
                throw ValidationException::withMessages([
                    $campo => $mensagem,
                ]);
            }

            return $funcionarioId;
        }

        private function buscarItensParaRomaneio(
                array $entregasIds,
                array $entregaItensIds
            ): EloquentCollection {
            $query = EntregaItem::query()
                ->with([
                    'entrega',
                    'produto',
                    'vendaItem.produto',
                    'itemOrcamento.produto',
                ])
                ->whereNotIn('status', [
                    'Cancelado',
                    'Entregue',
                    'Devolvido',
                ]);

            if (
                ! empty($entregasIds)
                && ! empty($entregaItensIds)
            ) {
                $query->where(function ($query) use (
                    $entregasIds,
                    $entregaItensIds
                ) {
                    $query
                        ->whereIn(
                            'entrega_id',
                            $entregasIds
                        )
                        ->orWhereIn(
                            'id',
                            $entregaItensIds
                        );
                });
            } elseif (! empty($entregaItensIds)) {
                $query->whereIn(
                    'id',
                    $entregaItensIds
                );
            } else {
                $query->whereIn(
                    'entrega_id',
                    $entregasIds
                );
            }

            return $query
                ->lockForUpdate()
                ->get();
        }

        private function validarEntregasDosItens(
                Collection $entregaItens
            ): void {
            $entregas = $entregaItens
                ->pluck('entrega')
                ->filter()
                ->unique('id');

            foreach ($entregas as $entrega) {
                if (! in_array(
                    $entrega->status,
                    [
                        'Aguardando_separacao',
                        'Em_preparacao',
                    ],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'entrega' =>
                            "A entrega #{$entrega->id} não está disponível para romaneio. Status atual: {$entrega->status}.",
                    ]);
                }
            }
        }

        private function prepararItensDoRomaneio(
                Collection $entregaItens,
                Collection $itensComQuantidade
            ): Collection {
            $quantidadesInformadas =
                $itensComQuantidade->keyBy(
                    'entrega_item_id'
                );

            return $entregaItens
                ->map(function (
                    EntregaItem $entregaItem
                ) use ($quantidadesInformadas) {
                    $quantidadeDisponivel = round(
                        (float) $entregaItem->quantidade_prevista
                        - (float) $entregaItem->quantidade_entregue,
                        2
                    );

                    if ($quantidadeDisponivel <= 0) {
                        return null;
                    }

                    $quantidadeInformada =
                        $quantidadesInformadas->get(
                            $entregaItem->id
                        );

                    $quantidade = $quantidadeInformada
                        ? round(
                            (float) $quantidadeInformada[
                                'quantidade'
                            ],
                            2
                        )
                        : $quantidadeDisponivel;

                    if ($quantidade > $quantidadeDisponivel) {
                        throw ValidationException::withMessages([
                            'itens' =>
                                "A quantidade do item #{$entregaItem->id} excede o saldo disponível.",
                        ]);
                    }

                    return [
                        'entrega_item' => $entregaItem,
                        'quantidade' => $quantidade,
                    ];
                })
                ->filter()
                ->values();
        }

        private function localizarDadosDoItem(
                Collection $itensRecebidos,
                RomaneioItem $romaneioItem
            ): ?array {
            $dadosItem = $itensRecebidos->first(
                function ($item) use ($romaneioItem) {
                    if (! is_array($item)) {
                        return false;
                    }

                    return (int) (
                        $item['romaneio_item_id'] ?? 0
                    ) === (int) $romaneioItem->id
                        || (int) (
                            $item['entrega_item_id'] ?? 0
                        ) ===
                        (int) $romaneioItem->entrega_item_id;
                }
            );

            return is_array($dadosItem)
                ? $dadosItem
                : null;
        }

        // private function atualizarStatusEntregas(
        //         Romaneio $romaneio,
        //         string $status
        //     ): void {
        //     $entregasIds = $romaneio->itens()
        //         ->with('entregaItem')
        //         ->get()
        //         ->pluck('entregaItem.entrega_id')
        //         ->filter()
        //         ->unique()
        //         ->values();

        //     Entrega::query()
        //         ->whereIn('id', $entregasIds)
        //         ->update([
        //             'status' => $status,
        //         ]);
        // }

        private function atualizarStatusEntregas(Romaneio $romaneio,string $status): void {
            $itens = $romaneio->itens()
                ->with('entregaItem')
                ->get();

            $entregasIds = $itens
                ->pluck('entregaItem.entrega_id')
                ->filter()
                ->unique()
                ->values();

            $campoOperador = match ($status) {
                'Material_separado' =>
                    'separado_por',

                'Separacao_conferida' =>
                    'conferencia_separacao_por',

                'Material_carregado' =>
                    'carregado_por',

                'Saida_conferida' =>
                    'conferencia_saida_por',

                default =>
                    null,
            };

            $operadorId = null;
            $operadorNome = null;

            if ($campoOperador !== null) {
                $operadoresIds = $itens
                    ->pluck($campoOperador)
                    ->filter()
                    ->unique()
                    ->values();

                if ($operadoresIds->count() === 1) {
                    $operadorId = (int) $operadoresIds->first();

                    $operadorNome = Funcionario::query()
                        ->whereKey($operadorId)
                        ->value('nome');
                }
            }

            DB::statement(
                'SET @entrega_usuario_id = ?',
                [Auth::id()]
            );

            DB::statement(
                'SET @entrega_operador_id = ?',
                [$operadorId]
            );

            DB::statement(
                'SET @entrega_operador_nome = ?',
                [$operadorNome]
            );

            try {
                Entrega::query()
                    ->whereIn('id', $entregasIds)
                    ->update([
                        'status' => $status,
                    ]);
            } finally {
                DB::statement(
                    'SET @entrega_usuario_id = NULL'
                );

                DB::statement(
                    'SET @entrega_operador_id = NULL'
                );

                DB::statement(
                    'SET @entrega_operador_nome = NULL'
                );
            }
        }

        private function atualizarPercentualCarregado(
                Romaneio $romaneio
            ): void {
            $romaneio->loadMissing('itens');

            $totalPrevisto = (float) $romaneio
                ->itens
                ->sum('quantidade_prevista');

            $totalCarregado = (float) $romaneio
                ->itens
                ->sum('quantidade_carregada');

            $percentual = $totalPrevisto > 0
                ? round(
                    ($totalCarregado / $totalPrevisto)
                    * 100,
                    2
                )
                : 0;

            $romaneio->update([
                'percentual_carregado' =>
                    min(100, max(0, $percentual)),
            ]);
        }

        private function bloquearRomaneio(
                int $romaneioId
            ): Romaneio {
            return Romaneio::query()
                ->with([
                    'itens',
                    'ocorrencias',
                ])
                ->lockForUpdate()
                ->findOrFail($romaneioId);
        }

        private function carregarRomaneio(
                Romaneio $romaneio
            ): Romaneio {
            $romaneio->refresh();

            return $romaneio->load([
                'motorista',
                'veiculo',
                'entrega',
                'itens.entregaItem',
                'ocorrencias',
                'eventos',
            ]);
        }

        private function gerarCodigoRomaneio(): string
        {
            do {
                $codigo = sprintf(
                    'ROM-%s-%04d',
                    now()->format('YmdHis'),
                    random_int(1, 9999)
                );
            } while (
                Romaneio::query()
                    ->where(
                        'codigo_romaneio',
                        $codigo
                    )
                    ->exists()
            );

            return $codigo;
        }

        private function normalizarStatus(?string $status): string 
        {
            return strtolower(
                trim(
                    str_replace(
                        ' ',
                        '_',
                        (string) $status
                    )
                )
            );
        }
    }