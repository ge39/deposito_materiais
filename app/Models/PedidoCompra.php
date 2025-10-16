<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    protected $fillable = [
        'fornecedor_id', 'data_pedido', 'status', 'total', 'observacoes'
    ];

    public function fornecedor() {
        return $this->belongsTo(Fornecedor::class);
    }

    public function itens() {
        return $this->hasMany(ItemPedidoCompra::class, 'pedido_id');
    }
}
