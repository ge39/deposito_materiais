<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Promocao;

class PromocaoPolicy
{
    public function before(User $user, $ability)
    {
        // Ajuste com o nome correto do campo do seu User
        $nivel = strtolower(trim($user->nivel_acesso ?? $user->nivel ?? ''));

        if ($nivel === 'admin') {
            return true; // admin passa em tudo
        }
    }

    public function viewAny(User $user)
    {
        return in_array(strtolower(trim($user->nivel_acesso ?? '')), ['admin', 'gerente']);
    }

    public function view(User $user, Promocao $promocao)
    {
        return in_array(strtolower(trim($user->nivel_acesso ?? '')), ['admin', 'gerente']);
    }

    public function create(User $user)
    {
        return in_array(strtolower(trim($user->nivel_acesso ?? '')), ['admin', 'gerente']);
    }

    public function update(User $user, Promocao $promocao)
    {
        return in_array(strtolower(trim($user->nivel_acesso ?? '')), ['admin', 'gerente']);
    }

    public function delete(User $user, Promocao $promocao)
    {
        return in_array(strtolower(trim($user->nivel_acesso ?? '')), ['admin', 'gerente']);
    }
}
