<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'pedido_compras';

    protected $fillable = [
        'user_id',
        'fornecedor_id',
        'data_pedido',
        'status',
        'total',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'total' => 'decimal:2',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
