<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido_compras';

    protected $fillable = [
        'pedido_id',     // FK correta
        'produto_id',
        'quantidade',
        'preco_unitario',
        'total',
        'validade',      // caso esteja usando validade por item
        'numero_lote',   // caso lote seja informado no pedido
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'validade' => 'date',
    ];

    // RELACIONAMENTOS ------------------------------------
    public function pedido()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
