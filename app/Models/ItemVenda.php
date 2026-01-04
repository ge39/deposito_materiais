<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    use HasFactory;

    protected $table = 'item_vendas';

    protected $fillable = [
        'venda_id',
        'produto_id',
        'lote_id',
        'quantidade',
        'preco_unitario',
        'desconto',
    ];

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

    public function devolucoes() {
        return $this->hasMany(Devolucao::class, 'venda_item_id'); // <- isso está errado
    }
   



}

