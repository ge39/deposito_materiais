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
        'total_sistema'  => 'decimal:2',
        'total_fisico'   => 'decimal:2',
        'diferenca'      => 'decimal:2',
        'data_auditoria' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    // Caixa auditado
    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    // Usuário que realizou a auditoria
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Detalhamento por forma de pagamento
    public function detalhes()
    {
        return $this->hasMany(AuditoriaDetalhe::class, 'auditoria_id');
    }

    // Movimentações vinculadas à auditoria (correções)
    public function movimentacoesAuditoria()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'auditoria_id')
            ->where('tipo', 'auditoria')
            ->orderBy('data_movimentacao');
    }

    // Abertura do caixa (quem abriu)
    public function abertura()
    {
        return $this->hasOne(MovimentacaoCaixa::class, 'caixa_id', 'caixa_id')
            ->where('tipo', 'abertura')
            ->oldest('data_movimentacao');
    }
}