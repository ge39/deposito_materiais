<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RomaneioEquipe extends Model
{
    protected $table = 'romaneio_equipes';

    protected $fillable = [
        'romaneio_id',
        'motorista_id',
        'veiculo_id',
        'status',
        'atribuido_por',
        'atribuido_em',
        'liberado_por',
        'liberado_em',
        'motivo_substituicao',
        'observacao',
    ];

    protected $casts = [
        'atribuido_em' => 'datetime',
        'liberado_em' => 'datetime',
    ];

    public function romaneio()
    {
        return $this->belongsTo(Romaneio::class, 'romaneio_id');
    }

    public function motorista()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function atribuidoPor()
    {
        return $this->belongsTo(Funcionario::class, 'atribuido_por');
    }

    public function liberadoPor()
    {
        return $this->belongsTo(Funcionario::class, 'liberado_por');
    }
}