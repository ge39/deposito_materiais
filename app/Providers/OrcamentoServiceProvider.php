<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Orcamento;

class OrcamentoServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Atualiza todos os orçamentos expirados apenas uma vez por request
        $hoje = now()->format('Y-m-d');

        Orcamento::where('status', 'Aguardando aprovacao')
            ->whereDate('validade', '<', $hoje)
            ->update([
                'status' => 'Expirado',
                'observacoes' => \DB::raw("CONCAT(IFNULL(observacoes,''), ' | Orçamento expirado em $hoje')")
            ]);
    }
}
