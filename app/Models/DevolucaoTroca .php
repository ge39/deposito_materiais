<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucaoTroca extends Model
{
    use HasFactory;

    protected $table = 'devolucoes_trocas';

    protected $fillable = [
        'venda_id',
        'produto_id',
        'quantidade',
        'tipo',      // 'devolucao' ou 'troca'
        'motivo',    // motivo da devolução/troca
        'status',    // 'pendente', 'concluida', etc.
    ];

    /**
     * Relação com a venda
     */
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    /**
     * Relação com o produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Cliente relacionado à devolução/troca
     */
    public function cliente()
    {
        // Cliente é obtido através da venda
        return $this->venda->cliente();
    }
}
