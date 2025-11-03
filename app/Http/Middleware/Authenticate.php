<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado.');
        }

        return $next($request);
    }
   
    protected function redirectTo($request)
    {
        // Redireciona para a rota /login ou qualquer página de login que você tenha
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

}
