<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVeiculo extends Model
{
    protected $table = 'tipos_veiculo';

    protected $fillable = [
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(ClasseVeiculo::class, 'tipo_veiculo_id');
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'tipo_veiculo_id');
    }
}