<?php

namespace App\Observers;

use App\Models\Produto;
use App\Models\Lote;
use Carbon\Carbon;

class ProdutoObserver
{
    /**
     * Handle the Produto "created" event.
     */
    public function created(Produto $produto)
    {
        if (!empty($produto->quantidade_estoque) && $produto->quantidade_estoque > 0) {

            Lote::create([
                'produto_id' => $produto->id,
                'fornecedor_id' => $produto->fornecedor_id, // Corrigido
                'quantidade' => $produto->quantidade_estoque,
                'preco_compra' => $produto->preco_custo ?? 0,
                'data_compra' => $produto->data_compra ?? now(),
                'validade_lote' => $produto->validade_produto
                    ? Carbon::parse($produto->validade_produto)->startOfDay()
                    : now(), // não deixar NULL
            ]);

            $produto->estoque_total = $produto->lotes()->sum('quantidade');
            $produto->saveQuietly();
        }
    }


    /**
     * Handle the Produto "updated" event.
     * Se quiser criar lotes também ao atualizar, use este método.
     */
    public function updated(Produto $produto)
    {
        // Aqui você pode decidir se atualiza lotes existentes ou cria novos
        // Exemplo: não criar lote ao atualizar
    }
}
