<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucao extends Model
{
    use HasFactory;

    protected $table = 'devolucoes';
    
    protected $fillable = [
        'cliente_id',
        'venda_id',
        'venda_item_id',
        'produto_id',
        'quantidade',
        'motivo',
        'tipo',
        'status',
        'observacao',
        'criado_por',
        'imagem1',
        'imagem2',
        'imagem3',
        'imagem4',
        'motivo_rejeicao',
        'empresa_id',
        'created_at',
        'updated_at',
    ];

    // 🔗 Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

     public function itemVenda()
    {
        return $this->belongsTo(ItemVenda::class, 'venda_item_id'); // FK correta
    }
    
    public function item()
    {
        return $this->belongsTo(ItemVenda::class, 'venda_item_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Funcionario::class, 'criado_por');
    }

    // Status helpers
    public function isAprovada()
    {
        return $this->status === 'aprovada';
    }

    public function isRejeitada()
    {
        return $this->status === 'rejeitada';
    }
}
