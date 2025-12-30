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
        'origem_id',
        'observacao',
        'data_movimentacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'datetime',
    ];

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
}
