<?php 
    namespace App\Observers;

    use App\Models\Lote;
    use App\Services\EstoqueService;

    class LoteObserver
    {
        /**
         * Quando um lote for CRIADO
         */
        public function created(Lote $lote)
        {
            app(EstoqueService::class)
                ->atenderPendentesPorProduto($lote->produto_id);
        }

        /**
         * Quando um lote for ATUALIZADO
         * (ex: entrada manual de estoque)
         */
        // public function updated(Lote $lote)
        // {
        //     // só dispara se mudou quantidade ou disponível
        //     if ($lote->wasChanged(['quantidade', 'quantidade_disponivel'])) {
        //         app(EstoqueService::class)
        //             ->atenderPendentes($lote->produto_id);
        //     }
        // }

        public function updated(Lote $lote)
        {
            $quantidadeAntes = $lote->getOriginal('quantidade');
            $quantidadeAtual = $lote->quantidade;

            if ($quantidadeAtual > $quantidadeAntes) {

                app(EstoqueService::class)
                    ->atenderPendentes($lote->produto_id);
            }
        }
        
        
    }