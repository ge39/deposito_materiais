<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstoqueMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'devolucao_id',
        'devolucao_lote_id',
        'romaneio_ocorrencia_avaliacao_id',
        'produto_id',
        'lote_id',
        'tipo',
        'natureza',
        'quantidade',
        'saldo_produto_anterior',
        'saldo_produto_posterior',
        'saldo_lote_anterior',
        'saldo_lote_posterior',
        'descricao',
        'movimentacao_origem_id',
        'registrada_por',
        'registrada_em',
        'estornada_por',
        'estornada_em',
        'motivo_estorno',
    ];

    protected $casts = [
        'devolucao_id' => 'integer',
        'devolucao_lote_id' => 'integer',
        'romaneio_ocorrencia_avaliacao_id' => 'integer',
        'produto_id' => 'integer',
        'lote_id' => 'integer',
        'quantidade' => 'decimal:3',
        'saldo_produto_anterior' => 'decimal:3',
        'saldo_produto_posterior' => 'decimal:3',
        'saldo_lote_anterior' => 'decimal:3',
        'saldo_lote_posterior' => 'decimal:3',
        'movimentacao_origem_id' => 'integer',
        'registrada_por' => 'integer',
        'registrada_em' => 'datetime',
        'estornada_por' => 'integer',
        'estornada_em' => 'datetime',
    ];

    public function devolucao(): BelongsTo
    {
        return $this->belongsTo(Devolucao::class, 'devolucao_id');
    }

    public function devolucaoLote(): BelongsTo
    {
        return $this->belongsTo(DevolucaoLote::class, 'devolucao_lote_id');
    }

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioOcorrenciaAvaliacao::class,
            'romaneio_ocorrencia_avaliacao_id'
        );
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function origem(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'movimentacao_origem_id'
        );
    }

    public function decorrentes(): HasMany
    {
        return $this->hasMany(
            self::class,
            'movimentacao_origem_id'
        );
    }

    public function registradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }

    public function estornadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estornada_por');
    }

    public function estaEstornada(): bool
    {
        return ! empty($this->estornada_em);
    }
}