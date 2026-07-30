<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucaoLote extends Model
{
    use HasFactory;

    protected $table = 'devolucao_lotes';

    protected $fillable = [
        'devolucao_id',
        'romaneio_ocorrencia_avaliacao_id',
        'produto_id',
        'lote_id',
        'quantidade',
        'destino_estoque',
        'status_processamento',
        'movimentacao_entrada_id',
        'movimentacao_saida_id',
        'venda_id',
        'item_venda_id',
        'devolvido_por',
        'processado_por',
        'processado_em',
    ];

    protected $casts = [
        'devolucao_id' => 'integer',
        'romaneio_ocorrencia_avaliacao_id' => 'integer',
        'produto_id' => 'integer',
        'lote_id' => 'integer',
        'quantidade' => 'decimal:3',
        'movimentacao_entrada_id' => 'integer',
        'movimentacao_saida_id' => 'integer',
        'venda_id' => 'integer',
        'item_venda_id' => 'integer',
        'devolvido_por' => 'integer',
        'processado_por' => 'integer',
        'processado_em' => 'datetime',
    ];

    public function devolucao(): BelongsTo
    {
        return $this->belongsTo(Devolucao::class, 'devolucao_id');
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

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function itemVenda(): BelongsTo
    {
        return $this->belongsTo(ItemVenda::class, 'item_venda_id');
    }

    public function devolvidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devolvido_por');
    }

    public function processadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processado_por');
    }

    public function movimentacaoEntrada(): BelongsTo
    {
        return $this->belongsTo(
            EstoqueMovimentacao::class,
            'movimentacao_entrada_id'
        );
    }

    public function movimentacaoSaida(): BelongsTo
    {
        return $this->belongsTo(
            EstoqueMovimentacao::class,
            'movimentacao_saida_id'
        );
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(
            EstoqueMovimentacao::class,
            'devolucao_lote_id'
        )->orderBy('registrada_em');
    }

    public function estaPendente(): bool
    {
        return $this->status_processamento === 'Pendente';
    }

    public function estaProcessado(): bool
    {
        return $this->status_processamento === 'Processado';
    }

    public function estaCancelado(): bool
    {
        return $this->status_processamento === 'Cancelado';
    }
}