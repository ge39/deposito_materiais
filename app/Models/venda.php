<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'funcionario_id',
        'data_venda',
        'total',
        'status',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class);
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class);
    }

    public function posVendas()
    {
        return $this->hasMany(PosVenda::class);
    }
}
