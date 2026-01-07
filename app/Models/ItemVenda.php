<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    use HasFactory;

    protected $table = 'item_vendas';

    protected $fillable = [
        'venda_id', 'produto_id', 'lote_id',
        'quantidade', 'preco_unitario', 'desconto', 'subtotal'
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal:2',
        'desconto' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // RELACIONAMENTOS
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
