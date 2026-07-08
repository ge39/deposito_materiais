<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Romaneio extends Model
{
    protected $table = 'romaneios';

    protected $fillable = [
        'entrega_id',
        'criado_por',
        'codigo',
        'codigo_romaneio',
        'token_abertura',
        'token_fechamento',
        'status',
        'veiculo_id',
        'motorista_id',
        'iniciado_por',
        'carregado_por',
        'conferido_por',
        'finalizado_por',
        'data_emissao',
        'data_inicio_separacao',
        'data_fim_separacao',
        'data_inicio_carregamento',
        'data_fim_carregamento',
        'data_saida',
        'data_baixa',
        'percentual_carregado',
        'observacao',
        'motivo_cancelamento',
        'cancelado_em',
        'cancelado_por',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_inicio_separacao' => 'datetime',
        'data_fim_separacao' => 'datetime',
        'data_inicio_carregamento' => 'datetime',
        'data_fim_carregamento' => 'datetime',
        'data_saida' => 'datetime',
        'data_baixa' => 'datetime',
        'cancelado_em' => 'datetime',
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
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_id');
    }

    public function criador()
    {
        return $this->belongsTo(Funcionario::class, 'criado_por');
    }

    public function iniciador()
    {
        return $this->belongsTo(Funcionario::class, 'iniciado_por');
    }

    public function carregador()
    {
        return $this->belongsTo(Funcionario::class, 'carregado_por');
    }

    public function conferente()
    {
        return $this->belongsTo(Funcionario::class, 'conferido_por');
    }

    public function finalizador()
    {
        return $this->belongsTo(Funcionario::class, 'finalizado_por');
    }

    public function cancelador()
    {
        return $this->belongsTo(Funcionario::class, 'cancelado_por');
    }
    
    public function equipes()
    {
        return $this->hasMany(RomaneioEquipe::class, 'romaneio_id');
    }

    public function equipeAtiva()
    {
        return $this->hasOne(RomaneioEquipe::class, 'romaneio_id')
            ->where('status', 'Ativa');
    }
}