<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNivelAcesso
{
    /**
     * Exemplo: ->middleware('checkNivel:admin,gerente')
     */
    public function handle(Request $request, Closure $next, ...$niveisPermitidos)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->nivel_acesso, $niveisPermitidos)) {
            abort(Response::HTTP_FORBIDDEN, 'Acesso negado: nível de permissão insuficiente.');
        }

        return $next($request);
    }
}
