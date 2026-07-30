<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucao extends Model
{
    use HasFactory;

    public const STATUS_PENDENTES = [
        'pendente',
        'aguardando_evidencias',
        'em_analise',
        'aguardando_decisao',
        'aguardando_orcamento',
        'orcamento_criado',
        'aguardando_estoque',
        'em_reposicao',
    ];

    public const STATUS_APROVADOS = [
        'aprovada',
        'aprovada_troca',
        'aprovada_devolucao',
    ];

    public const STATUS_CONCLUIDOS = [
        'aprovada',
        'aprovada_troca',
        'aprovada_devolucao',
        'concluida',
    ];

    public const TIPOS_COM_REPOSICAO = [
        'troca',
        'reposicao',
    ];

    protected $table = 'devolucoes';

    protected $fillable = [
        'romaneio_ocorrencia_id',
        'romaneio_id',
        'entrega_id',
        'romaneio_item_id',
        'entrega_item_id',

        'cliente_id',
        'venda_id',
        'venda_item_id',

        'orcamento_origem_id',
        'orcamento_reposicao_id',

        'produto_id',
        'quantidade',

        'motivo',
        'tipo',
        'status',
        'observacao',
        'motivo_rejeicao',

        'destino_estoque',
        'movimentacao_entrada_id',
        'movimentacao_saida_id',

        'criado_por',
        'responsavel_analise_id',
        'analise_iniciada_em',

        'decidida_por',
        'decidida_em',
        'decisao',

        'concluida_por',
        'concluida_em',

        'imagem1',
        'imagem2',
        'imagem3',
        'imagem4',

        'empresa_id',
    ];

    protected $casts = [
        'romaneio_ocorrencia_id' => 'integer',
        'romaneio_id' => 'integer',
        'entrega_id' => 'integer',
        'romaneio_item_id' => 'integer',
        'entrega_item_id' => 'integer',
        'cliente_id' => 'integer',
        'venda_id' => 'integer',
        'venda_item_id' => 'integer',
        'orcamento_origem_id' => 'integer',
        'orcamento_reposicao_id' => 'integer',
        'produto_id' => 'integer',
        'quantidade' => 'decimal:3',
        'movimentacao_entrada_id' => 'integer',
        'movimentacao_saida_id' => 'integer',
        'criado_por' => 'integer',
        'responsavel_analise_id' => 'integer',
        'decidida_por' => 'integer',
        'concluida_por' => 'integer',
        'empresa_id' => 'integer',
        'analise_iniciada_em' => 'datetime',
        'decidida_em' => 'datetime',
        'concluida_em' => 'datetime',
    ];

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioOcorrencia::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function romaneioItem(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioItem::class,
            'romaneio_item_id'
        );
    }

    public function entregaItem(): BelongsTo
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_id'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Cliente::class,
            'cliente_id'
        );
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(
            Venda::class,
            'venda_id'
        );
    }

    public function itemVenda(): BelongsTo
    {
        return $this->belongsTo(
            ItemVenda::class,
            'venda_item_id'
        );
    }

    public function orcamentoOrigem(): BelongsTo
    {
        return $this->belongsTo(
            Orcamento::class,
            'orcamento_origem_id'
        );
    }

    public function orcamentoReposicao(): BelongsTo
    {
        return $this->belongsTo(
            Orcamento::class,
            'orcamento_reposicao_id'
        );
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(
            Produto::class,
            'produto_id'
        );
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'criado_por'
        );
    }

    public function responsavelAnalise(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'responsavel_analise_id'
        );
    }

    public function decididaPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'decidida_por'
        );
    }

    public function concluidaPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'concluida_por'
        );
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'empresa_id'
        );
    }

    public function logs(): HasMany
    {
        return $this->hasMany(
            DevolucaoLog::class,
            'devolucao_id'
        )->orderBy('created_at');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(
            DevolucaoLote::class,
            'devolucao_id'
        )->orderBy('id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(
            EstoqueMovimentacao::class,
            'devolucao_id'
        )->orderBy('registrada_em');
    }

    public function estaPendente(): bool
    {
        return in_array(
            $this->status,
            self::STATUS_PENDENTES,
            true
        );
    }

    public function estaAprovada(): bool
    {
        return in_array(
            $this->status,
            self::STATUS_APROVADOS,
            true
        );
    }

    public function estaRejeitada(): bool
    {
        return $this->status === 'rejeitada';
    }

    /**
     * Os estados aprovados já representam o encerramento operacional
     * da devolução e liberam a resolução da ocorrência vinculada.
     */
    public function estaConcluida(): bool
    {
        return in_array(
            $this->status,
            self::STATUS_CONCLUIDOS,
            true
        );
    }

    public function estaCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function estaEncerrada(): bool
    {
        return
            $this->estaConcluida()
            || $this->estaRejeitada()
            || $this->estaCancelada();
    }

    public function exigeOrcamentoReposicao(): bool
    {
        return in_array(
            $this->tipo,
            self::TIPOS_COM_REPOSICAO,
            true
        );
    }

    public function possuiOrcamentoReposicao(): bool
    {
        return ! empty($this->orcamento_reposicao_id);
    }

    public function movimentacaoEstoqueConcluida(): bool
    {
        /*
         * Fluxo novo: cada linha da triagem possui tratamento próprio.
         * A devolução só pode avançar quando todos os detalhes estiverem
         * processados ou cancelados.
         */
        if ($this->lotes()->exists()) {
            return ! $this->lotes()
                ->where('status_processamento', 'Pendente')
                ->exists();
        }

        /*
         * Compatibilidade com devoluções antigas que ainda guardam
         * somente uma movimentação no cabeçalho.
         */
        return match ($this->destino_estoque) {
            'sem_movimentacao' => true,

            'quarentena',
            'reintegracao' => ! empty($this->movimentacao_entrada_id),

            'baixa_perda',
            'reposicao_cliente' => ! empty($this->movimentacao_saida_id),

            default => false,
        };
    }

    public function podeConcluir(): bool
    {
        if ($this->estaConcluida()) {
            return true;
        }

        if ($this->estaRejeitada()) {
            return ! empty($this->motivo_rejeicao);
        }

        if (
            $this->exigeOrcamentoReposicao()
            && ! $this->possuiOrcamentoReposicao()
        ) {
            return false;
        }

        return
            ! empty($this->decisao)
            && $this->movimentacaoEstoqueConcluida();
    }

    public function isPendente(): bool
    {
        return $this->estaPendente();
    }

    public function isAprovada(): bool
    {
        return $this->estaAprovada();
    }

    public function isRejeitada(): bool
    {
        return $this->estaRejeitada();
    }

    public function isConcluida(): bool
    {
        return $this->estaConcluida();
    }
}