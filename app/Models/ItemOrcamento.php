<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOrcamento extends Model
{
    use HasFactory;

    protected $table = 'itens_orcamento';

    protected $fillable = [
        'orcamento_id',
        'produto_id',
        'lote_id',
        'quantidade',
        'quantidade_atendida',
        'quantidade_pendente',
        'status',
        'preco_unitario',
        'subtotal',
        'previsao_entrega'
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'quantidade_atendida' => 'decimal:2',
        'quantidade_pendente' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'previsao_entrega' => 'date',
    ];

    // Relacionamento com o orçamento principal
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    // Produto vinculado (opcional)
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
    
    // Campo dinâmico para exibir o nome do item (produto cadastrado ou texto livre)
    public function getNomeItemAttribute()
    {
        return $this->produto->nome ?? $this->descricao_cliente ?? 'Produto não especificado';
    }
}
