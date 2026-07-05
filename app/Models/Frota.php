<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frota extends Model
{
    use HasFactory;

    protected $table = 'frotas';

    protected $fillable = [
        'placa',
        'modelo',
        'marca',
        'ano',
        'capacidade_kg',
        'capacidade_volume_m3',
        'comprimento_carroceria',
        'tipo',
        'status',
        'ativo',
        'observacoes',
    ];

    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'veiculo_id');
    }
}