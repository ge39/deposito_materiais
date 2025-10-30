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
        'preco_venda',
        'data_compra',
        'validade_produto',
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
        'validade_produto' => 'date',
        'preco_custo' => 'decimal:2',
        'preco_venda' => 'decimal:2',
    ];

    // -------------------------------
    // RELACIONAMENTOS
    // -------------------------------
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'produto_id');
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
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

    // Estoque total somado dos lotes
    public function getEstoqueTotalAttribute()
    {
        return $this->lotes->sum('quantidade');
    }

    // -------------------------------
    // MUTATORS
    // -------------------------------
    // Evita salvar data atual automaticamente se o campo vier vazio
    public function setValidadeProdutoAttribute($value)
    {
        $this->attributes['validade_produto'] = empty($value) ? null : $value;
    }
}
