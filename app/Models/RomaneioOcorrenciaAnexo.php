<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RomaneioOcorrenciaAnexo extends Model
{
    protected $table = 'romaneio_ocorrencia_anexos';

    protected $fillable = [
        'romaneio_ocorrencia_id',
        'tipo',
        'nome_original',
        'caminho',
        'mime_type',
        'tamanho_bytes',
        'enviado_por',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
    ];

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioOcorrencia::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function usuarioEnvio(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'enviado_por'
        );
    }
}