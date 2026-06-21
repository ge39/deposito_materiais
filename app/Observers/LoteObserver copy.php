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
        //     $quantidadeAntes = $lote->getOriginal('quantidade');
        //     $quantidadeAtual = $lote->quantidade;

        //     if ($quantidadeAtual > $quantidadeAntes) {

        //         app(EstoqueService::class)
        //             ->atenderPendentes($lote->produto_id);
        //     }
        // }


        // public function updated(Lote $lote)
        // {
        //     if (EstoqueService::$ignorarObserver) {
        //         return;
        //     }

        //     $quantidadeAntes = $lote->getOriginal('quantidade');
        //     $quantidadeAtual = $lote->quantidade;

        //     if ($quantidadeAtual > $quantidadeAntes) {

        //         app(EstoqueService::class)
        //             ->atenderPendentes($lote->produto_id);
        //     }
        // }   

        public function updated(Lote $lote)
        {
            // ❌ NÃO reagir a reserva
            if ($lote->wasChanged('quantidade_reservada')) {
                return;
            }

            // ✔ só reage se estoque real mudou
            if ($lote->wasChanged('quantidade')) {
                app(EstoqueService::class)
                    ->atenderPendentesPorProduto($lote->produto_id);
            }
        }
        
        
    }