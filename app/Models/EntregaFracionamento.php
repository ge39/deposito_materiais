<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaFracionamento extends Model
{
    protected $table = 'entrega_fracionamentos';

    public const UPDATED_AT = null;

    protected $fillable = [
        'entrega_id',
        'entrega_destino_id',
        'entrega_item_origem_id',
        'entrega_item_destino_id',
        'romaneio_origem_id',
        'romaneio_destino_id',
        'romaneio_item_origem_id',
        'romaneio_item_destino_id',
        'quantidade',
        'motivo',
        'observacao',
        'criado_por',

    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function entregaDestino(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_destino_id'
        );
    }

    public function entregaItemOrigem(): BelongsTo
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_origem_id'
        );
    }

    public function entregaItemDestino(): BelongsTo
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_destino_id'
        );
    }

    public function romaneioOrigem(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_origem_id'
        );
    }

    public function romaneioDestino(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_destino_id'
        );
    }

    public function romaneioItemOrigem(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioItem::class,
            'romaneio_item_origem_id'
        );
    }

    public function romaneioItemDestino(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioItem::class,
            'romaneio_item_destino_id'
        );
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'criado_por'
        );
    }
}
