<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucao extends Model
{
    use HasFactory;

    // Nome exato da tabela no banco de dados
    protected $table = 'devolucoes';
    
    // Removidos 'created_at' e 'updated_at' para deixar o Eloquent gerenciar os timestamps sozinho
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

    /**
     * Vincula o item da devolução ao item original da venda.
     * ATENÇÃO: Certifique-se de que no Model 'ItemVenda' exista a linha: protected $table = 'Item_Vendas';
     */
    public function itemVenda()
    {
        return $this->belongsTo(ItemVenda::class, 'venda_item_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Vincula ao funcionário/usuário que registrou a devolução
     */
    public function usuario()
    {
        return $this->belongsTo(Funcionario::class, 'criado_por');
    }

    // 🛠️ Status Helpers

    public function isPendente()
    {
        return $this->status === 'pendente';
    }

    public function isAprovada()
    {
        return $this->status === 'aprovada';
    }

    public function isRejeitada()
    {
        return $this->status === 'rejeitada';
    }

    public function isConcluida()
    {
        return $this->status === 'concluida';
    }
}