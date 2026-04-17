<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\ItemOrcamentoLote;

class ItemOrcamento extends Model
{
    use HasFactory;

    protected $table = 'item_orcamentos';

    protected $fillable = [
        'orcamento_id',
        'produto_id',
        'lote_id',
        'descricao_cliente',
        'quantidade_solicitada',
        'quantidade_atendida',
        'status',
        'preco_unitario',
        'subtotal',
        'previsao_entrega'
    ];

    protected $casts = [
        'quantidade_solicitada' => 'decimal:2',
        'quantidade_atendida'   => 'decimal:2',
        'preco_unitario'        => 'decimal:2',
        'subtotal'              => 'decimal:2',
        'previsao_entrega'      => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function lotesPivot()
    {
        return $this->hasMany(ItemOrcamentoLote::class);
    }

    public function lotes()
    {
        return $this->belongsToMany(
            Lote::class,
            'item_orcamento_lotes',
            'item_orcamento_id',
            'lote_id'
        )->withPivot([
            'quantidade_reservada',
            'quantidade_atendida'
        ])->withTimestamps();
    }
    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getNomeItemAttribute()
    {
        return $this->produto?->nome
            ?? $this->descricao_cliente
            ?? 'Produto não especificado';
    }

    public function getQuantidadePendenteAttribute()
    {
        return max(
            0,
            ($this->quantidade_solicitada ?? 0) - ($this->quantidade_atendida ?? 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    public function getLotePrincipalAttribute()
    {
        return $this->lote 
            ?? $this->lotes->first()?->lote;
    }

    public function estaPendente(): bool
    {
        return $this->quantidade_pendente > 0;
    }

    public function estaCompleto(): bool
    {
        return $this->quantidade_pendente <= 0;
    }
}