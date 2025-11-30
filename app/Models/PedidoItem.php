<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class PedidoItem extends Model
// {
//     use HasFactory;

//     protected $table = 'itens_pedido';

//     protected $fillable = [
//         'pedido_id',
//         'produto_id',
//         'quantidade',
//         'valor_unitario',
//         'subtotal',
//     ];

//     protected $casts = [
//         'quantidade' => 'integer',
//         'valor_unitario' => 'decimal:2',
//         'subtotal' => 'decimal:2',
//     ];

//     public function pedido()
//     {
//         return $this->belongsTo(PedidoCompra::class, 'pedido_id');
//     }

//     public function produto()
//     {
//         // return $this->belongsTo(Produto::class, 'produto_id');
//          return $this->belongsTo(Produto::class);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'subtotal',
        'validade',          // NOVO
        'numero_lote',       // NOVO
        'quantidade_recebida' // suporte para recebimento parcial (futuro)
    ];

    protected $casts = [
        'quantidade'           => 'integer',
        'quantidade_recebida'  => 'integer',
        'valor_unitario'       => 'decimal:2',
        'subtotal'             => 'decimal:2',
        'validade'             => 'date',
    ];

    /* ============================================================
       RELACIONAMENTOS 
    ============================================================ */

    public function pedido()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /* ============================================================
       MÉTODOS ESPECIAIS 
    ============================================================ */

    /**
     * Retorna TRUE se o item já foi totalmente recebido.
     */
    public function isTotalmenteRecebido()
    {
        return ($this->quantidade_recebida >= $this->quantidade);
    }

    /**
     * Retorna a quantidade ainda pendente no recebimento.
     */
    public function quantidadePendente()
    {
        return max(0, $this->quantidade - ($this->quantidade_recebida ?? 0));
    }

    /**
     * Define o número de lote automaticamente caso o usuário não informe.
     */
    public function gerarNumeroLotePadrao()
    {
        if ($this->numero_lote) {
            return $this->numero_lote;
        }

        return $this->produto_id . '-' . now()->format('YmdHis');
    }
}
