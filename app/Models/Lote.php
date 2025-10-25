<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'produto_id',
        'fornecedor_id',
        'quantidade',
        'preco_compra',
        'data_compra',
        'validade',
        'numero_lote',   // novo campo
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data_compra' => 'date',
        'validade'    => 'date',
        'preco_compra'=> 'decimal:2',
        'quantidade'  => 'decimal:2',
    ];

    // -------------------------------
    // RELACIONAMENTOS
    // -------------------------------
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    // -------------------------------
    // EVENTOS ELOQUENT
    // -------------------------------
    protected static function booted()
    {
        static::creating(function ($lote) {
            // Gera número do lote automaticamente se não informado
            if (empty($lote->numero_lote)) {
                $lote->numero_lote = now()->format('Ymd') . $lote->produto_id;
            }
        });
    }
}
