<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaSistema extends Model
{
    protected $table = 'auditoria_sistema';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'operacao_uuid',
        'modulo',
        'entidade',
        'entidade_id',
        'acao',
        'descricao',
        'dados_antes',
        'dados_depois',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'usuario_id' => 'integer',
        'entidade_id' => 'integer',
        'dados_antes' => 'array',
        'dados_depois' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
