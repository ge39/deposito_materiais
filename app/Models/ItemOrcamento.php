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
        'preco_unitario',
        'subtotal'
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
