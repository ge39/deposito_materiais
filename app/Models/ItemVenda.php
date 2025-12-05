<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    protected $fillable = [
        'venda_id',
        'data_venda',
        'produto_id',
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
    public function devolucoes() {
        return $this->hasMany(Devolucao::class, 'venda_item_id'); // <- isso está errado
    }
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }



}

