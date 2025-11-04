<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckNivel
{
    /**
     * Trata uma requisição recebida.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$niveis  Níveis permitidos (ex: admin, gerente)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$niveis)
    {
        $user = Auth::user();

        // Se o usuário não estiver logado
        if (!$user) {
            return redirect()->route('login');
        }

        // Se não tiver nível permitido
        if (!in_array($user->nivel_acesso, $niveis)) {
            abort(403, 'Acesso negado: nível insuficiente.');
        }

        return $next($request);
    }
}