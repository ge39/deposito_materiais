<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaRastreamentoConfig extends Model
{
    protected $table = 'empresa_rastreamento_config';

    protected $fillable = [
        'empresa_id',
        'provider',
        'server_url',
        'traccar_group_id',
        'traccar_user_id',
        'traccar_email',
        'traccar_password',
        'enabled',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'traccar_group_id' => 'integer',
        'traccar_user_id' => 'integer',
        'traccar_password' => 'encrypted',
        'enabled' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'empresa_id',
            'id'
        );
    }
}