<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id',
        'fornecedor_id',
        'quantidade',
        'preco_compra',
        'data_compra',
        'validade',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lote) {
            // Se não tiver data_compra, usa a data atual
            $lote->data_compra = $lote->data_compra ?? now();

            // Se não tiver validade, define +3 meses
            if (!$lote->validade) {
                $lote->validade = Carbon::parse($lote->data_compra)->addMonths(3);
            }
        });
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
