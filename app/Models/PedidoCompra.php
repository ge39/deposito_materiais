<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'pedido_compras';

    protected $fillable = [
        'user_id',
        'fornecedor_id',
        'data_pedido',
        'status',
        'total',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'total' => 'decimal:2',
    ];

    // RELACIONAMENTOS ------------------------------------------------

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }


    // ================================================================
    // MÉTODO PRINCIPAL: RECEBIMENTO DO PEDIDO
    // ================================================================
    public function receberProdutos()
    {
        if ($this->status !== 'aprovado') {
            throw new \Exception("Recebimento só é permitido para pedidos com status 'aprovado'.");
        }

        DB::transaction(function () {

            foreach ($this->itens as $item) {

                $produto = $item->produto;

                // -----------------------------------------------------
                // 1. Atualiza o estoque principal (campo direto no produto)
                // -----------------------------------------------------
                $produto->increment('quantidade_estoque', $item->quantidade);


                // -----------------------------------------------------
                // 2. Gerenciamento de LOTES
                // -----------------------------------------------------

                // Número de lote mais seguro (único por item)
                $numeroLote = $item->numero_lote
                    ?? ($produto->id . '-' . now()->format('YmdHis'));

                $lote = Lote::firstOrNew([
                    'produto_id'   => $produto->id,
                    'numero_lote'  => $numeroLote,
                ]);

                $lote->quantidade = ($lote->quantidade ?? 0) + $item->quantidade;
                $lote->validade = $item->validade ?? $produto->validade_produto ?? null;
                $lote->fornecedor_id = $this->fornecedor_id;
                $lote->data_compra = $this->data_pedido;
                $lote->save();
            }

            // -----------------------------------------------------
            // 3. Atualizar o status do pedido
            // -----------------------------------------------------
            $this->update([
                'status' => 'recebido'
            ]);
        });
    }
}
