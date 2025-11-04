<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckDatabaseConnection
{
    /**
     * Verifica se há conexão com o banco de dados antes de continuar a requisição.
     */
    public function handle($request, Closure $next)
    {
        try {
            // Testa a conexão
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            // Retorna uma resposta amigável
            return response()->view('errors.database', [
                'message' => 'Não foi possível conectar ao banco de dados. Verifique a configuração da conexão.'
            ], 500);
        }

        return $next($request);
    }
}
