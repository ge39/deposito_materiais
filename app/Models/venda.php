<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $table = 'vendas';

    public function itens()
    {
        return $this->hasMany(ItemVenda::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
