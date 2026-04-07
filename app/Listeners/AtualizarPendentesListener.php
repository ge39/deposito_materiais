<?php

namespace App\Listeners;

use App\Events\EstoqueAtualizado;
use App\Services\EstoqueService;
use App\Models\ItemOrcamento;

class AtualizarPendentesListener
{
    protected EstoqueService $estoqueService;

    public function __construct(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    public function handle(EstoqueAtualizado $event)
    {
        $produtoId = $event->lote->produto_id;

        // Pega todos os itens de orçamento pendentes desse produto
        $itens = ItemOrcamento::where('produto_id', $produtoId)
            ->where('quantidade_pendente', '>', 0)
            ->get();

        foreach ($itens as $item) {
            // Recalcula quantidade atendida e pendente
            $this->estoqueService->atenderPendentes($item);
        }
    }
}