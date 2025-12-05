<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'numero_lote',
        'pedido_compra_id',
        'produto_id',
        'fornecedor_id',
        'quantidade',
        'quantidade_disponivel',
        'preco_compra',
        'data_compra',
        'validade_lote',
        'lancado_por',
        'status', // 1 = ativo, 0 = inativo
    ];

    protected $casts = [
        'data_compra' => 'date',
        'validade_lote' => 'date',
        'preco_compra' => 'decimal:2',
        'quantidade' => 'decimal:2',
        'quantidade_disponivel' => 'decimal:2',
    ];

    /* ============================================================
       RELACIONAMENTOS
    ============================================================ */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'lancado_por');
    }
        public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    public function itensVenda()
    {
        return $this->belongsToMany(ItemVenda::class, 'item_venda_lote', 'lote_id', 'item_venda_id')
                    ->withPivot('quantidade');
    }


    /* ============================================================
       BOOT: NUMERO DE LOTE AUTOMÁTICO
    ============================================================ */
    protected static function booted()
    {
        static::creating(function ($lote) {
            if (empty($lote->numero_lote)) {
                $lote->numero_lote = now()->format('YmdHis') 
                    . '-' . $lote->produto_id 
                    . '-' . rand(100, 999);
            }

            // se quantidade_disponivel não informado, assume total
            if (!isset($lote->quantidade_disponivel)) {
                $lote->quantidade_disponivel = $lote->quantidade ?? 0;
            }

            // status padrão
            if (!isset($lote->status)) {
                $lote->status = 1;
            }
        });
    }

    /* ============================================================
       MÉTODOS ÚTEIS
    ============================================================ */

    /**
     * Atualiza a quantidade disponível do lote.
     * Garante que não fique negativo.
     */
    public function atualizarQuantidadeDisponivel($qtd)
    {
        $this->quantidade_disponivel = max(0, $this->quantidade_disponivel + $qtd);
        $this->save();
    }

    /**
     * Verifica se o lote ainda tem estoque disponível.
     */
    public function temEstoqueDisponivel()
    {
        return ($this->quantidade_disponivel ?? 0) > 0;
    }

    /**
     * Marca o lote como inativo (status = 0)
     */
    public function inativar()
    {
        $this->status = 0;
        $this->save();
    }

    /**
     * Retorna a menor validade entre os lotes ativos de um produto
     */
    public static function menorValidadeProduto($produtoId)
    {
        return self::where('produto_id', $produtoId)
            ->where('status', 1)
            ->whereNotNull('validade_lote')
            ->min('validade_lote');
    }
}
