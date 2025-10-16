<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItensPosVenda extends Model
{
    protected $fillable = [
        'pos_venda_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'total'
    ];

    // Relacionamento com PosVenda
    public function posVenda()
    {
        return $this->belongsTo(PosVenda::class, 'pos_venda_id');
    }

    // Relacionamento com Produto
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
