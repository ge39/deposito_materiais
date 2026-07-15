<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RomaneioOcorrencia extends Model
{
    protected $table = 'romaneio_ocorrencias';

    protected $fillable = [
        'romaneio_id',
        'entrega_id',
        'romaneio_item_id',
        'entrega_item_id',

        'categoria',
        'tipo',
        'criticidade',
        'etapa',
        'descricao',

        'bloqueia_operacao',
        'exige_autorizacao',

        'autorizada_por',
        'autorizada_em',
        'justificativa_autorizacao',

        'status',

        'registrada_por',
        'registrada_em',

        'resolvida_por',
        'resolvida_em',
        'solucao',
    ];

    protected $casts = [
        'bloqueia_operacao' => 'boolean',
        'exige_autorizacao' => 'boolean',

        'autorizada_em' => 'datetime',
        'registrada_em' => 'datetime',
        'resolvida_em' => 'datetime',
    ];

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function romaneioItem(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioItem::class,
            'romaneio_item_id'
        );
    }

    public function entregaItem(): BelongsTo
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_id'
        );
    }

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'autorizada_por'
        );
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrada_por'
        );
    }

    public function resolvedor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'resolvida_por'
        );
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrenciaAnexo::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function estaAberta(): bool
    {
        return in_array(
            $this->status,
            [
                'Aberta',
                'Em_analise',
            ],
            true
        );
    }

    public function estaResolvida(): bool
    {
        return $this->status === 'Resolvida';
    }

    public function exigeAutorizacaoPendente(): bool
    {
        return
            $this->exige_autorizacao &&
            empty($this->autorizada_por);
    }

    public function bloqueiaFluxo(): bool
    {
        return
            $this->estaAberta() &&
            (
                $this->bloqueia_operacao ||
                $this->exigeAutorizacaoPendente()
            );
    }
}