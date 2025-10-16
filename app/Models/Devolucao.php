<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucao extends Model
{
    protected $fillable = [
        'venda_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'tipo',
        'produto_troca_id',
        'diferenca',
        'observacoes'
    ];

    public function venda() {
        return $this->belongsTo(Venda::class);
    }

    public function produto() {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function produtoTroca() {
        return $this->belongsTo(Produto::class, 'produto_troca_id');
    }
}
