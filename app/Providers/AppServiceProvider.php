<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Orcamento;
use App\Models\Promocao; // <-- IMPORTAR AQUI
use App\Observers\PromocaoObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
    {
        $hoje = now()->format('Y-m-d');
         
        // Registrar Observer
        Promocao::observe(PromocaoObserver::class);

        // Atualizar orçamentos expirados
        Orcamento::where('status', 'Aguardando aprovacao')
            ->whereDate('validade', '<', $hoje)
            ->update([
                'status' => 'Expirado',
                'observacoes' => DB::raw("CONCAT(IFNULL(observacoes,''), ' | Orçamento expirado em $hoje')")
            ]);
    }
}
