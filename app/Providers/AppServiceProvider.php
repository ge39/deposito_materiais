<?php

namespace App\Providers;
use App\Models\Produto;
use App\Observers\ProdutoObserver;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('gerenciar-promocoes', function ($user) {
            return in_array(strtolower($user->nivel_acesso), ['admin', 'gerente']) && $user->ativo;
        });
    }
}
