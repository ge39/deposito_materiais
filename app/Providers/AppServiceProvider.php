<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator; // 🎯 CORREÇÃO DO BUG: Importação do Paginator adicionada
use App\Models\Orcamento;
use App\Models\Promocao;
use App\Observers\PromocaoObserver;
use App\Models\PagamentoVenda;
use App\Models\Venda;
use App\Observers\PagamentoVendaObserver;
use App\Observers\VendaObserver;
use App\Models\Lote;
use App\Observers\LoteObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. 🟢 FORÇA O LARAVEL A GERAR PAGINAÇÃO COMPATÍVEL COM BOOTSTRAP 5
        Paginator::useBootstrapFive();

        // 2. Registrar Observers do Ecossistema
        Promocao::observe(PromocaoObserver::class);
        PagamentoVenda::observe(PagamentoVendaObserver::class);
        Venda::observe(VendaObserver::class);
        Lote::observe(LoteObserver::class);

        // 3. 📉 MELHORIA DE ARQUITETURA (Otimização contra gargalos no XAMPP):
        // Executa a query de expiração apenas se a requisição atual não for uma chamada AJAX/API rápida.
        if (!request()->ajax() && !request()->wantsJson()) {
            $hoje = now()->format('Y-m-d');
            
            Orcamento::where('status', 'Aguardando aprovacao')
                ->whereDate('validade', '<', $hoje)
                ->update([
                    'status' => 'Expirado',
                    'observacoes' => DB::raw("CONCAT(IFNULL(observacoes,''), ' | Orçamento expirado em $hoje')")
                ]);
        }
    }
}
