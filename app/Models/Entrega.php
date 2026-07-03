<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'data_prevista' => 'date',
        'data_realizada' => 'date',
        'usar_endereco_cliente' => 'boolean',
    ];

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

    public function scopeFaturadas($query)
    {
        return $query->where('status', 'Faturado');
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