<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Romaneio extends Model
{
    protected $table = 'romaneios';

    protected $fillable = [
        'entrega_id',
        'codigo',
        'codigo_romaneio',
        'status',
        'veiculo_id',
        'motorista_id',
        'carregado_por',
        'conferido_por',
        'data_emissao',
        'data_inicio_carregamento',
        'data_fim_carregamento',
        'data_saida',
        'data_baixa',
        'percentual_carregado',
        'observacao',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_inicio_carregamento' => 'datetime',
        'data_fim_carregamento' => 'datetime',
        'data_saida' => 'datetime',
        'data_baixa' => 'datetime',
        'percentual_carregado' => 'decimal:2',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class, 'entrega_id');
    }

    public function itens()
    {
        return $this->hasMany(RomaneioItem::class, 'romaneio_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Frota::class, 'veiculo_id');
    }

    public function motorista()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_id');
    }

    public function carregador()
    {
        return $this->belongsTo(Funcionario::class, 'carregado_por');
    }

    public function conferente()
    {
        return $this->belongsTo(Funcionario::class, 'conferido_por');
    }
}