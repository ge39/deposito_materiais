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

    // Relações
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

    /**
     * Receber os produtos do pedido.
     * Atualiza quantidade_estoque dos produtos, gerencia lotes e atualiza status.
     */
   public function receberProdutos()
    {
    if ($this->status !== 'aprovado') {
        throw new \Exception("Recebimento só é permitido para pedidos aprovados.");
    }

    DB::transaction(function () {

        foreach ($this->itens as $item) {
            $produto = $item->produto;

            // Atualiza quantidade_estoque do produto
            $produto->quantidade_estoque += $item->quantidade;
            $produto->save();

            // Cria ou atualiza lote
            $lote = \App\Models\Lote::firstOrNew([
                'produto_id' => $produto->id,
                'numero_lote' => now()->format('Ymd') . $produto->id, // gera número do lote
            ]);

            $lote->quantidade = ($lote->quantidade ?? 0) + $item->quantidade;
            $lote->validade = $item->validade ?? $produto->validade ?? null;
            $lote->fornecedor_id = $this->fornecedor_id;
            $lote->data_compra = $this->data_pedido;
            $lote->save();
        }

        // Atualiza status do pedido
        $this->status = 'recebido';
        $this->updated_at = now();
        $this->save();
    });
    }
}