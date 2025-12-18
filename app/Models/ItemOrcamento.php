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
        'quantidade',
        'preco_unitario', // ← campo correto
        'subtotal'
    ];

    protected $casts = [
        'quantidade'     => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    // Relacionamento com o orçamento principal
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    // Produto vinculado (opcional)
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Fornecedor vinculado (opcional)
    // public function fornecedor()
    // {
    //     return $this->belongsTo(Fornecedor::class);
    // }

    // Campo dinâmico para exibir o nome do item (produto cadastrado ou texto livre)
    public function getNomeItemAttribute()
    {
        return $this->produto->nome ?? $this->descricao_cliente ?? 'Produto não especificado';
    }
}
