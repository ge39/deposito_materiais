<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoCaixa extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'caixa_id',
        'user_id',
        'tipo',
        'valor',
        'forma_pagamento',   // novo
        'bandeira',          // novo
        'origem_id',         // novo
        'observacao',
        'data_movimentacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'datetime',
    ];

    //Se quiser que data_movimentacao seja tratada como Carbon
    protected $dates = ['created_at','updated_at','data_movimentacao'];

    public $timestamps = true;

    /* =========================
       RELACIONAMENTOS
       ========================= */

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Venda relacionada (quando tipo = venda ou cancelamento_venda)
     * Usa origem_id
     */
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'origem_id');
    }

    // vincular uma movimentação a uma venda, sangria ou ajuste:
    public function origem()
    {
        return $this->morphTo(); // ou belongsTo dependendo do caso
    }   

}
