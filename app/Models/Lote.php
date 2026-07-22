<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'numero_lote',
        'pedido_compra_id',
        'produto_id',
        'fornecedor_id',
        'quantidade',
        'quantidade_disponivel',
        'quantidade_reservada',
        'preco_compra',
        'data_compra',
        'lancado_por',
        'validade_lote',
        'status',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'quantidade_disponivel' => 'decimal:3',
        'quantidade_reservada' => 'decimal:3',
        'preco_compra' => 'decimal:2',
        'data_compra' => 'date',
        'validade_lote' => 'date',
        'status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function produto()
    {
        return $this->belongsTo(
            Produto::class,
            'produto_id'
        );
    }

    public function pedidoCompra()
    {
        return $this->belongsTo(
            PedidoCompra::class,
            'pedido_compra_id'
        );
    }

    public function fornecedor()
    {
        return $this->belongsTo(
            Fornecedor::class,
            'fornecedor_id'
        );
    }

    public function itensOrcamento()
    {
        return $this->hasMany(
            ItemOrcamentoLote::class,
            'lote_id'
        );
    }

    public function itens()
    {
        return $this->belongsToMany(
            ItemOrcamento::class,
            'item_orcamento_lotes',
            'lote_id',
            'item_orcamento_id'
        )
            ->withPivot([
                'quantidade_reservada',
                'quantidade_atendida',
            ])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getDisponivelRealAttribute(): float
    {
        return round(
            (float) $this->quantidade_disponivel
            - (float) $this->quantidade_reservada,
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REGRAS DE NEGÓCIO
    |--------------------------------------------------------------------------
    */

    public function podeReservar(float $quantidade): bool
    {
        return $this->disponivel_real >= $quantidade;
    }

    public function reservar(float $quantidade): float
    {
        $disponivel = $this->disponivel_real;

        if ($disponivel <= 0) {
            return 0.0;
        }

        $quantidadeReservada = round(
            min($disponivel, $quantidade),
            3
        );

        $this->quantidade_reservada = round(
            (float) $this->quantidade_reservada
            + $quantidadeReservada,
            3
        );

        $this->save();

        return $quantidadeReservada;
    }

    public function liberarReserva(float $quantidade): void
    {
        $novaQuantidade = round(
            (float) $this->quantidade_reservada
            - $quantidade,
            3
        );

        $this->quantidade_reservada = max(
            0,
            $novaQuantidade
        );

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS DO MODEL
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Lote $lote): void {
            if (empty($lote->numero_lote)) {
                $lote->numero_lote =
                    now()->format('YmdHis')
                    . random_int(100, 999);
            }

            if ($lote->quantidade_disponivel === null) {
                $lote->quantidade_disponivel =
                    $lote->quantidade ?? 0;
            }

            if ($lote->quantidade_reservada === null) {
                $lote->quantidade_reservada = 0;
            }

            if ($lote->status === null) {
                $lote->status = 1;
            }
        });
    }
}