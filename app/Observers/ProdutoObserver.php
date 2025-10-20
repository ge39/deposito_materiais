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
    public function created(Produto $produto): void
    {
        // Cria automaticamente o lote
        Lote::create([
            'produto_id' => $produto->id,
            'fornecedor_id' => $produto->fornecedor_id,  // pegue do produto
            'quantidade' => $produto->quantidade_estoque,
            'preco_compra' => $produto->preco_custo,
            'validade' => $produto->validade,
        ]);
    }

    /**
     * Handle the Produto "updated" event.
     */
    public function updated(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "deleted" event.
     */
    public function deleted(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "restored" event.
     */
    public function restored(Produto $produto): void
    {
        //
    }

    /**
     * Handle the Produto "force deleted" event.
     */
    public function forceDeleted(Produto $produto): void
    {
        //
    }
}
