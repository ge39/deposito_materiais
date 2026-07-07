<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;

class Orcamento extends Model
{
    use HasFactory;

    const AGUARDANDO_APROVACAO = 'Aguardando Aprovacao';
    const AGUARDANDO_ESTOQUE = 'Aguardando Estoque';
    const APROVADO = 'Aprovado';
    const EXPIRADO = 'Expirado';
    const CANCELADO = 'Cancelado';
    const STATUS_FATURADO = 'Faturado';
    
    protected $table = 'orcamentos';

    protected $fillable = [
        'cliente_id',
        'empresa_id',
        'validade',
        'data_orcamento',
        'codigo_orcamento',
        'tipo_entrega',
        'status',
        'observacoes',
        'total',
        'ativo',
        'editando_por',  // 🌟 ADICIONE ESTA LINHA
        'editando_em',   // 🌟 ADICIONE ESTA LINHA
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
    public function empresa ()
    {
        return $this->belongsTo(Empresa::Class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** Cliente do orçamento */
   public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
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

    public function lote()
    {
        return $this->hasMany(Lote::class, 'orcamento_id');
    }

    public function venda()
    {
        return $this->hasOne(Venda::class, 'orcamento_id');
    }

    public function entrega()
    {
        return $this->hasMany(Entrega::class, 'orcamento_id');
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

    public function vendedor()
    {
        return $this->belongsTo(Funcionario::class, 'vendedor_id');
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
        return $query->where('status', '!=', 'Faturado');
    }

    /** Movimentacoes Dashboard */
    public function movimentacoes()
    {
        return $this->hasMany(\App\Models\MovimentacaoOrcamento::class, 'orcamento_id');
    }
}
