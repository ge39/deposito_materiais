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
        'validade'       => 'date',
        'total'          => 'decimal:2',
        'ativo'          => 'boolean',
    ];

    /* =========================
     | RELACIONAMENTOS
     ========================= */

    /** Cliente do orçamento */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Usuário que está editando o orçamento */
    public function editor()
    {
        return $this->belongsTo(User::class, 'editando_por');
    }

    /** Fornecedor (caso aplicável) */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /** Itens do orçamento */
    public function itens()
    {
        return $this->hasMany(ItemOrcamento::class, 'orcamento_id');
    }

    /** Unidade de medida (se usada no cabeçalho) */
    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    /* =========================
     | SCOPES ÚTEIS PARA O PDV
     ========================= */

    /** Orçamentos ativos */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /** Orçamento pelo código */
    public function scopeCodigo($query, $codigo)
    {
        return $query->where('codigo_orcamento', $codigo);
    }

    /** Orçamentos não faturados */
    public function scopeNaoFaturado($query)
    {
        return $query->where('status', '!=', 'faturado');
    }
}
