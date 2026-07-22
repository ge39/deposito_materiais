<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RomaneioItem extends Model
{
    protected $table = 'romaneio_itens';

    protected $fillable = [
        'romaneio_id',
        'romaneio_item_origem_id',
        'entrega_item_id',
        'ordem',

        'quantidade_prevista',
        'quantidade_separada',
        'quantidade_conferida_separacao',
        'quantidade_conferida',
        'quantidade_carregada',
        'quantidade_conferida_saida',

        'quantidade_entregue',
        'quantidade_devolvida',
        'quantidade_recusada',
        'quantidade_avariada',
        'quantidade_perdida',

        'motivo_saldo',
        'destino_saldo',
        'data_prevista_saldo',
        'saldo_decidido_por',
        'observacao_saldo',
        'saldo_liberado_novo_romaneio',

        'status',

        'separado_por',
        'separado_em',

        'conferencia_separacao_por',
        'conferencia_separacao_em',

        'carregado_por',
        'carregado_em',

        'conferencia_saida_por',
        'conferencia_saida_em',

        'conferido_por',
        'conferido_em',

        'retorno_conferido_por',
        'retorno_conferido_em',

        'observacao',
    ];

    protected $casts = [
        'quantidade_prevista' => 'decimal:2',
        'quantidade_separada' => 'decimal:2',
        'quantidade_conferida_separacao' => 'decimal:2',
        'quantidade_conferida' => 'decimal:2',
        'quantidade_carregada' => 'decimal:2',
        'quantidade_conferida_saida' => 'decimal:2',

        'quantidade_entregue' => 'decimal:2',
        'quantidade_devolvida' => 'decimal:2',
        'quantidade_recusada' => 'decimal:2',
        'quantidade_avariada' => 'decimal:2',
        'quantidade_perdida' => 'decimal:2',

        'saldo_liberado_novo_romaneio' => 'boolean',

        'data_prevista_saldo' => 'datetime',
        'separado_em' => 'datetime',
        'conferencia_separacao_em' => 'datetime',
        'carregado_em' => 'datetime',
        'conferencia_saida_em' => 'datetime',
        'conferido_em' => 'datetime',
        'retorno_conferido_em' => 'datetime',
    ];

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(
            Romaneio::class,
            'romaneio_id'
        );
    }

    public function origemSaldo(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'romaneio_item_origem_id'
        );
    }

    public function itensGeradosPeloSaldo(): HasMany
    {
        return $this->hasMany(
            self::class,
            'romaneio_item_origem_id'
        );
    }

    public function entregaItem(): BelongsTo
    {
        return $this->belongsTo(
            EntregaItem::class,
            'entrega_item_id'
        );
    }

    public function usuarioDecisaoSaldo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'saldo_decidido_por'
        );
    }

    public function separador(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'separado_por'
        );
    }

    public function conferenteSeparacao(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'conferencia_separacao_por'
        );
    }

    public function carregador(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'carregado_por'
        );
    }

    public function conferenteSaida(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'conferencia_saida_por'
        );
    }

    public function conferente(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'conferido_por'
        );
    }

    public function conferenteRetorno(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'retorno_conferido_por'
        );
    }

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrencia::class,
            'romaneio_item_id'
        );
    }

    public function getQuantidadeSaldoAttribute(): float
    {
        return max(
            round(
                (float) $this->quantidade_prevista
                - (float) $this->quantidade_separada,
                2
            ),
            0
        );
    }

    public function possuiSaldoPendente(): bool
    {
        return $this->quantidade_saldo >= 0.001;
    }

    public function saldoAguardaEstoque(): bool
    {
        return $this->possuiSaldoPendente()
            && $this->destino_saldo === 'Aguardar_estoque'
            && ! $this->saldo_liberado_novo_romaneio;
    }

    public function saldoDisponivelParaNovoRomaneio(): bool
    {
        return $this->possuiSaldoPendente()
            && $this->destino_saldo === 'Novo_romaneio'
            && $this->saldo_liberado_novo_romaneio;
    }

    public function quantidadePrestadaConta(): float
    {
        return
            (float) $this->quantidade_entregue
            + (float) $this->quantidade_devolvida
            + (float) $this->quantidade_recusada
            + (float) $this->quantidade_avariada
            + (float) $this->quantidade_perdida;
    }

    public function quantidadePendenteRetorno(): float
    {
        $pendente =
            (float) $this->quantidade_carregada
            - $this->quantidadePrestadaConta();

        return max(
            round($pendente, 2),
            0
        );
    }

    public function prestacaoContasConciliada(): bool
    {
        return abs(
            (float) $this->quantidade_carregada
            - $this->quantidadePrestadaConta()
        ) < 0.001;
    }

    public function possuiDivergenciaSeparacao(): bool
    {
        return abs(
            (float) $this->quantidade_separada
            - (float) $this->quantidade_conferida_separacao
        ) >= 0.001;
    }

    public function possuiDivergenciaSaida(): bool
    {
        return abs(
            (float) $this->quantidade_carregada
            - (float) $this->quantidade_conferida_saida
        ) >= 0.001;
    }
}