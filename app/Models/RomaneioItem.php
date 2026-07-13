<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RomaneioItem extends Model
{
    protected $table = 'romaneio_itens';

    protected $fillable = [
        'romaneio_id',
        'entrega_item_id',
        'quantidade_prevista',
        'quantidade_separada',
        'quantidade_carregada',
        'status',
        'carregado_por',
        'conferido_por',
        'conferido_em',
        'observacao',
    ];

    protected $casts = [
        'quantidade_prevista' => 'decimal:2',
        'quantidade_separada' => 'decimal:2',
        'quantidade_carregada' => 'decimal:2',
        'conferido_em' => 'datetime',
    ];

    public function romaneio()
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function entregaItem()
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_id'
        );
    }

    public function carregador()
    {
        return $this->belongsTo(
            Funcionario::class,
            'carregado_por'
        );
    }

    public function conferente()
    {
        return $this->belongsTo(
            Funcionario::class,
            'conferido_por'
        );
    }
}