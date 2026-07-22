<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucaoLog extends Model
{
    use HasFactory;

    protected $table = 'devolucao_logs';

    protected $fillable = [
        'devolucao_id',
        'acao',
        'status_anterior',
        'status_novo',
        'descricao',
        'usuario',
        'registrado_por',
        'registrado_em',
        'observacao',
        'motivo_rejeicao',
    ];

    protected $casts = [
        'registrado_em' => 'datetime',
    ];

    public function devolucao(): BelongsTo
    {
        return $this->belongsTo(
            Devolucao::class,
            'devolucao_id'
        );
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por'
        );
    }
}