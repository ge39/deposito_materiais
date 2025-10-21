<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValeCompra extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'devolucao_id',
        'valor',
        'valor_utilizado',
        'codigo',
        'status',
        'data_utilizacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_utilizado' => 'decimal:2',
    ];

    public function getSaldoAttribute()
    {
        return $this->valor - $this->valor_utilizado;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function devolucao()
    {
        return $this->belongsTo(Devolucao::class);
    }
}
