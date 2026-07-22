<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RomaneioOcorrenciaHistorico extends Model
{
    protected $table = 'romaneio_ocorrencia_historicos';

    protected $fillable = [
        'romaneio_ocorrencia_id',
        'status_anterior',
        'status_novo',
        'evento',
        'descricao',
        'registrado_por',
        'registrado_em',
    ];

    protected $casts = [
        'registrado_em' => 'datetime',
    ];

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioOcorrencia::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por'
        );
    }
}