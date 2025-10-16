<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoCompra extends Model
{
    protected $fillable = [
        'pedido_id', 'produto_id', 'quantidade', 'preco_unitario', 'total'
    ];

    public function pedido() {
        return $this->belongsTo(PedidoCompra::class, 'pedido_id');
    }

    public function produto() {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
