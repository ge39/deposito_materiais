<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\EntregaItem;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
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
    private const STATUS_AGUARDANDO_PRESTACAO_CONTAS = 'Aguardando_prestacao_contas';
    private const STATUS_EM_PRESTACAO_CONTAS = 'Em_prestacao_contas';
    private const STATUS_AGUARDANDO_FECHAMENTO = 'Aguardando_fechamento';
    private const STATUS_FECHADO = 'Fechado';
    private const STATUS_CANCELADO = 'Cancelado';

    public function __construct(
        private readonly RomaneioEventoService $eventoService
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

    public function atualizarOperacao(Romaneio $romaneio, string $acao, array $dados = [] ): Romaneio 
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

                'liberar_veiculo' =>
                    $this->liberarVeiculo(
                        $romaneio
                    ),

                'registrar_saida' =>
                    $this->registrarSaida(
                        $romaneio
                    ),

                'registrar_retorno' =>
                    $this->registrarRetorno(
                        $romaneio,
                        $dados
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

    private function salvarAndamento(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
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

    private function iniciarSeparacao(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_EM_SEPARACAO,
            'iniciado_por' =>
                $romaneio->iniciado_por ?? Auth::id(),
            'data_inicio_separacao' =>
                $romaneio->data_inicio_separacao ?? now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Em_preparacao'
        );

        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

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

    private function finalizarSeparacao(
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

        $this->validarSeparacaoCompleta(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO,
            'data_fim_separacao' =>
                $romaneio->data_fim_separacao ?? now(),
        ]);

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Separação finalizada',
            etapa: 'Separacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
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

    private function finalizarConferenciaSeparacao(
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
            'Pronta_para_carregamento'
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

        $this->atualizarStatusEntregas(
            $romaneio,
            'Pronta_para_carregamento'
        );

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
            'Carregada'
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

    private function liberarVeiculo(
        Romaneio $romaneio
    ): Romaneio {
        $this->validarLiberacao(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_LIBERADO,
            'finalizado_por' => Auth::id(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Liberada'
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Veículo liberado',
            etapa: 'Liberacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function registrarSaida(
        Romaneio $romaneio
    ): Romaneio {
        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_EM_ROTA,
            'data_saida' => now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Em_rota'
        );

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Saída do veículo registrada',
            etapa: 'Em_rota',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function registrarRetorno(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_CONFERENCIA_RETORNO,
            'data_retorno' => now(),
            'retorno_registrado_por' => Auth::id(),
        ]);

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Retorno do veículo registrado',
            etapa: 'Retorno',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            observacao: $dados['observacao_retorno']
                ?? null
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarConferenciaRetorno(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
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
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarConferenciaRetornoCompleta(
            $romaneio
        );

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' =>
                self::STATUS_AGUARDANDO_PRESTACAO_CONTAS,
        ]);

        $this->eventoService->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Conferência do retorno concluída',
            etapa: 'Conferencia_retorno',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarPrestacaoContas(
        Romaneio $romaneio
    ): Romaneio {
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

    private function finalizarPrestacaoContas(
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

    private function fecharRomaneio(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
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
                    'O romaneio possui pendências que impedem o fechamento.',
            ]);
        }

        $statusAnterior = $romaneio->status;

        $romaneio->update([
            'status' => self::STATUS_FECHADO,
            'fechado_em' => now(),
            'fechado_por' => Auth::id(),
            'finalizado_por' => Auth::id(),
            'data_baixa' => now(),
            'metodo_fechamento' => $metodo,
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

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function salvarDadosOperacionais(
        Romaneio $romaneio,
        array $dados
    ): void {
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

    private function preencherQuantidade(
        array &$atualizacao,
        array $dadosItem,
        string $campo
    ): void {
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

    private function resolverStatusItem(
        RomaneioItem $item,
        array $atualizacao
    ): string {
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

    private function validarSeparacaoCompleta(
        Romaneio $romaneio
    ): void {
        $romaneio->loadMissing('itens');

        foreach ($romaneio->itens as $item) {
            if (
                abs(
                    (float) $item->quantidade_prevista
                    - (float) $item->quantidade_separada
                ) >= 0.001
            ) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$item->entrega_item_id} ainda não foi totalmente separado.",
                ]);
            }
        }
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

    private function validarLiberacao(
        Romaneio $romaneio
    ): void {
        $this->validarConferenciaSaidaCompleta(
            $romaneio
        );

        if (! $romaneio->motorista_id) {
            throw ValidationException::withMessages([
                'motorista_id' =>
                    'Defina o motorista antes da liberação.',
            ]);
        }

        if (! $romaneio->veiculo_id) {
            throw ValidationException::withMessages([
                'veiculo_id' =>
                    'Defina o veículo antes da liberação.',
            ]);
        }

        if (! $romaneio->impresso_em) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'Imprima o romaneio antes da liberação.',
            ]);
        }

        if ($romaneio->possuiOcorrenciaBloqueante()) {
            throw ValidationException::withMessages([
                'ocorrencias' =>
                    'Existem ocorrências bloqueantes que impedem a liberação.',
            ]);
        }
    }

    private function validarAcaoPermitida(Romaneio $romaneio, string $acao): void 
    {
        if ($acao === 'navegar_etapa') {
            if (in_array(
                $this->normalizarStatus($romaneio->status),
                [
                    'em_rota',
                    'retornando',
                    'aguardando_conferencia_retorno',
                    'em_conferencia_retorno',
                    'aguardando_prestacao_contas',
                    'em_prestacao_contas',
                    'aguardando_fechamento',
                    'fechado',
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

            self::STATUS_EM_ROTA => [
                'registrar_retorno',
            ],

            self::STATUS_AGUARDANDO_CONFERENCIA_RETORNO => [
                'iniciar_conferencia_retorno',
            ],

            self::STATUS_EM_CONFERENCIA_RETORNO => [
                'salvar_andamento',
                'finalizar_conferencia_retorno',
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
            self::STATUS_CANCELADO => [],

            default => throw ValidationException::withMessages([
                'status' =>
                    "O status atual do romaneio ({$romaneio->status}) é inválido.",
            ]),
        };

        if (! in_array($acao, $acoesPermitidas, true)) {
            throw ValidationException::withMessages([
                'acao' =>
                    "A ação {$acao} não é permitida para o status {$romaneio->status}.",
            ]);
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
                'Carregada',
                'Liberacao',
            ],

            self::STATUS_AGUARDANDO_LIBERACAO => [
                self::STATUS_EM_CONFERENCIA_SAIDA,
                'Carregada',
                'Conferencia_saida',
            ],

            self::STATUS_AGUARDANDO_CONFERENCIA_SAIDA => [
                self::STATUS_CARREGANDO,
                'Pronta_para_carregamento',
                'Carregamento',
            ],

            self::STATUS_AGUARDANDO_CARREGAMENTO => [
                self::STATUS_EM_CONFERENCIA_SEPARACAO,
                'Em_preparacao',
                'Conferencia_separacao',
            ],

            self::STATUS_AGUARDANDO_CONFERENCIA_SEPARACAO => [
                self::STATUS_EM_SEPARACAO,
                'Em_preparacao',
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

        $ordemAtual = $etapas[$etapaAtual]['ordem'];
        $ordemDestino = $etapas[$etapaDestino]['ordem'];

        if ($ordemDestino > $ordemAtual) {
            throw ValidationException::withMessages([
                'etapa_destino' =>
                    'Não é permitido avançar manualmente para uma etapa futura. Utilize a conclusão normal da operação.',
            ]);
        }

        $statusAnterior = $romaneio->status;
        $configuracaoDestino = $etapas[$etapaDestino];

        $this->prepararRetornoParaEtapa(
            $romaneio,
            $etapaDestino
        );

        $romaneio->update([
            'status' => $configuracaoDestino['status_romaneio'],
            'observacao' => $this->adicionarHistoricoNaObservacao(
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

        DB::table('romaneio_eventos')->insert([
            'romaneio_id' => $romaneio->id,
            'evento' => 'Etapa alterada manualmente',
            'etapa' => $configuracaoDestino['label'],
            'status_anterior' => $statusAnterior,
            'status_novo' => $configuracaoDestino['status_romaneio'],
            'metodo_identificacao' => 'Sistema',
            'usuario_id' => Auth::id(),
            'funcionario_id' => null,
            'terminal' => request()->userAgent(),
            'endereco_ip' => request()->ip(),
            'observacao' => $motivo,
            'dados' => json_encode([
                'etapa_origem' => $etapaAtual,
                'etapa_destino' => $etapaDestino,
                'motivo' => $motivo,
            ], JSON_UNESCAPED_UNICODE),
            'ocorrido_em' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->carregarRomaneio(
            $romaneio->fresh()
        );
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
                'status_entrega' => 'Em_preparacao',
            ],

            'conferencia_separacao' => [
                'ordem' => 3,
                'label' => 'Conferência da Separação',
                'status_romaneio' => 'Em_conferencia_separacao',
                'status_entrega' => 'Em_preparacao',
            ],

            'carregamento' => [
                'ordem' => 4,
                'label' => 'Carregamento',
                'status_romaneio' => 'Carregando',
                'status_entrega' => 'Pronta_para_carregamento',
            ],

            'conferencia_saida' => [
                'ordem' => 5,
                'label' => 'Conferência de Saída',
                'status_romaneio' => 'Em_conferencia_saida',
                'status_entrega' => 'Carregada',
            ],

            'liberacao' => [
                'ordem' => 6,
                'label' => 'Liberação',
                'status_romaneio' => 'Aguardando_liberacao',
                'status_entrega' => 'Carregada',
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

    private function prepararRetornoParaEtapa(
        Romaneio $romaneio,
        string $etapaDestino
    ): void {
        $dados = match ($etapaDestino) {
            'montagem' => [
                'data_inicio_separacao' => null,
                'data_fim_separacao' => null,
                'data_inicio_conferencia_separacao' => null,
                'data_fim_conferencia_separacao' => null,
                'data_inicio_carregamento' => null,
                'data_fim_carregamento' => null,
                'data_inicio_conferencia_saida' => null,
                'data_fim_conferencia_saida' => null,
                'data_saida' => null,
            ],

            'separacao' => [
                'data_inicio_separacao' => now(),
                'data_fim_separacao' => null,
                'data_inicio_conferencia_separacao' => null,
                'data_fim_conferencia_separacao' => null,
                'data_inicio_carregamento' => null,
                'data_fim_carregamento' => null,
                'data_inicio_conferencia_saida' => null,
                'data_fim_conferencia_saida' => null,
                'data_saida' => null,
            ],

            'conferencia_separacao' => [
                'data_inicio_conferencia_separacao' => now(),
                'data_fim_conferencia_separacao' => null,
                'data_inicio_carregamento' => null,
                'data_fim_carregamento' => null,
                'data_inicio_conferencia_saida' => null,
                'data_fim_conferencia_saida' => null,
                'data_saida' => null,
            ],

            'carregamento' => [
                'data_inicio_carregamento' => now(),
                'data_fim_carregamento' => null,
                'data_inicio_conferencia_saida' => null,
                'data_fim_conferencia_saida' => null,
                'data_saida' => null,
            ],

            'conferencia_saida' => [
                'data_inicio_conferencia_saida' => now(),
                'data_fim_conferencia_saida' => null,
                'data_saida' => null,
            ],

            'liberacao' => [
                'data_saida' => null,
            ],

            default => [],
        };

        if (! empty($dados)) {
            $romaneio->update($dados);
        }
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

    private function atualizarStatusEntregas(
        Romaneio $romaneio,
        string $status
    ): void {
        $entregasIds = $romaneio->itens()
            ->with('entregaItem')
            ->get()
            ->pluck('entregaItem.entrega_id')
            ->filter()
            ->unique()
            ->values();

        Entrega::query()
            ->whereIn('id', $entregasIds)
            ->update([
                'status' => $status,
            ]);
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