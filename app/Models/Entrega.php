<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Funcionario;
use App\Models\Frota;
use App\Models\Romaneio;

class Entrega extends Model
{
    protected $table = 'entregas';

    protected $fillable = [
       'orcamento_id',
        'venda_id',
        'codigo_entrega',
        'data_prevista',
        'data_prevista_entrega',
        'periodo_entrega',
        'observacao_entrega',
        'status',
        'tipo_entrega',
        'usar_endereco_cliente',
        'endereco_entrega',
        'responsavel_recebimento',
        'telefone_recebimento',
        'cobrar_frete',
        'valor_frete',
        'motorista_id',
        'veiculo_id',
        'data_realizada',
    ];

    protected $casts = [
    'data_prevista' => 'date',
    'data_prevista_entrega' => 'date',
    'data_realizada' => 'date',
    'usar_endereco_cliente' => 'boolean',
    'cobrar_frete' => 'boolean',
    'valor_frete' => 'decimal:2',
    ];

    public function motorista()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Frota::class, 'veiculo_id');
    }

    public function romaneio()
    {
        return $this->hasOne(Romaneio::class, 'entrega_id');
    }

    public function itens()
    {
        return $this->hasMany(EntregaItem::class, 'entrega_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

  public function scopePendentes($query)
    {
        return $query->where('status', 'Pendente');
    }

    public function scopePendentesPagamento($query)
    {
        return $query->where('status', 'Pendente_pagamento');
    }

    public function scopeAguardandoFaturamento($query)
    {
        return $query->where('status', 'Aguardando_faturamento');
    }

    public function scopeAguardandoSeparacao($query)
    {
        return $query->where('status', 'Aguardando_separacao');
    }

    public function scopeSeparando($query)
    {
        return $query->where('status', 'Separando');
    }

    public function scopeCarregadas($query)
    {
        return $query->where('status', 'Carregado');
    }

    public function scopeEmRota($query)
    {
        return $query->where('status', 'Em_rota');
    }

    public function scopeEntregues($query)
    {
        return $query->where('status', 'Entregue');
    }

    public function getEstaFinalizadaAttribute()
    {
        return in_array($this->status, [
            'Entregue',
            'Cancelado',
            'Devolvido',
        ], true);
    }
}