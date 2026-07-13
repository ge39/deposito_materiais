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
    private const STATUS_GERADO = 'Gerado';
    private const STATUS_EM_SEPARACAO = 'Em_separacao';
    private const STATUS_SEPARADO = 'Separado';
    private const STATUS_NA_DOCA = 'Na_doca';
    private const STATUS_CARREGANDO = 'Carregando';
    private const STATUS_CARREGADO = 'Carregado';
    private const STATUS_CONFERIDO = 'Conferido';
    private const STATUS_LIBERADO = 'Liberado';
    private const STATUS_SAIU_PARA_ENTREGA = 'Saiu_para_entrega';
    private const STATUS_ENTREGUE = 'Entregue';
    private const STATUS_PARCIAL = 'Parcial';
    private const STATUS_DEVOLVIDO = 'Devolvido';
    private const STATUS_CANCELADO = 'Cancelado';

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
                        && isset($item['entrega_item_id'])
                        && (int) $item['entrega_item_id'] > 0
                        && (float) ($item['quantidade'] ?? 0) > 0;
                })
                ->map(function ($item) {
                    return [
                        'entrega_item_id' => (int) $item['entrega_item_id'],
                        'quantidade' => round(
                            (float) ($item['quantidade'] ?? 0),
                            2
                        ),
                    ];
                })
                ->values();

            if (
                isset($dados['entrega_id']) &&
                (int) $dados['entrega_id'] > 0
            ) {
                $entregasIds->push((int) $dados['entrega_id']);

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
                $entregasIds->isEmpty() &&
                $entregaItensIds->isEmpty()
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
                        'Não foi possível identificar a entrega principal do romaneio.',
                ]);
            }

            $romaneio = Romaneio::create([
                'entrega_id' => $entregaPrincipal->id,
                'codigo_romaneio' => $this->gerarCodigoRomaneio(),
                'token_abertura' => Str::random(64),
                'token_fechamento' => Str::random(64),
                'status' => self::STATUS_GERADO,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'motorista_id' => $dados['motorista_id'] ?? null,
                'criado_por' => Auth::id(),
                'data_emissao' => now(),
                'percentual_carregado' => 0,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            foreach ($itensPreparados as $itemPreparado) {
                RomaneioItem::create([
                    'romaneio_id' => $romaneio->id,
                    'entrega_item_id' =>
                        $itemPreparado['entrega_item']->id,
                    'quantidade_prevista' =>
                        $itemPreparado['quantidade'],
                    'quantidade_separada' => 0,
                    'quantidade_carregada' => 0,
                    'status' => 'Pendente',
                ]);
            }

            $this->atualizarStatusEntregas(
                $romaneio,
                'Aguardando_separacao'
            );

            return $this->carregarRomaneio(
                $romaneio
            );
        });
    }

    public function atualizarOperacao(
        Romaneio $romaneio,
        string $acao,
        array $dados = []
    ): Romaneio {
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

            if ($acao === 'voltar_etapa') {
                return $this->retornarEtapaAnterior(
                    $romaneio,
                    $dados
                );
            }

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

                'enviar_para_doca' =>
                    $this->enviarParaDoca(
                        $romaneio
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

                'concluir_conferencia' =>
                    $this->concluirConferencia(
                        $romaneio,
                        $dados
                    ),

                'liberar_veiculo' =>
                    $this->liberarRomaneio(
                        $romaneio
                    ),

                'registrar_saida' =>
                    $this->registrarSaida(
                        $romaneio
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
        $status = $this->normalizarStatus(
            $romaneio->status
        );

        if ($status === 'gerado') {
            $this->registrarInicioSeparacao(
                $romaneio
            );
        }

        if (in_array($status, [
            'separado',
            'na_doca',
        ], true)) {
            $this->registrarInicioCarregamento(
                $romaneio
            );
        }

        $romaneio->refresh();
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
        $this->registrarInicioSeparacao(
            $romaneio
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function finalizarSeparacao(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        if (
            $this->normalizarStatus($romaneio->status) ===
            'gerado'
        ) {
            $this->registrarInicioSeparacao(
                $romaneio
            );
        }

        $romaneio->refresh();
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

        $romaneio->update([
            'status' => self::STATUS_SEPARADO,
            'data_fim_separacao' =>
                $romaneio->data_fim_separacao ?? now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Aguardando_carregamento'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function enviarParaDoca(
        Romaneio $romaneio
    ): Romaneio {
        $this->validarSeparacaoCompleta(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_NA_DOCA,
        ]);

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function iniciarCarregamento(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $this->registrarInicioCarregamento(
            $romaneio
        );

        $romaneio->refresh();
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

    private function finalizarCarregamento(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        if (in_array(
            $this->normalizarStatus($romaneio->status),
            [
                'separado',
                'na_doca',
            ],
            true
        )) {
            $this->registrarInicioCarregamento(
                $romaneio
            );
        }

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->atualizarPercentualCarregado(
            $romaneio
        );

        $this->validarCarregamentoCompleto(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_CARREGADO,
            'data_fim_carregamento' =>
                $romaneio->data_fim_carregamento ?? now(),
            'percentual_carregado' => 100,
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Aguardando_conferencia'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function concluirConferencia(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
        $this->salvarDadosOperacionais(
            $romaneio,
            $dados
        );

        $romaneio->refresh();
        $romaneio->load('itens');

        $this->validarConferenciaCompleta(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_CONFERIDO,
            'conferido_por' => Auth::id(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Aguardando_liberacao'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function liberarRomaneio(
        Romaneio $romaneio
    ): Romaneio {
        $romaneio->load('itens');

        $this->validarLiberacao(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_LIBERADO,
            'finalizado_por' => Auth::id(),
        ]);

        /*
         * A liberação não representa a saída física.
         * A entrega permanece aguardando o registro da saída.
         */
        $this->atualizarStatusEntregas(
            $romaneio,
            'Aguardando_liberacao'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function registrarSaida(
        Romaneio $romaneio
    ): Romaneio {
        $romaneio->load('itens');

        $this->validarSaida(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_SAIU_PARA_ENTREGA,
            'data_saida' => now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Em_rota'
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    private function registrarInicioSeparacao(
        Romaneio $romaneio
    ): void {
        $romaneio->update([
            'status' => self::STATUS_EM_SEPARACAO,
            'iniciado_por' =>
                $romaneio->iniciado_por ?? Auth::id(),
            'data_inicio_separacao' =>
                $romaneio->data_inicio_separacao ?? now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Separando'
        );
    }

    private function registrarInicioCarregamento(
        Romaneio $romaneio
    ): void {
        $romaneio->loadMissing('itens');

        $this->validarSeparacaoCompleta(
            $romaneio
        );

        $romaneio->update([
            'status' => self::STATUS_CARREGANDO,
            'carregado_por' =>
                $romaneio->carregado_por ?? Auth::id(),
            'data_inicio_carregamento' =>
                $romaneio->data_inicio_carregamento ?? now(),
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            'Carregando'
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

        $etapaAtual = $this->resolverEtapaAtual(
            $romaneio
        );

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

            $prevista = round(
                (float) $romaneioItem->quantidade_prevista,
                2
            );

            $separadaAtual = round(
                (float) $romaneioItem->quantidade_separada,
                2
            );

            $carregadaAtual = round(
                (float) $romaneioItem->quantidade_carregada,
                2
            );

            $atualizacao = [];

            if ($etapaAtual === 'separacao') {
                $separada = round(
                    (float) (
                        $dadosItem['quantidade_separada']
                        ?? $separadaAtual
                    ),
                    2
                );

                if ($separada > $prevista) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade separada do item #{$romaneioItem->entrega_item_id} excede a quantidade prevista.",
                    ]);
                }

                $atualizacao['quantidade_separada'] =
                    $separada;

                $atualizacao['status'] = match (true) {
                    $separada <= 0 =>
                        'Pendente',

                    $separada < $prevista =>
                        'Parcial',

                    default =>
                        'Separado',
                };
            }

            if ($etapaAtual === 'carregamento') {
                $carregada = round(
                    (float) (
                        $dadosItem['quantidade_carregada']
                        ?? $carregadaAtual
                    ),
                    2
                );

                if ($carregada > $separadaAtual) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade carregada do item #{$romaneioItem->entrega_item_id} excede a quantidade separada.",
                    ]);
                }

                $atualizacao['quantidade_carregada'] =
                    $carregada;

                $atualizacao['carregado_por'] =
                    Auth::id();

                $atualizacao['status'] = match (true) {
                    $carregada <= 0 =>
                        'Pendente',

                    $carregada < $separadaAtual =>
                        'Parcial',

                    default =>
                        'Carregado',
                };
            }

            if ($etapaAtual === 'conferencia') {
                $statusInformado = strtolower(
                    trim(
                        (string) (
                            $dadosItem['status'] ?? ''
                        )
                    )
                );

                $statusConferencia = match ($statusInformado) {
                    'concluido',
                    'conferido',
                    'carregado' =>
                        'Conferido',

                    'divergente',
                    'parcial' =>
                        'Divergente',

                    default =>
                        $romaneioItem->status,
                };

                $atualizacao['status'] =
                    $statusConferencia;

                if ($statusConferencia === 'Conferido') {
                    $atualizacao['conferido_por'] =
                        Auth::id();

                    $atualizacao['conferido_em'] =
                        now();
                }
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

            if (! empty($atualizacao)) {
                $romaneioItem->update(
                    $atualizacao
                );
            }
        }
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

                $entregaItemId = (int) (
                    $item['entrega_item_id'] ?? 0
                );

                $romaneioItemId = (int) (
                    $item['romaneio_item_id'] ?? 0
                );

                return $entregaItemId ===
                        (int) $romaneioItem->entrega_item_id
                    || $romaneioItemId ===
                        (int) $romaneioItem->id;
            }
        );

        return is_array($dadosItem)
            ? $dadosItem
            : null;
    }

    private function validarSeparacaoCompleta(
        Romaneio $romaneio
    ): void {
        $romaneio->loadMissing('itens');

        if ($romaneio->itens->isEmpty()) {
            throw ValidationException::withMessages([
                'itens' =>
                    'O romaneio não possui itens.',
            ]);
        }

        foreach ($romaneio->itens as $item) {
            $prevista = round(
                (float) $item->quantidade_prevista,
                2
            );

            $separada = round(
                (float) $item->quantidade_separada,
                2
            );

            if ($separada !== $prevista) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$item->entrega_item_id} ainda não foi totalmente separado.",
                ]);
            }
        }
    }

    private function validarCarregamentoCompleto(
        Romaneio $romaneio
    ): void {
        $romaneio->loadMissing('itens');

        foreach ($romaneio->itens as $item) {
            $prevista = round(
                (float) $item->quantidade_prevista,
                2
            );

            $separada = round(
                (float) $item->quantidade_separada,
                2
            );

            $carregada = round(
                (float) $item->quantidade_carregada,
                2
            );

            if (
                $carregada !== $separada ||
                $carregada !== $prevista
            ) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$item->entrega_item_id} ainda não foi totalmente carregado.",
                ]);
            }
        }
    }

    private function validarConferenciaCompleta(
        Romaneio $romaneio
    ): void {
        $this->validarCarregamentoCompleto(
            $romaneio
        );

        foreach ($romaneio->itens as $item) {
            if (
                strtolower((string) $item->status) !==
                'conferido'
            ) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$item->entrega_item_id} ainda não foi conferido ou possui divergência.",
                ]);
            }
        }
    }

    private function validarLiberacao(
        Romaneio $romaneio
    ): void {
        $this->validarConferenciaCompleta(
            $romaneio
        );

        if (empty($romaneio->motorista_id)) {
            throw ValidationException::withMessages([
                'motorista_id' =>
                    'Defina o motorista antes da liberação.',
            ]);
        }

        if (empty($romaneio->veiculo_id)) {
            throw ValidationException::withMessages([
                'veiculo_id' =>
                    'Defina o veículo antes da liberação.',
            ]);
        }

        if (empty($romaneio->impresso_em)) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'Imprima o romaneio antes da liberação.',
            ]);
        }

        if (empty($romaneio->impresso_por)) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'Não foi possível identificar quem imprimiu o romaneio.',
            ]);
        }
    }

    private function validarSaida(
        Romaneio $romaneio
    ): void {
        if (
            $this->normalizarStatus($romaneio->status) !==
            'liberado'
        ) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'Somente um romaneio liberado pode registrar a saída.',
            ]);
        }

        if (empty($romaneio->finalizado_por)) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'O responsável pela liberação não foi registrado.',
            ]);
        }

        if (empty($romaneio->impresso_em)) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'O romaneio precisa estar impresso antes da saída.',
            ]);
        }
    }

    private function atualizarPercentualCarregado(
        Romaneio $romaneio
    ): void {
        $romaneio->loadMissing('itens');

        $totalPrevisto = round(
            (float) $romaneio->itens->sum(
                'quantidade_prevista'
            ),
            2
        );

        $totalCarregado = round(
            (float) $romaneio->itens->sum(
                'quantidade_carregada'
            ),
            2
        );

        $percentual = $totalPrevisto > 0
            ? round(
                ($totalCarregado / $totalPrevisto) * 100,
                2
            )
            : 0;

        $romaneio->update([
            'percentual_carregado' =>
                min(100, max(0, $percentual)),
        ]);
    }

    private function validarAcaoPermitida(
        Romaneio $romaneio,
        string $acao
    ): void {
        $status = $this->normalizarStatus(
            $romaneio->status
        );

        $acoesPermitidas = match ($status) {
            'gerado' => [
                'salvar_andamento',
                'iniciar_separacao',
                'finalizar_separacao',
            ],

            'em_separacao' => [
                'salvar_andamento',
                'finalizar_separacao',
            ],

            'separado' => [
                'enviar_para_doca',
                'iniciar_carregamento',
                'finalizar_carregamento',
                'voltar_etapa',
            ],

            'na_doca' => [
                'iniciar_carregamento',
                'finalizar_carregamento',
                'voltar_etapa',
            ],

            'carregando' => [
                'salvar_andamento',
                'finalizar_carregamento',
                'voltar_etapa',
            ],

            'carregado' => [
                'concluir_conferencia',
                'voltar_etapa',
            ],

            'conferido' => [
                'liberar_veiculo',
                'voltar_etapa',
            ],

            'liberado' => [
                'registrar_saida',
                'voltar_etapa',
            ],

            'saiu_para_entrega',
            'entregue',
            'parcial',
            'devolvido',
            'cancelado' => [],

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

    private function retornarEtapaAnterior(
        Romaneio $romaneio,
        array $dados
    ): Romaneio {
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

        $status = $this->normalizarStatus(
            $romaneio->status
        );

        [$statusRomaneio, $statusEntrega] = match ($status) {
            'liberado' => [
                self::STATUS_CONFERIDO,
                'Aguardando_liberacao',
            ],

            'conferido' => [
                self::STATUS_CARREGADO,
                'Aguardando_conferencia',
            ],

            'carregado' => [
                self::STATUS_CARREGANDO,
                'Carregando',
            ],

            'carregando' => [
                self::STATUS_NA_DOCA,
                'Aguardando_carregamento',
            ],

            'na_doca' => [
                self::STATUS_SEPARADO,
                'Aguardando_carregamento',
            ],

            'separado' => [
                self::STATUS_EM_SEPARACAO,
                'Separando',
            ],

            default => throw ValidationException::withMessages([
                'acao' =>
                    'O romaneio não pode retornar de etapa no status atual.',
            ]),
        };

        $registro = sprintf(
            '[%s] Retorno de etapa por usuário #%s. Motivo: %s',
            now()->format('d/m/Y H:i'),
            Auth::id() ?? 'sistema',
            $motivo
        );

        $observacaoAtual = trim(
            (string) $romaneio->observacao
        );

        $romaneio->update([
            'status' => $statusRomaneio,
            'observacao' => $observacaoAtual
                ? $observacaoAtual . PHP_EOL . $registro
                : $registro,
        ]);

        $this->atualizarStatusEntregas(
            $romaneio,
            $statusEntrega
        );

        return $this->carregarRomaneio(
            $romaneio
        );
    }

    public function cancelar(
        Romaneio $romaneio,
        string $motivo
    ): void {
        DB::transaction(function () use (
            $romaneio,
            $motivo
        ) {
            $romaneio = $this->bloquearRomaneio(
                $romaneio->id
            );

            $status = $this->normalizarStatus(
                $romaneio->status
            );

            if (in_array($status, [
                'liberado',
                'saiu_para_entrega',
                'entregue',
                'parcial',
                'devolvido',
                'cancelado',
            ], true)) {
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

            $romaneio->update([
                'status' => self::STATUS_CANCELADO,
                'motivo_cancelamento' => $motivo,
                'cancelado_em' => now(),
                'cancelado_por' => Auth::id(),
            ]);

            foreach ($romaneio->itens as $item) {
                $item->update([
                    'status' => 'Cancelado',
                ]);
            }

            $this->atualizarStatusEntregas(
                $romaneio,
                'Aguardando_separacao'
            );
        });
    }

    private function buscarItensParaRomaneio(
        array $entregasIds,
        array $entregaItensIds
    ): EloquentCollection {
        $entregasIds = collect($entregasIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $entregaItensIds = collect($entregaItensIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $query = EntregaItem::query()
            ->with([
                'entrega',
                'produto',
                'vendaItem.produto',
                'itemOrcamento.produto',
            ])
            ->whereNotIn('status', [
                'Cancelado',
                'cancelado',
                'Entregue',
                'entregue',
                'Devolvido',
                'devolvido',
            ]);

        if (
            $entregasIds->isNotEmpty() &&
            $entregaItensIds->isNotEmpty()
        ) {
            $query->where(function ($query) use (
                $entregasIds,
                $entregaItensIds
            ) {
                $query
                    ->whereIn(
                        'entrega_id',
                        $entregasIds->all()
                    )
                    ->orWhereIn(
                        'id',
                        $entregaItensIds->all()
                    );
            });
        } elseif ($entregaItensIds->isNotEmpty()) {
            $query->whereIn(
                'id',
                $entregaItensIds->all()
            );
        } else {
            $query->whereIn(
                'entrega_id',
                $entregasIds->all()
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

        if ($entregas->isEmpty()) {
            throw ValidationException::withMessages([
                'entrega' =>
                    'Os itens selecionados não possuem uma entrega válida.',
            ]);
        }

        foreach ($entregas as $entrega) {
            $status = $this->normalizarStatus(
                $entrega->status
            );

            if (! in_array($status, [
                'aguardando_separacao',
                'separando',
            ], true)) {
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
        $quantidadesInformadas = $itensComQuantidade
            ->keyBy('entrega_item_id');

        $entregaItensIds = $entregaItens
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $quantidadesJaAlocadas = RomaneioItem::query()
            ->whereIn(
                'entrega_item_id',
                $entregaItensIds->all()
            )
            ->whereHas(
                'romaneio',
                function ($query) {
                    $query->where(
                        'status',
                        '!=',
                        self::STATUS_CANCELADO
                    );
                }
            )
            ->selectRaw(
                'entrega_item_id, SUM(quantidade_prevista) AS total_alocado'
            )
            ->groupBy('entrega_item_id')
            ->lockForUpdate()
            ->pluck(
                'total_alocado',
                'entrega_item_id'
            );

        return $entregaItens
            ->map(function (
                EntregaItem $entregaItem
            ) use (
                $quantidadesInformadas,
                $quantidadesJaAlocadas
            ) {
                $quantidadePrevista = round(
                    (float) (
                        $entregaItem->quantidade_prevista
                        ?? 0
                    ),
                    2
                );

                $quantidadeJaAlocada = round(
                    (float) (
                        $quantidadesJaAlocadas[
                            $entregaItem->id
                        ] ?? 0
                    ),
                    2
                );

                $saldoDisponivel = round(
                    max(
                        0,
                        $quantidadePrevista -
                        $quantidadeJaAlocada
                    ),
                    2
                );

                if ($saldoDisponivel <= 0) {
                    return null;
                }

                $quantidadeInformada =
                    $quantidadesInformadas->get(
                        $entregaItem->id
                    );

                $quantidadeRomaneio =
                    $quantidadeInformada
                        ? round(
                            (float) $quantidadeInformada[
                                'quantidade'
                            ],
                            2
                        )
                        : $saldoDisponivel;

                if ($quantidadeRomaneio <= 0) {
                    return null;
                }

                if (
                    $quantidadeRomaneio >
                    $saldoDisponivel
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade informada para o item #{$entregaItem->id} excede o saldo disponível de " .
                            number_format(
                                $saldoDisponivel,
                                2,
                                ',',
                                '.'
                            ) .
                            '.',
                    ]);
                }

                return [
                    'entrega_item' => $entregaItem,
                    'quantidade' => $quantidadeRomaneio,
                ];
            })
            ->filter()
            ->values();
    }

    private function atualizarStatusEntregas(
        Romaneio $romaneio,
        string $status
    ): void {
        $romaneio->loadMissing([
            'entrega',
            'itens.entregaItem.entrega',
        ]);

        $entregas = $romaneio->itens
            ->pluck('entregaItem')
            ->filter()
            ->pluck('entrega')
            ->filter()
            ->push($romaneio->entrega)
            ->filter()
            ->unique('id');

        foreach ($entregas as $entrega) {
            $entrega->update([
                'status' => $status,
            ]);
        }
    }

    private function resolverEtapaAtual(
        Romaneio $romaneio
    ): string {
        return match (
            $this->normalizarStatus(
                $romaneio->status
            )
        ) {
            'gerado',
            'em_separacao' =>
                'separacao',

            'separado',
            'na_doca',
            'carregando' =>
                'carregamento',

            'carregado' =>
                'conferencia',

            'conferido',
            'liberado' =>
                'liberacao',

            'saiu_para_entrega',
            'entregue',
            'parcial',
            'devolvido',
            'cancelado' =>
                'finalizado',

            default => throw ValidationException::withMessages([
                'status' =>
                    "O status atual do romaneio ({$romaneio->status}) não corresponde a uma etapa válida.",
            ]),
        };
    }

    private function bloquearRomaneio(
        int $romaneioId
    ): Romaneio {
        return Romaneio::query()
            ->with([
                'entrega',
                'itens.entregaItem.entrega',
            ])
            ->lockForUpdate()
            ->findOrFail($romaneioId);
    }

    private function carregarRomaneio(
        Romaneio $romaneio
    ): Romaneio {
        return $romaneio->fresh([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.orcamento',
            'entrega.venda',
            'itens.entregaItem.entrega',
            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);
    }

    private function normalizarStatus(
        ?string $status
    ): string {
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

    private function gerarCodigoRomaneio(): string
    {
        $ultimoId = (int) Romaneio::max('id') + 1;

        return 'ROM-' .
            now()->format('Ymd') .
            '-' .
            str_pad(
                $ultimoId,
                5,
                '0',
                STR_PAD_LEFT
            );
    }
}