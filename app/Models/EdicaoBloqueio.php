<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdicaoBloqueio extends Model
{
    protected $table = 'edicao_bloqueios';

    protected $fillable = [
        'recurso_tipo',
        'recurso_id',
        'usuario_id',
        'sessao_hash',
        'token',
        'bloqueado_em',
        'renovado_em',
        'expira_em',
    ];

    protected $hidden = [
        'sessao_hash',
        'token',
    ];

    protected $casts = [
        'recurso_id' => 'integer',
        'usuario_id' => 'integer',
        'bloqueado_em' => 'datetime',
        'renovado_em' => 'datetime',
        'expira_em' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'usuario_id'
        );
    }

    public function expirou(): bool
    {
        return ! $this->expira_em
            || $this->expira_em->lessThanOrEqualTo(
                now()
            );
    }
}