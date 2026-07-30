<?php

namespace App\Services;

use App\Models\Devolucao;
use App\Models\DevolucaoLote;
use App\Models\EstoqueMovimentacao;
use App\Models\ItemVenda;
use App\Models\Lote;
use App\Models\Produto;
use App\Models\RomaneioOcorrenciaAvaliacao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucaoTriagemService
{
    /**
     * Cria ou atualiza os detalhes operacionais da devolução com base
     * nas linhas concluídas da triagem visual.
     */
    public function sincronizarAvaliacoes(
        Devolucao $devolucao
    ): Collection {
        return DB::transaction(function () use ($devolucao) {
            $usuarioId = $this->usuarioId();

            $devolucao = Devolucao::query()
                ->with(['ocorrencia', 'itemVenda'])
                ->lockForUpdate()
                ->findOrFail($devolucao->id);

            if (! $devolucao->romaneio_ocorrencia_id) {
                return $devolucao->lotes()
                    ->with(['avaliacao', 'lote'])
                    ->get();
            }

            if (
                ! $devolucao->ocorrencia
                || $devolucao->ocorrencia->triagem_status !== 'Concluida'
            ) {
                throw ValidationException::withMessages([
                    'triagem' =>
                        'Conclua a triagem visual antes de iniciar o tratamento da devolução.',
                ]);
            }

            $avaliacoes = RomaneioOcorrenciaAvaliacao::query()
                ->where(
                    'romaneio_ocorrencia_id',
                    $devolucao->romaneio_ocorrencia_id
                )
                ->orderBy('ordem')
                ->lockForUpdate()
                ->get();

            if ($avaliacoes->isEmpty()) {
                throw ValidationException::withMessages([
                    'triagem' =>
                        'A ocorrência foi concluída sem linhas de avaliação.',
                ]);
            }

            $totalAvaliado = round(
                (float) $avaliacoes->sum('quantidade'),
                3
            );

            $quantidadeDevolucao = round(
                (float) $devolucao->quantidade,
                3
            );

            if (abs($totalAvaliado - $quantidadeDevolucao) > 0.0005) {
                throw ValidationException::withMessages([
                    'quantidade' =>
                        'A soma da triagem não corresponde à quantidade da devolução.',
                ]);
            }

            $idsAvaliacoes = $avaliacoes
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            /*
             * Se uma linha deixou de existir antes do processamento,
             * o detalhe correspondente é cancelado, nunca apagado.
             */
            DevolucaoLote::query()
                ->where('devolucao_id', $devolucao->id)
                ->whereNotNull('romaneio_ocorrencia_avaliacao_id')
                ->whereNotIn(
                    'romaneio_ocorrencia_avaliacao_id',
                    $idsAvaliacoes
                )
                ->where('status_processamento', 'Pendente')
                ->update([
                    'status_processamento' => 'Cancelado',
                    'processado_por' => $usuarioId,
                    'processado_em' => now(),
                    'updated_at' => now(),
                ]);

            foreach ($avaliacoes as $avaliacao) {
                $existente = DevolucaoLote::query()
                    ->where(
                        'romaneio_ocorrencia_avaliacao_id',
                        $avaliacao->id
                    )
                    ->lockForUpdate()
                    ->first();

                if (
                    $existente
                    && $existente->status_processamento !== 'Pendente'
                ) {
                    continue;
                }

                DevolucaoLote::query()->updateOrCreate(
                    [
                        'romaneio_ocorrencia_avaliacao_id' =>
                            $avaliacao->id,
                    ],
                    [
                        'devolucao_id' => $devolucao->id,
                        'produto_id' => $devolucao->produto_id,
                        'lote_id' => $avaliacao->lote_id,
                        'quantidade' => $avaliacao->quantidade,
                        'destino_estoque' =>
                            $this->mapearDestino(
                                $avaliacao->destino_sugerido
                            ),
                        'status_processamento' => 'Pendente',
                        'venda_id' => $devolucao->venda_id,
                        'item_venda_id' => $devolucao->venda_item_id,
                        'devolvido_por' => $usuarioId,
                        'processado_por' => null,
                        'processado_em' => null,
                    ]
                );
            }

            return $devolucao->lotes()
                ->with(['avaliacao', 'lote'])
                ->get();
        });
    }

    /**
     * Processa todas as linhas ainda pendentes. Linhas destinadas à
     * reposição permanecem pendentes até existir orçamento de reposição.
     */
    public function processar(
        Devolucao $devolucao
    ): Devolucao {
        return DB::transaction(function () use ($devolucao) {
            $usuarioId = $this->usuarioId();

            $devolucao = Devolucao::query()
                ->with(['ocorrencia'])
                ->lockForUpdate()
                ->findOrFail($devolucao->id);

            $this->sincronizarAvaliacoes($devolucao);

            $detalhes = DevolucaoLote::query()
                ->where('devolucao_id', $devolucao->id)
                ->where('status_processamento', 'Pendente')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($detalhes as $detalhe) {
                if (
                    $detalhe->destino_estoque === 'reposicao_cliente'
                    && ! $devolucao->orcamento_reposicao_id
                ) {
                    continue;
                }

                $this->processarDetalhe(
                    $devolucao,
                    $detalhe,
                    $usuarioId
                );
            }

            $possuiReposicaoPendente = DevolucaoLote::query()
                ->where('devolucao_id', $devolucao->id)
                ->where('status_processamento', 'Pendente')
                ->where('destino_estoque', 'reposicao_cliente')
                ->exists();

            if ($possuiReposicaoPendente) {
                $devolucao->update([
                    'status' => $devolucao->orcamento_reposicao_id
                        ? 'aguardando_estoque'
                        : 'aguardando_orcamento',
                ]);
            }

            $this->atualizarMovimentacoesCabecalho($devolucao);

            return $devolucao->fresh([
                'lotes.avaliacao',
                'lotes.movimentacoes',
                'movimentacoes',
            ]);
        });
    }

    private function processarDetalhe(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId
    ): void {
        /*
         * A chave única avaliação + tipo e o lock do detalhe tornam
         * o processamento idempotente.
         */
        if ($detalhe->status_processamento !== 'Pendente') {
            return;
        }

        match ($detalhe->destino_estoque) {
            'sem_movimentacao' =>
                $this->concluirSemMovimentacao(
                    $detalhe,
                    $usuarioId
                ),

            'quarentena' =>
                $this->registrarMovimentacaoNeutra(
                    $devolucao,
                    $detalhe,
                    $usuarioId,
                    'quarentena',
                    'Material recebido e mantido em quarentena.'
                ),

            'baixa_perda' =>
                $this->registrarMovimentacaoNeutra(
                    $devolucao,
                    $detalhe,
                    $usuarioId,
                    'baixa_perda',
                    'Material retornado classificado como perda, sem reintegração ao saldo disponível.'
                ),

            'reintegracao' =>
                $this->reintegrar(
                    $devolucao,
                    $detalhe,
                    $usuarioId
                ),

            'reposicao_cliente' =>
                $this->registrarSaidaReposicao(
                    $devolucao,
                    $detalhe,
                    $usuarioId
                ),

            default => throw ValidationException::withMessages([
                'destino_estoque' =>
                    'O destino do detalhe da devolução é inválido.',
            ]),
        };
    }

    private function concluirSemMovimentacao(
        DevolucaoLote $detalhe,
        int $usuarioId
    ): void {
        $detalhe->update([
            'status_processamento' => 'Processado',
            'processado_por' => $usuarioId,
            'processado_em' => now(),
        ]);
    }

    private function registrarMovimentacaoNeutra(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId,
        string $tipo,
        string $descricao
    ): void {
        $produto = Produto::query()
            ->lockForUpdate()
            ->findOrFail($detalhe->produto_id);

        $lote = $detalhe->lote_id
            ? Lote::query()
                ->lockForUpdate()
                ->find($detalhe->lote_id)
            : null;

        $movimentacao = $this->registrarMovimentacao(
            devolucao: $devolucao,
            detalhe: $detalhe,
            usuarioId: $usuarioId,
            tipo: $tipo,
            natureza: 'Neutra',
            descricao: $descricao,
            produto: $produto,
            lote: $lote,
            saldoProdutoAnterior:
                (float) ($produto->quantidade_estoque ?? 0),
            saldoProdutoPosterior:
                (float) ($produto->quantidade_estoque ?? 0),
            saldoLoteAnterior:
                $lote
                    ? (float) $lote->quantidade_disponivel
                    : null,
            saldoLotePosterior:
                $lote
                    ? (float) $lote->quantidade_disponivel
                    : null
        );

        $campoMovimentacao = $tipo === 'baixa_perda'
            ? 'movimentacao_saida_id'
            : 'movimentacao_entrada_id';

        $detalhe->update([
            $campoMovimentacao => $movimentacao->id,
            'status_processamento' => 'Processado',
            'processado_por' => $usuarioId,
            'processado_em' => now(),
        ]);
    }

    private function reintegrar(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId
    ): void {
        $produto = Produto::query()
            ->lockForUpdate()
            ->findOrFail($detalhe->produto_id);

        $lote = $this->resolverLote(
            $devolucao,
            $detalhe,
            $usuarioId
        );

        $quantidade = (float) $detalhe->quantidade;
        $saldoProdutoAnterior =
            (float) ($produto->quantidade_estoque ?? 0);
        $saldoLoteAnterior =
            (float) $lote->quantidade_disponivel;

        $lote->quantidade =
            (float) $lote->quantidade + $quantidade;
        $lote->quantidade_disponivel =
            $saldoLoteAnterior + $quantidade;
        $lote->status = 1;
        $lote->save();

        $produto->quantidade_estoque =
            $saldoProdutoAnterior + $quantidade;
        $produto->save();

        $movimentacao = $this->registrarMovimentacao(
            devolucao: $devolucao,
            detalhe: $detalhe,
            usuarioId: $usuarioId,
            tipo: 'reintegracao',
            natureza: 'Entrada',
            descricao:
                'Material devolvido e reintegrado ao estoque disponível.',
            produto: $produto,
            lote: $lote,
            saldoProdutoAnterior: $saldoProdutoAnterior,
            saldoProdutoPosterior:
                (float) $produto->quantidade_estoque,
            saldoLoteAnterior: $saldoLoteAnterior,
            saldoLotePosterior:
                (float) $lote->quantidade_disponivel
        );

        $detalhe->update([
            'lote_id' => $lote->id,
            'movimentacao_entrada_id' => $movimentacao->id,
            'status_processamento' => 'Processado',
            'processado_por' => $usuarioId,
            'processado_em' => now(),
        ]);
    }

    private function registrarSaidaReposicao(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId
    ): void {
        if (! $devolucao->orcamento_reposicao_id) {
            return;
        }

        /*
         * A baixa física da reposição pertence ao faturamento/expedição
         * do novo orçamento. Aqui registramos somente o vínculo e mantemos
         * a linha aguardando estoque, evitando baixa antecipada ou dupla.
         */
        $devolucao->update([
            'status' => 'aguardando_estoque',
        ]);
    }

    private function registrarMovimentacao(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId,
        string $tipo,
        string $natureza,
        string $descricao,
        Produto $produto,
        ?Lote $lote,
        ?float $saldoProdutoAnterior,
        ?float $saldoProdutoPosterior,
        ?float $saldoLoteAnterior,
        ?float $saldoLotePosterior
    ): EstoqueMovimentacao {
        return EstoqueMovimentacao::query()->firstOrCreate(
            [
                'romaneio_ocorrencia_avaliacao_id' =>
                    $detalhe->romaneio_ocorrencia_avaliacao_id,
                'tipo' => $tipo,
            ],
            [
                'devolucao_id' => $devolucao->id,
                'devolucao_lote_id' => $detalhe->id,
                'produto_id' => $produto->id,
                'lote_id' => $lote?->id,
                'natureza' => $natureza,
                'quantidade' => $detalhe->quantidade,
                'saldo_produto_anterior' =>
                    $saldoProdutoAnterior,
                'saldo_produto_posterior' =>
                    $saldoProdutoPosterior,
                'saldo_lote_anterior' => $saldoLoteAnterior,
                'saldo_lote_posterior' => $saldoLotePosterior,
                'descricao' => $descricao,
                'registrada_por' => $usuarioId,
                'registrada_em' => now(),
            ]
        );
    }

    private function resolverLote(
        Devolucao $devolucao,
        DevolucaoLote $detalhe,
        int $usuarioId
    ): Lote {
        if ($detalhe->lote_id) {
            $lote = Lote::query()
                ->where('produto_id', $detalhe->produto_id)
                ->lockForUpdate()
                ->find($detalhe->lote_id);

            if ($lote) {
                return $lote;
            }
        }

        $itemVenda = ItemVenda::query()
            ->lockForUpdate()
            ->find($devolucao->venda_item_id);

        if ($itemVenda?->lote_id) {
            $lote = Lote::query()
                ->where('produto_id', $detalhe->produto_id)
                ->lockForUpdate()
                ->find($itemVenda->lote_id);

            if ($lote) {
                return $lote;
            }
        }

        $numeroLote = 'DEV-'
            . $devolucao->id
            . '-AV'
            . $detalhe->romaneio_ocorrencia_avaliacao_id;

        return Lote::query()->firstOrCreate(
            ['numero_lote' => $numeroLote],
            [
                'produto_id' => $detalhe->produto_id,
                'quantidade' => 0,
                'quantidade_reservada' => 0,
                'quantidade_disponivel' => 0,
                'preco_compra' => 0,
                'data_compra' => now()->toDateString(),
                'lancado_por' => $usuarioId,
                'status' => 1,
            ]
        );
    }

    private function atualizarMovimentacoesCabecalho(
        Devolucao $devolucao
    ): void {
        $entradaId = DevolucaoLote::query()
            ->where('devolucao_id', $devolucao->id)
            ->whereNotNull('movimentacao_entrada_id')
            ->value('movimentacao_entrada_id');

        $saidaId = DevolucaoLote::query()
            ->where('devolucao_id', $devolucao->id)
            ->whereNotNull('movimentacao_saida_id')
            ->value('movimentacao_saida_id');

        $devolucao->update([
            'movimentacao_entrada_id' => $entradaId,
            'movimentacao_saida_id' => $saidaId,
        ]);
    }

    private function mapearDestino(string $destino): string
    {
        return match ($destino) {
            'Quarentena' => 'quarentena',
            'Reintegracao' => 'reintegracao',
            'Perda' => 'baixa_perda',
            'Reposicao' => 'reposicao_cliente',
            default => 'sem_movimentacao',
        };
    }

    private function usuarioId(): int
    {
        $usuarioId = Auth::id();

        if (! $usuarioId) {
            throw ValidationException::withMessages([
                'usuario' =>
                    'Não foi possível identificar o usuário responsável pela operação.',
            ]);
        }

        return (int) $usuarioId;
    }
}