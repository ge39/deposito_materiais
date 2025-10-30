<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItensPedidoCompra extends Model
{
    protected $table = 'itens_pedido_compras';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'pedido_compra_id', // novo campo de referência
        'quantidade',
        'preco_unitario',
        'total'
    ];

    public function pedido() {
        return $this->belongsTo(PedidoCompra::class, 'pedido_id');
    }

    public function produto() {
        return $this->belongsTo(Produto::class);
    }
}
