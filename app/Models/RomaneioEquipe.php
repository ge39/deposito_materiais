<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RomaneioEquipe extends Model
{
    protected $table = 'romaneio_equipes';

    protected $fillable = [
        'romaneio_id',
        'motorista_id',
        'ajudante_id',
        'veiculo_id',
        'status',
        'atribuido_por',
        'atribuido_em',
        'liberado_por',
        'liberado_em',
        'motivo_substituicao',
        'observacao',
    ];

    protected $casts = [
        'atribuido_em' => 'datetime',
        'liberado_em' => 'datetime',
    ];

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'motorista_id'
        );
    }

    public function ajudante(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'ajudante_id'
        );
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(
            Veiculo::class,
            'veiculo_id'
        );
    }

    public function usuarioAtribuicao(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'atribuido_por'
        );
    }

    public function usuarioLiberacao(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'liberado_por'
        );
    }

    public function estaAtiva(): bool
    {
        return $this->status === 'Ativa';
    }
}