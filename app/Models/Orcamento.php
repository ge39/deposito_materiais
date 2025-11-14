<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';

    protected $fillable = [
        'cliente_id',
        'data_orcamento',
        'codigo_orcamento',
        'validade',
        'status',
        'observacoes',
        'total',
        'ativo',
    ];

    protected $casts = [
        'data_orcamento' => 'date',
        'validade' => 'date',
    ];

   

    /** Relacionamento com cliente */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Relacionamento com fornecedor */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /** Relacionamento com itens do orçamento */
    public function itens()
    {
        return $this->hasMany(ItemOrcamento::class);
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    
}
