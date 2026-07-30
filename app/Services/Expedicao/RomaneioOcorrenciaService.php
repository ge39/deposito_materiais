<?php

namespace App\Services\Expedicao;

use App\Models\Romaneio;
use App\Models\RomaneioEquipe;
use App\Models\RomaneioItem;
use App\Models\RomaneioOcorrencia;
use App\Models\RomaneioOcorrenciaAnexo;
use App\Models\RomaneioOcorrenciaHistorico;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class RomaneioOcorrenciaService
{
    public function criarOcorrenciasDoRetorno(
            Romaneio $romaneio
        ): Collection {
        return DB::transaction(function () use ($romaneio) {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' =>
                        'Não foi possível identificar o usuário responsável pelo registro das ocorrências.',
                ]);
            }

            $romaneio->load([
                'entrega',
                'itens.entregaItem',
            ]);

            $equipe = RomaneioEquipe::query()
                ->where(
                    'romaneio_id',
                    $romaneio->id
                )
                ->where(
                    'status',
                    'Ativa'
                )
                ->latest('id')
                ->first();

            $ocorrenciasCriadas = collect();

            foreach ($romaneio->itens as $romaneioItem) {
                $resultados = $this->resultadosComOcorrencia(
                    $romaneioItem
                );

                foreach ($resultados as $resultado) {
                    $ocorrencia = RomaneioOcorrencia::query()
                        ->firstOrCreate(
                            [
                                'romaneio_id' =>
                                    $romaneio->id,

                                'romaneio_item_id' =>
                                    $romaneioItem->id,

                                'tipo' =>
                                    $resultado['tipo'],
                            ],
                            [
                                'romaneio_equipe_id' =>
                                    $equipe?->id,

                                'entrega_id' =>
                                    $romaneio->entrega_id,

                                'entrega_item_id' =>
                                    $romaneioItem
                                        ->entrega_item_id,

                                'quantidade_envolvida' =>
                                    $resultado['quantidade'],

                                'categoria' =>
                                    'Material',

                                'classificacao_inicial' =>
                                    $resultado['classificacao'],

                                'classificacao_final' =>
                                    null,

                                'criticidade' =>
                                    $resultado['criticidade'],

                                'etapa' =>
                                    'Retorno',

                                'descricao' =>
                                    $this->montarDescricao(
                                        $romaneioItem,
                                        $resultado
                                    ),

                                'bloqueia_operacao' =>
                                    true,

                                'exige_autorizacao' =>
                                    $resultado[
                                        'exige_autorizacao'
                                    ],

                                'status' =>
                                    'Aguardando_evidencias',

                                'registrada_por' =>
                                    $usuarioId,

                                'registrada_em' =>
                                    now(),

                                'destino_estoque' =>
                                    $resultado[
                                        'destino_estoque'
                                    ],

                                'permite_fechamento_logistico' =>
                                    false,
                            ]
                        );

                    if ($ocorrencia->wasRecentlyCreated) {
                        $this->registrarHistorico(
                            ocorrencia: $ocorrencia,
                            statusAnterior: null,
                            statusNovo:
                                $ocorrencia->status,
                            evento:
                                'Ocorrência criada no retorno',
                            descricao:
                                $ocorrencia->descricao
                        );
                    }

                    $ocorrenciasCriadas->push(
                        $ocorrencia
                    );
                }
            }

            if ($ocorrenciasCriadas->isEmpty()) {
                return $ocorrenciasCriadas;
            }

            $statusAnterior =
                $romaneio->status;

            $romaneio->update([
                'status' =>
                    'Aguardando_tratativa_ocorrencia',
            ]);

            return $ocorrenciasCriadas;
        });
    }

    public function atribuirResponsavel(
        RomaneioOcorrencia $ocorrencia,
        int $responsavelId
     ): RomaneioOcorrencia {
        return DB::transaction(
            function () use (
                $ocorrencia,
                $responsavelId
            ) {
                $usuarioId = Auth::id();

                if (! $usuarioId) {
                    throw ValidationException::withMessages([
                        'usuario' =>
                            'Não foi possível identificar o usuário responsável pela operação.',
                    ]);
                }

                $ocorrencia = RomaneioOcorrencia::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $ocorrencia->id
                    );

                if (
                    $ocorrencia->estaResolvida()
                    || $ocorrencia->estaCancelada()
                ) {
                    throw ValidationException::withMessages([
                        'ocorrencia' =>
                            'A ocorrência já está encerrada e não pode receber um novo responsável.',
                    ]);
                }

                $statusAnterior =
                    $ocorrencia->status;

                $possuiEvidencias =
                    $ocorrencia
                        ->anexos()
                        ->exists();

                $statusNovo = $possuiEvidencias
                    ? 'Em_analise'
                    : 'Aguardando_evidencias';

                $ocorrencia->update([
                    'assumida_por' =>
                        $usuarioId,

                    'assumida_em' =>
                        now(),

                    'responsavel_analise_id' =>
                        $responsavelId,

                    'analise_iniciada_em' =>
                        $possuiEvidencias
                            ? now()
                            : null,

                    'status' =>
                        $statusNovo,
                ]);

                $this->registrarHistorico(
                    ocorrencia: $ocorrencia,
                    statusAnterior: $statusAnterior,
                    statusNovo: $statusNovo,
                    evento:
                        'Responsável pela análise definido',
                    descricao:
                        "Responsável ID {$responsavelId} vinculado à ocorrência."
                );

                return $ocorrencia->fresh([
                    'responsavelAnalise',
                    'anexos',
                    'historicos',
                ]);
            }
        );
    }

    public function registrarAnexo(
        RomaneioOcorrencia $ocorrencia,
        array $dados
        ): RomaneioOcorrenciaAnexo {
        return DB::transaction(
            function () use (
                $ocorrencia,
                $dados
            ) {
                $usuarioId = Auth::id();

                if (! $usuarioId) {
                    throw ValidationException::withMessages([
                        'usuario' =>
                            'Não foi possível identificar o usuário responsável pelo anexo.',
                    ]);
                }

                $ocorrencia = RomaneioOcorrencia::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $ocorrencia->id
                    );

                if (
                    $ocorrencia->estaResolvida()
                    || $ocorrencia->estaCancelada()
                ) {
                    throw ValidationException::withMessages([
                        'ocorrencia' =>
                            'Não é possível anexar evidências a uma ocorrência encerrada.',
                    ]);
                }

                $anexo = $ocorrencia
                    ->anexos()
                    ->create([
                        'tipo' =>
                            $dados['tipo'],

                        'descricao' =>
                            $dados['descricao']
                            ?? null,

                        'nome_original' =>
                            $dados['nome_original'],

                        'caminho' =>
                            $dados['caminho'],

                        'mime_type' =>
                            $dados['mime_type']
                            ?? null,

                        'tamanho_bytes' =>
                            $dados['tamanho_bytes']
                            ?? null,

                        'capturado_em' =>
                            $dados['capturado_em']
                            ?? now(),

                        'hash_arquivo' =>
                            $dados['hash_arquivo']
                            ?? null,

                        'enviado_por' =>
                            $usuarioId,
                    ]);

                $statusAnterior =
                    $ocorrencia->status;

                $statusNovo = $ocorrencia
                    ->possuiResponsavel()
                        ? 'Em_analise'
                        : 'Aguardando_responsavel';

                $ocorrencia->update([
                    'status' =>
                        $statusNovo,

                    'analise_iniciada_em' =>
                        $ocorrencia
                            ->possuiResponsavel()
                                ? (
                                    $ocorrencia
                                        ->analise_iniciada_em
                                    ?? now()
                                )
                                : null,
                ]);

                $this->registrarHistorico(
                    ocorrencia: $ocorrencia,
                    statusAnterior: $statusAnterior,
                    statusNovo: $statusNovo,
                    evento:
                        'Evidência anexada',
                    descricao:
                        $dados['descricao']
                        ?? $dados['nome_original']
                );

                return $anexo;
            }
        );
    }

    public function liberarFechamentoLogistico(
        RomaneioOcorrencia $ocorrencia
        ): RomaneioOcorrencia {
        return DB::transaction(
            function () use ($ocorrencia) {
                $ocorrencia = RomaneioOcorrencia::query()
                    ->with([
                        'anexos',
                        'responsavelAnalise',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $ocorrencia->id
                    );

                if (! $ocorrencia->possuiResponsavel()) {
                    throw ValidationException::withMessages([
                        'responsavel_analise_id' =>
                            'Defina o responsável pela análise antes de liberar o fechamento logístico.',
                    ]);
                }

                if (! $ocorrencia->possuiEvidencias()) {
                    throw ValidationException::withMessages([
                        'anexos' =>
                            'Registre pelo menos uma evidência antes de liberar o fechamento logístico.',
                    ]);
                }

                if (
                    $ocorrencia
                        ->exigeAutorizacaoPendente()
                ) {
                    throw ValidationException::withMessages([
                        'autorizacao' =>
                            'A ocorrência possui uma autorização pendente.',
                    ]);
                }

                $statusAnterior =
                    $ocorrencia->status;

                $ocorrencia->update([
                    'permite_fechamento_logistico' =>
                        true,

                    'bloqueia_operacao' =>
                        false,
                ]);

                $this->registrarHistorico(
                    ocorrencia: $ocorrencia,
                    statusAnterior: $statusAnterior,
                    statusNovo:
                        $ocorrencia->status,
                    evento:
                        'Fechamento logístico liberado',
                    descricao:
                        'A ocorrência possui responsável e evidências registradas.'
                );

                return $ocorrencia->fresh([
                    'anexos',
                    'responsavelAnalise',
                    'historicos',
                ]);
            }
        );
    }

    public function removerAnexo(RomaneioOcorrencia $ocorrencia, RomaneioOcorrenciaAnexo $anexo): void
    {
        $caminho = DB::transaction(function () use ($ocorrencia, $anexo) {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' => 'Não foi possível identificar o usuário responsável pela remoção.',
                ]);
            }

            $ocorrencia = RomaneioOcorrencia::query()
                ->lockForUpdate()
                ->findOrFail($ocorrencia->id);

            $anexo = RomaneioOcorrenciaAnexo::query()
                ->lockForUpdate()
                ->findOrFail($anexo->id);

            if ((int) $anexo->romaneio_ocorrencia_id !== (int) $ocorrencia->id) {
                throw ValidationException::withMessages([
                    'anexo' => 'A evidência não pertence à ocorrência informada.',
                ]);
            }

            if ($ocorrencia->estaResolvida() || $ocorrencia->estaCancelada()) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'Não é possível remover evidências de uma ocorrência encerrada.',
                ]);
            }

            $statusAnterior = $ocorrencia->status;
            $nomeOriginal = $anexo->nome_original;
            $caminho = $anexo->caminho;

            $anexo->delete();

            $possuiOutrasEvidencias = $ocorrencia
                ->anexos()
                ->exists();

            if (! $possuiOutrasEvidencias) {
                $ocorrencia->update([
                    'status' => 'Aguardando_evidencias',
                    'analise_iniciada_em' => null,
                    'permite_fechamento_logistico' => false,
                    'bloqueia_operacao' => true,
                ]);
            }

            $this->registrarHistorico(
                ocorrencia: $ocorrencia,
                statusAnterior: $statusAnterior,
                statusNovo: $ocorrencia->status,
                evento: 'Evidência removida',
                descricao: $nomeOriginal
            );

            return $caminho;
        });

        $caminhoAbsoluto = str_starts_with((string) $caminho, 'uploads/')
            || str_starts_with((string) $caminho, 'image/')
                ? public_path($caminho)
                : storage_path('app/public/' . ltrim((string) $caminho, '/'));

        if (File::exists($caminhoAbsoluto)) {
            File::delete($caminhoAbsoluto);
        }
    }

    public function autorizar(RomaneioOcorrencia $ocorrencia, string $justificativa): RomaneioOcorrencia
    {
        return DB::transaction(function () use ($ocorrencia, $justificativa) {
            $usuarioId = Auth::id();
            $justificativa = trim($justificativa);

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' => 'Não foi possível identificar o usuário responsável pela autorização.',
                ]);
            }

            if (mb_strlen($justificativa) < 5) {
                throw ValidationException::withMessages([
                    'justificativa_autorizacao' => 'Informe uma justificativa válida para a autorização.',
                ]);
            }

            $ocorrencia = RomaneioOcorrencia::query()
                ->lockForUpdate()
                ->findOrFail($ocorrencia->id);

            if ($ocorrencia->estaResolvida() || $ocorrencia->estaCancelada()) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'A ocorrência já está encerrada e não pode ser autorizada.',
                ]);
            }

            if (! $ocorrencia->exige_autorizacao) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'Esta ocorrência não exige autorização.',
                ]);
            }

            if ($ocorrencia->autorizada_por) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'Esta ocorrência já foi autorizada.',
                ]);
            }

            $statusAnterior = $ocorrencia->status;

            $ocorrencia->update([
                'autorizada_por' => $usuarioId,
                'autorizada_em' => now(),
                'justificativa_autorizacao' => $justificativa,
            ]);

            $this->registrarHistorico(
                ocorrencia: $ocorrencia,
                statusAnterior: $statusAnterior,
                statusNovo: $ocorrencia->status,
                evento: 'Ocorrência autorizada',
                descricao: $justificativa
            );

            return $ocorrencia->fresh([
                'autorizador',
                'responsavelAnalise',
                'anexos',
                'historicos',
            ]);
        });
    }

    public function registrarDecisao(RomaneioOcorrencia $ocorrencia, array $dados): RomaneioOcorrencia
    {
        return DB::transaction(function () use ($ocorrencia, $dados) {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' => 'Não foi possível identificar o usuário responsável pela decisão.',
                ]);
            }

            $classificacaoFinal = (string) ($dados['classificacao_final'] ?? '');
            $decisao = trim((string) ($dados['decisao'] ?? ''));
            $destinoEstoque = (string) ($dados['destino_estoque'] ?? 'Sem_movimentacao');
            $orcamentoReposicaoId = $dados['orcamento_reposicao_id'] ?? null;

            $classificacoesPermitidas = [
                'Extravio',
                'Avaria',
                'Recusa',
                'Devolucao',
                'Perda',
                'Divergencia',
                'Improcedente',
                'Outro',
            ];

            $destinosPermitidos = [
                'Sem_movimentacao',
                'Quarentena',
                'Reintegracao',
                'Perda',
                'Reposicao',
                'Tratamento_individual',
            ];

            if (! in_array($classificacaoFinal, $classificacoesPermitidas, true)) {
                throw ValidationException::withMessages([
                    'classificacao_final' => 'A classificação final informada é inválida.',
                ]);
            }

            if (! in_array($destinoEstoque, $destinosPermitidos, true)) {
                throw ValidationException::withMessages([
                    'destino_estoque' => 'O destino de estoque informado é inválido.',
                ]);
            }

            if (mb_strlen($decisao) < 5) {
                throw ValidationException::withMessages([
                    'decisao' => 'Descreva a decisão administrativa da ocorrência.',
                ]);
            }

            $ocorrencia = RomaneioOcorrencia::query()
                ->with([
                    'anexos',
                    'responsavelAnalise',
                    'avaliacoes',
                ])
                ->lockForUpdate()
                ->findOrFail($ocorrencia->id);

            /*
             * Quando a triagem detalhada existe, seus destinos são a
             * fonte oficial. O operador não pode reduzi-los manualmente
             * a um único destino no cabeçalho da ocorrência.
             */
            if (
                $ocorrencia->triagem_status === 'Concluida'
                && $ocorrencia->avaliacoes->isNotEmpty()
            ) {
                $destinosTriagem = $ocorrencia->avaliacoes
                    ->pluck('destino_sugerido')
                    ->filter()
                    ->unique()
                    ->values();

                $destinoEstoque =
                    $destinosTriagem->count() === 1
                        ? (string) $destinosTriagem->first()
                        : 'Tratamento_individual';
            }

            if ($ocorrencia->estaResolvida() || $ocorrencia->estaCancelada()) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'A ocorrência já está encerrada e não pode receber uma decisão.',
                ]);
            }

            if (! $ocorrencia->possuiResponsavel()) {
                throw ValidationException::withMessages([
                    'responsavel_analise_id' => 'Defina o responsável pela análise antes de registrar a decisão.',
                ]);
            }

            if (! $ocorrencia->possuiEvidencias()) {
                throw ValidationException::withMessages([
                    'anexos' => 'Registre pelo menos uma evidência antes da decisão.',
                ]);
            }

            if ($ocorrencia->exigeAutorizacaoPendente()) {
                throw ValidationException::withMessages([
                    'autorizacao' => 'Autorize a ocorrência antes de registrar a decisão.',
                ]);
            }

            $statusAnterior = $ocorrencia->status;
            $statusNovo = match ($classificacaoFinal) {
                'Extravio', 'Perda' => 'Extravio_confirmado',
                'Devolucao', 'Recusa' => 'Aprovada_devolucao',
                'Avaria' => $orcamentoReposicaoId ? 'Aprovada_troca' : 'Aguardando_decisao',
                'Improcedente' => 'Improcedente',
                default => 'Aguardando_decisao',
            };

            $ocorrencia->update([
                'classificacao_final' => $classificacaoFinal,
                'decidida_por' => $usuarioId,
                'decidida_em' => now(),
                'decisao' => $decisao,
                'destino_estoque' => $destinoEstoque,
                'orcamento_reposicao_id' => $orcamentoReposicaoId ?: null,
                'status' => $statusNovo,
            ]);

            $this->registrarHistorico(
                ocorrencia: $ocorrencia,
                statusAnterior: $statusAnterior,
                statusNovo: $statusNovo,
                evento: 'Decisão administrativa registrada',
                descricao: $decisao
            );

            return $ocorrencia->fresh([
                'responsavelAnalise',
                'anexos',
                'historicos',
                'orcamentoReposicao',
            ]);
        });
    }

    public function resolver(RomaneioOcorrencia $ocorrencia, string $solucao): RomaneioOcorrencia 
    {
        return DB::transaction(
            function () use ($ocorrencia, $solucao) {
                $usuarioId = Auth::id();
                $solucao = trim($solucao);

                if (! $usuarioId) {
                    throw ValidationException::withMessages([
                        'usuario' =>
                            'Não foi possível identificar o usuário responsável pela resolução.',
                    ]);
                }

                if (mb_strlen($solucao) < 5) {
                    throw ValidationException::withMessages([
                        'solucao' =>
                            'Descreva a solução aplicada à ocorrência.',
                    ]);
                }

                $ocorrencia = RomaneioOcorrencia::query()
                    ->lockForUpdate()
                    ->findOrFail($ocorrencia->id);

                if (
                    $ocorrencia->estaResolvida()
                    || $ocorrencia->estaCancelada()
                ) {
                    throw ValidationException::withMessages([
                        'ocorrencia' =>
                            'A ocorrência já está encerrada.',
                    ]);
                }

                if (
                    ! $ocorrencia->decidida_por
                    || ! $ocorrencia->classificacao_final
                ) {
                    throw ValidationException::withMessages([
                        'decisao' =>
                            'Registre a decisão administrativa antes de resolver a ocorrência.',
                    ]);
                }

                if (
                    ! $ocorrencia
                        ->permite_fechamento_logistico
                ) {
                    throw ValidationException::withMessages([
                        'fechamento_logistico' =>
                            'Libere o fechamento logístico antes de resolver a ocorrência.',
                    ]);
                }

                /*
                * Destinos que exigem tratamento pelo fluxo real
                * de devoluções e estoque por lote.
                */
                $destinosComDevolucao = [
                    'Quarentena',
                    'Reintegracao',
                    'Perda',
                    'Reposicao',
                ];

                $exigeDevolucao = in_array(
                    $ocorrencia->destino_estoque,
                    $destinosComDevolucao,
                    true
                );

                if ($exigeDevolucao) {
                    $devolucao = \App\Models\Devolucao::query()
                        ->where(
                            'romaneio_ocorrencia_id',
                            $ocorrencia->id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (! $devolucao) {
                        throw ValidationException::withMessages([
                            'devolucao' =>
                                'Registre a devolução do material antes de resolver esta ocorrência.',
                        ]);
                    }

                    if (! $devolucao->estaConcluida()) {
                        $statusDevolucao = str_replace(
                            '_',
                            ' ',
                            (string) $devolucao->status
                        );

                        throw ValidationException::withMessages([
                            'devolucao' =>
                                'A devolução #'
                                . $devolucao->id
                                . ' ainda está em '
                                . $statusDevolucao
                                . '. Conclua o fluxo da devolução antes de resolver a ocorrência.',
                        ]);
                    }
                }

                $statusAnterior = $ocorrencia->status;

                $ocorrencia->update([
                    'status' =>
                        'Resolvida',

                    'resolvida_por' =>
                        $usuarioId,

                    'resolvida_em' =>
                        now(),

                    'solucao' =>
                        $solucao,

                    'bloqueia_operacao' =>
                        false,

                    'permite_fechamento_logistico' =>
                        true,
                ]);

                $this->registrarHistorico(
                    ocorrencia: $ocorrencia,
                    statusAnterior: $statusAnterior,
                    statusNovo: 'Resolvida',
                    evento: 'Ocorrência resolvida',
                    descricao: $solucao
                );

                return $ocorrencia->fresh([
                    'anexos',
                    'historicos',
                    'responsavelAnalise',
                    'devolucao',
                ]);
            }
        );
    }

    public function cancelar(RomaneioOcorrencia $ocorrencia, string $justificativa): RomaneioOcorrencia
    {
        return DB::transaction(function () use ($ocorrencia, $justificativa) {
            $usuarioId = Auth::id();
            $justificativa = trim($justificativa);

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' => 'Não foi possível identificar o usuário responsável pelo cancelamento.',
                ]);
            }

            if (mb_strlen($justificativa) < 5) {
                throw ValidationException::withMessages([
                    'justificativa' => 'Informe a justificativa do cancelamento.',
                ]);
            }

            $ocorrencia = RomaneioOcorrencia::query()
                ->lockForUpdate()
                ->findOrFail($ocorrencia->id);

            if ($ocorrencia->estaResolvida() || $ocorrencia->estaCancelada()) {
                throw ValidationException::withMessages([
                    'ocorrencia' => 'A ocorrência já está encerrada.',
                ]);
            }

            $statusAnterior = $ocorrencia->status;

            $ocorrencia->update([
                'status' => 'Cancelada',
                'classificacao_final' => 'Improcedente',
                'decidida_por' => $usuarioId,
                'decidida_em' => now(),
                'decisao' => $justificativa,
                'resolvida_por' => $usuarioId,
                'resolvida_em' => now(),
                'solucao' => $justificativa,
                'bloqueia_operacao' => false,
                'permite_fechamento_logistico' => true,
            ]);

            $this->registrarHistorico(
                ocorrencia: $ocorrencia,
                statusAnterior: $statusAnterior,
                statusNovo: 'Cancelada',
                evento: 'Ocorrência cancelada',
                descricao: $justificativa
            );

            return $ocorrencia->fresh([
                'anexos',
                'historicos',
                'responsavelAnalise',
            ]);
        });
    }

    private function resultadosComOcorrencia(RomaneioItem $romaneioItem): array 
    {
        $mapeamentos = [
            'quantidade_devolvida' => [
                'tipo' =>
                    'Devolução de material',

                'classificacao' =>
                    'Devolucao',

                'criticidade' =>
                    'Atencao',

                'exige_autorizacao' =>
                    false,

                'destino_estoque' =>
                    'Quarentena',
            ],

            'quantidade_recusada' => [
                'tipo' =>
                    'Material recusado pelo cliente',

                'classificacao' =>
                    'Recusa',

                'criticidade' =>
                    'Atencao',

                'exige_autorizacao' =>
                    false,

                'destino_estoque' =>
                    'Quarentena',
            ],

            'quantidade_avariada' => [
                'tipo' =>
                    'Material avariado',

                'classificacao' =>
                    'Avaria',

                'criticidade' =>
                    'Critico',

                'exige_autorizacao' =>
                    true,

                'destino_estoque' =>
                    'Quarentena',
            ],

            'quantidade_perdida' => [
                'tipo' =>
                    'Possível extravio de material',

                'classificacao' =>
                    'Extravio',

                'criticidade' =>
                    'Critico',

                'exige_autorizacao' =>
                    true,

                'destino_estoque' =>
                    'Sem_movimentacao',
            ],
        ];

        $quantidadeSaida = round(
            (float) (
                $romaneioItem->quantidade_conferida_saida
                ?? 0
            ),
            3
        );

        $resultados = [];
        $quantidadeComOcorrencia = 0.0;

        foreach (
            $mapeamentos
            as $campo => $mapeamento
        ) {
            $quantidade = round(
                (float) (
                    $romaneioItem->{$campo}
                    ?? 0
                ),
                3
            );

            if ($quantidade < 0) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "O item #{$romaneioItem->entrega_item_id} possui quantidade negativa no campo {$campo}.",
                ]);
            }

            if ($quantidade === 0.0) {
                continue;
            }

            if ($quantidade > $quantidadeSaida) {
                throw ValidationException::withMessages([
                    'itens' =>
                        "A quantidade com ocorrência do item #{$romaneioItem->entrega_item_id} ultrapassa a quantidade conferida na saída.",
                ]);
            }

            $quantidadeComOcorrencia = round(
                $quantidadeComOcorrencia
                + $quantidade,
                3
            );

            $resultados[] = [
                'campo' =>
                    $campo,

                'tipo' =>
                    $mapeamento['tipo'],

                'classificacao' =>
                    $mapeamento['classificacao'],

                'quantidade' =>
                    $quantidade,

                'criticidade' =>
                    $mapeamento['criticidade'],

                'exige_autorizacao' =>
                    $mapeamento['exige_autorizacao'],

                'destino_estoque' =>
                    $mapeamento['destino_estoque'],
            ];
        }

        if ($quantidadeComOcorrencia > $quantidadeSaida) {
            throw ValidationException::withMessages([
                'itens' =>
                    "A soma das ocorrências do item #{$romaneioItem->entrega_item_id} ultrapassa a quantidade conferida na saída.",
            ]);
        
        }
        return $resultados;

        }

        private function montarDescricao(
            RomaneioItem $romaneioItem,
            array $resultado
        ): string {
            /*
             * Produto, lote e quantidade possuem campos próprios.
             * A descrição da ocorrência deve conter somente o relato
             * operacional informado durante a triagem.
             */
            return trim(
                (string) (
                    $romaneioItem->observacao
                    ?? ''
                )
            );
        }

        private function registrarHistorico(
            RomaneioOcorrencia $ocorrencia,
            ?string $statusAnterior,
            string $statusNovo,
            string $evento,
            ?string $descricao = null
            ): RomaneioOcorrenciaHistorico {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' =>
                        'Não foi possível identificar o usuário responsável pelo histórico.',
                ]);
            }

            return RomaneioOcorrenciaHistorico::query()
                ->create([
                    'romaneio_ocorrencia_id' =>
                        $ocorrencia->id,

                    'status_anterior' =>
                        $statusAnterior,

                    'status_novo' =>
                        $statusNovo,

                    'evento' =>
                        $evento,

                    'descricao' =>
                        $descricao,

                    'registrado_por' =>
                        $usuarioId,

                    'registrado_em' =>
                        now(),
                ]);
        }

    
        public function prepararOcorrenciaDaTriagem(
            Romaneio $romaneio,
            RomaneioItem $romaneioItem,
            string $classificacao,
            float $quantidade,
            ?string $observacao = null
            ): RomaneioOcorrencia {
            return DB::transaction(function () use (
                $romaneio,
                $romaneioItem,
                $classificacao,
                $quantidade,
                $observacao
            ) {
            $usuarioId = Auth::id();

            if (! $usuarioId) {
                throw ValidationException::withMessages([
                    'usuario' =>
                        'Não foi possível identificar o responsável pela triagem.',
                ]);
            }

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

            $mapeamentos = [
                'Devolucao' => [
                    'tipo' =>
                        'Devolução de material',

                    'criticidade' =>
                        'Atencao',

                    'exige_autorizacao' =>
                        false,

                    'destino_estoque' =>
                        'Quarentena',
                ],

                'Recusa' => [
                    'tipo' =>
                        'Material recusado pelo cliente',

                    'criticidade' =>
                        'Atencao',

                    'exige_autorizacao' =>
                        false,

                    'destino_estoque' =>
                        'Quarentena',
                ],

                'Avaria' => [
                    'tipo' =>
                        'Material avariado',

                    'criticidade' =>
                        'Critico',

                    'exige_autorizacao' =>
                        true,

                    'destino_estoque' =>
                        'Quarentena',
                ],

                'Extravio' => [
                    'tipo' =>
                        'Possível extravio de material',

                    'criticidade' =>
                        'Critico',

                    'exige_autorizacao' =>
                        true,

                    'destino_estoque' =>
                        'Sem_movimentacao',
                ],
            ];

            $classificacao = trim($classificacao);

            if (! isset($mapeamentos[$classificacao])) {
                throw ValidationException::withMessages([
                    'tipo_resultado' =>
                        'O tipo de problema informado é inválido.',
                ]);
            }

            $configuracao = $mapeamentos[$classificacao];

            $itemBloqueado = RomaneioItem::query()
                ->with([
                    'entregaItem.vendaItem.produto',
                    'entregaItem.vendaItem.lote',
                    'entregaItem.itemOrcamento.produto',
                ])
                ->whereKey($romaneioItem->id)
                ->where(
                    'romaneio_id',
                    $romaneioBloqueado->id
                )
                ->lockForUpdate()
                ->first();

            if (! $itemBloqueado) {
                throw ValidationException::withMessages([
                    'romaneio_item_id' =>
                        'O produto selecionado não pertence a este romaneio.',
                ]);
            }

            $quantidade = round(
                $quantidade,
                3
            );

            if ($quantidade <= 0) {
                throw ValidationException::withMessages([
                    'quantidade_afetada' =>
                        'A quantidade afetada deve ser maior que zero.',
                ]);
            }

            $quantidadeSaida = round(
                (float) (
                    $itemBloqueado
                        ->quantidade_conferida_saida
                    ?? 0
                ),
                3
            );

            if ($quantidade > $quantidadeSaida) {
                throw ValidationException::withMessages([
                    'quantidade_afetada' =>
                        'A quantidade afetada não pode ultrapassar a quantidade conferida na saída.',
                ]);
            }

            $ocorrencia = RomaneioOcorrencia::query()
                ->where(
                    'romaneio_id',
                    $romaneioBloqueado->id
                )
                ->where(
                    'romaneio_item_id',
                    $itemBloqueado->id
                )
                ->where(
                    'tipo',
                    $configuracao['tipo']
                )
                ->lockForUpdate()
                ->first();

            if (
                $ocorrencia
                && (
                    in_array(
                        $ocorrencia->status,
                        [
                            'Resolvida',
                            'Cancelada',
                        ],
                        true
                    )
                    || ! empty(
                        $ocorrencia->decidida_por
                    )
                    || $ocorrencia->triagem_status
                        === 'Concluida'
                )
            ) {
                throw ValidationException::withMessages([
                    'romaneio_item_id' =>
                        'Este problema já foi concluído ou encaminhado para decisão e não pode ser alterado.',
                ]);
            }

            $consultaOutrasOcorrencias =
                RomaneioOcorrencia::query()
                    ->where(
                        'romaneio_id',
                        $romaneioBloqueado->id
                    )
                    ->where(
                        'romaneio_item_id',
                        $itemBloqueado->id
                    )
                    ->whereNotIn(
                        'status',
                        [
                            'Cancelada',
                        ]
                    );

            if ($ocorrencia) {
                $consultaOutrasOcorrencias->where(
                    'id',
                    '<>',
                    $ocorrencia->id
                );
            }

            $quantidadeOutrasOcorrencias = round(
                (float) $consultaOutrasOcorrencias
                    ->sum('quantidade_envolvida'),
                3
            );

            if (
                round(
                    $quantidadeOutrasOcorrencias
                    + $quantidade,
                    3
                ) > $quantidadeSaida
            ) {
                throw ValidationException::withMessages([
                    'quantidade_afetada' =>
                        'A soma das quantidades com problema ultrapassa a quantidade conferida na saída.',
                ]);
            }

            $equipe = RomaneioEquipe::query()
                ->where(
                    'romaneio_id',
                    $romaneioBloqueado->id
                )
                ->where(
                    'status',
                    'Ativa'
                )
                ->latest('id')
                ->first();

            $resultado = [
                'tipo' =>
                    $configuracao['tipo'],

                'classificacao' =>
                    $classificacao,

                'quantidade' =>
                    $quantidade,

                'criticidade' =>
                    $configuracao['criticidade'],

                'exige_autorizacao' =>
                    $configuracao['exige_autorizacao'],

                'destino_estoque' =>
                    $configuracao['destino_estoque'],
            ];

            $descricao = trim(
                (string) $observacao
            );

            if ($descricao === '') {
                $descricao = $this->montarDescricao(
                    $itemBloqueado,
                    $resultado
                );
            }

            if (! $ocorrencia) {
                $ocorrencia = RomaneioOcorrencia::query()
                    ->create([
                        'romaneio_id' =>
                            $romaneioBloqueado->id,

                        'romaneio_equipe_id' =>
                            $equipe?->id,

                        'entrega_id' =>
                            $romaneioBloqueado->entrega_id,

                        'romaneio_item_id' =>
                            $itemBloqueado->id,

                        'entrega_item_id' =>
                            $itemBloqueado->entrega_item_id,

                        'quantidade_envolvida' =>
                            $quantidade,

                        'categoria' =>
                            'Material',

                        'tipo' =>
                            $configuracao['tipo'],

                        'classificacao_inicial' =>
                            $classificacao,

                        'classificacao_final' =>
                            null,

                        'criticidade' =>
                            $configuracao['criticidade'],

                        'etapa' =>
                            'Retorno',

                        'descricao' =>
                            $descricao,

                        'bloqueia_operacao' =>
                            true,

                        'exige_autorizacao' =>
                            $configuracao['exige_autorizacao'],

                        'status' =>
                            'Aguardando_evidencias',

                        'triagem_status' =>
                            'Aguardando',

                        'registrada_por' =>
                            $usuarioId,

                        'registrada_em' =>
                            now(),

                        'destino_estoque' =>
                            $configuracao['destino_estoque'],

                        'permite_fechamento_logistico' =>
                            false,
                    ]);

                $this->registrarHistorico(
                    ocorrencia: $ocorrencia,
                    statusAnterior: null,
                    statusNovo: $ocorrencia->status,
                    evento:
                        'Ocorrência criada na triagem do retorno',
                    descricao:
                        $descricao
                );
            } else {
                $quantidadeAnterior = round(
                    (float) (
                        $ocorrencia
                            ->quantidade_envolvida
                        ?? 0
                    ),
                    3
                );

                $ocorrencia->update([
                    'quantidade_envolvida' =>
                        $quantidade,

                    'descricao' =>
                        $descricao,

                    'classificacao_inicial' =>
                        $classificacao,

                    'criticidade' =>
                        $configuracao['criticidade'],

                    'exige_autorizacao' =>
                        $configuracao['exige_autorizacao'],

                    'destino_estoque' =>
                        $configuracao['destino_estoque'],
                ]);

                if ($quantidadeAnterior !== $quantidade) {
                    $this->registrarHistorico(
                        ocorrencia: $ocorrencia,
                        statusAnterior:
                            $ocorrencia->status,
                        statusNovo:
                            $ocorrencia->status,
                        evento:
                            'Quantidade da ocorrência atualizada na triagem',
                        descricao:
                            'Quantidade alterada de '
                            . number_format(
                                $quantidadeAnterior,
                                3,
                                ',',
                                '.'
                            )
                            . ' para '
                            . number_format(
                                $quantidade,
                                3,
                                ',',
                                '.'
                            )
                            . '.'
                    );
                }
            }

            return $ocorrencia->fresh([
                'anexos',
                'avaliacoes',
                'entregaItem.vendaItem.produto',
                'entregaItem.vendaItem.lote',
                'entregaItem.itemOrcamento.produto',
                'romaneioItem.entregaItem.vendaItem.produto',
                'romaneioItem.entregaItem.vendaItem.lote',
                'romaneioItem.entregaItem.itemOrcamento.produto',
            ]);
        });
    }
}