<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    protected $fillable = [
        'venda_id',
        'produto_id',
        'lote_id',
        'quantidade',
        'preco_unitario',
        'desconto'
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}

