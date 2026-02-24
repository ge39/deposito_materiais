<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaDetalhe extends Model
{
    protected $table = 'auditoria_detalhes';

    protected $fillable = [
        'auditoria_id',
        'forma_pagamento',
        'total_sistema',
        'total_fisico',
        'diferenca',
        'status',
    ];

    protected $casts = [
        'total_sistema' => 'decimal:2',
        'total_fisico'  => 'decimal:2',
        'diferenca'     => 'decimal:2',
    ];

    public function auditoria()
    {
        return $this->belongsTo(\App\Models\AuditoriaCaixa::class, 'auditoria_id', 'id');
    }
}
