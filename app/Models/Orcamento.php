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
        'usar_endereco_cliente',
        'endereco_entrega',
        'bairro_entrega',
        'latitude_entrega',
        'longitude_entrega',
        'coordenada_confirmada',
        'responsavel_recebimento',
        'telefone_recebimento',
        'data_prevista_entrega',
        'periodo_entrega',
        'observacao_entrega',
        'status',
        'observacoes',
        'total',
        'ativo',
        'editando_por',
        'editando_em',
    ];

    protected $casts = [
        'data_orcamento'          => 'date',
        'validade'                => 'date',
        'data_prevista_entrega'   => 'date',
        'usar_endereco_cliente'   => 'boolean',
        'latitude_entrega'        => 'decimal:7',
        'longitude_entrega'       => 'decimal:7',
        'coordenada_confirmada'   => 'boolean',
        'total'                   => 'decimal:2',
        'ativo'                   => 'boolean',
        'editando_em'             => 'datetime',
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

    /** Cliente do or├ºamento */
   public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /** Usu├írio que est├í editando o or├ºamento */
    public function editor()
    {
        return $this->belongsTo(User::class, 'editando_por');
    }

    /** Fornecedor (caso aplic├ível) */
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

    /** Itens do or├ºamento */
    public function itens()
    {
        return $this->hasMany(ItemOrcamento::class, 'orcamento_id');
    }

    /** Unidade de medida (se usada no cabe├ºalho) */
    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Funcionario::class, 'vendedor_id');
    }


    /* =========================
     | SCOPES ├ÜTEIS PARA O PDV
     ========================= */

    /** Or├ºamentos ativos */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /** Or├ºamento pelo c├│digo */
    public function scopeCodigo($query, $codigo)
    {
        return $query->where('codigo_orcamento', $codigo);
    }

    /** Or├ºamentos n├úo faturados */
    public function scopeNaoFaturado($query)
    {
        return $query->where('status', '!=', 'Faturado');
    }

    /** Movimentacoes Dashboard */
    public function movimentacoes()
    {
        return $this->hasMany(
            \App\Models\MovimentacaoOrcamento::class,
            'orcamento_id'
        );
    }
}
