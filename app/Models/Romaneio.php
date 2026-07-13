<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Romaneio extends Model
{
    protected $table = 'romaneios';

    protected $fillable = [
        'entrega_id',
        'criado_por',
        'codigo_romaneio',
        'token_abertura',
        'token_fechamento',
        'status',
        'veiculo_id',
        'motorista_id',
        'iniciado_por',
        'carregado_por',
        'conferido_por',
        'finalizado_por',
        'data_emissao',
        'impresso_em',
        'impresso_por',
        'data_inicio_separacao',
        'data_fim_separacao',
        'data_inicio_carregamento',
        'data_fim_carregamento',
        'data_saida',
        'data_baixa',
        'percentual_carregado',
        'observacao',
        'motivo_cancelamento',
        'cancelado_em',
        'cancelado_por',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'impresso_em' => 'datetime',
        'data_inicio_separacao' => 'datetime',
        'data_fim_separacao' => 'datetime',
        'data_inicio_carregamento' => 'datetime',
        'data_fim_carregamento' => 'datetime',
        'data_saida' => 'datetime',
        'data_baixa' => 'datetime',
        'cancelado_em' => 'datetime',
        'percentual_carregado' => 'decimal:2',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function itens(): HasMany
    {
        return $this->hasMany(
            RomaneioItem::class,
            'romaneio_id'
        );
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(
            Veiculo::class,
            'veiculo_id'
        );
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'motorista_id'
        );
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'criado_por'
        );
    }

    public function iniciador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'iniciado_por'
        );
    }

    public function carregador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'carregado_por'
        );
    }

    public function conferente(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'conferido_por'
        );
    }

    public function finalizador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'finalizado_por'
        );
    }

    public function impressor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'impresso_por'
        );
    }

    public function cancelador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelado_por'
        );
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(
            RomaneioEquipe::class,
            'romaneio_id'
        );
    }

    public function equipeAtiva(): HasOne
    {
        return $this->hasOne(
            RomaneioEquipe::class,
            'romaneio_id'
        )->where(
            'status',
            'Ativa'
        );
    }
}