<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoExtraviado extends Model
{
    protected $table = 'produtos_extraviados';

    protected $fillable = [
        'romaneio_ocorrencia_id',
        'romaneio_id',
        'entrega_id',
        'romaneio_item_id',
        'entrega_item_id',
        'produto_id',
        'quantidade',
        'status',
        'estoque_baixado',
        'movimentacao_estoque_id',
        'confirmado_por',
        'confirmado_em',
        'encerrado_por',
        'encerrado_em',
        'observacao',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'estoque_baixado' => 'boolean',
        'confirmado_em' => 'datetime',
        'encerrado_em' => 'datetime',
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

    public function produto(): BelongsTo
    {
        return $this->belongsTo(
            Produto::class,
            'produto_id'
        );
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmado_por'
        );
    }

    public function encerradoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'encerrado_por'
        );
    }
}