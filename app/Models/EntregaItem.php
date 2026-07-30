<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntregaItem extends Model
{
    protected $table = 'entrega_itens';

    protected $fillable = [
        'entrega_id',
        'entrega_item_origem_id',
        'entrega_item_principal_id',
        'item_orcamento_id',
        'venda_item_id',
        'quantidade_prevista',
        'quantidade_entregue',
        'quantidade_recusada',
        'quantidade_devolvida',
        'quantidade_avariada',
        'motivo_nao_entrega',
        'status',
        'observacao',
    ];

    protected $casts = [
        'quantidade_prevista' => 'decimal:2',
        'quantidade_entregue' => 'decimal:2',
        'quantidade_recusada' => 'decimal:2',
        'quantidade_devolvida' => 'decimal:2',
        'quantidade_avariada' => 'decimal:2',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'entrega_id');
    }

    public function itemOrigem(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'entrega_item_origem_id'
        );
    }

    public function itemPrincipal(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'entrega_item_principal_id'
        );
    }

    public function itensFilhos(): HasMany
    {
        return $this->hasMany(
            self::class,
            'entrega_item_origem_id'
        );
    }

    public function itensFracionados(): HasMany
    {
        return $this->hasMany(
            self::class,
            'entrega_item_principal_id'
        );
    }

    public function vendaItem(): BelongsTo
    {
        return $this->belongsTo(ItemVenda::class, 'venda_item_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(
            Produto::class,
            'produto_id'
        );
    }

    public function itemOrcamento(): BelongsTo
    {
        return $this->belongsTo(ItemOrcamento::class, 'item_orcamento_id');
    }

    public function romaneioItens(): HasMany
    {
        return $this->hasMany(RomaneioItem::class, 'entrega_item_id');
    }

    public function getProdutoAttribute(): ?Produto
    {
        return $this->itemOrcamento?->produto
            ?? $this->vendaItem?->produto;
    }

    public function getQuantidadePendenteAttribute(): float
    {
        return max(
            0,
            (float) $this->quantidade_prevista
            - (float) $this->quantidade_entregue
        );
    }

    public function getPercentualEntregueAttribute(): float
    {
        $quantidadePrevista = (float) $this->quantidade_prevista;

        if ($quantidadePrevista <= 0) {
            return 0;
        }

        return round(
            ((float) $this->quantidade_entregue / $quantidadePrevista) * 100,
            2
        );
    }
}