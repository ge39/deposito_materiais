<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    protected $table = 'itens_pedido';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'lote_id',
        'quantidade',
        'quantidade_entregue',
        'quantidade_pendente',
        'preco_unitario',
        'subtotal',
        'status',
        'previsao_entrega'
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'quantidade_entregue' => 'decimal:2',
        'quantidade_pendente' => 'decimal:2',
    ];
}