<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemVenda;
use App\Models\ItemOrcamento;

class EntregaItem extends Model
{
    protected $table = 'entrega_itens';

    protected $fillable = [
        'entrega_id',
        'item_orcamento_id',
        'venda_item_id',
        'quantidade_prevista',
        'quantidade_entregue',
        'status',
        'observacao',
    ];

    protected $casts = [
        'quantidade_prevista' => 'decimal:2',
        'quantidade_entregue' => 'decimal:2',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class, 'entrega_id');
    }

    public function vendaItem()
    {
        return $this->belongsTo(ItemVenda::class, 'venda_item_id');
    }

    public function itemOrcamento()
    {
        return $this->belongsTo(ItemOrcamento::class, 'item_orcamento_id');
    }

    public function getSaldoAttribute()
    {
        return max(0, $this->quantidade_prevista - $this->quantidade_entregue);
    }

    public function getPercentualEntregueAttribute()
    {
        if ($this->quantidade_prevista <= 0) {
            return 0;
        }

        return round(($this->quantidade_entregue / $this->quantidade_prevista) * 100, 2);
    }
}