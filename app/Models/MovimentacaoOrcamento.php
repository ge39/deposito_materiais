<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimentacaoOrcamento extends Model
{
    protected $table = 'movimentacao_orcamentos';

    protected $fillable = [
        'orcamento_id',
        'item_orcamento_id',
        'tipo',
        'descricao',
        'quantidade',
        'user_id',
    ];

    /**
     * 🔹 Relacionamento com orçamento
     */
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    /**
     * 🔹 Relacionamento com item
     */
    public function item()
    {
        return $this->belongsTo(ItemOrcamento::class, 'item_orcamento_id');
    }

    /**
     * 🔹 Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}