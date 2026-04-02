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
        'quantidade' => 'float',
        'quantidade_disponivel' => 'float',
        'quantidade_reservada' => 'float',
        'preco_compra' => 'float',
        'data_compra' => 'date',
        'validade_lote' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    public function getDisponivelRealAttribute()
    {
        return $this->quantidade_disponivel - $this->quantidade_reservada;
    }

    /*
    |--------------------------------------------------------------------------
    | REGRAS DE NEGÓCIO
    |--------------------------------------------------------------------------
    */

    public function podeReservar($qtd)
    {
        return $this->disponivel_real >= $qtd;
    }

    public function reservar($qtd)
    {
        $disponivel = $this->disponivel_real;

        if ($disponivel <= 0) {
            return 0;
        }

        $qtdReservada = min($disponivel, $qtd);

        $this->quantidade_reservada += $qtdReservada;
        $this->save();

        return $qtdReservada;
    }

    public function liberarReserva($qtd)
    {
        $this->quantidade_reservada -= $qtd;

        if ($this->quantidade_reservada < 0) {
            $this->quantidade_reservada = 0;
        }

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($lote) {

            if (empty($lote->numero_lote)) {
                $lote->numero_lote = now()->format('YmdHis') . rand(100, 999);
            }

            if (!isset($lote->quantidade_disponivel)) {
                $lote->quantidade_disponivel = $lote->quantidade ?? 0;
            }

            if (!isset($lote->quantidade_reservada)) {
                $lote->quantidade_reservada = 0;
            }

            if (!isset($lote->status)) {
                $lote->status = 1;
            }
        });
    }
}