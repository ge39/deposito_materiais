<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucao extends Model
{
    use HasFactory;

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
        'quantidade' => 'decimal:3',
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
            Funcionario::class,
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

    public function estaPendente(): bool
    {
        return in_array(
            $this->status,
            [
                'pendente',
                'aguardando_evidencias',
                'em_analise',
                'aguardando_decisao',
                'aguardando_orcamento',
                'orcamento_criado',
                'aguardando_estoque',
                'em_reposicao',
            ],
            true
        );
    }

    public function estaAprovada(): bool
    {
        return in_array(
            $this->status,
            [
                'aprovada',
                'aprovada_troca',
                'aprovada_devolucao',
            ],
            true
        );
    }

    public function estaRejeitada(): bool
    {
        return $this->status === 'rejeitada';
    }

    public function estaConcluida(): bool
    {
        return $this->status === 'concluida';
    }

    public function estaCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function exigeOrcamentoReposicao(): bool
    {
        return in_array(
            $this->tipo,
            [
                'troca',
                'reposicao',
            ],
            true
        );
    }

    public function possuiOrcamentoReposicao(): bool
    {
        return ! empty(
            $this->orcamento_reposicao_id
        );
    }

    public function movimentacaoEstoqueConcluida(): bool
    {
        return match ($this->destino_estoque) {
            'sem_movimentacao' =>
                true,

            'quarentena',
            'reintegracao' =>
                ! empty($this->movimentacao_entrada_id),

            'baixa_perda',
            'reposicao_cliente' =>
                ! empty($this->movimentacao_saida_id),

            default =>
                false,
        };
    }

    public function podeConcluir(): bool
    {
        if ($this->estaRejeitada()) {
            return ! empty(
                $this->motivo_rejeicao
            );
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