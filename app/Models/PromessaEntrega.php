<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromessaEntrega extends Model
{
    protected $table = 'promessa_entregas';

    protected $fillable = [
        'entrega_id',
        'romaneio_id',
        'romaneio_item_id',
        'data_prometida',
        'periodo',
        'motivo',
        'observacao',
        'criada_por',
    ];

    protected $casts = [
        'data_prometida' => 'datetime',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function romaneioItem(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioItem::class,
            'romaneio_item_id'
        );
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'criada_por'
        );
    }
}