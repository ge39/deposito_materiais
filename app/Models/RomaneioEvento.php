<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RomaneioEvento extends Model
{
    protected $table = 'romaneio_eventos';

    protected $fillable = [
        'romaneio_id',

        'evento',
        'etapa',

        'status_anterior',
        'status_novo',

        'metodo_identificacao',

        'usuario_id',
        'funcionario_id',

        'terminal',
        'endereco_ip',

        'observacao',
        'dados',

        'ocorrido_em',
    ];

    protected $casts = [
        'dados' => 'array',
        'ocorrido_em' => 'datetime',
    ];

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'usuario_id'
        );
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'funcionario_id'
        );
    }
}