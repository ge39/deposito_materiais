<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagamentoVenda extends Model
{
    use HasFactory;

    protected $table = 'pagamentos_venda';

    protected $fillable = [
        'user_id',
        'venda_id',
        'caixa_id',
        'forma_pagamento',
        'bandeira',
        'valor',
        'parcelas',
        'status',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
}
