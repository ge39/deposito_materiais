<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo_barras',
        'sku',
        'descricao',
        'categoria_id',
        'fornecedor_id',
        'unidade_medida_id',
        'marca_id',
        'quantidade_estoque',
        'estoque_total',
        'estoque_minimo',
        'preco_custo',
        'data_compra',
        'validade',
        'preco_venda',
        'peso',
        'largura',
        'altura',
        'profundidade',
        'localizacao_estoque',
        'imagem',
        'ativo',
    ];
    
    protected $casts = [
    'data_compra' => 'date',
    'validade' => 'date',
    'preco_custo' => 'decimal:2',
    'preco_venda' => 'decimal:2',
];

    // -------------------------------
    // RELACIONAMENTOS
    // -------------------------------
    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }
    
    public function unidade()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_id');
    }

    // Quantidade total automática
    public function getEstoqueTotalAttribute()
    {
        return $this->lotes->sum('quantidade');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    // public function unidadeMedida()
    // {
    //     return $this->belongsTo(UnidadeMedida::class);
    // }
}
