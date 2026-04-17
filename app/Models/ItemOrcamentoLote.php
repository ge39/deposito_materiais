<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemOrcamentoLote extends Model
{
    protected $table = 'item_orcamento_lotes';

    protected $fillable = [
        'item_orcamento_id',
        'lote_id',
        'quantidade_atendida'
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemOrcamento::class, 'item_orcamento_id');
    }
}