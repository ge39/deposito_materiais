<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCarroceria extends Model
{
    protected $table = 'tipos_carroceria';

    protected $fillable = [
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function classes()
    {
        return $this->belongsToMany(
            ClasseVeiculo::class,
            'classe_veiculo_carroceria',
            'tipo_carroceria_id',
            'classe_veiculo_id'
        )->withPivot('ativo');
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'tipo_carroceria_id');
    }
}