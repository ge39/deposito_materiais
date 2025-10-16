<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
        'frota_id',
        'funcionario_id',
        'data_entrega',
        'endereco_entrega',
        'status',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function frota()
    {
        return $this->belongsTo(Frota::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
