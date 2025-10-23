<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaItem extends Model
{
    protected $table = 'venda_itens';

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    // App/Models/VendaItem.php
    public function devolucoes()
    {
        return $this->hasMany(Devolucao::class, 'venda_item_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
