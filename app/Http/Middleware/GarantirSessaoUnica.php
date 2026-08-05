<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GarantirSessaoUnica
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! Auth::check()) {
            return $next($request);
        }

        $usuario = $request->user();

        $sessaoAtivaHash = trim(
            (string) $usuario->sessao_ativa_hash
        );

        $sessaoAtualHash = hash(
            'sha256',
            $request->session()->getId()
        );

        if (
            $sessaoAtivaHash !== ''
            && hash_equals(
                $sessaoAtivaHash,
                $sessaoAtualHash
            )
        ) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mensagem =
            'Este acesso foi encerrado porque o usuário entrou no sistema em outro navegador ou dispositivo.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $mensagem,
                'redirect' => route('login'),
            ], 401);
        }

        return redirect()
            ->route('login')
            ->with('error', $mensagem);
    }
}