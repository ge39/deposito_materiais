<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AuditoriaCaixa extends Model
{
    protected $table = 'auditorias_caixa';

    protected $fillable = [
        'caixa_id',
        'user_id',
        'codigo_auditoria',
        'total_sistema',
        'total_fisico',
        'diferenca',
        'status',
        'observacao',
        'data_auditoria',
    ];

    protected $casts = [
        'total_sistema' => 'decimal:2',
        'total_fisico'  => 'decimal:2',
        'diferenca'     => 'decimal:2',
        'data_auditoria'=> 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function detalhes()
    {
        return $this->hasMany(\App\Models\AuditoriaDetalhe::class, 'auditoria_id', 'id');
    }


    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'auditoria_id');
    }
    
    public function auditoria()
    {
        return $this->belongsTo(AuditoriaCaixa::class, 'auditoria_id');
    }

    
}
