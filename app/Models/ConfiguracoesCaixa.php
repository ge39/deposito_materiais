<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracoesCaixa extends Model
{
    protected $table = 'configuracoes_caixa';

    protected $fillable = [
        'empresa_id',
        'limite_sangria',
        'percentual_bloqueio',
        'bloqueio_ativo',
    ];

    protected $casts = [
        'limite_sangria' => 'decimal:2',
        'percentual_bloqueio' => 'decimal:2',
        'bloqueio_ativo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id');
    }
}