<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
         'numero_lote',
        'pedido_compra_id',
        'produto_id',
        'fornecedor_id',
        'pedido_compra_id',
        'quantidade',
        'preco_compra',
        'data_compra',
        'validade_lote',
       
        // timestamps são gerenciados automaticamente
    ];

    protected $casts = [
        'data_compra' => 'date',
        'validade_lote'    => 'date',
        'preco_compra'=> 'decimal:2',
        'quantidade'  => 'decimal:2',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    protected static function booted()
    {
        static::creating(function ($lote) {
            if (empty($lote->numero_lote)) {
                $lote->numero_lote = now()->format('Ymd') . $lote->produto_id . rand(10, 99);
            }
        });
    }
}
