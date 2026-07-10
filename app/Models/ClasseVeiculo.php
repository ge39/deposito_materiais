<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasseVeiculo extends Model
{
    protected $table = 'classes_veiculo';

    protected $fillable = [
        'tipo_veiculo_id',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function tipoVeiculo()
    {
        return $this->belongsTo(TipoVeiculo::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function carrocerias()
    {
        return $this->belongsToMany(
            TipoCarroceria::class,
            'classe_veiculo_carroceria',
            'classe_veiculo_id',
            'tipo_carroceria_id'
        )->withPivot('ativo');
    }
}