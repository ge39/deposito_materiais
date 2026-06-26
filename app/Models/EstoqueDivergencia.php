<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueDivergencia extends Model
{
    protected $table = 'estoque_divergencias';

    public $timestamps = false;

    protected $fillable = [
        'produto_id',
        'venda_id',
        'caixa_id',
        'quantidade_solicitada',
        'quantidade_atendida',
        'diferenca',
        'tipo',
        'observacao',
        'usuario_id',
        'created_at',
    ];

    protected $casts = [
        'quantidade_solicitada' => 'decimal:3',
        'quantidade_atendida' => 'decimal:3',
        'diferenca' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}