<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Funcionario;
use App\Models\Frota;
use App\Models\Romaneio;
use Illuminate\Support\Facades\DB;

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
    'data_realizada',
    'status',
    'status_alterado_em',
    'cobrar_frete',
    'valor_frete',
    'tipo_entrega',
    'usar_endereco_cliente',
    'endereco_entrega',
    'latitude_entrega',
    'longitude_entrega',
    'coordenada_confirmada',
    'responsavel_recebimento',
    'telefone_recebimento',
    'motorista_id',
    'veiculo_id',
    'ordem_rota',
    'observacao',
    'entrega_origem_id',
    'entrega_principal_id',
    ];

    protected $casts = [
        'data_prevista' => 'date',
        'data_prevista_entrega' => 'date',
        'data_realizada' => 'date',
        'usar_endereco_cliente' => 'boolean',
        'cobrar_frete' => 'boolean',
        'valor_frete' => 'decimal:2',
        'ordem_rota' => 'integer',
        'latitude_entrega' => 'decimal:7',
        'longitude_entrega' => 'decimal:7',
        'coordenada_confirmada' => 'boolean',
        'status_alterado_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (): void {
            DB::statement(
                'SET @entrega_usuario_id = ?',
                [auth()->id()]
            );
        });

        static::saved(function (): void {
            DB::statement('SET @entrega_usuario_id = NULL');
        });
    }

    public function entregaOrigem()
    {
        return $this->belongsTo(
            self::class,
            'entrega_origem_id'
        );
    }

    public function entregaPrincipal()
    {
        return $this->belongsTo(
            self::class,
            'entrega_principal_id'
        );
    }

    public function entregasFilhas()
    {
        return $this->hasMany(
            self::class,
            'entrega_origem_id'
        );
    }

    public function entregasFracionadas()
    {
        return $this->hasMany(
            self::class,
            'entrega_principal_id'
        );
    }

    public function bloqueioEdicaoAtivo()
    {
        return $this->hasOne(
            EdicaoBloqueio::class,
            'recurso_id'
        )
            ->where(
                'recurso_tipo',
                'entrega'
            )
            ->where(
                'expira_em',
                '>',
                now()
            );
    }

    public function motorista()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function romaneio()
    {
        return $this->hasOne(Romaneio::class, 'entrega_id');
    }

    public function itens()
    {
        return $this->hasMany(EntregaItem::class, 'entrega_id');
    }
    
    public function itensVenda()
    {
        return $this->hasMany(EntregaItem::class, 'entrega_id')
            ->whereNotNull('venda_id');
    }

    public function itemVenda()
    {
        return $this->hasMany(ItemVenda::class, 'venda_id', 'venda_id');
    }

    public function itemOrcamento()
    {
        return $this->hasMany(ItemOrcamento::class, 'orcamento_id', 'orcamento_id');
    }

    public function vendaItem()
    {
        return $this->hasMany(VendaItem::class, 'venda_id', 'venda_id');
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