<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogBackup extends Model
{
    protected $table = 'logs_backup';

    protected $fillable = [
        'user_id',
        'acao',
        'arquivo',
        'driver',
        'status',
        'tamanho_bytes',
        'duracao_ms',
        'mensagem',
        'metadata',
        'iniciado_em',
        'finalizado_em',
    ];

    protected $casts = [
        'metadata' => 'array',
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
    ];
}