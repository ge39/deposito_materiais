<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Orcamento;
use App\Models\Promocao; // <-- IMPORTAR AQUI
use App\Observers\PromocaoObserver;
use App\Models\PagamentoVenda;
use App\Models\Venda;
use App\Observers\PagamentoVendaObserver;
use App\Observers\VendaObserver;
use App\Models\Lote;
use App\Observers\LoteObserver;

class AppServiceProvider extends ServiceProvider
{
  
    public function boot(): void
    {
        $hoje = now()->format('Y-m-d');
         
        // Registrar Observer
        Promocao::observe(PromocaoObserver::class);
        PagamentoVenda::observe(PagamentoVendaObserver::class);
        Venda::observe(VendaObserver::class);

        // Atualizar orçamentos expirados
        Orcamento::where('status', 'Aguardando aprovacao')
            ->whereDate('validade', '<', $hoje)
            ->update([
                'status' => 'Expirado',
                'observacoes' => DB::raw("CONCAT(IFNULL(observacoes,''), ' | Orçamento expirado em $hoje')")
        ]);

         Lote::observe(LoteObserver::class);
    }
}
