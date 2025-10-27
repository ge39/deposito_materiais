<?php

namespace App\Observers;

use App\Models\Produto;
use App\Models\Lote;

class ProdutoObserver
{
    /**
     * Handle the Produto "created" event.
     */
    public function created(Produto $produto): void
    {
        $this->gerarLote($produto);
    }

    /**
     * Handle the Produto "updated" event.
     */
    public function updated(Produto $produto): void
    {
        $this->gerarLote($produto);
    }

    /**
     * Gera ou atualiza lote do produto
     */
    protected function gerarLote(Produto $produto): void
    {
        if ($produto->quantidade_estoque > 0) {
            // Número de lote único: YYYYMMDD + id + random
            $numeroLote = date('Ymd') . $produto->id . rand(10, 99);

            Lote::create([
                'produto_id' => $produto->id,
                'fornecedor_id' => $produto->fornecedor_id,
                'quantidade' => $produto->quantidade_estoque,
                'preco_compra' => $produto->preco_custo,
                'data_compra' => $produto->data_compra,
                'validade' => $produto->validade,
                'numero_lote' => $numeroLote,
            ]);
        } else {
            // Estoque zero -> lote "SEM_LOTE"
            Lote::updateOrCreate(
                ['produto_id' => $produto->id, 'numero_lote' => 'SEM_LOTE'],
                [
                    'quantidade' => $produto->quantidade_estoque,
                    'fornecedor_id' => $produto->fornecedor_id,
                    'preco_compra' => $produto->preco_custo,
                    'data_compra' => $produto->data_compra,
                    'validade' => $produto->validade,
                ]
            );
        }
    }
}
