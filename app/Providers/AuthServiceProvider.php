<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * As políticas de mapeamento para o aplicativo.
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Registra quaisquer serviços de autenticação / autorização.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para gerenciar fornecedores
        Gate::define('gerenciar-fornecedores', function (User $user) {
            return in_array(strtolower($user->nivel_acesso), ['admin', 'gerente']) && $user->ativo;
        });

        // Gate para gerenciar promoções
        Gate::define('gerenciar-promocoes', function (User $user) {
            return in_array(strtolower($user->nivel_acesso), ['admin', 'gerente']) && $user->ativo;
        });
    }
}
