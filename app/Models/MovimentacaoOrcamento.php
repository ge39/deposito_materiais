<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoOrcamento extends Model
{
    protected $table = 'movimentacao_orcamentos';

    protected $fillable = [
        'lote_id',
        'orcamento_id',
        'item_orcamento_id',
        'user_id',
        'tipo',
        'descricao',
        'quantidade_antes',
        'quantidade_depois',
        'origem',
    ];

    // 🔗 Relacionamentos
    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemOrcamento::class, 'item_orcamento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
     
    public function produto()
    {
        return $this->item?->produto;
    }
}