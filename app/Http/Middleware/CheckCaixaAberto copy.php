<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Caixa;

class CheckCaixaAberto
{
    public function handle(Request $request, Closure $next)
    {
        $terminal = $request->attributes->get('terminal');
        $userId = auth()->id();

        if (!$terminal) {
            abort(500, 'Terminal não identificado.');
        }

        if (!$userId) {
            abort(403, 'Usuário não autenticado.');
        }

        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('user_id', $userId)
            ->where('status', 'aberto')
            ->orderByDesc('data_abertura')
            ->first();

        if (!$caixaAberto) {
            return redirect()
                ->route('caixa.abrir')
                ->with('erro', 'Não existe caixa aberto para este usuário neste terminal.');
        }

        $request->attributes->set('caixaAberto', $caixaAberto);

        return $next($request);
    }
}
