<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Caixa;

class CheckCaixaAberto
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('🔵 [CheckCaixaAberto] Middleware iniciado');

        // 1. Terminal vindo do middleware anterior
        $terminal = $request->attributes->get('terminal');

        Log::info('🔵 [CheckCaixaAberto] Terminal recebido', [
            'terminal' => $terminal ? $terminal->toArray() : null,
        ]);

        if (!$terminal) {
            Log::error('🔴 [CheckCaixaAberto] Terminal NÃO identificado');
            abort(500, 'Terminal não identificado no CheckCaixaAberto.');
        }

        // 2. Consulta ao caixa
        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->latest('data_abertura')
            ->first();

        Log::info('🔵 [CheckCaixaAberto] Resultado da consulta de caixa', [
            'terminal_id' => $terminal->id,
            'caixa_encontrado' => $caixaAberto ? true : false,
            'caixa' => $caixaAberto ? $caixaAberto->toArray() : null,
        ]);

        // 3. Injeção no request
        $request->attributes->set('caixaAberto', $caixaAberto);

        Log::info('🟢 [CheckCaixaAberto] caixaAberto injetado no request');

        return $next($request);
    }
}
